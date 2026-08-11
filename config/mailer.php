<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php'; 
require_once __DIR__ . '/database.php';

function sendSystemEmail($toEmail, $toName, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USER');
        $mail->Password   = getenv('SMTP_PASS');
        $mail->SMTPSecure = getenv('SMTP_ENCRYPTION') ?: PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = getenv('SMTP_PORT') ?: 587;

        $mail->setFrom(getenv('SMTP_FROM_EMAIL') ?: 'civentral@gmail.com', getenv('SMTP_FROM_NAME') ?: 'Civentral Portal');
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        return $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

if (!function_exists('sendOTPEmail')) {
    function sendOTPEmail($toEmail, $toName, $otpCode, $purpose = 'Registration') {
        $subject = "CIVentral Verification Code - {$otpCode}";
        $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #E2E8F0; border-radius: 12px;'>
                <h2 style='color: #0F172A; text-align: center;'>CIVentral Municipal Portal</h2>
                <hr style='border: none; border-top: 1px solid #E2E8F0;' />
                <p>Hello <strong>" . htmlspecialchars($toName) . "</strong>,</p>
                <p>Your one-time verification code for <strong>" . htmlspecialchars($purpose) . "</strong> is:</p>
                <div style='background-color: #F1F5F9; border-radius: 8px; padding: 16px; text-align: center; margin: 20px 0;'>
                    <span style='font-size: 32px; font-weight: bold; letter-spacing: 6px; color: #165B7E;'>" . htmlspecialchars($otpCode) . "</span>
                </div>
                <p style='color: #64748B; font-size: 13px;'>This code is valid for 10 minutes. If you did not request this code, please ignore this email.</p>
                <hr style='border: none; border-top: 1px solid #E2E8F0;' />
                <p style='color: #94A3B8; font-size: 11px; text-align: center;'>City Government of Caloocan - CIVentral E-Governance Portal</p>
            </div>
        ";
        return sendSystemEmail($toEmail, $toName, $subject, $body);
    }
}
