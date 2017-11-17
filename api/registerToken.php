<?php
require 'config.php';
$encoded_data = file_get_contents('php://input');
$data = $_GET;
global $wpdb;
$error = 1;
if (empty($data['deviceToken'])) {
    $error = 0;
}
if (empty($data['userId'])) {
    $error = 0;
}
if(!empty($error)){
    $checkAuthorisarion = AuthUser($data['userId'], 'string');    
    update_user_meta($data['userId'], "deviceToken", $data['deviceToken']); 
    response(1,"1", 'No Error Found.');    
 }else{
     response(0, null, 'Please enter the required fields.');
}
?>