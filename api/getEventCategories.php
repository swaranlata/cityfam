<?php
require 'config.php';
$encoded_data = file_get_contents('php://input');
$data = $_GET;
if (empty($data['userId'])) {
    response(0, array(), 'Please enter required fields.');
} else {
    $getResponse = user_id_exists($data['userId']);
    if (!empty($getResponse)) {
        $args = array(
            'type' => 'post',
            'child_of' => 0,
            'parent' => '',
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => 0,
            'hierarchical' => 1,
            'exclude' => '',
            'include' => '',
            'number' => '',
            'taxonomy' => 'event',
            'pad_counts' => false);
        $categories = get_categories($args);
        $finalCategory = array();
        if (!empty($categories)) {
            foreach ($categories as $key => $value) {
                $finalCategory[$key]['categoryName'] = $value->name;
                $finalCategory[$key]['categoryId'] = "$value->term_id";
            }
            response(1, $finalCategory, 'No Error Found.');
        } else {
            response(0,array(), 'No Categories Found.');
        }
    } else {
        response(0, array(), 'User is not authorised to access the categories.');
    }
}
?>