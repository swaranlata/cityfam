<?php

require 'config.php';
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
$error = 1;
if (empty($data['name'])) {
    $error = 0;
}
if (empty($data['emailId'])) {
    $error = 0;
}
if (empty($data['password'])) {
    $error = 0;
}
$password = $data['password'];
if (empty($error)) {
    response(0, null, 'Please enter required fields.');
}
if (!empty($data['facebookId'])) {
    $user_details = get_user_by_email($data['emailId']);
    $user_details = json_decode(json_encode($user_details), TRUE);
    if (!empty($user_details)) {
        $user_id = $user_details['data']['ID'];
        $user_data = get_user_meta($user_id, 'facebookId', true);
        $userPostedBy = convert_array(get_user_by('id', $user_id));
        $image = get_user_meta($user_id, 'user_image', true);
        $user_image = '';
        if (!empty($image)) {
            $user_image = get_post_field('guid', $image);
        }
        $finalArray = array(
            'userId' => "$user_id",
            'userImageUrl' => $user_image,
            'userName' => $userPostedBy['data']['display_name'],
        );
        response(1, $finalArray, 'No Error Found');
        /*  if(!empty($user_data) and $data['facebookId']==$user_data){
          response(1, "$user_id", 'No Error Found');
          }else{
          response(0, null, 'Invalid Credentials.');
          } */
    }
} elseif (!empty($data['googleId'])) {
    $user_details = get_user_by_email($data['emailId']);
    $user_details = json_decode(json_encode($user_details), TRUE);
    if (!empty($user_details)) {
        $user_id = $user_details['data']['ID'];
        $user_data = get_user_meta($user_id, 'googleId', true);
        $userPostedBy = convert_array(get_user_by('id', $user_id));
        $image = get_user_meta($user_id, 'user_image', true);
        $user_image = '';
        if (!empty($image)) {
            $user_image = get_post_field('guid', $image);
        }
        $finalArray = array(
            'userId' => "$user_id",
            'userImageUrl' => $user_image,
            'userName' => $userPostedBy['data']['display_name'],
        );
        response(1, $finalArray, 'No Error Found');
        /* if(!empty($user_data) and $data['googleId']==$user_data){
          response(1, "$user_id", 'No Error Found');
          }else{
          response(0, null, 'Invalid Credentials.');
          } */
    }
} else {
    if (!filter_var($data['emailId'], FILTER_VALIDATE_EMAIL)) {
        response(0, null, 'Please enter valid email address.');
    }
    if (email_exists($data['emailId'])) {
        response(0, null, 'Email already exists.');
    }
}
$username = strtolower(substr($data['name'], 0, 4)) . '_' . randomString(4);
$user_id = wp_create_user($username, $data['password'], $data['emailId']);
wp_update_user(array('ID' => $user_id, 'display_name' => $data['name']));
update_user_meta($user_id, "name", $data['name']);
update_user_meta($user_id, "phone", $data['phone']);
update_user_meta($user_id, "latitude", $data['latitude']);
update_user_meta($user_id, "longitude", $data['longitude']);
update_user_meta($user_id, "address", $data['address']);
update_user_meta($user_id, "deviceToken", $data['deviceToken']);
update_user_meta($user_id, "deviceType", $data['deviceType']);
update_user_meta($user_id, "googleId", $data['googleId']);
update_user_meta($user_id, "facebookId", $data['facebookId']);
if (!empty($data['profilePicBase64'])) {
    $profilePicBase64 = $data['profilePicBase64'];
    $directory = "/" . date(Y) . "/" . date(m) . "/";
    $wp_upload_dir = wp_upload_dir();
    $data = base64_decode($profilePicBase64);
    $filename = time() . ".png";
    $fileurl = "../wp-content/uploads" . $directory . $filename;
    $filetype = wp_check_filetype(basename($fileurl), null);
    file_put_contents($fileurl, $data);
    $attachment = array(
        'guid' => $wp_upload_dir['url'] . '/' . basename($fileurl),
        'post_mime_type' => $filetype['type'],
        'post_title' => preg_replace('/\.[^.]+$/', '', basename($fileurl)),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment($attachment, $fileurl, $postId);
    require_once('../wp-admin/includes/image.php' );
    $attach_data = wp_generate_attachment_metadata($attach_id, $fileurl);
    wp_update_attachment_metadata($attach_id, $attach_data);
    update_user_meta($user_id, 'user_image', $attach_id);
}
$from = 'admin@cityfam.com';
$headers = 'From: ' . $from . "\r\n";
$subject = "Registration successful";
$msg = "Hello\n '" . $data['name'] . "'\n Registration successful.\nYour login details\nUsername: $username\nPassword: $password\n Thanks,\n CityFam Team";
wp_mail($data['emailId'], $subject, $msg, $headers);
$userPostedBy = convert_array(get_user_by('id', $user_id));
$image = get_user_meta($user_id, 'user_image', true);
$user_image = '';
if (!empty($image)) {
    $user_image = get_post_field('guid', $image);
}
$finalArray = array(
    'userId' => "$user_id",
    'userImageUrl' => $user_image,
    'userName' => $userPostedBy['data']['display_name'],
);
$user_notification_setting=user_notification_setting($user_id);
response(1,$finalArray,'No Error Found');
?>