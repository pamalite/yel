var filter_by = '0';
var job_filter_by = '0';
var candidates_list = new ListBox('candidates', 'candidates_list');
var saved_jobs_list = new ListBox('saved_jobs', 'saved_jobs_list', true);

var has_saved_jobs = false;
var has_contacts = false;

function close_testimony_form() {
    $('div_testimony_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_testimony_form() {
    if ((isEmpty(candidates_list.selected_index) && isEmpty($('email_addr').value)) || 
        saved_jobs_list.get_selected_values().length <= 0) {
        set_status('Please select a candidate, or enter the e-mail address of a new candidate, and a job to make the referral.');
        return false;
    }
    
    if (!isEmpty($('email_addr').value)) {
        if (!isEmail($('email_addr').value)) {
            set_status('Please provide a valid e-mail address.');
            return false;
        }
    }
    
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
    
    if (!isEmpty(candidates_list.selected_index)) {
        for (var i=0; i < spans.length; i++) {
            if (spans[i].id == 'candidate_name') {
                spans[i].innerHTML = candidates_list.selected_item;
            } 
        }
    } else {
        for (var i=0; i < spans.length; i++) {
            if (spans[i].id == 'candidate_name') {
                spans[i].innerHTML = $('email_addr').value;
            } 
        }
    }
    
    var jobs = saved_jobs_list.get_selected_values();
    var job_titles = '';
    for (var i=0; i < jobs.length; i++) {
        var job_details = jobs[i].split('|');
        job_titles = job_titles + job_details[0];
        
        if (i < jobs.length-1) {
            job_titles = job_titles + '; ';
        }
    }
    //$('job_title').set('html', saved_jobs_list.selected_item);
    $('job_title').set('html', job_titles);
    
    $('div_testimony_form').setStyle('display', 'block');
}

function show_candidates() {
    $('candidates').set('html', '');
    
    var params = 'id=' + id + '&action=get_candidates';
    params = params + '&filter_by=' + filter_by;
    
    var uri = root + "/members/refer_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading candidates.');
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
            
            if (ids.length <= 0) {
                var html = '<div style="text-align: center; padding-top: 50%;">Please add candidates in the <a href="candidates.php">Candidates</a> page.</div>';
                $('candidates').set('html', html);
                
                if (!has_contacts) {
                    $('network_filter').disabled = true;
                }
            } else {
                has_contacts = true;
            }
        },
        onRequest: function(instance) {
            set_status('Loading referees...');
        }
    });
    
    request.send(params);
}

function show_saved_jobs() {
    $('saved_jobs').set('html', '');
    
    var params = 'id=' + id + '&action=get_saved_jobs';
    params = params + '&job_filter_by=' + job_filter_by;
    
    var uri = root + "/members/refer_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading saved jobs.');
                return false;
            }
            
            saved_jobs_list.clear();
            
            var ids = xml.getElementsByTagName('id');
            var titles = xml.getElementsByTagName('title');
            
            for (var i=0; i < ids.length; i++) {
                saved_jobs_list.add_item(titles[i].childNodes[0].nodeValue, ids[i].childNodes[0].nodeValue);
            }
            
            saved_jobs_list.show();
            set_status('');
            
            if (ids.length <= 0) {
                var html = '<div style="text-align: center; padding-top: 50%;">You may search jobs from the <a href="' + root + '/index.php">main</a> page, or from the search field on top, and save them.</div>';
                $('saved_jobs').set('html', html);
                
                if (!has_saved_jobs) {
                    $('job_filter').disabled = true;
                }
            } else {
                has_saved_jobs = true;
            }
        },
        onRequest: function(instance) {
            set_status('Loading saved jobs...');
        }
    });
    
    request.send(params);
}

function show_job_details() {
    $('job_description').set('html', '');
    check_referred_already();
    
    if (isEmpty(saved_jobs_list.selected_value)) {
        var html = '<div style="text-align: center; padding-top: 50%;">Please select a job to see its job description.</div>';
        $('job_description').set('html', html);
        set_status('');
        return;
    }
    
    var params = 'id=' + saved_jobs_list.selected_value + '&action=get_job_description';
    
    var uri = root + "/members/refer_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading job description.');
                return false;
            }
            
            var ids = xml.getElementsByTagName('id');
            var descriptions = xml.getElementsByTagName('description');
            var potential_rewards = xml.getElementsByTagName('potential_reward');
            var industries = xml.getElementsByTagName('industry');
            var countries = xml.getElementsByTagName('country');
            var states = xml.getElementsByTagName('state');
            var currencies = xml.getElementsByTagName('currency');
            var employers = xml.getElementsByTagName('employer');
            
            var state = '';
            if (states[0].childNodes.length > 0) {
                state = '/' + states[0].childNodes[0].nodeValue;
            }
            
            var html = '<div class="desc_title">Potential Reward</div>' + "\n";
            html = html + '<div class="desc_reward">' + currencies[0].childNodes[0].nodeValue + ' ' + potential_rewards[0].childNodes[0].nodeValue + '</div>' + "\n";
            html = html + '<div class="desc_title">Industry</div>' + "\n";
            html = html + '<div class="desc_field">' + industries[0].childNodes[0].nodeValue + '</div>' + "\n";
            html = html + '<div class="desc_title">Employer</div>' + "\n";
            html = html + '<div class="desc_field">' + employers[0].childNodes[0].nodeValue + '</div>' + "\n";
            html = html + '<div class="desc_title">Description</div>' + "\n";
            html = html + '<div class="desc_description">' + descriptions[0].childNodes[0].nodeValue + '</div>' + "\n";
            html = html + '<div class="desc_title">Country/State</div>' + "\n";
            html = html + '<div class="desc_field">' + countries[0].childNodes[0].nodeValue + state + '</div>' + "\n";
            
            $('job_description').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading job description...');
        }
    });
    
    request.send(params);
}

function check_referred_already() {
    if (!isEmpty(saved_jobs_list.selected_value) && !isEmpty(candidates_list.selected_value)) {
        var params = 'job=' + saved_jobs_list.selected_value + 
                      '&id=' + id + 
                      '&candidate=' + candidates_list.selected_value + 
                      '&action=referred_already';

        var uri = root + "/members/refer_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == '1') {
                    alert("You have already referred " + candidates_list.selected_item + " to this job. \n\nPlease unselect from the Saved Jobs list.");
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

function set_filter() {
    filter_by = $('network_filter').options[$('network_filter').selectedIndex].value;
    show_candidates();
}

function set_job_filter() {
    job_filter_by = $('job_filter').options[$('job_filter').selectedIndex].value;
    show_saved_jobs();
}

function check_has_banks(_member) {
    var params = 'id=' + _member + '&action=has_banks';
    var uri = root + "/members/refer_action.php";
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

function refer() {
    var answer_1 = $('testimony_answer_1').value;
    var answer_2 = $('testimony_answer_2').value;
    var answer_3 = $('testimony_answer_3').value;
    var candidate = $('email_addr').value;
    var number_of_jobs = 1;
    var job = '';
    var from = 'list'; // list or email
    
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
    
    if (!isEmpty(candidate)) {
        if (!isEmail(candidate)) {
            alert('Please select at least a candidate from your contacts, or enter the e-mail addres of a new candidate.');
            return false;
        }
        
        from = 'email'; 
    } else {
        candidate = candidates_list.selected_value;
    }
    
    var testimony = answer_1 + '<br/>' + answer_2 + '<br/>' + answer_3;
    
    var jobs = saved_jobs_list.get_selected_values();
    number_of_jobs = jobs.length;
    for (var i=0; i < number_of_jobs; i++) {
        var job_details = jobs[i].split('|');
        job = job + job_details[1];
        
        if (i < number_of_jobs-1) {
            job = job + '|';
        }
    }
    
    var params = 'id=' + id + '&action=make_referral';
    params = params + '&referee=' + candidate;
    params = params + '&job=' + job;
    params = params + '&testimony=' + testimony;
    params = params + '&from=' + from;
    
    var uri = root + "/members/refer_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('You have already referred one of the contacts to the job. Please refer another contact.');
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
            set_status('Your contact was successfully referred. A notification email has been sent to the referred contact. You may make another referrals.');
        },
        onRequest: function(instance) {
            set_status('Making referral...');
        }
    });
    
    request.send(params);
    
    check_has_banks(id);
}

function onDomReady() {
    set_root();
    get_employers_for_mini();
    get_industries_for_mini();
    set_mini_keywords();
    get_referrals_count();
    get_requests_count();
    get_jobs_employed_count();
    
    $('candidates').addEvent('click', function() {
        $('email_addr').value = '';
        check_referred_already();
    });
    $('refer').addEvent('click', show_testimony_form);
    $('saved_jobs').addEvent('click', show_job_details);
    $('email_addr').addEvent('focus', function() {
        candidates_list.declick();
    });
    
    $('testimony_answer_1').addEvent('keyup', function() {
       update_word_count_of('word_count_q1', 'testimony_answer_1') 
    });

    $('testimony_answer_2').addEvent('keyup', function() {
       update_word_count_of('word_count_q2', 'testimony_answer_2') 
    });
    
    $('testimony_answer_3').addEvent('keyup', function() {
       update_word_count_of('word_count_q3', 'testimony_answer_3') 
    });
    
    show_candidates();
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
