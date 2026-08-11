<?php
/**
 * CIVENTRAL - IprogSMS REST API Helper Service
 */

if (!function_exists('sendSMSOTP')) {
    function sendSMSOTP($toMobile, $otpCode, $purpose = 'Registration') {
        $apiKey = getenv('IPROGSMS_API_KEY') ?: ($_ENV['IPROGSMS_API_KEY'] ?? '');
        $apiUrl = getenv('IPROGSMS_API_URL') ?: 'https://api.iprogsms.com/v1/send';

        if (empty($toMobile)) {
            return false;
        }

        // Clean mobile number format (convert +63 or 09 to 639 format)
        $cleanPhone = preg_replace('/[^0-9]/', '', $toMobile);
        if (substr($cleanPhone, 0, 1) === '0') {
            $cleanPhone = '63' . substr($cleanPhone, 1);
        } elseif (substr($cleanPhone, 0, 2) !== '63' && strlen($cleanPhone) === 10) {
            $cleanPhone = '63' . $cleanPhone;
        }

        $message = "CIVentral Portal: Your 6-digit verification code for {$purpose} is {$otpCode}. Valid for 10 minutes.";

        $payload = json_encode([
            'api_key' => $apiKey,
            'api_token' => $apiKey,
            'phone' => $cleanPhone,
            'to' => $cleanPhone,
            'message' => $message,
            'sender_id' => 'CIVentral'
        ]);

        try {
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
                'Content-Length: ' . strlen($payload)
            ]);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            error_log("IprogSMS Sent to {$cleanPhone} (HTTP {$httpCode}): {$result}");
            return ($httpCode >= 200 && $httpCode < 300);
        } catch (Throwable $e) {
            error_log("IprogSMS Exception: " . $e->getMessage());
            return false;
        }
    }
}
