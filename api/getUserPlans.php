<?php

require 'config.php';
$data = $_GET;
$error = 1;
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
if (isset($data['status']) and $data['status'] != '') {
    $array = array(0, 1);
    if (!in_array($data['status'], $array)) {
        $error = 0;
    }
} else {
    $error = 0;
}
if (empty($error)) {
    response(0, array(), 'Please enter required fields.');
} else {
    $all_events = array();
    $allExpiredEvents = getAllExpiredEvents();
    $results = $wpdb->get_results('SELECT `event_id` FROM `wp_event_invitations` WHERE  `status`="1" and `userID`="' . $data['anotherUserId'] . '"');
    $upcoming_events = array();
    $past_events = array();
    if (!empty($results)) {
        $results = convert_array($results);
        foreach ($results as $k => $v) {
            if (!in_array($v['event_id'], $allExpiredEvents)) {
                $upcoming_events[] = $v['event_id'];
            } else {
                $past_events[] = $v['event_id'];
            }
        }
    }
    if ($data['status'] == 0) {//upcoming events
        if (!empty($upcoming_events)) {
            $args['post__in'] = $upcoming_events;
            $args['post_type'] = 'events';
            $all_events = get_posts($args);
        }
    } else {
        if (!empty($past_events)) {
            $args['post__in'] = $past_events;
            $args['post_type'] = 'events';
            $all_events = get_posts($args);
        }
    }
    if (!empty($all_events)) {
        if ($data['userId'] == $data['anotherUserId']) {
            $user_id = $data['userId'];
        } else {
            $user_id = $data['anotherUserId'];
        }
        $offset=20;
        $limit=20;
        if ($data['offset']==0 || !empty($data['offset']) and  $data['offset']!='') {
            $offset=20*$data['offset'];
            $limit=20;
        }
        $AllEvents = getParticularEvent($all_events, $user_id);
        $all = array();
        if (!empty($AllEvents)) {
            $date[] = 'test';
            foreach ($AllEvents as $kk => $vv) {
                $startDate = get_post_meta($vv['eventId'], 'eventStartTime')[0];
                $eventEndDate = date('l,F d', strtotime($startDate));
                $dated[] = trim($eventEndDate);
            }
            $dated = array_unique($dated);
            if (!empty($dated)) {
                $counterAttack=0;
                foreach ($dated as $ke => $val) {
                    foreach ($AllEvents as $key => $values) {
                        $customstartDate = get_post_meta($values['eventId'], 'eventStartTime')[0];
                        $customeventEndDate = date('l,F d', strtotime($customstartDate));
                        if ($val == $customeventEndDate) {
                            $all[$counterAttack]['day'] = $customeventEndDate;
                            $all[$counterAttack]['events'][] = $values;
                        }
                    }
                    $counterAttack++;
                }
            }
            response(1, $all, 'No Error Found.');
        } else {
            response(0, array(), 'No Error Found.');
        }
    } else {
        response(0, array(), 'No Events Found.');
    }
}
?>