<?php

require 'config.php';
$data = $_GET;
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
if (empty($data['eventId'])) {
    $error = 0;
}
if ($data['offset']<0 || $data['offset']=='') 
{
    $error = 0;
}
if (!empty($error)) {
    $checkAuthorisarion = AuthUser($data['userId'], 'array');
    $getAllevents=getAllEvents();
    if(!in_array($data['eventId'],$getAllevents)){
      response(0, array(), 'Event not found.');   
    }
    $offset=20;
    $limit=20;
    if ($data['offset']==0 || !empty($data['offset']) and  $data['offset']!='') {
        $offset=20*$data['offset'];
        $limit=20;
    }
    $comments = get_approved_comments($data['eventId']);
    $commentsQuery = 'select * from `wp_comments` where `comment_post_ID`="'.$data['eventId'].'" and `comment_approved`="1" order by comment_ID desc limit '.$offset.','.$limit;
    $comments=$wpdb->get_results($commentsQuery);
    $listComment = array();
    if (!empty($comments)) {
        $comments = convert_array($comments);   
       // krsort($comments);        
        foreach ($comments as $key => $value) {
            $eventCreatedBy = AuthUser($value['user_id'], 'array');
            $image = get_user_meta($value['user_id'], 'user_image', true);
            $user_image = '';
            if (!empty($image)) {
                $user_image = get_post_field('guid', $image);
            }
            $listComment[$key]['userImageUrl'] = $user_image;
            $listComment[$key]['userName'] = $eventCreatedBy[0]['display_name'];
            $listComment[$key]['userId'] = $eventCreatedBy[0]['ID'];
            $listComment[$key]['comment'] = $value['comment_content'];
            //$listComment[$key]['test_id'] = $value['comment_ID'];
            $time=strtotime($value['comment_date']);
            $listComment[$key]['timeElapsed'] = "$time";
        }
        $finalCommentList=array();
        if(!empty($listComment)){
            foreach($listComment as $k=>$v){
                $finalCommentList[]=$v;
            }
        }
        if(!empty($finalCommentList)){
           response(1,$finalCommentList, 'No Error Found'); 
        }else{
           response(0, array(), 'No Comments Found'); 
        }        
    } else {
        response(0, array(), 'No Comments Found');
    }
} else {
    response(0, array(), 'Please enter required fields.');
}
?>