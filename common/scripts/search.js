var order_by = 'jobs.created_on';
var order = 'desc';
var is_filter = false;

function changed_country() {
    // is_local = 0;
}

function change_order() {
    order = $('order_selector').options[$('order_selector').selectedIndex].value;
    sort_using_selected();
}

function sort_using_selected() {
    sort_by($('sort_selector').options[$('sort_selector').selectedIndex].value);
}

function sort_by(_column) {
    order_by = _column;
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
                    var total_rewards = parseFloat(to_number(rewards[i].childNodes[0].nodeValue));
                    var token_reward = total_rewards * 0.05;
                    var potential_reward = total_rewards - token_reward;
                    
                    var a_result = a_result_template;
                    a_result = a_result.replace(/%job_id%/g, ids[i].childNodes[0].nodeValue);
                    a_result = a_result.replace(/%job_title%/g, job_titles[i].childNodes[0].nodeValue);
                    a_result = a_result.replace(/%currency%/g, currencies[i].childNodes[0].nodeValue);
                    
                    a_result = a_result.replace(/%employer%/g, employers[i].childNodes[0].nodeValue);
                    a_result = a_result.replace(/%country%/g, countries[i].childNodes[0].nodeValue);
                    a_result = a_result.replace(/%industry%/g, industries[i].childNodes[0].nodeValue);
                    
                    var salary_range = salaries[i].childNodes[0].nodeValue;
                    if (salary_ends[i].childNodes.length > 0) {
                        salary_range = salary_range + ' - ' + salary_ends[i].childNodes[0].nodeValue;
                    }
                    a_result = a_result.replace(/%salary_range%/g, salary_range);
                    
                    a_result = a_result.replace(/%potential_reward%/g, to_nice_number(potential_reward.toFixed(2), 2));
                    a_result = a_result.replace(/%potential_token_reward%/g, to_nice_number(token_reward.toFixed(2), 2));
                    a_result = a_result.replace(/%expire_on%/g, expire_ons[i].childNodes[0].nodeValue);
                    
                    html = html + a_result;
                }
                
                $('searched_results').set('html', html);
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

function onDomReady() {
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

function onLoaded() {
    initialize_page();
}

window.addEvent('domready', onDomReady);
window.addEvent('load', onLoaded);