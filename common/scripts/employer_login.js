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
            
            if (status[0].childNodes[0].nodeValue == 'ok') {
                location.replace(root + '/employers/resumes.php');
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
        onFailure: function() {
            location.replace(root + '/errors/temporarily_down.php');
        },
        onRequest: function(instance) {
            set_status("Loading...");
        }
    });
    
    request.send();
}

function drop_contact_now() {
    if ($('company').value == '' || $('company').value == '-') {
        alert('You will need to provide the name of your company.');
        return false;
    } 
    
    if ($('phone').value == '' && $('email').value == '') {
        alert('You will need to provide at least a way to contact you. \n\n Perhaps an e-mail address or telephone number?');
        return false;
    }
    
    if ($('email').value != '') {
        if (!isEmail($('email').value)) {
            alert('Thank you for providing an email. However, it seems like the email address is incorrect. \n\n Please try again.');
            return false;
        }
    }
    
    if ($('contact').value == '') {
        var is_fine = confirm("Not to be rude, perhaps it is fine not to address you when we contact you?");
        if (!is_fine) {
            return false;
        }
    }
    
    var company = $('company').value;
    var phone = $('phone').value;
    var email = $('email').value;
    var contact = $('contact').value;
    var uri = root + "/common/php/drop_contact.php";
    var params = 'company=' + company + '&phone=' + phone + '&email=' + email + '&contact=' + contact;
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == 'ok') {
                alert('Great news! We have successfully received your contact and you will hear from us shortly.');
                close_contact_drop_form();
            } else {
                alert('Somehow your contact drop is not working. Perhaps you should try again later.');
            }
        }
    });
    
    request.send(params);
    
    return false;
}

function close_contact_drop_form() {
    close_window('contact_drop_form');
}

function show_contact_drop_form() {
    show_window('contact_drop_form');
}

function onDomReady() {
    initialize_page();
    get_seed();
    $('login').addEvent('click', login);
    $('drop').addEvent('click', drop_contact_now);
}

window.addEvent('domready', onDomReady);
