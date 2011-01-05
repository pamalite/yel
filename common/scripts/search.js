var order_by = 'jobs.created_on';
var order = 'desc';
var is_filter = false;

function changed_country() {
    // is_local = 0;
}

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function sort_by(_column) {
    order_by = _column;
    ascending_or_descending();
    show_jobs();
}

function filter_jobs() {
    country_code = $('filter_country').options[$('filter_country').selectedIndex].value;
    industry = $('filter_industry').options[$('filter_industry').selectedIndex].value;
    employer = $('filter_employer').options[$('filter_employer').selectedIndex].value;
    filter_salary = $('filter_salary').options[$('filter_salary').selectedIndex].value;
    offset = 0;
    is_filter = true;
    show_jobs();
}

function show_jobs() {
    if ($('page') != null || !is_filter) {
        offset = parseInt($('page').options[$('page').selectedIndex].value) * limit;
    }
    
    var params = 'industry=' + industry;
    params = params + '&employer=' + employer;
    params = params + '&country_code=' + country_code;
    params = params + '&country=' + country_code;
    // params = params + '&is_local=' + is_local;
    params = params + '&salary=' + filter_salary;
    
    if (parseInt(filter_salary_end) > 0) {
        params = params + '&salary_end=' + filter_salary_end;
    }
    
    params = params + '&keywords=' + keywords;
    params = params + '&offset=' + offset;
    params = params + '&limit=' + limit;
    params = params + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/search_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while searching jobs.');
                return false;
            }
            
            if (txt == '0') {
                $('statistics').set('html', 'Found 0 jobs in 0 seconds.');
                $('pagination').set('html', 'Page 1 of 1');
                $('results').set('html', '<div class="empty_results">No jobs with the criteria found.</div>');
            } else {
                var total_jobs = xml.getElementsByTagName('total');
                var elapsed = xml.getElementsByTagName('elapsed');
                
                $('statistics').set('html', 'Found ' + total_jobs[0].childNodes[0].nodeValue + ' jobs in ' + elapsed[0].childNodes[0].nodeValue + ' seconds.');
                
                if (is_filter) {
                    if (parseInt(total_jobs[0].childNodes[0].nodeValue) <= limit) {
                        $('pagination').set('html', 'Page 1 of 1');
                    } else {
                        var pages = Math.ceil(parseInt(total_jobs[0].childNodes[0].nodeValue) / parseInt(limit));
                        var html = 'Page <select id="page" onChange="show_jobs();">' + "\n";

                        for (var i=0; i < pages; i++) {
                            if (i == 0) {
                                html = html + '<option value="' + i + '" selected>' + (i+1) + '</option>' + "\n";
                            } else {
                                html = html + '<option value="' + i + '">' + (i+1) + '</option>' + "\n";
                            }
                        }

                        html = html + '</select> of ' + pages + "\n";
                        $('pagination').set('html', html);
                    }
                }
                
                var ids = xml.getElementsByTagName('id');
                var job_titles = xml.getElementsByTagName('title');
                var industries = xml.getElementsByTagName('industry');
                var countries = xml.getElementsByTagName('country');
                var descriptions = xml.getElementsByTagName('description');
                var employers = xml.getElementsByTagName('employer');
                var currencies = xml.getElementsByTagName('currency');
                var salaries = xml.getElementsByTagName('salary');
                var salary_ends = xml.getElementsByTagName('salary_end');
                var expire_ons = xml.getElementsByTagName('formatted_expire_on');
                var rewards = xml.getElementsByTagName('potential_reward');
                
                var html = '';
                for (var i=0; i < ids.length; i++) {
                    var job_short_details = '<div class="job_short_details">' + "\n";
                    
                    job_short_details = job_short_details + '<div class="job_title">' + "\n";
                    job_short_details = job_short_details + '<span class="industry">' + industries[i].childNodes[0].nodeValue + '</span>' + "\n";
                    job_short_details = job_short_details + '<a href="./job/' + ids[i].childNodes[0].nodeValue + '">' + job_titles[i].childNodes[0].nodeValue + '</a>' + "\n";
                    job_short_details = job_short_details + '</div>' + "\n";
                    
                    job_short_details = job_short_details + '<div class="employer">' + "\n";
                    job_short_details = job_short_details + employers[i].childNodes[0].nodeValue + "\n";
                    job_short_details = job_short_details + '<span class="country">' + countries[i].childNodes[0].nodeValue + '</span>' + "\n";
                    job_short_details = job_short_details + '</div>' + "\n";
                    
                    job_short_details = job_short_details + '<div class="description">' + "\n";
                    job_short_details = job_short_details + descriptions[i].childNodes[0].nodeValue + '...</div>' + "\n";
                    
                    job_short_details = job_short_details + '<div class="date_and_salary">' + "\n";
                    job_short_details = job_short_details + '<span class="salary">' + currencies[i].childNodes[0].nodeValue + "$ \n";
                    if (salary_ends[i].childNodes.length > 0) {
                        job_short_details = job_short_details + salaries[i].childNodes[0].nodeValue + ' - ' + salary_ends[i].childNodes[0].nodeValue;
                    } else {
                        job_short_details = job_short_details + salaries[i].childNodes[0].nodeValue
                    }
                    job_short_details = job_short_details + '</span>' + "\n";
                    job_short_details = job_short_details + '&nbsp;<span class="reward">Potential Reward:' + currencies[i].childNodes[0].nodeValue + '$ ' + rewards[i].childNodes[0].nodeValue + '</span>' + "\n";
                    job_short_details = job_short_details + '&nbsp;<span class="controls">' + "\n";
                    job_short_details = job_short_details + '<a href="./job/' + ids[i].childNodes[0].nodeValue + '?refer=1">Refer Now</a> | <a href="./job/' + ids[i].childNodes[0].nodeValue + '?apply=1">Apply Now</a> | <a href="./job/' + ids[i].childNodes[0].nodeValue + '">View Details</a>' + '</span>' + "\n";
                    job_short_details = job_short_details + '</div>' + "\n";
                    
                    job_short_details = job_short_details + '<div class="expire_on">Expires on ' + expire_ons[i].childNodes[0].nodeValue + '</div>' + "\n";
                    
                    job_short_details = job_short_details + '</div>' + "\n";
                    html = html + "\n" + job_short_details;
                }
                
                $('results').set('html', html);
                // window.scrollTo(0, 0);
                
                if ($('page') != null) {
                    $('page').blur();
                }
            }
            
            is_filter = false;
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Searching jobs...');
        }
    });
    
    request.send(params);
}

function close_refer_form() {
    $('div_refer_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_refer_job() {
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
    
    $('job_title').set('html', $('job.title').get('html'));
    
    $('div_refer_form').setStyle('display', 'block');
    show_candidates();
}

function set_filter() {
    filter_by = $('network_filter').options[$('network_filter').selectedIndex].value;
    show_candidates();
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
    var params = 'job=' + $('job_id').value + 
                  '&id=' + id + 
                  '&candidate=' + candidates_list.selected_value + 
                  '&action=referred_already';

    var uri = root + "/members/search_action.php";
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

function check_referred_already() {
    if (!isEmpty(candidates_list.selected_value)) {
        var params = 'job=' + $('job_id').value + 
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
        if (isEmpty(candidates_list.selected_value)) {
            alert('Please select a candidate.');
            return false;
        }
        
        referee = candidates_list.selected_value;
    } else {
        if (isEmpty($('email_addr').value) || !isEmail($('email_addr').value)) {
            alert('Please provide a valid email address of the candidate.');
            return false;
        }
        
        referee = $('email_addr').value;
        from = 'email';
    }
    
    var answer_1 = $('testimony_answer_1').value;
    var answer_2 = $('testimony_answer_2').value;
    var answer_3 = $('testimony_answer_3').value;
    
    if (isEmpty(answer_1) || isEmpty(answer_2) || isEmpty(answer_3)) {
        alert('Please briefly answer all questions.');
        return false;
    } else if (answer_1.split(' ').length > 200 || answer_2.split(' ').length > 200 || answer_3.split(' ').length > 200) {
        if (answer_1.split(' ').length > 200) {
            alert('Please keep your 1st answer below 200 words.');
        } else if (answer_2.split(' ').length > 200) {
            alert('Please keep your 2nd answer below 200 words.');
        } else if (answer_3.split(' ').length > 200) {
            alert('Please keep your 3rd and final answer below 200 words.');
        }
        return false;
    }
    
    var testimony = answer_1 + '<br/>' + answer_2 + '<br/>' + answer_3;
    
    check_has_banks(id);
    
    var params = 'id=' + id + '&action=make_referral';
    params = params + '&from=' + from;
    params = params + '&referee=' + referee;
    params = params + '&job=' + $('job_id').value;
    params = params + '&testimony=' + testimony;
    
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
            } else if (txt == '-2') {
                alert('It appears that this candidate is not in your candidates list. The candidate will be notified before the referral can be made. \n\nYellow Elevator will automatically complete the referral process once the candidate approved the request of being added to your list.');
            } else if (txt == '-3') {
                alert('It appears that this candidate is not in a member of Yellow Elevator. The candidate will be notified before the referral can be made. \n\nYellowElevator.com will automatically complete the referral process once the candidate had signed up as a member. The candidate will be added into your contacts list automatically.');
            }
            
            close_refer_form();
            set_status('Your contact was successfully referred. A notification email has been sent to the referred contact. You may make another referrals.');
        },
        onRequest: function(instance) {
            set_status('Making referral...');
        }
    });
    
    request.send(params);
}

function onDomReady() {
    initialize_page();
    
    for (var i=0; i < $('mini_employer').options.length; i++) {
        if ($('mini_employer').options[i].value == employer) {
            $('mini_employer').selectedIndex = i;
            break;
        }
    }
    
    for (var i=0; i < $('mini_industry').options.length; i++) {
        if ($('mini_industry').options[i].value == industry) {
            $('mini_industry').selectedIndex = i;
            break;
        }
    }
    
    for (var i=0; i < $('mini_country').options.length; i++) {
        if ($('mini_country').options[i].value == country_code) {
            $('mini_country').selectedIndex = i;
            break;
        }
    }
}

window.addEvent('domready', onDomReady);