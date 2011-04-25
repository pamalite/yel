var employers_index = 0;
var seed = "";
var sid = "";

function login() {
    if ($('id').value == "" || $('password').value == "") {
        set_status("Login ID and Password fields cannot be empty.");
        return false;
    } 
    
    if (seed == "") {
        location.replace(root + "/errors/temporarily_down.php");
        return false;
    }
    
    var login_uri = root + "/employers/login_action.php";
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
                    location.replace(root + '/errors/failed_login.php?dir=employers');
                }
                
                set_status(msg);
                return false;
            }
            
            var status = xml.getElementsByTagName('status');
            var is_new = xml.getElementsByTagName('is_new');
            
            if (status[0].childNodes[0].nodeValue == 'ok') {
                if (is_new[0].childNodes[0].nodeValue == '1') {
                    alert('First time login detected!' + "\n\nSince this is the first time you logged in, please change the password to prevent security breach.");
                    location.replace(root + '/employers/profile.php');
                } else {
                    location.replace(root + '/employers/resumes.php');
                }
                
            }
        },
        onRequest: function(instance) {
            set_status("Logging in...");
        }
    });
    
    request.send(params);
}

function get_seed() {
    var seed_uri = root + "/employers/seed.php";
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
    initialize_page();
    get_seed();
    $('login').addEvent('click', login);
    set_employers_mouse_events();
}

window.addEvent('domready', onDomReady);
