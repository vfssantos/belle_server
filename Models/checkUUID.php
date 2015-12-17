<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of checkEmail
 *
 * @author admin@3embed
 */
require_once 'ConDB.php';

class checkUUID {
    public $available;
    
    public function __construct($userid,$uuid) {
        $db = new ConDB();
        $get_user_qry = "select * from user where UUID ='" . $uuid . "' and id !=".$userid;
        $user_res = mysql_query($get_user_qry, $db->conn);
        if(mysql_num_rows($user_res) > 0){
            $this->available = false;
        }else{
            $this->available = true;
        }
        
        $db->close($db);
    }
}

?>

