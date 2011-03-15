var order_by = 'ucr.added_on';
var order = 'desc';

var current_candidate_email = '';
var current_referrer_email = '';
var current_job_id = 0;

// Job class for easy storage
// function Job(_id, _title, _employer, _industry, _currency, _salary, _description) {
//     this.id = _id;
//     this.title = _title;
//     this.employer = _employer;
//     this.industry = _industry;
//     this.currency = _currency;
//     this.salary = _salary;
//     this.description = _description;
// }
// var available_jobs = new Array();
// var jobs_list = new ListBox('jobs', 'jobs_list', false);
// var jobs_filter_by = '0';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function show_uploaded_resumes() {
    var params = 'id=' + id + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/prs/resumes_uploaded_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading resumes.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no users contributed resumes at the moment.</div>';
            } else {
                var job_ids = xml.getElementsByTagName('job_id');
                var job_titles = xml.getElementsByTagName('job');
                var job_employers = xml.getElementsByTagName('job_employer');
                var job_industries = xml.getElementsByTagName('job_industry');
                var candidate_email_addrs = xml.getElementsByTagName('candidate_email_addr');
                var candidate_names = xml.getElementsByTagName('candidate');
                var candidate_phone_nums = xml.getElementsByTagName('candidate_phone_num');
                var candidate_zips = xml.getElementsByTagName('candidate_zip');
                var candidate_countries = xml.getElementsByTagName('candidate_country');
                var referrer_email_addrs = xml.getElementsByTagName('referrer_email_addr');
                var referrer_names = xml.getElementsByTagName('referrer');
                var referrer_phone_nums = xml.getElementsByTagName('referrer_phone_num');
                var referrer_zips = xml.getElementsByTagName('referrer_zip');
                var referrer_countries = xml.getElementsByTagName('referrer_country');
                var added_ons = xml.getElementsByTagName('formatted_added_on');
                var file_names = xml.getElementsByTagName('resume_label');
                
                for (var i=0; i < job_ids.length; i++) {
                    html = html + '<tr id="'+ i + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="date">' + added_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="candidate"><a href="mailto: ' + candidate_email_addrs[i].childNodes[0].nodeValue + '">' + candidate_names[i].childNodes[0].nodeValue + '</a><br/><div class="phone_num"><strong>Tel:</strong> ' + candidate_phone_nums[i].childNodes[0].nodeValue + '<br/><strong>E-mail:</strong> ' + candidate_email_addrs[i].childNodes[0].nodeValue + '<br/><strong>Postcode:</strong> ' + candidate_zips[i].childNodes[0].nodeValue + '<br/><strong>Country:</strong> ' + candidate_countries[i].childNodes[0].nodeValue + '</div></td>' + "\n";
                    html = html + '<td class="candidate"><a href="mailto: ' + referrer_email_addrs[i].childNodes[0].nodeValue + '">' + referrer_names[i].childNodes[0].nodeValue + '</a><br/><div class="phone_num"><strong>Tel:</strong> ' + referrer_phone_nums[i].childNodes[0].nodeValue + '<br/><strong>E-mail:</strong> ' + referrer_email_addrs[i].childNodes[0].nodeValue + '<br/><strong>Postcode:</strong> ' + referrer_zips[i].childNodes[0].nodeValue + '<br/><strong>Country:</strong> ' + referrer_countries[i].childNodes[0].nodeValue + '</div></td>' + "\n";
                    
                    if (file_names[i].childNodes.length > 0) {
                        html = html + '<td class="resume"><a href="../employees/resume.php?job_id=' + job_ids[i].childNodes[0].nodeValue + '&candidate_email=' + candidate_email_addrs[i].childNodes[0].nodeValue + '&referrer_email=' + referrer_email_addrs[i].childNodes[0].nodeValue + '">' + file_names[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    } else {
                        html = html + '<td class="resume"><span style="font-size: 9pt; color: #CCCCCC; font-style: italic;">None Provided</span></td>' + "\n";
                    }
                    
                    html = html + '<td class="job"><a href="' + root + '/job/' + job_ids[i].childNodes[0].nodeValue + '">' + job_titles[i].childNodes[0].nodeValue + '</a><br/><div class="phone_num"><strong>Industry:</strong> ' + job_industries[i].childNodes[0].nodeValue + '<br/><strong>Employer:</strong> ' + job_employers[i].childNodes[0].nodeValue + '</div></td>' + "\n";
                    html = html + '<td class="actions"><a class="no_link" onClick="show_add_to_privileged_form(' + job_ids[i].childNodes[0].nodeValue + ',\'' + candidate_email_addrs[i].childNodes[0].nodeValue + '\',\'' + referrer_email_addrs[i].childNodes[0].nodeValue + '\', \'' + referrer_countries[i].childNodes[0].nodeValue + '\');">Add To Privileged</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
            }
            html = html + '</table>';
            
            $('div_candidates_list').set('html', html);
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading resumes...');
        }
    });
    
    request.send(params);
}

function add_to_privileged() {
    var params = 'id=' + id + '&user_id=' + user_id + '&action=add_to_privileged';
    params = params + '&recommender_email=' + current_referrer_email;
    params = params + '&candidate_email=' + current_candidate_email;
    params = params + '&job_id=' + current_job_id;
    params = params + '&recommender_remarks=' + $('recommender_remark').value;
    params = params + '&recommender_region=' + $('recommender_region').value;
    params = params + '&candidate_remarks=' + $('candidate_remark').value;
    
    var industries = '';
    for (var i=0; i < $('recommender_industries').options.length; i++) {
        if ($('recommender_industries').options[i].selected) {
            if (isEmpty(industries)) {
                industries = $('recommender_industries').options[i].value;
            } else {
                industries = industries + ',' + $('recommender_industries').options[i].value;
            }
        }
    }
    params = params + '&recommender_industries=' + industries;
    
    if ($('candidate_primary_industry').selectedIndex > 0) {
        params = params + '&candidate_primary_industry=' + $('candidate_primary_industry').options[$('candidate_primary_industry').selectedIndex].value;
    }
    
    if ($('candidate_secondary_industry').selectedIndex > 0) {
        params = params + '&candidate_secondary_industry=' + $('candidate_secondary_industry').options[$('candidate_secondary_industry').selectedIndex].value;
    }
    
    if ($('candidate_tertiary_industry').selectedIndex > 0) {
        params = params + '&candidate_tertiary_industry=' + $('candidate_tertiary_industry').options[$('candidate_tertiary_industry').selectedIndex].value;
    }
    
    var uri = root + "/prs/resumes_uploaded_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while adding candidate to privileged list.');
                return false;
            }
            
            if (txt == '-1') {
                alert('The buffered record no longer exists. Please try another.');
            } else if (txt == '-2') {
                alert('Unable to create recommender. Please try again later.');
                return false;
            } else if (txt == '-3') {
                alert('Unable to create member account. Please report this to the administrator.');
                return false;
            } else if (txt == '-4') {
                alert('Unable to create member activation token. Please report this to the administrator');
                return false;
            } else if (txt == '-5') {
                alert('There is an existing member using the same email address.');
                return false;
            } else if (txt == '-6') {
                alert('Everything was created successfully, except for recommender\'s industries are not added into the system, and default contact was not added into the system.\n\nPlease update through the Recommenders section for the former, and report the latter to the system administrator.');
            } else if (txt == '-7') {
                alert('Everything was created successfully, except for recommender\'s industries are not added into the system.\n\nPlease update through the Recommenders section.');
            } else if (txt == '-8') {
                alert('Everything was created successfully, except for default contact was not added into the system.\n\nPlease report this to system administrator');
            } else if (txt == '-9') {
                alert('Unable to create resume record. Please report this to system administrator.');
                return false;
            } else if (txt == '-10') {
                alert('Unable to copy resume. Please report this to system administrator.');
                return false;
            }
            
            close_add_to_privileged_form();
            set_status('');
            show_uploaded_resumes();
        },
        onRequest: function(instance) {
            set_status('Adding...');
        }
    });
    
    request.send(params);
}

function close_add_to_privileged_form() {
    $('div_add_to_privileged_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
    
    current_job_id = 0;
    current_candidate_email = '';
    current_referrer_email = '';
}

function show_add_to_privileged_form(_job_id, _candidate_email, _referrer_email, _referrer_country) {
    current_job_id = _job_id;
    current_candidate_email = _candidate_email;
    current_referrer_email = _referrer_email;
    
    $('div_blanket').setStyle('display', 'block');
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_add_to_privileged_form').getStyle('height'));
    var div_width = parseInt($('div_add_to_privileged_form').getStyle('width'));
    
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
    
    $('div_add_to_privileged_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_add_to_privileged_form').setStyle('left', ((window_width - div_width) / 2));
    
    $('recommender_region').value = _referrer_country;
    
    $('div_add_to_privileged_form').setStyle('display', 'block');
}

function onDomReady() {
    initialize_page();
    list_available_industries('0');
    
    $('sort_added_on').addEvent('click', function() {
        order_by = 'ucr.added_on';
        ascending_or_descending();
        show_uploaded_resumes();
    });
    
    $('sort_candidate').addEvent('click', function() {
        order_by = 'ucr.candidate_lastname';
        ascending_or_descending();
        show_uploaded_resumes();
    });
    
    $('sort_referrer').addEvent('click', function() {
        order_by = 'ucr.referrer_firstname';
        ascending_or_descending();
        show_uploaded_resumes();
    });
    
    $('sort_job').addEvent('click', function() {
        order_by = 'jobs.title';
        ascending_or_descending();
        show_uploaded_resumes();
    });
    
    show_uploaded_resumes();
}

window.addEvent('domready', onDomReady);
