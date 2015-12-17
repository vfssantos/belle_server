<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('../Models/ConDB.php');

$db = new ConDB();

if ($_REQUEST['req_type'] == '2') {

    $photo_url = $_REQUEST['photo'];
    unlink('../photos/' . basename($photo_url));
    $deleteQry = "delete from photos where Photo_Url = '" . $photo_url . "'";
    $deleteRes = mysql_query($deleteQry, $db->conn);
    if (mysql_affected_rows() > 0) {

        $res = array('flag' => 0, 'message' => 'Process completed', 'error' => '');
    } else {
//        echo '--4--';
        $res = array('flag' => 1, 'message' => '', 'error' => '');
    }
    echo json_encode($res);
}
?>
