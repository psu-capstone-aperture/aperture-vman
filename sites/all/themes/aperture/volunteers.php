<?php
// Get user type
$userType = 'volunteer';

// Page Title
echo "<p class=\"vol-list-title\">VOLUNTEER LIST</p>";
// Create tables for the volunteer lists
echo "<table class=\"vol-list\" align=\"center\" border=\"0\">
	<tr id=\"head-vol-list\">
		<td class=\"list-nums-head\">#</td>
		<td class=\"list-name-col-left\">Full Name</td>
		<td  class=\"list-name-col-right\">Username</td>
	</tr> ";

// Query retrieving user ID, first name, last name of volunteers
$result = db_query('SELECT f.field_first_name_value, l.field_last_name_value, u.name FROM {role} r, {users_roles} ur, {field_data_field_first_name} f, {field_data_field_last_name} l, {users} u WHERE u.uid = ur.uid AND f.entity_id = ur.uid AND l.entity_id = ur.uid AND ur.rid = r.rid AND r.name = :userType ORDER BY l.field_last_name_value ASC', array(':userType' => $userType));
$count = 0;

// Output
foreach ($result as $record) {
	$userName = $record->name;
	$firstName = $record->field_first_name_value;
	$lastName = $record->field_last_name_value;
	++$count;
	echo "<tr><td class=\"list-nums\">$count</td><td class=\"list-name-col-left\">$firstName $lastName</td><td><a href=\"../users/$userName/\">$userName</a></td></tr>";
}

// Close the table
echo "</table>";

// Total volunteer members
echo "<p class=\"vol-list-count\" align=\"right\">Total Volunteer Members: $count</p>";
