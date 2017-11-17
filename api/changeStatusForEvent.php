<?php
require 'config.php';
global $wpdb;
$data = $_REQUEST;
$error = 1;
if (empty($data['userId'])) {
    $error = 0;
}
if (empty($data['eventId'])) {
    $error = 0;
}
if (empty($data['status'])) {
    $error = 0;
}
if (!empty($error)) {
    $userData = AuthUser($data['userId'], 'string');
    $userName=$userData[0]['display_name'];
    $statusArray = array('Accept', 'Decline', 'Interested');
    if (!in_array($data['status'], $statusArray)) {
        response(0, null, 'Invalid Event Action.');
    }
    if ($data['status'] == 'Accept') {
        $status = 1;
        $act='accept';
    } elseif ($data['status'] == 'Decline') {
        $status = 2;
         $act='decline';
    } elseif ($data['status'] == 'Interested') {
        $status = 3;
        $act='interested';
    }
    $getAllEvents = getAllEvents();
    $getAllExpiredEvents= getAllExpiredEvents();
    if (in_array($data['eventId'], $getAllExpiredEvents)) {
        response(0, null, 'Expired Event.');
    }
    if (!in_array($data['eventId'], $getAllEvents)) {
        response(0, null, 'No Event Found.');
    }    
    $result = $wpdb->get_results('select * from `wp_event_invitations` where `userID`="' . $data['userId'] . '" and `event_id`="' . $data['eventId'] . '"');
    $get_post=convert_array(get_post($data['eventId']));
    if (!empty($result)) {
        $result = convert_array($result);
        //$query = $wpdb->query('update `wp_event_invitations` set `status`="' . $status . '" where `id`="' . $result[0]['id'] . '"');
        $query = $wpdb->query('update `wp_event_invitations` set `status`="' . $status . '" where `userID`="' . $data['userId'] . '" and `event_id`="' . $data['eventId'] . '"');
        $activity=activity($data['userId'],$act,$data['eventId']);
         if ($data['status'] == 'Accept') {
            $checkNotificationStatus=getNotificationStatusByUserId($get_post['post_author'],3);
            if(!empty($checkNotificationStatus)){
                pushMessageNotification($get_post['post_author'],$userName.' accepted the invitation for '.$get_post['post_title'].' event.');
            }
          }
        if ($data['status'] == 'Decline') {
            $checkNotificationStatus=getNotificationStatusByUserId($get_post['post_author'],2);
            if(!empty($checkNotificationStatus)){
                pushMessageNotification($get_post['post_author'],$userName.' declined the invitation for '.$get_post['post_title'].' event.');
            }
          }
        response(1, $data['eventId'], 'Event Status has been changed.');
    } else {
        $query = $wpdb->query('insert into `wp_event_invitations`(`status`,`userID`,`name`,`emailID`,`event_id`) values("' . $status . '","' . $data['userId'] . '","' . $userData[0]['display_name'] . '","' . $userData[0]['user_email'] . '","' . $data['eventId'] . '")');
        $activity=activity($data['userId'],$act,$data['eventId']);
        if ($data['status'] == 'Accept') {
            $checkNotificationStatus=getNotificationStatusByUserId($get_post['post_author'],3);
            if(!empty($checkNotificationStatus)){
                pushMessageNotification($get_post['post_author'],$userName.' accepted the invitation for '.$get_post['post_title'].' event.');
            }
            if ($data['status'] == 'Decline') {
                $checkNotificationStatus=getNotificationStatusByUserId($get_post['post_author'],2);
                if(!empty($checkNotificationStatus)){
                    pushMessageNotification($get_post['post_author'],$userName.' declined the invitation for '.$get_post['post_title'].' event.');
                }
            }
            
         }
        response(1, $data['eventId'], 'Event Status has been changed.');
    }
} else {
    response(0, null, 'Please enter the required fields.');
}
?>