//used from sign up page
function on_facebook_auth() {
    set_status('Please wait... Signing up through Facebook...');
        
    var params = 'action=facebook_login';
    
    var request = new Request({
        url: 'facebook_action.php',
        onSuccess: function(txt, xml) {
        	if (txt == 'ok') {
                location.replace('home.php');
            }
        	else
        	{
        		if (txt == 'connect_error')	{
        			alert('An error occured while trying to get your facebook profile.')
        		}
        		else if (txt == 'create_error') {
	                alert('An error occured while signing up with Facebook account.');
	            }
	            else if (txt == 'update_error') {
	                alert('An error occured while associating your existing account with Facebook.');
	            }
	            else if (txt == 'hacking_detected') {
	                alert('Another Facebook user has already used this email address.');
	            }
	            else if (txt == "bad_login") {
	                location.replace(root + '/errors/failed_login.php?dir=members');
	            }
	            else	{
	            	alert(txt);
	            }
                
                set_status('');
                logout_from_facebook();
                return;
            }
        }
    });

    request.send(params);
}

//used in welcome page
function on_facebook_auth_welcome() {
	if ($('not_logged_in_bar').getStyle('display') == 'none') {
        return;
    }
	show_login_progress();
	
    var params = 'action=facebook_login';
    
    var request = new Request({
        url: 'members/facebook_action.php',
        onSuccess: function(txt, xml) {
        	if (txt == 'ok') {
                location.replace('members/home.php');
            }
        	else
        	{
        		hide_login_progress();
        		if (txt == 'connect_error')	{
        			alert('An error occured while trying to get your facebook profile.')
        		}
        		else if (txt == 'create_error') {
	                alert('An error occured while signing up with Facebook account.');
	            }
	            else if (txt == 'update_error') {
	                alert('An error occured while associating your existing account with Facebook.');
	            }
	            else if (txt == 'hacking_detected') {
	                alert('Another Facebook user has already used this email address.');
	            }
	            else if (txt == "bad_login") {
	                location.replace(root + '/errors/failed_login.php?dir=members');
	            }
	            else	{
	            	alert(txt);
	            }
                
                set_status('');
                logout_from_facebook();
                return;
            }
        }
    });

    request.send(params);
}

function logout_from_facebook() {
    
}