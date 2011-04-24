var seed = "";
var sid = "";
var employers_index = 0;

function verify() {
    if ($('industry').options[$('industry').selectedIndex].value == 0 && 
        $('employer').options[$('employer').selectedIndex].value == 0 && 
        $('country').options[$('country').selectedIndex].value == '' && 
        ($('keywords').value == 'Job title or keywords' || $('keywords').value == '')) {
        alert('Please select an employer, industry/sub-industry or enter the job title/keywords in order to do a search. You may choose to do all if you wish to do a more specific search.');
        return false;
    }
    
    if ($('keywords').value == 'Job title or keywords') {
        $('keywords').value = '';
    }
    
    return true;
}

function login() {
    if (!isEmail($('login_email_addr').value) || isEmpty($('login_password').value)) {
        alert("Email and Password fields cannot be empty.");
        return false;
    } 
    
    if (seed == "") {
        location.replace(root + "/errors/temporarily_down.php");
        return false;
    }
    
    
    var hash = sha1($('login_email_addr').value + md5($('login_password').value) + seed);
    var params = 'id=' + $('login_email_addr').value + '&sid=' + sid + '&hash=' + hash + '&action=login';
    
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
                
                alert(msg);
                return false;
            }
            
            var status = xml.getElementsByTagName('status');
            
            if (status[0].childNodes[0].nodeValue == 'ok') {
                location.replace(root + '/members/home.php');
            }
        },
        onRequest: function(instance) {
            // set_status("Logging in...");
        }
    });
    
    request.send(params);
}

function on_linkedin_auth() {
    IN.API.Profile("me").result(function(me) {
        var linkedin_id = me.values[0].id;
        var linkedin_firstname = me.values[0].firstName;
        var linkedin_lastname = me.values[0].lastName;
        
        if (isEmpty(linkedin_id) || linkedin_id == null) {
            alert('Cannot login from LinkedIn. Please use your normal login instead.');
            return;
        }
        
        var params = 'id=' + linkedin_id + '&action=linkedin_auth';
        var request = new Request({
            url: root + "/members/login_action.php",
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    alert('Cannot login from LinkedIn. Please use your normal login instead.');
                    return;
                }
                
                var new_linkedin = false;
                var member_id = '';
                if (isEmpty(txt)) {
                    member_id = prompt('Please enter your email address again.');
                    if (member_id == false) {
                        alert('You have chosen not to sign in through LinkedIn.' + "\n\n" + 'Cannot login from LinkedIn. Please use your normal login instead.');
                        return;
                    }
                    
                    while (!isEmail(member_id)) {
                        member_id = prompt('The email address entered is invalid.' + "\n\n" + 'Please enter your email address again.');
                    }
                    
                    new_linkedin = true;
                } else {
                    if (!isEmail(txt)) {
                        alert('Cannot login from LinkedIn. Please use your normal login instead.');
                        return;
                    }
                    
                    member_id = txt;
                }
                
                login_via_linkedin(member_id, linkedin_id, linkedin_firstname, 
                                   linkedin_lastname, new_linkedin);
            }
        });

        request.send(params);
    });
}

function login_via_linkedin(_member_id, _linkedin_id, _linkedin_firstname,
                            _linkedin_lastname, _is_new) {
    alert('member_id: ' + _member_id + "\n" + 'linkedin_id: ' + _linkedin_id);
    
    var params = 'id=' + _member_id + '&action=linkedin_login';
    params = params + '&linkedin_id=' + _linkedin_id;
    params = params + '&linkedin_firstname=' + _linkedin_firstname;
    params = params + '&linkedin_lastname=' + _linkedin_lastname;
    var hash = sha1(_member_id + md5(_linkedin_id) + seed);
    params = params + '&sid=' + sid + '&hash=' + hash;
    
    if (_is_new) {
        params = params + '&is_new=1';
    } else {
        params = params + '&is_new=0';
    }
    
    alert(params);
}

function get_seed() {
    var seed_uri = root + "/members/seed.php";
    var request = new Request({
        url: seed_uri,
        onSuccess: function(txt, xml) {
            // set_status("");
            
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
            // set_status("Loading...");
        }
    });
    
    request.send();
}

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
                    alert('The answer is incorrect.');
                    return false;
                }
                
                alert('Password was successfully reset. Please check your inbox for temporary password.');
                // set_status();
            },
            onRequest: function(instance) {
                // set_status('Resetting password...');
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

function set_employers_mouse_events() {
    var employers = new Array();
    var number_of_tabs = 0;
    
    for (var i=0; i < $('employer_tabs').childNodes.length; i++) {
        if ($('employer_tabs').childNodes[i].nodeName == 'DIV') {
            number_of_tabs++;
        }
    }
    
    for (var i=0; i < number_of_tabs; i++) {
        var tab = 'employers_' + i;
        employers[i] = new Fx.Tween(tab);
        
        if (i > 0) {
            employers[i].set('display', 'none');
        }
    }
    
    $('toggle_right').addEvent('click', function(e) {
        e.stop();
        
        employers[employers_index].start('opacity', '0');
        employers[employers_index].set('display', 'none');
        
        employers_index++;
        if (employers_index >= number_of_tabs) {
            employers_index = 0;
        }
        
        employers[employers_index].set('display', 'block');
        employers[employers_index].set('opacity', '0');
        employers[employers_index].start('opacity', '1');
    });
    
    $('toggle_left').addEvent('click', function(e) {
        e.stop();
        
        employers[employers_index].start('opacity', '0');
        employers[employers_index].set('display', 'none');
        
        employers_index--;
        if (employers_index < 0) {
            employers_index = number_of_tabs - 1;
        }
        
        employers[employers_index].set('display', 'block');
        employers[employers_index].set('opacity', '0');
        employers[employers_index].start('opacity', '1');
    });
}

function onDomReady() {
    set_employers_mouse_events();
}

function onLoaded() {
    if ($('login_email_addr') != null) {
        new OverText($('login_email_addr'));
        new OverText($('login_password'));
    }
    
    initialize_page();
    
    if ($('login') != null) {
        get_seed();
        $('login').addEvent('click', login);
        $('login_password').addEvent('keypress:keys(enter)', login);
    }
}

window.addEvent('domready', onDomReady);
window.addEvent('load', onLoaded);
