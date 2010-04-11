var order_by = 'relevance';
var order = 'desc';
//var offset = 0;
var current_page = 1;
var total_pages = 1;
var filter_by = '0';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
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

function back_to_results() {
    $('div_job_info').setStyle('display', 'none');
    $('div_search_results').setStyle('display', 'block');
    set_status('');
}

function show_pagination_dropdown() {
    var html = '<select id="pagination_dropdown" name="page" onChange="go_to_page();">' + "\n";
    for (var i = 1; i <= total_pages; i++) {
        if (i == current_page) {
            html = html + '<option value="' + i + '" selected>' + i + '</option>' + "\n";
        } else {
            html = html + '<option value="' + i + '">' + i + '</option>' + "\n";
        }
    }
    html = html + '</select>' + "\n";
    
    $('current_page').set('html', html);
    $('current_page_1').set('html', html);
}

function show_limit_dropdown() {
    var html = '<select id="limit_dropdown" name="limit" onChange="filter_jobs();">' + "\n";
    for (var i = 5; i <= 50; i += 5) {
        if (i == limit) {
            html = html + '<option value="' + i + '" selected>' + i + '</option>' + "\n";
        } else {
            html = html + '<option value="' + i + '">' + i + '</option>' + "\n";
        }
    }
    html = html + '</select>' + "\n";
    
    $('filter_limit_dropdown').set('html', html);
}

function filter_jobs() {
    if ($('industry_dropdown').options[$('industry_dropdown').selectedIndex].value == 0 && 
        $('country_dropdown').options[$('country_dropdown').selectedIndex].value == 0 && 
        $('mini_employer').options[$('mini_employer').selectedIndex].value == 0 && 
        isEmpty(keywords)) {
        alert('Listing jobs in any industry from any country without prior entering keywords will slow down the system.\n\nPlease refine your search by using keywords in any available keywords field.');
        
        $('industry_dropdown').selectedIndex = industry;
        $('country_dropdown').selectedIndex = country_code;
        return false;
    }
    
    industry = $('industry_dropdown').options[$('industry_dropdown').selectedIndex].value;
    country_code = $('country_dropdown').options[$('country_dropdown').selectedIndex].value;
    limit = $('limit_dropdown').options[$('limit_dropdown').selectedIndex].value;
    
    offset = 0;
    show_jobs();
}

function go_to_last_page() {
    offset = (parseInt(total_pages) - 1) * parseInt(limit);
    show_jobs();
}

function go_to_first_page() {
    offset = 0;
    show_jobs();
}

function go_to_next_page() {
    offset = parseInt(offset) + parseInt(limit);
    show_jobs();
}

function go_to_previous_page() {
    offset = parseInt(offset) - parseInt(limit);
    show_jobs();
}

function go_to_page() {
    offset = (parseInt($('pagination_dropdown').options[$('pagination_dropdown').selectedIndex].value) - 1) * parseInt(limit);
    show_jobs();
}

function show_jobs() {
    $('div_job_info').setStyle('display', 'none');
    $('div_search_results').setStyle('display', 'block');
    
    var params = 'industry=' + industry;
    params = params + '&employer=' + employer;
    params = params + '&country_code=' + country_code;
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
                set_status('An error occured while searching jobs.');
                return false;
            }
            
            if (txt == '0') {
                set_status('No job found with the criteria.');
                $('div_list').set('html', '');
                $('current_page').set('html', '0');
                $('total_page').set('html', '0');
                $('current_page_1').set('html', '0');
                $('total_page_1').set('html', '0');
                show_limit_dropdown();
                return false;
            }
            
            var ids = xml.getElementsByTagName('id');
            var matches = xml.getElementsByTagName('match_percentage');
            var job_titles = xml.getElementsByTagName('title');
            var industries = xml.getElementsByTagName('industry');
            var countries = xml.getElementsByTagName('country');
            var states = xml.getElementsByTagName('state');
            var employers = xml.getElementsByTagName('name');
            var currencies = xml.getElementsByTagName('currency');
            var salaries = xml.getElementsByTagName('salary');
            var salary_ends = xml.getElementsByTagName('salary_end');
            var created_ons = xml.getElementsByTagName('formatted_created_on');
            var rewards = xml.getElementsByTagName('potential_reward');
            var total_results = xml.getElementsByTagName('total_results');
            var current_pages = xml.getElementsByTagName('current_page');
            var changed_country_code = xml.getElementsByTagName('changed_country_code');
            
            var total = total_results[0].childNodes[0].nodeValue;
            total_pages = Math.ceil(total / limit);
            current_page = current_pages[0].childNodes[0].nodeValue;
            
            var next_page_button_html = '';
            if (current_page < total_pages) {
                next_page_button_html = '<a class="no_link" onClick="go_to_next_page();"><img src="' + root + '/common/images/next_page.jpg" style="vertical-align: middle;" onMouseOver="this.src=root + \'/common/images/next_page_hover.jpg\'" onMouseOut="this.src=root + \'/common/images/next_page.jpg\'" /></a>&nbsp;&nbsp;';
                next_page_button_html = next_page_button_html + '<a class="no_link" onClick="go_to_last_page();"><img src="' + root + '/common/images/last_page.jpg" style="vertical-align: middle;" onMouseOver="this.src=root + \'/common/images/last_page_hover.jpg\'" onMouseOut="this.src=root + \'/common/images/last_page.jpg\'" /></a>';
            }
            
            var previous_page_button_html = '';
            if (current_page > 1) {
                previous_page_button_html = '<a class="no_link" onClick="go_to_first_page();"><img src="' + root + '/common/images/first_page.jpg" style="vertical-align: middle;" onMouseOver="this.src=root + \'/common/images/first_page_hover.jpg\'" onMouseOut="this.src=root + \'/common/images/first_page.jpg\'" /></a>&nbsp;&nbsp;';
                previous_page_button_html = previous_page_button_html +  '<a class="no_link" onClick="go_to_previous_page();"><img src="' + root + '/common/images/previous_page.jpg" style="vertical-align: middle;" onMouseOver="this.src=root + \'/common/images/previous_page_hover.jpg\'" onMouseOut="this.src=root + \'/common/images/previous_page.jpg\'" /></a>';
            }
            
            var html = '<table id="list" class="list">';
            for (var i=0; i < ids.length; i++) {
                var job_id = ids[i].childNodes[0].nodeValue;
                
                html = html + '<tr id="'+ job_id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                html = html + '<td class="match_percentage"><img src="' + root + '/common/images/match_bar.jpg" style="height: 4px; width: ' + Math.floor(matches[i].childNodes[0].nodeValue / 100 * 50) + 'px; vertical-align: middle;" /></td>' + "\n";
                html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                html = html + '<td class="industry">' + industries[i].childNodes[0].nodeValue + '</td>' + "\n";
                //html = html + '<td class="title"><a class="no_link" onClick="show_job(\'' + job_id + '\');">' + job_titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                html = html + '<td class="title"><a href="' + root + '/job/' + job_id + '">' + job_titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                //html = html + '<td class="date">' + created_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                html = html + '<td class="country">' + countries[i].childNodes[0].nodeValue + '</td>' + "\n";
                
                var state = 'n/a';
                if (states[i].childNodes.length > 0) {
                    state = states[i].childNodes[0].nodeValue;
                }
                
                html = html + '<td class="state">' + state + '</td>' + "\n";
                
                var salary_end = '';
                if (salary_ends[i].childNodes.length > 0) {
                    salary_end = '&nbsp;-&nbsp;' + salary_ends[i].childNodes[0].nodeValue;
                }
                html = html + '<td class="salary">' + currencies[i].childNodes[0].nodeValue + '$ ' + salaries[i].childNodes[0].nodeValue + salary_end + '</td>' + "\n";
                if (rewards[i].childNodes.length > 0) {
                    html = html + '<td class="potential_reward">' + currencies[i].childNodes[0].nodeValue + '$ ' + rewards[i].childNodes[0].nodeValue + '</td>' + "\n";
                } else {
                    html = html + '<td class="potential_reward">0.00</td>' + "\n";
                }
                
                html = html + '</tr>' + "\n";
            }
            html = html + '</table>';
            
            $('div_list').set('html', html);
            $('current_page').set('html', current_page);
            $('total_page').set('html', total_pages);
            $('current_page_1').set('html', current_page);
            $('total_page_1').set('html', total_pages);
            $('next_page').set('html', next_page_button_html);
            $('next_page_1').set('html', next_page_button_html);
            $('previous_page').set('html', previous_page_button_html);
            $('previous_page_1').set('html', previous_page_button_html);
            
            show_pagination_dropdown();
            show_limit_dropdown();
            set_status('');
            
            if (changed_country_code[0].childNodes[0].nodeValue == '1') {
                country_code = '';
                list_countries_in('', 'filter_country_dropdown', 'country_dropdown', 'country_dropdown', true, 'filter_jobs();');
            }
        },
        onRequest: function(instance) {
            set_status('Searching jobs...');
        }
    });
    
    request.send(params);
    
    list_industries_in(industry, 'filter_industry_dropdown', 'industry_dropdown', 'industry_dropdown', true, 'filter_jobs();');
    list_countries_in(country_code, 'filter_country_dropdown', 'country_dropdown', 'country_dropdown', true, 'filter_jobs();');
}

function show_job(job_id) {
    $('div_job_info').setStyle('display', 'block');
    $('div_search_results').setStyle('display', 'none');
    $('job_id').value = job_id;
    
    var params = 'id=' + job_id;
    params = params + '&action=get_job_info';
    
    var uri = root + "/search_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading job.');
                return false;
            } 
            
            var title = xml.getElementsByTagName('title');
            var industry = xml.getElementsByTagName('full_industry');
            var employer = xml.getElementsByTagName('employer_name');
            var country = xml.getElementsByTagName('country_name');
            var state = xml.getElementsByTagName('state');
            var currency = xml.getElementsByTagName('currency_symbol');
            var salary = xml.getElementsByTagName('salary');
            var salary_end = xml.getElementsByTagName('salary_end');
            var salary_negotiable = xml.getElementsByTagName('salary_negotiable');
            var description = xml.getElementsByTagName('description');
            var created_on = xml.getElementsByTagName('formatted_created_on');
            var expire_on = xml.getElementsByTagName('formatted_expire_on');
            var expired = xml.getElementsByTagName('expired');
            var potential_reward = xml.getElementsByTagName('potential_reward');
            
            $('job.potential_reward').set('html', potential_reward[0].childNodes[0].nodeValue);
            $('job.title').set('html', title[0].childNodes[0].nodeValue);
            $('job.industry').set('html', industry[0].childNodes[0].nodeValue);
            $('job.employer').set('html', employer[0].childNodes[0].nodeValue);
            $('job.currency').set('html', currency[0].childNodes[0].nodeValue);
            $('job.currency_1').set('html', currency[0].childNodes[0].nodeValue);
            $('job.country').set('html', country[0].childNodes[0].nodeValue);
            $('job.description').set('html', description[0].childNodes[0].nodeValue);
            $('job.created_on').set('html', created_on[0].childNodes[0].nodeValue);
            $('job.expire_on').set('html', expire_on[0].childNodes[0].nodeValue);
            
            if (state[0].childNodes.length <= 0) {
                $('job.state').set('html', '<span style="font-style: italic;">(None provided.)</span>');
            } else {
                $('job.state').set('html', state[0].childNodes[0].nodeValue);
            }
            
            if (salary[0].childNodes.length <= 0) {
                $('job.salary').set('html', '<span style="font-style: italic;">(None provided.)</span>');
            } else {
                $('job.salary').set('html', salary[0].childNodes[0].nodeValue);
            }
            
            if (salary_end[0].childNodes.length <= 0) {
                $('job.salary_end').set('html', '');
            } else {
                $('job.salary_end').set('html', '-&nbsp;' + salary_end[0].childNodes[0].nodeValue);
            }
            
            if (salary_negotiable[0].childNodes[0].nodeValue == 'Y') {
                $('job.salary_negotiable').set('html', 'Negotiable');
            } else {
                $('job.salary_negotiable').set('html', 'Not Negotiable');
            }
            
            var html = '';
            if (id != '0') {
                html = '<input class="button" type="button" id="save_job" name="save_job" value="Save Job" onClick="save_job();" />&nbsp;<input class="button" type="button" id="refer_job" name="refer_job" value="Refer Now" onClick="show_refer_job();" />';
            }
            
            $('job_buttons').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading job...');
        }
    });
    
    request.send(params);
}

function save_job() {
    var params = 'id=' + $('job_id').value;
    params = params + '&member=' + id;
    params = params + '&action=save_job_to_bin';
    
    var uri = root + "/search_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while saving job.');
                return false;
            } 
            
            if (txt == '-1') {
                alert('This job was previously saved.');
                set_status('');
                return true;
            }
            
            alert('Job was successfully saved.');
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Saving job...');
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
}

window.addEvent('domready', onDomReady);