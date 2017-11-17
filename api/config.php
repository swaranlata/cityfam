        <?php        
        require '../wp-config.php';
        date_default_timezone_set("Asia/Calcutta");
        $encoded_data = file_get_contents('php://input');
        $data = json_decode($encoded_data, true);
        function pr($array = null) {
            echo "<pre>";
            print_r($array);
            echo "</pre>";
            die;
        }

        function response($success = null, $result = null, $error = null) {
            echo json_encode(array(
                'success' => $success,
                'result' => $result,
                'error' => $error));
            die;
        }

        function getGroupMember($group_id=null,$type=null){
             global $wpdb;
            $results=$wpdb->get_results('select * from `wp_group_members` where `groupId`="'.$group_id.'"');
            if($type=='count'){
              $count=count($results);  
            }else{
               $count=$results;
            }
            return $count;

        }

        function getGroupMemberDetail($group_id=null){
            global $wpdb;
            $results=$wpdb->get_results('select * from `wp_group_members` where `groupId`="'.$group_id.'"');
            $members=array();
            if(!empty($results)){
                $results=convert_array($results);
                foreach($results as $key=>$val){   
                    if(getUserByEmail($val['email'])){
                       $members[]=getUserByEmail($val['email']); 
                    }else{
                       $members[]=array(
                           'user_email'=>$val['email'],
                           'display_name'=>'',
                           'id'=>'',
                       ); 
                    }

                }   
            }
            if(!empty($members)){
                $members=convert_array($members);
            }
            return $members;  
        }

        function getUserByEmail($email=null){
            global $wpdb;
            $results=$wpdb->get_row('select `id`,`user_email`,`display_name` from `wp_users` where `user_email`="'.$email.'"');
            return $results; 
        }

        function randomString($length = 6) {
            $str = "";
            $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
            $max = count($characters) - 1;
            for ($i = 0; $i < $length; $i++) {
                $rand = mt_rand(0, $max);
                $str .= $characters[$rand];
            }
            return $str;
        }

        function Generate_Featured_Image($image_url, $post_id) {
            $upload_dir = wp_upload_dir();
            $image_data = file_get_contents($image_url);
            $filename = basename($image_url);
            if (wp_mkdir_p($upload_dir['path']))
                $file = $upload_dir['path'] . '/' . $filename;
            else
                $file = $upload_dir['basedir'] . '/' . $filename;
            file_put_contents($file, $image_data);
            $wp_filetype = wp_check_filetype($filename, null);
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attach_id = wp_insert_attachment($attachment, $file, $post_id);
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $file);
            wp_update_attachment_metadata($attach_id, $attach_data);
            set_post_thumbnail($post_id, $attach_id);
            return $attach_id;
        }

        function user_id_exists($user) {
            global $wpdb;
            $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->users WHERE ID = %d", $user));
            if ($count == 1) {
                return 1;
            } else {
                return 0;
            }
        }

        function convert_array($array = null) {
            $finalArray = json_decode(json_encode($array), true);
            return $finalArray;
        }

        function get_date_format($date = null, $type = null) {
            $finalDate = strtotime($date);
            if ($type == 'eventDate') {
                return date('l,F d,Y', $finalDate);
            } elseif ($type == 'time') {
                return date('h:i A', $finalDate);
            } else {

            }
        }

        function getEventActionByUser($post_id = null, $action = null) {
            global $wpdb;
            if ($action == 0) {
                $results = $wpdb->get_results('SELECT * FROM `wp_event_invitations` WHERE `event_id` = "' . $post_id . '"');
            } else {
                $results = $wpdb->get_results('SELECT * FROM `wp_event_invitations` WHERE `event_id` = "' . $post_id . '" and `status`="' . $action . '"');
            }
            if (!empty($results)) {
                $count = count($results);
            } else {
                $count = "0";
            }
            return $count;
        }

        function getMyStatus($user_id = null, $post_id = null) {
            global $wpdb;
            $results = $wpdb->get_results('SELECT `status` FROM `wp_event_invitations` WHERE `event_id` = "' . $post_id . '" and `userID`="' . $user_id . '"');
              if (!empty($results)) {
                $results = convert_array($results);
                if ($results[0]['status'] == 0) {/* Invited */
                    return 'Invited';
                } elseif ($results[0]['status'] == 1) {/* accepted */
                    return 'Accept';
                } elseif ($results[0]['status'] == 2) {/* declined */
                    return 'Decline';
                } elseif ($results[0]['status'] == 3) {/* intersted */
                    return 'Interested';
                } else {
                    return '';
                }
            } else {
                return '';
            }
        }

        function AuthUser($id = null, $error_type = null, $array = null) {
            global $wpdb;
            $results = $wpdb->get_results('SELECT * FROM `wp_users` WHERE `ID`="' . $id . '"');
            $array = convert_array($results);
            if (!isset($array[0]['ID'])) {
                if ($error_type == 'string') {
                    response(0, null, 'You are not authorise to access this content.');
                } else {
                    response(0, $error_type, 'You are not authorise to access this content.');
                }
            } else {
                $array[0]['metadata'] = get_user_meta($array[0]['ID']);
                return $array;
            }
        }

        function getDistanceBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'Mi') {
            $theta = $longitude1 - $longitude2;
            $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
            $distance = acos($distance);
            $distance = rad2deg($distance);
            $distance = $distance * 60 * 1.1515;
            switch ($unit) {
                case 'Mi': break;
                case 'Km' : $distance = $distance * 1.609344;
            }
            return (round($distance, 2));
        }

        /* Events function */

        function getOptimiseFunctionForEvent($all_events = null, $data_user_id = null,$offset=null) {
            $data['userId'] = $data_user_id;
            $finalEventsIds=array();
            $events=array();
            if(!empty($all_events)){
              $all_events = convert_array($all_events);
              foreach($all_events as $k=>$v){
                  $finalEventsIds[]=$v['ID'];
              }  
            }            
            if(!empty($finalEventsIds)){
                $args['post_type'] = 'events';
                $args['orderby'] = 'date';
                $args['order'] = 'desc';
                if($offset==-1){
                   $args['posts_per_page'] = -1;
                }else{
                   $args['posts_per_page'] = 20;
                   $args['offset'] =$offset;   
                }
                $args['post_status'] = 'publish';            
                $args['include'] = $finalEventsIds;            
                $all_events = get_posts($args);
                if(!empty($all_events)){
                    $all_events = convert_array($all_events);
                    /* For Offset just get Event Ids and add offset in parameter */
                    foreach ($all_events as $key => $value) {
                        //$value['ID']=314;
                        $eventCreatedBy = convert_array(get_user_by('id', $value['post_author']));
                        if (!empty($eventCreatedBy['ID'])) {
                            /* start User Details */
                            $image = get_user_meta($eventCreatedBy['ID'], 'user_image', true);
                            $user_image = '';
                            if (!empty($image)) {
                                $user_image = get_post_field('guid', $image);
                            }
                            $events[$key]['userImageUrl'] = $user_image;
                            $events[$key]['userName'] = $eventCreatedBy['data']['display_name'];
                            $events[$key]['userId'] = $eventCreatedBy['data']['ID'];

                            if ($eventCreatedBy['data']['ID'] == $data['userId']) {
                                $events[$key]['userRole'] = 'Host';
                            } else {
                                $events[$key]['userRole'] = 'Normal User';
                            }
                            $events[$key]['userRole'] = 'Host';
                            /* end User Details */
                        }
                        $events[$key]['eventCoverImageUrl'] = '';
                        if (!empty(get_the_post_thumbnail_url($value['ID']))) {
                            $events[$key]['eventCoverImageUrl'] = get_the_post_thumbnail_url($value['ID']);
                        }
                        $coverArray = array();        
                        $getCoverImages=get_post_meta($value['ID'],'more_images',TRUE);
                        if(!empty($getCoverImages)){
                          $getCoverImages=unserialize($getCoverImages);
                            foreach($getCoverImages as $imgKey=>$imgVal){
                              $coverArray[]=wp_get_attachment_url($imgVal);
                            }
                        }else{
                            if (!empty($events[$key]['eventCoverImageUrl'])) {
                            $coverArray[] = $events[$key]['eventCoverImageUrl'];
                            }
                        }
                        $events[$key]['eventImagesUrlArray'] = $coverArray;
                        $eventName = '';
                        if (!empty($value['post_title'])) {
                            $eventName = $value['post_title'];
                        }
                        $events[$key]['eventName'] = $eventName;
                        $eventId = '';
                        if (!empty($value['ID'])) {
                            $eventId = $value['ID'];
                        }
                        $events[$key]['eventId'] = "$eventId";
                        $eventStartTime = '';
                        if (!empty(get_post_meta($value['ID'], 'eventStartTime'))) {
                            $eventStartTime = get_date_format(get_post_meta($value['ID'], 'eventStartTime')[0], 'time');
                            $eventStartDate = get_date_format(get_post_meta($value['ID'], 'eventStartTime')[0], 'eventDate');
                        }
                        $events[$key]['eventStartTime'] = $eventStartTime;
                        $events[$key]['eventStartDate'] = $eventStartDate;
                        $eventEndTime = '';
                        $eventEndDate = '';
                        if (!empty(get_post_meta($value['ID'], 'eventEndTime'))) {
                            $eventEndTime = get_date_format(get_post_meta($value['ID'], 'eventEndTime')[0], 'time');
                            $eventEndDate = get_date_format(get_post_meta($value['ID'], 'eventEndTime')[0], 'eventDate');
                        }
                        $events[$key]['eventEndTime'] = $eventEndTime;
                        $events[$key]['eventEndDate'] = $eventEndDate;
                        $eventAddress = '';
                        if (get_post_meta($value['ID'], 'placeName')[0]) {
                            $eventAddress = get_post_meta($value['ID'], 'placeName')[0];
                        }
                        $events[$key]['eventAddress'] = $eventAddress;
                        $latitude = '';
                        if (get_post_meta($value['ID'], 'latitude')[0]) {
                            $latitude = get_post_meta($value['ID'], 'latitude')[0];
                        }
                        $events[$key]['latitude'] = $latitude;
                        $longitude = '';
                        if (get_post_meta($value['ID'], 'latitude')[0]) {
                            $longitude = get_post_meta($value['ID'], 'longitude')[0];
                        }
                        $events[$key]['longitude'] = $longitude;
                        $events[$key]['description'] = $value['post_content'];
                        $ticketLink = '';
                        if (get_post_meta($value['ID'], 'ticketLink')[0]) {
                            $ticketLink = get_post_meta($value['ID'], 'ticketLink')[0];
                        }
                        $events[$key]['ticketLink'] = $ticketLink;
                        $canShareEvent = "0";
                        if (get_post_meta($value['ID'], 'allowGuestsToInviteOthers')[0]) {
                            $canShareEvent = get_post_meta($value['ID'], 'allowGuestsToInviteOthers')[0];
                        }
                        $events[$key]['canShareEvent'] = $canShareEvent;
                        $events[$key]['whoCanSee'] = get_post_meta($value['ID'], 'whoCanSee')[0];
                        if($events[$key]['whoCanSee']=='private'){
                            $groupIds=array();
                            if(isset(get_post_meta($value['ID'], 'invited_groups')[0]) and !empty(get_post_meta($value['ID'], 'invited_groups')[0])){
                               $groupIds=unserialize(get_post_meta($value['ID'], 'invited_groups')[0]); 
                            }
                            $events[$key]['privateUserList']['groupIds'] = $groupIds; 
                            $invited_emails=array();
                            if(isset(get_post_meta($value['ID'], 'invited_emails')[0]) and !empty(get_post_meta($value['ID'], 'invited_emails')[0])){
                               $invited_emails=unserialize(get_post_meta($value['ID'], 'invited_emails')[0]); 
                            }
                          $events[$key]['privateUserList']['groupIds'] = $groupIds;  
                          $events[$key]['privateUserList']['emailId'] = $invited_emails;  
                        }
                        $numberOfPeopleAttending = getEventActionByUser($value['ID'], 1);
                        $events[$key]['numberOfPeopleAttending'] = "$numberOfPeopleAttending";
                        $numberOfPeopleInvited = getEventActionByUser($value['ID'], 0);
                        if ($events[$key]['whoCanSee'] == 'public') {
                            $events[$key]['numberOfPeopleInvited'] = "0";
                            $events[$key]['numberOfPeopleInvited'] = getInvitedCountForPublic($value['ID']);
                        } else {
                            $events[$key]['numberOfPeopleInvited'] = "$numberOfPeopleInvited";
                        }
                        $numberOfPeopleInterested = getEventActionByUser($value['ID'], 3);
                        $events[$key]['numberOfPeopleInterested'] = "$numberOfPeopleInterested";
                        $commentsByPost = wp_count_comments($value['ID']);
                        if (!empty($commentsByPost)) {
                            $comments = $commentsByPost->approved;
                        }
                        $events[$key]['numberOfComments'] = "$comments";
                        $events[$key]['myStatus'] = getMyStatus($data['userId'], $value['ID']);
                    }   
                }else{
                    $events=array(); 
                }
                             
            }
            return $events;
        }

        function getParticularEvent($all_events = null,$user_id=null) {
            $data['userId']=$user_id;
            $all_events = convert_array($all_events);
            foreach ($all_events as $key => $value) {
                $eventCreatedBy = convert_array(get_user_by('id', $value['post_author']));
                if (!empty($eventCreatedBy['ID'])) {
                    /* start User Details */
                    $image = get_user_meta($eventCreatedBy['ID'], 'user_image', true);
                    $user_image = '';
                    if (!empty($image)) {
                        $user_image = get_post_field('guid', $image);
                    }
                    $events[$key]['userImageUrl'] = $user_image;
                    $events[$key]['userName'] = $eventCreatedBy['data']['display_name'];
                    $events[$key]['userId'] = $eventCreatedBy['data']['ID'];

                    if ($eventCreatedBy['data']['ID'] == $data['userId']) {
                        $events[$key]['userRole'] = 'Host';
                    } else {
                        $events[$key]['userRole'] = 'Normal User';
                    }
                    $events[$key]['userRole'] = 'Host';
                    /* end User Details */
                }
                $events[$key]['eventCoverImageUrl'] = '';
                if (!empty(get_the_post_thumbnail_url($value['ID']))) {
                    $events[$key]['eventCoverImageUrl'] = get_the_post_thumbnail_url($value['ID']);
                }
                $im="";
                $coverArray = array();        
                $getCoverImages=get_post_meta($value['ID'],'more_images',TRUE);
                if(!empty($getCoverImages)){
                  $getCoverImages=unserialize($getCoverImages);
                    foreach($getCoverImages as $imgKey=>$imgVal){
                        $im=wp_get_attachment_url($imgVal);
                      $coverArray[]=wp_get_attachment_url($imgVal);
                    }
                }else{
                    if(!empty($events[$key]['eventCoverImageUrl'])) {
                    $coverArray[] = $events[$key]['eventCoverImageUrl'];
                    }
                }
                if(empty($events[$key]['eventCoverImageUrl'])){
                 $events[$key]['eventCoverImageUrl']=$im; 
                }
                $events[$key]['eventImagesUrlArray'] = $coverArray;
                $eventName = '';
                if (!empty($value['post_title'])) {
                    $eventName = $value['post_title'];
                }
                $events[$key]['eventName'] = $eventName;
                $eventId = '';
                if (!empty($value['ID'])) {
                    $eventId = $value['ID'];
                }
                $events[$key]['eventId'] = "$eventId";
                $eventStartTime = '';
                if (!empty(get_post_meta($value['ID'], 'eventStartTime'))) {
                    $eventStartTime = get_date_format(get_post_meta($value['ID'], 'eventStartTime')[0], 'time');
                    $eventStartDate = get_date_format(get_post_meta($value['ID'], 'eventStartTime')[0], 'eventDate');
                }
                $events[$key]['eventStartTime'] = $eventStartTime;
                $events[$key]['eventStartDate'] = $eventStartDate;
                $eventEndTime = '';
                $eventEndDate = '';
                if (!empty(get_post_meta($value['ID'], 'eventEndTime'))) {
                    $eventEndTime = get_date_format(get_post_meta($value['ID'], 'eventEndTime')[0], 'time');
                    $eventEndDate = get_date_format(get_post_meta($value['ID'], 'eventEndTime')[0], 'eventDate');
                }
                $events[$key]['eventEndTime'] = $eventEndTime;
                $events[$key]['eventEndDate'] = $eventEndDate;
                $eventAddress = '';
                if (!empty(get_post_meta($value['ID'], 'placeName'))) {
                    $eventAddress = get_post_meta($value['ID'], 'placeName')[0];
                }
                $events[$key]['eventAddress'] = $eventAddress;
                $latitude = '';
                if (!empty(get_post_meta($value['ID'], 'latitude'))) {
                    $latitude = get_post_meta($value['ID'], 'latitude')[0];
                }
                $events[$key]['latitude'] = $latitude;
                $longitude = '';
                if (!empty(get_post_meta($value['ID'], 'latitude'))) {
                    $longitude = get_post_meta($value['ID'], 'longitude')[0];
                }
                $events[$key]['longitude'] = $longitude;
                $events[$key]['description'] = $value['post_content'];
                $ticketLink = '';
                if (!empty(get_post_meta($value['ID'], 'ticketLink'))) {
                    $ticketLink = get_post_meta($value['ID'], 'ticketLink')[0];
                }
                $events[$key]['ticketLink'] = $ticketLink;
                $canShareEvent = "0";
                if (!empty(get_post_meta($value['ID'], 'allowGuestsToInviteOthers'))) {
                    $canShareEvent = get_post_meta($value['ID'], 'allowGuestsToInviteOthers')[0];
                }
                $events[$key]['canShareEvent'] = $canShareEvent;
                $events[$key]['whoCanSee'] = get_post_meta($value['ID'], 'whoCanSee')[0];
                if($events[$key]['whoCanSee']=='private'){
                    $groupIds=array();
                    if(isset(get_post_meta($value['ID'], 'invited_groups')[0]) and !empty(get_post_meta($value['ID'], 'invited_groups')[0])){
                       $groupIds=unserialize(get_post_meta($value['ID'], 'invited_groups')[0]); 
                    }
                    $events[$key]['privateUserList']['groupIds'] = $groupIds; 
                    $invited_emails=array();
                    if(isset(get_post_meta($value['ID'], 'invited_emails')[0]) and !empty(get_post_meta($value['ID'], 'invited_emails')[0])){
                       $invited_emails=unserialize(get_post_meta($value['ID'], 'invited_emails')[0]); 
                    }
                  $events[$key]['privateUserList']['groupIds'] = $groupIds;  
                  $events[$key]['privateUserList']['emailId'] = $invited_emails;  
                }
                $numberOfPeopleAttending = getEventActionByUser($value['ID'], 1);
                $events[$key]['numberOfPeopleAttending'] = "$numberOfPeopleAttending";
                $numberOfPeopleInvited = getEventActionByUser($value['ID'], 0);
                if ($events[$key]['whoCanSee'] == 'public') {
                    $events[$key]['numberOfPeopleInvited'] = "0";
                    $events[$key]['numberOfPeopleInvited'] = getInvitedCountForPublic($value['ID']);
                } else {
                    $events[$key]['numberOfPeopleInvited'] = "$numberOfPeopleInvited";
                }
                $numberOfPeopleInterested = getEventActionByUser($value['ID'], 3);
                $events[$key]['numberOfPeopleInterested'] = "$numberOfPeopleInterested";
                $commentsByPost = wp_count_comments($value['ID']);
                if (!empty($commentsByPost)) {
                    $comments = $commentsByPost->approved;
                }
                $events[$key]['numberOfComments'] = "$comments";
                $events[$key]['myStatus'] = getMyStatus($data['userId'], $value['ID']);
            }
            return $events;
        }

        /* Get My friends */

        function getMyFriends($user_id = null, $type = null) {
            global $wpdb;
            $query = 'select * from `wp_friends` where (`user_id`= "' . $user_id . '" and  `status`="1") or (`friend_id`="' . $user_id . '"  and `status`="1")';
            $results = $wpdb->get_results($query);
            $friends = array();
            if (!empty($results)) {
                $results = convert_array($results);
                if ($type == 'true') {
                    $friends[] = $user_id;
                }
                foreach ($results as $key => $value) {
                    if ($value['user_id'] == $user_id) {
                        $friends[] = $value['friend_id'];
                    } else {
                        $friends[] = $value['user_id'];
                    }
                }
                return $friends;
            } else {
                return $friends;
            }
        }

        function checkMyFriend($userId = null, $anotherUserId = null) {
            global $wpdb;
            $query = 'select * from `wp_friends` where (`user_id`= "' . $userId . '" and  `friend_id`="' . $anotherUserId . '") or (`friend_id`= "' . $userId . '" and  `user_id`="' . $anotherUserId . '")';
            $results = $wpdb->get_results($query);
            $status = 0;
            if (!empty($results)) {
                $results = convert_array($results);
                if ($results[0]['status'] == 0) {
                    $status = 2;
                } elseif ($results[0]['status'] == 1) {
                    $status = 1;
                } else {
                    $status = 0;
                }
            }
            return $status;
        }

        function getAllEvents() {
            $args['post_type'] = 'events';
            $args['orderby'] = 'date';
            $args['order'] = 'desc';
            $args['posts_per_page'] = -1;
            $args['post_status'] = 'publish';
            $all_events = get_posts($args);
            $events = array();
            if (!empty($all_events)) {
                $all_events = convert_array($all_events);
                if (!empty($all_events)) {
                    foreach ($all_events as $key => $value) {
                        $events[] = $value['ID'];
                    }
                }
            }
            return $events;
        }

        function getAllExpiredEvents(){
            $args['post_type'] = 'events';
            $args['orderby'] = 'date';
            $args['order'] = 'desc';
            $args['posts_per_page'] = -1;
            $args['post_status'] = 'publish';
            $all_events = get_posts($args);
            $events = array();
            if (!empty($all_events)) {
                $all_events = convert_array($all_events);
                if (!empty($all_events)) {
                    foreach ($all_events as $key => $value) {
                        $eventStartTime = '';
                        if (!empty(get_post_meta($value['ID'], 'eventEndTime'))) {
                            $eventStartTime = get_date_format(get_post_meta($value['ID'], 'eventEndTime')[0], 'time');
                            $eventStartDate = get_date_format(get_post_meta($value['ID'], 'eventEndTime')[0], 'eventDate');
                            $getStartTime = get_post_meta($value['ID'], 'eventEndTime')[0];
                            if (strtotime($getStartTime) < time()) {
                               $events[] = $value['ID'];
                            }
                        }

                    }
                }
            }
            return $events;
        }

        function unReadMessagesCount($loginUserId=null,$userId=null,$type=null){
            global $wpdb;
            if($type=='private'){
                $type='1';
            }
            $getChat = $wpdb->get_results('select * from `wp_conversations` where `type`="'.$type.'" and `opponentUserId` ="'.$loginUserId.'" and `userId`="'.$userId.'" and `seen`="0"');
            $count=0;
            if(!empty($getChat)){
               $count=count($getChat); 
            }else{
               $count=0; 
            }
            return "$count";    
        }

        /* Add data into Activity Table */
        function activity($user_id=null,$type=null,$event_id=null,$anotherUserId=null){
            global $wpdb;
            $insertArray['user_id'] =$user_id;
            $insertArray['event_id'] =$event_id;
            $insertArray['type'] =$type;
            $insertArray['status'] ="0";
            $insertArray['created'] = date('Y-m-d H:i:s');
            if(!empty($anotherUserId)){
                $insertArray['receiver_id'] =$anotherUserId;
            }
            $wpdb->insert('wp_activities',$insertArray);  
        }

        function getSingleEvent($event_id=null){
             $value = convert_array(get_post($event_id));
             $eventCreatedBy = convert_array(get_user_by('id', $value['post_author']));
                if (!empty($eventCreatedBy['ID'])) {
                    /* start User Details */
                    $image = get_user_meta($eventCreatedBy['ID'], 'user_image', true);
                    $user_image = '';
                    if (!empty($image)) {
                        $user_image = get_post_field('guid', $image);
                    }
                    $events['userImageUrl'] = $user_image;
                    $events['userName'] = $eventCreatedBy['data']['display_name'];
                    $events['userId'] = $eventCreatedBy['data']['ID'];
                    $events['userRole'] = 'Host';
                    /* end User Details */
                }
                $events['eventCoverImageUrl'] = '';
                if (!empty(get_the_post_thumbnail_url($value['ID']))) {
                    $events['eventCoverImageUrl'] = get_the_post_thumbnail_url($value['ID']);
                }
                $coverArray = array();        
                $getCoverImages=get_post_meta($value['ID'],'more_images',TRUE);
                $im="";
                if(!empty($getCoverImages)){
                  $getCoverImages=unserialize($getCoverImages);
                    foreach($getCoverImages as $imgKey=>$imgVal){
                     $im=wp_get_attachment_url($imgVal);
                      $coverArray[]=wp_get_attachment_url($imgVal);
                    }
                }else{
                    if (!empty($events[$key]['eventCoverImageUrl'])) {
                    $coverArray[] = $events[$key]['eventCoverImageUrl'];
                    }
                }
                if(empty($events['eventCoverImageUrl'])){
                  $events['eventCoverImageUrl']=$im;  
                }
                $events['eventImagesUrlArray'] = $coverArray;
                $eventName = '';
                if (!empty($value['post_title'])) {
                    $eventName = $value['post_title'];
                }
                $events['eventName'] = $eventName;
                $eventId = '';
                if (!empty($value['ID'])) {
                    $eventId = $value['ID'];
                }
                $events['eventId'] = "$eventId";
                $eventStartTime = '';
                if (!empty(get_post_meta($value['ID'], 'eventStartTime'))) {
                    $eventStartTime = get_date_format(get_post_meta($value['ID'], 'eventStartTime')[0], 'time');
                    $eventStartDate = get_date_format(get_post_meta($value['ID'], 'eventStartTime')[0], 'eventDate');
                }
                $events['eventStartTime'] = $eventStartTime;
                $events['eventStartDate'] = $eventStartDate;
                $eventEndTime = '';
                $eventEndDate = '';
                if (!empty(get_post_meta($value['ID'], 'eventEndTime'))) {
                    $eventEndTime = get_date_format(get_post_meta($value['ID'], 'eventEndTime')[0], 'time');
                    $eventEndDate = get_date_format(get_post_meta($value['ID'], 'eventEndTime')[0], 'eventDate');
                }
                $events['eventEndTime'] = $eventEndTime;
                $events['eventEndDate'] = $eventEndDate;
                $eventAddress = '';
                if (!empty(get_post_meta($value['ID'], 'placeName'))) {
                    $eventAddress = get_post_meta($value['ID'], 'placeName')[0];
                }
                $events['eventAddress'] = $eventAddress;
                $latitude = '';
                if (get_post_meta($value['ID'], 'latitude')[0]) {
                    $latitude = get_post_meta($value['ID'], 'latitude')[0];
                }
                $events['latitude'] = $latitude;
                $longitude = '';
                if (get_post_meta($value['ID'], 'longitude')[0]) {
                    $longitude = get_post_meta($value['ID'], 'longitude')[0];
                }
                $events['longitude'] = $longitude;
                $events['description'] = $value['post_content'];
                $ticketLink = '';
                if (!empty(get_post_meta($value['ID'], 'ticketLink'))) {
                    $ticketLink = get_post_meta($value['ID'], 'ticketLink')[0];
                }
                $events['ticketLink'] = $ticketLink;
                $canShareEvent = "0";
                if (!empty(get_post_meta($value['ID'], 'allowGuestsToInviteOthers'))) {
                    $canShareEvent = get_post_meta($value['ID'], 'allowGuestsToInviteOthers')[0];
                }
                $events['canShareEvent'] = $canShareEvent;
                $events['whoCanSee'] = get_post_meta($value['ID'], 'whoCanSee')[0];
                if($events[$key]['whoCanSee']=='private'){
                    $groupIds=array();
                    if(isset(get_post_meta($value['ID'], 'invited_groups')[0]) and !empty(get_post_meta($value['ID'], 'invited_groups')[0])){
                       $groupIds=unserialize(get_post_meta($value['ID'], 'invited_groups')[0]); 
                    }
                    $events['privateUserList']['groupIds'] = $groupIds; 
                    $invited_emails=array();
                    if(isset(get_post_meta($value['ID'], 'invited_emails')[0]) and !empty(get_post_meta($value['ID'], 'invited_emails')[0])){
                       $invited_emails=unserialize(get_post_meta($value['ID'], 'invited_emails')[0]); 
                    }
                  $events['privateUserList']['groupIds'] = $groupIds;  
                  $events['privateUserList']['emailId'] = $invited_emails;  
                }
                $numberOfPeopleAttending = getEventActionByUser($value['ID'], 1);
                $events['numberOfPeopleAttending'] = "$numberOfPeopleAttending";
                $numberOfPeopleInvited = getEventActionByUser($value['ID'], 0);
                if ($events['whoCanSee'] == 'public') {
                    $events['numberOfPeopleInvited'] = "0";
                    $events['numberOfPeopleInvited'] = getInvitedCountForPublic($value['ID']);
                } else {
                    $events['numberOfPeopleInvited'] = "$numberOfPeopleInvited";
                }
                $numberOfPeopleInterested = getEventActionByUser($value['ID'], 3);
                $events['numberOfPeopleInterested'] = "$numberOfPeopleInterested";
                $commentsByPost = wp_count_comments($value['ID']);
                if (!empty($commentsByPost)) {
                    $comments = $commentsByPost->approved;
                }
                $events['numberOfComments'] = "$comments";
                $events['myStatus'] = getMyStatus($data['userId'], $value['ID']);
                return $events;
            }
        /* Count Public Events */
        function getInvitedCountForPublic($event_id=null){
            global $wpdb;
            $getInvitation = $wpdb->get_results('select * from `wp_public_invitations` where `event_id`="'.$event_id.'"');
            $count=0;
            if(!empty($getInvitation)){
               $count=count($getInvitation); 
            }else{
               $count=0; 
            }
            return "$count";     
        }
        /* User Notification settings*/
        function user_notification_setting($user_id=null){
            $array=array(
                '1'=>'1',
                '2'=>'1',
                '3'=>'1',
                '4'=>'1',
                '5'=>'1',
                '6'=>'1',
                '7'=>'1',
                '8'=>'1',
            );
            $serialize=serialize($array);
            global $wpdb;
            $insertArray=array(
            'user_id'=>$user_id,
            'settings'=>$serialize,
            );
            $wpdb->insert('wp_user_notification_settings',$insertArray);   
         }

        function getTitleById($id=null){
            global $wpdb;
            $getNotificationSetting = $wpdb->get_row('select * from `wp_notification_settings` where `id`="'.$id.'"');  
            $title='';
            if(!empty($getNotificationSetting)){
                $getNotificationSetting=convert_array($getNotificationSetting);
                $title=$getNotificationSetting['title'];
                }
            return "$title";    
        }

        function getNotificationStatusByUserId($user_id=null,$titleId=null){
            global $wpdb;
            $getNotificationSetting = $wpdb->get_row('select `settings` from `wp_user_notification_settings` where `user_id`="'.$user_id.'"');  
            if(!empty($getNotificationSetting)){
                $getNotificationSetting=convert_array($getNotificationSetting);
                $settings=unserialize($getNotificationSetting['settings']);
                return $settings[$titleId];
            }              
        }

        function getInvitaionListForEvent($event_id=null){
           global $wpdb;
           $getInvitationList = $wpdb->get_results('select `userID` from `wp_event_invitations` where `event_id`="'.$event_id.'"');  
           if(!empty($getInvitationList)){
                 $getInvitationList=convert_array($getInvitationList);
           }
           return $getInvitationList;        
        }

        function getPersonInvolved($event_id=null){
            global $wpdb;
            $results = $wpdb->get_results('SELECT * FROM `wp_event_invitations` WHERE `event_id` = "' . $event_id . '" and `status`="1"'); 
            $array =array();
            if(!empty($results)){
                $results=convert_array($results);
                foreach($results as $key=>$val){
                    $image = get_user_meta($val['userID'], 'user_image', true);                     
                    if (!empty($image)) {
                       $user_image = get_post_field('guid', $image); 
                        if(empty($user_image)){
                            $user_image=get_site_url().'/api/default.jpg';   
                        }
                    }   
                    $array[]= $user_image;  
                }
            }
            return $array;            
        }

        /* start Push Notifications */
        function pushMessageNotification($user_id,$message) 
        {
            global $wpdb;
            $tokens = trim(get_user_meta($user_id,'deviceToken',true));
            $deviceName = get_user_meta($user_id,'deviceType',true);
            if(!empty($tokens))
            {
                if($deviceName=='android')
                {
                  $res=sendMessageAndroid($tokens,$message);
                  return $res;
                }else{
                  $res=sendMessageIos($tokens,$message);
                  return $res;
                }
            } 
        }

        function getAllUser(){
            global $wpdb;
            $results = $wpdb->get_results('SELECT `ID` FROM `wp_users`');
            $array = convert_array($results);
            return $array;
        }

        function sendMessageIos($token_id,$checkNotification)
        {
        $title = "CityFam API";
        $description = $checkNotification;
        //FCM api URL	
        $url = 'https://fcm.googleapis.com/fcm/send';
        //api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
        $server_key='AAAAX6rjzgk:APA91bGwuMKikxiyioJMR66OnQED_HCtvFum-7fhhwOt7tMZ5qONIXPfMRa3Van9eozURYOuWpRyHfT1okyaUxWyFdg0klSdDgJPk3rONdTqJ-YYCkqyI1wldjeKkWpaHeDu_C0vkM2K';
        //header with content_type api key
        $fields = array (
          'to' => $token_id,
          "content_available"  => true,
          "priority" =>  "high",
          'notification' => array( 
                "sound"=>  "default",
                "badge"=>  "12",
                'title' => "$title",
                'body' => "$description",
            )
        );
        //header with content_type api key
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key='.$server_key
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
        die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
        }


        function sendMessageAndroid($token_id,$checkNotification)
        {
        $title = "CityFam";
        $description = $checkNotification;

        //FCM api URL	
        $url = 'https://android.googleapis.com/gcm/send';
        //api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
        $server_key = 'AAAAX6rjzgk:APA91bGwuMKikxiyioJMR66OnQED_HCtvFum-7fhhwOt7tMZ5qONIXPfMRa3Van9eozURYOuWpRyHfT1okyaUxWyFdg0klSdDgJPk3rONdTqJ-YYCkqyI1wldjeKkWpaHeDu_C0vkM2K';

        //header with content_type api key
        $fields = array (
            'to' => $token_id,
            "content_available"  => true,
            "priority" =>  "high",
            'notification' => array( 
                "sound"=>  "default",
                "badge"=>  "12",
                'title' => "$title",
                'body' => "$description",
            )
        );
        //header with content_type api key
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key='.$server_key
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
        }
        /* end Push Notifications */
        ?>
