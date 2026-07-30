<?php

namespace App\Services;

class AuditLogger
{
    /**
     * Centralized static method to log a generic audit event.
     *
     * @param array $params
     * @return int|bool
     */
    public static function log(array $params)
    {
        global $db;

        try {
            if (!$db && class_exists('Database')) {
                $db = \Database::getInstance();
            }
            if (!$db) {
                return false;
            }

            $action = $params['action'] ?? 'ACTIVITY_LOG';
            $description = $params['description'] ?? null;
            $targetTable = $params['target_table'] ?? null;
            $targetId = isset($params['target_id']) ? (string)$params['target_id'] : null;
            $status = $params['status'] ?? 'Success';

            // Process context JSON
            $contextJson = null;
            if (isset($params['context_json'])) {
                if (is_array($params['context_json'])) {
                    $contextJson = json_encode($params['context_json'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                } else {
                    $contextJson = (string)$params['context_json'];
                }
            }

            // 1. Actor User ID
            $actorUserId = $params['actor_user_id'] ?? ($_SESSION['user_id'] ?? null);

            // 2. Session ID
            $sessionId = $params['session_id'] ?? ($_SESSION['session_id'] ?? null);

            // 3. Department ID resolution
            $departmentId = $params['department_id'] ?? ($_SESSION['department_id'] ?? null);
            if (empty($departmentId) && !empty($actorUserId)) {
                $deptQuery = "SELECT p.department_id 
                              FROM users u 
                              LEFT JOIN positions p ON u.position_id = p.position_id 
                              WHERE u.user_id = :user_id 
                              LIMIT 1";
                $res = $db->query($deptQuery, ['user_id' => $actorUserId]);
                if (!empty($res[0]['department_id'])) {
                    $departmentId = (int)$res[0]['department_id'];
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        $_SESSION['department_id'] = $departmentId;
                    }
                }
            }

            // 4. Module & Resource IDs
            $moduleId = $params['module_id'] ?? null;
            $resourceId = $params['resource_id'] ?? null;

            // 5. HTTP Request Context
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'POST';
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';

            // Parse User Agent
            $parsedUa = self::parseUserAgent($userAgent);
            $browser = $params['browser'] ?? $parsedUa['browser'];
            $operatingSystem = $params['operating_system'] ?? $parsedUa['os'];

            $insertPayload = [
                'actor_user_id'    => $actorUserId,
                'session_id'       => $sessionId,
                'department_id'    => $departmentId,
                'module_id'        => $moduleId,
                'resource_id'      => $resourceId,
                'action'           => $action,
                'target_table'     => $targetTable,
                'target_id'        => $targetId,
                'description'      => $description,
                'ip_address'       => $ipAddress,
                'request_method'   => $requestMethod,
                'request_uri'      => $requestUri,
                'browser'          => $browser,
                'operating_system' => $operatingSystem,
                'status'           => $status,
                'context_json'     => $contextJson,
                'created_at'       => date('Y-m-d H:i:s')
            ];

            return $db->insert('audit_logs', $insertPayload);
        } catch (\Throwable $e) {
            error_log("AuditLogger Failure: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log a CRUD Data Mutation event with before-and-after field comparison.
     * Prevents audit logging if no fields were modified during an update operation.
     *
     * @param array $params
     * @return int|bool
     */
    public static function logMutation(array $params)
    {
        $action = $params['action'] ?? 'Data Update';
        $targetTable = $params['target_table'] ?? 'system';
        $targetId = isset($params['target_id']) ? (string)$params['target_id'] : null;

        $oldData = is_array($params['old_data'] ?? null) ? $params['old_data'] : null;
        $newData = is_array($params['new_data'] ?? null) ? $params['new_data'] : null;

        $changes = [];
        $ignoredFields = ['updated_at', 'created_at', 'password'];

        if ($oldData !== null && $newData !== null) {
            $allKeys = array_unique(array_merge(array_keys($oldData), array_keys($newData)));
            foreach ($allKeys as $key) {
                if (in_array($key, $ignoredFields, true)) {
                    continue;
                }
                $oldVal = $oldData[$key] ?? null;
                $newVal = $newData[$key] ?? null;

                // Handle password masking if passed
                if (strpos(strtolower($key), 'password') !== false) {
                    $oldVal = '***';
                    $newVal = '***';
                }

                if ((string)$oldVal !== (string)$newVal) {
                    $changes[$key] = [
                        'old' => $oldVal,
                        'new' => $newVal
                    ];
                }
            }

            // No-op check: if action is an update and zero fields changed, skip audit logging
            if (empty($changes) && (stripos($action, 'Update') !== false || stripos($action, 'Edit') !== false)) {
                return true; // No changes to record
            }
        }

        // Build human-readable description summary if not explicitly provided
        $description = $params['description'] ?? null;
        if (empty($description)) {
            if (!empty($changes)) {
                $changeSummaries = [];
                foreach ($changes as $fld => $diff) {
                    $o = $diff['old'] !== null ? (string)$diff['old'] : 'null';
                    $n = $diff['new'] !== null ? (string)$diff['new'] : 'null';
                    $changeSummaries[] = "{$fld}: \"{$o}\" → \"{$n}\"";
                }
                $description = "{$action} on {$targetTable}" . ($targetId ? " #{$targetId}" : "") . ": " . implode(', ', $changeSummaries);
            } else {
                $description = "{$action} on {$targetTable}" . ($targetId ? " #{$targetId}" : "");
            }
        }

        // Build structured context JSON payload
        $contextPayload = [
            'old_data' => $oldData,
            'new_data' => $newData,
            'changes'  => !empty($changes) ? $changes : null
        ];

        // Merge existing context_json if provided as an array
        if (isset($params['context_json']) && is_array($params['context_json'])) {
            $contextPayload = array_merge($contextPayload, $params['context_json']);
        }

        $params['description'] = $description;
        $params['context_json'] = $contextPayload;

        return self::log($params);
    }

    /**
     * Parse User-Agent string to extract browser and OS.
     */
    public static function parseUserAgent(?string $ua): array
    {
        if (!$ua) {
            return ['browser' => 'Unknown', 'os' => 'Unknown'];
        }

        $os = 'Desktop OS';
        if (preg_match('/windows|win32/i', $ua)) {
            $os = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/linux/i', $ua)) {
            $os = 'Linux';
        } elseif (preg_match('/android/i', $ua)) {
            $os = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $ua)) {
            $os = 'iOS';
        }

        $browser = 'Browser';
        if (preg_match('/edg/i', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/chrome|crios/i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox|fxios/i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $ua)) {
            $browser = 'Safari';
        } elseif (preg_match('/msie|trident/i', $ua)) {
            $browser = 'Internet Explorer';
        }

        return ['browser' => $browser, 'os' => $os];
    }
}
