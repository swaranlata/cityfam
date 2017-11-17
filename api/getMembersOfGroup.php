<?php
require 'config.php';
$data = $_GET;
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
if (empty($data['groupId'])) {
    $error = 0;
}
if (!empty($error)) {
    $checkAuthorisarion = AuthUser($data['userId'], array());
    $results = $wpdb->get_row('select * from `wp_groups` where `id`="' . $data['groupId'] . '"');
    if (!empty($results)) {
        $results = convert_array($results);
        if($results['group_type']==1){//chat
           $getGroupMembers = $wpdb->get_results('select * from `wp_group_members` where `groupId`="' . $data['groupId'] . '" and `email`!="'.$checkAuthorisarion[0]['user_email'].'"'); 
        }else{//Events
           $getGroupMembers = $wpdb->get_results('select * from `wp_group_members` where `groupId`="' . $data['groupId'] . '" and `userId`="'.$data['userId'].'"'); 
            if(empty($getGroupMembers)){
               response(0, array(), 'You are not authorise to access the group content.'); 
            }
        }  
        $members = array();
        if (!empty($getGroupMembers)) {
            $getGroupMembers = convert_array($getGroupMembers);
            foreach ($getGroupMembers as $key => $value) {               
                $getUserDetail = get_user_by('email', $value['email']);
                if (!empty($getUserDetail)) {
                    $getUserDetail=convert_array($getUserDetail);
                    $image = get_user_meta($getUserDetail['ID'], 'user_image', true);
                    $user_image = '';
                    if (!empty($image)) {
                        $user_image = get_post_field('guid', $image);
                    }
                    $members[$key]['userImageUrl'] = $user_image;
                    $members[$key]['userName'] = $getUserDetail['data']['display_name'];
                    $members[$key]['userId'] = $getUserDetail['data']['ID'];
                    $members[$key]['emailId'] = $getUserDetail['data']['user_email'];
                } else {
                    $members[$key]['userImageUrl'] = '';
                    $members[$key]['userName'] = '';
                    $members[$key]['userId'] = '';
                    $members[$key]['emailId'] = $value['email'];
                }
            }
             response(1, $members, 'No Error Found.');
        }else{
             response(0, array(), 'No Member found in this group.');
        }       
    } else {
        response(0, array(), 'No Group found.');
    }
} else {
    response(0, array(), 'Please enter required fields.');
}
?>