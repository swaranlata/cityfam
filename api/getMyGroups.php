<?php
error_reporting(1);
require 'config.php';
$data = $_GET;
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
$array=array(0,1);
if($data['type']!=''){
	if(!in_array($data['type'],$array)){
		$error = 0;
	}	
}else{
	$error = 0;
}
if (!empty($error)) {
    $checkAuthorisarion = AuthUser($data['userId'], array());
	if($data['type']==0){
		$results = $wpdb->get_results('select * from `wp_groups` where `userId`="' . $data['userId'] . '" and `group_type`="'.$data['type'].'"');
	}else{		
		$user_details=convert_array(get_user_by('id',$data['userId']));
		$myQuery='SELECT * FROM `wp_group_members` where `email`="' .$user_details['data']['user_email'] . '"';
	    $mYgroup = $wpdb->get_results($myQuery);
		if(!empty($mYgroup)){
			$mYgroup=convert_array($mYgroup);
			foreach($mYgroup as $key=>$val){
				$groupID[]=$val['groupId'];
			}
			$groupIDs=implode('","',array_unique($groupID));			
		}
	    $qry='select * from `wp_groups` where `group_type`="1" and `id` in("'.$groupIDs.'")';
		$results = $wpdb->get_results($qry);
	}  
    if (!empty($results)) {
        $results=convert_array($results);
		$groups=array();
		foreach ($results as $key => $value) {
            $groups[$key]['groupId']=$value['id'];
            $groups[$key]['ownerId']=$value['userId'];
            $groups[$key]['groupName']=$value['groupName'];
            if($data['type']==0){
             $count=getGroupMember($value['id'],'count');    
            }else{//friends
             $count=getGroupMember($value['id'],'count');
             $count=$count-1;
            }
            $groups[$key]['groupMemberCount']="$count";
        }   
		
        response(1,$groups, 'No Error Found.');
    } else {
        response(0, array(), 'No Group found.');
    }
} else {
    response(0, array(),'Please enter required fields.');
}
?>