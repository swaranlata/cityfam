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
if (empty($data['phone'])) {
    // $error = 0;
}
if (empty($data['password'])) {
    $error = 0;
}
if (empty($data['latitude'])) {
    //$error = 0;
}
if (empty($data['longitude'])) {
    //$error = 0;
}
if (empty($data['address'])) {
    // $error = 0;
}
if (empty($data['profilePicBase64'])) {
    //$error = 0;
}
if (empty($error)) {
    response(0, null, 'Please enter required fields.');
}
if (!empty($data['facebookId']) || !empty($data['googleId'])) {
    if (email_exists($data['emailId'])) {
        $user_details = get_user_by_email($data['emailId']);
        if (!empty($user_details)) {
            $user_data = json_decode(json_encode($user_details), TRUE);
            $user_id = $user_data['data']['ID'];
            response(1, "$user_id", 'No Error Found');
        }
    } else {
        $username = randomString(4).'_'. strtolower(str_replace('_', '', $data['name']));
        $user_id = wp_create_user($username, $data['password'], $data['emailId']);
        wp_update_user(array('display_name' => $data['name'],'ID' => $user_id ));
        update_user_meta($user_id, "name", $data['name']);
        update_user_meta($user_id, "phone", $data['phone']);
        update_user_meta($user_id, "latitude", $data['latitude']);
        update_user_meta($user_id, "longitude", $data['longitude']);
        update_user_meta($user_id, "address", $data['address']);
        update_user_meta($user_id, "deviceToken", $data['deviceToken']);
        update_user_meta($user_id, "deviceType", $data['deviceType']);
        update_user_meta($user_id, "googleId", $data['googleId']);
        update_user_meta($user_id, "facebookId", $data['facebookId']);
        response(1, "$user_id", 'No Error Found');
    }
} else {
    if (email_exists($data['emailId'])) {
        response(0, null, 'Email already exists.');
    }
    if (username_exists($data['name'])) {
        response(0, null, 'Username already exists.');
    }
    $username = randomString(4).'_'. strtolower(str_replace('_', '', $data['name']));
    $user_id = wp_create_user($username, $data['password'], $data['emailId']);
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
    response(1, "$user_id", 'No Error Found');
}

if (empty($data['facebookId']) and empty($data['googleId'])) {
    $from = 'admin@cityfam.com';
    $headers = 'From: ' . $from . "\r\n";
    $subject = "Registration successful";
    $msg = "Hello\n '" . $data['name'] . "'\n Registration successful.\nYour login details\nUsername: $username\nPassword: $password\n Thanks,\n CityFam Team";
    wp_mail($data['emailId'], $subject, $msg, $headers);
}
?>