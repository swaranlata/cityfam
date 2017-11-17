            <?php
            require 'config.php';
            $getAllEvents=getAllEvents();
            $getAllExpiredEvents=getAllExpiredEvents();
            $events=array();
            if(!empty($getAllEvents)){
             foreach($getAllEvents as $key=>$val){
               if(!in_array($val,$getAllExpiredEvents)){
                 $events[]=$val;
                } 
              }   
            }
            $currentDate=date('Y-m-d');
            if(!empty($events)){
               foreach($events as $kk=>$vv){
                 $getEvent=convert_array(get_post($vv));
                 $getStartTime = get_post_meta($vv,'eventStartTime',true);
                 $beforeTwoDays=date('Y-m-d',strtotime('-2 days',strtotime($getStartTime)));
                 $getInvitationList=getInvitaionListForEvent($vv);
                 if(strtotime($beforeTwoDays)==strtotime($currentDate)){
                    $getInvitationList=getInvitaionListForEvent($vv);
                        if(!empty($getInvitationList)){
                        foreach($getInvitationList as $key=>$val){
                            $checkNotificationStatus=getNotificationStatusByUserId($val['userID'],6);
                            if(!empty($checkNotificationStatus)){
                                pushMessageNotification($val['userID'],"2 days left for ".$getEvent['post_title']." event.");   
                            }
                        }
                        }  
                     }
                  }
               } 
            }
            ?>