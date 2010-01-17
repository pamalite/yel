var selected_tab = 'li_open';
var order_by = 'jobs.created_on';
var order = 'desc';
var employers_order_by = 'num_expired';
var employers_order = 'desc';

var selected_employer_id = '';
var selected_employer_currency = 'MYR';

// var editor = new Editor();

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function employers_ascending_or_descending() {
    if (employers_order == 'desc') {
        employers_order = 'asc';
    } else {
        employers_order = 'desc';
    }
}

function validate() {
    if ($('title').value == '') {
        set_status('Title cannot be empty.');
        return false;
    } 
    
    if ($('industry').value == '0') {
        set_status('You need to select an industry.');
        return false;
    }
    
    if ($('country').value == '0') {
        set_status('You need to select a country.');
        return false;
    }
    
    /*if ($('currency').value == '0') {
        set_status('You need to select a currency for the salary.');
        return false;
    }*/
    
    if ($('salary').value == '' || parseFloat($('salary').value) <= 0.00) {
        set_status('Monthly Pay field must be at least 1.00 for us to calculate the potential rewards.');
        return false;
    } else {
        if (!isNumeric($('salary').value)) {
            set_status('Monthly Pay must be in numeric numbers only.');
            return false;
        }
    }
    
    if (!isEmpty($('salary_end').value)) {
        if (!isNumeric($('salary_end').value)) {
            set_status('Monthly Pay range must be in numeric numbers only.');
            return false;
        } else {
            if (parseFloat($('salary_end').value) < parseFloat($('salary').value)) {
                set_status('Monthly Pay range is invalid.');
                return false;
            }
        }
    }
    
    /*if ($('description').value == '') {
        set_status('Description cannot be empty.');
        return false;
    }*/
    
    // if (isEmpty(editor.getValue())) {
    //     set_status('Description cannot be empty');
    //     return false;
    // }
    
    if (isEmpty(document.getElementById(rteFormName).value)) {
        set_status('Description cannot be empty');
        return false;        
    }
    
    set_status('');
    return true;
}

function show_employers() {
    $('div_jobs').setStyle('display', 'none');
    $('div_employers').setStyle('display', 'block');
    
    selected_employer_id = '';
    selected_employer_currency = '';
    
    var params = 'id=0';
    params = params + '&order_by=' + employers_order_by + ' ' + employers_order;
    
    var uri = root + "/employees/jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading employers.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">No employers created at the moment.</div>';
            } else {
                var ids = xml.getElementsByTagName('id');
                var employers = xml.getElementsByTagName('name');
                var expired_jobs = xml.getElementsByTagName('num_expired');
                var saved_jobs = xml.getElementsByTagName('num_saved');
                var open_jobs = xml.getElementsByTagName('num_open');
                var closed_jobs = xml.getElementsByTagName('num_closed');
                var total_jobs = xml.getElementsByTagName('total_jobs');
                
                for (i=0; i < ids.length; i++) {
                    var id = ids[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    
                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="count"><a class="no_link" onClick="show_open_jobs(\'' + id + '\');">' + open_jobs[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="count"><a class="no_link" onClick="show_closed_jobs(\'' + id + '\');">' + closed_jobs[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="count">' + expired_jobs[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="count">' + saved_jobs[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="count">' + total_jobs[i].childNodes[0].nodeValue + '</td>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_employers_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading employers...');
        }
    });
    
    request.send(params);
}

function save_job() {
    // Must do this because the FreeRTE do not have a getValue() method to update the value
    rteModeType('rte_preview_mode');
    rteModeType('rte_design_mode');
    
    if (!validate()) {
        return false;
    }
    
    var salary_negotiable = 'N';
    if ($('salary_negotiable').checked) {
        salary_negotiable = 'Y';
    }
    
    var params = 'id=' + $('job_id').value;
    params = params + '&action=save';
    params = params + '&employer=' + selected_employer_id;
    params = params + '&title=' + encodeURIComponent($('title').value);
    params = params + '&industry=' + $('industry').value;
    params = params + '&country=' + $('country').value;
    params = params + '&state=' + $('state').value;
    params = params + '&currency=' + $('currency').value;
    params = params + '&salary=' + $('salary').value;
    params = params + '&salary_end=' + $('salary_end').value;
    params = params + '&salary_negotiable=' + salary_negotiable;
    //params = params + '&description=' + encodeURIComponent($('description').value);
    // params = params + '&description=' + encodeURIComponent(editor.getValue());
    params = params + '&description=' + encodeURIComponent(document.getElementById(rteFormName).value);
    params = params + '&resume_type=' + $('acceptable_resume_type').options[$('acceptable_resume_type').selectedIndex].value;
    
    var uri = root + "/employees/jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ok') {
                set_status('New job successfully saved.');
            } else if (txt == '-1') {
                alert('The account is not set up yet');
                return false;
            } else {
                alert('Sorry! We are not able to save the job at the moment. Please try again later.');
                return false;
            }
            
            $('div_tabs').setStyle('display', 'block');
            show_open_jobs_with_selected_employer();
        },
        onRequest: function(instance) {
            set_status('Saving and updating...');
        }
    });
    
    request.send(params);
}

function publish_job() {
    // Must do this because the FreeRTE do not have a getValue() method to update the value
    rteModeType('rte_preview_mode');
    rteModeType('rte_design_mode');
    
    if (!validate()) {
        return false;
    }
    
    if (!confirm('You will not be able to make anymore amendments after clicking \'OK\'. Do you wish to proceed?')) {
        return false;
    }
    
    var salary_negotiable = 'N';
    if ($('salary_negotiable').checked) {
        salary_negotiable = 'Y';
    }
    
    var params = 'id=' + $('job_id').value;
    params = params + '&action=publish';
    params = params + '&employer=' + selected_employer_id;
    params = params + '&title=' + encodeURIComponent($('title').value);
    params = params + '&industry=' + $('industry').value;
    params = params + '&country=' + $('country').value;
    params = params + '&state=' + $('state').value;
    params = params + '&currency=' + $('currency').value;
    params = params + '&salary=' + $('salary').value;
    
    var salary_end = $('salary_end').value;
    if (salary_end <= 0 || isEmpty(salary_end)) {
        salary_end = 0;
    }
    params = params + '&salary_end=' + salary_end;
    params = params + '&salary_negotiable=' + salary_negotiable;
    //params = params + '&description=' + encodeURIComponent($('description').value);
    // params = params + '&description=' + encodeURIComponent(editor.getValue());
    params = params + '&description=' + encodeURIComponent($(rteFormName).value);
    params = params + '&resume_type=' + $('acceptable_resume_type').options[$('acceptable_resume_type').selectedIndex].value;
    
    var uri = root + "/employees/jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ok') {
                set_status('New job successfully published.');
            } else if (txt == '-1') {
                alert('The account is not set up yet');
                return false;
            } else if (txt == '-2') {
                alert('The employer\'s subscription is expired.');
                return false;
            } else {
                alert('Sorry! We are not able to publish the new job at the moment. Please try again later.');
                return false;
            }
            
            $('div_tabs').setStyle('display', 'block');
            show_open_jobs_with_selected_employer();
        },
        onRequest: function(instance) {
            set_status('Saving and updating...');
        }
    });
    
    request.send(params);
}

function show_update_job(job_id) {    
    var params = 'id=' + job_id + '&action=get_job';
    
    var uri = root + "/employees/jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading job.');
                return false;
            } 
            
            var industries = $('industry').options;
            var countries = $('country').options;
            var resume_types = $('acceptable_resume_type').options;
            //var currencies = $('currency').options;
            
            var title = xml.getElementsByTagName('title');
            var industry = xml.getElementsByTagName('industry');
            var country = xml.getElementsByTagName('country');
            var state = xml.getElementsByTagName('state');
            //var currency = xml.getElementsByTagName('currency');
            var salary = xml.getElementsByTagName('salary');
            var salary_end = xml.getElementsByTagName('salary_end');
            var salary_negotiable = xml.getElementsByTagName('salary_negotiable');
            var description = xml.getElementsByTagName('description');
            var acceptable_resume_types = xml.getElementsByTagName('acceptable_resume_type');
            
            $('title').value = title[0].childNodes[0].nodeValue;
            $('job.industry').set('html', industry[0].childNodes[0].nodeValue);
            $('job.country').set('html', country[0].childNodes[0].nodeValue);
            $('state').value = '';
            if (state[0].childNodes.length > 0) {
                $('state').value = state[0].childNodes[0].nodeValue;
            }
            //$('job.currency').set('html', currency[0].childNodes[0].nodeValue);
            $('salary').value = salary[0].childNodes[0].nodeValue;
            
            if (salary[0].childNodes.length > 0) {
                $('salary_end').value = salary_end[0].childNodes[0].nodeValue;
            } else {
                $('salary_end').value = '';
            }
            
            //$('description').value = description[0].childNodes[0].nodeValue.replace(/<br\/>/g, "\n");
            // editor.setValue(description[0].childNodes[0].nodeValue);
            startRTE(description[0].childNodes[0].nodeValue);
            
            if (salary_negotiable[0].childNodes[0].nodeValue == 'Y') {
                $('salary_negotiable').checked = true;
            } else {
                $('salary_negotiable').checked = false;
            }
            
            for (var i=0; i < industries.length; i++) {
                if (industries[i].value == industry[0].childNodes[0].nodeValue) {
                    industries[i].selected = true;
                }
            }
            
            for (var i=0; i < countries.length; i++) {
                if (countries[i].value == country[0].childNodes[0].nodeValue) {
                    countries[i].selected = true;
                }
            }
            
            for (var i=0; i < resume_types.length; i++) {
                if (resume_types[i].value == acceptable_resume_types[0].childNodes[0].nodeValue) {
                    resume_types[i].selected = true;
                }
            }
            
            /*for (i=0; i < currencies.length; i++) {
                if (currencies[i].value == currency[0].childNodes[0].nodeValue) {
                    currencies[i].selected = true;
                }
            }*/
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading job...');
        }
    });
    
    request.send(params);
    
    add_new_job();
    $('job_id').value = job_id;
    $('form_title').set('html', 'Update an Unpublished Job');
}

function show_job(job_id) {
    $('div_job_info').setStyle('display', 'block');
    $('div_job_form').setStyle('display', 'none');
    $('div_open').setStyle('display', 'none');
    $('div_closed').setStyle('display', 'none');
    
    var params = 'id=' + job_id + '&action=get_job';
    
    var uri = root + "/employees/jobs_action.php";
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
            var country = xml.getElementsByTagName('country_name');
            var state = xml.getElementsByTagName('state');
            var currency = xml.getElementsByTagName('currency');
            var salary = xml.getElementsByTagName('salary');
            var salary_end = xml.getElementsByTagName('salary_end');
            var salary_negotiable = xml.getElementsByTagName('salary_negotiable');
            var description = xml.getElementsByTagName('description');
            var created_on = xml.getElementsByTagName('formatted_created_on');
            var expire_on = xml.getElementsByTagName('formatted_expire_on');
            var expired = xml.getElementsByTagName('expired');
            var closed = xml.getElementsByTagName('closed');
            var acceptable_resume_types = xml.getElementsByTagName('acceptable_resume_type');
            
            if (closed[0].childNodes[0].nodeValue != 'Y') {
                $('open_back_arrow').setStyle('display', 'inline');
            } else {
                $('closed_back_arrow').setStyle('display', 'inline');
            }
            
            $('job.title').set('html', title[0].childNodes[0].nodeValue);
            $('job.industry').set('html', industry[0].childNodes[0].nodeValue);
            $('job.country').set('html', country[0].childNodes[0].nodeValue);
            var state_name = '';
            if (state[0].childNodes.length > 0) {
                state_name = state[0].childNodes[0].nodeValue;
            }
            $('job.state').set('html', state_name);
            //$('job.currency').set('html', currency[0].childNodes[0].nodeValue);
            $('job.salary').set('html', salary[0].childNodes[0].nodeValue);
            
            if (salary_end[0].childNodes.length > 0) {
                $('job.salary_end').set('html', '-&nbsp;' + salary_end[0].childNodes[0].nodeValue);
            } else {
                $('job.salary_end').set('html', '');
            }
            
            $('job.description').set('html', description[0].childNodes[0].nodeValue);
            $('job.created_on').set('html', created_on[0].childNodes[0].nodeValue);
            $('job.expire_on').set('html', expire_on[0].childNodes[0].nodeValue);
            
            var resume_type = acceptable_resume_types[0].childNodes[0].nodeValue;
            switch (resume_type) {
                case 'O':
                    $('job.acceptable_resume_type').set('html', 'Online Submission Only');
                    break;
                case 'F':
                    $('job.acceptable_resume_type').set('html', 'File Upload Only');
                    break;
                default:
                    $('job.acceptable_resume_type').set('html', 'Any Kind');
                    break;
            }
            
            if (salary_negotiable[0].childNodes[0].nodeValue == 'Y') {
                $('job.salary_negotiable').set('html', 'Negotiable');
            } else {
                $('job.salary_negotiable').set('html', 'Not Negotiable');
            }
            
            var is_closed = closed[0].childNodes[0].nodeValue;
            if (is_closed == 'Y') {
                $('job.extend').set('html', '<a class="no_link" onClick="extend_job(\'' + job_id + '\');">Re-open this job for another 30 days</a>');
            } else {
                $('job.extend').set('html', '<a class="no_link" onClick="extend_job(\'' + job_id + '\');">Extend this job for another 30 days</a>');
            }
            
            $('job_extend_note').setStyle('display', 'block');
            
            var html = '';
            if (is_closed == 'N') {
                html = html + '<input class="button" type="button" id="close_job" name="close_job" value="Close This Job" onClick="close_job(\'' + job_id + '\');" />&nbsp;&nbsp;&nbsp;';
            } else if (is_closed == 'S') {
                html = html + '<input type="button" class="button" value="Update" onClick="show_update_job(\'' + job_id + '\');" />&nbsp;&nbsp;&nbsp;';
            }

            html = html + '<input class="button" type="button" id="new_from_job" name="new_from_job" value="New From This Job" onClick="new_from_job(\'' + job_id + '\');"/>';
            
            $('job_buttons').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading job...');
        }
    });
    
    request.send(params);
}

function close_job(job_id) {
    var proceed = confirm('Once you click "OK", the selected job/s will be closed permanently. Would you like to continue?');
    if (!proceed) {
        return false;
    }
    
    var payload = '<jobs>' + "\n";
    payload = payload + '<id>' + job_id + '</id>' + "\n";
    payload = payload + '</jobs>';
    
    var params = 'id=0&job=0';
    params = params + '&action=close';
    params = params + '&payload=' + payload;
    
    var uri = root + "/employees/jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while closing selected jobs.');
                return false;
            }
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading currently opened jobs...');
        }
    });
    
    request.send(params);
}

function close_jobs() {
    var inputs = $('list').getElementsByTagName('input');
    var payload = '<jobs>' + "\n";
    var count = 0;
    
    for(i=0; i < inputs.length; i++) {
        var attributes = inputs[i].attributes;
        if (attributes.getNamedItem('type').value == 'checkbox') {
            if (inputs[i].checked) {
                payload = payload + '<id>' + inputs[i].id + '</id>' + "\n";
                count++;
            }
        }
    }
    
    payload = payload + '</jobs>';
    
    if (count <= 0) {
        set_status('Please select at least one job.');
        return false;
    }
    
    var proceed = confirm('Once you click "OK", the selected job/s will be closed permanently. Would you like to continue?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=0&job=0';
    params = params + '&action=close';
    params = params + '&payload=' + payload;
    
    var uri = root + "/employees/jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while closing selected jobs.');
                return false;
            }
            
            for (i=0; i < inputs.length; i++) {
                var attributes = inputs[i].attributes;
                if (attributes.getNamedItem('type').value == 'checkbox') {
                    if (inputs[i].checked) {
                        $(inputs[i].id).setStyle('display', 'none');
                    }
                }
            }
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading currently opened jobs...');
        }
    });
    
    request.send(params);
}

function add_new_job() {
    $('job_id').value = '0';
    $('form_title').set('html', 'Add New Job');
    $('div_job_form').setStyle('display', 'block');
    $('div_job_info').setStyle('display', 'none');
    $('div_open').setStyle('display', 'none');
    $('div_closed').setStyle('display', 'none');
    $('div_tabs').setStyle('display', 'none');
    
    startRTE('');
}

function new_from_job(job_id) {    
    var params = 'id=' + job_id + '&action=get_job';
    
    var uri = root + "/employees/jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading job.');
                return false;
            } 
            
            var industries = $('industry').options;
            var countries = $('country').options;
            var resume_types = $('acceptable_resume_type').options;
            //var currencies = $('currency').options;
            
            var title = xml.getElementsByTagName('title');
            var industry = xml.getElementsByTagName('industry');
            var country = xml.getElementsByTagName('country');
            var state = xml.getElementsByTagName('state');
            //var currency = xml.getElementsByTagName('currency');
            var salary = xml.getElementsByTagName('salary');
            var salary_end = xml.getElementsByTagName('salary_end');
            var salary_negotiable = xml.getElementsByTagName('salary_negotiable');
            var description = xml.getElementsByTagName('description');
            var acceptable_resume_types = xml.getElementsByTagName('acceptable_resume_type');
            
            $('title').value = title[0].childNodes[0].nodeValue;
            $('job.industry').set('html', industry[0].childNodes[0].nodeValue);
            $('job.country').set('html', country[0].childNodes[0].nodeValue);
            $('state').value = '';
            if (state[0].childNodes.length > 0) {
                $('state').value = state[0].childNodes[0].nodeValue;
            }
            
            //$('job.currency').set('html', currency[0].childNodes[0].nodeValue);
            $('salary').value = salary[0].childNodes[0].nodeValue;
            
            if (salary_end[0].childNodes.length > 0) {
                $('salary_end').value = salary_end[0].childNodes[0].nodeValue;
            } else {
                $('salary_end').value = '';
            }
            
            //$('description').value = description[0].childNodes[0].nodeValue.replace(/<br\/>/g, "\n");
            // editor.setValue(description[0].childNodes[0].nodeValue);
            startRTE(description[0].childNodes[0].nodeValue);
            
            if (salary_negotiable[0].childNodes[0].nodeValue == 'Y') {
                $('salary_negotiable').checked = true;
            } else {
                $('salary_negotiable').checked = false;
            }
            
            for (var i=0; i < industries.length; i++) {
                if (industries[i].value == industry[0].childNodes[0].nodeValue) {
                    industries[i].selected = true;
                }
            }
            
            for (var i=0; i < countries.length; i++) {
                if (countries[i].value == country[0].childNodes[0].nodeValue) {
                    countries[i].selected = true;
                }
            }
            
            for (var i=0; i < resume_types.length; i++) {
                if (resume_types[i].value == acceptable_resume_types[0].childNodes[0].nodeValue) {
                    resume_types[i].selected = true;
                }
            }
            
            /*for (i=0; i < currencies.length; i++) {
                if (currencies[i].value == currency[0].childNodes[0].nodeValue) {
                    currencies[i].selected = true;
                }
            }*/
            
            $('job_id').value = '0';
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading job...');
        }
    });
    
    request.send(params);
    
    add_new_job();
    $('form_title').set('html', 'Add New Job From an Existing Job');
}

function extend_job(job_id) {
    var extend = confirm('Are you sure to extend/re-open this job for another 30 days?');
    
    if (extend == false) {
        return;
    }
    
    var params = 'id=' + job_id + '&action=extend';
    var uri = root + "/employees/jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while extending job.');
                return false;
            } 
            
            //show_job(job_id);
            show_open_jobs_with_selected_employer();
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Extending job...');
        }
    });
    
    request.send(params);
}

function get_employer_currency() {
    var params = 'id=' + selected_employer_id + '&action=get_currency';
    
    var uri = root + "/employees/jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            selected_employer_currency = txt;
            
            $('currency').value = selected_employer_currency;
            $('employer.currency').set('html', selected_employer_currency);
            $('employer.currency_1').set('html', selected_employer_currency);
        }
    });
    
    request.send(params);
}
function show_open_jobs_with_selected_employer() {
    show_open_jobs(selected_employer_id);
}

function show_open_jobs(_employer_id) {
    selected_employer_id = _employer_id;
    
    if (isEmpty(selected_employer_currency)) {
        get_employer_currency();
    }
    
    $('div_jobs').setStyle('display', 'block');
    $('div_employers').setStyle('display', 'none');
    
    selected_tab = 'li_open';
    $(selected_tab).setStyle('border', '1px solid #CCCCCC');
    $('li_closed').setStyle('border', '1px solid #0000FF');
    $('div_open').setStyle('display', 'block');
    $('div_closed').setStyle('display', 'none');
    $('div_job_info').setStyle('display', 'none');
    $('div_job_form').setStyle('display', 'none');
    $('open_back_arrow').setStyle('display', 'none');
    $('closed_back_arrow').setStyle('display', 'none');
    
    var params = 'id=' + selected_employer_id + '&action=get_jobs';
    params = params + '&order_by=' + order_by + ' ' + order;
    params = params + '&closed=N';
    
    var uri = root + "/employees/jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading opened jobs.');
                return false;
            }
            
            var ids = xml.getElementsByTagName('id');
            var industries = xml.getElementsByTagName('industry');
            var titles = xml.getElementsByTagName('title');
            var created_ons = xml.getElementsByTagName('created_on');
            var expire_ons = xml.getElementsByTagName('expire_on');
            var expired_days = xml.getElementsByTagName('expired_days');
            var closeds = xml.getElementsByTagName('closed');
            
            var html = '<table id="list" class="list">';
            if (ids.length <= 0) {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">Please click on the \"Add New Job Ad\" button to get started.</div>';
                
                $('close_jobs').disabled = true;
                $('close_jobs_1').disabled = true;
            } else {
                var odd = true;
                for (i=0; i < ids.length; i++) {
                    var job_id = ids[i];
                    
                    html = html + '<tr id="'+ job_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    
                    html = html + '<td class="checkbox"><input type="checkbox" id="'+ job_id.childNodes[0].nodeValue + '" /></td>' + "\n";
                    //html = html + '<td class="id">' + job_id.childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="industry">' + industries[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    html = html + '<td class="title"><a href="#" onClick="show_job(\'' + job_id.childNodes[0].nodeValue + '\')">' + titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="date">' + created_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    if (parseInt(expired_days[i].childNodes[0].nodeValue) > 0) {
                        html = html + '<td class="date"><span style="font-weight: bold; color: #FF0000;">' + expire_ons[i].childNodes[0].nodeValue + '</span></td>' + "\n";
                    } else {
                        html = html + '<td class="date">' + expire_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    }
                    
                    var closed = closeds[i].childNodes[0].nodeValue;
                    if (closed == 'N') {
                        html = html + '<td class="new_from"><input type="button" class="mini_button" value="New From This Job" onClick="new_from_job(\'' + job_id.childNodes[0].nodeValue + '\')" /></td>' + "\n";
                    } else {
                        html = html + '<td class="new_from"><input type="button" class="mini_button" value="Update" onClick="show_update_job(\'' + job_id.childNodes[0].nodeValue + '\')" /></td>' + "\n";
                    }
                    // html = html + '<td class="new_from"><input type="button" class="mini_button" value="New From This Job" onClick="new_from_job(\'' + job_id.childNodes[0].nodeValue + '\')" /></td>' + "\n";
                    html = html + '</tr>' + "\n";

                    if (odd) {
                        odd = false;
                    } else {
                        odd = true;
                    }
                }
                html = html + '</table>';
                
                $('close_jobs').disabled = false;
                $('close_jobs_1').disabled = false;
            }
            
            $('div_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading currently opened jobs...');
        }
    });
    
    request.send(params);
}

function show_closed_jobs_with_selected_employer() {
    show_closed_jobs(selected_employer_id);
}

function show_closed_jobs(_employer_id) {
    selected_employer_id = _employer_id;
    
    if (isEmpty(selected_employer_currency)) {
        get_employer_currency();
    }
    
    $('div_jobs').setStyle('display', 'block');
    $('div_employers').setStyle('display', 'none');
    
    selected_tab = 'li_closed';
    $(selected_tab).setStyle('border', '1px solid #CCCCCC');
    $('li_open').setStyle('border', '1px solid #0000FF');
    $('div_closed').setStyle('display', 'block');
    $('div_open').setStyle('display', 'none');
    $('div_job_info').setStyle('display', 'none');
    $('div_job_form').setStyle('display', 'none');
    $('open_back_arrow').setStyle('display', 'none');
    $('closed_back_arrow').setStyle('display', 'none');
    
    var params = 'id=' + selected_employer_id + '&action=get_jobs';
    params = params + '&order_by=' + order_by + ' ' + order;
    params = params + '&closed=Y';
    
    var uri = root + "/employers/jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading opened jobs.');
                return false;
            }
            
            var ids = xml.getElementsByTagName('id');
            var industries = xml.getElementsByTagName('industry');
            var titles = xml.getElementsByTagName('title');
            var created_ons = xml.getElementsByTagName('created_on');
            var expire_ons = xml.getElementsByTagName('expire_on');
            
            var html = '<table id="closed_list" class="list">';
            if (ids.length <= 0) {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no closed jobs at the moment.</div>';
            } else {
                var odd = true;
                for (i=0; i < ids.length; i++) {
                    var job_id = ids[i];
                    
                    html = html + '<tr id="'+ job_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    
                    //html = html + '<td class="id">' + job_id.childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="industry">' + industries[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title"><a href="#" onClick="show_job(\'' + job_id.childNodes[0].nodeValue + '\')">' + titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="date">' + created_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + expire_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="new_from"><input type="button" class="mini_button" value="New From This Job" onClick="new_from_job(\'' + job_id.childNodes[0].nodeValue + '\')" /></td>' + "\n";
                    html = html + '</tr>' + "\n";

                    if (odd) {
                        odd = false;
                    } else {
                        odd = true;
                    }
                }
                html = html + '</table>';
            }
            
            $('div_closed_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading closed jobs...');
        }
    });
    
    request.send(params);
}

function select_all_jobs() {
    var inputs = $('list').getElementsByTagName('input');
    
    if ($('close_all').checked) {
        for (i=0; i < inputs.length; i++) {
            var attributes = inputs[i].attributes;
            if (attributes.getNamedItem('type').value == 'checkbox') {
                inputs[i].checked = true;
            }
        }
    } else {
        for (i=0; i < inputs.length; i++) {
            var attributes = inputs[i].attributes;
            if (attributes.getNamedItem('type').value == 'checkbox') {
                inputs[i].checked = false;
            }
        }
    }
}

function set_mouse_events() {
    $('li_open').addEvent('mouseover', function() {
        $('li_open').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_open').addEvent('mouseout', function() {
        $('li_open').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_closed').addEvent('mouseover', function() {
        $('li_closed').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_closed').addEvent('mouseout', function() {
        $('li_closed').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_back').addEvent('mouseover', function() {
        $('li_back').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_back').addEvent('mouseout', function() {
        $('li_back').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_back_main').addEvent('mouseover', function() {
        $('li_back_main').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_back_main').addEvent('mouseout', function() {
        $('li_back_main').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
}

function onDomReady() {
    set_root();
    set_mouse_events();
    get_unapproved_photos_count();
    get_employee_rewards_count();
    get_employee_tokens_count();
    
    $('li_back_main').addEvent('click', show_employers);
    $('li_open').addEvent('click', show_open_jobs_with_selected_employer);
    $('li_closed').addEvent('click', show_closed_jobs_with_selected_employer);
    $('close_jobs').addEvent('click', close_jobs);
    $('close_jobs_1').addEvent('click', close_jobs);
    $('add_new_job').addEvent('click', add_new_job);
    $('add_new_job_1').addEvent('click', add_new_job);
    $('close_all').addEvent('click', select_all_jobs);
    
    // editor.render($('description'));
    
    $('li_back').addEvent('click', function() {
        $('div_open').setStyle('display', 'block');
        $('div_closed').setStyle('display', 'block');
        $('div_tabs').setStyle('display', 'block');
        show_open_jobs_with_selected_employer();
    });
    
    $('sort_employer').addEvent('click', function() {
        employers_order_by = 'employers.name';
        employers_ascending_or_descending();
        show_employers();
    });
    
    $('sort_expired').addEvent('click', function() {
        employers_order_by = 'num_expired';
        employers_ascending_or_descending();
        show_employers();
    });
    
    $('sort_closed').addEvent('click', function() {
        employers_order_by = 'num_closed';
        employers_ascending_or_descending();
        show_employers();
    });
    
    $('sort_open').addEvent('click', function() {
        employers_order_by = 'num_open';
        employers_ascending_or_descending();
        show_employers();
    });
    
    $('sort_saved').addEvent('click', function() {
        employers_order_by = 'num_saved';
        employers_ascending_or_descending();
        show_employers();
    });
    
    $('sort_total').addEvent('click', function() {
        employers_order_by = 'total_jobs';
        employers_ascending_or_descending();
        show_employers();
    });
    
    $('sort_industry').addEvent('click', function() {
        order_by = 'industry';
        ascending_or_descending();
        show_open_jobs_with_selected_employer();
    });
    
    $('sort_title').addEvent('click', function() {
        order_by = 'title';
        ascending_or_descending();
        show_open_jobs_with_selected_employer();
    });
    
    $('sort_industry').addEvent('click', function() {
        order_by = 'industry';
        ascending_or_descending();
        show_open_jobs_with_selected_employer();
    });
    
    $('sort_title').addEvent('click', function() {
        order_by = 'title';
        ascending_or_descending();
        show_open_jobs_with_selected_employer();
    });
    
    $('sort_created_on').addEvent('click', function() {
        order_by = 'jobs.created_on';
        ascending_or_descending();
        show_open_jobs_with_selected_employer();
    });
    
    $('sort_expire_on').addEvent('click', function() {
        order_by = 'jobs.expire_on';
        ascending_or_descending();
        show_open_jobs_with_selected_employer();
    });
    
    $('sort_industry_closed').addEvent('click', function() {
        order_by = 'industry';
        ascending_or_descending();
        show_closed_jobs_with_selected_employer();
    });
    
    $('sort_title_closed').addEvent('click', function() {
        order_by = 'title';
        ascending_or_descending();
        show_closed_jobs_with_selected_employer();
    });
    
    $('sort_created_on_closed').addEvent('click', function() {
        order_by = 'jobs.created_on';
        ascending_or_descending();
        show_closed_jobs_with_selected_employer();
    });
    
    $('sort_expire_on_closed').addEvent('click', function() {
        order_by = 'jobs.expire_on';
        ascending_or_descending();
        show_closed_jobs_with_selected_employer();
    });
    
    // if (selected_tab == 'li_open') {
    //     show_open_jobs();
    // } else {
    //     show_closed_jobs();
    // }
    
    show_employers();
    
    $('publish_job').addEvent('click', publish_job);
    $('save_job').addEvent('click', save_job);
    $('cancel_job').addEvent('click', function() {
        $('div_job_form').setStyle('display', 'none');
        $('div_tabs').setStyle('display', 'block');
        
        if (selected_tab == 'li_open') {
            $('div_open').setStyle('display', 'block');
        } else {
            $('div_closed').setStyle('display', 'block');
        }
        
        set_status('');
        $('job_id').value = '0';
    });
}

window.addEvent('domready', onDomReady);
