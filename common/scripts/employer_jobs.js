var order_by = 'created_on';
var order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function sort_by(_table, _column) {
    switch (_table) {
        case 'jobs':
            order_by = _column;
            ascending_or_descending();
            show_jobs();
            break;
    }
}

function show_job_description(_job_id) {
    $('div_jobs').setStyle('display', 'none');
    $('div_job_desc').setStyle('display', 'block');
    
    var params = 'id=' + _job_id + '&action=get_job_desc';
    
    var uri = root + "/employers/jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == 'ko') {
                alert('An error occured while loading job.');
                return false;
            } 
            
            var title = xml.getElementsByTagName('title');
            var industry = xml.getElementsByTagName('industry');
            var state = xml.getElementsByTagName('state');
            var salary = xml.getElementsByTagName('salary');
            var salary_end = xml.getElementsByTagName('salary_end');
            var salary_negotiable = xml.getElementsByTagName('salary_negotiable');
            var description = xml.getElementsByTagName('description');
            var created_on = xml.getElementsByTagName('formatted_created_on');
            var expire_on = xml.getElementsByTagName('formatted_expire_on');
            var contact_ccs = xml.getElementsByTagName('contact_carbon_copy');
            var alternate_employer = xml.getElementsByTagName('alternate_employer');
            
            $('job.title').set('html', title[0].childNodes[0].nodeValue);
            
            var carbon_copy = '<span style="color: #666666; text-style: italic;">None Provided</span>';
            if (contact_ccs[0].childNodes.length > 0) {
                carbon_copy = contact_ccs[0].childNodes[0].nodeValue;
            }
            $('job.contact_carbon_copy').set('html', carbon_copy);
            
            var alt_employer = '<span style="color: #666666; text-style: italic;">Actual Employer Viewable to All</span>';
            if (alternate_employer[0].childNodes.length > 0) {
                alt_employer = alternate_employer[0].childNodes[0].nodeValue;
            }
            $('job.alternate_employer').set('html', alt_employer);
            
            $('job.specialization').set('html', industry[0].childNodes[0].nodeValue);
            
            var state_name = '<span style="color: #666666; text-style: italic;">None Provided</span>';
            if (state[0].childNodes.length > 0) {
                state_name = state[0].childNodes[0].nodeValue;
            }
            $('job.state').set('html', state_name);
            
            var salary_range = salary[0].childNodes[0].nodeValue;
            if (salary_end[0].childNodes.length > 0) {
                salary_range = salary_range + ' - ' + salary_end[0].childNodes[0].nodeValue;
            }
            $('job.salary_range').set('html', salary_range);
            
            if (salary_negotiable[0].childNodes[0].nodeValue == 'Y') {
                $('job.salary_negotiable').set('html', 'Negotiable');
            } else {
                $('job.salary_negotiable').set('html', 'Not Negotiable');
            }
            
            $('job.description').set('html', description[0].childNodes[0].nodeValue);
            $('job.created_on').set('html', created_on[0].childNodes[0].nodeValue);
            $('job.expired_on').set('html', expire_on[0].childNodes[0].nodeValue);
            
            set_status('');
            // window.scrollTo(0, 250);
        },
        onRequest: function(instance) {
            set_status('Loading job...');
        }
    });
    
    request.send(params);
}


function show_jobs() {
    $('div_jobs').setStyle('display', 'block');
    $('div_job_desc').setStyle('display', 'none');
    
    var params = 'id=' + id + '&action=get_jobs';
    params = params + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employers/jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == 'ko') {
                alert('An error occured while loading jobs.');
                return false;
            }
            
            var ids = xml.getElementsByTagName('id');
            var titles = xml.getElementsByTagName('title');
            var created_ons = xml.getElementsByTagName('formatted_created_on');
            var expire_ons = xml.getElementsByTagName('formatted_expire_on');
            
            var jobs_table = new FlexTable('jobs_table', 'jobs');
            
            var header = new Row('');
            header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('jobs', 'created_on');\">Created On</a>", '', 'header'));
            header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('jobs', 'title');\">Job</a>", '', 'header'));
            header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('jobs', 'expire_on');\">Expires On</a>", '', 'header'));
            jobs_table.set(0, header);
            
            for (var i=0; i < ids.length; i++) {
                var row = new Row('');
                row.set(0, new Cell(created_ons[i].childNodes[0].nodeValue, '', 'cell'));
                row.set(1, new Cell('<a class="no_link" onClick="show_job_description(' + ids[i].childNodes[0].nodeValue + ');">' + titles[i].childNodes[0].nodeValue + '</a>', '', 'cell'));
                row.set(2, new Cell(expire_ons[i].childNodes[0].nodeValue, '', 'cell'));
                jobs_table.set((parseInt(i)+1), row);
            }
            
            $('div_jobs').set('html', jobs_table.get_html());
        },
        onRequest: function(instance) {
            set_status('Loading currently opened jobs...');
        }
    });
    
    request.send(params);
}

function onDomReady() {
    initialize_page();
}

window.addEvent('domready', onDomReady);
