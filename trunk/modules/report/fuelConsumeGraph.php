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
}
//echo $devices_query;
//$devices_query =  "SELECT * FROM tb_deviceinfo WHERE di_clientId =".$_SESSION[clientID]." AND di_status = 1 ORDER BY di_deviceName,di_deviceId ASC";
$devices_resp = mysql_query($devices_query);	
?>

<script type="text/javascript" language="javascript">
jQuery(function() {

    //$("#time3, #time4").timePicker();
	 $("#time3, #time4").timePicker({
	  startTime: "12:01 AM", // Using string. Can take string or Date object.
	  endTime: "11:59 PM", // Using Date object here.
	  show24Hours: false,
	  separator: ':',
	  step: 1});    
        
    // Store time used by duration.
    var oldTime = $.timePicker("#time3").getTime();
    
    // Keep the duration between the two inputs.
    $("#time3").change(function() {
      if ($("#time4").val()) { // Only update when second input has a value.
        // Calculate duration.
        var duration = ($.timePicker("#time4").getTime() - oldTime);
        var time = $.timePicker("#time3").getTime();
        // Calculate and update the time in the second input.
        $.timePicker("#time4").setTime(new Date(new Date(time.getTime() + duration)));
        oldTime = time;
      }
    });
    // Validate.
    $("#time4").change(function() {
      if($.timePicker("#time3").getTime() > $.timePicker(this).getTime()) {
        $(this).addClass("error");
      }
      else {
        $(this).removeClass("error");
      }
    });
    
  });
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
function days_between(date1, date2) {

    var ONE_DAY = 1000 * 60 * 60 * 24

    var date1_ms = date1.getTime()
    var date2_ms = date2.getTime()

	var difference_ms = date1_ms - date2_ms
	    
    return Math.round(difference_ms/ONE_DAY)

}

function validateMapReport()
{
	if(document.getElementById('map_device_id').value== 0 )
	{
	alert("Select Device"); 
	document.getElementById('map_device_id').focus();
	return 0;  
	}
	
	if(document.getElementById('from_date').value=='')
	{
	alert("Select Date");
	document.getElementById('from_date').focus();
	return 0;  
	}
	var strTime = document.getElementById('time3').value.split(":"); 
	strTime1 = strTime[1].split(" ");
	strTime = (eval(strTime[0])*60)+eval(strTime1[0]);
	
	var endTime = document.getElementById('time4').value.split(":"); 
	endTime1 = endTime[1].split(" ");
	endTime  = (eval(endTime[0])*60)+eval(endTime1[0]);

	var days_diff = parseInt(endTime) - parseInt(strTime);
	
	if(days_diff > 180)
	{ 
		alert("End time should not be greater than 3 hours from start time.");
		return 0;
	}
	
	return 1;
	
}
function sendCSVData()
{
	document.frmTripData.submit();
}
$(function() {
	$( "#from_date" ).datepicker({
		changeMonth: true,
		changeYear: true,
		maxDate: 0,
		showOn: "button",
		buttonImage: "../user/images/calendar.gif",
		buttonImageOnly: true,
		dateFormat : "dd-mm-yy"
	});
});
</script>
<div id="formDiv">
<form id="frm_map_filter" name="frm_map_filter" method="post" action="" onSubmit="return validateMapReport();">      	 
<table class="gridform">
<tr><th colspan="4">Fuel Graph</th></tr>

  <tr>
    <td width="14%" align="right"><span class="form_text">Select Device</span></td>
    <td width="36%" align="left">
    <select name="map_device_id" id="map_device_id" tabindex="1" style="width:100%">
        <option value="0">Select Device</option>
        <?php 
		while($devices_fetch = @mysql_fetch_assoc($devices_resp)) 
		{
			if($devices_fetch[di_deviceName])
				$devName = $devices_fetch[di_deviceName];
			else
				$devName = $devices_fetch[di_deviceId];
        ?>
        <option value="<?php echo $devices_fetch[di_deviceId]."#".$devices_fetch[di_imeiId]; ?>" 
        <?php if($_POST[map_device_id] == $devices_fetch[di_deviceId]."#".$devices_fetch[di_imeiId]) echo "selected"; ?>><?php echo $devName; ?></option>
        <?php } ?>	
        </select>
     <input type="hidden" name="curdate" id="curdate" value="<?php echo date('Y-m-d'); ?>" />
    </td>
    <td width="14%" align="right"><span class="form_text">Date&nbsp;</span></td>
    <td width="36%" align="left">
    <input type="text" name="from_date" id="from_date" tabindex="2" readonly="true" size="12" value="<?php echo $_POST[from_date]; ?>" style="width:140px;"/></td>
  </tr>              
  <tr>
    <td align="right"><span class="form_text">Start Time &nbsp;</span></td>
    <td align="left"><div><input type="text" name="time3" id="time3" readonly="readonly" tabindex="3" size="7" value="<?php if($_POST[time3]) echo $_POST[time3]; else echo "09:00 AM";?>" /></div></td>
    
    <td  align="right"><span class="form_text">End Time &nbsp;</span></td>
    <td align="left"><div><input type="text" name="time4" id="time4" readonly="readonly" size="7" tabindex="4" value="<?php if($_POST[time4]) echo $_POST[time4]; else echo "06:00 PM";?>" /></div></td>
  </tr>
  
  <tr>
    <td height="33" colspan="4" align="center">
    <input type="button" name="map_filter_btn"   value="Filter" class="click_btn" tabindex="5"  onclick="showPreloader();"/>
    <input type="hidden" name="map_filter_btn" value="Filter" />
    <input type="button" name="map_cancel_btn" id="map_cancel_btn" value="Reset" class="click_btn" onClick="location.href='index.php?ch=altitute';" tabindex="6" /> 
    <!--<?php if(isset($_POST[map_filter_btn]) && $_POST[map_filter_btn]!='')  { ?>
    <input type="button" name="map_export_btn" id="map_export_btn" value="Export" class="click_btn" style="font-weight:bold;" onClick="sendCSVData();" /> <?php } ?>-->
    </td>
  </tr>
</table>
</form>
</div>
<div id="popup_div" style=" display:block; border:0px;" >

</div>
<?php
if(isset($_POST[map_filter_btn]) && $_POST[map_filter_btn]!='')
{
	
/*print_r($_POST);

exit;*/

?>
<?php
$altData = array();
$sdate = $_POST[from_date];
$srcData = explode("#",$_POST[map_device_id]);

$strtTime = explode(":",date("H:i",strtotime($_POST[time3])));
$strtTime = (($strtTime[0] * 60) + $strtTime[1]);

$endTime = explode(":",date("H:i",strtotime($_POST[time4])));
$endTime = (($endTime[0] * 60) + $endTime[1]);

$diffTime = ($endTime - $strtTime);
if($diffTime > 60 && $diffTime <= 720)
{
	$nextUp = 120;	
}
else if($diffTime > 720 && $diffTime <= 1440)
{
	$nextUp = 240;
}
else
{
	$nextUp = 10;
}
//echo $diffTime." ".$strtTime." ".$nextUp."<br>";

$timeArr= array();
$spdLessData =array();
$spdMidData =array();
$spdHighData =array();
$totalDistance=0;
$tripdata = "Date,Device ID,User ID,Start Point,Start Time,End Point,End Time,Duration,Avg. Speed,Dist. Covered";
$tripdata .= "@";
$ct=0;
$inc = 5;
$cailb = '';
$fuel_info_query =  "SELECT * FROM tb_deviceinfo,tb_client_subscription WHERE tcs_isActive = 1 AND tcs_deviceId = di_id AND di_imeiId =".$srcData[1]." AND di_status = 1";

$fuel_info_resp = mysql_query($fuel_info_query);	

while($fuel_info_fetch = @mysql_fetch_assoc($fuel_info_resp)) {
	$total_full_tank_size = $fuel_info_fetch[di_fulltankSize];

	$analog_full_tank_signal = $fuel_info_fetch[di_fullTanksignal];
	$analog_full_tank_signal_meas = $fuel_info_fetch[di_fullTanksignalmeasure];

   if($analog_full_tank_signal_meas=='mv'){
      $analog_full_tank_signal = $analog_full_tank_signal/1000;
   }

		$half_tank_size = $fuel_info_fetch[di_halfTankSize];

			$analog_half_tank_signal = $fuel_info_fetch[di_halfTanksignal];
			$analog_half_tank_signal_meas = $fuel_info_fetch[di_halfTanksignalmeasure];
			
	if($analog_empty_tank_signal_meas=='mv'){
      $analog_empty_tank_signal = $analog_empty_tank_signal/1000;
   }
	 $fuel_port = $fuel_info_fetch[di_Fuelport];
	
}
$avg_voltage = ($analog_full_tank_signal+$analog_half_tank_signal)/2;
 $avg_fuel_ltrs = ($total_full_tank_size+$half_tank_size)/2;

//	$file = $GLOBALS[dataPath].'src/data/'.date("d-m-Y", strtotime($sdate))."/".$srcData[1].".txt";   
	$file = 'http://localhost/gpsapp/data/'.date("d-m-Y", strtotime($sdate))."/352848025684735.txt";   
	

	
	if(chk_folder($file))
	{
		$file1 = @fopen($file, "r");
		if($file1)
		{
			while(!feof($file1))
			{
				$data= fgets($file1);
			}
			$data = getSortedData($data);
		}
		/*echo $data;
		exit;*/
		if(count($data)>0)
		{
		 $data1=explode("#",$data);
		 

		
		 //echo count($data1); 
		 for($j1=0;$j1<count($data1); $j1++)					
		 {
			 		// $intervals = count($data1)*0.1;
				$data2=explode("@",$data1[$j1]);
				//echo '<pre>'; print_r($data2);echo '</pre>';
				if(count($data2)>0)
				{
					$data3=explode(",",$data2[1]);
					  $fule_param = $data3[7]; 
					  $d =preg_split("/[\s,]+/",$fule_param);
					  foreach($d as $e){
						 // echo substr($e,0,3).'<br>';
                          if(substr($e,0,3)=='['.$fuel_port.'='){
                            $v= str_replace(array('['.$fuel_port.'=',']'),'',$e);
						  }
						  
					  }
					
                  


		if(strlen($v)>2 && $v!='0'){
		 $calib = ($avg_voltage*$avg_fuel_ltrs)/($v/1000);
		}/* else if($v!='0'){
		 $calib = ($avg_voltage*$avg_fuel_ltrs)/$v;
		}*/
		
					$vehi=$data3[0];
					$geodate = $data3[8]." ".$data3[9];
					$geoTime = date("h:i",strtotime($data3[9]));
					$curTime = explode(":",$data3[9]);
					$curTime = (($curTime[0]) * 60);

					// $strtTime." ".$curTime." ".$endTime." ".$data3[3]."<br>";				
					

							if(strlen($v)>2 && $v!='0'){

						
							$altData [] = ceil($calib);
							$spdTime [] = "'".date("H:i A",strtotime($data3[9]))."'";
							$cnt++; 	
							array_push($timeArr,$geoTime);
						
					}
				
			 }
			// $j1 = $j1+$intervals;
		}
		fclose($file1);
		}
	}


	if(count($altData)>0)
	{
		$spdTime = implode(",",$spdTime);
?>	  

<script type="text/javascript">
showFormDiv();
hidePreLoader();
</script>
<table class="gridform">      
  <tr>
    <th width="85%">Fuel Graph</th>
    <td><a href="#" id="shLink" onClick="showFormDiv();">Show Form</a></td>
</tr>	

<script type="text/javascript">

 var chart;
 $(document).ready(function() {
	chart = new Highcharts.Chart({
	chart: {
		renderTo: 'container',
		defaultSeriesType: 'column',
		margin: [ 50, 50, 100, 80]
	},
	title: {
		text: 'Fuel Graph - <?php echo $srcData[0]." (".$_POST[from_date].")";?>', margin: 80
	},
	xAxis: { 

		categories: [<?php echo $spdTime;?>],
		labels: {
			rotation: -90,
			align: 'right',
			style: {
				 font: 'normal 11px Verdana, sans-serif'
			}
		}
				
	},
	yAxis: {
		min: 0,
		title: {
			text: 'Fuel in ltrs'
		},
	},
	legend: {
		enabled: false
	},
	tooltip: {
		formatter: function() {
			return 'Time: '+ this.x +' Fuel: '+ this.y;
		}
	},
		series: [{
		type: 'area',
		name: 'Population',
		data: [<?php echo join($altData, ',');?>]
		}]
});


 });

</script>
<tr>
	<td colspan="2"><div id="container" style="width: 800px; height: 500px; margin: 0 auto"></div></td>
</tr>
<?php
  }
  else
  {
  	echo "<tr><td colspan=2>No Data Found</td></tr>";
  }
}///end of post

?> 
</table>