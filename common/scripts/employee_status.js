var order = 'desc';
var order_by = 'referrals.referred_on';
var filter = '';
var current_page = 0;

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function sort_by(_table, _column) {
    switch (_table) {
        case 'referrals':
            order_by = _column;
            ascending_or_descending();
            update_applications();
            break;
    }
}

function filter_applications() {
    filter = $('filter').options[$('filter').selectedIndex].value;
    
    var from_year = $('from_year').value;
    var to_year = $('to_year').value;
    if (isNaN(from_year) || isNaN(to_year)) {
        alert('The year fields must be a number.');
        return;
    }
    
    if (isEmpty($('from_month').options[$('from_month').selectedIndex].value) || 
        isEmpty($('to_month').options[$('to_month').selectedIndex].value)) {
        alert('You need to select a month.');
        return;
    }
    
    period = from_year + '-' + $('from_month').options[$('from_month').selectedIndex].value + '-' + $('from_day').options[$('from_day').selectedIndex].value;
    period = period + ';' + to_year + '-' + $('to_month').options[$('to_month').selectedIndex].value + '-' + $('to_day').options[$('to_day').selectedIndex].value;
    
    current_page = 0;
    update_applications();
}

function goto_page() {
    current_page = $('current_page').options[$('current_page').selectedIndex].value;
    if (arguments.length > 0) {
        current_page = $('current_page_bottom').options[$('current_page_bottom').selectedIndex].value;
    }
    
    update_applications();
}

function update_applications() {
    var params = 'id=' + id;
    params = params + '&action=get_applications';
    params = params + '&order_by=' + order_by + ' ' + order;
    params = params + '&filter=' + filter;
    params = params + '&page=' + current_page;
    params = params + '&period=' + period;
    
    var uri = root + "/employees/status_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            //set_status('<pre>' + txt + '</pre>');
            //return;
            set_status('');
            if (txt == 'ko') {
                alert('An error occured while getting applications.');
                return false;
            }
            
            if (txt == '0') {
                $('applications').set('html', '<div class="empty_results">No applications found.</div>');
                return;
            } else {
                var total_pages = xml.getElementsByTagName('total_pages');
                $('total_pages').set('html', total_pages[0].childNodes[0].nodeValue);
                $('total_pages_bottom').set('html', total_pages[0].childNodes[0].nodeValue);
                
                $('current_page').options.length = 0;
                for (var i=0; i < total_pages[0].childNodes[0].nodeValue; i++) {
                    var page = ''; 
                    if (i == current_page) {
                        page = new Option((i+1), i, true, true);
                    } else {
                        page = new Option((i+1), i);
                    }
                    
                    $('current_page').add(page);
                }
                
                $('current_page_bottom').options.length = 0;
                for (var i=0; i < total_pages[0].childNodes[0].nodeValue; i++) {
                    var page = ''; 
                    if (i == current_page) {
                        page = new Option((i+1), i, true, true);
                    } else {
                        page = new Option((i+1), i);
                    }
                    
                    $('current_page_bottom').add(page);
                }
                
                var ids = xml.getElementsByTagName('id');
                var jobs = xml.getElementsByTagName('job');
                var job_ids = xml.getElementsByTagName('job_id');
                var employers = xml.getElementsByTagName('employer');
                var employer_ids = xml.getElementsByTagName('employer_id');
                var referrer_names = xml.getElementsByTagName('referrer_name');
                var referrers = xml.getElementsByTagName('referrer');
                var candidate_names = xml.getElementsByTagName('candidate_name');
                var candidates = xml.getElementsByTagName('candidate');
                var referred_ons = xml.getElementsByTagName('formatted_referred_on');
                var viewed_ons = xml.getElementsByTagName('formatted_employer_agreed_terms_on');
                var employed_ons = xml.getElementsByTagName('formatted_employed_on');
                var rejected_ons = xml.getElementsByTagName('formatted_employer_rejected_on');
                var deleted_ons = xml.getElementsByTagName('formatted_employer_removed_on');
                var has_testimonies = xml.getElementsByTagName('has_testimony');
                var has_remarks = xml.getElementsByTagName('has_employer_remarks');
                var resume_ids = xml.getElementsByTagName('resume_id');
                var resume_files = xml.getElementsByTagName('file_name');
                
                var applications_table = new FlexTable('applications_table', 'applications');
                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'employers.name');\">Employers</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'jobs.title');\">Job</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'referrers.lastname');\">Referrer</a>", '', 'header'));
                header.set(3, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'candidates.lastname');\">Candidate</a>", '', 'header'));
                header.set(4, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'referrals.referred_on');\">Applied On</a>", '', 'header'));
                header.set(5, new Cell("Status", '', 'header'));
                header.set(6, new Cell("Testimony", '', 'header'));
                applications_table.set(0, header);
                
                for (var i=0; i < ids.length; i++) {
                    var row = new Row('');
                    row.set(0, new Cell('<a href="employer.php?id=' + employer_ids[i].childNodes[0].nodeValue + '">' + employers[i].childNodes[0].nodeValue + '</a>', '', 'cell'));
                    row.set(1, new Cell('<a class="no_link" onClick="show_job_desc(' + job_ids[i].childNodes[0].nodeValue + ');">' + jobs[i].childNodes[0].nodeValue + '</a>', '', 'cell'));
                    row.set(2, new Cell('<a href="member.php?member_email_addr=' + add_slashes(referrers[i].childNodes[0].nodeValue) + '">' + referrer_names[i].childNodes[0].nodeValue + '</a>', '', 'cell'));
                    row.set(3, new Cell('<a href="member.php?member_email_addr=' + add_slashes(candidates[i].childNodes[0].nodeValue) + '">' + candidate_names[i].childNodes[0].nodeValue + '</a><div class="resume"><span style="font-weight: bold;">Resume:</span> <a href="resume.php?id=' + resume_ids[i].childNodes[0].nodeValue + '">' + resume_files[i].childNodes[0].nodeValue + '</a></div>', '', 'cell'));
                    row.set(4, new Cell(referred_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var status = '<span class="not_viewed_yet">Not Viewed Yet</span>';
                    if (viewed_ons[i].childNodes.length > 0) {
                        status = '<span class="viewed">Viewed On:</span> ' + viewed_ons[i].childNodes[0].nodeValue;
                    }
                    
                    if (employed_ons[i].childNodes.length > 0) {
                        status = '<span class="employed">Employed On:</span> ' + employed_ons[i].childNodes[0].nodeValue;
                    }
                    
                    if (rejected_ons[i].childNodes.length > 0) {
                        status = '<span class="rejected">Rejected On:</span> ' + viewed_ons[i].childNodes[0].nodeValue;
                    }
                    
                    if (deleted_ons[i].childNodes.length > 0) {
                        status = '<span class="deleted">Deleted On:</span> ' + viewed_ons[i].childNodes[0].nodeValue;
                    }
                    
                    if (has_remarks[i].childNodes[0].nodeValue == '1') {
                        status = status + '<br/><a class="no_link" onClick="show_employer_remarks(' + ids[i].childNodes[0].nodeValue + ');">Employer Remarks</a>';
                    }
                    row.set(5, new Cell(status, '', 'cell testimony'));
                    
                    var testimony = 'None Provided';
                    if (has_testimonies[i].childNodes[0].nodeValue == '1') {
                        testimony = '<a class="no_link" onClick="show_testimony(' + ids[i].childNodes[0].nodeValue + ');">Show</a>';
                    }
                    row.set(6, new Cell(testimony, '', 'cell testimony'));
                    
                    applications_table.set((parseInt(i)+1), row);
                }
                
                $('applications').set('html', applications_table.get_html());
            }
        },
        onRequest: function(instance) {
            set_status('Loading applications...');
        }
    });
    
    request.send(params);
}

function show_testimony(_referral_id) {
    var params = 'id=' + _referral_id;
    params = params + '&action=get_testimony';
    
    var uri = root + "/employees/member_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (isEmpty(txt)) {
                alert('Testimony not found!');
                return;
            }
            
            $('testimony').set('html', txt);
            set_status('');
            show_window('testimony_window');
            window.scrollTo(0, 0);
        },
        onRequest: function(instance) {
            set_status('Loading testimony...');
        }
    });
    
    request.send(params);
}

function close_testimony() {
    close_window('testimony_window');
}

function show_job_desc(_job_id) {
    var params = 'id=' + _job_id;
    params = params + '&action=get_job_desc';
    
    var uri = root + "/employees/member_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (isEmpty(txt)) {
                alert('Job not found!');
                return;
            }
            
            $('job_desc').set('html', txt);
            set_status('');
            show_window('job_desc_window');
            window.scrollTo(0, 0);
        },
        onRequest: function(instance) {
            set_status('Loading job desccription...');
        }
    });
    
    request.send(params);
}

function close_job_desc() {
    close_window('job_desc_window');
}

function show_employer_remarks(_referral_id) {
    var params = 'id=' + _referral_id;
    params = params + '&action=get_employer_remarks';
    
    var uri = root + "/employees/member_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (isEmpty(txt)) {
                alert('Remarks not found!');
                return;
            }
            
            $('employer_remarks').set('html', txt);
            set_status('');
            show_window('employer_remarks_window');
            window.scrollTo(0, 0);
        },
        onRequest: function(instance) {
            set_status('Loading employer remarks...');
        }
    });
    
    request.send(params);
}

function close_employer_remarks() {
    close_window('employer_remarks_window');
}

function onDomReady() {
    initialize_page();
}

window.addEvent('domready', onDomReady);
