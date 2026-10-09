<?php
declare(strict_types=1);

use App\Services\DriverInvitationDelivery;

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root=dirname(__DIR__);
require_once $root.'/src/Services/DriverInvitations.php';
require_once $root.'/src/Services/DriverInvitationDelivery.php';
require_once $root.'/config/driver-invitations.php';
require_once $root.'/config/driver-invitation-mailer.php';
try {
    require_once $root.'/config/database.php';
    $db = Database::getInstance();
    require_once $root.'/vendor/autoload.php';
    require_once $root.'/src/Services/AuditLogger.php';
    $key=driverInvitationEncryptionKey();
    $sender=static function(array $payload): bool {
        $url=$payload['invitation_url']; $contact=$payload['contact'];
        if ($contact['type']==='email') {
            $safeUrl=htmlspecialchars($url,ENT_QUOTES,'UTF-8');
            $mail = driverInvitationMailer();
            $mail->addAddress($contact['value'], 'PUV driver');
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Your Civentral PUV driver invitation';
            $mail->Body = <<<HTML
<!doctype html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Your Civentral PUV driver invitation</title></head>
<body style="margin:0;padding:0;background-color:#F1F5F9;font-family:Arial,Helvetica,sans-serif;color:#334155;">
  <div style="display:none;font-size:1px;line-height:1px;color:#F1F5F9;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;">Your PUV driver registration is approved. Open your Civentral invitation to continue.</div>
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#F1F5F9">
    <tr><td align="center" style="padding:24px 12px;">
      <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="width:100%;max-width:560px;table-layout:fixed;border:1px solid #E2E8F0;border-radius:12px;background-color:#FFFFFF;">
        <tr><td align="center" style="padding:28px 24px 22px;border-bottom:1px solid #E2E8F0;">
          <h1 style="margin:0;color:#176B87;font-size:26px;line-height:34px;font-weight:800;letter-spacing:2px;">CIVENTRAL</h1>
          <p style="margin:6px 0 0;color:#386B99;font-size:11px;line-height:18px;font-weight:bold;letter-spacing:1px;">CALOOCAN PORTAL &middot; DRIVER SERVICES</p>
        </td></tr>
        <tr><td style="padding:26px 24px 0;">
          <h2 style="margin:0 0 20px;color:#173E4C;font-size:22px;line-height:30px;">Your PUV driver invitation</h2>
          <p style="margin:0 0 16px;font-size:15px;line-height:24px;">Hello,</p>
          <p style="margin:0 0 16px;font-size:15px;line-height:24px;">Your PUV driver registration has been <strong>approved by a Records Officer.</strong></p>
          <p style="margin:0;font-size:15px;line-height:24px;">Open your invitation to review your details and continue setting up or linking your Civentral account.</p>
        </td></tr>
        <tr><td align="center" style="padding:26px 24px;">
          <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tr><td align="center" bgcolor="#176B87" style="border-radius:8px;background-color:#176B87;mso-padding-alt:15px 26px;">
            <a href="{$safeUrl}" style="display:inline-block;padding:15px 26px;color:#FFFFFF;font-size:16px;line-height:22px;font-weight:bold;text-decoration:none;border-radius:8px;">Civentral Invitation</a>
          </td></tr></table>
        </td></tr>
        <tr><td style="padding:0 24px 24px;">
          <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#EEF5FF" style="border:1px solid #B4D4FF;border-radius:8px;background-color:#EEF5FF;"><tr><td style="padding:16px;">
            <p style="margin:0 0 6px;color:#173E4C;font-size:14px;line-height:22px;font-weight:bold;">Already have a Civentral account?</p>
            <p style="margin:0;color:#334155;font-size:14px;line-height:22px;">Use your current password. This invitation does not change your existing password.</p>
          </td></tr></table>
          <p style="margin:22px 0 8px;color:#475569;font-size:12px;line-height:20px;">If the button does not open, copy and paste this link into your browser:</p>
          <p style="margin:0;font-size:12px;line-height:20px;word-break:break-all;overflow-wrap:anywhere;"><a href="{$safeUrl}" style="color:#176B87;text-decoration:underline;word-break:break-all;">{$safeUrl}</a></p>
        </td></tr>
        <tr><td align="center" style="padding:20px 24px;border-top:1px solid #E2E8F0;">
          <p style="margin:0 0 8px;color:#475569;font-size:12px;line-height:20px;">Keep this invitation link private. If you were not expecting this invitation, you can ignore this email.</p>
          <p style="margin:0;color:#64748B;font-size:11px;line-height:18px;">Civentral &middot; Transport &amp; Mobility</p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
            $mail->AltBody = "CIVENTRAL | Caloocan Portal - Driver Services\n\nYour PUV driver invitation\n\nHello,\n\nYour PUV driver registration has been approved by a Records Officer.\nOpen your invitation to review your details and continue setting up or linking your Civentral account.\n\nCiventral Invitation:\n".$url."\n\nAlready have a Civentral account? Use your current password. This invitation does not change your existing password.\n\nKeep this invitation link private. If you were not expecting this invitation, you can ignore this email.\n\nCiventral | Transport & Mobility";
            return $mail->send() === true;
        }
        $token=getenv('IPROGSMS_API_KEY')?:($_ENV['IPROGSMS_API_KEY']??'');
        if ($token==='' || !function_exists('curl_init')) return false;
        $curl=curl_init('https://www.iprogsms.com/api/v1/sms_messages');
        curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>12,
            CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_HTTPHEADER=>['Content-Type: application/json'],
            CURLOPT_POSTFIELDS=>json_encode(['api_token'=>$token,'phone_number'=>$contact['value'],
                'message'=>'You have been approved as a PUV driver. Open your Civentral invitation: '.$url],JSON_THROW_ON_ERROR)]);
        $response=curl_exec($curl); $status=curl_getinfo($curl,CURLINFO_HTTP_CODE); curl_close($curl);
        $body=is_string($response)?json_decode($response,true):null;
        return $status>=200 && $status<300 && is_array($body) && in_array($body['status']??null,[200,'200','success'],true);
    };
    $result = (new DriverInvitationDelivery($db->getPdo(),$key,$sender,'driverInvitationAudit'))->run();
    echo json_encode($result,JSON_THROW_ON_ERROR).PHP_EOL;
    exit($result['failed'] > 0 ? 1 : 0);
} catch (Throwable) { fwrite(STDERR,"Driver invitation delivery unavailable.\n"); exit(1); }
