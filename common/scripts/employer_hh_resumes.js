var order_by = 'num_referrals';
var order = 'desc';
var resumes_order_by = 'headhunter_referrals.referred_on';
var resumes_order = 'desc';

var current_job_id = 0;
var current_job_title = '';
var is_year_changed = false;

function Referral() {
    this.cover_note = '';
}
var resume_referrals = new Array();

function rate_stars_for(_referral_id, _stars) {
    var params = 'id=' + _referral_id + '&action=rate_candidate';
    params = params + '&rating=' + _stars;
    
    var uri = root + "/employers/hh_resumes_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            //set_status('<pre>' + txt + '</pre>');
            //return;
            if (txt == 'ko') {
                alert('An error rating candidate.');
                return false;
            }
            
            var html = get_display_stars_for(_referral_id, _stars);
            $('referral_' + _referral_id).set('html', html);
        }
    });
    
    request.send(params);
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
    
    var uri = root + "/employers/hh_resumes_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            // set_status('<pre>' + txt + '</pre>');
            // return;
            set_status('');
            if (txt == 'ko') {
                alert('An error occurred while loading resumes.');
                return false;
            }
            
            if (txt == '0') {
                alert('No resumess found for the selected job posts at this moment.');
                $('filter').selectedIndex = 0;
                return;
            } else {
                var referral_ids = xml.getElementsByTagName('id');
                var resume_files = xml.getElementsByTagName('resume_file_name');
                var resume_hashes = xml.getElementsByTagName('resume_file_hash');
                var referred_ons = xml.getElementsByTagName('formatted_referred_on');
                var employed_ons = xml.getElementsByTagName('formatted_employed_on');
                var employer_agreed_ons = xml.getElementsByTagName('formatted_agreed_on');
                var scheduled_ons = xml.getElementsByTagName('formatted_scheduled_on');
                var employer_rejected_ons = xml.getElementsByTagName('formatted_rejected_on');
                var cover_notes = xml.getElementsByTagName('cover_note');
                var ratings = xml.getElementsByTagName('rating');
                
                var resumes_table = new FlexTable('resumes_table', 'resumes');
                
                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('applications', 'headhunter_referrals.referred_on');\">Received On</a>", '', 'header'));
                header.set(1, new Cell("Resume File Submitted", '', 'header'));
                header.set(2, new Cell("Cover Note", '', 'header'));
                header.set(3, new Cell('&nbsp;', '', 'header'));
                resumes_table.set(0, header);
                
                candidates = new Array();
                for (var i=0; i < referral_ids.length; i++) {
                    var row = new Row('');
                    row.set(0, new Cell(referred_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    // resume file submitted
                    var resume_file = '<a href="hh_resumes_action.php?id=' + referral_ids[i].childNodes[0].nodeValue + '&hash=' + resume_hashes[i].childNodes[0].nodeValue + '">' + resume_files[i].childNodes[0].nodeValue + '</a>';
                    row.set(1, new Cell(resume_file, '', 'cell'));
                    
                    // cover note
                    resume_referrals[i] = new Referral();
                    var cover_note = '<span style="font-style: italic; color: #CCCCCC;">None Provided</span>';
                    if (cover_notes[i].childNodes.length > 0) {
                        cover_note = '<a class="no_link" onClick="show_cover_note_popup(' + i + ');">View Cover Note</a>';
                        resume_referrals[i].cover_note = cover_notes[i].childNodes[0].nodeValue;
                    }
                    row.set(2, new Cell(cover_note, '', 'cell actions_column'));
                    
                    // action
                    var is_agreed = false;
                    var actions = '<input type="button" value="Accept Resume &amp; Schedule Interview" onClick="show_schedule_interview_popup(' + referral_ids[i].childNodes[0].nodeValue + ');" />';
                    actions = actions + '<br/><input type="button" value="Reject Resume" onClick="reject_resume(' + referral_ids[i].childNodes[0].nodeValue + ');" />'
                    if (employer_agreed_ons[i].childNodes.length >  0) {
                        is_agreed = true;
                        
                        actions = '<span style="font-weight: bold;">Employer Accepted On: </span>' + employer_agreed_ons[i].childNodes[0].nodeValue;
                        
                        if (scheduled_ons[i].childNodes.length > 0) {
                            actions = actions + '<br/><span style="font-weight: bold;">Interview Scheduled On: </span>' + scheduled_ons[i].childNodes[0].nodeValue;
                        }
                    }
                    
                    if (employed_ons[i].childNodes.length > 0) {
                        actions = actions + '<br/><span style="font-weight: bold;">Employed On: </span>' + employed_ons[i].childNodes[0].nodeValue;
                    } else {
                        if (is_agreed) {
                            actions = actions + '<br/><input type="button" value="Confirm Employed" onClick="show_employment_popup(' + referral_ids[i].childNodes[0].nodeValue + ');" />'
                        }
                    }
                    row.set(3, new Cell(actions, '', 'cell actions_column'));
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
    
    var uri = root + "/employers/hh_resumes_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == 'ko') {
                alert('An error occurred while loading recommended resumes.');
                return false;
            }
            
            if (txt == '0') {
                $('div_referred_jobs').set('html', '<div class="empty_results">No resumes found for all job posts at this moment.</div>');
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
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'jobs.expire_on');\">Expires On</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'jobs.title');\">Job</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'num_referrals');\">Resumes</a>", '', 'header'));
                jobs_table.set(0, header);
                
                for (var i=0; i < job_ids.length; i++) {
                    job_titles[i] = titles[i].childNodes[0].nodeValue;
                    
                    var row = new Row('');
                    
                    row.set(0, new Cell(expire_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var job_title = "<a class=\"no_link\" onClick=\"toggle_job_description('" + i + "');\">" + titles[i].childNodes[0].nodeValue + "</a>";
                    job_title = job_title + "<div id=\"inline_job_desc_" + i + "\" class=\"inline_job_desc\">" + descriptions[i].childNodes[0].nodeValue + "</div>";
                    row.set(1, new Cell(job_title, '', 'cell'));
                    
                    var referral = "<a class=\"no_link\" onClick=\"show_resumes_of('" + job_ids[i].childNodes[0].nodeValue + "', '" + add_slashes(titles[i].childNodes[0].nodeValue) + "');\">" + referrals[i].childNodes[0].nodeValue;
                    if (parseInt(new_referrals[i].childNodes[0].nodeValue) > 0) {
                        referral = referral + "&nbsp;<span style=\"vertical-align: top; font-size: 7pt;\">[ " + new_referrals[i].childNodes[0].nodeValue + " new ]</span>"
                    }
                    referral = referral + "</a>";
                    row.set(2, new Cell(referral, '', 'cell resumes_column'));
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

function close_cover_note_popup() {
    close_window('testimony_window');
}

function show_cover_note_popup(_idx) {
    $('referral_id').value = _idx;
    $('window_testimony').set('html', resume_referrals[_idx].cover_note);
    
    show_window('testimony_window');
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
        params = params + '&salary=' + $('salary').value;
        
        var uri = root + "/employers/hh_resumes_action.php";
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

function show_employment_popup(_referral_id) {
    $('employment_referral_id').value = _referral_id;
    $('window_employment_title').set('html', 'Employment Confirmation for ' + current_job_title);
    
    show_window('employment_window');
}

function close_schedule_interview_popup(_is_schedule) {
    if (_is_schedule) {
        if (isEmpty($('schedule_datetime').value)) {
            alert('Date & Time cannot be empty.');
            return false;
        }
        
        var params = 'id=' + $('schedule_employment_referral_id').value + '&action=schedule_interview';
        params = params + '&datetime=' + $('schedule_datetime').value;
        params = params + '&message=' + encodeURIComponent($('schedule_message').value);
        
        var uri = root + "/employers/hh_resumes_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    alert('An error occured while scheduling interview.');
                    return false;
                }
                
                show_resumes_of(current_job_id, current_job_title);
            }
        });

        request.send(params);
    }
    
    close_window('interview_schedule_window');
}

function show_schedule_interview_popup(_referral_id) {
    $('schedule_employment_referral_id').value = _referral_id;
    
    new DatePicker('.schedule_datetime', {
        timePicker: true,
        format: 'Y-m-d @ H:i',
        inputOutputFormat: 'Y-m-d H:i:00'
    });
    
    show_window('interview_schedule_window');
}

function reject_resume(_referral_id) {
    if (!confirm('Are you sure to reject this resume?')) {
        return;
    }
    
    var params = 'id=' + _referral_id + '&action=reject_resume';
    
    var uri = root + "/employers/hh_resumes_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            show_resumes_of(current_job_id, current_job_title);
        }
    });

    request.send(params);
}

function toggle_job_description(_idx) {
    if ($('inline_job_desc_' + _idx).getStyle('display') == 'none') {
        $('inline_job_desc_' + _idx).setStyle('display', 'block');
    } else {
        $('inline_job_desc_' + _idx).setStyle('display', 'none');
    }
}

function onDomReady() {
    initialize_page();
}

window.addEvent('domready', onDomReady);