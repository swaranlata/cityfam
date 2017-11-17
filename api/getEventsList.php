<?php

require 'config.php';
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);

$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
$off=$data['offset'];
$data['offset']="$off";
if(!isset($data['offset'])){
  $error = 0;    
}
if(isset($data['offset']) and $data['offset']==''){
 $error = 0;  
}
if (empty($error)) {
    $resultS['notificationCount'] = "";
    $resultS['eventDetail'] = array();
    response(0, $resultS, 'Please enter required fields.');
} else {
    $resultS['notificationCount'] = "";
    $resultS['eventDetail'] = array();
    $checkAuthorisarion = AuthUser($data['userId'], $resultS);
    $userLat = '';
    if (isset($checkAuthorisarion[0]['metadata']['latitude'][0])) {
        $userLat = $checkAuthorisarion[0]['metadata']['latitude'][0];
    }
    $userLong = '';
    if (isset($checkAuthorisarion[0]['metadata']['longitude'][0])) {
        $userLong = $checkAuthorisarion[0]['metadata']['longitude'][0];
    }
    $offset=20;
    if ($data['offset']==0 || !empty($data['offset'])) {
        $offset=20*$data['offset'];
    }
    $args['post_type'] = 'events';
    $args['orderby'] = 'date';
    $args['order'] = 'desc';
    $args['posts_per_page'] = -1;
    //$args['offset'] =$offset;
    $args['post_status'] = 'publish';
    if ($data['type'] == 1) {
        /* Event Created By Friends */
        $userFriends = getMyFriends($data['userId'], 'false');
        if (!empty($userFriends)) {
            $args['author__in'] = $userFriends;
            $all_events = get_posts($args);
        } else {
            $all_events = array();
        }
    } else {
        /* All Events */
        $all_events = get_posts($args);
    }
    $events = array();
    $allEventsAfterFilter = array();
    if (!empty($all_events)) {
        $all_events = convert_array($all_events);
        foreach ($all_events as $key => $value) {
            $latitude = '';
            $longitude = '';
            $eventStartTime = '';
            $eventEndTime = '';
            if (!empty(get_post_meta($value['ID'], 'eventEndTime'))) {
                $eventEndTime = get_date_format(get_post_meta($value['ID'], 'eventEndTime')[0], 'time');
                $eventEndDate = get_date_format(get_post_meta($value['ID'], 'eventEndTime')[0], 'eventDate');
                $getEndTime = get_post_meta($value['ID'], 'eventEndTime')[0];
                if (strtotime($getEndTime) < time()) {
                    continue;
                }
            }
            if (!empty(get_post_meta($value['ID'], 'eventStartTime'))) {
                $eventStartTime = get_date_format(get_post_meta($value['ID'], 'eventStartTime')[0], 'time');
                $eventStartDate = get_date_format(get_post_meta($value['ID'], 'eventStartTime')[0], 'eventDate');
                $getStartTime = get_post_meta($value['ID'], 'eventStartTime')[0];                
            }
            if (!empty($data['filters']['distance'])) {
                if (!empty(get_post_meta($value['ID'], 'latitude'))) {
                    $latitude = get_post_meta($value['ID'], 'latitude')[0];
                }
                if (!empty(get_post_meta($value['ID'], 'latitude'))) {
                    $longitude = get_post_meta($value['ID'], 'longitude')[0];
                }
                $totalMiles = getDistanceBetweenPointsNew($latitude, $longitude, $userLat, $userLong, $unit = 'Mi');
                if ($totalMiles > $data['filters']['distance']) {
                    continue;
                }
            }
            if (!empty($data['filters']['daysOfWeek'])) {
                $weekArray = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
                if (strtolower($data['filters']['daysOfWeek']) == 'weekends') {
                    $weekArray = array('sat', 'sun');
                }
                if (strtolower($data['filters']['daysOfWeek']) == 'weekdays') {
                    $weekArray = array('mon', 'tue', 'thu', 'wed', 'fri');
                }
                $eventStartTime = '';
                if (!empty(get_post_meta($value['ID'], 'eventStartTime'))) {
                    $eventStartTime = date('D', strtotime(get_post_meta($value['ID'], 'eventStartTime')[0]));
                    if (!in_array(strtolower($eventStartTime), $weekArray)) {
                        continue;
                    }
                }
            }
            if (!empty($data['filters']['timeOfDay'])) {
                $StartTime = date('H', strtotime(get_post_meta($value['ID'], 'eventStartTime')[0]));
                if (strtolower($data['filters']['timeOfDay']) == 'day') {
                    if(!in_array($StartTime,array(4,5,6,7,8,9,10,11,12,13,14,15,16))){
                        continue; 
                    }
                }
                if (strtolower($data['filters']['timeOfDay']) == 'night') {
                    if(!in_array($StartTime,array(17,18,19,20,21,22,23,24,1,2,3))){
                        continue;   
                    }
                 }
            }
            if (!empty($data['filters']['categories'])) {
                $category = explode('|', $data['filters']['categories']);
                $Catresults = $wpdb->get_results('SELECT * FROM `wp_term_relationships` WHERE `object_id` = "' . $value['ID'] . '"');
                $resultsCat = convert_array($Catresults);
                $temp = 0;
                if (!empty($resultsCat)) {
                    foreach ($resultsCat as $key => $valu) {
                        if (in_array($valu['term_taxonomy_id'], $category)) {
                            $temp = 1;
                        }
                    }
                }
                if (empty($temp)) {
                    continue;
                }
            }
            $whoCanSee = get_post_meta($value['ID'], 'whoCanSee');
            if (isset($whoCanSee[0]) and ! empty($whoCanSee[0])) {
                if ($data['type'] == 0) {
                    /* if (strtolower($whoCanSee[0]) == 'friends'){ 
                      $userFriends = getMyFriends($data['userId'],'true');
                      if (!in_array($data['userId'], $userFriends)) {
                      continue;
                      }
                      } elseif (strtolower($whoCanSee[0]) == 'private') {
                      $getPrivateLists = get_post_meta($value['ID'], 'privateUserList');
                      if (!empty($getPrivateLists)) {
                      if (!in_array($data['userId'], $getPrivateLists)) {
                      continue;
                      }
                      }
                      }elseif(strtolower($whoCanSee[0])!= 'public') {
                      continue;
                      } */
              
                    if (strtolower($whoCanSee[0]) == 'public') {
                        
                    } else {
                        if ($data['userId'] != $value['post_author']) {
                           continue;     
                        }                                              
                    }
                } else {
                    if (strtolower($whoCanSee[0]) == 'friends') {
                        $userFriends = getMyFriends($data['userId'], 'true');
                        if (!in_array($data['userId'], $userFriends)) {
                            continue;
                        }
                    }
                    if (strtolower($whoCanSee[0]) == 'private') {
                        $getPrivateLists = get_post_meta($value['ID'], 'privateUserList');
                        if (!empty($getPrivateLists)) {
                            if (!in_array($data['userId'], $getPrivateLists)) {
                                continue;
                            }
                        }
                    }
                }
            }
            $allEventsAfterFilter[] = $value['ID'];
        }
    }
    if (!empty($allEventsAfterFilter)) {
        $args['include'] = $allEventsAfterFilter;
        $all_events = get_posts($args);
    } else {
        $all_events = array();
    }
    if (!empty($all_events)) {
        $events = getOptimiseFunctionForEvent($all_events, $data['userId'],$offset);
        if(!empty($events)){
            $result['notificationCount'] = "0";
            $result['eventDetail'] = $events;
            response(1, $result, 'No Error Found.');  
        }else{
            $result['notificationCount'] = "";
            $result['eventDetail'] = array();
            response(0, $result, 'No Events Found.'); 
        }
        
    } else {
        $result['notificationCount'] = "";
        $result['eventDetail'] = array();
        response(0, $result, 'No Events Found.');
    }
}
?>