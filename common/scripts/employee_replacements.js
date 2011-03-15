var order_by = 'employed_on';
var order = 'asc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function show_invoice_page(_invoice_id) {
    var popup = window.open('invoice.php?id=' + _invoice_id, '', 'scrollbars');
    
    if (!popup) {
        alert('Popup blocker was detected. Please allow pop-up windows for YellowElevator.com and try again.');
    }
}

function authorize_replacement(_referral_id, _invoice) {
    if (_referral_id == '0' || isEmpty(_referral_id)) {
        alert('This payment is corrupted');
        return false;
    }
    
    var confirmed = confirm('Are you sure to authorize a replacement for invoice ' + _invoice + ' ?');
    if (!confirmed) {
        return false;
    }
    
    var params = 'id=' + _referral_id + '&action=authorize_replacement';
    
    var uri = root + "/employees/replacements_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while authorizing replacement.');
                return false;
            }
            
            set_status('');
            show_replacements();
        },
        onRequest: function(instance) {
            set_status('Authorizing replacement...');
        }
    });
     
    request.send(params);
}

function show_replacements() {
    var params = 'id=0&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employees/replacements_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading replacement eligible referrals.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no referrals eligible for replacement at the moment.</div>';
            } else {
                var referrals = xml.getElementsByTagName('referral');
                var invoices = xml.getElementsByTagName('invoice');
                var padded_invoices = xml.getElementsByTagName('padded_invoice');
                var employers = xml.getElementsByTagName('employer');
                var employer_emails = xml.getElementsByTagName('employer_email');
                var employer_phones = xml.getElementsByTagName('phone_num');
                var contact_persons = xml.getElementsByTagName('contact_person');
                var jobs = xml.getElementsByTagName('title');
                var members = xml.getElementsByTagName('member');
                var referees = xml.getElementsByTagName('referee');
                var employed_ons = xml.getElementsByTagName('formatted_employed_on');
                
                for (var i=0; i < referrals.length; i++) {
                    var invoice = invoices[i].childNodes[0].nodeValue;
                    var referral = referrals[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ referral + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="invoice"><a class="no_link" onClick="show_invoice_page(\'' + invoice + '\')">' + padded_invoices[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="employer"><a class="no_link" onClick="show_contact(\'' + add_slashes(employers[i].childNodes[0].nodeValue) + '\', \'' + add_slashes(contact_persons[i].childNodes[0].nodeValue) + '\', \'' + add_slashes(employer_emails[i].childNodes[0].nodeValue) + '\', \'' + employer_phones[i].childNodes[0].nodeValue + '\');">' + employers[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="title">' + jobs[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="member">' + members[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="member">' + referees[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + employed_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="action"><input type="button" onClick="authorize_replacement(\'' + referral + '\', \'' + padded_invoices[i].childNodes[0].nodeValue + '\');" value="Authorize Replacement" /></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_replacements_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading replacement eligible referrals...');
        }
    });
    
    request.send(params);
}

function close_contact() {
    $('div_contact').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_contact(_name, _contact_person, _email, _phone) {
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_contact').getStyle('height'));
    var div_width = parseInt($('div_contact').getStyle('width'));
    
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
    
    $('div_contact').setStyle('top', ((window_height - div_height) / 2));
    $('div_contact').setStyle('left', ((window_width - div_width) / 2));
    
    $('name').set('html', _name + '<br/>(' + _contact_person + ')');
    $('telephone').set('html', _phone);
    $('email_addr').set('html', _email);
    
    $('div_blanket').setStyle('display', 'block');
    $('div_contact').setStyle('display', 'block');
}

function onDomReady() {
    initialize_page();
    get_unapproved_photos_count();
    get_employee_rewards_count();
    get_employee_tokens_count();
    
    show_replacements();
}

window.addEvent('domready', onDomReady);
