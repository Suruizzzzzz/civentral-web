<?php
declare(strict_types=1);
use App\Services\DriverInvitationClaims;
use App\Services\DriverInvitationError;
ini_set('display_errors','0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');
header('Allow: POST');
$root=dirname(__DIR__,2);
require_once $root.'/src/Services/DriverInvitations.php';
require_once $root.'/src/Services/DriverInvitationClaims.php';
require_once $root.'/config/driver-invitation-claims.php';
try {
    if (($_SERVER['REQUEST_METHOD']??'GET')!=='POST') throw new DriverInvitationError(405,'method_not_allowed','Use POST.');
    $raw=file_get_contents('php://input',false,null,0,8193);
    if (!is_string($raw) || strlen($raw)>8192) throw new DriverInvitationError(413,'request_too_large','Request is too large.');
    try { $input=json_decode($raw,true,16,JSON_THROW_ON_ERROR); }
    catch (JsonException) { throw new DriverInvitationError(400,'invalid_json','Send a JSON object.'); }
    if (!is_array($input) || !str_starts_with(ltrim($raw),'{')) throw new DriverInvitationError(400,'invalid_json','Send a JSON object.');
    // Cheap validation precedes database bootstrap; no session/cookie state is used.
    if (!is_string($input['invitation_id']??null) || !preg_match('/^[a-f0-9-]{36}$/i',$input['invitation_id'])
        || !is_string($input['token']??null) || !preg_match('/^[a-f0-9]{64}$/',$input['token'])) throw new DriverInvitationError(404,'invalid_invitation','This invitation link is invalid.');
    require_once $root.'/config/database.php';
    require_once $root.'/src/Services/AuditLogger.php';
    $invitationDb=Database::getInstance()->getPdo();
    $config=driverInvitationClaimConfig();
    $authorization=$_SERVER['HTTP_AUTHORIZATION']??$_SERVER['REDIRECT_HTTP_AUTHORIZATION']??'';
    $sessionToken=preg_match('/^Bearer (\S+)$/i',$authorization,$match)?$match[1]:'';
    [$status,$body]=(new DriverInvitationClaims($invitationDb,$config,'sendDriverInvitationEmailCode'))->handle($input,$sessionToken);
} catch (Throwable $error) {
    $known=$error instanceof DriverInvitationError;
    $status=$known?$error->httpStatus:500;
    $body=['status'=>'error','code'=>$known?$error->errorCode:'claim_failed',
        'message'=>$known?$error->getMessage():'Unable to verify the invitation. Try again.'];
    error_log('Driver invitation claim rejected: '.$body['code']);
}
http_response_code($status);
echo json_encode($body,JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
