<?php
require 'config.php';
$data = $_GET;
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
if ($data['offset']<0 || $data['offset']=='') 
{
    $error = 0;
}
if(!empty($error)){
    $checkAuthorisarion = AuthUser($data['userId'], 'array');
    $query='select * from `wp_conversations` where (`type`="1" and `userId`="'.$data['userId'].'") or (`type`="1" and `opponentUserId`="'.$data['userId'].'") order by id desc';
    $getChat = $wpdb->get_results($query);
    $usersArray=array();
    $offset=20;
    $limit=20;
    if ($data['offset']==0 || !empty($data['offset']) and  $data['offset']!='') {
        $offset=20*$data['offset'];
        $limit=20;
    }
    if (!empty($getChat)) {
        //krsort($getChat);
        $getChat = convert_array($getChat);  
        $chat = array();
        $counter=0;
        foreach ($getChat as $key => $value) {
           if($value['userId']==$data['userId']){
                $userID=$value['opponentUserId'];
            }else{
                $userID=$value['userId'];
            }            
            if(in_array($userID,$usersArray)){
               continue;
            }
            $usersArray[]=$userID;
            $userPostedBy = convert_array(get_user_by('id',$userID));
            $image = get_user_meta($userID, 'user_image', true);
            $user_image = '';
            if (!empty($image)) {
                $user_image = get_post_field('guid', $image);
            }            
            $chat[$counter]['chatUserImageUrl'] = $user_image;
            $chat[$counter]['chatUserName'] = $userPostedBy['data']['display_name'];
            $time=strtotime($value['created']);
            $chat[$counter]['timeElapsed'] ="$time" ;
            $chat[$counter]['chatMessage'] = $value['message'];
            $chat[$counter]['conversationId'] = $value['id'];
            $chat[$counter]['unReadMessagesCount'] = unReadMessagesCount($data['userId'],$userID,'private');
            $chat[$counter]['chatUserId'] = $userPostedBy['data']['ID'];                        
            $counter++;
        }
        $finalChat=array();
        if (!empty($chat)) {
            $countChat=count($chat);
            if(!empty($data['offset'])){
              $record=1+$data['offset'];  
              $recordCheck=1+$data['offset']*20;
              $start=$data['offset']*20;
            }else{
              $record=0;  
              $recordCheck=20;
              $start=0;
            }            
            $counterVar=1;            
            foreach($chat as $k=>$v){
                if($k<$recordCheck and $k>=$start and $counterVar<20){
                  $finalChat[]=$v;                    
                  $counterVar++;
                }              
            }  
            response(1, $finalChat, 'No Error Found');
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