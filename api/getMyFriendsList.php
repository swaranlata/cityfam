<?php

require 'config.php';
global $wpdb;
$data = $_REQUEST;
$error = 1;
if (empty($data['userId'])) {
    $error = 0;
}
if ($data['offset']<0 || $data['offset']=='') 
{
    $error = 0;
}
if (!empty($error)) {
    AuthUser($data['userId'],array());
    $friends = array();
    $allUsers=array();
    $offset=20;
    $limit=20;
    if ($data['offset']==0 || !empty($data['offset']) and  $data['offset']!='') {
        $offset=20*$data['offset'];
        $limit=20;
    }
    $getFriends = $wpdb->get_results('select *  from `wp_friends` where (`user_id`="' . $data['userId'] . '" and `status`="1") or (`friend_id`="' . $data['userId'] . '" and `status`="1") limit '.$offset.','.$limit);
    if (!empty($getFriends)) {
        $getFriends = convert_array($getFriends);
        $allUsers = array();
        if (!empty($getFriends)) {
            foreach ($getFriends as $key => $val) {
                if ($val['user_id'] == $data['userId']) {
                    $userArray[] = $val['friend_id'];
                } else {
                    $userArray[] = $val['user_id'];
                }
            }
            $users_is = implode('","', $userArray);
        }
        $getUserByName = $wpdb->get_results('select * from `wp_users` where `ID` in ("' . $users_is . '") order by display_name asc');
        $array = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $finalArray = array();
        if (!empty($getUserByName)) {
            $getUserByName = convert_array($getUserByName);
            foreach ($getUserByName as $key => $value) {
                $getUserDetail = convert_array(get_user_by('id', $value['ID']));
                if (!empty($getUserDetail['ID'])) {
                    $test[]=$getUserDetail['ID'];
                    $image = get_user_meta($getUserDetail['ID'], 'user_image', true);
                    $address = get_user_meta($getUserDetail['ID'], 'address', true);
                    $user_image = '';
                    if (!empty($image)) {
                        $user_image = get_post_field('guid', $image);
                    }
                    $allUsers[$key]['userImageUrl'] = $user_image;
                    $allUsers[$key]['userName'] = $getUserDetail['data']['display_name'];
                    $allUsers[$key]['userId'] = $getUserDetail['data']['ID'];
                    $allUsers[$key]['emailId'] = $getUserDetail['data']['user_email'];
                   /* $first = strtoupper(substr($getUserDetail['data']['display_name'], 0, 1));
                    if (in_array(strtoupper($first), $array)) {
                        $finalArray['friends'][$first][] = $allUsers;
                    }*/
                }
            }
        }
        $customArray = array();
        if (!empty($allUsers)) {
//            $i = 0;
//            foreach ($finalArray['friends'] as $key => $value) {
//                $customArray[$i]['letters'] = $key;
//                $customArray[$i]['friends'] = $value;
//                $i++;
//            }
            response(1, $allUsers, 'No Error Found.');
        }else{
           response(0, $allUsers, 'No Friends Found.'); 
        }
         
    }else{
         response(0, array(), 'No Friends found.');
    }
} else {
    response(0, array(), 'Please enter the required fields.');
}
?>