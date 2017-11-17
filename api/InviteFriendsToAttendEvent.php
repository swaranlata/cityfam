<?php
require 'config.php';
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
if (empty($data['groupIds']) and empty($data['emailId'])) {
    $error = 0;
}
if (empty($data['eventId'])) {
    $error = 0;
}
if (empty($error)) {    
    response(0, null, 'Please enter required fields.');
}else {
    $checkAuthorisarion = AuthUser($data['userId'], 'string');
    $getAllEvents = getAllEvents();
    $getAllExpiredEvents= getAllExpiredEvents();
    if (in_array($data['eventId'], $getAllExpiredEvents)) {
        response(0, null, 'Expired Event.');
    }
    if (!in_array($data['eventId'], $getAllEvents)) {
        response(0, null, 'No Event Found.');
    }   
    $get_post=get_post($data['eventId']);
    if(!empty($get_post)){
        $get_post=convert_array($get_post);
        if($get_post['post_author']!=$data['userId']){
        response(0, null, 'You are not author of this event.');
        }
    }
   $post_id=$data['eventId'];
   if(!empty($data['groupIds'])){
      foreach ($data['groupIds'] as $k => $v) {
        $getMembersOfGroup=getGroupMemberDetail($v);
          if(!empty($getMembersOfGroup)){
             foreach($getMembersOfGroup as $keys=>$values){   
                 $new_query='select `id` from `wp_event_invitations` where `event_id`="'.$data['eventId'].'" and `emailID`="'.$values['user_email'].'"';
                 $getInvitationExist=$wpdb->get_results($new_query);
                 if(empty($getInvitationExist)){
                    if(!empty($values['id'])){
                        $wpdb->insert('wp_event_invitations', array('userID' => $values['id'], 'event_id' => $post_id, 'emailID' => $values['user_email'], 'name' => $values['display_name'], 'status' => '0', 'created' => date('Y-m-d H:i:s'))); 
                        $checkNotificationStatus=getNotificationStatusByUserId($values['id'],1);
                        if(!empty($checkNotificationStatus)){
                            pushMessageNotification($values['id'],'You are invited to the event.');
                        }   
                        //$activity=activity($data['userId'],'invited',$data['eventId'],$values['id']);
                    }else{
                        $wpdb->insert('wp_event_invitations', array('userID' => "", 'event_id' => $post_id, 'emailID' => $values['user_email'], 'name' => "", 'status' => '0', 'created' => date('Y-m-d H:i:s')));
                    }
                    $whoCanSee=get_post_meta($data['eventId'], 'whoCanSee')[0];
                    if($whoCanSee=='public'){
                      if(!empty($values['id'])){
                        $wpdb->insert('wp_public_invitations', array('userID' => $values['id'], 'event_id' => $post_id, 'emailID' => $values['user_email'], 'name' => $values['display_name'], 'status' => '0', 'created' => date('Y-m-d H:i:s'))); 
                        $checkNotificationStatus=getNotificationStatusByUserId($values['id'],1);
                        if(!empty($checkNotificationStatus)){
                            pushMessageNotification($values['id'],'You are invited to the  '.$get_post['post_title']);
                        }                         
                       // $activity=activity($data['userId'],'invited',$data['eventId'],$values['id']);
                        }else{
                          $wpdb->insert('wp_public_invitations', array('userID' => "", 'event_id' => $post_id, 'emailID' => $values['user_email'], 'name' => "", 'status' => '0', 'created' => date('Y-m-d H:i:s')));
                        }  
                    }                    
                 }
                  

             } 
          }                 
      } 
      add_post_meta($post_id, 'invited_groups', serialize($data['groupIds']));
   }    
   if(!empty($data['emailId'])){
        foreach($data['emailId'] as $k=>$v){
            $values=getUserByEmail($v);
            $new_query='select * from `wp_event_invitations` where `event_id`="'.$post_id.'" and `emailID`="'.$v.'"';
            $getInvitationExist=$wpdb->get_results($new_query);
            if(empty($getInvitationExist)){
                if(!empty($values)){
                    $values=convert_array($values);
                    $wpdb->insert('wp_event_invitations', array('userID' => $values['id'], 'event_id' => $post_id, 'emailID' => $values['user_email'], 'name' => $values['display_name'], 'status' => '0', 'created' => date('Y-m-d H:i:s')));
                    $checkNotificationStatus=getNotificationStatusByUserId($values['id'],1);
                    if(!empty($checkNotificationStatus)){
                       pushMessageNotification($values['id'],"You are invited to the event."); 
                    }                    
                   // $activity=activity($data['userId'],'invited',$post_id,$values['id']);
                }else{
                    $wpdb->insert('wp_event_invitations', array('userID' => '', 'event_id' => $post_id, 'emailID' => $v, 'name' => '', 'status' => '0', 'created' => date('Y-m-d H:i:s'))); 
                }
            }                
        }
        add_post_meta($post_id, 'invited_emails', serialize($data['emailId']));
  } 
  response(1, "1", 'No Error Found.'); 
}
?>
