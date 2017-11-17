<?php

require 'config.php';
$data = $_REQUEST;
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
if (empty($data['anotherUserId'])) {
    $error = 0;
}
if ($data['offset']<0 || $data['offset']=='') 
{
    $error = 0;
}
if (empty($error)) {
    response(0, array(), 'Please enter required fields.');
}else {
    $checkAuthorisarion = AuthUser($data['userId'], array());
    $args['post_type'] = 'events';
    $args['orderby'] = 'title';
    $args['order'] = 'ASC';
    $args['post_status'] = 'publish';
    $args['author'] = $data['anotherUserId'];
    $args['posts_per_page'] = -1;
    $all_events = get_posts($args);
    $finalEvents = array();
    if (!empty($all_events)) {
        $all_events = convert_array($all_events);
        foreach ($all_events as $key => $value) {
            $latitude = '';
            $longitude = '';
            $eventStartTime = '';
            if (!empty(get_post_meta($value['ID'], 'eventEndTime'))) {
                $eventStartTime = get_date_format(get_post_meta($value['ID'], 'eventEndTime')[0], 'time');
                $eventStartDate = get_date_format(get_post_meta($value['ID'], 'eventEndTime')[0], 'eventDate');
                $getStartTime = get_post_meta($value['ID'], 'eventEndTime')[0];
                if (strtotime($getStartTime) < time()) {
                    continue;
                }
            }
            $finalEvents[]=$value;
        }
        $offset=20;
        $limit=20;
        if ($data['offset']==0 || !empty($data['offset']) and  $data['offset']!='') {
            $offset=20*$data['offset'];
            $limit=20;
        }
        $events = getOptimiseFunctionForEvent($finalEvents, $data['userId'],$offset);
        if(!empty($events)){
            response(1, $events, 'No Error Found.');
        }else{
              response(0, array(), 'No Events Found.');
        }
        
    } else {
        response(0, array(), 'No Events Found.');
    }
}
?>