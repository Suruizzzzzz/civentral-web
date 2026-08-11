<?php
/**
 * CIVENTRAL - IprogSMS Dedicated OTP API Integration Service
 * Uses IPROG's dedicated OTP REST endpoints:
 * - POST https://www.iprogsms.com/api/v1/otp/send_otp
 * - POST https://www.iprogsms.com/api/v1/otp/verify_otp
 */

if (!function_exists('sendIprogSMSOTP')) {
    /**
     * Send OTP via IPROG Dedicated OTP API
     */
    function sendIprogSMSOTP($toMobile) {
        $apiToken = getenv('IPROGSMS_API_KEY') ?: ($_ENV['IPROGSMS_API_KEY'] ?? '4a370bcf7f9967169168f4f42fbc42f33d66bf57');
        $apiUrl = 'https://www.iprogsms.com/api/v1/otp/send_otp';

        if (empty($toMobile)) {
            return false;
        }

        // Standardize mobile number format
        $cleanPhone = preg_replace('/[^0-9]/', '', $toMobile);
        if (substr($cleanPhone, 0, 1) === '0') {
            $cleanPhone = '63' . substr($cleanPhone, 1);
        } elseif (substr($cleanPhone, 0, 2) !== '63' && strlen($cleanPhone) === 10) {
            $cleanPhone = '63' . $cleanPhone;
        }

        $postFields = http_build_query([
            'api_token' => $apiToken,
            'phone_number' => $cleanPhone,
            'expires_in_minutes' => 10
        ]);

        try {
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded'
            ]);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            error_log("IprogSMS Send OTP Response (HTTP {$httpCode}): {$result}");
            
            $json = json_decode($result, true);
            return ($httpCode === 200 && isset($json['status']) && $json['status'] === 'success');
        } catch (Throwable $e) {
            error_log("IprogSMS Send OTP Exception: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('verifyIprogSMSOTP')) {
    /**
     * Verify OTP via IPROG Dedicated OTP API
     */
    function verifyIprogSMSOTP($toMobile, $otpCode) {
        $apiToken = getenv('IPROGSMS_API_KEY') ?: ($_ENV['IPROGSMS_API_KEY'] ?? '4a370bcf7f9967169168f4f42fbc42f33d66bf57');
        $apiUrl = 'https://www.iprogsms.com/api/v1/otp/verify_otp';

        if (empty($toMobile) || empty($otpCode)) {
            return false;
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $toMobile);
        if (substr($cleanPhone, 0, 1) === '0') {
            $cleanPhone = '63' . substr($cleanPhone, 1);
        } elseif (substr($cleanPhone, 0, 2) !== '63' && strlen($cleanPhone) === 10) {
            $cleanPhone = '63' . $cleanPhone;
        }

        $postFields = http_build_query([
            'api_token' => $apiToken,
            'phone_number' => $cleanPhone,
            'otp' => trim($otpCode)
        ]);

        try {
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded'
            ]);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            error_log("IprogSMS Verify OTP Response (HTTP {$httpCode}): {$result}");
            
            $json = json_decode($result, true);
            return ($httpCode === 200 && isset($json['status']) && $json['status'] === 'success');
        } catch (Throwable $e) {
            error_log("IprogSMS Verify OTP Exception: " . $e->getMessage());
            return false;
        }
    }
}
