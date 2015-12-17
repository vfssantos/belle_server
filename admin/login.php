<?php
session_start();
// store session data
include('../Models/ConDB.php');
$db = new ConDB();

if (isset($_REQUEST['submit'])) {
    $checkUser = "SELECT id FROM administrator WHERE username= '" . $_REQUEST['username'] . "' and password= '" . md5($_REQUEST['password']) . "'";
    $ifUserAvailable = mysql_query($checkUser, $db->conn);
    $getcount = mysql_num_rows($ifUserAvailable);
//		if ($_REQUEST['username'] == 'superadmin' && $_REQUEST['password'] == 'superadmin')
//				$getcount = 1;
//		else 
//				$getcount = 0;
				
    if ($getcount > 0) {
        $rows = mysql_fetch_assoc($ifUserAvailable);

        $_SESSION['admin'] = 'super';
        $_SESSION['admin_name'] = 'Super Admin';
        $_SESSION['admin_id'] = $rows['id'];

        $oneHourExp = (24 * 60) + time();

        $_SESSION['validity'] = $oneHourExp;

//         $_SESSION['id'] = $rows['doc_id'];
//       $_SESSION['v'] = $rows['email'];
//         $_SESSION['v1'] = $rows['profile_pic'];
//       $_SESSION['v2'] = $rows['first_name'];
//       echo($rows['email']);
// store session data
//$_SESSION['v'] = 40;
//echo($rows['first_name']);

        header("location: index.php");
    } else {
        echo "Your Login Name or Password is invalid";
    }
}
?>

<!DOCTYPE html>
<!-- saved from url=(0046)http://192.168.1.112/example/picturish/#/login -->
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Admin Login</title>

    <style>[ng-cloak]#splash{display:block!important}[ng-cloak]{display:none}#splash{display:none;position:absolute;top:45%;left:48%;width:50px;height:50px;z-index:0;animation:loader 2s infinite ease;border:4px solid #ff5722}#splash-spinner{vertical-align:top;display:inline-block;width:100%;background-color:#ff5722;animation:loader-inner 2s infinite ease-in}@keyframes  loader{0%{transform:rotate(0deg)}25%,50%{transform:rotate(180deg)}100%,75%{transform:rotate(360deg)}}@keyframes  loader-inner{0%,25%{height:0}50%,75%{height:100%}100%{height:0}}</style>
    <link rel="stylesheet" href="assert/css/main.css">
		<link rel="stylesheet" href="assert/css/custom.css">
		<script type='text/javascript' src='assert/js/core.min.js'></script>
    <link rel="icon" type="image/x-icon" href="assert/favicon/favicon.ico">
	</head>
  <body ng-app="app" class="" style="cursor: auto; overflow: hidden;">
	  <div id="splash" aria-hidden="false">
       <div id="splash-spinner"></div>
    </div>

    <div style="height: 100%" ng-controller="RootController" aria-hidden="false">
    <!-- uiView:  -->
    	<div id="main-view" ui-view="" class="">
    		<section id="login-page" style="display: table;margin: 0 auto;">

    <!-- ngIf: utils.isDemo -->

    			<div ng-class="{ loading: loading }" class="container login-container">
        		<form ng-submit="submit()" ed-login-register-validator="" class="ng-valid ng-dirty ng-valid-parse" style="margin-top: -4em;">

            	<a ui-sref="home" href="../homepage/index.html"><img src="assert/image/logo_dark.png" alt="logo" class="logo"></a>

            	<div class="alert alert-danger alert-dismissible fade in" role="alert">
                <div class="message"></div>
            	</div>

            	<div class="form-group">
                <input class="form-control ng-untouched ng-valid ng-dirty ng-valid-parse" name="username" placeholder="Username" type="text" ng-model="credentials.email" tabindex="0" aria-invalid="false">
            	</div>
            	<div class="form-group">
                <input class="form-control ng-untouched ng-valid ng-dirty ng-valid-parse" name="password" type="password" placeholder="Password" ng-model="credentials.password" tabindex="0" aria-invalid="false">
            	</div>

            	<section class="clearfix">
                <div class="pull-right" ng-controller="SocialLoginController">
                    <button class="md-primary md-raised md-button md-default-theme" ng-transclude="" type="submit" name="submit"><span>Login</span></button>
                </div>
            	</section>

        		</form>
    			</div>
				</section>
			</div>
		</div>
	</body>
</html>