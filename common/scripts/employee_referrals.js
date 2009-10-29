var order_by = 'referred_on';
var order = 'desc';

var header_at_every_rows = 10;

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function show_acknowledged_referrals() {
    var params = 'id=0&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employees/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading referrals.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no acknowledged referrals at the moment.</div>';
            } else {
                var ids = xml.getElementsByTagName('id');
                var employers = xml.getElementsByTagName('employer');
                var employer_ids = xml.getElementsByTagName('employer_id');
                var jobs = xml.getElementsByTagName('title');
                var job_ids = xml.getElementsByTagName('job_id');
                var referrers = xml.getElementsByTagName('referrer');
                var candidates = xml.getElementsByTagName('candidate');
                var candidate_emails = xml.getElementsByTagName('candidate_email');
                var referred_ons = xml.getElementsByTagName('formatted_referred_on');
                var acknowledged_ons = xml.getElementsByTagName('formatted_acknowledged_on');
                var member_confirmed_ons = xml.getElementsByTagName('formatted_member_confirmed_on');
                var agreed_terms_ons = xml.getElementsByTagName('formatted_agreed_terms_on');
                var employed_ons = xml.getElementsByTagName('formatted_employed_on');
                var commence_ons = xml.getElementsByTagName('formatted_commence_on');
                var confirmed_ons = xml.getElementsByTagName('formatted_confirmed_on');
                var coe_received_ons = xml.getElementsByTagName('formatted_coe_received_on');
                
                for (var i=0; i < ids.length; i++) {
                    var referral = ids[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ referral + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="employer"><a class="no_link" onClick="show_contact(\'' + employer_ids[i].childNodes[0].nodeValue + '\', false);">' + employers[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="title"><a href="' + root + '/job/' + job_ids[i].childNodes[0].nodeValue + '">' + jobs[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="referrer">' + referrers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="candidate"><a class="no_link" onClick="show_contact(\'' + add_slashes(candidate_emails[i].childNodes[0].nodeValue) + '\', true);">' + candidates[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="date">' + referred_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + acknowledged_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    var member_confirmed_on = '';
                    if (member_confirmed_ons[i].childNodes.length > 0) {
                        member_confirmed_on = member_confirmed_ons[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="date">' + member_confirmed_on + '</td>' + "\n";
                    
                    var agreed_terms_on = '';
                    if (agreed_terms_ons[i].childNodes.length > 0) {
                        agreed_terms_on = agreed_terms_ons[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="date">' + agreed_terms_on + '</td>' + "\n";
                    
                    var employed_on = '';
                    if (employed_ons[i].childNodes.length > 0) {
                        employed_on = employed_ons[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="date">' + employed_on + '</td>' + "\n";
                    
                    var commence_on = '';
                    if (commence_ons[i].childNodes.length > 0) {
                        commence_on = commence_ons[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="date">' + commence_on + '</td>' + "\n";
                    
                    var confirmed_on = '';
                    if (confirmed_ons[i].childNodes.length > 0) {
                        confirmed_on = confirmed_ons[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="date">' + confirmed_on + '</td>' + "\n";
                    
                    if (!isEmpty(employed_on)) {
                        if (coe_received_ons[i].childNodes.length > 0) {
                            html = html + '<td class="date">' + coe_received_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                        } else {
                            html = html + '<td class="coe_received"><input type="button" value="Received" onClick="confirm_coe_reception(\'' + referral + '\');" /></td>' + "\n";
                        }
                    } else {
                        html = html + '<td class="coe_received"><input type="button" value="Received" disabled /></td>' + "\n";
                    }
                    
                    html = html + '</tr>' + "\n";
                    
                    if ((((parseInt(i)+1) % header_at_every_rows) == 0) && i > 0) {
                        html = html + '<tr>' + "\n";
                        html = html + '<td class="header_employer">Employer</td>' + "\n";
                        html = html + '<td class="header_title">Job</td>' + "\n";
                        html = html + '<td class="header_referrer">Referrer</td>' + "\n";
                        html = html + '<td class="header_candidate">Candidate</td>' + "\n";
                        html = html + '<td class="header_date">Referred On</td>' + "\n";
                        html = html + '<td class="header_date">Candidate Responded On</td>' + "\n";
                        html = html + '<td class="header_date">Referrer Submitted On</td>' + "\n";
                        html = html + '<td class="header_date">Employer Viewed Resume On</td>' + "\n";
                        html = html + '<td class="header_date">Employed On</td>' + "\n";
                        html = html + '<td class="header_date">Work Commence On</td>' + "\n";
                        html = html + '<td class="header_date">Candidate Confirmed Employment</td>' + "\n";
                        html = html + '<td class="header_date">Offer Letter Received</td>' + "\n";
                        html = html + '</tr>' + "\n";
                    }
                }
            }
            html = html + '</table>';
            
            $('div_acknowledged_referrals_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading referrals...');
        }
    });
    
    request.send(params);
}

function close_contact() {
    $('div_contact').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_contact(_id, _candidate) {
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
    
    if (_id != '0') {
        var params = 'id=' + _id;
        if (_candidate) {
            params = params + '&action=get_candidate_contact';
        } else {
            params = params + '&action=get_employer_contact';
        }
        
        var uri = root + "/employees/referrals_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    alert('An error occured while loading contacts.');
                    return false;
                }
                set_status(txt);
                if (txt != '0') {
                    if (_candidate) {
                        var names = xml.getElementsByTagName('name');
                        var phones = xml.getElementsByTagName('candidate_phone');
                        var emails = xml.getElementsByTagName('candidate_email');
                        
                        $('name').set('html', names[0].childNodes[0].nodeValue);
                        $('telephone').set('html', phones[0].childNodes[0].nodeValue);
                        $('email_addr').set('html',emails[0].childNodes[0].nodeValue);
                    } else {
                        var names = xml.getElementsByTagName('name');
                        var phones = xml.getElementsByTagName('employer_phone');
                        var contact_persons = xml.getElementsByTagName('contact_person');
                        var emails = xml.getElementsByTagName('employer_email');
                        
                        $('name').set('html', names[0].childNodes[0].nodeValue + '<br/>(' + contact_persons[0].childNodes[0].nodeValue + ')');
                        $('telephone').set('html', phones[0].childNodes[0].nodeValue);
                        $('email_addr').set('html',emails[0].childNodes[0].nodeValue);
                    }
                }

                set_status('');
            },
            onRequest: function(instance) {
                set_status('Loading contacts...');
            }
        });

        request.send(params);
    }
    
    $('div_blanket').setStyle('display', 'block');
    $('div_contact').setStyle('display', 'block');
}

function confirm_coe_reception(_referral_id) {
    
    var params = 'id=' + _referral_id;
    params = params + '&action=confirm_coe_reception';
    
    var uri = root + "/employees/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while confirming Offer Letter reception.');
                return false;
            }
            
            set_status('');
            show_acknowledged_referrals();
        },
        onRequest: function(instance) {
            set_status('Confirming Offer Letter reception...');
        }
    });
    
    request.send(params);
}

function onDomReady() {
    set_root();
    get_unapproved_photos_count();
    get_employee_rewards_count();
    get_employee_tokens_count();
    
    $('sort_employer').addEvent('click', function() {
        order_by = 'employer';
        ascending_or_descending();
        show_acknowledged_referrals();
    });
    
    $('sort_title').addEvent('click', function() {
        order_by = 'title';
        ascending_or_descending();
        show_acknowledged_referrals();
    });
    
    $('sort_referrer').addEvent('click', function() {
        order_by = 'referrer';
        ascending_or_descending();
        show_acknowledged_referrals();
    });
    
    $('sort_candidate').addEvent('click', function() {
        order_by = 'candidate';
        ascending_or_descending();
        show_acknowledged_referrals();
    });
    
    $('sort_referred_on').addEvent('click', function() {
        order_by = 'referred_on';
        ascending_or_descending();
        show_acknowledged_referrals();
    });
    
    $('sort_acknowledged_on').addEvent('click', function() {
        order_by = 'referee_acknowledged_on';
        ascending_or_descending();
        show_acknowledged_referrals();
    });
    
    $('sort_member_confirmed_on').addEvent('click', function() {
        order_by = 'member_confirmed_on';
        ascending_or_descending();
        show_acknowledged_referrals();
    });
    
    $('sort_agreed_terms_on').addEvent('click', function() {
        order_by = 'employer_agreed_terms_on';
        ascending_or_descending();
        show_acknowledged_referrals();
    });
    
    $('sort_employed_on').addEvent('click', function() {
        order_by = 'employed_on';
        ascending_or_descending();
        show_acknowledged_referrals();
    });
    
    $('sort_commence_on').addEvent('click', function() {
        order_by = 'work_commence_on';
        ascending_or_descending();
        show_acknowledged_referrals();
    });
    
    $('sort_confirmed_on').addEvent('click', function() {
        order_by = 'referee_confirmed_hired_on';
        ascending_or_descending();
        show_acknowledged_referrals();
    });
    
    $('sort_coe_received_on').addEvent('click', function() {
        order_by = 'employment_contract_received_on';
        ascending_or_descending();
        show_acknowledged_referrals();
    });
    
    show_acknowledged_referrals();
}

window.addEvent('domready', onDomReady);
