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
    response(0, array(), 'Please enter required fields.');
} else {
    $checkAuthorisarion = AuthUser($data['userId'],array());
    $getAllEvents = getAllEvents();
    if (!in_array($data['eventId'], $getAllEvents)) {
        response(0, array(), 'No Event Found.');
    }
    $users = array();
    $custom_query='';
    if ($data['offset']==0 || !empty($data['offset']) and  $data['offset']!='') {
        $offset=20*$data['offset'];
        $custom_query .= ' LIMIT 20  OFFSET ' .$offset;
    }
    $query = 'SELECT * FROM `wp_event_invitations` WHERE `event_id` = "' . $data['eventId'] . '" and `status`="1"'.$custom_query;
    $results = $wpdb->get_results($query);
    if(!empty($results)) {
        $results = convert_array($results);
        foreach ($results as $key => $values) {
            if(!empty($values['userID'])){
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
            } 
            }else{
               $users[$key]['userImageUrl'] = '';
                $users[$key]['userName'] = $values['name'];
                $users[$key]['userId'] = "";  
            }            
        }
        response(1, $users, 'No Error Found.');
    } else {
        response(0, array(), 'No users are attending this event.');
    }
}
?>