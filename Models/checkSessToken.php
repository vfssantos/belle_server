<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of checkSessToken
 *
 * @author admin@3embed
 */
require_once 'ConDB.php';

class checkSessToken {

    public $db;
    public $sessFlag;
    public $sessExp;
    public $test_data = '';

    public function __construct($userId, $inputToken) {
        $this->db = new ConDB();
        $getTokenQry = "select sessToken from user where id='" . $userId."' and expDate > NOW()";
        $getTokenRes = mysql_query($getTokenQry, $this->db->conn);
        if (mysql_num_rows($getTokenRes) > 0) {
            $tokenRow = mysql_fetch_assoc($getTokenRes);
            if ($inputToken == $tokenRow['sessToken']) {
                $this->sessFlag = true;
                $this->updateSession($userId);
            } else {
                $this->sessFlag = false;
                //$this->test_data.= '<br>'.$inputToken.'<br>'.$tokenRow['sessToken'];
            }
            $this->sessExp = false;
        } else {
            $this->sessExp = true;
        }
    }
    
    public function updateSession($uid){
        $Day_cur = new DateTime(date("Y-m-d H:i:s") . ' + 2 day');
        $exp_date_time = $Day_cur->format('Y-m-d H:i:s');
        
        $updateSessionQry = "update user set expDate = '".$exp_date_time."' where id = '".$uid."'";
        mysql_query($updateSessionQry,$this->db->conn);
    }
}

?>
