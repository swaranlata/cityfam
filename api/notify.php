    <?php
    require 'config.php';
 

    $title = "CityFam API";
    $description = 'Welcome to CityFam';
    $url = 'https://fcm.googleapis.com/fcm/send';
    //api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
    $server_key='AAAAX6rjzgk:APA91bGwuMKikxiyioJMR66OnQED_HCtvFum-7fhhwOt7tMZ5qONIXPfMRa3Van9eozURYOuWpRyHfT1okyaUxWyFdg0klSdDgJPk3rONdTqJ-YYCkqyI1wldjeKkWpaHeDu_C0vkM2K';
    $fields = array (
      'to' => 'f9LFgYSTCvE:APA91bH70hRkd3DHWA6NOgPPDQnnZEhwjBstxvfEgyloQu3HUVBKSFiKiEPPVTEqPspah99nA7FANN4ro2McMbTDFGCfnFQRsIGFP_213MzI8m8yp-kTCjN8a_53g8zCJclhlJPjSsOR',
      "content_available"  => true,
      "priority" =>  "high",
      'notification' => array( 
            "sound"=>  "default",
            "badge"=>  "12",
            'title' => "$title",
            'body' => "$description",
        )
    );
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
    echo "<pre>";
    print_r($result);
    die;
    if ($result === FALSE) {
    die('FCM Send Error: ' . curl_error($ch));
    }
    curl_close($ch);
    return $result;
        //FCM api URL	
        $url = 'https://fcm.googleapis.com/fcm/send';
        //api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
        $server_key='AAAAX6rjzgk:APA91bGwuMKikxiyioJMR66OnQED_HCtvFum-7fhhwOt7tMZ5qONIXPfMRa3Van9eozURYOuWpRyHfT1okyaUxWyFdg0klSdDgJPk3rONdTqJ-YYCkqyI1wldjeKkWpaHeDu_C0vkM2K';
        //header with content_type api key
        $fields = array (
          'to' => 'fQzBlAmBswc:APA91bFhclRzNfjyq3NO7TWXKD0_BFVFsn22ycyWxl7ICPGCCOjOJEHY9hm-fLa4gzOm3xjCe0qO8B5mLxbJl0hLWTIR14OAy-XSVRaiXMSM2iGSNLYMGmbegMMWfgtQvZhsKZBfFU_p',
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
        echo "<pre>";
        print_r($result);
        die;
        if ($result === FALSE) {
        die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;


    ?>