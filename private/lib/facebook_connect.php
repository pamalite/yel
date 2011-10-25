<?php
require_once dirname(__FILE__). "/facebook.php";

function get_current_facebook_user()
{
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
	
	if ($user_profile){
		$id = $user_profile["email"];
		
		if (empty($sid)) {
	        $seed = Seed::generateSeed();
	        $hash = sha1($id. md5($facebook_id). $seed['login']['seed']);
	        $sid = $seed['login']['id'];
	    }
	    $member = new Member($id, $sid);
	    
	    // 1. find whether this member exists, from the ID
	    $criteria = array(
	        'columns' => "COUNT(*) AS is_exists", 
	        'match' => "email_addr = '". $id. "'"
	    );
	    
	    $result = $member->find($criteria);
	    
	    if ($result[0]['is_exists'] == '1') {
			// reverse check by looking for facebook_id from id.
	        $stored_facebook_id = $member->getFacebookId();
	        if ($stored_facebook_id == $user_profile["id"]) {
	            return $id;
	        }
	    }
	}
	return "";
}
?>