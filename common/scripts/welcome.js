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
        set_status("Sign In Email and Password fields cannot be empty.");
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
                
                // set_status(msg);
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
    
    get_seed();
    $('login').addEvent('click', login);
}

window.addEvent('domready', onDomReady);
window.addEvent('load', onLoaded);
