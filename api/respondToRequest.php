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
$testStatus = 2;
if (isset($data['status']) and $data['status'] == '0' || $data['status'] == '1') {
    $testStatus = $data['status'];
}
$status = array(0, 1);
if (!in_array($testStatus, $status)) {
    $error = 0;
}
if (empty($error)) {
    response(0, null, 'Please enter required fields.');
} else {
    $checkAuthorisarion = AuthUser($data['userId'], 'string');
    if ($data['userId'] == $data['anotherUserId']) {
        response(0, null, 'You cant take action on yourself.');
    }
    $query = 'select * from `wp_friends` where `user_id`= "' . $data['anotherUserId'] . '" and `friend_id`="' . $data['userId'] . '"';
    $results = $wpdb->get_results($query);
    if (!empty($results)) {
        $results = convert_array($results);
        if ($data['status'] == 1) {
            $response = $wpdb->query('update `wp_friends` set `status`="1" where `id`="' . $results[0]['id'] . '"');
            if (!empty($response)) {
               /* $getEventsOfUser = $wpdb->get_results('select ID from `wp_posts` where `post_status`="publish" and `post_author`="' . $data['anotherUserId'] . '"');
                if (!empty($getEventsOfUser)) {
                    $getEventsOfUser = convert_array($getEventsOfUser);
                    foreach ($getEventsOfUser as $k => $v) {
                        $whoCanSee = get_post_meta($v['ID'], 'whoCanSee');
                        if (isset($whoCanSee[0]) and $whoCanSee[0]=='friends') {
                            $wpdb->query('insert into `wp_event_invitations`(`status`,`userID`,`name`,`emailID`,`event_id`) values("0","' . $data['userId'] . '","' . $checkAuthorisarion[0]['display_name'] . '","' . $checkAuthorisarion[0]['user_email'] . '","' .$v['ID'] . '")');
                        }
                    }
                } */
                response(1, $data['anotherUserId'], 'Friend request is accepted.');
            } else {
                response(0, null, 'record is not updated.');
            }
        } else {
            $query = 'delete from `wp_friends` where `id`="' . $results[0]['id'] . '"';
            $response = $wpdb->query($query);
            if (!empty($response)) {
               /*  $getEventsOfUser = $wpdb->get_results('select ID from `wp_posts` where `post_status`="publish" and `post_author`="' . $data['anotherUserId'] . '"');
                if (!empty($getEventsOfUser)) {
                    $getEventsOfUser = convert_array($getEventsOfUser);
                    foreach ($getEventsOfUser as $k => $v) {
                        $whoCanSee = get_post_meta($v['ID'], 'whoCanSee');
                        if (isset($whoCanSee[0]) and $whoCanSee[0]=='friends') {
                            $wpdb->query('delete from `wp_event_invitations` where `userID`="'.$data['userId'].'" and `event_id`="'.$v['ID'].'"');
                        }
                    }
                } */
                response(1, $data['anotherUserId'], 'Friend request is declined.');
            } else {
                response(0, null, 'record is not updated.');
            }
        }
    } else {
        response(0, null, 'User is not in friendlist.');
    }
}
?>