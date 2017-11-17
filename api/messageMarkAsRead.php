<?php
require 'config.php';
$data = $_GET;
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
$typeArray = array(0, 1, 2);
if (!in_array($data['type'], $typeArray)) {
    $error = 0;
}
if ($data['type'] == 1) {
    if (empty($data['opponentUserId'])) {
        $error = 0;
    }
}
if($data['type'] == 0){
    if(empty($data['groupId'])){
        $error = 0;
    }
}
if (!empty($error)) {
    $checkAuthorisarion = AuthUser($data['userId'], 'string');
    if($data['type']==1){//private
         $query='select * from `wp_conversations` where `opponentUserId`="'.$data['userId'].'" and `userId`="'.$data['opponentUserId'].'"  and `type`="1"';
    }elseif($data['type']==2){//public
        $query='select * from `wp_conversations` where `opponentUserId`="'.$data['userId'].'" and `type`="2"';
    }else{//friends
         $query='select * from `wp_conversations` where `opponentUserId`="'.$data['userId'].'" and `groupId`="'.$data['groupId'].'" and `type`="0"';
    }    
    $getChat = $wpdb->get_results($query);
    if(!empty($getChat)){
       $getChat=convert_array($getChat); 
       foreach ($getChat as $key => $value) {
          $wpdb->query('update `wp_conversations` set `seen`="1" where `id`="'.$value['id'].'" '); 
       }
       response(1,'message successfully read marked', 'No Error Found.'); 
    }else{
       response(1,'message successfully read marked', 'No Error Found.'); 
    }    
} else {
    response(0, null, 'Please enter required fields.');
}
?>