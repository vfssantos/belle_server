<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of sendApplePush
 *
 * @author admin@3embed
 */
require_once 'ConDB.php';

class sendApplePush {

    private $db;                                        //Have database connection object
    private $apns_fp;                              //Have apns connection file pointer

    /**/
    public $token;                                    //Stores device token of the current user
    public $apns_con;                             //Have connection object for the apns server
    public $push_sent;                            //boolean, have push status.. sent or not
    public $tokenAvailable;                   //boolean, tells token available or not
    public $ownerName;                      //string, owner name who is sending the push/invitation
    public $groupName;                      //string, group/apartment name for which the invitation is being sent
    public $invitedUserName;            //string, name of inviting user
    public $userLoggedIn;                   //boolean, checks if the user loggedin in his device or not
    public $userIdPush;
    public $userDeviceType;

    /* Event invitee details */
    public $inviteeTokenAvailable;      //boolean, checks if the token of the invitee available or not
    public $inviteeToken;                        //Stores device token of the current invitee
    public $inviteeName;                        //string, invitee name who is sending the push/invitation

    /* Event details */
    public $eventTitle;                         //stores event title
    public $eventTime;                          //stores event time
    public $eventOwner;                         //stores owner of the event

    /* Chore details */
    public $choreTitle;                          //stores Chore title
    public $choreDate;                          //Stores chore date
    public $choreOwner;                         //stores chore owner

    /* Expense details */
    public $expTitle;                          //stores Chore title
    public $expOwner;                         //stores chore owner

    /* Push message and notification details */
    public $pushDetStored;                  //boolean, checks if the details of the push notification stored or not
    public $pushDetId;                          //Stores generated id, after storing push note details
    public $badgeCount;

    public function __construct() {

        $this->db = new ConDB(); //db connection object

        $passphrase = 'driver'; //certificate local private key

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', '../cert/ck.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        $this->apns_fp = stream_socket_client(
                'ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

        if (!$this->apns_fp)
            $this->apns_con = false;
        else
            $this->apns_con = true;
    }

    //check User Token for the provided user id and returns user details
    public function checkUserToken($userId) {
        $get_user_qry = "select id as userId,DeviceType,firstName as ownerName,Token,loginFlag from user where id ='" . $userId . "'";
        $user_res = mysql_query($get_user_qry, $this->db->conn);
        $user_row = mysql_fetch_assoc($user_res);
        if ($user_row['Token'] == '') {
            $this->tokenAvailable = false;
        } else {
            $this->tokenAvailable = true;
            $this->userIdPush = $user_row['userId'];
            $this->token = $user_row['Token'];
            $this->ownerName = $user_row['ownerName'];
            $this->userLoggedIn = $user_row['loginFlag'];
            $this->userDeviceType = $user_row['DeviceType'];
        }
    }

    public function getUserGrpDet($grpid) {
        $get_grp_qry = "select Name as groupName from roomiegroup where id ='" . $grpid . "'";
        $grp_res = mysql_query($get_grp_qry, $this->db->conn);
        $grp_row = mysql_fetch_assoc($grp_res);

        $this->groupName = $grp_row['groupName'];
    }

    public function getEventDet($event_id) {
        $get_event_qry = "select owner_id,title,DATE_FORMAT(eventDate,'%H:%i') as eventTime from events where id ='" . $event_id . "'";
        $event_res = mysql_query($get_event_qry, $this->db->conn);
        $event_row = mysql_fetch_assoc($event_res);

        $this->eventTitle = $event_row['title'];
        $this->eventTime = $event_row['eventTime'];
        $this->eventOwner = $event_row['owner_id'];
    }

    public function getChoreDet($chore_id) {
        $get_chore_qry = "select owner_id,title from chores where chore_id ='" . $chore_id . "'";
        $chore_res = mysql_query($get_chore_qry, $this->db->conn);
        $chore_row = mysql_fetch_assoc($chore_res);

        $this->choreTitle = $chore_row['title'];
        $this->choreOwner = $chore_row['owner_id'];
    }

    public function getExpenseDet($exp_id) {
        $get_exp_qry = "select owner_id,title from expenses where id ='" . $exp_id . "'";
        $get_exp_res = mysql_query($get_exp_qry, $this->db->conn);
        $exp_row = mysql_fetch_assoc($get_exp_res);

        $this->expTitle = $exp_row['title'];
        $this->expOwner = $exp_row['owner_id'];
    }

    //check invitee User Token for the provided user id and returns user details
    public function checkEventInviteeToken($userId) {
        $get_user_qry = "select id as userId,DeviceType,firstName,Token,loginFlag from user where id ='" . $userId . "'";
        $user_res = mysql_query($get_user_qry, $this->db->conn);
        $user_row = mysql_fetch_assoc($user_res);
        if ($user_row['Token'] == '') {
            $this->inviteeTokenAvailable = false;
        } else {
            $this->userIdPush = $user_row['userId'];
            $this->inviteeTokenAvailable = true;
            $this->inviteeToken = $user_row['Token'];
            $this->inviteeName = $user_row['firstName'];
            $this->userLoggedIn = $user_row['loginFlag'];
            $this->userDeviceType = $user_row['DeviceType'];
        }
    }

    public function getUserToken($userId) {
        $get_user_qry = "select id as userId,DeviceType,firstName,Token,loginFlag from user where id ='" . $userId . "'";
        $user_res = mysql_query($get_user_qry, $this->db->conn);
        $user_row = mysql_fetch_assoc($user_res);
        if ($user_row['Token'] == '') {
            $this->inviteeTokenAvailable = false;
        } else {
            $this->userIdPush = $user_row['userId'];
            $this->inviteeTokenAvailable = true;
            $this->inviteeToken = $user_row['Token'];
            $this->inviteeName = $user_row['firstName'];
            $this->userLoggedIn = $user_row['loginFlag'];
            $this->userDeviceType = $user_row['DeviceType'];
        }
    }

    //send push note, takes token and message
    public function sendAPush($token_arr, $message) {
        if ($this->userLoggedIn == '1') { //checkes if user is logged-in in the app or not, if logged in then sends push note
            if ($this->userDeviceType == '1') { //Checking if the user is an apple user or not
                //$this->getBadgeCount($this->userIdPush);
                $body['aps'] = array(
                    //'badge' => $this->badgeCount,
                    'alert' => $message,
                    'sound' => 'default'
                );

                $payload = json_encode($body);
// Build the binary notification
                //$deviceTokenArr = explode(',', $token_arr);
                $msg = '';
                foreach ($token_arr as $token) {
                    $msg .= chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;
                }

// Send it to the server
                $result = fwrite($this->apns_fp, $msg, strlen($msg));

                if (!$result) {
                    $this->push_sent = false;
                } else {
                    $this->push_sent = true;
                }
            } else if ($this->userDeviceType == '2') { //Checking if the user is an android user or not
//$url = 'https://android.googleapis.com/gcm/send'; // Use this if ssl is enabled, otherwise gives ssl certificate error
                $apiKey = 'AIzaSyAlG2e8FLyFIcHlVek42FbJjcmaREnuNZg';
                $url = 'http://android.googleapis.com/gcm/send';
                //$registrationIDs = array($token);
                $data = array("payload" => $message);

                $fields = array(
                    'registration_ids' => $token_arr,
                    'data' => $data,
                );

                $headers = array(
                    'Authorization: key=' . $apiKey,
                    'Content-Type: application/json'
                );
                // Open connection
                $ch = curl_init();

                // Set the url, number of POST vars, POST data
                curl_setopt($ch, CURLOPT_URL, $url);

                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                // Disabling SSL Certificate support temporarly
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

                // Execute post
                $result = curl_exec($ch);
                if ($result === FALSE) {
                    $this->push_sent = false;
                } else {
                    $this->push_sent = true;
                }

                // Close connection
                curl_close($ch);
//        echo $result;
            }
        } else {
            $this->push_sent = true;
        }
    }

    public function getInvitedUserDet($invitedUserId) {
        $get_user_qry = "select firstName,token from user where id ='" . $invitedUserId . "'";
        $user_res = mysql_query($get_user_qry, $this->db->conn);
        $user_row = mysql_fetch_assoc($user_res);
        $this->invitedUserName = $user_row['firstName'];
        $this->inviteeToken = $user_row['Token'];
//        $this->userIdPush = $invitedUserId;
    }

    //stores notification details into the database
    public function storePushDetails($notifTypeId, $owner_id, $reciever_id_arr, $msg, $grp_id, $entity_id, $notifStatus) {

        $det_stored = 0;

//        if($notifTypeId == '5' || $notifTypeId == '8' || $notifTypeId == '9' || $notifTypeId == '11'){
//            $notif_status = ',notifStatus';
//            $notif_status_val = ',1';
//        }else{
//            $notif_status = '';
//            $notif_status_val = '';
//        }


        foreach ($reciever_id_arr as $reciever_id) {
            $insert_notif_qry = "insert into notifications(type_id,owner_id,reciever_id,datetime,message,group_id,entity_id,notifStatus) 
                                        values('" . $notifTypeId . "','" . $owner_id . "','" . $reciever_id . "',NOW(),'" . $msg . "','" . $grp_id . "','" . $entity_id . "','" . $notifStatus . "')";
            mysql_query($insert_notif_qry, $this->db->conn);
            $insertedId = mysql_insert_id();
            if (mysql_affected_rows() > 0) {
                $det_stored++;
            }
        }

        //$insertedId = mysql_insert_id();

        if ($det_stored == count($reciever_id_arr)) {
            $this->pushDetStored = true;
            $this->pushDetId = $insertedId;
        } else {
            $this->pushDetStored = false;
        }
    }

    public function getBadgeCount($userId) {
        $getBadgesQry = "select count(id) as badgeCount from Notifications where notifStatus = 3 and reciever_id = " . $userId;
        $getBadgesRes = mysql_query($getBadgesQry, $this->db->conn);
        $badgeRow = mysql_fetch_assoc($getBadgesRes);
        if ($badgeRow['badgeCount'] == 0) {
            $this->badgeCount = $badgeRow['badgeCount'];
        } else {
            $this->badgeCount = $badgeRow['badgeCount'] + 1;
        }
    }

    public function setCurrentDeviceType($devType) {
        $this->userDeviceType = $devType;
        return $this->userDeviceType;
    }

    public function closeServerConn() {
        fclose($this->apns_fp); //close connection with the apple server
    }

}

?>
