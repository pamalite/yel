var order_by = 'num_referrals';
var order = 'desc';
var resumes_order_by = 'referrals.referred_on';
var resumes_order = 'desc';

var current_job_id = 0;
var current_job_title = '';
function Candidate() {
    this.name = '';
    this.email_addr = '';
    this.phone_num = '';
    this.testimony = '';
    this.employer_remarks = '';
}
var candidates = new Array();
var is_year_changed = false;

function rate_stars_for(_referral_id, _stars) {
    // TODO: save the ratings
    
    var html = get_display_stars_for(_referral_id, _stars);
    $('referral_' + _referral_id).set('html', html);
}

function get_display_stars_for(_referral_id, _stars) {
    var html = '<a class="no_link" onClick="rate_stars_for(' + _referral_id + ', 0);"><img src="' + root + '/common/images/stars/star_reset.gif" /></a>';
    html = html + '&nbsp;';
    for (var i=0; i < 5; i++) {
        if (i <= (_stars-1)) {
            html = html + '<a class="no_link" onClick="rate_stars_for(' + _referral_id + ', ' + (parseInt(i)+1) + ');"><img src="' + root + '/common/images/stars/star_rated.gif" /></a>';
        } else {
            html = html + '<a class="no_link" onClick="rate_stars_for(' + _referral_id + ', ' + (parseInt(i)+1) + ');"><img src="' + root + '/common/images/stars/star_unrated.gif" /></a>';
        }
        
        if (i < (5-1)) {
            html = html + '&nbsp;';
        }
    }
    
    return html;
}

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function resumes_ascending_or_descending() {
    if (resumes_order == 'desc') {
        resumes_order = 'asc';
    } else {
        resumes_order = 'desc';
    }
}

function sort_by(_table, _column) {
    switch (_table) {
        case 'referred_jobs':
            order_by = _column;
            ascending_or_descending();
            show_referred_jobs();
            break;
        case 'applications':
            resumes_order_by = _column;
            resumes_ascending_or_descending();
            show_resumes_of(current_job_id, current_job_title);
            break;
    }
}

function show_resumes_of(_job_id, _job_title) {
    $('div_referred_jobs').setStyle('display', 'none');
    $('div_resumes').setStyle('display', 'block');
    
    current_job_id = _job_id;
    current_job_title = _job_title;
    $('job_title').set('html', '<a class="no_link" onClick="show_job_description_popup();">' + _job_title + '</a>');
    
    var params = 'id=' + _job_id + '&action=get_resumes';
    params = params + '&order_by=' + resumes_order_by + ' ' + resumes_order;
    params = params + '&filter_by=' + $('filter').options[$('filter').selectedIndex].value;
    
    var uri = root + "/employers/resumes_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            //return;
            if (txt == 'ko') {
                alert('An error occurred while loading resumes.');
                return false;
            }
            
            if (txt == '0') {
                $('div_resumes').set('html', '<div class="empty_results">No resumess found for the selected job posts at this moment.</div>');
            } else {
                var referral_ids = xml.getElementsByTagName('id');
                var resume_ids = xml.getElementsByTagName('resume');
                var referrers = xml.getElementsByTagName('referrer');
                var candidate_names = xml.getElementsByTagName('candidate');
                var referred_ons = xml.getElementsByTagName('formatted_referred_on');
                var employed_ons = xml.getElementsByTagName('formatted_employed_on');
                var employer_agreed_terms_ons = xml.getElementsByTagName('employer_agreed_terms_on');
                var referrer_emails = xml.getElementsByTagName('referrer_email_addr');
                var candidate_emails = xml.getElementsByTagName('candidate_email_addr');
                var referrer_phone_nums = xml.getElementsByTagName('referrer_phone_num');
                var candidate_phone_nums = xml.getElementsByTagName('candidate_phone_num');
                var testimonies = xml.getElementsByTagName('testimony');
                var remarks = xml.getElementsByTagName('employer_remarks');
                
                var resumes_table = new FlexTable('resumes_table', 'resumes');
                
                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('applications', 'referrals.referred_on');\">Applied On</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('applications', 'candidate');\">Candidate</a>", '', 'header'));
                header.set(2, new Cell("Resume", '', 'header'));
                header.set(3, new Cell("Remarks", '', 'header'));
                header.set(4, new Cell('&nbsp;', '', 'header'));
                resumes_table.set(0, header);
                
                candidates = new Array();
                for (var i=0; i < referral_ids.length; i++) {
                    candidates[i] = new Candidate();
                    candidates[i].name = candidate_names[i].childNodes[0].nodeValue;
                    candidates[i].email_addr = candidate_emails[i].childNodes[0].nodeValue;
                    candidates[i].phone_num = candidate_phone_nums[i].childNodes[0].nodeValue
                    candidates[i].testimony = '';
                    var has_testimony = false;
                    if (testimonies[i].childNodes.length > 0) {
                        has_testimony = true;
                        candidates[i].testimony = testimonies[i].childNodes[0].nodeValue;
                    }
                    
                    candidates[i].employer_remarks = '';
                    if (remarks[i].childNodes.length > 0) {
                        candidates[i].employer_remarks = remarks[i].childNodes[0].nodeValue;
                    }
                    
                    var is_agreed_terms = false;
                    if (employer_agreed_terms_ons[i].childNodes[0].nodeValue != '-1') {
                        is_agreed_terms = true;
                    }
                    
                    var row = new Row('');
                    row.set(0, new Cell(referred_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var candidate_details = '';
                    if (is_agreed_terms) {
                        candidate_details = candidates[i].name;
                        candidate_details = candidate_details + '<div class="mini_contacts">Tel: ' + candidates[i].phone_num + '<br/>Email: <a href="mailto: ' + candidates[i].email_addr + '">' + candidates[i].email_addr + '</a></div></div>';
                    } else {
                        candidate_details = '<div id="candidate_' + i + '">' + candidates[i].name + '</div>';
                    }
                    row.set(1, new Cell(candidate_details, '', 'cell'));
                    
                    var agreed_terms = 'false';
                    if (is_agreed_terms) {
                        agreed_terms = 'true';
                    }
                    
                    var resume_link = '<a class="no_link" onClick="download_resume(' + referral_ids[i].childNodes[0].nodeValue + ', ' + resume_ids[i].childNodes[0].nodeValue + ', ' + i + ', ' + agreed_terms + ');">Download</a>';
                    
                    if (has_testimony) {
                        resume_link = resume_link + '<br/><a class="no_link testimony_link" onClick="show_testimony_popup(' + i + ');">(View Testimony)</a>';
                    }
                    row.set(2, new Cell(resume_link, '', 'cell actions_column'));
                    
                    var remarks_link = '<span style="font-style: italic; color: #CCCCCC;">Disabled</span>';
                    if (is_agreed_terms) {
                        remarks_link = '<a class="no_link" onClick="show_remarks_popup(' + referral_ids[i].childNodes[0].nodeValue + ', ' + i + ');">View/Update</a>';
                    }
                    row.set(3, new Cell(remarks_link, '', 'cell actions_column'));
                    
                    var actions = '';
                    if (employed_ons[i].childNodes.length > 0) {
                        actions = 'Employed on ' + employed_ons[i].childNodes[0].nodeValue;
                    } else if (!is_agreed_terms) {
                        actions = 'Resume not viewed yet.';
                    } else {
                        // TODO: get the referrals.rating
                        var stars = 0;
                        
                        actions = '<span id="referral_' + referral_ids[i].childNodes[0].nodeValue + '">'
                        actions = actions + get_display_stars_for(referral_ids[i].childNodes[0].nodeValue, stars);
                        actions = actions + '</span>';
                        actions = actions + '<br/>';
                        actions = actions + '<a class="no_link" onClick="show_employment_popup(' + referral_ids[i].childNodes[0].nodeValue + ', ' + i + ');">Hired</a>';
                        actions = actions + '&nbsp;|&nbsp;';
                        actions = actions + '<a class="no_link" onClick="show_notify_popup(' + referral_ids[i].childNodes[0].nodeValue + ', ' + i + ');">Notify</a>';
                    }
                    row.set(4, new Cell(actions, '', 'cell actions_column'));
                    resumes_table.set((parseInt(i)+1), row);
                }
                
                $('resumes_list').set('html', resumes_table.get_html());
            }
        }, 
        onRequest: function(instance) {
            set_status('Loading...');
        }
    });
    
    request.send(params);
}

function show_referred_jobs() {
    $('div_referred_jobs').setStyle('display', 'block');
    $('div_resumes').setStyle('display', 'none');
    
    var params = 'id=' + id + '&action=get_referred_jobs';
    params = params + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employers/resumes_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == 'ko') {
                alert('An error occurred while loading applied jobs.');
                return false;
            }
            
            if (txt == '0') {
                $('div_referred_jobs').set('html', '<div class="empty_results">No applications found for all job posts at this moment.</div>');
            } else {
                var job_ids = xml.getElementsByTagName('id');
                var industries = xml.getElementsByTagName('industry');
                var titles = xml.getElementsByTagName('title');
                var expire_ons = xml.getElementsByTagName('formatted_expire_on');
                var referrals = xml.getElementsByTagName('num_referrals');
                var new_referrals = xml.getElementsByTagName('new_referrals_count');
                var descriptions = xml.getElementsByTagName('description');
                
                job_titles = new Array();
                var jobs_table = new FlexTable('referred_jobs_table', 'referred_jobs');
                
                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'industries.industry');\">Specialization</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'jobs.title');\">Job</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'jobs.expire_on');\">Expires On</a>", '', 'header'));
                header.set(3, new Cell("<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'num_referrals');\">Resumes</a>", '', 'header'));
                jobs_table.set(0, header);
                
                for (var i=0; i < job_ids.length; i++) {
                    job_titles[i] = titles[i].childNodes[0].nodeValue;
                    
                    var row = new Row('');
                    row.set(0, new Cell(industries[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var job_title = "<a class=\"no_link\" onClick=\"toggle_job_description('" + i + "');\">" + titles[i].childNodes[0].nodeValue + "</a>";
                    job_title = job_title + "<div id=\"inline_job_desc_" + i + "\" class=\"inline_job_desc\">" + descriptions[i].childNodes[0].nodeValue + "</div>";
                    row.set(1, new Cell(job_title, '', 'cell'));
                    
                    row.set(2, new Cell(expire_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var referral = "<a class=\"no_link\" onClick=\"show_resumes_of('" + job_ids[i].childNodes[0].nodeValue + "', '" + add_slashes(titles[i].childNodes[0].nodeValue) + "');\">" + referrals[i].childNodes[0].nodeValue;
                    if (parseInt(new_referrals[i].childNodes[0].nodeValue) > 0) {
                        referral = referral + "&nbsp;<span style=\"vertical-align: top; font-size: 7pt;\">[ " + new_referrals[i].childNodes[0].nodeValue + " new ]</span>"
                    }
                    referral = referral + "</a>";
                    row.set(3, new Cell(referral, '', 'cell resumes_column'));
                    jobs_table.set((parseInt(i)+1), row);
                }
                
                $('div_referred_jobs').set('html', jobs_table.get_html());
            }
        }, 
        onRequest: function(instance) {
            set_status('Loading...');
        }
    });
    
    request.send(params);
}

function show_resume_page(_resume_id) {
    var popup = window.open('resume.php?id=' + _resume_id, '', 'scrollbars');
    if (!popup) {
        alert('Popup blocker was detected. Please allow pop-up windows for YellowElevator.com and try again.');
    }
}

function agree_terms(_referral_id, _resume_id, _candidate_idx) {
    var params = 'id=' + _referral_id + '&action=agreed_terms';
    
    var uri = root + "/employers/resumes_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while trying to confirm that you agreed on the resume viewing terms.\n\nPlease try again.');
                return false;
            }
            
            show_resume_page();
            
            var html = $('candidate_' + _candidate_idx).get('html');
            html = html + '<div class="mini_contacts">Tel: ' + candidates[_candidate_idx].phone_num + '<br/>Email: <a href="mailto: ' + candidates[_candidate_idx].email_addr + '">' + candidates[_candidate_idx].email_addr + '</a></div>';
            $('candidate_' + _candidate_idx).set('html', html);
        }
    });
    
    request.send(params);
}

function download_resume(_referral_id, _resume_id, _candidate_idx, _is_agreed_terms) {
    if (!_is_agreed_terms) {
        var agree = confirm('Please confirm that you wish to view the whole resume.\n\nClick "OK" to confirm or "Cancel" to decline.');
        
        if (agree) {
            agree_terms(_referral_id, _resume_id, _candidate_idx);
        }
    } else {
        show_resume_page();
    }
}

function close_job_description_popup() {
    close_window('job_description_window');
}

function show_job_description_popup() {
    $('window_job_title').set('html', current_job_title);
    
    var params = 'id=' + current_job_id + '&action=get_job_description';
    var uri = root + "/employers/resumes_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            var descriptions = xml.getElementsByTagName('description');
            
            $('window_description').set('html', descriptions[0].childNodes[0].nodeValue);
            
            show_window('job_description_window');
        }
    });
    
    request.send(params);
}

function close_testimony_popup() {
    close_window('testimony_window');
}

function show_testimony_popup(_candidate_idx) {
    $('window_testimony_candidate').set('html', 'Testimony for' + candidates[_candidate_idx].name);
    $('window_testimony').set('html', candidates[_candidate_idx].testimony);
    
    show_window('testimony_window');
}

function close_remarks_popup(_needs_saving) {
    if (_needs_saving) {
        if (isEmpty($('txt_remarks').value)) {
            close_window('remarks_window');
            return;
        }
        
        var params = 'id=' + $('remarks_referral_id').value + '&action=save_remarks';
        params = params + '&remarks=' + encodeURIComponent($('txt_remarks').value);

        var uri = root + "/employers/resumes_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    alert('Unable to save remarks. Please try again later.');
                    return;
                }
            }
        });

        request.send(params);
    }
    
    close_window('remarks_window');
    candidates[$('candidate_idx').value].employer_remarks = $('txt_remarks').value;
}

function show_remarks_popup(_referral_id, _candidate_idx) {
    $('remarks_referral_id').value = _referral_id;
    $('remarks_candidate_idx').value = _candidate_idx;
    $('window_remarks_candidate').set('html', 'Remarks saved for ' + candidates[_candidate_idx].name);
    $('txt_remarks').value = candidates[_candidate_idx].employer_remarks;
    
    show_window('remarks_window');
}

function close_employment_popup(_to_confirm) {
    if (_to_confirm) {
        var is_confirmed = confirm('Are you sure all the employment details provided are correct?');
        if (!is_confirmed) {
            return;
        }
        
        if (is_year_changed) {
            var proceed = confirm('Are you sure to proceed for an employment made in ' + $('year_label').get('html') + '?');
            if (!proceed) {
                return;
            }
        }
        
        var params = 'id=' + $('employment_referral_id').value + '&action=confirm_employed';
        params = params + '&employer=' + id;
        params = params + '&job=' + current_job_title;
        params = params + '&job_id=' + current_job_id;
        params = params + '&candidate_email_addr=' + candidates[$('employment_candidate_idx').value].email_addr;
        params = params + '&candidate_name=' + candidates[$('employment_candidate_idx').value].name;
        params = params + '&work_commence_on=' + $('year_label').get('html') + '-' + $('month').options[$('month').selectedIndex].value + '-' + $('day').options[$('day').selectedIndex].value;
        params = params + '&salary=' + $('salary').value;
        
        var uri = root + "/employers/resumes_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                show_resumes_of(current_job_id, current_job_title);
            }
        });

        request.send(params);
    }
    
    close_window('employment_window');
}

function show_employment_popup(_referral_id, _candidate_idx) {
    $('employment_referral_id').value = _referral_id;
    $('employment_candidate_idx').value = _candidate_idx;
    $('window_employment_title').set('html', 'Employment Confirmation of ' + candidates[_candidate_idx].name + ' for ' + current_job_title);
    
    show_window('employment_window');
}

function close_notify_popup(_needs_sending) {
    if (_needs_sending) {
        var is_good = '1';
        if ($('bad').checked) {
            is_good = '0';
        }
        
        var params = 'id=' + $('notify_referral_id').value + '&action=notify_candidate';
        params = params + '&is_good=' + is_good;
        params = params + '&job=' + current_job_title;
        params = params + '&job_id=' + current_job_id;
        params = params + '&candidate_email_addr=' + candidates[$('notify_candidate_idx').value].email_addr;
        params = params + '&candidate_name=' + candidates[$('notify_candidate_idx').value].name;
        params = params + '&message=' + encodeURIComponent($('txt_message').value);

        var uri = root + "/employers/resumes_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                alert('Notification send.');
            }
        });

        request.send(params);
    }
    
    close_window('notify_window');
}

function show_notify_popup(_referral_id, _candidate_idx) {
    $('notify_referral_id').value = _referral_id;
    $('notify_candidate_idx').value = _candidate_idx;
    $('window_notify_candidate').set('html', 'A message to ' + candidates[_candidate_idx].name + ' about ' + current_job_title);
    show_window('notify_window');
}

function toggle_job_description(_idx) {
    if ($('inline_job_desc_' + _idx).getStyle('display') == 'none') {
        $('inline_job_desc_' + _idx).setStyle('display', 'block');
    } else {
        $('inline_job_desc_' + _idx).setStyle('display', 'none');
    }
}

function update_year() {
    var today = new Date();
    var month = parseInt(today.get('month')) + 1;
    var day = today.get('date');
    var selected_month = $('month').options[$('month').selectedIndex].value;
    var selected_day = $('day').options[$('day').selectedIndex].value;
    
    if (selected_month.substr(0, 1) == '0') {
        selected_month = parseInt(selected_month.substr(1));
    } else {
        selected_month = parseInt(selected_month);
    }
    
    if (selected_day.substr(0, 1) == '0') {
        selected_day = parseInt(selected_day.substr(1));
    } else {
        selected_day = parseInt(selected_day);
    }
    
    if (selected_month > month || 
        selected_month == month && selected_day > day) {
        $('year_label').set('html', (today.get('year') - 1));
        is_year_changed = true;
    } else {
        $('year_label').set('html', today.get('year'));
        is_year_changed = false;
    }
    
    if (!today.isLeapYear() && selected_month == 2 &&
        selected_day > 28) {
        $('day').selectedIndex = 29;
    } else if (selected_month == 2 && selected_day > 29) {
        $('day').selectedIndex = 30;
    } else {
        switch (selected_month) {
            case 4:
            case 6:
            case 9:
            case 11:
                if (selected_day > 30) {
                    $('day').selectedIndex = 31;
                }
                break;
        }
    }
}

function onDomReady() {
    set_root();
    
    $('month').addEvent('change', update_year);
    $('day').addEvent('change', update_year);
}

window.addEvent('domready', onDomReady);