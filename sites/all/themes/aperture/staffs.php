<?php
// Retrieve the user ID from table role in the database
$userType = 'staff'; // In this case is volunteer

// Retrieve the role ID from table role
$result = db_query('SELECT r.rid FROM {role} r WHERE r.name = :userType', array(':userType' => $userType));

// Get the role ID store into $roleID
foreach ($result as $record) {
	$roleID = $record->rid;
}

// Retrieve user ID matching the role ID
$query = db_select('users_roles', 'ur');
// Select the field uid in table ur
$query
	->fields('ur', array('uid'))
	->condition('ur.rid', $roleID, '=');
$result = $query->execute();
// And store results into an array
$arrayID = array();
foreach($result as $record) {
	$arrayID[] = $record->uid;
}

// Count the number of elements in the array
$count = count($arrayID);

// Find all the first names that matched with ID's found above
$firstNameSelect = db_select('field_data_field_first_name', 'f');
$firstNameSelect
	->fields('f', array('entity_id', 'field_first_name_value'))
	->condition('f.entity_id', $arrayID, "IN");

// Join with last names and order them alphabetically
$firstNameSelect->join('field_data_field_last_name', 'l', 'f.entity_id = l.entity_id');
$firstNameSelect
	->fields('f', array('field_first_name_value', 'entity_id'))
	->fields('l', array('field_last_name_value'))
	->orderBy('field_last_name_value', 'ASC');
$result = $firstNameSelect->execute();
foreach($result as $record) {
	$userID = $record->entity_id;
	$firstName = $record->field_first_name_value;
	$lastName = $record->field_last_name_value;
	echo '<a href="?q=user/' . $userID . '/">' . $firstName . ' ' . $lastName . '</a>';
	echo "<br /><br />";
}

if ($count > 1) {
	echo "Total Staff Members: $count<br />";
}
