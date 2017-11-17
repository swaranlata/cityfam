<?php
require 'config.php';
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
if (empty($data['contactsList'])) {
    $error = 0;
}
if (empty($error)) {
    response(0,array(), 'Please enter required fields.');
} else {
    $checkAuthorisarion = AuthUser($data['userId'],array());
    $results =$data['contactsList'];
	if (!empty($results)) {
        foreach ($results as $key => $value) {
            if (!empty($value)) {
                $getUserDetail = convert_array(get_user_by('email', $value['emailId']));
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
                    $users[$key]['emailId'] = $getUserDetail['data']['user_email'];
                    $users[$key]['isCityFamUser'] = "1";
                }else{
                    $users[$key]['userImageUrl'] = '';
                    $users[$key]['userName'] = $value['userName'];
                    $users[$key]['userId'] = "";
                    $users[$key]['emailId'] = $value['emailId'];
                    $users[$key]['isCityFamUser'] = "0";
            }
            }
        }
        response(1, $users, 'No Error Found.');
    } else {
        response(0, array(), 'No Users Found.');
    }
}
?>