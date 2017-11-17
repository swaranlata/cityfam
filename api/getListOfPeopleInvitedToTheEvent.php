<?php
require 'config.php';

$data = $_REQUEST;
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
if (empty($data['eventId'])) {
    $error = 0;
}
if ($data['offset']=='') {
    $error = 0;
}
if (empty($error)) {
    response(0,array(), 'Please enter required fields.');
} else {
    $checkAuthorisarion = AuthUser($data['userId'],array());
    $getAllEvents = getAllEvents();
    if (!in_array($data['eventId'], $getAllEvents)) {
        response(0, array(), 'No Event Found.');
    }
    $custom_query='';
    if ($data['offset']==0 || !empty($data['offset']) and  $data['offset']!='') {
        $offset=20*$data['offset'];
        $custom_query .= ' LIMIT 20  OFFSET ' .$offset;
    }
    $users = array();
    $whocansee=get_post_meta($data['eventId'], 'whoCanSee')[0]; 
    if($whocansee=='public'){
         $query ='SELECT * FROM `wp_event_invitations` WHERE  `event_id` = "' . $data['eventId'] . '" and `status`="0"'.$custom_query;
    }else{
        $query ='SELECT * FROM `wp_event_invitations` WHERE  `event_id` = "' .$data['eventId'] . '"'.$custom_query; 
    }   
    $results = $wpdb->get_results($query);
    if (!empty($results)) {
        $results = convert_array($results);
        foreach ($results as $key => $values) {
            if (!empty($values['userID'])) {
                $getUserDetail = convert_array(get_user_by('id', $values['userID']));
                if (!empty($getUserDetail['ID'])) {
                    /* start User Details */
                    $image = get_user_meta($getUserDetail['ID'], 'user_image', true);
                    $user_image = '';
                    if (!empty($image)) {
                        $user_image = get_post_field('guid', $image);
                    }
                    $users[$key]['userImageUrl'] = $user_image;
                    $users[$key]['userName'] = $getUserDetail['data']['display_name'];
                    $users[$key]['userId'] = $getUserDetail['data']['ID'];
                    if ($values['status'] == 0) {
                        $response = 'pending';
                    } elseif ($values['status'] == 1) {
                        $response = 'accepted';
                    } elseif ($values['status'] == 2) {
                        $response = 'declined';
                    } elseif ($values['status'] == 3) {
                        $response = 'interested';
                    }
                    $users[$key]['response'] = $response;
                    $users[$key]['emailId'] = $getUserDetail['data']['user_email'];
                    $users[$key]['isCityFamUser'] = "1";
                }
            }else{
                    $users[$key]['userImageUrl'] = '';
                    $users[$key]['userName'] = $values['name'];
                    $users[$key]['userId'] = "";
                    if ($values['status'] == 0) {
                        $response = 'pending';
                    } elseif ($values['status'] == 1) {
                        $response = 'accepted';
                    } elseif ($values['status'] == 2) {
                        $response = 'declined';
                    } elseif ($values['status'] == 3) {
                        $response = 'interested';
                    }
                    $users[$key]['response'] = $response;
                    $users[$key]['emailId'] = $values['emailID'];
                    $users[$key]['isCityFamUser'] = "0";
            }
        }
        response(1, $users, 'No Error Found.');
    } else {
        response(0, array(), 'No Invitation sent for this event.');
    }
}
?>