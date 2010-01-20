var selected_tab = 'li_open';
var order_by = 'jobs.created_on';
var order = 'desc';

var cc_emails = new Array();
// var editor = new Editor();
var added_admin_fee = false;

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
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
    
    if (!isEmpty($('contact_carbon_copy').value)) {
        var emails = $('contact_carbon_copy').value.split(',');
        for (var i=0; i < emails.length; i++) {
            emails[i] = trim(emails[i]);
            if (!isEmail(emails[i])) {
                set_status('One of the Send Alert Cc email address is invalid.<br/>You have to separate the email addresses by commas.');
                return false;
            }
        }
        
        cc_emails = emails;
    }
    
    set_status('');
    return true;
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
    
    var params = 'job=' + $('job_id').value;
    params = params + '&action=save';
    params = params + '&employer=' + id;
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
    
    params = params + '&cc=';
    if (cc_emails.length > 0) {
        for (var i=0; i < cc_emails.length; i++) {
            params = params + cc_emails[i];
            
            if (i < (cc_emails.length - 1)) {
                params = params + ', ';
            }
        }
    }
    
    var uri = root + "/employers/job_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ok') {
                set_status('New job successfully saved.');
            } else if (txt == '-1') {
                set_status('Your account is not set up yet. Upon receiving the Welcome email from us, please allow up to the next 24 hours for your account to be active. <br/>If this message still shows up after 24 hours, please contact your account manager immediately.');
                return false;
            } else {
                set_status('Sorry! We are not able to save the job at the moment. Please try again later.');
                return false;
            }
            
            $('div_tabs').setStyle('display', 'block');
            show_open_jobs();
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
    
    var params = 'job=' + $('job_id').value;
    params = params + '&action=publish';
    params = params + '&employer=' + id;
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
    
    params = params + '&cc=';
    if (cc_emails.length > 0) {
        for (var i=0; i < cc_emails.length; i++) {
            params = params + cc_emails[i];
            
            if (i < (cc_emails.length - 1)) {
                params = params + ', ';
            }
        }
    }
    
    var uri = root + "/employers/job_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ok') {
                set_status('New job successfully published.');
            } else if (txt == '-1') {
                alert('Your account is not set up yet. Upon receiving the Welcome email from us, please allow up to the next 24 hours for your account to be active. <br/>If this message still shows up after 24 hours, please contact your account manager immediately.');
                return false;
            } else if (txt == '-2') {
                alert('You have no more job slots left to publish!' + "\n\nPlease save your job and publish it later after you have purchased enough job slots from the 'Job Slots' page.");
                return false;
            } else {
                set_status('Sorry! We are not able to publish the new job at the moment. Please try again later.');
                return false;
            }
            
            $('div_tabs').setStyle('display', 'block');
            show_open_jobs();
        },
        onRequest: function(instance) {
            set_status('Saving and updating...');
        }
    });
    
    request.send(params);
}

function show_job(job_id) {
    $('div_job_info').setStyle('display', 'block');
    $('div_job_form').setStyle('display', 'none');
    $('div_open').setStyle('display', 'none');
    $('div_closed').setStyle('display', 'none');
    
    var params = 'job=' + job_id;
    
    var uri = root + "/employers/job_action.php";
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
            var contact_ccs = xml.getElementsByTagName('contact_carbon_copy');
            var contact_names = xml.getElementsByTagName('contact_person');
            var contact_email_addrs = xml.getElementsByTagName('email_addr');
            
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
            
            $('job.contact').set('html', contact_names[0].childNodes[0].nodeValue + ' (' + contact_email_addrs[0].childNodes[0].nodeValue + ')');
            
            $('job.contact_carbon_copy').set('html', '');
            if (contact_ccs[0].childNodes.length > 0) {
                $('job.contact_carbon_copy').set('html', contact_ccs[0].childNodes[0].nodeValue);
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
                // $('job.extend').set('html', '<a class="no_link" onClick="extend_job(\'' + job_id + '\');">Re-open this job for another 30 days</a>');
                $('job.extend').set('html', '<input type="button" onClick="extend_job(\'' + job_id + '\');" value="Re-open this job for another 30 days" />');
            } else {
                // $('job.extend').set('html', '<a class="no_link" onClick="extend_job(\'' + job_id + '\');">Extend this job for another 30 days</a>');
                $('job.extend').set('html', '<input type="button" onClick="extend_job(\'' + job_id + '\');" value="Extend this job for another 30 days" />');
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
    
    var params = 'job=0';
    params = params + '&action=close';
    params = params + '&payload=' + payload;
    
    var uri = root + "/employers/job_action.php";
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
    
    var params = 'job=0';
    params = params + '&action=close';
    params = params + '&payload=' + payload;
    
    var uri = root + "/employers/job_action.php";
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
    $('contact').set('html', '');
    
    var params = 'job=0&id=' + id + '&action=get_contact_person';
    var uri = root + "/employers/job_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            var contact_names = xml.getElementsByTagName('contact_person');
            var email_addrs = xml.getElementsByTagName('email_addr');
            
            $('contact').set('html', contact_names[0].childNodes[0].nodeValue + ' (' + email_addrs[0].childNodes[0].nodeValue + ')');
        }
    });
    
    request.send(params);
    
    startRTE('');
}

function new_from_job(job_id) {    
    var params = 'job=' + job_id;
    
    var uri = root + "/employers/job_action.php";
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
            var contact_ccs = xml.getElementsByTagName('contact_carbon_copy');
            var contact_names = xml.getElementsByTagName('contact_person');
            var contact_email_addrs = xml.getElementsByTagName('email_addr');
            
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
            
            $('contact').set('html', contact_names[0].childNodes[0].nodeValue + ' (' + contact_email_addrs[0].childNodes[0].nodeValue + ')');
            
            $('contact_carbon_copy').value = '';
            if (contact_ccs[0].childNodes.length > 0) {
                $('contact_carbon_copy').value = contact_ccs[0].childNodes[0].nodeValue;
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

function show_update_job(job_id) {    
    var params = 'job=' + job_id;
    
    var uri = root + "/employers/job_action.php";
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
            var contact_ccs = xml.getElementsByTagName('contact_carbon_copy');
            var contact_names = xml.getElementsByTagName('contact_person');
            var contact_email_addrs = xml.getElementsByTagName('email_addr');
            
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
            
            $('contact').set('html', contact_names[0].childNodes[0].nodeValue + ' (' + contact_email_addrs[0].childNodes[0].nodeValue + ')');
            
            $('contact_carbon_copy').value = '';
            if (contact_ccs[0].childNodes.length > 0) {
                $('contact_carbon_copy').value = contact_ccs[0].childNodes[0].nodeValue;
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

function extend_job(job_id) {
    var extend = confirm('Are you sure to extend/re-open this job for another 30 days?');
    
    if (extend == false) {
        return;
    }
    
    var params = 'job=' + job_id + '&action=extend';
    var uri = root + "/employers/job_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while extending job.');
                return false;
            } else if (txt == '-2') {
                alert('You have no more job slots left to publish!' + "\n\nPlease extend it later after you have purchased enough job slots from the 'Job Slots' page.");
                return false;
            }
            
            //show_job(job_id);
            show_open_jobs();
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Extending job...');
        }
    });
    
    request.send(params);
}

function show_open_jobs() {
    selected_tab = 'li_open';
    $(selected_tab).setStyle('border', '1px solid #CCCCCC');
    $('li_closed').setStyle('border', '1px solid #0000FF');
    $('div_open').setStyle('display', 'block');
    $('div_closed').setStyle('display', 'none');
    $('div_job_info').setStyle('display', 'none');
    $('div_job_form').setStyle('display', 'none');
    $('open_back_arrow').setStyle('display', 'none');
    $('closed_back_arrow').setStyle('display', 'none');
    
    var params = 'id=' + id;
    params = params + '&order_by=' + order_by + ' ' + order;
    params = params + '&closed=N';
    
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
            var is_referreds = xml.getElementsByTagName('is_referred');
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
                    
                    var is_referred = '';
                    if (is_referreds[i].childNodes[0].nodeValue == 'Y') {
                        is_referred = '[&bull;]&nbsp;';
                    }
                    html = html + '<td class="title">' + is_referred + '<a href="#" onClick="show_job(\'' + job_id.childNodes[0].nodeValue + '\')">' + titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="date">' + created_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    var closed = closeds[i].childNodes[0].nodeValue;
                    if (closed == 'N') {
                        html = html + '<td class="date">' + expire_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                        html = html + '<td class="new_from"><input type="button" class="mini_button" value="New From This Job" onClick="new_from_job(\'' + job_id.childNodes[0].nodeValue + '\')" /></td>' + "\n";
                    } else {
                        html = html + '<td class="date">-</td>' + "\n";
                        html = html + '<td class="new_from"><input type="button" class="mini_button" value="Update" onClick="show_update_job(\'' + job_id.childNodes[0].nodeValue + '\')" /></td>' + "\n";
                    }
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

function show_closed_jobs() {
    selected_tab = 'li_closed';
    $(selected_tab).setStyle('border', '1px solid #CCCCCC');
    $('li_open').setStyle('border', '1px solid #0000FF');
    $('div_closed').setStyle('display', 'block');
    $('div_open').setStyle('display', 'none');
    $('div_job_info').setStyle('display', 'none');
    $('div_job_form').setStyle('display', 'none');
    $('open_back_arrow').setStyle('display', 'none');
    $('closed_back_arrow').setStyle('display', 'none');
    
    var params = 'id=' + id;
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

function get_slots_left() {
    $('num_slots').set('html', '(Free)');
    $('num_slots').setStyle('color', '#1D3E6E');
    $('slots_expiry').set('html', '(Not Applicable)');
    $('slots_expiry').setStyle('color', '#666666');
    $('buy_postings_button').disabled = true;
    $('buy_postings_button').src = '../common/images/button_buy_now_disabled.gif';
    
    var params = 'id=' + id + '&action=get_slots_left';
    
    var uri = root + "/employers/slots_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                $('num_slots').set('html', '(Error)');
                $('num_slots').setStyle('color', '#FF0000');
                return false;
            }
            
            if (txt != '-1') {
                var slots = xml.getElementsByTagName('slots');
                var expired = xml.getElementsByTagName('expired');
                var expire_on = xml.getElementsByTagName('expire_on');
                
                $('slots_expiry').set('html', expire_on[0].childNodes[0].nodeValue);

                if (parseInt(expired[0].childNodes[0].nodeValue) < 0) {
                    $('num_slots').set('html', '(All slots are expired.)');
                    $('num_slots').setStyle('color', '#FF0000');
                    $('slots_expiry').setStyle('color', '#FF0000');
                } else {
                    if (parseInt(slots[0].childNodes[0].nodeValue) == 0) {
                        $('num_slots').set('html', '(You have no more slots left.)');
                        $('num_slots').setStyle('color', '#FF0000');
                    } else {
                        if (parseInt(slots[0].childNodes[0].nodeValue) <= 2) {
                            $('num_slots').set('html', slots[0].childNodes[0].nodeValue);
                            $('num_slots').setStyle('color', '#FFAE00');
                        } else {
                            $('num_slots').set('html', slots[0].childNodes[0].nodeValue);
                            $('num_slots').setStyle('color', '#079607');
                        }
                    }
                }
                
                $('buy_postings_button').disabled = false;
                $('buy_postings_button').src = '../common/images/button_buy_now.gif';
            }
        }
    });
    
    request.send(params);
}

function show_buy_slots_form() {
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_buy_slots_form').getStyle('height'));
    var div_width = parseInt($('div_buy_slots_form').getStyle('width'));
    
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
    
    if (window_height <= div_height) {
        $('div_buy_slots_form').setStyle('height', window_height);
        $('div_buy_slots_form').setStyle('top', 0);
        window.scrollTo(0, 0);
    } else {
        $('div_buy_slots_form').setStyle('top', ((window_height - div_height) / 2));
    }
    $('div_buy_slots_form').setStyle('left', ((window_width - div_width) / 2));
    
    if ($('payment_method_credit_card').disabled) {
        add_admin_fee();
    }
    
    $('div_blanket').setStyle('display', 'block');
    $('div_buy_slots_form').setStyle('display', 'block');
}

function close_buy_slots_form() {
    $('div_buy_slots_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function calculate_fee() {
    var price = parseFloat($('price_per_slot').get('html'));
    var qty = parseInt($('qty').value);
    var discount = 0;
    var amount = 0.00;
    
    if (qty <= 0 || isEmpty($('qty').value) || isNaN($('qty').value)) {
        $('total_amount').set('html', '0.00');
        return;
    } else if (qty >= 5 && qty <= 15) {
        discount = 10;
    } else if (qty > 15 && qty <= 25) {
        discount = 15;
    } else if (qty > 25 && qty <= 35) {
        discount = 20;
    } else if (qty > 35) {
        discount = 25;
    } 
    
    amount = (price * qty) - ((price * qty) * (discount / 100));
    if (added_admin_fee) {
        amount = amount + (amount * 0.05);
    }
    
    $('discount').set('html', discount + '%');
    $('total_amount').set('html', amount);
}

function add_admin_fee() {
    if ($('payment_method_cheque').checked) {
        added_admin_fee = true;
        calculate_fee();
    }
}

function remove_admin_fee() {
    if (added_admin_fee) {
        added_admin_fee = false;
        calculate_fee();
    }
}

function buy_slots() {
    var qty = $('qty').value;
    
    if (isNaN(qty) || isEmpty(qty) || parseInt(qty) <= 0) {
        alert('You must purchase at least 1 slot.');
        return false;
    }
    
    var is_confirmed = confirm('You are about to purchase ' + qty + ' slot(s) at ' + $('payment_currency').value + '$ ' + $('total_amount').get('html') + ".\n\nPlease click 'OK' to proceed to payment portal or 'Cancel' to continue using the available slots.");
    
    if (!is_confirmed) {
        set_status('');
        close_buy_slots_form();
        return false;
    }
    
    var payment_method = 'credit_card';
    if ($('payment_method_cheque').checked) {
        payment_method = 'cheque';
    }
    
    if (payment_method == 'credit_card') {
        var return_url = root + paypal_return_url_base;
        var cancel_url = root + paypal_return_url_base;
        
        var paypal_inputs = new Hash({
            'cmd': '_xclick',
            'business': paypal_id,
            'item_name': qty + ' Job Slots',
            'amount': parseFloat($('total_amount').get('html')),
            'currency_code': $('payment_currency').value, 
            'custom': 'employer=' + id + '&price=' + parseFloat($('price_per_slot').get('html')) + '&qty=' + qty,
            'return': root + paypal_return_url_base,
            'cancel_return': root + paypal_return_url_base,
            'notify_url': paypal_ipn_url
        });
        
        close_buy_slots_form();
        
        div_height = parseInt($('div_paypal_progress').getStyle('height'));
        div_width = parseInt($('div_paypal_progress').getStyle('width'));

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

        if (window_height <= div_height) {
            $('div_paypal_progress').setStyle('height', window_height);
            $('div_paypal_progress').setStyle('top', 0);
            window.scrollTo(0, 0);
        } else {
            $('div_paypal_progress').setStyle('top', ((window_height - div_height) / 2));
        }
        $('div_paypal_progress').setStyle('left', ((window_width - div_width) / 2));
        $('div_blanket').setStyle('display', 'block');
        $('div_paypal_progress').setStyle('display', 'block');
        
        post_to_paypal_with(paypal_inputs);
        return;
    }
    
    var params = 'id=' + id;
    params = params + '&action=buy_slots';
    params = params + '&currency=' + $('payment_currency').value;
    params = params + '&price=' + parseFloat($('price_per_slot').get('html'));
    params = params + '&qty=' + parseInt(qty);
    params = params + '&amount=' + parseFloat($('total_amount').get('html'));
    params = params + '&payment_method=' + payment_method;
    
    var uri = root + "/employers/slots_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while purchasing slots.');
                return false;
            }
            
            if (txt == '-1') {
                alert('A payment instruction has been send to your email account. Please follow the instruction to lodge your payment.');
            }
            
            set_status('');
            close_buy_slots_form();
            show_purchase_histories();
            get_slots_left();
        },
        onRequest: function(instance) {
            set_status('Purchasing slots...');
        }
    });
    
    request.send(params);
}

function show_purchase_histories() {
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_purchase_histories').getStyle('height'));
    var div_width = parseInt($('div_purchase_histories').getStyle('width'));
    
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
    
    if (window_height <= div_height) {
        $('div_purchase_histories').setStyle('height', window_height);
        $('div_purchase_histories').setStyle('top', 0);
        window.scrollTo(0, 0);
    } else {
        $('div_purchase_histories').setStyle('top', ((window_height - div_height) / 2));
    }
    $('div_purchase_histories').setStyle('left', ((window_width - div_width) / 2));
    
    var params = 'id=' + id;
    
    var uri = root + "/employers/slots_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading slots purchase histories.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There is no past purchase of slots.</div>';
            } else {
                var price_per_slots = xml.getElementsByTagName('price_per_slot');
                var number_of_slots = xml.getElementsByTagName('number_of_slot');
                var total_amounts = xml.getElementsByTagName('total_amount');
                var purchased_ons = xml.getElementsByTagName('formatted_purchased_on');
                
                for (var i=0; i < price_per_slots.length; i++) {
                    html = html + '<tr onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="date">' + purchased_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="number_of_slots">' + number_of_slots[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="price_per_slot">' + price_per_slots[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="amount">' + total_amounts[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                
                html = html + '</table>';
            }
            
            $('div_purchases_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading slots purchase histories...');
        }
    });
    
    request.send(params);
    
    $('div_blanket').setStyle('display', 'block');
    $('div_purchase_histories').setStyle('display', 'block');
}

function close_purchase_histories() {
    $('div_purchase_histories').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
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
}

function onDomReady() {
    set_root();
    set_mouse_events();
    get_employer_referrals_count();
    
    // get_slots_left();
    
    $('li_open').addEvent('click', show_open_jobs);
    $('li_closed').addEvent('click', show_closed_jobs);
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
        show_open_jobs();
    });
    
    $('sort_industry').addEvent('click', function() {
        order_by = 'industry';
        ascending_or_descending();
        show_open_jobs();
    });
    
    $('sort_title').addEvent('click', function() {
        order_by = 'title';
        ascending_or_descending();
        show_open_jobs();
    });
    
    $('sort_created_on').addEvent('click', function() {
        order_by = 'jobs.created_on';
        ascending_or_descending();
        show_open_jobs();
    });
    
    $('sort_expire_on').addEvent('click', function() {
        order_by = 'jobs.expire_on';
        ascending_or_descending();
        show_open_jobs();
    });
    
    $('sort_industry_closed').addEvent('click', function() {
        order_by = 'industry';
        ascending_or_descending();
        show_closed_jobs();
    });
    
    $('sort_title_closed').addEvent('click', function() {
        order_by = 'title';
        ascending_or_descending();
        show_closed_jobs();
    });
    
    $('sort_created_on_closed').addEvent('click', function() {
        order_by = 'jobs.created_on';
        ascending_or_descending();
        show_closed_jobs();
    });
    
    $('sort_expire_on_closed').addEvent('click', function() {
        order_by = 'jobs.expire_on';
        ascending_or_descending();
        show_closed_jobs();
    });
    
    if (selected_tab == 'li_open') {
        show_open_jobs();
    } else {
        show_closed_jobs();
    }
    
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
