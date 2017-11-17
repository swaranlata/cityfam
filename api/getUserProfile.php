<?php

require 'config.php';
$data = $_REQUEST;
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
$userID = $data['userId'];
if (!empty($data['anotherUserId'])) {
    $userID = $data['anotherUserId'];
}
if (!empty($error)) {
    AuthUser($data['userId'],'string');
    $getUserDetail = convert_array(get_user_by('id', $userID));
    if (!empty($getUserDetail['ID'])) {
        $image = get_user_meta($getUserDetail['ID'], 'user_image', true);
        $address = get_user_meta($getUserDetail['ID'], 'address', true);
        $user_image = '';
        if (!empty($image)) {
            $user_image = get_post_field('guid', $image);           
        }
        $allUsers['userImageUrl'] = $user_image;
        $allUsers['userName'] = $getUserDetail['data']['display_name'];
        $allUsers['userEmailAddress'] = $getUserDetail['data']['user_email'];
        $allUsers['userId'] = $getUserDetail['data']['ID'];
        $allUsers['userAddress'] = $address;
        if ($userID == $data['userId']) {
            $allUsers['isMyFriend'] = 0;
        } else {
            $allUsers['isMyFriend'] = (int)checkMyFriend($data['userId'], $userID);
        }
        $multipleImages = array();
        $user_gallery_images = get_user_meta($getUserDetail['ID'], 'user_gallery_images', true);
        if (!empty($user_gallery_images)) {
            $gallery = unserialize($user_gallery_images);
            if (!empty($gallery)) {
                foreach ($gallery as $key => $value) {
                    $multipleImages[] = get_post_field('guid', $value);
                }
            }
        }
        $allUsers['userImagesArray'] = $multipleImages;
        response(1, $allUsers, 'No Error Found.');
    } else {
        response(0, null, 'No User Found.');
    }
} else {
    response(0, null, 'Please enter the required fields.');
}
?>