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
if (empty($data['emailId'])) {
    $error = 0;
}
if (!empty($error)) {
    $checkAuthorisarion = AuthUser($data['userId'], 'string');
    $results = $wpdb->get_results('select * from `wp_group_members` where `userID`="' . $data['userId'] . '" and `groupId`="' . $data['groupId'] . '" and `email`="'.$data['emailId'].'"');
    if (!empty($results)) {
        $results=convert_array($results);
        $id=$results[0]['id'];
        $email=$data['emailId'];
        $wpdb->query('delete from `wp_group_members` where `id`="' .$id. '"');
        response(1, "$email", 'No Error Found');
    } else {
        response(0, null, 'No Data Found.');
    }
} else {
    response(0, null, 'Please enter required fields.');
}
?>