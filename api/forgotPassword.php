<?php
require 'config.php';
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
if (!empty($data['emailId'])) {
    $user_details = get_user_by_email($data['emailId']);
    if (!empty($user_details)) {
        $user_data = json_decode(json_encode($user_details), TRUE);
        $from = 'admin@cityfam.com';
        $headers = 'From: ' . $from . "\r\n";
        $subject = "Forgot Password";
        $password = randomString(8);
        $user_id = $user_data['data']['ID'];
        wp_set_password($password,$user_id);
        $msg = "Hello\n " . $user_data['data']['display_name'] . "\n your password has been reset.Your new password is : $password\n Thanks,\n CityFam Team";
        wp_mail($data['emailId'], $subject, $msg, $headers);
        response(1, "$user_id", 'No Error Found.');
    }else{
         response(0, null, 'No User Found.');
    }
} else {
    response(0, null, 'Please enter your email.');
}
?>