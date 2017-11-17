<?php
require 'config.php';
$data = $_REQUEST;
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
/* User Received Friend Request*/
if (!empty($error)) {
    AuthUser($data['userId'],'string');
    $final_query='select `ID` from `wp_posts` where  `post_status`="publish" and `post_type`="events" and `post_author`="'.$data['userId'].'" order by ID desc limit 5';
    $query=$wpdb->get_results($final_query);
    $locations=array();
    if(!empty($query)){
      $query=convert_array($query);
       foreach($query as $key=>$val){
        $locations[$key]['placeName']=get_post_meta($val['ID'],'placeName',TRUE);
        $locations[$key]['latitude']=get_post_meta($val['ID'],'latitude',TRUE);
        $locations[$key]['longitude']=get_post_meta($val['ID'],'longitude',TRUE);
      }  
    }
    if(!empty($locations)){
         response(1,$locations, 'No Error Found.');
    }else{
        response(0,array(), 'No Location Found.'); 
    }
} else {
    response(0,array(), 'Please enter the required fields.');
}
?>
