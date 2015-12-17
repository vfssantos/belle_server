<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of getErrorMsg
 *
 * @author admin@3embed
 */
//require_once 'ConDB.php';

class getErrorMsg {

    public $errNo;
    public $errMsg;

    public function __construct($errNumber) {
        $db = new ConDB();
        $get_message_qry = "select * from statusmessages where sid ='" . $errNumber . "'";
        $message_res = mysql_query($get_message_qry, $db->conn);
        $message = mysql_fetch_assoc($message_res);
        
        $this->errId = $message['sid'];
        $this->errFlag = $message['statusNumber'];
        $this->errMsg = $message['statusMessage'];
        $db->close($db);
    }
}

?>
