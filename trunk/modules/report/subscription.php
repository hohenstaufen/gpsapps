<style type="text/css" title="currentStyle">
	@import "../media/css/demo_table.css";
	@import "media/css/TableTools.css";
</style>

<script type="text/javascript" charset="utf-8" src="../media/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf-8" src="media/ZeroClipboard/ZeroClipboard.js"></script>
<script type="text/javascript" charset="utf-8" src="media/js/TableTools.js"></script>
<script type="text/javascript" charset="utf-8">
	$(document).ready( function () {
		/* You might need to set the sSwfPath! Something like:
		 *   TableToolsInit.sSwfPath = "/media/swf/ZeroClipboard.swf";
		 */
		$('#example').dataTable( {
			"sDom": 'T<"clear">lfrtip'
		} );
	} );


	function showFormDiv()
{
	if(document.getElementById('formDiv').style.display=='none')
	{
		document.getElementById('formDiv').style.display = 'block';
		document.getElementById('shLink').innerHTML = "Hide Form";
	}
	else
	{
		document.getElementById('formDiv').style.display = 'none';
		if(document.getElementById('shLink'))
		document.getElementById('shLink').innerHTML = "Show Form";
	}
}
function showPreloader()
{
	var returnVal = validateMapReport()
	if(returnVal == 1)
	{
		document.getElementById('popup_div').innerHTML = '<div id="loading_txt" >Loading...</div>';
		document.frm_map_filter.submit();
	}
}

function hidePreLoader()
{
	document.getElementById('popup_div').innerHTML = '&nbsp;';
}
</script>
<?php
@set_time_limit(0);

function chk_folder($filename)
{
	$fp_load = @fopen("$filename", "rb");
	if ( $fp_load )
	{
		return true;
	}
	else
	{
		return false;
	}
}

if($recordUserInfo[ci_clientType] == "Client" && $recordUserInfo[ui_isAdmin] == "1")
{
	$devices_query =  "SELECT * FROM tb_deviceinfo,tb_client_subscription WHERE tcs_isActive = 1 AND tcs_deviceId = di_id AND di_clientId =".$_SESSION[clientID]." AND di_status = 1 ORDER BY di_deviceName,di_deviceId ASC";
}
else if($recordUserInfo[ci_clientType] == "Client" && $recordUserInfo[ui_isAdmin] == "0" && $recordUserInfo[ui_roleId] == "1")
{
	$devices_query = "SELECT * FROM tb_deviceinfo,tb_client_subscription WHERE tcs_isActive = 1 AND tcs_deviceId = di_id AND di_status = 1 AND di_clientId=".$_SESSION[clientID]." AND di_assignedUserId = ".$_SESSION[userID]." ORDER BY di_deviceName,di_deviceId ASC";
} else if($recordUserInfo[ci_clientType] == "Reseller")
	{ //echo '<pre>'; print_r($_SESSION);echo '</pre>';
	$devices_query = "SELECT * FROM tb_deviceinfo, tb_clientinfo WHERE di_status =1 AND ci_clientId =".$_SESSION[userID]." ORDER BY ci_clientName, di_deviceName, di_deviceId ASC";
	}

$devices_resp = mysql_query($devices_query);

?>

<script type="text/javascript" language="javascript">


function validateMapReport()
{
	if(document.getElementById('map_device_id').value== 0 )
	{
	alert("Select Device"); 
	document.getElementById('map_device_id').focus();
	return false;
	} 

	return true;
}


</script>
<div id="formDiv">
<form id="frm_map_filter" name="frm_map_filter" method="post" action="" onSubmit="return validateMapReport();">      	 
<table class="gridform">
<tr><th colspan="4">Subscription Report</th></tr>

  <tr>
    <td width="14%" align="right"><span class="form_text">Select Device</span></td>
    <td width="26%" align="left">
    <select name="map_device_id[]" id="map_device_id" size="5" tabindex="1" style="width:100%" multiple>
        <option value="0">Select Device</option>
        <?php 
		while($devices_fetch = @mysql_fetch_assoc($devices_resp)) 
		{
			if($devices_fetch[di_deviceName])
				$devName = $devices_fetch[di_deviceName];
			else
				$devName = $devices_fetch[di_deviceId];

$x = trim($devName."#".$devices_fetch[di_id]."#".$devices_fetch[di_mobileNo]);?>
       
		<option value="<?php echo $x;?>"
        <?php echo '>'.$devName; 
		echo '</option>';
         } ?>	
        </select>
    </td>
   
  </tr>              
 

  <tr>
    <td height="33" colspan="4" align="center">
    <input type="submit" name="map_filter_btn"   value="Filter" class="click_btn" tabindex="5" />
    <input type="button" name="map_cancel_btn" id="map_cancel_btn" value="Reset" class="click_btn" onClick="location.href='index.php?ch=subscription_report';" tabindex="6" /> 
   
    </td>
  </tr>

 
</table>
</form>
</div>
<div id="popup_div" style=" display:block; border:0px;" >

</div>

<?php
if(isset($_POST[map_filter_btn]) && $_POST[map_filter_btn]!=""){?>

<script type="text/javascript">
showFormDiv();
hidePreLoader();
</script>
<div style="overflow:scroll; overflow-X:hidden;  border:1px solid #dfe9ed; border-top:0px solid #FFF;">
<div class="listofusers" align="right" style="padding-right:10%"><a href="#" class="error_strings" id="shLink" onClick="showFormDiv();">Show Form</a></div>

<?php
// echo '<pre>';print_r($_POST); echo '</pre>';

//foreach($_POST[map_device_id] as $map_dv_id){
for($i=0;$i<=count($_POST[map_device_id]);$i++){
	if($_POST[map_device_id][$i]!=""){
    $dev_id = explode('#',$_POST[map_device_id][$i]);
    $device_id[] = $dev_id[1];
	}

}
//print_r($device_id); echo '<br>';
 $all_dev = implode("','",$device_id);

   $devSubInfo = "select tcs_deviceId,tcs_clientId,tcs_renewalDateFrom from tb_client_subscription where tcs_deviceId in ('".$all_dev."')";
  $devSubresp = mysql_query($devSubInfo);
  ?>
<table cellspacing="0" cellpadding="0" border="0" id="example" class="gridform_final">
<thead>
<tr>
  <th class="sorting_asc">Sno</th>
  <th class="sorting_asc">Device Name</th>
  <th class="sorting_asc">Client Name</th>
  <th class="sorting_asc">Client Mobile Number</th>
  <th class="sorting_asc">Device Mobile Number</th>
  <th class="sorting_asc">Expiry Date</th>
  </tr>
  </thead>
  <?php
	  $i=1;
  while($devsubResult = mysql_fetch_array($devSubresp)){

   $query = mysql_query("select ui_username,ui_mobile from tb_userinfo where ui_id='".$devsubResult[tcs_clientId]."'");
   $result = mysql_fetch_row($query);
    // echo '<pre>'; print_r($dev_id);echo '</pre>';

	$dev_id1 = explode('#',$_POST[map_device_id][$i-1]);
    //$device_id[] = $dev_id[1];
 if($dev_id1[2]!=""){
	  echo '<tr>';
  	  echo '<td>'.$i.'</td>';
  	  echo '<td>'.$dev_id1[0].'</td>';
 	  echo '<td>'.$result[0].'</td>';
	  echo '<td>'.$result[1].'</td>';
	  echo '<td>'.$dev_id1[2].'</td>';
	  echo '<td>'.$devsubResult[tcs_renewalDateFrom].'</td>';
	  echo '</tr>';
	  $i++;
 }
  }?>

</table>
<?php } ?>