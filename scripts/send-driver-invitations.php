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
            $mail->Subject = 'Your Civentral PUV driver invitation';
            $mail->Body = '<p>You have been registered as a PUV driver and approved by a Records Officer.</p><p><a href="'.$safeUrl.'">Open your Civentral invitation</a></p><p>This invitation does not change your existing password.</p>';
            $mail->AltBody = 'You have been approved as a PUV driver. Open your Civentral invitation: '.$url;
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
