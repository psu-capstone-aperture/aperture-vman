<?php
function runReport($startDate, $endDate, $isCheckboxSet) {
	
	$offset = timeOffsetFromGMTToPST();
	$userCount = 0;
	
	echo "<table class=\"gen-report\" align=\"center\" border=\"0\">
	<tr id=\"head-report\">
		<td class=\"report-nums-head\">#</td>
		<td class=\"report-name-col-left\">Name</td>
		<td>Username</td>
		<td  class=\"report-name-col-right\">Total Time</td>
	</tr> ";

	/* RETREIVE DATA FROM DATABASE SORTED BY LASTNAME */
	$calEvents = db_query('SELECT u.name, u.uid, first.field_first_name_value, first.entity_id, last.field_last_name_value, last.entity_id, n.nid, n.type, n.uid, fd.entity_id, fd.field_event_date_value, fd.field_event_date_value2
FROM users u INNER JOIN field_data_field_first_name first INNER JOIN field_data_field_last_name last INNER JOIN node n INNER JOIN field_data_field_event_date fd
ON u.uid = last.entity_id AND u.uid = first.entity_id AND n.uid = u.uid AND fd.entity_id = n.nid
ORDER BY first.field_first_name_value');

	//define a collection
	$calCollection = array( array());

	//convert startDate/endDate to Unix timestamp
	$start_date = strtotime($startDate);
	$end_date = strtotime($endDate);

	foreach ($calEvents as $calEvent) {

		//get start/end unix time and adjust to pacific timezone
		$start_time = strtotime($calEvent -> field_event_date_value) - $offset;
		$end_time = strtotime($calEvent -> field_event_date_value2) - $offset;
		// number of hours in unix format
		$volHours = abs($end_time - $start_time);

		//Get only events between the dates
		if ($start_date <= $start_time AND $end_date >= $end_time) {
			if (array_key_exists($calEvent -> uid, $calCollection)) {
				$calCollection[$calEvent -> uid]['volHours'] = $calCollection[$calEvent -> uid]['volHours'] + $volHours;
			} else {
				$calCollection[$calEvent -> uid] = array('name' => $calEvent -> name, 'uid' => $calEvent -> uid, 'firstName' => $calEvent -> field_first_name_value, 'lastName' => $calEvent -> field_last_name_value, 'volHours' => $volHours);
			}
		}
	}
	$saveToCSVArray = array();
	$saveToCSVCollection = array();
	foreach ($calCollection as $arrayWithData) {
		if ($arrayWithData == NULL) {
			continue;
		}
		++$userCount;
		$userName = $arrayWithData['name'];
		$firstName = $arrayWithData['firstName'];
		$lastName = $arrayWithData['lastName'];
		$totalTime = convertUnixTimeToHoursMinutes($arrayWithData['volHours']);
		//create cells and display data
		echo "<tr><td class=\"report-nums\">$userCount</td><td class=\"report-name-col-left\">$firstName $lastName</td><td>$userName</td><td>$totalTime</td></tr>";
		if ($isCheckboxSet == 1) {
			#save data into 2d array
			$saveToCSVArray = array($firstName, $lastName, $userName, $totalTime);
			array_push($saveToCSVCollection, $saveToCSVArray);
		}
	}
	echo "</table>";
	echo "<div class=\"small\"><span>*</span> the report is based on the calendar data</div>";

	if ($isCheckboxSet == 1) {
		exportToCSV($saveToCSVCollection);
		include ("download-report.php");
	}

}

function exportToCSV($list) {
	array_unshift($list, array("First Name", "Last Name", "Username", "Total Hours"));
	$fp = fopen('report.csv', 'w');
	foreach ($list as $fields) {
		fputcsv($fp, $fields);
	}
	fclose($fp);
}

function timeOffsetFromGMTToPST() {
	return abs(strtotime(gmdate("M d Y H:i", time())) - strtotime(date('M d Y H:i', time())));
}

function convertUnixTimeToHoursMinutes($unixTime) {
	$hours = floor($unixTime / 3600);
	$minutes = floor(($unixTime / 60) % 60);
	return "$hours hrs $minutes mins";
}
?>

<!-- SELECT START/END DATE FORM -->
<div class="run-report-wrapper">
<form name="get_date" action="" method="post" autocomplete="off">
<input id="from-date" type="text" name="startDate" placeholder="Start Date" required />
<input id="to-date"type="text" name="endDate" placeholder="End Date" required />
<button class="form-submit" type="submit" name="submit" value="GO" >GO</button>
<input class="saveToScv" type="checkbox" name="saveToCSV" value="1" /> <div class="checkboxText">Save to .CSV</div>
</form></div>
<!-- END FORM -->

<?php
/* --- GET DATES, RUN REPORT ---- */
if (isset($_POST["submit"])) {
	if (!$_POST['startDate']) {
		echo "<div class=\"error-label-start\"><span style=\"color:red\">*</span>Start date required</div>";
	} 
	if (!$_POST['endDate']) {
		echo "<div class=\"error-label-end\"><span style=\"color:red\">*</span>End date required</div>";
	} 
	
	// check status of checkbox
	$isCheckboxSet = (isset($_POST["saveToCSV"])) ? 1 : 0;
	if ($_POST['startDate'] AND $_POST['endDate']) {	
		runReport($_POST["startDate"], $_POST["endDate"], $isCheckboxSet);
	}
}
?>

<div style="height: 5px;"></div>

<?php
/* --- INCLUDE SCRIPTS FOR DATE PICKER --- */
include ("sites/all/themes/aperture/datepicker/loads_scripts.php");
?>

<?php
/* --- INCLUDE SCRIPTS FOR FORM PERSISTANCE --- */
include ("sites/all/themes/aperture/js/save-form-fields/form-persistance.php");
?>