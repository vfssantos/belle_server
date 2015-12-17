<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();

//$admin = $_SESSION['admin'];

session_destroy();

//if (!isset($admin)) {
    header('location: login.php');
//} else {
//    header('location: driverlogin.php');
//}
?>