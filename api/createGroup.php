<?php

require 'config.php';
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
if (empty($data['groupName'])) {
    $error = 0;
}
$array=array(0,1);
if($data['type']!=''){
	if(!in_array($data['type'],$array)){
		$error = 0;
	}	
}else{
	$error = 0;
}
if (!empty($error)) {
    $checkAuthorisarion = AuthUser($data['userId'], 'string');
    $wpdb->insert('wp_groups', array('userID' => $data['userId'], 'groupName' => $data['groupName'],  'group_type' => $data['type'],'created' => date('Y-m-d h:i:s')));
    $group_id = $wpdb->insert_id;
    if($data['type']==1){
        $wpdb->insert('wp_group_members', array(
               'userID' => $data['userId'],
               'groupId' => $group_id, 
               'email' => $checkAuthorisarion[0]['user_email'], 
               'created' => date('Y-m-d h:i:s')));      
    }
    response(1, "$group_id", 'No Error Found.');
} else {
    response(0, null, 'Please enter required fields.');
}
?>