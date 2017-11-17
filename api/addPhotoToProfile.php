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
    if (!empty($data['photo'])) {
        $profilePicBase64 = $data['photo'];
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
        $user_gallery=get_user_meta($user_id,'user_gallery_images',true);
        $user_gallery=  unserialize($user_gallery);
        if(!empty($user_gallery)){
            array_push($user_gallery,$attach_id);  
            $allGallery=$user_gallery;
          }else{
           $allGallery[] = $attach_id;
        }
        update_user_meta($user_id, 'user_gallery_images',serialize($allGallery));
    }
    response(1, 1, 'No Error Found.');
} else {
    response(0, null, 'Please enter the required fields.');
}
?>