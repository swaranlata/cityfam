<?php
require 'config.php';
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
if (empty($data['eventId'])) {
    $error = 0;
}
if (empty($data['comment'])) {
    $error = 0;
}
if (!empty($error)) {
    $checkAuthorisarion = AuthUser($data['userId'], 'string');
    $getAllevents=getAllEvents();
    if(!in_array($data['eventId'],$getAllevents)){
      response(0, null, 'Event not found.');   
    }
    $getUserRole = get_user_meta($data['userId'], 'wp_capabilities', true);
    $user_type = '';
    if (!empty($getUserRole)) {
        foreach ($getUserRole as $key => $value) {
            $user_type = $key;
        }
    }
    $time = current_time('mysql');
    $get_post=convert_array(get_post($data['eventId']));
    $comment_data = array(
        'comment_post_ID' => $data['eventId'],
        'comment_author' => $user_type,
        'comment_author_email' => $checkAuthorisarion[0]['user_email'],
        'comment_author_url' => $checkAuthorisarion[0]['user_url'],
        'comment_content' => $data['comment'],
        'comment_type' => '',
        'comment_parent' => 0,
        'user_id' => $data['userId'],
        'comment_date' => $time,
        'comment_approved' => 1,
    );
    wp_insert_comment($comment_data);
    $checkNotificationStatus=getNotificationStatusByUserId($get_post['post_author'],5);
    if(!empty($checkNotificationStatus)){
       $activity=activity($data['userId'],'comment',$data['eventId'],'');
       pushMessageNotification($get_post['post_author'],ucwords($checkAuthorisarion[0]['display_name'])." posted a comment on your event.");   
    }  
    response(1, 1, 'No Error Found');
} else {
    response(0, null, 'Please enter required fields.');
}
?>