var order_by = 'jobs.expire_on';
var order = 'desc';
var filter_by = '0';
var candidates_list = new ListBox('candidates', 'candidates_list', true);
var referrers_list = new ListBox('referrers', 'referrers_list', true);
var selected_job_id = '';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function get_job_title(_job_id) {
    var params = 'id=' + _job_id + '&action=get_job_title';
    var uri = root + "/members/saved_jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (isEmpty(txt)) {
                $('job_title').set('html', 'Unknown Job');
            } else {
                $('job_title').set('html', txt);
            }
        }
    });
    
    request.send(params);
}

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

function show_candidates() {
    $('candidates').set('html', '');
    
    var params = 'id=' + id + '&action=get_candidates';
    params = params + '&filter_by=' + filter_by;
    
    var uri = root + "/search_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            
            if (txt == 'ko') {
                alert('An error occured while loading candidates.');
                return false;
            }
            
            candidates_list.clear();
            
            var ids = xml.getElementsByTagName('id');
            var referee_names = xml.getElementsByTagName('referee_name');
            var referee_emails = xml.getElementsByTagName('referee');
            
            for (var i=0; i < ids.length; i++) {
                candidates_list.add_item(referee_names[i].childNodes[0].nodeValue, referee_emails[i].childNodes[0].nodeValue);
            }
            
            candidates_list.show();
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading referees...');
        }
    });
    
    request.send(params);
}

function show_referrers() {
    $('candidates').set('html', '');
    
    var params = 'id=' + id + '&action=get_candidates';
    params = params + '&filter_by=' + filter_by;
    
    var uri = root + "/search_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while loading referrers.');
                return false;
            }
            
            referrers_list.clear();
            
            var ids = xml.getElementsByTagName('id');
            var contact_names = xml.getElementsByTagName('referee_name');
            var contact_emails = xml.getElementsByTagName('referee');
            
            for (var i=0; i < ids.length; i++) {
                referrers_list.add_item(contact_names[i].childNodes[0].nodeValue, contact_emails[i].childNodes[0].nodeValue);
            }
            
            referrers_list.show();
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading referrers...');
        }
    });
    
    request.send(params);
}

function toggle_description(job_id) {
    if ($('description_' + job_id).getStyle('display') == 'none') {
        $('description_' + job_id).setStyle('display', 'block');
    } else {
        $('description_' + job_id).setStyle('display', 'none');
    }
}

function remove_jobs() {
    var inputs = $('list').getElementsByTagName('input');
    var payload = '<jobs>' + "\n";
    var count = 0;
    
    for(i=0; i < inputs.length; i++) {
        var attributes = inputs[i].attributes;
        if (attributes.getNamedItem('type').value == 'checkbox') {
            if (inputs[i].checked) {
                payload = payload + '<id>' + inputs[i].id + '</id>' + "\n";
                count++;
            }
        }
    }
    
    payload = payload + '</jobs>';
    
    if (count <= 0) {
        set_status('Please select at least one job.');
        return false;
    }
    
    var proceed = confirm('Are you sure to remove the selected jobs?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=' + id;
    params = params + '&action=remove_from_saved_jobs';
    params = params + '&payload=' + payload;

    var uri = root + "/members/saved_jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while closing selected jobs.');
                return false;
            }
            
            for (i=0; i < inputs.length; i++) {
                var attributes = inputs[i].attributes;
                if (attributes.getNamedItem('type').value == 'checkbox') {
                    if (inputs[i].checked) {
                        $(inputs[i].id).setStyle('display', 'none');
                        $('desc_' + inputs[i].id).setStyle('display', 'none');
                    }
                }
            }
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading currently saved jobs...');
        }
    });
    
    request.send(params);
}

function show_saved_jobs() {
    var params = 'id=' + id + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/members/saved_jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading saved jobs.');
                return false;
            }
            
            var has_saved_jobs = false;
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">You have no saved jobs at the moment. You may search jobs from the <a href="' + root + '/index.php">main</a> page, or from the search field on top, and save them here to be reivewed later.</div>';
            } else {
                var ids = xml.getElementsByTagName('id');
                var industries = xml.getElementsByTagName('industry');
                var employers = xml.getElementsByTagName('employer');
                var titles = xml.getElementsByTagName('title');
                //var descriptions = xml.getElementsByTagName('description');
                //var created_ons = xml.getElementsByTagName('formatted_created_on');
                var expire_ons = xml.getElementsByTagName('formatted_expire_on');
                var saved_ons = xml.getElementsByTagName('formatted_saved_on');
                var currencies = xml.getElementsByTagName('currency');
                var potential_rewards = xml.getElementsByTagName('potential_reward');
                
                for (i=0; i < ids.length; i++) {
                    var job_id = ids[i];
                
                    html = html + '<tr id="'+ job_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="checkbox"><input type="checkbox" id="'+ job_id.childNodes[0].nodeValue + '" /></td>' + "\n";
                    html = html + '<td class="industry">' + industries[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title"><a href="' + root + '/job/' + job_id.childNodes[0].nodeValue + '">' + titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="date">' + saved_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + expire_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="potential_reward">' + currencies[i].childNodes[0].nodeValue + '$&nbsp;' + potential_rewards[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_list').set('html', html);            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading saved jobs...');
        }
    });
    
    request.send(params);
}

function select_all_jobs() {
    var inputs = $('list').getElementsByTagName('input');
    
    if ($('close_all').checked) {
        for (i=0; i < inputs.length; i++) {
            var attributes = inputs[i].attributes;
            if (attributes.getNamedItem('type').value == 'checkbox') {
                inputs[i].checked = true;
            }
        }
    } else {
        for (i=0; i < inputs.length; i++) {
            var attributes = inputs[i].attributes;
            if (attributes.getNamedItem('type').value == 'checkbox') {
                inputs[i].checked = false;
            }
        }
    }
}

function close_refer_form() {
    $('div_refer_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_refer_job(_job_id) {
    $('div_blanket').setStyle('display', 'block');
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_refer_form').getStyle('height'));
    var div_width = parseInt($('div_refer_form').getStyle('width'));
    
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
    
    $('div_refer_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_refer_form').setStyle('left', ((window_width - div_width) / 2));
    
    get_job_title(_job_id);
    selected_job_id = _job_id;
    
    $('div_refer_form').setStyle('display', 'block');
    show_candidates();
}

function set_filter(_is_from_request_form) {
    if (!_is_from_request_form) {
        filter_by = $('network_filter').options[$('network_filter').selectedIndex].value;
        show_candidates();
    } else {
        filter_by = $('network_filter_request').options[$('network_filter_request').selectedIndex].value;
        show_referrers();
    }
    
}

function check_has_banks(_member) {
    var params = 'id=' + _member + '&action=has_banks';
    var uri = root + "/search_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == '0') {
                alert('Our system indicates that you have not provided us your bank account details. \n\nIf you like us to transfer your rewards directly into your bank account, please go to the "Bank Accounts" page to submit your bank account details. \n\nHowever, if you wish to receive your rewards by cheque instead, please ensure that your full name and mailing address in the "Profile" page is valid.');
            } 
        },
        onRequest: function(instance) {
            set_status('Checking reward matters...');
        }
    });
    
    request.send(params);
}

function check_referred_already() {
    if (!isEmpty(candidates_list.selected_value)) {
        var params = 'job=' + selected_job_id + 
                      '&id=' + id + 
                      '&candidate=' + candidates_list.selected_value + 
                      '&action=referred_already';
        
        var uri = root + "/search_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == '1') {
                    alert("You have already referred " + candidates_list.selected_item + " to this job. \n\nPlease unselect " + candidates_list.selected_item + " from the Contacts list.");
                } 
                
                set_status('');
            },
            onRequest: function(instance) {
                set_status('Checking referral existence...');
            }
        });

        request.send(params);
    }
}

function refer() {
    var referee = '';
    var from = 'list'; // list or email
    if ($('from_list').checked) {
        var referees = candidates_list.get_selected_values();
        var number_of_referees = referees.length;
        
        if (number_of_referees <= 0) {
            alert('Please select at least a candidate.');
            return false;
        }
        
        for (var i=0; i < number_of_referees; i++) {
            var referee_details = referees[i].split('|');
            referee = referee + referee_details[1];

            if (i < number_of_referees-1) {
                referee = referee + '|';
            }
        }
    } else {
        if (isEmpty($('email_addr').value)) {
            alert('Please provide at least a valid email address of the candidate.');
            return false;
        }
        
        var temp = $('email_addr').value;
        temp = temp.replace(/ /g, ',');
        temp = temp.replace(/;/g, ',');
        var emails = temp.split(',');
        for (var i=0; i < emails.length; i++) {
            if (!isEmail(emails[i])) {
                if (!isEmpty(emails[i])) {
                    alert('One of your e-mail addresses is invalid- <strong>' + emails[i] + '</strong>');
                    return false;
                }
            }

            if (!isEmpty(emails[i]) && !duplicated(referee, emails[i])) {
                if (isEmpty(referee)) {
                    referee = emails[i];
                } else {
                    referee = referee + '|' + emails[i];
                }
            }
        }
        
        from = 'email';
    }
    
    // var answer_1 = $('testimony_answer_1').value;
    // var answer_2 = $('testimony_answer_2').value;
    // var answer_3 = $('testimony_answer_3').value;
    
    // if (isEmpty(answer_1) || isEmpty(answer_2) || isEmpty(answer_3)) {
    //     alert('Please briefly answer all questions.');
    //     return false;
    // } else if (answer_1.split(' ').length > 50 || answer_3.split(' ').length > 50 || answer_3.split(' ').length > 50) {
    //     if (answer_1.split(' ').length > 50) {
    //         alert('Please keep your 1st answer below 50 words.');
    //     } else if (answer_2.split(' ').length > 50) {
    //         alert('Please keep your 2nd answer below 50 words.');
    //     } else if (answer_3.split(' ').length > 50) {
    //         alert('Please keep your 3rd and final answer below 50 words.');
    //     }
    //     return false;
    // }
    // 
    // var testimony = answer_1 + '<br/>' + answer_2 + '<br/>' + answer_3;
    
    check_has_banks(id);
    
    var proceed = confirm('Your referred candidates will be requested to submit their resumes. As a referrer, you are responsible for screening your candidates\' resumes to confirm that they are suitable for this job position before recommending them.\n\nYou will be notified by email to check the "Referral Requests" section once the resumes are submitted.\n\nClick "OK" to continue or "Cancel" to make changes.');
    
    if (!proceed) {
        set_status('');
        return false;
    }
    
    var params = 'id=' + id + '&action=make_referral';
    params = params + '&from=' + from;
    params = params + '&referee=' + referee;
    params = params + '&job=' + selected_job_id;
    // params = params + '&testimony=' + testimony;
    
    var uri = root + "/search_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while referring the candidate to the job. \n\nIt might because of this referral had been made before. Please try again.');
                close_refer_form();
                set_status('');
                return false;
            } else if (txt == '-900') {
                alert('An error occured while adding the potential candidate into your contacts list. Please try again.');
                close_refer_form();
                set_status('');
                return false;
            } else if (txt == '-901') {
                alert('An error occured while inviting the potential candidate to become a member. Please try again.');
                close_refer_form();
                set_status('');
                return false;
            } else if (txt == '-902') {
                alert('An error occured while reserving a member place for the potential candidate. Please try again.');
                close_refer_form();
                set_status('');
                return false;
            } else if (txt == '-903') {
                alert('Hmm... an error occured while adding the potential candidate into your contacts list after inviting and reserving a place. Please try again.');
                close_refer_form();
                set_status('');
                return false;
            } /*else if (txt == '-2') {
                alert('It appears that this candidate is not in your candidates list. The candidate will be notified before the referral can be made. \n\nYellow Elevator will automatically complete the referral process once the candidate approved the request of being added to your list.');
            } else if (txt == '-3') {
                alert('It appears that this candidate is not in a member of Yellow Elevator. The candidate will be notified before the referral can be made. \n\nYellowElevator.com will automatically complete the referral process once the candidate had signed up as a member. The candidate will be added into your contacts list automatically.');
            }*/
            
            close_refer_form();
            set_status('Your contact was successfully referred. A notification email has been sent to the referred contact. You may make another referrals.');
        },
        onRequest: function(instance) {
            set_status('Making referral...');
        }
    });
    
    request.send(params);
}

function close_refer_me() {
    $('div_acknowledge_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_refer_me(_job_id) {
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_acknowledge_form').getStyle('height'));
    var div_width = parseInt($('div_acknowledge_form').getStyle('width'));
    
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
    
    $('div_acknowledge_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_acknowledge_form').setStyle('left', ((window_width - div_width) / 2));
    
    selected_job_id = _job_id;
    
    var params = 'id=' + id;
    params = params + '&action=has_resumes';
    
    var uri = root + "/members/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            if (txt == '0') {
                alert('You have not created or uploaded your resume yet. In order to request for a referral, you need to either create or upload a resume at the "Resumes" section. \n\nIf you have already created one, please ensure that the \'Private\' check box is unchecked.');
                referral = 0;
                return false;
            }
            
            //$('ack.job_title').set('html', $('job.title').get('html'));
            $('div_blanket').setStyle('display', 'block');
            $('div_acknowledge_form').setStyle('display', 'block');
        },
        onRequest: function(instance) {
            set_status('Checking resumes...');
        }
    });
    
    request.send(params);
    show_referrers();
}

function refer_me() {
    if ($('resume').options[$('resume').selectedIndex].value == '0') {
        alert('Please choose a resume to proceed.');
        return false;
    }
    
    var referrer = '';
    var from = 'contacts';
    if ($('referrer_contacts').checked) {
        var referrers = referrers_list.get_selected_values();
        number_of_referrers = referrers.length;
        for (var i=0; i < number_of_referrers; i++) {
            var referrer_details = referrers[i].split('|');
            referrer = referrer + referrer_details[1];

            if (i < number_of_referrers-1) {
                referrer = referrer + '|';
            }
        }
    } else if ($('referrer_others').checked) {
        from = 'others';
        if (isEmpty($('referrer_emails').value)) {
            salert('You need to enter at least an e-mail address.');
            return false;
        }
        
        var temp = $('referrer_emails').value;
        temp = temp.replace(/\n/g, ' ');
        var emails = temp.split(' ');
        for (var i=0; i < emails.length; i++) {
            if (!isEmail(emails[i])) {
                if (!isEmpty(emails[i])) {
                    alert('One of your e-mail addresses is invalid- <strong>' + emails[i] + '</strong>');
                    return false;
                }
            }

            if (!isEmpty(emails[i]) && !duplicated(referrer, emails[i])) {
                if (isEmpty(referrer)) {
                    referrer = emails[i];
                } else {
                    referrer = referrer + '|' + emails[i];
                }
            }
        }
    } else {
        from = 'yel';
        referrer = 'initial@yellowelevator.com';
    }
    
    var params = 'id=' + id; 
    params = params + '&job=' + selected_job_id;
    params = params + '&resume=' + $('resume').options[$('resume').selectedIndex].value;
    params = params + '&referrer=' + referrer;
    params = params + '&from=' + from;
    params = params + '&action=refer_me';
    
    var uri = root + "/search_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('Some of your requests were not send due to duplications.');
                return false;
            }
            
            set_status('');
            if (txt == '-1') {
                alert('Sorry, this job only accepts resumes created online.');
                return false;
            } else if (txt == '-2') {
                alert('Sorry, this job only accepts uploaded file resumes.');
                return false;
            }
            
            set_status('Your resume has been received, and you will be referred shortly.');
            close_refer_me();
        },
        onRequest: function(instance) {
            set_status('Referring you to job...');
        }
    });
    
    request.send(params);
}

function toggle_banner() {
    var height = $('div_banner').getStyle('height');
    var params = 'id=' + id + '&action=set_hide_banner';
    
    if (parseInt(height) >= 100) {
        $('hide_show_label').set('html', 'Show');
        $('div_banner').tween('height', '15px');
        params = params + '&hide=1';
    } else {
        $('hide_show_label').set('html', 'Hide');
        $('div_banner').tween('height', '230px');
        params = params + '&hide=0';
    }
    
    var uri = root + "/members/saved_jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post'
    });
    
    request.send(params);
}

function hide_show_banner() {
    var params = 'id=' + id + '&action=get_hide_banner';
    
    var uri = root + "/members/saved_jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post', 
        onSuccess: function(txt, xml) {
            if (txt == '1') {
                $('hide_show_label').set('html', 'Show');
                $('div_banner').setStyle('height', '15px');
            } else {
                $('hide_show_label').set('html', 'Hide');
                $('div_banner').setStyle('height', '230px');
            }
        }
    });
    
    request.send(params);
}

function set_mouse_events() {
    /*$('li_open').addEvent('mouseover', function() {
        $('li_open').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_open').addEvent('mouseout', function() {
        $('li_open').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });*/
}

function onDomReady() {
    initialize_page();
    get_employers_for_mini();
    get_industries_for_mini();
    set_mini_keywords();
    get_referrals_count();
    get_requests_count();
    get_jobs_employed_count();
    set_mouse_events();
    
    hide_show_banner();
    
    $('remove_jobs').addEvent('click', remove_jobs);
    $('remove_jobs_1').addEvent('click', remove_jobs);
    $('close_all').addEvent('click', select_all_jobs);
    
    $('sort_industry').addEvent('click', function() {
        order_by = 'industries.industry';
        ascending_or_descending();
        show_saved_jobs();
    });
    
    $('sort_title').addEvent('click', function() {
        order_by = 'title';
        ascending_or_descending();
        show_saved_jobs();
    });
    
    $('sort_employer').addEvent('click', function() {
        order_by = 'employer';
        ascending_or_descending();
        show_saved_jobs();
    });
    
    /*$('sort_created_on').addEvent('click', function() {
        order_by = 'jobs.created_on';
        ascending_or_descending();
        show_saved_jobs();
    });*/
    
    $('sort_expire_on').addEvent('click', function() {
        order_by = 'jobs.expire_on';
        ascending_or_descending();
        show_saved_jobs();
    });
    
    $('sort_saved_on').addEvent('click', function() {
        order_by = 'member_saved_jobs.saved_on';
        ascending_or_descending();
        show_saved_jobs();
    });
    
    $('sort_potential_reward').addEvent('click', function() {
        order_by = 'jobs.potential_reward';
        ascending_or_descending();
        show_saved_jobs();
    });
    
    show_saved_jobs();
    
    var suggest_url = root + '/common/php/search_suggest.php';
    new Autocompleter.Ajax.Json('mini_keywords', suggest_url, {
        'postVar': 'keywords',
        'minLength' : 1,
        'overflow' : true,
        'delay' : 50
    });
}

window.addEvent('domready', onDomReady);
