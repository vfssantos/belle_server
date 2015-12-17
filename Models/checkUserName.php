<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of checkUserName
 *
 * @author admin@3embed
 */
require_once 'ConDB.php';

class checkUserName {
    public $available;
    
    public function __construct($userName,$uid) {
        $db = new ConDB();
        $get_user_qry = "select * from user where UserName ='" . $userName . "' and id !='".$uid."'";
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
