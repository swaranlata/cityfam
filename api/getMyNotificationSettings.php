<?php
require 'config.php';
global $wpdb;
$data = $_REQUEST;
$error = 1;
if (empty($data['userId'])) {
    $error = 0;
}
if (!empty($error)) {
    AuthUser($data['userId'],array());
    $getNotificationSetting = $wpdb->get_row('select * from `wp_user_notification_settings` where `user_id`="'.$data['userId'].'"');  
    if(!empty($getNotificationSetting)){
        $getNotificationSetting=convert_array($getNotificationSetting);
        $settings=unserialize($getNotificationSetting['settings']);
        $notification=array();
        if(!empty($settings)){
            $count=0;
            foreach($settings as $key=>$val){
               $notification[$count]['title']=getTitleById($key);
               $notification[$count]['titleId']="$key";
               $notification[$count]['isEnabled']=$val;                
               $count++;  
            }            
        }
        response(1,$notification, 'No Error Found.');
    }else{
       response(0, array(), 'No Notifications settings are available for this user.'); 
    }    
} else {
    response(0, array(), 'Please enter the required fields.');
}
?>