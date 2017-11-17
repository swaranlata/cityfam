<?php
require 'config.php';
$data = $_REQUEST;
$error = 1;
global $wpdb;
if (empty($data['userId'])) {
    $error = 0;
}
if ($data['offset']=='') {
    $error = 0;
}
if (empty($error)) {
    response(0,array(), 'Please enter required fields.');
} else {
    AuthUser($data['userId'],array());
    $custom_query = '';
    $new_query='`ID`!="'.$data['userId'].'" and';
    if (!empty($data['searchText'])) {
        $custom_query .= '`display_name` like "%' . $data['searchText'] . '%"';
    }
    if ($data['offset']==0 || !empty($data['offset']) and  $data['offset']!='') {
        $offset=20*$data['offset'];
        $custom_query .= 'LIMIT 20  OFFSET ' .$offset;
    }
    if(empty($custom_query)){
       $custom_query = '`ID`!="'.$data['userId'].'"'; 
    }
    $allUsers = array();
    $getQuery = 'select `ID` from `wp_users` where `ID`!="'.$data['userId'].'"';
    if(!empty($data['searchText']) and $data['offset']==0 || !empty($data['offset']) and  $data['offset']!=''){
      $getQuery = 'select `ID` from `wp_users` where ' . $new_query.$custom_query; 
    }elseif($data['offset']==0 || !empty($data['offset']) and  $data['offset']!=''){
      $getQuery = 'select `ID` from `wp_users` where `ID`!="'.$data['userId'].'" ' . $custom_query;   
    }
   
    $getResults = $wpdb->get_results($getQuery);
    if (!empty($getResults)) {
        $getResults = convert_array($getResults);
        foreach ($getResults as $key => $value) {
            $getUserDetail = convert_array(get_user_by('id', $value['ID']));
            if (!empty($getUserDetail['ID'])) {
                $image = get_user_meta($getUserDetail['ID'], 'user_image', true);
                $user_image = '';
                if (!empty($image)) {
                    $user_image = get_post_field('guid', $image);
                }
                $allUsers[$key]['userImageUrl'] = $user_image;
                $allUsers[$key]['userName'] = $getUserDetail['data']['display_name'];
                $allUsers[$key]['userId'] = $getUserDetail['data']['ID'];
                $allUsers[$key]['status'] = '0';
                $query='select * from `wp_friends` where (`user_id`="'.$data['userId'].'" and `friend_id`="' . $getUserDetail['ID'] . '") or (`user_id`="'.$getUserDetail['ID'].'" and `friend_id`="' .$data['userId']. '")';
                $result = $wpdb->get_results($query);   
                if (!empty($result)) {
                    $records = convert_array($result);
                    if (!empty($records)) {                           
                        if ($records[0]['status']==0) {
                            $allUsers[$key]['status'] = '1';
                        }
                        if ($records[0]['status']==1) {
                            $allUsers[$key]['status'] = '2';
                        }
                     }
                }
            }
        }
    }
    if (!empty($allUsers)) {
        response(1, $allUsers, 'No Error Found.');
    } else {
        response(1, $allUsers, 'No User Found.');
    }
}
?>