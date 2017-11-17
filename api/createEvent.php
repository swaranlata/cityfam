<?php
require 'config.php';
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
if (empty($data['eventName'])) {
    $error = 0;
}
if (empty($data['eventStartTime'])) {
    $error = 0;
}
if (empty($data['eventEndTime'])) {
    $error = 0;
}
if (!empty($error)) {
    $checkAuthorisarion = AuthUser($data['userId'],'string');
    $start_time_format = str_replace('-', ' ', $data['eventStartTime']);
    $start_time_valid = str_replace('/', '-', $start_time_format);
    $start_time = date('Y-m-d h:i:s a', strtotime($start_time_valid));
    $end_time_format = str_replace('-', ' ', $data['eventEndTime']);
    $end_time_valid = str_replace('/', '-', $end_time_format);
    $end_time = date('Y-m-d h:i:s a', strtotime($end_time_valid));
    $insert = array(
        'post_author' => $data['userId'],
        'post_title' => $data['eventName'],
        'post_content' => $data['eventDescription'],
        'post_type' => 'events',
        'post_status' => 'publish',
    );
    $post_id = wp_insert_post($insert);
    $activity=activity($data['userId'],'created',$post_id);
    $cat_ids = explode('|', $data['categories']);
    if (!empty($cat_ids)) {
        foreach ($cat_ids as $k => $v) {
            $wpdb->insert('wp_term_relationships', array('object_id' => $post_id, 'term_taxonomy_id' => $v));
        }
    }
    add_post_meta($post_id, 'eventStartTime', $start_time);
    add_post_meta($post_id, 'eventEndTime', $end_time);
    add_post_meta($post_id, 'whoCanSee', $data['whoCanSee']);
    if ($data['whoCanSee'] == 'private') {
        $privateUserLists = $data['privateUserList'];
        $customUsers = array();
        if (!empty($privateUserLists)) {
            if(!empty($privateUserLists['groupIds'])){
              foreach ($privateUserLists['groupIds'] as $k => $v) {
                $getMembersOfGroup=getGroupMemberDetail($v);
                  if(!empty($getMembersOfGroup)){
                     foreach($getMembersOfGroup as $keys=>$values){   
                         if(!empty($values['id'])){
                            $wpdb->insert('wp_event_invitations', array('userID' => $values['id'], 'event_id' => $post_id, 'emailID' => $values['user_email'], 'name' => $values['display_name'], 'status' => '0', 'created' => date('Y-m-d h:i:s'))); 
                            // $activity=activity($data['userId'],'invited',$post_id,$values['id']);
                             pushMessageNotification($values['id'],'You are invited to the  '.$data['eventName']);
                        }else{
                            $wpdb->insert('wp_event_invitations', array('userID' => "", 'event_id' => $post_id, 'emailID' => $values['user_email'], 'name' => "", 'status' => '0', 'created' => date('Y-m-d h:i:s'))); 
                        } 
                         
                        
                     } 
                  }                 
              } 
              add_post_meta($post_id, 'invited_groups', serialize($privateUserLists['groupIds']));
            }
           if(!empty($privateUserLists['emailId'])){
                foreach($privateUserLists['emailId'] as $k=>$v){
                    $values=getUserByEmail($v);
                    $new_query='select * from `wp_event_invitations` where `event_id`="'.$post_id.'" and `emailID`="'.$v.'"';
                    $getInvitationExist=$wpdb->get_results($new_query);
                    if(empty($getInvitationExist)){
                        if(!empty($values)){
                            $values=convert_array($values);
                            $wpdb->insert('wp_event_invitations', array('userID' => $values['id'], 'event_id' => $post_id, 'emailID' => $values['user_email'], 'name' => $values['display_name'], 'status' => '0', 'created' => date('Y-m-d H:i:s')));
                            //$activity=activity($data['userId'],'invited',$post_id,$values['id']);
                            pushMessageNotification($values['id'],'You are invited to the  '.$data['eventName']);
                        }else{
                            $wpdb->insert('wp_event_invitations', array('userID' => '', 'event_id' => $post_id, 'emailID' => $v, 'name' => '', 'status' => '0', 'created' => date('Y-m-d H:i:s'))); 
                        }
                        
                    }                  
                    
                }
                add_post_meta($post_id, 'invited_emails', serialize($privateUserLists['emailId']));
              } 
            
    
        } 
        
    }
    elseif ($data['whoCanSee'] == 'friends') {
        $userFriends = getMyFriends($data['userId']);
        if (!empty($userFriends)) {
            foreach ($userFriends as $key => $value) {
                $customUsers[] = $value;
                $userCurrentFriend = convert_array(get_user_by('id', $value));
                $wpdb->insert('wp_event_invitations', array('userID' => $userCurrentFriend['ID'], 'event_id' => $post_id, 'emailID' => $userCurrentFriend['data']['user_email'], 'name' => $userCurrentFriend['data']['display_name'], 'status' => '0','created' => date('Y-m-d h:i:s')));
                pushMessageNotification($userCurrentFriend['ID'],'You are invited to the  '.$data['eventName']);
                //$activity=activity($data['userId'],'invited',$post_id,$userCurrentFriend['ID']);
            }
        }
    } 
    else {
        $getUserList = get_users(array('fields' => array('ID', 'display_name', 'user_email')));
        $allUserList = json_decode(json_encode($getUserList), true);
        if (!empty($allUserList)) {
            foreach ($allUserList as $k => $v) {
                if (!empty($v['ID'])) {
                    $customUsers[] = $v['ID'];
                }
             /*  $wpdb->insert('wp_event_invitations', array('userID' => $v['ID'], 'event_id' => $post_id, 'emailID' => $v['user_email'], 'name' => $v['display_name'], 'status' => '0','event_type'=>'public', 'created' => date('Y-m-d h:i:s')));  */
            }
        }       
    }
    add_post_meta($post_id, 'privateUserList', serialize($customUsers));
    add_post_meta($post_id, 'allowGuestsToInviteOthers', $data['allowGuestsToInviteOthers']);
    add_post_meta($post_id, 'latitude', $data['latitude']);
    add_post_meta($post_id, 'longitude', $data['longitude']);
    add_post_meta($post_id, 'placeName', $data['placeName']);
    add_post_meta($post_id, 'ticketLink', $data['ticketLink']);
    if (!empty($data['eventImage'])) {
        $encode_image = $data['eventImage'];
        $directory = "/" . date(Y) . "/" . date(m) . "/";
        $wp_upload_dir = wp_upload_dir();
        $data = base64_decode($encode_image);
        $filename = time() . ".png";
        $fileurl = "../wp-content/uploads" . $directory . $filename;
        $filetype = wp_check_filetype(basename($fileurl), null);
        file_put_contents($fileurl, $data);
        $attachment = array(
            'guid' => $wp_upload_dir['url'] . '/' . basename($fileurl),
            'post_mime_type' => $filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($fileurl)),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment($attachment, $fileurl, $postId);
        require_once('../wp-admin/includes/image.php' );
        $attach_data = wp_generate_attachment_metadata($attach_id, $fileurl);
        wp_update_attachment_metadata($attach_id, $attach_data);
        add_post_meta($post_id, '_thumbnail_id', $attach_id);
    }
    response(1, "$post_id", 'No Error Found.');
} else {
    response(0, null, 'Please enter required fields.');
}

?>
