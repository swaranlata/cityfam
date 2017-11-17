<?php
require 'config.php';
$data = $_REQUEST;
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
if ($data['offset']=='') {
    $error = 0;
}
if ($data['type']=='') {
    $error = 0;
}else{
    $array=array(0,1); 
    if(!in_array($data['type'],$array)){
       $error = 0;  
    }
}
if (empty($error)) {
   response(0, array(), 'Please enter required fields.');
} else {    
    $checkAuthorisarion = AuthUser($data['userId'],array());
    $custom_query='';
    if ($data['offset']==0 || !empty($data['offset']) and  $data['offset']!='') {
        $offset=20*$data['offset'];
        $custom_query .= ' LIMIT 20  OFFSET ' .$offset;
    }
    if(!empty($data['type'])){
       $userFriends = getMyFriends($data['userId'], 'false');
        $rows=array();
        if(!empty($userFriends)){
          $usrFriends=implode('","',$userFriends);  
          $userFrndQuery='select * from `wp_activities` where `user_id` in ("'.$usrFriends.'") and `status`="0" order by id desc'.$custom_query;
          $rows = $wpdb->get_results($userFrndQuery);
        }      
    }else{
        $rows= $wpdb->get_results('select * from `wp_activities` where `user_id`="'.$data['userId'].'" and `status`="0"  order by id desc'.$custom_query);
    } 
    $activity=array();
    if(!empty($rows)){
        $rows=convert_array($rows);  
        $kk=0;
        foreach($rows as $key=>$value){
            $crntUserRecord=convert_array(get_user_by('id',$value['user_id']));
            $userName=$crntUserRecord['data']['display_name'];
            $eventDetails=getSingleEvent($value['event_id']);
            $eventName=$eventDetails['eventName'];
            $getAllPoints=getAllEvents();
            if(in_array($value['event_id'],$getAllPoints)){
                $activity[$kk]=getSingleEvent($value['event_id']);  
                $activity[$kk]['ActivityType']= $value['type']; 
                $activityMsg='';
                if($value['type']=='created'){
                    $activityMsg=' has created a new event '.$eventName;
                }elseif($value['type']=='accept'){
                    $activityMsg=' accepted the '.$eventName.' event invitation.';
                }elseif($value['type']=='invited'){
                    $activityMsg='invited';
                }elseif($value['type']=='interested'){
                     $activityMsg= ' intersted in  this '.$eventName.' event.';
                }elseif($value['type']=='decline'){
                    $activityMsg= ' declined the '.$eventName.' event.';
                }
                elseif($value['type']=='comment'){
                    $activityMsg=' commented on  the '.$eventName.' event.';
                }
                $activity[$kk]['activityMessage']= $activityMsg; 
                $activity[$kk]['activityUser']= $userName; 
                $images=getPersonInvolved($value['event_id']); 
                if(!empty($images)){
                    $images=array_filter($images);
                  $activity[$kk]['personInvolved']=$images;          
                }else{
                  $activity[$kk]['personInvolved']=array();               
                }
                $time=strtotime($value['created']);
                $activity[$kk]['timeElapsed']= "$time";            
                $kk++;                
            }            
        }
       response(1, $activity, 'No Error Found.'); 
    }else{
      response(0, array(), 'No Activity Found.');  
    }
} 
?>