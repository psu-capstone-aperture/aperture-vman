<?php
// Get user type
$userType = 'volunteer';

// Query retrieving user ID, first name, last name of volunteers
$result = db_query('SELECT f.field_first_name_value, l.field_last_name_value, u.name FROM {role} r, {users_roles} ur, {field_data_field_first_name} f, {field_data_field_last_name} l, {users} u WHERE u.uid = ur.uid AND f.entity_id = ur.uid AND l.entity_id = ur.uid AND ur.rid = r.rid AND r.name = :userType', array(':userType' => $userType));
echo "<br />";
// Output
foreach ($result as $record) {
	$userName = $record->name;
	$firstName = $record->field_first_name_value;
	$lastName = $record->field_last_name_value;
	echo '<a href="/drupal2/users/' . $userName . '/">' . $firstName . ' ' . $lastName . '</a>';
	echo "<br /><br />";
}
