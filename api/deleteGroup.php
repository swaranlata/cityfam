<?php

require 'config.php';
$data = $_GET;
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
if (empty($data['groupId'])) {
    $error = 0;
}
if (!empty($error)) {
    $checkAuthorisarion = AuthUser($data['userId'], 'string');
    $results = $wpdb->get_results('select * from `wp_groups` where `userID`="' . $data['userId'] . '" and `id`="' . $data['groupId'] . '"');
    if (!empty($results)) {
        $wpdb->query('delete from `wp_groups` where `userID`="' . $data['userId'] . '" and `id`="' . $data['groupId'] . '"');
        $group_id = $data['groupId'];
		$wpdb->query('delete from `wp_conversations` where `groupId`="' . $data['groupId'] . '"');
        $group_id = $data['groupId'];
        response(1, "$group_id", 'Group deleted successfully.');
    } else {
        response(0,null, 'Group not exists.');
    }
} else {
    response(0, null, 'Please enter required fields.');
}
?>