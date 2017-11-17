<?php

require 'config.php';
global $wpdb;
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
$error = 1;
if (empty($data['userId'])) {
    $error = 0;
}
if (!empty($error)) {
    AuthUser($data['userId'],'string');
    $user_id = $data['userId'];
    if (!empty($data['name'])) {
        update_user_meta($data['userId'], 'name', $data['name']);
        wp_update_user(array('ID' => $data['userId'], 'display_name' => $data['name']));
    }
    if (!empty($data['latitude'])) {
        update_user_meta($data['userId'], 'latitude', $data['latitude']);
    }
    if (!empty($data['longitude'])) {
        update_user_meta($data['userId'], 'longitude', $data['longitude']);
    }
    if (!empty($data['address'])) {
        update_user_meta($data['userId'], 'address', $data['address']);
    }
    if (!empty($data['userImageString'])) {
        $profilePicBase64 = $data['userImageString'];
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
        $attach_id = wp_insert_attachment($attachment, $fileurl, @$postId);
        require_once('../wp-admin/includes/image.php' );
        $attach_data = wp_generate_attachment_metadata($attach_id, $fileurl);
        wp_update_attachment_metadata($attach_id, $attach_data);
        update_user_meta($user_id, 'user_image', $attach_id);
    }
    response(1, 1, 'No Error Found.');
} else {
    response(0, null, 'Please enter the required fields.');
}
?>