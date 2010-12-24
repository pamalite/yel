var order_by = 'applied_on';
var order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function sort_by(_table, _column) {
    order_by = _column;
    ascending_or_descending();
    show_applications();
}

function confirm_employment(_referral_id, _employer, _job) {
    var is_ok = confirm('By clicking \'OK\', you confirm that you have been employed by ' + _employer + ' for the ' + _job + ' position. Would you like to proceed?');
    if (!is_ok) {
        return false;
    }
    
    var params = 'id=' + _referral_id + '&action=confirm_employment';
    
    var uri = root + "/members/job_applications_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == 'ko') {
                alert('An error occured while confirming job application.');
                return false;
            }
            
            show_applications();
        },
        onRequest: function(instance) {
            set_status('Confirming job application...');
        }
    });
    
    request.send(params);
}

function show_applications() {
    var params = 'id=' + id + '&action=get_applications';
    params = params + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/members/job_applications_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == 'ko') {
                alert('An error occured while loading job applications.');
                return false;
            }
            
            if (txt == '0') {
               $('div_applications').set('html', '<div class="empty_results">No jobs applied.</div>');
            } else {
                var tabs = xml.getElementsByTagName('tab');
                var ids = xml.getElementsByTagName('id');
                var job_ids = xml.getElementsByTagName('job_id');
                var resume_ids = xml.getElementsByTagName('resume_id');
                var resumes = xml.getElementsByTagName('resume');
                var jobs = xml.getElementsByTagName('job');
                var employers = xml.getElementsByTagName('employer');
                var referred_ons = xml.getElementsByTagName('formatted_referred_on');
                var employed_ons = xml.getElementsByTagName('formatted_employed_on');
                var statuses = xml.getElementsByTagName('status');
                var confirmed_hired_ons = xml.getElementsByTagName('formatted_confirmed_on');
                
                var applications_table = new FlexTable('applications_table', 'applications');
                
                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'applied_on');\">Applied On</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'job');\">Job</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'employer');\">Employer</a>", '', 'header'));
                header.set(3, new Cell("Resume Submitted", '', 'header'));
                header.set(4, new Cell("&nbsp;", '', 'header actions'));
                applications_table.set(0, header);
                
                applications = new Array();
                for (var i=0; i < ids.length; i++) {
                    var row = new Row('');
                    
                    row.set(0, new Cell(referred_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    row.set(1, new Cell('<a href="../job/' + job_ids[i].childNodes[0].nodeValue + '">' + jobs[i].childNodes[0].nodeValue + '</a>', '', 'cell'));
                    row.set(2, new Cell(employers[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var resume_submitted = '';
                    if (resumes[i].childNodes.length > 0) {
                        resume_submitted = resumes[i].childNodes[0].nodeValue;
                    }
                    row.set(3, new Cell(resume_submitted, '', 'cell'));
                    
                    var button = 'Processing...';
                    if (tabs[i].childNodes[0].nodeValue == 'ref') {
                        button = '<input type="button" value="Confirm Employed" onClick="confirm_employment(' + ids[i].childNodes[0].nodeValue + ', \'' + add_slashes(employers[i].childNodes[0].nodeValue) + '\', \'' + add_slashes(jobs[i].childNodes[0].nodeValue) + '\')" />';
                        if (confirmed_hired_ons[i].childNodes.length > 0) {
                            button = '<span style="color: #666666; font-size: 9pt;">Employed on ' + employed_ons[i].childNodes[0].nodeValue + '<br/>Confirmed on ' + confirmed_hired_ons[i].childNodes[0].nodeValue + '</span>';
                        }
                    }
                    
                    row.set(4, new Cell(button, '', 'cell actions'));
                    
                    applications_table.set((parseInt(i)+1), row);
                }
                
                $('div_applications').set('html', applications_table.get_html());
            }
        },
        onRequest: function(instance) {
            set_status('Loading currently job applications...');
        }
    });
    
    request.send(params);
}

function onDomReady() {
    initialize_page();
}

window.addEvent('domready', onDomReady);
