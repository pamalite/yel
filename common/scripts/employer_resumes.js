var order_by = 'num_referrals';
var order = 'desc';
var resumes_order_by = 'referrals.referred_on';
var resumes_order = 'desc';

var current_job_id = 0;

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
            show_resumes_of(current_job_id);
            break;
    }
}

function show_resumes_of(_job_id) {
    $('div_referred_jobs').setStyle('display', 'none');
    $('div_resumes').setStyle('display', 'block');
    
    current_job_id = _job_id;
    
    var params = 'id=' + _job_id + '&action=get_resumes';
    params = params + '&order_by=' + resumes_order_by + ' ' + resumes_order;
    
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
                var job_ids = xml.getElementsByTagName('id');
                var industries = xml.getElementsByTagName('industry');
                var titles = xml.getElementsByTagName('title');
                var expire_ons = xml.getElementsByTagName('formatted_expire_on');
                var referrals = xml.getElementsByTagName('num_referrals');
                var new_referrals = xml.getElementsByTagName('new_referrals_count');
                var descriptions = xml.getElementsByTagName('description');
                
                var jobs_table = new FlexTable('referred_jobs_table', 'referred_jobs');
                
                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'industries.industry');\">Specialization</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'jobs.title');\">Job</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'jobs.expire_on');\">Expires On</a>", '', 'header'));
                header.set(3, new Cell("<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'num_referrals');\">Resumes</a>", '', 'header'));
                jobs_table.set(0, header);
                
                for (var i=0; i < job_ids.length; i++) {
                    var row = new Row('');
                    row.set(0, new Cell(industries[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var job_title = "<a class=\"no_link\" onClick=\"toggle_job_description('" + i + "');\">" + titles[i].childNodes[0].nodeValue + "</a>";
                    job_title = job_title + "<div id=\"inline_job_desc_" + i + "\" class=\"inline_job_desc\">" + descriptions[i].childNodes[0].nodeValue + "</div>";
                    row.set(1, new Cell(job_title, '', 'cell'));
                    
                    row.set(2, new Cell(expire_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var referral = "<a class=\"no_link\" onClick=\"show_resumes_of('" + job_ids[i].childNodes[0].nodeValue + "');\">" + referrals[i].childNodes[0].nodeValue;
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
                
                var jobs_table = new FlexTable('referred_jobs_table', 'referred_jobs');
                
                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'industries.industry');\">Specialization</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'jobs.title');\">Job</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'jobs.expire_on');\">Expires On</a>", '', 'header'));
                header.set(3, new Cell("<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'num_referrals');\">Resumes</a>", '', 'header'));
                jobs_table.set(0, header);
                
                for (var i=0; i < job_ids.length; i++) {
                    var row = new Row('');
                    row.set(0, new Cell(industries[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var job_title = "<a class=\"no_link\" onClick=\"toggle_job_description('" + i + "');\">" + titles[i].childNodes[0].nodeValue + "</a>";
                    job_title = job_title + "<div id=\"inline_job_desc_" + i + "\" class=\"inline_job_desc\">" + descriptions[i].childNodes[0].nodeValue + "</div>";
                    row.set(1, new Cell(job_title, '', 'cell'));
                    
                    row.set(2, new Cell(expire_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var referral = "<a class=\"no_link\" onClick=\"show_resumes_of('" + job_ids[i].childNodes[0].nodeValue + "');\">" + referrals[i].childNodes[0].nodeValue;
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

function toggle_job_description(_idx) {
    if ($('inline_job_desc_' + _idx).getStyle('display') == 'none') {
        $('inline_job_desc_' + _idx).setStyle('display', 'block');
    } else {
        $('inline_job_desc_' + _idx).setStyle('display', 'none');
    }
}

function onDomReady() {
    set_root();
}

window.addEvent('domready', onDomReady);