<?php
require_once("smsCredentials.php");



function sendSMS($from,$to,$message)
{

	$user=SMSGatewayUsername; //your username
	$password=SMSGatewayPassword; //your password
	$mobilenumbers=$to; //enter Mobile numbers
	
	$message = urlencode($message);
	$sender = urlencode(SMSGatewaySenderID); //Your senderid
	
	//$path = "http://sms.vrksolutions.com/messageapi.asp?username=$user&password=$password&sender=$sender&mobile=$mobilenumbers&message=$message";
	$path = SMSGatewayAPIPath."?username=$user&password=$password&from=$sender&to=$mobilenumbers&text=$message";
	$data = @file_get_contents ($path); 
	//echo $data;
	
	/*$curlPost = "username=$user&password=$password&sender=$sender&mobile=$mobilenumbers&message=$message";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://sms.vrksolutions.com/messageapi.asp");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
	$data = curl_exec($ch);
	curl_close($ch); */

	if(empty($data))
	{
		 return 0;
	}
	else
	{
		 return $data;
	} 
}

function sendGatewaySMS($from,$to,$message,$uri,$user,$password,$sender)
{

	$mobilenumbers=$to; //enter Mobile numbers
	$message = urlencode($message);
	$sender = urlencode($sender); //Your senderid
	
	$path = $uri."?username=$user&password=$password&from=$sender&to=$mobilenumbers&text='$message'";
	$data = file_get_contents ($path); 
	//echo $data;

	if(empty($data))
	{
		 return 0;
	}
	else
	{
		 return 1;
	} 
}
function sendSMSAlert($geoAssId,$devDateTime)
{
$db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
$db->connect(); 

	$getData = "SELECT * FROM tb_assigngeofence,tb_deviceinfo,tb_geofence_info,tb_clientinfo WHERE ci_id = tag_clientId AND tgi_id = tag_geofenceId AND di_id = tag_diId AND tag_id = ".$geoAssId;
	$resData = mysql_query($getData);
	if(@mysql_affected_rows() > 0)
	{
		$fetData = mysql_fetch_assoc($resData);
		$from = "";
		$to = $fetData[tag_alertSrc];
		
		if($fetData[di_deviceName])
			$devName = $fetData[di_deviceName];
		else
			$devName = $fetData[di_deviceId];
			
		if($fetData[tag_inout] == "in")
			$status = "entered zone";
		else
			$status = "left zone";
		
		$msg = "Dear ".ucfirst($fetData[ci_clientName])."! ".$devName." has ".$status." ".$fetData[tgi_name]." at ".date("H:i:s",strtotime($devDateTime))." - ".$fetData[ci_weburl];
		//echo $msg;
		$smsres = sendSMS($from,$to,$msg);
		
		$smsdata['tsi_mobileno'] = $fetData[tag_alertSrc];
		$smsdata['tsi_tgai_id'] = $geoAssId;
		$smsdata['tsi_smsResult'] = $smsres;
		$smsdata['tsi_message'] = urlencode($msg);
		$smsdata['tsi_smsType'] = "GEOALERT";		
		//print_r($smsdata);		
		//exit;
		if($db->query_insert("tb_smsinfo", $smsdata))
			$res = 1;
		else $res = 0;
		
		return $res;
		//print_r($fetData);
	}
}
?>
