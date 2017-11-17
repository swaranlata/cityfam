<?php
require 'config.php';
global $wpdb;
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
$data=$_GET;
$error = 1;
if (empty($data['userId'])) {
    $error = 0;
}
if (empty($data['titleId'])) {
    $error = 0;
}
if ($data['status']!='') {
   $status=array(0,1); 
    if(!in_array($data['status'],$status)){
     $error = 0;   
    }
}else{
  $error = 0;  
}
if(!empty($error)){
    if(!empty($data['status'])){
        $status='enabled';
    }else{
         $status='disabled';
    }
    AuthUser($data['userId'],'string');
    $titles=array(1,2,3,4,5,6,7,8);
    if(!in_array($data['titleId'],$titles)){
      response(0, null, 'Please enter valid title Id.');   
    }
    $getNotificationSetting = $wpdb->get_row('select * from `wp_user_notification_settings` where `user_id`="'.$data['userId'].'"');  
    if(!empty($getNotificationSetting)){
        $getNotificationSetting=convert_array($getNotificationSetting);
        $settings=unserialize($getNotificationSetting['settings']);
        if(!empty($settings)){
            $settings[$data['titleId']]=$data['status'];
        }        
        if($data['titleId']=='7'){
          $settings=array(
            '1'=>'1',
            '2'=>'1',
            '3'=>'1',
            '4'=>'1',
            '5'=>'1',
            '6'=>'1',
            '7'=>'1',
            '8'=>'1',
          );  
        }
        $serilaizeSettings=serialize($settings);        
        $wpdb->update('wp_user_notification_settings', array(
          'settings' => $serilaizeSettings
        ),
        array(
          'id'=>$getNotificationSetting['id']
        )
        ); 
       }
    response(1, "$status", 'No Error Found'); 
}else{
    response(0, null, 'Please enter the required fields.');
}

?>