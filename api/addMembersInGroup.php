<?php
require 'config.php';
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
if (empty($data['groupId'])) {
    $error = 0;
}
if (empty($data['emailIdArray'])) {
    $error = 0;
}
if (!empty($error)) {
    $checkAuthorisarion = AuthUser($data['userId'], 'string');
    $alreadyEmails=array();
    foreach ($data['emailIdArray'] as $key => $value) {
       $getResults=$wpdb->get_results('select * from `wp_group_members` where  `email`="'.$value.'" and `groupId`="'.$data['groupId'].'"'); 
       if(empty($getResults)){
          $wpdb->insert('wp_group_members', array(
           'userID' => $data['userId'],
           'groupId' => $data['groupId'], 
           'email' => $value, 
           'created' => date('Y-m-d h:i:s')));  
       }else{
           $alreadyEmails[]=$value;
       }       
    }  
    $already=count($alreadyEmails);
    $emailAlready=count($data['emailIdArray']);
    if($already==$emailAlready){
       response(0, null, 'Members are already added in the group.'); 
    }else{
       response(1, "members added successfully", 'No Error Found');  
    }
   
} else {
    response(0, null, 'Please enter required fields.');
}
?>