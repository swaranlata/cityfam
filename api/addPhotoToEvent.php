    <?php
    require 'config.php';
    $encoded_data = file_get_contents('php://input');
    $data = json_decode($encoded_data, true);
    $error = 1;
    global $wpdb;
    if (empty($data['userId'])) {
        $error = 0;
    }
    if (empty($data['eventId'])) {
        $error = 0;
    }
    if (empty($data['photo'])) {
        $error = 0;
    }
    if(!empty($error)){
        $checkAuthorisarion = AuthUser($data['userId'], 'string');
        $getAllEvents = getAllEvents();
        $getAllExpiredEvents= getAllExpiredEvents();
        if (in_array($data['eventId'], $getAllExpiredEvents)) {
            response(0, null, 'Expired Event.');
        }
        if (!in_array($data['eventId'], $getAllEvents)) {
            response(0, null, 'No Event Found.');
        }   
        $get_post=get_post($data['eventId']);
        if(!empty($get_post)){
            $get_post=convert_array($get_post);
            if($get_post['post_author']!=$data['userId']){
            response(0, null, 'You are not author of this event.');
            }
        }
        $post_id=$data['eventId'];
        $encode_image = $data['photo'];
        $directory = "/" . date(Y) . "/" . date(m) . "/";
        $wp_upload_dir = wp_upload_dir();
        $data = base64_decode($encode_image);
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
        $attach_id = wp_insert_attachment($attachment, $fileurl, $post_id);
        require_once('../wp-admin/includes/image.php' );
        $attach_data = wp_generate_attachment_metadata($attach_id, $fileurl);
        wp_update_attachment_metadata($attach_id, $attach_data);
        $event_images=get_post_meta($post_id,'more_images',TRUE);
        if(!empty($event_images)){
           $event_images=unserialize($event_images);
           $event_images[]=$attach_id;
           update_post_meta($post_id, 'more_images', serialize($event_images));   
        }else{
           $event_images[]=$attach_id; 
           add_post_meta($post_id, 'more_images', serialize($event_images));   
        } 
        $getInvitaionListForEvent=getInvitaionListForEvent($data['eventId']);
        if(!empty($getInvitaionListForEvent)){
            foreach($getInvitaionListForEvent as $key=>$value){
             $checkNotificationStatus=getNotificationStatusByUserId($value['userID'],4);
                if(!empty($checkNotificationStatus)){
                pushMessageNotification($value['userID'],'New photo has been to the  '.$get_post['post_title'].' event.');    
                }
             }
        }
        response(1, 1, 'No Error Found.'); 
        }else{
            response(0, null, 'Please enter the required fields.'); 
        }
    ?>
