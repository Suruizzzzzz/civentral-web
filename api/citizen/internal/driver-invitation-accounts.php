<?php
declare(strict_types=1);
use App\Services\DriverInvitationError;
ini_set('display_errors','0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$root=dirname(__DIR__,3);
require_once $root.'/src/Services/DriverInvitations.php';
require_once $root.'/config/driver-invitations.php';
try {
    if(driverInvitationSetting('CIVENTRAL_DRIVER_INVITATIONS_ENABLED')!=='true') throw new DriverInvitationError(503,'integration_disabled','Driver invitations are not enabled.');
    $expected=driverInvitationSetting('CIVENTRAL_TRANSPORT_INVITATION_SERVICE_KEY');
    $key=(string)($_SERVER['HTTP_X_INTERNAL_SERVICE_KEY']??'');
    if($expected==='')throw new DriverInvitationError(503,'integration_unconfigured','Invitation authentication is not configured.');
    if($key===''||!hash_equals($expected,$key))throw new DriverInvitationError(401,'service_authentication_required','Service authentication required.');
    if(($_SERVER['REQUEST_METHOD']??'GET')!=='GET')throw new DriverInvitationError(405,'method_not_allowed','Use GET.');
    $citizen=filter_var($_GET['citizen_user_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
    $page=filter_var($_GET['page']??1,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>10000]]);
    if($citizen===false||$page===false)throw new DriverInvitationError(422,'invalid_reference','Supply a valid citizen ID and page.');
    require_once $root.'/config/database.php';
    $pdo=Database::getInstance()->getPdo();
    $account=$pdo->prepare("SELECT citizen_user_id FROM citizen_users WHERE citizen_user_id=:id AND status='Active' AND deleted_at IS NULL");
    $account->execute(['id'=>$citizen]);
    if($account->fetchColumn()===false)throw new DriverInvitationError(403,'account_unavailable','This citizen account is not available.');
    $query=$pdo->prepare('SELECT id invitation_id,source_request_id,driver_reference,approved_at,citizen_user_id,claim_status,delivery_status,expires_at,verified_at,terms_version,privacy_version,consent_at FROM citizen_driver_invitations WHERE citizen_user_id=:citizen ORDER BY approved_at DESC,id LIMIT 101 OFFSET '.(($page-1)*100));
    $query->execute(['citizen'=>$citizen]);$items=$query->fetchAll(PDO::FETCH_ASSOC);
    $hasNext=count($items)>100;$items=array_slice($items,0,100);
    foreach($items as &$item){$item['citizen_user_id']=(int)$item['citizen_user_id'];if($item['claim_status']==='pending'&&strtotime($item['expires_at'].' UTC')<=time())$item['claim_status']='expired';}unset($item);
    $status=200;$body=['status'=>'success','data'=>['items'=>$items,'pagination'=>['page'=>$page,'per_page'=>100,'has_next'=>$hasNext]]];
}catch(Throwable $error){$known=$error instanceof DriverInvitationError;$status=$known?$error->httpStatus:500;$body=['status'=>'error','code'=>$known?$error->errorCode:'invitation_lookup_failed','message'=>$known?$error->getMessage():'Unable to retrieve driver invitation status.'];}
http_response_code($status);echo json_encode($body,JSON_THROW_ON_ERROR);
