<?php

require 'config.php';
$data = $_GET;
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
if(!isset($data['groupId'])){
   $error = 0;  
}
$typeArray = array(0,2);
if (!in_array($data['type'], $typeArray)) {
    $error = 0;
}
if ($data['type'] == 0) {
    if (empty($data['groupId'])) {
        $error = 0;
    }
}
if ($data['offset']<0 || $data['offset']=='') 
{
    $error = 0;
}

if (!empty($error)) {
    $offset=20;
    $limit=20;
    if ($data['offset']==0 || !empty($data['offset']) and  $data['offset']!='') {
        $offset=20*$data['offset'];
        $limit=20;
    }
    $checkAuthorisarion = AuthUser($data['userId'], 'array');
    if ($data['type'] ==2) {//public
        $type = '2';  
        $qryy='select * from `wp_conversations` where `type`="' . $type . '" and `groupId`="0" group by created order by id desc limit '.$offset.','.$limit;
        $getChat = $wpdb->get_results($qryy);    
    } else {//friends
        $type = '0';
        $qryy='select * from `wp_group_members` where `groupId`="' . $data['groupId'] . '" and `email`="'.$checkAuthorisarion[0]['user_email'].'" order by id desc';
        $getGroupMembers = $wpdb->get_results($qryy);
        $getChat=array();
        if(!empty($getGroupMembers)){
            $getChat = $wpdb->get_results('select * from `wp_conversations` where `type`="' . $type . '" and `groupId`="'.$data['groupId'].'" group by created order by id  desc  limit '.$offset.','.$limit); 
        }else{
            if($data['offset']>0){
              response(0, array(), 'No Conversation Found.');
            }else{
              response(0, array(), 'You are not authorise to read this message.');  
            }
             
        }        
    }
    if (!empty($getChat)) {
        $getChat = convert_array($getChat);
        // krsort($getChat);
        $chat = array();
        foreach ($getChat as $key => $value) {
            $userPostedBy = convert_array(get_user_by('id', $value['userId']));
            $image = get_user_meta($value['userId'], 'user_image', true);
            $user_image = '';
            if (!empty($image)) {
                $user_image = get_post_field('guid', $image);
            }
            $chat[$key]['chatUserImageUrl'] = $user_image;
            $chat[$key]['chatMessage'] = $value['message'];
            $chat[$key]['chatUserId'] = $userPostedBy['data']['ID'];
        }
        if (!empty($chat)) {
            response(1, $chat, 'No Error Found');
        } else {
            response(0, array(), 'No Conversation Found.');
        }
    } else {
        response(0, array(), 'No Conversation Found.');
    }
  
} else {
    response(0, array(), 'Please enter required fields.');
}
?>