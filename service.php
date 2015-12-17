<?php

require_once 'Models/API.php';
require_once 'Models/ConDB.php';
require_once 'Models/getErrorMsg.php';
require_once 'Models/ManageToken.php';

class MyAPI extends API {

    protected $User;
    private $db;
    private $host = 'http://104.236.150.212/';
    private $ios_cert_path = 'cert/*.pem';
    private $ios_cert_pwd = 'pass';
    private $androidApiKey = 'AIzaSyDios94N5PZdisKUQxVnTVonbKQXwScvhE';
    private $androidUrl = 'http://android.googleapis.com/gcm/send';
    private $chatMessagePageSize = 30;
    private $profileMatchesPageSize = 10;
    private $default_profile_pic = 'pics/aa_default_profile_pic.gif';
    private $default_profile_photo = 'pics/deafult_profile_photo.png';
    private $ios_cert_server = "ssl://gateway.sandbox.push.apple.com:2195";

    /*
      Development -- ssl://gateway.sandbox.push.apple.com:2195
      Production -- ssl://gateway.push.apple.com:2195
     */

    public function __construct($request_uri, $postData, $origin) {

        parent::__construct($request_uri, $postData);

        $this->db = new ConDB();
    }

    /*              ----------------                SERVICE METHODS             ---------------------               */
    /*
     * Method name: login
     * Desc: social Login / Sign up for the app
     * Input: Request data
     * Output:  Success flag with data array if completed successfully, else data array with error flag
     */

    protected function login($args) {

        if ($args['ent_email'] == '' || $args['ent_dev_id'] == '' || $args['ent_auth_type'] == '' || $args['ent_device_type'] == '' || $args['ent_push_token'] == '')
            return $this->_getStatusMessage(1);

        $token_obj = new ManageToken();

        if ($args['ent_auth_type'] == '1') {
        		if ($args['ent_fb_id'] == '' || $args['ent_gender'] == '')
            		return $this->_getStatusMessage(1);
            if ($args['ent_gender'] == '1')
            		return $this->_getStatusMessage(19);
        } else if ($args['ent_auth_type'] == '2' && $args['ent_password'] == '') {
            return $this->_getStatusMessage(1);
				} else if ($args['ent_auth_type'] == '3') {
				 		if ($args['ent_password'] == '' || $args['ent_gender'] == '')
								return $this->_getStatusMessage(1);
						if ($args['ent_gender'] == '1')
            		return $this->_getStatusMessage(19);
        }
        return $this->_entitySocialLogin($args, $token_obj);
    }
    
    /*
     * Method name: getProfile
     * Desc: Get profile of any users
     * Input: Request data
     * Output:  Complete profile details if available, else error message
     */

    protected function getProfile($args) {

        $returned = $this->_validate_token($args['ent_sess_token'], $args['ent_dev_id']);

        if (is_array($returned))
            return $returned;

        $getProfileQry = "select Name, Email, Profile_Pic_Url from entity where Entity_Id = '" . $this->User['entityId'] . "'";
        $getProfileRes = mysql_query($getProfileQry, $this->db->conn);

        if (mysql_num_rows($getProfileRes) > 0) {

            $entityDet = mysql_fetch_assoc($getProfileRes);

            $errMsgArr = $this->_getStatusMessage(18);

            $entityArr = array('name' => $entityDet['Name'], 'email' => $entityDet['Email'], 'profilePic' => $entityDet['Profile_Pic_Url']);
             		
            return array('errNum' => $errMsgArr['errNum'], 'errFlag' => $errMsgArr['errFlag'], 'errMsg' => $errMsgArr['errMsg'], 'userProfile' => $entityArr);
             
        } else {
            return $this->_getStatusMessage(19);
        }
    }

    /*
     * Method name: editProfile
     * Desc: Edit profile of any users
     * Input: Request data
     * Output:  status message according to the result
     */

    protected function editProfile($args) {

     		if ($args['ent_name'] == '' || $args['ent_email'] == '')
     				return $this->_getStatusMessage(1);

        $returned = $this->_validate_token($args['ent_sess_token'], $args['ent_dev_id']);

        if (is_array($returned))
            return $returned;

				if ($args['ent_new_pwd'] != '') {
						$pwdRes = mysql_query("update entity set Password = '" . md5($args['ent_new_pwd']) . "' where Entity_Id = '" . $this->User['entityId'] . "'", $this->db->conn);		
						if (!$pwdRes)
								return $this->_getStatusMessage(20);
				}
				$url = $this->User['pPic'];
				if($_FILES['profile']['type'] == "image/gif" || $_FILES['profile']['type'] == "image/jpeg" || $_FILES['profile']['type'] == "image/jpg" || $_FILES['profile']['type'] == "image/png") {
						$file_to_open = 'pics/' . $this->User['entityId'] . '_' . time();
        		$profPicRes = move_uploaded_file($_FILES['profile']['tmp_name'], $file_to_open);
						$url = $this->host . $file_to_open;
						unlink('pics/' . end(explode('/', $this->User['pPic'])));
        }
 				$editProfileQry = "update entity set Name = '" . $args['ent_name'] . "', Email = '" . $args['ent_email'] . "', Profile_Pic_Url = '" . $url . "' where Entity_Id = '" . $this->User['entityId'] . "'";
        $editProfileRes = mysql_query($editProfileQry, $this->db->conn);
        if ($editProfileRes) {
            $errMsgArr = $this->_getStatusMessage(21);
            return array('errNum' => $errMsgArr['errNum'], 'errFlag' => $errMsgArr['errFlag'], 'errMsg' => $errMsgArr['errMsg'], 'updateUrl' => $url);
        } else {
            return $this->_getStatusMessage(8);
         }
    }
    
     /*
     * Method name: logout
     * Desc: Edit profile of any users
     * Input: Request data
     * Output:  status message according to the result
     */

    protected function logout($args) {

        $returned = $this->_validate_token($args['ent_sess_token'], $args['ent_dev_id']);

        if (is_array($returned))
            return $returned;

        $logoutQry = "update user_sessions set loggedIn = '2' where oid = '" . $this->User['entityId'] . "' and sid = '" . $this->User['sid'] . "'";
        mysql_query($logoutQry, $this->db->conn);

        if (mysql_affected_rows() > 0)
            return $this->_getStatusMessage(21);
        else
            return $this->_getStatusMessage(22);
    }

     /*
     * Method name: addPhoto
     * Desc: add photo
     * Input: Request data
     * Output:  status message according to the result
     */
    protected function addPhoto($args) {
    	
    		if ($args['pt_option'] == '' || $_FILES['share_photo'] == '')
    				return $this->_getStatusMessage(1);
    				
        $returned = $this->_validate_token($args['ent_sess_token'], $args['ent_dev_id']);

        if (is_array($returned))
            return $returned;
            
        $curr_time = time();
        $curr_gmt_date = gmdate('Y-m-d H:i:s', $curr_time);
				$file_to_open = 'photos/' . $this->User['entityId'] . '_' . $curr_time;
				if($_FILES['share_photo']['type'] == "image/gif" || $_FILES['share_photo']['type'] == "image/jpeg" || $_FILES['share_photo']['type'] == "image/jpg" || $_FILES['share_photo']['type'] == "image/png")
        		$sharePicRes = move_uploaded_file($_FILES['share_photo']['tmp_name'], $file_to_open);
        		
				if($sharePicRes) {
						$image_url = $this->host . $file_to_open;
				
						$addQry = "insert into photos (Entity_Id, Photo_Url, Post_Dt, Share_Opt) values ('" . $this->User['entityId'] . "', 
											'" . $image_url . "', '" . $curr_gmt_date . "', '" . $args['pt_option'] . "')";
						mysql_query($addQry, $this->db->conn);
						$newProductId = mysql_insert_id();
						if ($newProductId > 0)
								return $this->_getStatusMessage(11);
						else
								return $this->_getStatusMessage(12);
				} else {
						return $this->_getStatusMessage(12);
				}
		}

     /*
     * Method name: findPhoto
     * Desc: find photo
     * Input: Request data
     * Output:  status message according to the result
     */
    protected function findPhoto($args) {
    	
        $returned = $this->_validate_token($args['ent_sess_token'], $args['ent_dev_id']);

        if (is_array($returned))
            return $returned;
            
        $photoArr = array();
        $worldQry = "select pt.Photo_Id, pt.Photo_Url from photos pt where pt.Entity_Id != '" . $this->User['entityId'] . "' and pt.Share_Opt = '2' and 
        						pt.Photo_Id NOT IN (select Photo_Id from likes where Entity_Id = '" . $this->User['entityId'] . "' and Photo_Id = pt.Photo_Id) order by rand() limit 4";
        $worldRes = mysql_query($worldQry, $this->db->conn);
        if (mysql_num_rows($worldRes) > 0) {
        		while ($worldRow = mysql_fetch_assoc($worldRes))
        				$photoArr[] = array('pid' => $worldRow['Photo_Id'], 'url' => $worldRow['Photo_Url'], 'friend' => '0');
        }
        $friendQry = "select pt.Photo_Id, pt.Photo_Url from photos pt where pt.Entity_Id != '" . $this->User['entity_Id'] . "' and pt.Share_Opt = '1' and 
        						(pt.Entity_Id IN (select Entity1_Id from friends where Entity1_Id = pt.Entity_Id and Entity2_Id = '" . $this->User['entityId'] . "') or 
        						 pt.Entity_Id IN (select Entity2_Id from friends where Entity2_Id = pt.Entity_Id and Entity1_Id = '" . $this->User['entityId'] . "')) and
        						pt.Photo_Id NOT IN (select Photo_Id from likes where Entity_Id = '" . $this->User['entityId'] . "' and Photo_Id = pt.Photo_Id) order by rand() limit 1";
        $friendRes = mysql_query($friendQry, $this->db->conn);
        if (mysql_num_rows($friendRes) == 1) {
        		$friendRow = mysql_fetch_assoc($friendRes);
        		$photoArr[] = array('pid' => $friendRow['Photo_Id'], 'url' => $friendRow['Photo_Url'], 'friend' => '1');
        }
        if (count($photoArr) > 0) {
            $errMsgArr = $this->_getStatusMessage(13);
            return array('errNum' => $errMsgArr['errNum'], 'errFlag' => $errMsgArr['errFlag'], 'errMsg' => $errMsgArr['errMsg'], 'photos' => $photoArr);        		
       	} else {
       			return $this->_getStatusMessage(14);
       	}
		}

    /*
     * Method name: likeAction
     * Desc: Action for photo(1 - like, 2 - dislike, 3 - skip)
     * Input: Request data
     * Output:  Success message if completed, else returns error message
     */

    protected function likeAction($args) {

        if ($args['photo_id'] == '' || $args['photo_action'] == '')
            return $this->_getStatusMessage(1);

        $returned = $this->_validate_token($args['ent_sess_token'], $args['ent_dev_id']);

        if (is_array($returned))
            return $returned;

				$checkQry = "select Like_Id from likes where Entity_Id = '" . $this->User['entityId'] . "' and Photo_Id = '" . $args['photo_id'] . "'";
				$checkRes = mysql_query($checkQry, $this->db->conn);
				if (mysql_num_rows($checkRes) > 0)
						return $this->_getStatusMessage(8);
						
				$insertQry = "insert into likes (Entity_Id, Photo_Id, Like_Flag) values ('" . $this->User['entityId'] . "', '" . $args['photo_id'] . "', '" . $args['photo_action'] . "')";
				mysql_query($insertQry, $this->db->conn);
				
        if (mysql_insert_id() > 0)
						return $this->_getStatusMessage(15);
        else
						return $this->_getStatusMessage(16);
    }

    /*
     * Method name: favoriteAction
     * Desc: Favorite action for photo(1 - add to favorite, 2 - delete from favorite)
     * Input: Request data
     * Output:  Success message if completed, else returns error message
     */
		protected function favoriteAction($args) {
				
				if ($args['photo_id'] == '' || $args['photo_action'] == '')
						return $this->_getStatusMessage(1);
						
        $returned = $this->_validate_token($args['ent_sess_token'], $args['ent_dev_id']);
				
        if (is_array($returned))
            return $returned;
        
        if ($args['photo_action'] == '1') {
        		mysql_query("insert into favorites (Entity_Id, Photo_Id) values ('" . $this->User['entityId'] . "', '" . $args['photo_id'] . "')", $this->db->conn);
        		$result = mysql_insert_id();
        } else {
        		mysql_query("delete from favorites where Entity_Id = '" . $this->User['entityId'] . "' and Photo_Id = '" . $args['photo_id'] . "'", $this->db->conn);
        		$result = mysql_affected_rows();
        }
        if ($result > 0)
        		return $this->_getStatusMessage(15);
        else
        		return $this->_getStatusMessage(16);
		}

    /*
     * Method name: feedback
     * Desc: get my photos 
     * Input: Request data
     * Output:  Success message if completed, else returns error message
     */
		protected function feedback($args) {
				
        $returned = $this->_validate_token($args['ent_sess_token'], $args['ent_dev_id']);
				
        if (is_array($returned))
            return $returned;
        
				$myQry = "select pt.Photo_Id, pt.Photo_Url, (select count(Like_Id) from likes where Photo_Id = pt.Photo_Id and Like_Flag = '1') as lkCnt, 
												(select count(Like_Id) from likes where Photo_Id = pt.Photo_Id and Like_Flag = '2') as dlkCnt, 
												(select count(Like_Id) from likes where Photo_Id = pt.Photo_Id) as vwCnt,
												(select count(fav_id) from favorites where Photo_Id = pt.Photo_Id) as favCnt
												from photos pt where pt.Entity_Id = '" . $this->User['entityId'] . "'";
				$myRes = mysql_query($myQry, $this->db->conn);
				if (mysql_num_rows($myRes) > 0) {
						$myArr = array();
						while ($myRow = mysql_fetch_assoc($myRes)) {
								$myArr[] = array('pid' => $myRow['Photo_Id'], 'url' => $myRow['Photo_Url'], 'likes' => $myRow['lkCnt'], 
																			'dislikes' => $myRow['dlkCnt'], 'views' => $myRow['vwCnt'], 'favs' => $myRow['favCnt']);
						}
            $errMsgArr = $this->_getStatusMessage(13);
            return array('errNum' => $errMsgArr['errNum'], 'errFlag' => $errMsgArr['errFlag'], 'errMsg' => $errMsgArr['errMsg'], 'photos' => $myArr);
				} else {
        		return $this->_getStatusMessage(14);
        }
		}

    /*
     * Method name: deletePhoto
     * Desc: delete own photo
     * Input: Request data
     * Output:  Success message if completed, else returns error message
     */
		protected function deletePhoto($args) {
				
				if ($args['photo_id'] == '')
						return $this->_getStatusMessage(1);

        $returned = $this->_validate_token($args['ent_sess_token'], $args['ent_dev_id']);
				
        if (is_array($returned))
            return $returned;
        
        $photoQry = "select Photo_Url from photos where Photo_Id = '" . $args['photo_id'] . "' and Entity_Id = '" . $this->User['entityId'] . "'";
        $photoRes = mysql_query($photoQry, $this->db->conn);
        if (mysql_num_rows($photoRes) == 1) {
      			$photoRow = mysql_fetch_assoc($photoRes);
      			unlink('photos/' . basename($photoRow['Photo_Url']));
		        $deleteQry = "delete from photos where Photo_Id = '" . $args['photo_id'] . "' and Entity_Id = '" . $this->User['entityId'] . "'";
		        mysql_query($deleteQry, $this->db->conn);
      	}
        
        if (mysql_affected_rows() > 0)
        		return $this->_getStatusMessage(17);
        else
        		return $this->_getStatusMessage(8);
		}
    /*
     * Method name: looks
     * Desc: get my favorite photos and trending photos
     * Input: Request data
     * Output:  Success message if completed, else returns error message
     */
		protected function looks($args) {
				
        $returned = $this->_validate_token($args['ent_sess_token'], $args['ent_dev_id']);
				
        if (is_array($returned))
            return $returned;

				$favoriteQry = "select pt.Photo_Id, pt.Photo_Url, (select count(Like_Id) from likes where Photo_Id = pt.Photo_Id and Like_Flag = '1') as lkCnt, 
												(select count(Like_Id) from likes where Photo_Id = pt.Photo_Id and Like_Flag = '2') as dlkCnt, 
												(select count(Like_Id) from likes where Photo_Id = pt.Photo_Id) as vwCnt,
												(select count(fav_id) from favorites where Photo_Id = pt.Photo_Id) as favCnt
												from photos pt where pt.Photo_Id IN (select Photo_Id from favorites where Entity_Id = '" . $this->User['entityId'] . "' and Photo_Id = pt.Photo_Id)";
				$favoriteRes = mysql_query($favoriteQry, $this->db->conn);
				$favoriteArr = array();
				if (mysql_num_rows($favoriteRes) > 0) {
						while ($favoriteRow = mysql_fetch_assoc($favoriteRes)) {
								$favoriteArr[] = array('pid' => $favoriteRow['Photo_Id'], 'url' => $favoriteRow['Photo_Url'], 'likes' => $favoriteRow['lkCnt'], 
																			'dislikes' => $favoriteRow['dlkCnt'], 'views' => $favoriteRow['vwCnt'], 'favs' => $favoriteRow['favCnt']);
						}
				}
				$trendingQry = "select pt.Photo_Id, pt.Photo_Url, (select count(Like_Id) from likes where Photo_Id = pt.Photo_Id and Like_Flag = '1') as lkCnt, 
												(select count(Like_Id) from likes where Photo_Id = pt.Photo_Id and Like_Flag = '2') as dlkCnt, 
												(select count(Like_Id) from likes where Photo_Id = pt.Photo_Id) as vwCnt,
												(select count(fav_id) from favorites where Photo_Id = pt.Photo_Id) as favCnt
												from photos pt order by lkCnt DESC limit 30";
				$trendingRes = mysql_query($trendingQry, $this->db->conn);
				$trendingArr = array();
				if (mysql_num_rows($trendingRes) > 0) {
						while ($trendingRow = mysql_fetch_assoc($trendingRes)) {
								$trendingArr[] = array('pid' => $trendingRow['Photo_Id'], 'url' => $trendingRow['Photo_Url'], 'likes' => $trendingRow['lkCnt'], 
																			'dislikes' => $trendingRow['dlkCnt'], 'views' => $trendingRow['vwCnt'], 'favs' => $trendingRow['favCnt']);
						}
				}
        $errMsgArr = $this->_getStatusMessage(13);
        return array('errNum' => $errMsgArr['errNum'], 'errFlag' => $errMsgArr['errFlag'], 'errMsg' => $errMsgArr['errMsg'], 'favorites' => $favoriteArr, 'trendings' => $trendingArr);
		}
		
		protected function formatDatabase($args) {
				
				if ($args['Realy_delete'] != 'OK')
						return $this->getStatusMessage(1);
				
				$deleted = 0;		
				mysql_query("delete from favorites where 1", $this->db->conn);
				$deleted += mysql_affected_rows();
				mysql_query("delete from likes where 1", $this->db->conn);
				$deleted += mysql_affected_rows();
				mysql_query("delete from friends where 1", $this->db->conn);
				$deleted += mysql_affected_rows();
				mysql_query("delete from entity where 1", $this->db->conn);
				$deleted += mysql_affected_rows();
				mysql_query("delete from user_sessions where 1", $this->db->conn);
				$deleted += mysql_affected_rows();
				mysql_query("delete from photos where 1", $this->db->conn);
				$deleted += mysql_affected_rows();
				
				return array('deletedRows' => $deleted);
		}
		
    /*             ----------------                 HELPER METHODS             ------------------             */
    /*
     * Method name: _validate_token
     * Desc: Authorizes the user with token provided
     * Input: Token
     * Output:  gives entity details if available else error msg
     */

    protected function _validate_token($ent_sess_token, $ent_dev_id) {

        if ($ent_sess_token == '' || $ent_dev_id == '') {

            return $this->_getStatusMessage(1);
        } else {

            $sessDetArr = $this->_getSessDetails($ent_sess_token, $ent_dev_id);
//            print_r($sessDetArr);
            if ($sessDetArr['flag'] == '0') {
                $this->_updateActiveDateTime($sessDetArr['entityId']);
                $this->User = $sessDetArr;
            } else if ($sessDetArr['flag'] == '1') {
                return $this->_getStatusMessage(9);
            } else {
                return $this->_getStatusMessage(10);
            }
        }
    }
    /*
     * Method name: _checkEntityLogin
     * Desc: Checks the unique id with the authentication type
     * Input: Unique id and the auth type
     * Output:  entity details if true, else false
     */

    protected function _checkEntityLogin($email) {

     		$checkFbIdQry = "select Entity_Id, First_Name, Last_Name, Profile_Pic_Url, Create_Dt from entity where Email = '" . $email . "'";
        $checkFbIdRes = mysql_query($checkFbIdQry, $this->db->conn);

        if (mysql_num_rows($checkFbIdRes) == 1) {

            $userDet = mysql_fetch_assoc($checkFbIdRes);

            if ($userDet['Profile_Pic_Url'] == "")
                $userDet['Profile_Pic_Url'] = $this->host . $this->default_profile_pic;

            return array('flag' => '1', 'entityId' => $userDet['Entity_Id'], 'fname' => $userDet['First_Name'], 'lname' => $userDet['Last_Name'], 'profilePic' => $userDet['Profile_Pic_Url'], 'joined' => $userDet['Create_Dt']);
        } else {

            return array('flag' => '0');
        }
    }

    /*
     * Method name: _getDeviceTypeName
     * Desc: Returns device name using device type id
     * Input: Device type id
     * Output:  Array with Device type name if true, else false
     */

    protected function _getDeviceTypeName($devTypeId) {

        $getDeviceNameQry = "select name from dev_type where dev_id = '" . $devTypeId . "'";
        $devNameRes = mysql_query($getDeviceNameQry, $this->db->conn);
        if (mysql_num_rows($devNameRes) > 0) {

            $devNameArr = mysql_fetch_assoc($devNameRes);
            return array('flag' => true, 'name' => $devNameArr['name']);
        } else {

            return array('flag' => false);
        }
    }
    /*
     * Method name: _getStatusMessage
     * Desc: Get details of an error from db
     * Input: Error number that need details
     * Output:  Returns an array with error details
     */

    protected function _getStatusMessage($errNo) {

        $msg = new getErrorMsg($errNo);
        return array('errNum' => $msg->errId, 'errFlag' => $msg->errFlag, 'errMsg' => $msg->errMsg); //,'test'=>$test_num);
    }

    /*
     * Method name: revokeSessToken
     * Desc: Revokes a session token
     * Input: Object Id and Token
     * Output: 1 for Success and 0 for Failure
     */

    protected function _getSessDetails($token, $device_id) {

        $curr_date = time();
        $curr_gmt_date = gmdate('Y-m-d H:i:s', $curr_date);

        $getDetQry = "select  us.oid, us.expiry_gmt, us.device, us.type, us.sid,ent.First_Name,ent.Last_Name, ent.Profile_Pic_Url from user_sessions us, entity ent where us.oid = ent.Entity_Id and us.token = '" . $token . "' and us.device = '" . $device_id . "'";
        $getDetRes = mysql_query($getDetQry, $this->db->conn);

        if (mysql_num_rows($getDetRes) > 0) {

            $sessDet = mysql_fetch_assoc($getDetRes);

            if ($sessDet['Profile_Pic_Url'] == "")
                $sessDet['Profile_Pic_Url'] = $this->host . $this->default_profile_pic;

            if ($sessDet['expiry_gmt'] > $curr_gmt_date)
                return array('flag' => '0', 'sid' => $sessDet['sid'], 'entityId' => $sessDet['oid'], 'deviceId' => $sessDet['device'], 'deviceType' => $sessDet['type'], 'fName' => $sessDet['First_Name'], 'lName' => $sessDet['Last_Name'], 'pPic' => $sessDet['Profile_Pic_Url']);
            else
                return array('flag' => '1');
        } else {
            return array('flag' => '2');
        }
    }

    /*
     * Method name: _entitySocialLogin
     * Desc: Checks the entity social login cred's
     * Input: Request data, unique id, token object
     * Output: returns array of output data if completed, else returns error message
     */

    protected function _entitySocialLogin($args, $token_obj) {

        $checkUserFbId = $this->_checkEntityLogin($args['ent_email']);

        if ($checkUserFbId['flag'] == '1') {
        	
        		if ($args['ent_auth_type'] == '3') {
        				return $this->_getStatusMessage(18);
        		} else if ($args['ent_auth_type'] == '2') {
        				$Qry = mysql_query("select Profile_Pic_Url from entity where Email = '" . $args['ent_email'] . "' and Password = '" . md5($args['ent_password']) . "'", $this->db->conn);
        				if (mysql_num_rows($Qry) <= 0) return $this->_getStatusMessage(20);
        				$pPic['url'] = $checkUserFbId['profilePic'];
        		} else {
		            $pPic = $this->_updateEntityDetails($args, $checkUserFbId['entityId']);
		            if ($args['ent_friend'] != '')
            				$this->_addFriends($args['ent_friends'], $checkUserFbId['entityId']);
						}
            $this->_updateActiveDateTime($checkUserFbId['entityId']);

            $checkUserSessionQry = "select sid, token, expiry_gmt from user_sessions where oid = '" . $checkUserFbId['entityId'] . "' and device = '" . $args['ent_dev_id'] . "'";
            $checkUserSessionRes = mysql_query($checkUserSessionQry, $this->db->conn);
            if (mysql_num_rows($checkUserSessionRes) == 1) {

                $updateArr = $token_obj->updateSessToken($checkUserFbId['entityId'], $args['ent_dev_id'], $args['ent_push_token']);

                $errMsgArr = $this->_getStatusMessage(2);
                return array('errNum' => $errMsgArr['errNum'], 'errFlag' => $errMsgArr['errFlag'], 'errMsg' => $errMsgArr['errMsg'], 'token' => $updateArr['Token'], 'profilePic' => $checkUserFbId['profilePic'], 'fName' => $checkUserFbId['fname'], 'lName' => $checkUserFbId['lname'], 'joined' => $checkUserFbId['joined']);
            } else {

                $devTypeNameArr = $this->_getDeviceTypeName($args['ent_device_type']);
                if (!$devTypeNameArr['flag']) {

                    return $this->_getStatusMessage(5);
                } else {

                    $createSessArr = $token_obj->createSessToken($checkUserFbId['entityId'], $devTypeNameArr['name'], $args['ent_dev_id'], $args['ent_push_token']);

                    $errMsgArr = $this->_getStatusMessage(2);
                    return array('errNum' => $errMsgArr['errNum'], 'errFlag' => $errMsgArr['errFlag'], 'errMsg' => $errMsgArr['errMsg'], 'token' => $createSessArr['Token'], 'profilePic' => $checkUserFbId['profilePic'], 'fName' => $checkUserFbId['fname'], 'lName' => $checkUserFbId['lname'], 'joined' => $checkUserFbId['joined']);
                }
            }
        } else {
						if ($args['ent_auth_type'] == '2')
								return $this->_getStatusMessage(20);
						else
            		return $this->_signupEntity($args, $token_obj);
            
        }
    }

    /*
     * Method name: _signupEntity
     * Desc: Signs up an entity
     * Input: Request data, unique id, token object
     * Output: returns array of output data if completed, else returns error message
     */

    protected function _signupEntity($args, $token_obj) {

        $devTypeNameArr = $this->_getDeviceTypeName($args['ent_device_type']);

        if (!$devTypeNameArr['flag'])
            return $this->_getStatusMessage(5);
        
        $curr_time = time();
        $curr_gmt_date = gmdate('Y-m-d H:i:s', $curr_time);

				if ($args['ent_auth_type'] == '1') {
						$profPicRes = FALSE;
						$file_to_open = 'pics/' . $args['ent_fb_id'] . '_' . $curr_time;
						$url = 'http://graph.facebook.com/' . $args['ent_fb_id'] . '/picture?type=large';
						$profPicRes = file_put_contents($file_to_open, file_get_contents($url));
						if ($profPicRes !== FALSE) {
		    				$prof_image_url = $this->host . $file_to_open;
		    				$pPic = $this->host . $file_to_open;
		    		} else {
		    				$prof_image_url = '';
		    				$pPic = $this->host . $this->default_profile_pic;
						}
        		$signupEntityQry = "insert into entity(Fb_Id, Email, First_Name, Last_Name, Profile_Pic_Url, Create_Dt, Last_Active_Dt_Time)
                		values('" . $args['ent_fb_id'] . "', '" . $args['ent_email'] . "', '" . $args['ent_first_name'] . "', '" . $args['ent_last_name'] . "', 
                		'" . $prof_image_url . "', '" . $curr_gmt_date . "', '" . $curr_gmt_date . "')";
        } else if ($args['ent_auth_type'] == '3') {
        		$signupEntityQry = "insert into entity(Email, Password, Create_Dt, Last_Active_Dt_Time) values ('" . $args['ent_email'] . "', 
        						'" . md5($args['ent_password']) . "', '" . $curr_gmt_date . "', '" . $curr_gmt_date . "')";
        } else {
        		return $this->_getStatusMessage(8);
        }
        mysql_query($signupEntityQry, $this->db->conn);

        $newEntityId = mysql_insert_id();
        
        if ($newEntityId > 0) {
        		
            if ($args['ent_auth_type'] == '1' && $args['ent_friend'] != '')
            		$this->_addFriends($args['ent_friends'], $newEntityId);
            		
        		$createSessArr = $token_obj->createSessToken($newEntityId, $devTypeNameArr['name'], $args['ent_dev_id'], $args['ent_push_token']);
            $errMsgArr = $this->_getStatusMessage(3);
            
            
            return array('errNum' => $errMsgArr['errNum'], 'errFlag' => $errMsgArr['errFlag'], 'errMsg' => $errMsgArr['errMsg'], 'token' => $createSessArr['Token'], 'fName' => $args['ent_first_name'], 'lName' => $args['ent_last_name'], 'profilePic' => $pPic, 'joined' => $curr_gmt_date);

        } else {
            return $this->_getStatusMessage(7);
        }
    }

    /*
     * Method name: _updateEntityDetails
     * Desc: Updates entity details
     * Input: Request data, entity_id
     * Output: nothing
     */

    protected function _updateEntityDetails($args, $entity_id) {

/*				$file_to_open = 'pics/' . $entity_id;
				$url = 'http://graph.facebook.com/' . $args['ent_fb_id'] . '/picture?type=large';
				$profPicRes = file_put_contents($file_to_open, file_get_contents($url));

        if ($profPicRes !== FALSE)
        		$prof_image_url = $this->host . $file_to_open;
        else
        		$prof_image_url = $this->host . $this->default_profile_photo;
*/
		    $updateAdditionalDetailsQry = "
                 update entity
                        set
                        Fb_Id = '" . $args['ent_fb_id'] . "',
                        First_Name = '" . $args['ent_first_name'] . "',
                        Last_Name = '" . $args['ent_last_name'] . "',
                        where
                        Entity_Id = '" . $entity_id . "'";

        mysql_query($updateAdditionalDetailsQry, $this->db->conn);
    }
    
    protected function _updateActiveDateTime($entId) {

        $curr_date = time();
        $curr_gmt_date = gmdate('Y-m-d H:i:s', $curr_date);

        $updateQry = "update entity set Last_Active_Dt_Time = '" . $curr_gmt_date . "' where Entity_Id = '" . $entId . "'";
        mysql_query($updateQry, $this->db->conn);

        if (mysql_affected_rows() > 0)
            return true;
        else
            return false;
    }

    /*
     * Method name: _addFriends
     * Desc: Inserts friends for a user
     * Input: Request data, entity_id
     * Output: 1 - success, 0 - failure
     */

    protected function _addFriends($friend_string, $ent_id) {

        $friends_arr = array_filter(array_unique(explode(',', $friend_string)));
        foreach ($friends_arr as $value) {

						$friendidQry = mysql_query("select Entity_Id from entity where Fb_Id = '" . $value . "'", $this->db->conn);
						if (mysql_num_rows($friendidQry) == 1) {
								$friendid = mysql_fetch_assoc($friendidQry);
						
								$checkFriendQry = mysql_query("select fid from friends where (Entity1_Id = '" . $friendid['Entity_Id'] . "' and Entity2_Id = '" . $ent_id . "') or (Entity1_Id = '" . $ent_id . "' and Entity2_Id = '" . $friendid['Entity_Id'] . "')", $this->db->conn);

								if (mysql_num_rows($checkFriendQry) == 0) {
            				$insertFriendQry = "insert into friends(Entity1_Id,Entity2_Id) values ('" . $friendid['Entity_Id'] . "','" . $ent_id . "')";
            				$insertFriendRes = mysql_query($insertFriendQry, $this->db->conn);
    		        }
            }
        }
    }
		
		protected function _sendWelcomeMail($name, $to) {
				$subject = 'Welcome to Traincase!';
				$msg = '<html>
									<head>
										<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Source+Sans+Pro:300">
										<style type="text/css">
											<!--
												body {
								        	font-family: \'Source Sans Pro\', sans-serif, serif;
								      	}
											-->
										</style>
									</head>
									<body style = "width:100%; margin:0; background:#F0F0F0;">
										<div style="width:100%;margin:0 auto;padding-top: 2%;padding-bottom:2%;text-align: center;">
											<img src="http://www.fovealdesign.com/traincase_dev/images/traincase_icon.png" width="10%" alt>
											<div style = "margin-left:5%;margin-right:5%;width: 90%;text-align:left;">
												<p style = "font-size: 18px; color: #777777">Welcome to Traincase</p>
											</div>
											<div style = "margin:0 5% 0 5%;width: 90%;box-shadow: 1px 2px 3px #888888;background:#FFFFFF">
												<div style = "padding:20px;text-align:left;">
													<p style = "font-size: 15px; color: #777777">Hi ' . $name . ',</p>
													<p style = "font-size: 15px; color: #777777">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc sed risus sit amet turpis fringilla interdum. Nunc gravida enim auctor elementum congue. Aliquam eu porta odio, quis tempus ligula. Phasellus vel convallis massa, vel sollicitudin risus. </p>
													<p style = "font-size: 15px; color: #777777">Aenean volutpat, libero et bibendum venenatis, arcu sem tempus diam, sed condimentum arcu nibh sit amet est. In fermentum sem vitae diam malesuada, vel dictum mi aliquet. Fusce auctor metus urna, sit amet porta turpis hendrerit at.</p>
												</div>
												<div style = "margin-top:-30px;padding:20px;text-align:left;">
													<ul style = "margin-left:-35px;list-style-type:none;">
														<li style = "font-size: 15px; color: #777777">Warm Regards,</li>
														<li style = "font-size: 15px; color: #777777">Misty P.</li>
														<li style = "font-size: 12px; color: #777777">Co-Founder of Traincase</li>
													</ul>
												</div>
											</div>
										</div>
									</body>
								</html>';
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

				$headers .= 'From: misty@traincase.net' . "\r\n";
				$flag = mail($to, $subject, $msg, $headers);
				return $flag;
		}
    
    /*
     * Method name: _sendPush
     * Desc: Divides the tokens according to device type and sends a push accordingly
     * Input: Request data, entity_id
     * Output: 1 - success, 0 - failure
     */

    protected function _sendPush($senderId, $recEntityArr, $message, $notifType, $sname, $datetime, $msg_id = NULL, $msg_type = NULL) {

        $entity_string = '';
        $aplTokenArr = array();
        $andiTokenArr = array();
        $return_arr = array();

//        print_r($recEntityArr);
//        echo '-'.$senderId.'-sid,';
//        echo '-'.$message.'-msg,';
//        echo '-'.$msg_id.'-msid,';

        foreach ($recEntityArr as $entity) {

            $insertNotesQry = "insert into notifications(notif_type,sender,receiver,message,notif_dt) values('" . $notifType . "','" . $senderId . "','" . $entity . "','" . $message . "','" . $datetime . "')";
            mysql_query($insertNotesQry, $this->db->conn);
            $ins_id = mysql_insert_id();

            if ($ins_id > 0)
                $return_arr[] = array($entity => $ins_id);

            $entity_string = $entity . ',';
        }

        $entity_comma = rtrim($entity_string, ',');
//echo '--'.$entity_comma.'--';
        $getUserDevTypeQry = "select distinct type,push_token from user_sessions where oid in (" . $entity_comma . ") and loggedIn = '1' and LENGTH(push_token) > 63 ";
        $getUserDevTypeRes = mysql_query($getUserDevTypeQry, $this->db->conn);

        if (mysql_num_rows($getUserDevTypeRes) > 0) {

            while ($tokenArr = mysql_fetch_assoc($getUserDevTypeRes)) {

                if ($tokenArr['type'] == 1)
                    $aplTokenArr[] = $tokenArr['push_token'];
                else if ($tokenArr['type'] == 2)
                    $andiTokenArr[] = $tokenArr['push_token'];
            }

            $aplTokenArr = array_values(array_filter(array_unique($aplTokenArr)));
            $andiTokenArr = array_values(array_filter(array_unique($andiTokenArr)));
//            print_r($andiTokenArr);
            if (count($aplTokenArr) > 0)
                $aplResponse = $this->_sendApplePush($aplTokenArr, $message, $notifType, $sname, $datetime, $msg_id, $msg_type);

            if (count($andiTokenArr) > 0)
                $andiResponse = $this->_sendAndroidPush($andiTokenArr, $message, $notifType, $sname, $datetime, $msg_id, $msg_type);

//            echo '---';
//print_r($aplResponse);
//echo '---';
//print_r($andiResponse);
//echo '---';
//print_r($aplTokenArr);
//echo '---';
//print_r($andiTokenArr);
//echo '---';

            if ($aplResponse['errorNo'] != '')
                $errNum = $aplResponse['errorNo'];
            else if ($andiResponse['errorNo'] != '')
                $errNum = $andiResponse['errorNo'];
            else
                $errNum = 46;

            return array('insEnt' => $return_arr, 'errNum' => $errNum);
        } else {
            return array('insEnt' => $return_arr, 'errNum' => 45); //means push not sent
        }
    }

    protected function _sendApplePush($tokenArr, $message, $notifType, $sname, $datetime, $msg_id = NULL, $msg_type = NULL) {

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $this->ios_cert_path);
        stream_context_set_option($ctx, 'ssl', 'passphrase', $this->ios_cert_pwd);

        $apns_fp = stream_socket_client($this->ios_cert_server, $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

        if ($apns_fp) {

            if ($msg_type == NULL)
                $msg_type = 0;

            if ($msg_id == NULL)
                $msg_id = 0;

            $body['aps'] = array(
//'badge' => $this->badgeCount,
                'alert' => $message,
                'nt' => $notifType,
                'sound' => 'default',
                'sname' => $sname,
                'dt' => $datetime,
                'mt' => $msg_type,
                'mid' => $msg_id
            );

            $payload = json_encode($body);

            $msg = '';
            foreach ($tokenArr as $token) {
                $msg .= chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;
            }

            $result = fwrite($apns_fp, $msg, strlen($msg));

            if (!$result)
                return array('errorNo' => 46);
            else
                return array('errorNo' => 44);
        } else {
            return array('errorNo' => 30);
        }
    }

    protected function _sendAndroidPush($tokenArr, $message, $notifType, $sname, $datetime, $msg_id = NULL, $msg_type = NULL) {

//        print_r($tokenArr);


        if ($msg_type == NULL)
            $msg_type = 0;

        if ($msg_id == NULL)
            $msg_id = 0;

        $data = array('payload' => $message, 'action' => $notifType, 'sname' => $sname, 'dt' => $datetime, 'mt' => $msg_type, 'mid' => $msg_id); // action defines whether any action should take after recieving the push

        $fields = array(
            'registration_ids' => $tokenArr,
            'data' => $data,
        );

        $headers = array(
            'Authorization: key=' . $this->androidApiKey,
            'Content-Type: application/json'
        );
// Open connection
        $ch = curl_init();

// Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $this->androidUrl);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

// Execute post
        $result = curl_exec($ch);

        curl_close($ch);
//        echo 'Result from google:' . $result . '---';
        $res_dec = json_decode($result);

        if ($res_dec->success >= 1)
            return array('errorNo' => 44, 'result' => $result);
        else
            return array('errorNo' => 46, 'result' => $result);
    }
}

if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {

    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

try {

    $API = new MyAPI($_SERVER['REQUEST_URI'], $_REQUEST, $_SERVER['HTTP_ORIGIN']);

    echo $API->processAPI();
} catch (Exception $e) {

    echo json_encode(Array('error' => $e->getMessage()));
}
?>
