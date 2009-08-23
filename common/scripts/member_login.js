var seed = "";
var sid = "";

function close_get_password_hint() {
    $('div_get_password_hint_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_get_password_hint() {
    $('div_blanket').setStyle('display', 'block');
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_get_password_hint_form').getStyle('height'));
    var div_width = parseInt($('div_get_password_hint_form').getStyle('width'));
    
    if (typeof window.innerHeight != 'undefined') {
        window_height = window.innerHeight;
    } else {
        window_height = document.documentElement.clientHeight;
    }
    
    if (typeof window.innerWidth != 'undefined') {
        window_width = window.innerWidth;
    } else {
        window_width = document.documentElement.clientWidth;
    }
    
    $('div_get_password_hint_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_get_password_hint_form').setStyle('left', ((window_width - div_width) / 2));
    $('div_get_password_hint_form').setStyle('display', 'block');
}

function get_password_hint() {
    if (!isEmail($('email_addr').value)) {
        alert('Please provide the e-mail address used as your login.\n\nIf you have forgotten your login e-mail, please contact our support team.');
        return false;
    }
    
    $('password_hint').set('html', '');
    
    var params = 'email_addr=' + $('email_addr').value + '&action=get_password_hint';
    
    var uri = root + "/members/login_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while searching password hint.');
                return false;
            }
            
            if (txt == '0') {
                return false;
            }
            
            var hints = xml.getElementsByTagName('hint');
            $('password_hint').set('html', hints[0].childNodes[0].nodeValue);
            
            close_get_password_hint();
            show_reset_password();
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Searching password hint...');
        }
    });
    
    request.send(params);
}

function close_reset_password() {
    $('div_reset_password_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_reset_password() {
    $('div_blanket').setStyle('display', 'block');
    $('div_reset_password_form').setStyle('height', '250px');
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_reset_password_form').getStyle('height'));
    var div_width = parseInt($('div_reset_password_form').getStyle('width'));
    
    if (typeof window.innerHeight != 'undefined') {
        window_height = window.innerHeight;
    } else {
        window_height = document.documentElement.clientHeight;
    }
    
    if (typeof window.innerWidth != 'undefined') {
        window_width = window.innerWidth;
    } else {
        window_width = document.documentElement.clientWidth;
    }
    
    $('div_reset_password_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_reset_password_form').setStyle('left', ((window_width - div_width) / 2));
    $('div_reset_password_form').setStyle('display', 'block');
}

function reset_password() {
    if (isEmpty($('hint_answer').value)) {
        alert('You need to answer the question to proceed with the password reset.');
        return false;
    }
    
    var params = 'email_addr=' + $('email_addr').value + '&action=reset_password';
    params = params + '&answer='+ $('hint_answer').value;
    
    var uri = root + "/members/login_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while resetting password hint.');
                return false;
            }
            
            if (txt == 'bad') {
                close_reset_password();
                set_status('Password was not reset.');
                return false;
            }
            
            close_reset_password();
            set_status('Password was successfully reset. Please check your inbox for temporary password.');
        },
        onRequest: function(instance) {
            set_status('Searching password hint...');
        }
    });
    
    request.send(params);
}

function login() {
    if ($('id').value == "" || $('password').value == "") {
        set_status("Login ID and Password fields cannot be empty.");
        return false;
    } 
    
    if (seed == "") {
        location.replace(root + "/errors/temporarily_down.php");
        return false;
    }
    
    var login_uri = root + "/members/login_action.php";
    var hash = sha1($('id').value + md5($('password').value) + seed);
    var params = 'id=' + $('id').value + '&sid=' + sid + '&hash=' + hash;
    var function_call = login_uri;
    var request = new Request({
        url: function_call,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status("");
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
        onFailure: function() {
            location.replace(root + '/errors/temporarily_down.php');
        },
        onRequest: function(instance) {
            set_status("Loading...");
        }
    });
    
    request.send();
}

function onDomReady() {
    set_root();
    get_seed();
    $('login').addEvent('click', login);
    
    if (signed_up) {
        alert('An activation e-mail has been sent to your registered e-mail address. You need to activate your account before you can sign in.');
    }
    
    if (activated) {
        alert('Your account has been activated. You may now sign in using your registered e-mail address and the password that you have created during registration.');
    }
}

window.addEvent('domready', onDomReady);
