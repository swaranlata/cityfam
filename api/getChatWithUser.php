<?php
require 'config.php';
$data = $_GET;
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
if (empty($data['opponentUserId'])) {
    $error = 0;
}
if ($data['offset']=='') {
    $error = 0;
}
if(!empty($error)){
    $checkAuthorisarion = AuthUser($data['userId'], 'array');
    $offset=20;
    $limit=20;
    if ($data['offset']==0 || !empty($data['offset']) and  $data['offset']!='') {
        $offset=20*$data['offset'];
        $limit=20;
    }
    $query='select * from `wp_conversations` where (`userId`="'.$data['userId'].'" and `opponentUserId`="'.$data['opponentUserId'].'" and `type`="1") or (`opponentUserId`="'.$data['userId'].'" and `userId`="'.$data['opponentUserId'].'"  and `type`="1") order by id  desc limit '.$offset.','.$limit;
    $getChat = $wpdb->get_results($query);
    if (!empty($getChat)) {
        $getChat = convert_array($getChat);
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
    
 }else{
  response(0, array(), 'Please enter required fields.');  
}
?>