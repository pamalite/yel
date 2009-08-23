var selected_tab = 'li_smart_invite';
var contacts_list = new ListBox('contacts', 'contacts_list', true);

var username = '';
var service = '';

function duplicated(emails, email) {
    if (isEmpty(emails)) {
        return false;
    }
    
    var temp = emails.split(',');
    for (var i=0; i < temp.length; i++) {
        if (email == temp[i]) {
            return true;
        }
    }
    
    return false;
}

function validate() {
    if (isEmpty($('oi_service').value) || $('oi_service').value == '0') {
        set_status('You need to select either e-mail or social networking service.');
        return false;
    }
    
    if (isEmpty($('username').value)) {
        set_status('You need to enter the username to the service.');
        return false;
    }
    
    if (isEmpty($('password').value)) {
        set_status('You need to enter the password to the service.');
        return false;
    }
    
    return true;
}

function show_smart_invite() {
    selected_tab = 'li_smart_invite';
    $('div_smart_invite').setStyle('display', 'block');
    $('div_manual_invite').setStyle('display', 'none');
    
    $('li_smart_invite').setStyle('border', '1px solid #CCCCCC');
    $('li_manual_invite').setStyle('border', '1px solid #0000FF');
    
    set_status('');
    
    $('get_contacts_form').setStyle('display', 'block');
    $('send_invite_form').setStyle('display', 'none');
}

function show_manual_invite() {
    selected_tab = 'li_manual_invite';
    $('div_smart_invite').setStyle('display', 'none');
    $('div_manual_invite').setStyle('display', 'block');
    
    $('li_smart_invite').setStyle('border', '1px solid #0000FF');
    $('li_manual_invite').setStyle('border', '1px solid #CCCCCC');
    
    set_status('');
}

function get_contacts() {
    if (!validate()) {
        return;
    }
    
    username = $('username').value;
    service = $('oi_service').value;
    
    var params = 'id=' + id + '&action=get_contacts';
    params = params + '&oi_service=' + $('oi_service').value;
    params = params + '&username=' + $('username').value;
    params = params + '&password=' + $('password').value;
    
    var uri = root + "/members/invites_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            contacts_list.clear();
            
            if (txt == '-1') {
                set_status('An error occured while retrieving your contacts.');
                return false;
            } else if (txt == '-2') {
                set_status('The login username and password are incorrect.');
                return false;
            } else if (txt == '-3') {
                set_status('Unable to retrieve your contacts. Please try again.');
                return false;
            }
            
            $('password').value = '';
            
            var oi_session_id = xml.getElementsByTagName('sessionid');
            var emails = xml.getElementsByTagName('email');
            var names = xml.getElementsByTagName('name');
            var plugin_type = xml.getElementsByTagName('plugin_type');
            var indices = xml.getElementsByTagName('index');
            
            if (emails.length <= 0) {
                alert('You do not have contacts in this service.');
                return false;
            }
            
            for (var i=0; i < indices.length; i++) {
                contacts_list.add_item(names[i].childNodes[0].nodeValue, indices[i].childNodes[0].nodeValue);
            }
            
            $('oi_session_id').value = oi_session_id[0].childNodes[0].nodeValue;
            $('oi_service_name').set('html', $('oi_service').options[$('oi_service').selectedIndex].text.substr(3));
            $('get_contacts_form').setStyle('display', 'none');
            $('send_invite_form').setStyle('display', 'block');
            set_status('');
            contacts_list.show();
        },
        onRequest: function(instance) {
            set_status('Retrieving your contacts...');
        }
    });
    
    request.send(params);
}

function send_invite_smart() {
    var contacts = contacts_list.get_selected_values();
    var number_of_contacts = contacts.length;
    
    if (number_of_contacts <= 0) {
        set_status('You need to at least select one of your contacts from the list.');
        return false;
    }
    
    var selected_contacts = '';
    for (var i=0; i < number_of_contacts; i++) {
        var contact_details = contacts[i].split('|');
        selected_contacts = selected_contacts + contact_details[1];
        
        if (i < number_of_contacts-1) {
            selected_contacts = selected_contacts + '|';
        }
    }
    
    var params = 'id=' + id + '&action=smart_send_invites';
    params = params + '&oi_service=' + service;
    params = params + '&oi_session_id=' + $('oi_session_id').value;
    params = params + '&username=' + username;
    params = params + '&selected_contacts=' + selected_contacts;
    params = params + '&message=' + $('smart_message').value;
    
    var uri = root + "/members/invites_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while sending out the invitation e-mails.');
                return false;
            }
            
            set_status('Invitation e-mails were successfully send!');
            
            username = '';
            service = '';
            $('oi_session_id').value = '';
            $('oi_service_name').set('html', '');
            $('get_contacts_form').setStyle('display', 'block');
            $('send_invite_form').setStyle('display', 'none');
        },
        onRequest: function(instance) {
            set_status('Sending invitation e-mails...');
        }
    });
    
    request.send(params);
}

function send_invite_manual() {
    if (isEmpty($('email_addresses').value) || isEmpty($('message').value)) {
        set_status('You need to enter at least an e-mail address and a short message.');
        return false;
    }
    
    var temp = $('email_addresses').value;
    temp = temp.replace(/\n/g, ' ');
    var emails = temp.split(' ');
    var email_addresses = '';
    for (var i=0; i < emails.length; i++) {
        if (!isEmail(emails[i])) {
            if (!isEmpty(emails[i])) {
                set_status('One of your e-mail addresses is invalid- <strong>' + emails[i] + '</strong>');
                return false;
            }
        }
        
        if (!isEmpty(emails[i]) && !duplicated(email_addresses, emails[i])) {
            email_addresses = email_addresses + emails[i];

            if (i < (emails.length - 1)) {
                email_addresses = email_addresses + ',';
            }
        }
    }
    var params = 'id=' + id + '&message=' + $('message').value + '&email_addresses=' + email_addresses;
    
    var uri = root + "/members/invites_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while sending out the invitation e-mails.');
                return false;
            }
            
            set_status('Invitation e-mails were successfully send!');
        },
        onRequest: function(instance) {
            set_status('Sending invitation e-mails...');
        }
    });
    
    request.send(params);
}

function set_mouse_events() {
    $('li_smart_invite').addEvent('mouseover', function() {
        $('li_smart_invite').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_smart_invite').addEvent('mouseout', function() {
        $('li_smart_invite').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_manual_invite').addEvent('mouseover', function() {
        $('li_manual_invite').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_manual_invite').addEvent('mouseout', function() {
        $('li_manual_invite').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
}

function onDomReady() {
    set_root();
    get_employers_for_mini();
    get_industries_for_mini();
    set_mini_keywords();
    get_referrals_count();
    get_requests_count();
    get_jobs_employed_count();
    set_mouse_events();
    
    $('li_smart_invite').addEvent('click', show_smart_invite);
    $('li_manual_invite').addEvent('click', show_manual_invite);
    
    $('send').addEvent('click', send_invite_manual);
    $('send_smart').addEvent('click', send_invite_smart);
    $('get_contacts').addEvent('click', get_contacts);
    
    var suggest_url = root + '/common/php/search_suggest.php';
    new Autocompleter.Ajax.Json('mini_keywords', suggest_url, {
        'postVar': 'keywords',
        'minLength' : 1,
        'overflow' : true,
        'delay' : 50
    });
}

window.addEvent('domready', onDomReady);
