    <?php
    require 'config.php';
    $encoded_data = file_get_contents('php://input');
    $data = json_decode($encoded_data, true);
    $error = 1;
    global $wpdb;
    if (empty($data['eventId'])) {
        $error = 0;
        $post_id = $data['eventId'];
    }
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
        $start_time_format = str_replace('-', ' ', $data['eventStartTime']);
        $start_time_valid = str_replace('/', '-', $start_time_format);
        $start_time = date('Y-m-d h:i:s a', strtotime($start_time_valid));
        $end_time_format = str_replace('-', ' ', $data['eventEndTime']);
        $end_time_valid = str_replace('/', '-', $end_time_format);
        $end_time = date('Y-m-d h:i:s a', strtotime($end_time_valid));
        $wpdb->query('update `wp_posts` set `post_title`= "'.$data['eventName'].'",`post_content`="'.$data['eventDescription'].'",`post_type`="events",`post_status`="publish" where `ID`="'.$data['eventId'].'"');
        $post_id = $data['eventId'];
        $cat_ids = explode('|', $data['categories']);
        if (!empty($cat_ids)) {
            $wpdb->query('delete from `wp_term_relationships` where `object_id`="' . $data['eventId'] . '"');
            foreach ($cat_ids as $k => $v) {
                $wpdb->insert('wp_term_relationships', array('object_id' => $data['eventId'], 'term_taxonomy_id' => $v));
            }
        }
        update_post_meta($post_id, 'eventStartTime', $start_time);
        update_post_meta($post_id, 'eventEndTime', $end_time);
        update_post_meta($post_id, 'whoCanSee', $data['whoCanSee']);
        $canSee = get_post_meta($post_id, 'whoCanSee', TRUE);
        $groupsDataAlready=get_post_meta($post_id, 'invited_groups',true);
        if(!empty($groupsDataAlready)){
            $groupsDataAlready=unserialize($groupsDataAlready);
        }
        $EmailDataAlready=get_post_meta($post_id, 'invited_emails',true);
            if(!empty($EmailDataAlready)){
                $EmailDataAlready=unserialize($EmailDataAlready);
            }
        if ($data['whoCanSee'] == 'private') {
            $privateUserLists = $data['privateUserList'];
            $customUsers = array();
            if (!empty($privateUserLists)) {
                if(!empty($privateUserLists['groupIds'])){
                  foreach ($privateUserLists['groupIds'] as $k => $v) {
                    $groupsDataAlready[]=$v;
                    $getMembersOfGroup=getGroupMemberDetail($v);
                      if(!empty($getMembersOfGroup)){
                         foreach($getMembersOfGroup as $keys=>$values){
                             $getInvitationExist=$wpdb->get_results('select * from `wp_event_invitations` where `event_id`="'.$post_id.'" and `emailID`="'.$values['user_email'].'"');
                             if(empty($getInvitationExist)){
                                 $userEmailData=getUserByEmail($values['user_email']);
                                 if(!empty($userEmailData)){
                                     $userEmailData=convert_array($userEmailData);
                                      $wpdb->insert('wp_event_invitations', array('userID' => $userEmailData['id'], 'event_id' => $post_id, 'emailID' => $userEmailData['user_email'], 'name' => $userEmailData['display_name'], 'status' => '0', 'created' => date('Y-m-d h:i:s')));                                     $checkNotificationStatus=getNotificationStatusByUserId($userEmailData['id'],1);
                                        if(!empty($checkNotificationStatus)){
                                           pushMessageNotification($userEmailData['id'],'You are invited to the  '.$get_post['post_title']);
                                        }
                                      //$activity=activity($data['userId'],'invited',$post_id,$userEmailData['id']);

                                 }else{
                                      $wpdb->insert('wp_event_invitations', array('userID' =>"", 'event_id' => $post_id, 'emailID' => $values['user_email'], 'name' => "", 'status' => '0', 'created' => date('Y-m-d h:i:s')));
                                 }

                             }
                            } 
                      }                 
                  }
                    $groupsDataAlready=array_unique($groupsDataAlready);
                  update_post_meta($post_id, 'invited_groups', serialize($groupsDataAlready));
                }
               if(!empty($privateUserLists['emailId'])){
                    foreach($privateUserLists['emailId'] as $k=>$v){
                        $EmailDataAlready[]=$v;
                        $values=getUserByEmail($v);
                        $getInvitationExist=$wpdb->get_results('select * from `wp_event_invitations` where `event_id`="'.$post_id.'" and `emailID`="'.$v.'"');
                        if(empty($getInvitationExist)){
                            if(!empty($values)){
                                $wpdb->insert('wp_event_invitations', array('userID' => $values['id'], 'event_id' => $post_id, 'emailID' => $values['user_email'], 'name' => $values['display_name'], 'status' => '0', 'created' => date('Y-m-d h:i:s')));
                                $checkNotificationStatus=getNotificationStatusByUserId($values['id'],1);
                                        if(!empty($checkNotificationStatus)){
                                           pushMessageNotification($values['id'],'You are invited to the  '.$get_post['post_title']);
                                        }
                                
                               // $activity=activity($data['userId'],'invited',$post_id,$values['id']);
                            }else{
                                $wpdb->insert('wp_event_invitations', array('userID' => '', 'event_id' => $post_id, 'emailID' => $v, 'name' => '', 'status' => '0', 'created' => date('Y-m-d h:i:s'))); 
                            }

                        }                  

                    }
                   $EmailDataAlready=array_unique($EmailDataAlready);
                   update_post_meta($post_id, 'invited_emails', serialize($EmailDataAlready));
                  }

            }
       }elseif ($data['whoCanSee'] == 'friends') {
            $userFriends = getMyFriends($data['userId']);
            if (!empty($userFriends)) {
                foreach ($userFriends as $key => $value) {
                    $wpdb->query('delete from `wp_event_invitations` where `event_id`="'.$post_id.'" and `userID`=""');
                    $customUsers[] = $value;
                    $userCurrentFriend = convert_array(get_user_by('id', $value));
                    $check_existence= $wpdb->get_row('select `id` from `wp_event_invitations` where `event_id`="'.$post_id.'" and `userID`="'.$value.'"');
                    if(!empty($check_existence)){
                         $check_existence=convert_array($check_existence);
                          $wpdb->update('wp_event_invitations', array(
                           'userID' => $userCurrentFriend['userID'],
                           'event_id' => $post_id, 
                           'emailID' => $userCurrentFriend['data']['user_email'],
                           'name' => $userCurrentFriend['data']['display_name'], 
                           'created' => date('Y-m-d h:i:s')
                           ),
                           array(
                               'id'=>$check_existence['id']
                           )
                          );   
                    }else{
                        $wpdb->insert('wp_event_invitations', array('userID' => $userCurrentFriend['ID'], 'event_id' => $post_id, 'emailID' => $userCurrentFriend['data']['user_email'], 'name' => $userCurrentFriend['data']['display_name'], 'status' => '0', 'created' => date('Y-m-d h:i:s')));
                        $checkNotificationStatus=getNotificationStatusByUserId($userCurrentFriend['ID'],1);
                        if(!empty($checkNotificationStatus)){
                          pushMessageNotification($userCurrentFriend['ID'],'You are invited to the  '.$get_post['post_title']);
                        }                        
                       // $activity=activity($data['userId'],'invited',$post_id,$userCurrentFriend['ID']);
                    }
                }
            }
        } else {
            $getUserList = get_users(array('fields' => array('ID', 'display_name', 'user_email')));
            $allUserList = json_decode(json_encode($getUserList), true);
            if (!empty($allUserList)) {
                foreach ($allUserList as $k => $v) {
                    if (!empty($v['ID'])) {
                        $customUsers[] = $v['ID'];
                    }
                }
            }
        }    
        update_post_meta($post_id, 'privateUserList', serialize($customUsers));
        update_post_meta($post_id, 'allowGuestsToInviteOthers', $data['allowGuestsToInviteOthers']);
        if (!empty($data['latitude'])) {
            update_post_meta($post_id, 'latitude', $data['latitude']);
        }
        if (!empty($data['longitude'])) {
            update_post_meta($post_id, 'longitude', $data['longitude']);
        }
        if (!empty($data['placeName'])) {
            update_post_meta($post_id, 'placeName', $data['placeName']);
        }
        if (!empty($data['ticketLink'])) {
            update_post_meta($post_id, 'ticketLink', $data['ticketLink']);
        }
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
            update_post_meta($post_id, '_thumbnail_id', $attach_id);
        }


        response(1,"1", 'No Error Found.');
    } else {
        response(0, null, 'Please enter required fields.');
    }
    ?>
