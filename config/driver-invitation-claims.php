<?php
declare(strict_types=1);
require_once __DIR__.'/driver-invitations.php';
require_once __DIR__.'/driver-invitation-mailer.php';

function driverInvitationClaimConfig(): array
{
    return ['legal'=>[
        'terms_version'=>driverInvitationSetting('CIVENTRAL_DRIVER_TERMS_VERSION') ?: '2026-09-07',
        'privacy_version'=>driverInvitationSetting('CIVENTRAL_DRIVER_PRIVACY_VERSION') ?: '2026-09-07',
    ]];
}

function sendDriverInvitationEmailCode(string $email, string $code): bool
{
    require_once dirname(__DIR__).'/vendor/autoload.php';
    try {
        $mail=driverInvitationMailer();
        $mail->addAddress($email);
        $mail->Subject='Verify your email for your PUV driver invitation';
        $mail->Body='Your email verification code is '.$code.'. It expires in 10 minutes. This code is only for your PUV driver invitation.';
        return $mail->send();
    } catch (Throwable) { return false; }
}
