var order_by = 'referral_requests.requested_on';
var order = 'desc';

var candidate_names = new Array();
var job_names = new Array();
var selected_candidate_name = '';
var selected_candidate_id = '';
var selected_job_title = '';
var selected_job_id = '';
var request_id = '';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
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
            
            show_requests();
        },
        onRequest: function(instance) {
            set_status('Checking reward matters...');
        }
    });
    
    request.send(params);
}

function close_testimony_form() {
    $('div_testimony_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_testimony_form(_request_id, _candidate_index, _candidate_id, 
                             _job_index, _job_id, _requested_on, _resume_id) {
    selected_candidate_name = candidate_names[_candidate_index];
    selected_candidate_id = _candidate_id;
    selected_job_title = job_names[_job_index];
    selected_job_id = _job_id;
    request_id = _request_id;
    
    $('div_blanket').setStyle('display', 'block');
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_testimony_form').getStyle('height'));
    var div_width = parseInt($('div_testimony_form').getStyle('width'));
    
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
    
    $('div_testimony_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_testimony_form').setStyle('left', ((window_width - div_width) / 2));
    
    var testimony_form = $('div_testimony_form');
    var spans = testimony_form.getElementsByTagName('span');
    
    for (var i=0; i < spans.length; i++) {
        if (spans[i].id == 'candidate_name') {
            spans[i].innerHTML = selected_candidate_name;
        } 
    }
    
    $('job_title').set('html', selected_job_title);
    $('requested_on').value = _requested_on;
    $('resume').value = _resume_id;
    
    $('div_testimony_form').setStyle('display', 'block');
}

function show_requests() {
    var params = 'id=' + id;
    params = params + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/members/referral_requests_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading requests.');
                return false;
            }
            
            var has_requests = false;
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no requests from your contacts.</div>';
            } else {
                candidate_names = new Array();
                job_names = new Array();
                
                var ids = xml.getElementsByTagName('id');
                var job_ids = xml.getElementsByTagName('job_id');
                var candidate_ids = xml.getElementsByTagName('candidate_id');
                var employers = xml.getElementsByTagName('employer');
                var titles = xml.getElementsByTagName('title');
                var candidates = xml.getElementsByTagName('candidate');
                var requested_ons = xml.getElementsByTagName('formatted_requested_on');
                var requested_timestamps = xml.getElementsByTagName('requested_on');
                var rewards = xml.getElementsByTagName('potential_reward');
                var currencies = xml.getElementsByTagName('currency');
                var resumes = xml.getElementsByTagName('resume');

                for (var i=0; i < ids.length; i++) {
                    var request_id = ids[i].childNodes[0].nodeValue;
                    candidate_names[i] = candidates[i].childNodes[0].nodeValue;
                    job_names[i] = titles[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ request_id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";

                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title"><a class="no_link" onClick="toggle_description(\'' + request_id + '\')">' + titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="title">' + candidates[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + requested_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="reward">' + currencies[i].childNodes[0].nodeValue + '&nbsp;' + rewards[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="actions"><a href="resume_viewer.php?id=' + resumes[i].childNodes[0].nodeValue + '" target="_new">View Resume</a>&nbsp;|&nbsp;<a class="no_link" onClick="show_testimony_form(' + request_id + ', ' + i + ', \'' + candidate_ids[0].childNodes[0].nodeValue + '\', ' + i + ', ' + job_ids[i].childNodes[0].nodeValue + ', \'' + requested_timestamps[i].childNodes[0].nodeValue + '\', ' + resumes[i].childNodes[0].nodeValue + ');">Refer</a>&nbsp;|&nbsp;<a class="no_link" onClick="reject_request(' + request_id + ');">Ignore</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                    html = html + '<tr onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td colspan="7"><div class="description" id="desc_' + request_id + '"></div></td>' + "\n";
                    html = html + '</tr>';
                }
                html = html + '</table>';
                
                has_requests = true;
            }
            
            $('div_list').set('html', html);
            
            if (has_requests) {
                var ids = xml.getElementsByTagName('id');
                var descriptions = xml.getElementsByTagName('description');
                
                for (var i=0; i < ids.length; i++) {
                    var request_id = ids[i].childNodes[0].nodeValue;
                    
                    $('desc_' + request_id).set('html', descriptions[i].childNodes[0].nodeValue);
                }
            }
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading requests...');
        }
    });
    
    request.send(params);
}

function toggle_description(job_id) {
    if ($('desc_' + job_id).getStyle('display') == 'none') {
        $('desc_' + job_id).setStyle('display', 'block');
    } else {
        $('desc_' + job_id).setStyle('display', 'none');
    }
}

function close_request() {
    var params = 'id=' + request_id + '&action=close_request';
    var uri = root + "/members/referral_requests_action.php";
    var request = new Request({
        url: uri,
        method: 'post'
    });
    
    request.send(params);
}

function reject_request() {
    var params = 'id=' + request_id + '&action=reject_request';
    var uri = root + "/members/referral_requests_action.php";
    var request = new Request({
        url: uri,
        method: 'post'
    });
    
    request.send(params);
}

function refer() {
    var answer_1 = $('testimony_answer_1').value;
    var answer_2 = $('testimony_answer_2').value;
    var answer_3 = $('testimony_answer_3').value;
    
    if (isEmpty(answer_1) || isEmpty(answer_2) || isEmpty(answer_3)) {
        alert('Please briefly answer all questions.');
        return false;
    } else if (answer_1.split(' ').length > 50 || answer_3.split(' ').length > 50 || answer_3.split(' ').length > 50) {
        if (answer_1.split(' ').length > 50) {
            alert('Please keep your 1st answer below 50 words.');
        } else if (answer_2.split(' ').length > 50) {
            alert('Please keep your 2nd answer below 50 words.');
        } else if (answer_3.split(' ').length > 50) {
            alert('Please keep your 3rd and final answer below 50 words.');
        }
        return false;
    }
    
    var testimony = answer_1 + '<br/>' + answer_2 + '<br/>' + answer_3;
    
    var params = 'id=' + id + '&action=make_referral';
    params = params + '&referee=' + selected_candidate_id;
    params = params + '&job=' + selected_job_id;
    params = params + '&testimony=' + testimony;
    params = params + '&from=list';
    params = params + '&request=1';
    params = params + '&resume=' + $('resume').value;
    params = params + '&requested_on=' + $('requested_on').value;
    
    var uri = root + "/members/refer_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('You have already referred this contact to the job. Please refer another contact.');
                close_testimony_form();
                set_status('');
                return false;
            } else if (txt == '-900') {
                alert('An error occured while adding the potential candidate into your contacts list. Please try again.');
                close_testimony_form();
                set_status('');
                return false;
            } else if (txt == '-901') {
                alert('An error occured while inviting the potential candidate to become a member. Please try again.');
                close_testimony_form();
                set_status('');
                return false;
            } else if (txt == '-902') {
                alert('An error occured while reserving a member place for the potential candidate. Please try again.');
                close_testimony_form();
                set_status('');
                return false;
            } else if (txt == '-903') {
                alert('Hmm... an error occured while adding the potential candidate into your contacts list after inviting and reserving a place. Please try again.');
                close_testimony_form();
                set_status('');
                return false;
            } else if (txt == '-2') {
                alert('It appears that this contact is not in your contacts list. The contact will be notified before the referral can be made. \n\nYellow Elevator will automatically complete the referral process once the contact approved the request of being added to your list.');
            } else if (txt == '-3') {
                alert('The contact that you have just referred is not a member of Yellow Elevator yet. \n\nA notification email has been sent to this contact to notify the contact to sign up as a member of Yellow Elevtor in order to accept your referral.');
            }
            
            close_testimony_form();
            close_request();
            show_requests();
            get_referrals_count();
            check_has_banks(id);
        },
        onRequest: function(instance) {
            set_status('Making referral...');
        }
    });
    
    request.send(params);
}

function onDomReady() {
    set_root();
    get_employers_for_mini();
    get_industries_for_mini();
    get_referrals_count();
    get_requests_count();
    get_jobs_employed_count();
    set_mini_keywords();
    
    $('testimony_answer_1').addEvent('keypress', function() {
       update_word_count_of('word_count_q1', 'testimony_answer_1') 
    });

    $('testimony_answer_2').addEvent('keypress', function() {
       update_word_count_of('word_count_q2', 'testimony_answer_2') 
    });
    
    $('testimony_answer_3').addEvent('keypress', function() {
       update_word_count_of('word_count_q3', 'testimony_answer_3') 
    });
    
    $('sort_employer').addEvent('click', function() {
        order_by = 'employer';
        ascending_or_descending();
        show_requests();
    });
    
    $('sort_title').addEvent('click', function() {
        order_by = 'jobs.title';
        ascending_or_descending();
        show_requests();
    });
     
    $('sort_candidate').addEvent('click', function() {
        order_by = 'referrals.referee';
        ascending_or_descending();
        show_requests();
    });
    
    $('sort_requested_on').addEvent('click', function() {
        order_by = 'referrals.referred_on';
        ascending_or_descending();
        show_requests();
    });
    
    $('sort_reward').addEvent('click', function() {
        order_by = 'jobs.potential_reward';
        ascending_or_descending();
        show_requests();
    });
    
    
    show_requests();
    
    var suggest_url = root + '/common/php/search_suggest.php';
    new Autocompleter.Ajax.Json('mini_keywords', suggest_url, {
        'postVar': 'keywords',
        'minLength' : 1,
        'overflow' : true,
        'delay' : 50
    });
}

window.addEvent('domready', onDomReady);
