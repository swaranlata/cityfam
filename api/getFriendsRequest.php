<?php
require 'config.php';
$data = $_REQUEST;
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
if ($data['offset']<0 || $data['offset']=='') 
{
    $error = 0;
}
/* User Received Friend Request*/
if (!empty($error)) {
    AuthUser($data['userId'],array());
    $offset=20;
    $limit=20;
    if ($data['offset']==0 || !empty($data['offset']) and  $data['offset']!='') {
        $offset=20*$data['offset'];
        $limit=20;
    }
    $result = $wpdb->get_results('select * from `wp_friends` where `friend_id`="' . $data['userId'] . '" and `status`="0" limit '.$offset.','.$limit);   
    $i = 0;
    $friends = array();
    if (!empty($result)) {
        $records = convert_array($result);
        foreach ($records as $key => $value) {
            $getUserDetail = convert_array(get_user_by('id', $value['user_id']));
            if (!empty($getUserDetail['ID'])) {
                $image = get_user_meta($getUserDetail['ID'], 'user_image', true);
                $user_image = '';
                if (!empty($image)) {
                    $user_image = get_post_field('guid', $image);
                }
                $friends[$key]['userImageUrl'] = $user_image;
                $friends[$key]['userName'] = $getUserDetail['data']['display_name'];
                $friends[$key]['userId'] = $getUserDetail['data']['ID'];
            }
        }
        response(1, $friends, 'No Error Found.');
    }else{
        response(0, $friends, 'No Data Found.');
    }
} else {
    response(0,array(), 'Please enter the required fields.');
}
?>
