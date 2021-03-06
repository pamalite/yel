var seed = "";
var sid = "";

function close_password_reset_window(_is_reset_password) {
    if (_is_reset_password) {
        if (!isEmail($('email_addr').value)) {
            alert('Please provide the e-mail address used as your login.\n\nIf you have forgotten your login e-mail, please contact our support team.');
            return false;
        }
        
        var params = 'id=' + $('email_addr').value + '&action=reset_password';
        
        var uri = root + "/members/login_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    alert('An error occured while resetting password.');
                    return false;
                }

                if (txt == 'bad') {
                    alert('The email address provided is invalid.');
                    return false;
                }
                
                alert('Password was successfully reset. Please check your inbox for temporary password.');
                set_status();
            },
            onRequest: function(instance) {
                set_status('Resetting password...');
            }
        });

        request.send(params);
    }
    
    close_window('div_password_reset_window');
}

function show_password_reset_window() {
    $('div_password_reset_form').setStyle('display', 'block');
    
    show_window('div_password_reset_window');
}

function login() {
    if (!isEmail($('id').value) || isEmpty($('password').value)) {
        set_status("Sign In Email and Password fields cannot be empty.");
        return false;
    } 
    
    if (seed == "") {
        location.replace(root + "/errors/temporarily_down.php");
        return false;
    }
    
    
    var hash = sha1($('id').value + md5($('password').value) + seed);
    var params = 'id=' + $('id').value + '&sid=' + sid + '&hash=' + hash + '&action=login';
    
    var uri = root + "/members/login_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (xml.getElementsByTagName('errors').length != 0) {
                var errors = xml.getElementsByTagName('error');
                var msg = errors[0].childNodes[0].nodeValue;
                if (msg == "bad_login") {
                    location.replace(root + '/errors/failed_login.php?dir=members');
                }
                
                set_status(msg);
                return false;
            }
            
            var status = xml.getElementsByTagName('status');
            
            if (status[0].childNodes[0].nodeValue == 'ok') {
                location.replace(root + '/members/home.php' + job_to_redirect);
            }
        },
        onRequest: function(instance) {
            set_status("Logging in...");
        }
    });
    
    request.send(params);
}

function get_seed() {
    var seed_uri = root + "/members/seed.php";
    var request = new Request({
        url: seed_uri,
        onSuccess: function(txt, xml) {
            set_status("");
            
            if (xml.getElementsByTagName('errors').length != 0) {
                location.replace(root + '/errors/temporarily_down.php');
            }
            
            var sids = xml.getElementsByTagName('id');
            var seeds = xml.getElementsByTagName('seed');
            
            sid = sids[0].childNodes[0].nodeValue;
            seed = seeds[0].childNodes[0].nodeValue;
        },
        onRequest: function(instance) {
            set_status("Loading...");
        }
    });
    
    request.send();
}

function onDomReady() {
    initialize_page();
    get_seed();
    
    $('login').addEvent('click', login);
    
    if (signed_up) {
        alert('An activation e-mail has been sent to your registered e-mail address. You need to activate your account before you can sign in.');
    }
    
    if (activated) {
        alert('Your account has been activated. You may now sign in using your registered e-mail address and the password that you have created during sign up.');
    }
}

window.addEvent('domready', onDomReady);