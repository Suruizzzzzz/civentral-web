<?php
declare(strict_types=1);

function driverInvitationMailer(): \PHPMailer\PHPMailer\PHPMailer
{
    require_once dirname(__DIR__).'/vendor/autoload.php';
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = getenv('SMTP_USER') ?: '';
    $mail->Password = getenv('SMTP_PASS') ?: '';
    $mail->SMTPSecure = getenv('SMTP_ENCRYPTION') ?: 'tls';
    $mail->Port = (int)(getenv('SMTP_PORT') ?: 587);
    $mail->Timeout = 12;
    $mail->getSMTPInstance()->Timelimit = 15;
    $mail->SMTPDebug = 0;
    $mail->setFrom(getenv('SMTP_FROM_EMAIL') ?: 'civentral@gmail.com', getenv('SMTP_FROM_NAME') ?: 'Civentral Portal');
    return $mail;
}
