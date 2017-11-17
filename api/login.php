<?php

require 'config.php';
$credentials = array();
$error = 1;
if (empty($data['emailId'])) {
    $error = 0;
}
if (empty($data['password'])) {
    $error = 0;
}
if (!empty($error)) {
    $credentials['user_login'] = $data['emailId'];
    $credentials['user_password'] = $data['password'];
    $response = wp_signon($credentials, $secure_cookie = '');
    if (isset($response->ID) and ! empty($response->ID)) {
        $userPostedBy = convert_array(get_user_by('id', $response->ID));
        $image = get_user_meta($response->ID, 'user_image', true);
        $user_image = '';
        if (!empty($image)) {
            $user_image = get_post_field('guid', $image);
        }
        response(1, array(
            'userId' => "$response->ID",
            'userImageUrl' =>$user_image,
            'userName' => $userPostedBy['data']['display_name'],
            'eventsAddToCalendar' => '0'
         ), 'No Error Found');
    } else {
        response(0, null, 'Please enter valid credentials.');
    }
} else {
    response(0, null, 'Please enter required fields.');
}
?>