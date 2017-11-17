    <?php
require 'config.php';
date_default_timezone_set("Asia/Calcutta");
    $encoded_data = file_get_contents('php://input');
    $data = json_decode($encoded_data, true);
    $error = 1;
    global $wpdb;
    if (empty($data['userId'])) {
        $error = 0;
    }
    if (empty($data['message'])) {
        $error = 0;
    }
    $typeArray = array(0, 1, 2);
    if($data['type']!=''){
        if (!in_array($data['type'], $typeArray)) {
        $error = 0;
    }
    }else{
         $error = 0;
    }
    if ($data['type'] == 1) {
        if (empty($data['opponentUserId'])) {
            $error = 0;
        }
    }
    if ($data['type'] == 0) {
        if (empty($data['groupId'])) {
            $error = 0;
        }
    }
    if (!empty($error)) {    
        $checkAuthorisarion = AuthUser($data['userId'], 'string');    
        $sentBy=$checkAuthorisarion[0]['display_name'];
        if($data['type'] == 0 and !empty($data['groupId'])){
            $qryy='select * from `wp_group_members` where `groupId`="' . $data['groupId'] . '" and `email`="'.$checkAuthorisarion[0]['user_email'].'"';
            $getGroupMembers = $wpdb->get_results($qryy);
            if(!empty($getGroupMembers)){
               $groupMember=getGroupMemberDetail($data['groupId']);
                if(!empty($groupMember)){
                   foreach($groupMember as $key=>$val){
                       if($val['id']!=$data['userId']){
                          $array['userId']=$data['userId'];
                          $array['groupId']=$data['groupId'];
                          $array['message']=$data['message'];
                          $array['created']=date('Y-m-d h:i:s A',time()); 
                          $array['type']=$data['type'];
                          $array['opponentUserId']=$val['id'];
                          $wpdb->insert('wp_conversations',$array);  
                          $checkNotificationStatus=getNotificationStatusByUserId($val['id'],8);
                            if(!empty($checkNotificationStatus)){
                                pushMessageNotification($val['id'] ,'you received a new message from '.$sentBy);
                            } 
                       }              
                   } 
                }else{
               response(0,null, 'You are not member of this group.');  
                }
            }else{
               response(0,null, 'You are not member of this group.');  
            }                
        }elseif($data['type']==2){        
            $publicMember=getAllUser();
            if(!empty($publicMember)){
                foreach($publicMember as $key=>$val){
                    if($val['ID']!=$data['userId']){
                        $array['userId']=$data['userId'];
                        $array['groupId']="0";
                        $array['message']=$data['message'];
                        $array['created']=date('Y-m-d H:i:s A'); 
                        $array['type']=$data['type'];
                        $array['opponentUserId']=$val['ID'];
                        $wpdb->insert('wp_conversations',$array); 
                        $checkNotificationStatus=getNotificationStatusByUserId($val['ID'],8);
                        if(!empty($checkNotificationStatus)){
                            pushMessageNotification($val['ID'],'you received a new message from '.$sentBy);
                        } 
                    } 
                } 
            } 
        }else{
          $array['userId']=$data['userId'];
          $array['message']=$data['message'];
          $array['created']=date('Y-m-d H:i:s A'); 
          $array['type']=$data['type'];
          if(!empty($data['opponentUserId'])){
             $array['opponentUserId']=$data['opponentUserId'];
          }   
          $wpdb->insert('wp_conversations',$array);
          $checkNotificationStatus=getNotificationStatusByUserId($data['opponentUserId'],8);
            if(!empty($checkNotificationStatus)){
                pushMessageNotification($data['opponentUserId'],'you received a new message from '.$sentBy);
            } 
        }    
        response(1,'Message successfully sent', 'No Error Found');
    } else {
        response(0, null, 'Please enter required fields.');
    }
    ?>