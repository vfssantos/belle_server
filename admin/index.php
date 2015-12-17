<?php
session_start();
$curTime = time();
if ($_SESSION['admin'] != 'super' || $_SESSION['validity'] <= $curTime) {

    header('location: logout.php');
}

$oneHourExp = (24 * 60) + time();
$_SESSION['validity'] = $oneHourExp;

include '../Models/ConDB.php';
$db = new ConDB();
$totalUser = mysql_fetch_assoc(mysql_query("select count(Entity_Id) as cnt from entity", $db->conn));
$totalPosts = mysql_fetch_assoc(mysql_query("select count(Photo_Id) as cnt from photos", $db->conn));
$downloads = mysql_fetch_assoc(mysql_query("select count(distinct device) as cnt from user_sessions", $db->conn));
$loggedIns = mysql_fetch_assoc(mysql_query("select count(distinct oid) as cnt from user_sessions where loggedIn = '1'", $db->conn));
?>
<!DOCTYPE html>
<!-- saved from url=(0058)http://192.168.1.112/example/picturish/#/dashboard/albums/ -->
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Admin Panel</title>

    <style>[ng-cloak]#splash{display:block!important}[ng-cloak]{display:none}#splash{display:none;position:absolute;top:45%;left:48%;width:50px;height:50px;z-index:0;animation:loader 2s infinite ease;border:4px solid #ff5722}#splash-spinner{vertical-align:top;display:inline-block;width:100%;background-color:#ff5722;animation:loader-inner 2s infinite ease-in}@keyframes  loader{0%{transform:rotate(0deg)}25%,50%{transform:rotate(180deg)}100%,75%{transform:rotate(360deg)}}@keyframes  loader-inner{0%,25%{height:0}50%,75%{height:100%}100%{height:0}}</style>
    <link rel="stylesheet" href="assert/css/main.css">
		<link rel="stylesheet" href="assert/css/custom.css">
	  <link href="crop-avatar/css/bootstrap.min.css" rel="stylesheet">
  	<link href="crop-avatar/css/cropper.min.css" rel="stylesheet">
  	<link href="crop-avatar/css/main.css" rel="stylesheet">

		<script src="crop-avatar/js/jquery.min.js"></script>
		<script type='text/javascript' src='assert/js/core.min.js'></script>
		<script src="assert/js/coderprefix.js" type="text/javascript"></script>
		<!--script src="assert/js/jquery.min.js" type="text/javascript"></script-->

    <link rel="icon" type="image/x-icon" href="assert/favicon/favicon.ico">
		<style class="csscreations">* {margin: 0; padding: 0;}
		
		#lightbox {
			margin: 10px 50px 100px 50px;
			border-bottom: 1px solid #ccc;
			overflow: hidden;
		}
		@font-face {
      font-family: 'custom';
      src: url('assert/font/custom.eot?21878635');
      src: url('assert/font/custom.eot?21878635#iefix') format('embedded-opentype'),
           url('assert/font/custom.woff?21878635') format('woff'),
           url('assert/font/custom.ttf?21878635') format('truetype'),
           url('assert/font/custom.svg?21878635#custom') format('svg');
      font-weight: normal;
      font-style: normal;
    }
		/*#lightbox ul {
			overflow: hidden;
		}
		/*Image styles*/
		#lightbox .post_frame {
			float: left;
			width: 240px; height: 270px;
			background: #fff;
			margin: 0 20px 20px 0;
			position: relative;
		}

		#lightbox .image_frame {
			float: left;
			width: 240px; height: 240px;
			padding: 10px;
			background: #fff;
			position: relative;
			cursor: pointer;
		}

		#lightbox .post_frame img {
			display: block;
			width:100%; 
			height:100%; 
			object-fit:cover;
		}
		
		/*Image titles*/
		#lightbox .image_title {
			width: 240px; height: 240px;
			background: rgba(0, 0, 0, 0.5);
			position: absolute;
			top: 0; left: 0;
			display: table;
			/*Hover effect - default state*/
			opacity: 0;
			transition: all 0.5s;
		}
		#lightbox .title {
			color: #fff;
			background: rgba(0, 0, 0, 0.5);
			font-size: 14px;
			text-align: center;
			/*Vertical center align*/
			display: table-cell;
			vertical-align: middle;
			/*Hover effect - default state*/
			transform: scale(0.2);
			transition: all 0.25s;
		}
		/*Zoom icon over each title using iconfont and pseudo elements*/
		#lightbox .title::before {
			content: '\e80e';
    	font-family: fontello;
			font-size: 24px;
			color: #fff;
			display: block;
			opacity:0.5
			line-height: 36px;
		}
		/*Hover states*/
		#lightbox .image_frame:hover {
			box-shadow: inset 0 0 10px 1px rgba(0, 0, 0, 0.75);
		}
		#lightbox .image_frame:hover .image_title {
			opacity: 1;
		}
		#lightbox .image_frame:hover .title {
			transform: scale(1);
		}
		#lightbox .property_postedDt {
			float:left;
			width:70px;
			margin-left:12px;
		}
		#lightbox .posted_time {
			font-size: 12px;
		}
		#lightbox .posted_time::before {
			content: '\e816';
			float:left;
    	font-family: fontello;
			font-size: 12px;
			color: #000;
			display: block;
		}
		#lightbox .property_likes {
			float:left;
			width:40px;
			margin-left:12px;
		}
		#lightbox .likenumbers {
			font-size: 12px;
		}
		#lightbox .likenumbers::before {
			content: '\e800';
			float:left;
    	font-family: custom;
			font-size: 12px;
			color: #000;
			display: block;
		}
		#lightbox .property_dislikes {
			float:left;
			width:40px;
			margin-left:12px;
		}
		#lightbox .dislikenumbers {
			font-size: 12px;
		}
		#lightbox .dislikenumbers::before {
			content: '\e801';
			float:left;
    	font-family: custom;
			font-size: 12px;
			color: #000;
			display: block;
		}
		#lightbox .action_trash {
			content: '\e813';
			float:right;
			width:20px;
			margin: -7px 5px 0 0;
			cursor:pointer;
		}
		#lightbox .trash_plain {
			font-size: 20px;
		}
		#lightbox .trash_plain::before {
			content: '\e813';
			float:left;
    	font-family: fontello;
			font-size: 18px;
			color: rgb(255, 87, 34);
			display: block;
		}
		/*Lightbox element style*/
		.lb_backdrop {
			background: rgba(0, 0, 0, 0.7);
			position: fixed;
			top: 0; left: 0; right: 0; bottom: 0;
		}
		/*The canvas contains the larger image*/
		.lb_canvas {
			background: white;
			width: 500px; height: 500px;
			position: fixed;
			top: 0; left: 0; /*Will be centered later by Jquery*/
			box-shadow: 0 0 20px 5px black;
			padding: 10px;
		}
		/*A separate class for loading GIF, for easy Jquery handling*/
		.lb_canvas.loading {
			background: white url("assert/image/loading.gif") center center no-repeat;
		}
		/*Lightbox Controls*/
		.lb_controls {
			width: 400px;
			height:40px; 
			background: rgba(0, 0, 0, 0.75);
			position: fixed;
			bottom: 50px;
			color: white;
			/*To horizontally center it*/
			left: 0; right: 0; margin: 0 auto; 
		}
		.lb_controls span {
			line-height: 30px;
			height: 40px;
		}
		.lb_controls span.inactive {
			opacity: 0.25;
		}
		.lb_previous, .lb_next {
			position: absolute;
			top: 0;
			padding: 5px 12px;
			font-family: Lato;
			font-size: 30px;
			background: black;
			cursor: pointer;
		}
		.lb_previous {
			left: 0;
			border-right: 1px solid rgba(255, 255, 255, 0.1);
		}
		.lb_next {
			right: 0;
			border-left: 1px solid rgba(255, 255, 255, 0.1);
		}
		.lb_title {
			text-align: center;
			display: block;
			font-size: 14px;
			text-transform: uppercase;
			padding: 5px 0;
			font-weight: bold;
		}
		
		
		
		</style>
	</head>

	<body>
    <div style="height:100%;width:100%;" class="container" id="crop-avatar">
      <section id="middle-col">
        <nav class="navbar navbar-default" ng-controller="NavbarController" style="border-bottom: 1px solid #D0D8DC;">
          <div class="container-fluid">
            <div class="navbar-header">
              <a class="navbar-brand" ui-sref="home" href="../homepage/index.html">
                <img class="logo" src="assert/image/logo_dark.png" alt="logo">
              </a>
            </div>
            <!--form class="navbar-form navbar-left navbar-search ng-pristine ng-valid ng-valid-required" ng-submit="goToSearchPage()">
              <div class="input-group">
                <md-autocomplete placeholder="Search for Photos and Albums" md-menu-class="search-suggestions" md-selected-item="selectedItem" md-selected-item-change="selectItem()" md-delay="300" md-search-text="searchText" md-items="item in getSearchResults(searchText)" md-item-text="item.name" class="md-default-theme">        <md-autocomplete-wrap layout="row" ng-class="{ &#39;md-whiteframe-z1&#39;: !floatingLabel }" role="listbox" class="md-whiteframe-z1"><input flex="" type="search" id="input-2" name="" ng-if="!floatingLabel" autocomplete="off" ng-required="isRequired" ng-disabled="$mdAutocompleteCtrl.isDisabled" ng-model="$mdAutocompleteCtrl.scope.searchText" ng-keydown="$mdAutocompleteCtrl.keydown($event)" ng-blur="$mdAutocompleteCtrl.blur()" ng-focus="$mdAutocompleteCtrl.focus()" placeholder="Search for Photos and Albums" aria-owns="ul-2" aria-label="Search for Photos and Albums" aria-autocomplete="list" aria-haspopup="true" aria-activedescendant="" aria-expanded="false" class="ng-pristine ng-untouched ng-valid ng-valid-required" tabindex="0" aria-required="false" aria-invalid="false"></md-autocomplete-wrap>        <aria-status class="md-visually-hidden" role="status" aria-live="assertive"></aria-status></md-autocomplete>
                <div ng-click="goToSearchPage()" class="input-group-addon" role="button" tabindex="0"><i class="icon icon-search"></i></div>
              </div>
            </form-->
            <ul class="nav navbar-nav navbar-right">
              <li class="navbar-text" ng-click="users.logout()" role="button" tabindex="0"><a href="logout.php"><i class="icon icon-login"></i> <div class="text">Log Out</div></a></li>
            </ul>
          </div>
        </nav>
        <section class="flex-fluid-container">
          <div class="middle-col-flex flex-fluid" ng-controller="ItemsController" ng-file-drop="" drag-over-class="dragover" ng-file-change="upload($files)" ng-multiple="true" allow-dir="true">
          	<!--div class="container" id="crop-avatar"-->
	            <div id="actions-bar" class="clearfix" ng-class="{ &#39;no-files&#39;: !items || !items.length }">
            		<div id="admin" style="width: 88%;float: left;">
									<section layout="row" class="stats-row">
								    <div class="stats-container">
								        <div class="stats-icon"><i class="icon icon-users"></i></div>
								        <div class="details">
								            <div class="number"><?php echo $totalUser['cnt']; ?></div>
								            <div class="text">Total Users</div>
								        </div>
								    </div>
								    <div class="stats-container">
								        <div class="stats-icon"><i class="icon icon-pictures"></i></div>
								        <div class="details">
								            <div class="number"><?php echo $totalPosts['cnt']; ?></div>
								            <div class="text">Total Photos</div>
								        </div>
								    </div>
								    <div class="stats-container">
								        <div class="stats-icon"><i class="icon icon-download"></i></div>
								        <div class="details">
								            <div class="number"><?php echo $downloads['cnt']; ?></div>
								            <div class="text">Downloads</div>
								        </div>
								    </div>
								    <div class="stats-container">
								        <div class="stats-icon"><i class="icon icon-link"></i></div>
								        <div class="details">
								        		<div class="number"><?php echo $loggedIns['cnt']; ?></div>
								            <div id="active-users-container">Logged Ins</div>
								        </div>
								    </div>
									</section>
								</div>
          		  <div class="avatar-view" title="" style="width:1px;height:1px">
      						<img id="preview_photo" src="crop-avatar/img/picture.jpg" alt="Avatar">
					    	</div>
              	<section class="pull-right buttons">
             			<button id="photo_upload" class="md-raised md-primary md-button md-default-theme" style="margin-top: 52px;">
             	  		<i class="icon icon-upload-cloud"></i><span>Add</span>
             	  	</button>
              	</section>
	            </div>
						<!--/div-->
            <div ui-view="" class="deselect-file files-view" afkl-image-container="">
            	<div id="lightbox">
            		<?php
            			$curr_time = time();
            			$curr_gmt_date = gmdate('Y-m-d H:i:s', $curr_time);
            			$photoQry = "select ent.Email, pts.Photo_Url, TIMESTAMPDIFF(SECOND,pts.Post_Dt,'" . $curr_gmt_date . "') as ago, 
            							(select count(Like_Id) from likes where Photo_Id = pts.Photo_Id and Like_Flag = '1') as like_cnt,
            							(select count(Like_Id) from likes where Photo_Id = pts.Photo_Id and Like_Flag = '2') as dislike_cnt
            							 from entity ent, photos pts where pts.Entity_Id = ent.Entity_Id order by ago";
            			$photoRes = mysql_query($photoQry, $db->conn);
            			if (mysql_num_rows($photoRes) > 0) {
            					$i = 1;
            					while ($photoRow = mysql_fetch_assoc($photoRes)) {
            							if ($photoRow['ago'] > 60*60*24*365)
            									$postAgo = intval($photoRow['ago']/(60*60*24*365)) . 'year';
            							else if ($photoRow['ago'] > 60*60*24*30)
            									$postAgo = intval($photoRow['ago']/(60*60*24*30)) . 'mon';
            							else if ($photoRow['ago'] > 60*60*24*7)
            									$postAgo = intval($photoRow['ago']/(60*60*24*7)) . 'week';
            							else if ($photoRow['ago'] > 60*60*24)
            									$postAgo = intval($photoRow['ago']/(60*60*24)) . 'day';
            							else if ($photoRow['ago'] > 60*60)
            									$postAgo = intval($photoRow['ago']/(60*60)) . 'hour';
            							else if ($photoRow['ago'] > 60)
            									$postAgo = intval($photoRow['ago']/60) . 'min';
            							else
            									$postAgo = $photoRow['ago'] . 'sec';
            							?>
									<div id="postimage<?php echo $i; ?>" class="post_frame">
										<div class="image_frame">
											<img src="<?php echo $photoRow['Photo_Url']; ?>" data-large="<?php echo $photoRow['Photo_Url']; ?>">
											<div class="image_title">
												<h5 class="title">Posted from <br /><?php echo $photoRow['Email']; ?></h5>
											</div>
										</div>
										<div class="property_postedDt"><h6 class="posted_time">&nbsp;&nbsp;<?php echo $postAgo; ?></h6></div>
										<div class="property_likes"><h6 class="likenumbers">&nbsp;&nbsp;<?php echo $photoRow['like_cnt']; ?></h6></div>
										<div class="property_dislikes"><h6 class="dislikenumbers">&nbsp;&nbsp;<?php echo $photoRow['dislike_cnt']; ?></h6></div>
										<div class="action_trash" data="<?php echo $photoRow['Photo_Url']; ?>" photo_idx="<?php echo $i; ?>"><h5 class="trash_plain"></h5></div>
									</div>            							
            							<?php
            									$i++;
            					}
            			}
            		?>
							</div>
						</div>
						<div id = "variable_field"></div>
          </div>
        </section>
      </section>
			<!-- Cropping modal -->
			<div class="modal fade" id="avatar-modal" aria-hidden="true" aria-labelledby="avatar-modal-label" role="dialog" tabindex="-1">
      	<div class="modal-dialog modal-lg">
      	  <div class="modal-content">
      	    <form class="avatar-form" action="crop-avatar/crop.php" enctype="multipart/form-data" method="post">
      	      <div class="modal-header">
      	        <button class="close" data-dismiss="modal" type="button">&times;</button>
      	        <h4 class="modal-title" id="avatar-modal-label">Photo Upload and Crop</h4>
      	      </div>
      	      <div class="modal-body">
      	        <div class="avatar-body">
      	
      	          <!-- Upload image and data -->
      	          <div class="avatar-upload">
      	            <input class="avatar-src" name="avatar_src" type="hidden">
      	            <input class="avatar-data" name="avatar_data" type="hidden">
      	            <label for="avatarInput">Local upload</label>
      	            <input class="avatar-input" id="avatarInput" name="avatar_file" type="file">
      	          </div>
      	
      	          <!-- Crop and preview -->
      	          <div class="row">
      	            <div class="col-md-9">
      	              <div class="avatar-wrapper"></div>
      	            </div>
      	            <div class="col-md-3">
      	              <div class="avatar-preview preview-lg"></div>
      	              <div class="avatar-preview preview-md"></div>
      	              <div class="avatar-preview preview-sm"></div>
      	            </div>
      	          </div>
      	
      	          <div class="row avatar-btns">
      	            <div class="col-md-9">
      	              <div class="btn-group">
      	                <button class="btn btn-primary" data-method="rotate" data-option="-90" type="button" title="Rotate -90 degrees">Rotate Left</button>
      	                <button class="btn btn-primary" data-method="rotate" data-option="-15" type="button">-15deg</button>
      	                <button class="btn btn-primary" data-method="rotate" data-option="-30" type="button">-30deg</button>
      	                <button class="btn btn-primary" data-method="rotate" data-option="-45" type="button">-45deg</button>
      	              </div>
      	              <div class="btn-group">
      	                <button class="btn btn-primary" data-method="rotate" data-option="90" type="button" title="Rotate 90 degrees">Rotate Right</button>
      	                <button class="btn btn-primary" data-method="rotate" data-option="15" type="button">15deg</button>
      	                <button class="btn btn-primary" data-method="rotate" data-option="30" type="button">30deg</button>
      	                <button class="btn btn-primary" data-method="rotate" data-option="45" type="button">45deg</button>
      	              </div>
      	            </div>
      	            <div class="col-md-3">
      	              <button id="upload_submit" class="btn btn-primary btn-block avatar-save" type="submit">Done</button>
      	            </div>
      	          </div>
      	        </div>
      	      </div>
      	      <!-- <div class="modal-footer">
      	        <button class="btn btn-default" data-dismiss="modal" type="button">Close</button>
      	      </div> -->
      	    </form>
      	  </div>
      	</div>
			</div><!-- /.modal -->

			<!-- Loading state -->
			<div class="loading" aria-label="Loading" role="img" tabindex="-1"></div>
    </div>
		<script src="crop-avatar/js/bootstrap.min.js"></script>
		<script src="crop-avatar/js/cropper.min.js"></script>
		<script src="crop-avatar/js/main.js"></script>
		<script>$(document).ready(function(){
		/*Jquery time*/
		$(document).ready(function(){
			var item, img, title, large_img;
			var CW, CH, CL, CT, hpadding, vpadding, imgtag;
			//Flag for preventing multiple image displays
			var lb_loading = false;
			var doc = $(document);
			
			$("#lightbox .image_frame").click(function(){
				if(lb_loading) return false;
				lb_loading= true;
				
				item = $(this.parentNode);
				img = item.find("img");
				title = item.find(".title").html();
				title = title.substring(16);
				
				//Remove active class from previously clicked LI
				$("#lightbox .post_frame.active").removeClass("active");
				//Mark the clicked LI as active for later use
				item.addClass("active");
				
				//The large image
				large_img = new Image();
				//Use data-large or the src itself if large image url is not available
				large_img.src = img.attr("data-large") ? img.attr("data-large") : img.attr("src");
				
				//Adding additional HTML - only if it hasn't been added before
				if($(".lb_backdrop").length < 1)
				{
					var lb_backdrop = '<div class="lb_backdrop"></div>';
					var lb_canvas = '<div class="lb_canvas"></div>';
					var lb_previous = '<span class="lb_previous"><</span>';
					var lb_title = '<span class="lb_title"></span>';
					var lb_next = '<span class="lb_next">></span>';
					var lb_controls = '<div class="lb_controls">'+lb_previous+lb_title+lb_next+'</div>';
					var total_html = lb_backdrop+lb_canvas+lb_controls;
					
					//$(total_html).appendTo("body");
					$('#variable_field').html(total_html);
				}
				//Fade in lightbox elements if they are hidden due to a previous exit
				if($(".lb_backdrop:visible").length == 0)
				{
					$(".lb_backdrop, .lb_canvas, .lb_controls").fadeIn("slow");
				}
				
				//Display preloader till the large image loads and make the previous image translucent so that the loader in the BG is visible
				if(!large_img.complete) 
					$(".lb_canvas").addClass("loading").children().css("opacity", "0.5")
				
				//Disabling left/right controls on first/last items
				if(item.prev().length == 0)
					$(".lb_previous").addClass("inactive");
				else
					$(".lb_previous").removeClass("inactive");
				if(item.next().length == 0)
					$(".lb_next").addClass("inactive");
				else
					$(".lb_next").removeClass("inactive");
				
				//Centering .lb_canvas
				CW = $(".lb_canvas").outerWidth();
				CH = $(".lb_canvas").outerHeight();
				//top and left coordinates
				CL = ($(window).width() - CW)/2;
				CT = ($(window).height() - CH)/2;
				$(".lb_canvas").css({top: CT, left: CL});
				
				//Inserting the large image into .lb_canvas once it's loaded
				$(large_img).load(function(){
					//Recentering .lb_canvas with new dimensions
//					CW = large_img.width;
//					CH = large_img.height;
					//.lb_canvas padding to be added to image width/height to get the total dimensions
					hpadding = parseInt($(".lb_canvas").css("paddingLeft")) + parseInt($(".lb_canvas").css("paddingRight"));
					vpadding = parseInt($(".lb_canvas").css("paddingTop")) + parseInt($(".lb_canvas").css("paddingBottom"));
					CL = ($(window).width() - CW - hpadding)/2;
					CT = ($(window).height() - CH - vpadding)/2;
					
					//Animating .lb_canvas to new dimentions and position
					$(".lb_canvas").html("").animate({width: CW, height: CH, top: CT, left: CL}, 500, function(){
						//Inserting the image but keeping it hidden
						imgtag = '<img src="'+large_img.src+'" style="width:480px;height:480px;object-fit:cover;opacity: 0;" />';
						$(".lb_canvas").html(imgtag);
						$(".lb_canvas img").fadeTo("slow", 1);
						//Displaying the image title
						$(".lb_title").html(title);
						
						lb_loading= false;
						$(".lb_canvas").removeClass("loading");
					})
				})
			})
			
			//Click based navigation
			doc.on("click", ".lb_previous", function(){ navigate(-1) });
			doc.on("click", ".lb_next", function(){ navigate(1) });
			doc.on("click", ".lb_backdrop", function(){ navigate(0) });
			
			//Keyboard based navigation
			doc.keyup(function(e){
				//Keyboard navigation should work only if lightbox is active which means backdrop is visible.
				if($(".lb_backdrop:visible").length == 1)
				{
					//Left
					if(e.keyCode == "37") navigate(-1);
					//Right
					else if(e.keyCode == "39") navigate(1);
					//Esc
					else if(e.keyCode == "27") navigate(0);
				}
			});
			
			//Navigation function
			function navigate(direction)
			{
				if(direction == -1) // left
					$("#lightbox .post_frame.active").prev().find(".image_frame").trigger("click");
				else if(direction == 1) //right
					$("#lightbox .post_frame.active").next().find(".image_frame").trigger("click");
				else if(direction == 0) //exit
				{
					$("#lightbox .post_frame.active").removeClass("active");
					$(".lb_canvas").removeClass("loading");
					//Fade out the lightbox elements
					$(".lb_backdrop, .lb_canvas, .lb_controls").fadeOut("slow", function(){
						//empty canvas and title
						$(".lb_canvas, .lb_title").html("");
					})
					lb_loading= false;
				}
			}
		});
   		$(".action_trash").click(function() {

   			var dis = $(this);

        var photo_url = $(dis).attr("data");
        var idx = $(dis).attr("photo_idx");

				if (confirm("Are you confirm to delete this photo from database?")) {
        		$.ajax({
        		    type: "POST",
        		    url: "manage_database.php",
        		    data: {req_type: 2, photo: photo_url},
        		    dataType: "JSON",
        		    success: function(result) {
        		        alert(result.message);
        		        if (result.flag == 0) {
		                    $("#postimage" + idx).remove();
        		        }
        		    }
        		});
        }
    	});
    	$("#photo_upload").click(function() {
    			$('.avatar-view').trigger('click');
    	});
		});
		</script>
	</body>
</html>