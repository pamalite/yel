<?php
//Author: Ing Chen Lee
//Created: 2011-09-24
//Description: integrate facebook social connect to yellow elevator website and database
require_once dirname(__FILE__). "/../private/lib/utilities.php";
require_once dirname(__FILE__). "/../private/lib/facebook.php";

session_start();

if (!isset($_POST['action'])) {
    redirect_to('login.php');
}

$facebook = new Facebook(array(
  'appId'  => $GLOBALS['fb_app_id'],
  'secret' => $GLOBALS['fb_app_secret'],
));
$user = $facebook->getUser();
if ($user) {
  try {
    $user_profile = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    error_log($e);
    $user = null;
  }
}

//used for member sign up
if ($_POST['action'] == 'facebook_login') {
	try 
	{
		if ($user_profile)
		{
			$id = $user_profile["email"];
		    $facebook_id = $user_profile["id"];
		    $facebook_firstname = $user_profile["first_name"];
		    $facebook_lastname = $user_profile["last_name"];
		}
		else 
		{
			echo 'connect_error';
	        exit();
		}
	    
	    if (empty($sid)) {
	        $seed = Seed::generateSeed();
	        $hash = sha1($id. md5($facebook_id). $seed['login']['seed']);
	        $sid = $seed['login']['id'];
	    }
	    
	    $_SESSION['yel']['member']['id'] = $id;
	    $_SESSION['yel']['member']['hash'] = $hash;
	    $_SESSION['yel']['member']['sid'] = $sid;
	    $_SESSION['yel']['member']['facebook_id'] = $facebook_id;
	    
	    header('Content-type: text/xml');
	    
	    $member = new Member($id, $sid);
	    // 1. find whether this member exists, from the ID
	    $criteria = array(
	        'columns' => "COUNT(*) AS is_exists", 
	        'match' => "email_addr = '". $id. "'"
	    );
	    
	    $result = $member->find($criteria);
	    if ($result[0]['is_exists'] != '1') {
	        // sign the member up
	        $joined_on = today();
	        $data = array();
	        $data['password'] = md5(generate_random_string_of(6));
	        $data['phone_num'] = '0';
	        $data['firstname'] = $facebook_firstname;
	        $data['lastname'] = $facebook_lastname;
	        $data['facebook_id'] = $facebook_id;
	        $data['joined_on'] = $joined_on;
	        $data['updated_on'] = $joined_on;
	        $data['active'] = 'Y';
	        $data['checked_profile'] = 'Y';
	        
	        if (is_null($data['firstname']) || empty($data['firstname']) || 
	            is_null($data['lastname']) || empty($data['lastname'])) {
	            $data['firstname'] = 'Unknown';
	            $data['lastname'] = 'Unknown';
	        }
	        
	        if ($member->create($data) === false) {
	            $_SESSION['yel']['member']['hash'] = "";
	            echo "create_error";
	            exit();
	        } 
	    } else {
	        // reverse check by looking for facebook_id from id.
	        // if it is empty, then update. 
	        // if it is not a match with the supplied facebook_id, then error out
	        $stored_facebook_id = $member->getFacebookId();
	        if ($stored_facebook_id == false || is_null($stored_facebook_id)) {
	            // update
	            $data = array();
	            $data['facebook_id'] = $facebook_id;
	            $member->setAdmin(true);
	            if ($member->update($data) === false) {
	                $_SESSION['yel']['member']['hash'] = "";
	                echo "update_error";
	                exit();
	            }
	        }
	    }
	    
	    // 2. set session and go
	    if (!$member->setSessionWith($hash)) {
	        $_SESSION['yel']['member']['hash'] = "";
	        echo "bad_login";
	        exit();
	    }
	    
	    echo "ok";
	    exit();
	}
	catch (Exception $e)
	{
		echo "error";
	}
}

//used for login from welcome page
if ($_POST['action'] == 'facebook_auth') {
	if ($user_profile)
	{
	    $member = new Member(); 
	    $email = $member->getEmailFromFacebook($user_profile["id"]);
	    
	    if (($email === false) || is_null($email)) {
	        echo 'not_registered';
	    } else {
	        echo $email;
	    }
	}
	else {
		echo "not_login";
	}
    exit();
}
?>