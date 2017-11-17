<?php

require 'config.php';
$data = $_GET;
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
if ($data['offset']<0 || $data['offset']=='') 
{
    $error = 0;
}
if (empty($error)) {
    $resultData['invitationCount'] = '';
    $resultData['eventDetail'] = array();
    response(0, $resultData, 'Please enter required fields.');
} else {
    $resultData['invitationCount'] = '';
    $resultData['eventDetail'] = array();
    $checkAuthorisarion = AuthUser($data['userId'], $resultData);
    $offset=20;
    $limit=20;
    if ($data['offset']==0 || !empty($data['offset']) and  $data['offset']!='') {
        $offset=20*$data['offset'];
        $limit=20;
    }
    $countAllEvent = 'SELECT `event_id` FROM `wp_event_invitations` WHERE `userID`="' . $data['userId'] . '" and `status`="0"';
    $getInvitedEventsCount = $wpdb->get_results($countAllEvent);
    $query = 'SELECT `event_id` FROM `wp_event_invitations` WHERE `userID`="' . $data['userId'] . '" and `status`="0" limit '.$offset.','.$limit;
    $getInvitedEvents = $wpdb->get_results($query);
    $all_events = array();
    if (!empty($getInvitedEvents)) {
        $getInvitedEvents = convert_array($getInvitedEvents);
        if (!empty($getInvitedEvents)) {
            foreach ($getInvitedEvents as $key => $value) {
                $all_events[] = $value['event_id'];
            }
        }
        $all_events=array_unique($all_events);
    }
    if (empty($all_events)) {
        $result['invitationCount'] = "";
        $result['eventDetail'] = array();
        response(0, $result, 'No Events Found.');
    } else {
        $args['post_type'] = 'events';
        $args['orderby'] = 'date';
        $args['order'] = 'desc';
        $args['posts_per_page'] = -1;
        $args['post_status'] = 'publish';
        $args['include'] = $all_events;
        $all_events = get_posts($args);
        $events = getOptimiseFunctionForEvent($all_events, $data['userId'],-1);
        $result['invitationCount'] = count($getInvitedEventsCount);
        $result['eventDetail'] = $events;
        response(1, $result, 'No Error Found.');
    }
}
?>