<?php

require 'config.php';
$data = $_REQUEST;
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
if (empty($data['anotherUserId'])) {
    $error = 0;
}
$testStatus=2;
if(isset($data['status']) and $data['status']=='0' || $data['status']=='1'){
   $testStatus=$data['status'];
}
$status=array(0,1);
if (!in_array($testStatus,$status)) {
    $error = 0;
}
if (empty($error)) {
    response(0, null, 'Please enter required fields.');
} else {
    $checkAuthorisarion = AuthUser($data['userId'],'string');
    if($data['userId']==$data['anotherUserId']){
      response(0, null, 'You cant send friend request to yourself.');  
    }
    if ($data['status'] == 1) {
        $result = $wpdb->query('select * from `wp_friends` where (`user_id`= "' . $data['userId'] . '" and `friend_id`="' . $data['anotherUserId'] . '"  and `status`="1") or (`user_id`= "' . $data['anotherUserId'] . '" and `friend_id`="' . $data['userId'] . '"  and `status`="1")');
        if (!empty($result)) {
            $query = 'DELETE  FROM `wp_friends` WHERE (`user_id`= "' . $data['userId'] . '" and `friend_id`="' . $data['anotherUserId'] . '") or (`user_id`= "' . $data['anotherUserId'] . '" and `friend_id`="' . $data['userId'] . '")';
            $wpdb->query($query);
            response(1, 1, 'No Error Found.');           
        } else {
            response(0,null,'User is not in friendlist.');
        }
    } else {
        $result = $wpdb->query('select * from `wp_friends` where (`user_id`= "' . $data['userId'] . '" and `friend_id`="' . $data['anotherUserId'] . '") or (`user_id`= "' . $data['anotherUserId'] . '" and `friend_id`="' . $data['userId'] . '") and `status`="0"');
        if (!empty($result)) {
            response(0, null, 'Friend Request already sent.');
        } else {
            $wpdb->insert('wp_friends', array(
                'user_id' => $data['userId'],
                'friend_id' => $data['anotherUserId'],
                'status' => '0'
            ));
            response(1,1, 'No Error Found.');
        }
    }
}
?>