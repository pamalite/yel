var selected_tab = 'li_profile';
var order_by = 'members.joined_on';
var order = 'desc';
var resumes_order_by = 'modified_on';
var resumes_order = 'desc';

var current_member_email_addr = '';
var current_member_name = '';
var selected_resume_id = '0';

// Job class for easy storage
function Job(_id, _title, _employer, _industry, _currency, _salary, _description) {
    this.id = _id;
    this.title = _title;
    this.employer = _employer;
    this.industry = _industry;
    this.currency = _currency;
    this.salary = _salary;
    this.description = _description;
}
var available_jobs = new Array();
var jobs_list = new ListBox('jobs', 'jobs_list', false);
var filter_by = '0';

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

function validate_new_candidate_form() {
    if (!isEmail($('member_email_addr').value)) {
        alert('The e-mail address provided is not valid.');
        return false;
    }
    
    if (isEmpty($('member_firstname').value)) {
        alert('Candidate firstnames cannot be empty.');
        return false;
    }
    
    if (isEmpty($('member_lastname').value)) {
        alert('Candidate lastnames cannot be empty.');
        return false;
    }
    
    if (isEmpty($('member_phone_num').value)) {
        alert('Candidate telephone cannot be empty.');
        return false;
    }
    
    if ($('country').options[$('country').selectedIndex].value == 0) {
        alert('You must at least choose a country of residence.');
        return false;
    } 
    
    if (isEmpty($('zip').value)) {
        alert('Postal/Zip Code cannot be empty.');
        return false;
    }
    
    if ($('recommender_from_list').checked) {
        if ($('recommender').options[$('recommender').selectedIndex].value == 0) {
            alert('You need to choose one of the existing recommenders.');
            return false;
        } 
    } else if ($('recommender_from_new').checked) {
        if (!isEmail($('recommender_email_addr').value)) {
            alert('The recommender\'s e-mail address provided is not valid.');
            return false;
        }

        if (isEmpty($('recommender_firstname').value)) {
            alert('Recommender firstnames cannot be empty.');
            return false;
        }

        if (isEmpty($('recommender_lastname').value)) {
            alert('Recommender lastnames cannot be empty.');
            return false;
        }
        
        var selected_count = 0;
        for (var i=0; i < $('recommender_industries').options.length; i++) {
            if ($('recommender_industries').options[i].selected) {
                selected_count++;
            }
        }
        
        if (selected_count <= 0) {
            var msg = 'Are you sure not to classify the recommender with any of the specilizations?';
            if (!confirm(msg)) {
                return false;
            }
        }
    }
    
    return true;
}

function save_remark() {
    var params = 'id=' + current_member_email_addr + '&action=save_remark';
    var params = params + '&remark=' + $('profile.remarks').value;
    
    var uri = root + "/prs/resumes_privileged_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('Error occured while saving remark.');
                return false;
            }
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Saving remark...');
        }
    });
    
    request.send(params);
}

function clear_job_details() {
    $('job_details.title').set('html', '&nbsp;');
    $('job_details.industry').set('html', '&nbsp;');
    $('job_details.currency').set('html', '&nbsp;');
    $('job_details.salary').set('html', '&nbsp;');
    $('job_details.description').set('html', '&nbsp;');
}

function show_job_details() {
    $('instructions').setStyle('display', 'none');
    $('job_details').setStyle('display', 'block');
    
    clear_job_details();
    
    if (isEmpty(jobs_list.selected_value)) {
        $('instructions').setStyle('display', 'block');
        $('job_details').setStyle('display', 'none');
        set_status('');
        return;
    }
    
    for (var i=0; i < available_jobs.length; i++) {
        if (available_jobs[i].id == jobs_list.selected_value) {
            $('job_details.title').set('html', available_jobs[i].title);
            $('job_details.industry').set('html', available_jobs[i].industry);
            $('job_details.currency').set('html', available_jobs[i].currency);
            $('job_details.salary').set('html', available_jobs[i].salary);
            $('job_details.description').set('html', available_jobs[i].description);
            break;
        }
    }
}

function show_available_jobs() {
    $('jobs').set('html', '');
    $('instructions').setStyle('display', 'block');
    $('job_details').setStyle('display', 'none');
    
    var params = 'id=' + id + '&action=get_jobs';
    params = params + '&filter_by=' + filter_by;
    
    var uri = root + "/prs/resumes_privileged_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == '0') {
                alert('No jobs available for referral at the moment.');
                return false;
            }
            
            available_jobs = new Array();
            jobs_list.clear();
            
            var ids = xml.getElementsByTagName('id');
            var titles = xml.getElementsByTagName('title');
            var descriptions = xml.getElementsByTagName('description');
            var job_industries = xml.getElementsByTagName('industry');
            var employers = xml.getElementsByTagName('employer');
            var salaries = xml.getElementsByTagName('salary');
            var currencies = xml.getElementsByTagName('currency');
            
            for (var i=0; i < ids.length; i++) {
                var job = new Job(ids[i].childNodes[0].nodeValue, titles[i].childNodes[0].nodeValue, employers[i].childNodes[0].nodeValue, job_industries[i].childNodes[0].nodeValue, currencies[i].childNodes[0].nodeValue, salaries[i].childNodes[0].nodeValue, descriptions[i].childNodes[0].nodeValue);
                available_jobs[i] = job;
                jobs_list.add_item('<span style="font-weight: bold;">' + job.title + '</span><br/><span style="font-size: 7pt;">' + job.employer + '</span>', job.id);
            }
            
            jobs_list.show();
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading jobs...');
        }
    });
    
    request.send(params);
}

function show_candidates() {
    $('div_candidates').setStyle('display', 'block');
    $('div_candidate').setStyle('display', 'none');
    $('div_new_member_form').setStyle('display', 'none');
    $('div_upload_resume_form').setStyle('display', 'none');
    
    var params = 'id=' + id + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/prs/resumes_privileged_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading privileged candidates.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no candidates at the moment.</div>';
            } else {
                var member_emails = xml.getElementsByTagName('member_email_addr');
                var recommender_emails = xml.getElementsByTagName('recommender_email_addr');
                var added_bys = xml.getElementsByTagName('employee');
                var candidates = xml.getElementsByTagName('candidate_name');
                var recommenders = xml.getElementsByTagName('recommender_name');
                var candidate_phone_nums = xml.getElementsByTagName('member_phone_num');
                var recommender_phone_nums = xml.getElementsByTagName('recommender_phone_num');
                var joined_ons = xml.getElementsByTagName('formatted_joined_on');
                var remarks = xml.getElementsByTagName('remarks');
                
                for (var i=0; i < member_emails.length; i++) {
                    var id = member_emails[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="date">' + joined_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + added_bys[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="candidate"><a href="mailto: ' + id + '">' + candidates[i].childNodes[0].nodeValue + '</a><br/><div class="phone_num"><strong>Tel:</strong> ' + candidate_phone_nums[i].childNodes[0].nodeValue + '<br/><strong>E-mail:</strong> ' + id + '</div></td>' + "\n";
                    
                    var phone_num = 'N/A';
                    if (recommender_phone_nums[i].childNodes.length > 0) {
                        phone_num = recommender_phone_nums[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="candidate"><a href="mailto: ' + recommender_emails[i].childNodes[0].nodeValue + '">' + recommenders[i].childNodes[0].nodeValue + '</a><br/><div class="phone_num"><strong>Tel:</strong> ' + phone_num + '<br/><strong>E-mail:</strong> ' + recommender_emails[i].childNodes[0].nodeValue + '</div></td>' + "\n";
                    html = html + '<td class="actions"><a class="no_link" onClick="show_profile(\'' + id + '\');">View Profile &amp; Resumes</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                    
                    var remark = '';
                    if (remarks[i].childNodes.length > 0) {
                        remark = 'Remarks: ' + remarks[i].childNodes[0].nodeValue;
                    }
                    html = html + '<tr>' + "\n";
                    html = html + '<td colspan="5" style="text-align: right; font-style: italic;">' + remark + '</td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
            }
            html = html + '</table>';
            
            $('div_candidates_list').set('html', html);
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading privileged requests...');
        }
    });
    
    request.send(params);
}

function show_resume_page(resume_id) {
    var popup = window.open('../employees/resume.php?id=' + resume_id, '', 'scrollbars');
    
    if (!popup) {
        alert('Popup blocker was detected. Please allow pop-up windows for YellowElevator.com and try again.');
    }
}

function show_current_candidate_profile() {
    show_profile(current_member_email_addr);
}

function show_profile(_member_email_addr) {
    current_member_email_addr = _member_email_addr;
    
    $('div_candidates').setStyle('display', 'none');
    $('div_candidate').setStyle('display', 'block');
    $('div_new_member_form').setStyle('display', 'none');
    $('div_upload_resume_form').setStyle('display', 'none');
    
    $('li_profile').setStyle('border', '1px solid #CCCCCC');
    $('li_resumes').setStyle('border', '1px solid #0000FF');
    $('div_profile').setStyle('display', 'block');
    $('div_resumes').setStyle('display', 'none');
    
    var params = 'id=' + current_member_email_addr + '&action=get_profile';
    
    var uri = root + "/prs/resumes_privileged_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading candidate.');
                return false;
            } 
            
            var member_firstname = xml.getElementsByTagName('member_firstname');
            var member_lastname = xml.getElementsByTagName('member_lastname');
            var recommender_firstname = xml.getElementsByTagName('recommender_firstname');
            var recommender_lastname = xml.getElementsByTagName('recommender_lastname');
            var member_phone_num = xml.getElementsByTagName('member_phone_num');
            var country = xml.getElementsByTagName('country');
            var zip = xml.getElementsByTagName('zip');
            var recommender_email_addr = xml.getElementsByTagName('recommender_email_addr');
            var recommender_phone_num = xml.getElementsByTagName('recommender_phone_num');
            var member_joined_on = xml.getElementsByTagName('formatted_joined_on');
            var member_checked_profile = xml.getElementsByTagName('checked_profile');
            var member_remarks = xml.getElementsByTagName('remarks');
            
            $('profile.joined_on').set('html', member_joined_on[0].childNodes[0].nodeValue);
            
            $('profile.checked_profile').set('html', '<br/>Pending verification&nbsp;');
            if (member_checked_profile[0].childNodes[0].nodeValue == 'Y') {
                $('profile.checked_profile').setStyle('display', 'none');
            }
            
            $('profile.firstname').value = member_firstname[0].childNodes[0].nodeValue;
            $('profile.lastname').value = member_lastname[0].childNodes[0].nodeValue;
            
            current_member_name = member_firstname[0].childNodes[0].nodeValue + ', ' + member_lastname[0].childNodes[0].nodeValue;
            
            $('profile.email_addr_label').set('html', current_member_email_addr);
            $('profile.email_addr').value = current_member_email_addr;
            
            $('profile.phone_num').value = member_phone_num[0].childNodes[0].nodeValue;
            
            $('profile.country').selectedIndex = 0;
            for (var i=0; i < $('profile.country').options.length; i++) {
                var option = $('profile.country').options[i].value;
                if (option == country[0].childNodes[0].nodeValue) {
                    $('profile.country').selectedIndex = i;
                    break;
                }
            }
            
            var remark = '';
            if (member_remarks[0].childNodes.length > 0) {
                remark = member_remarks[0].childNodes[0].nodeValue;
            }
            $('profile.remarks').value = remark;
            
            var zip_code = 'N/A';
            if (zip[0].childNodes.length > 0) {
                zip_code = zip[0].childNodes[0].nodeValue;
            }
            $('profile.zip').value = zip_code;
            
            $('profile.recommender.firstname').set('html', recommender_firstname[0].childNodes[0].nodeValue);
            $('profile.recommender.lastname').set('html', recommender_lastname[0].childNodes[0].nodeValue);
            $('profile.recommender.email_addr').set('html', recommender_email_addr[0].childNodes[0].nodeValue);
            
            var phone_num = 'N/A';
            if (recommender_phone_num[0].childNodes.length > 0) {
                phone_num = recommender_phone_num[0].childNodes[0].nodeValue;
            }
            $('profile.recommender.phone_num').set('html', phone_num);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading candidate...');
        }
    });
    
    request.send(params);
}

function show_current_candidate_resumes() {
    show_resumes(current_member_email_addr);
}

function show_resumes(_member_email_addr) {
    current_member_email_addr = _member_email_addr;
    
    $('div_candidates').setStyle('display', 'none');
    $('div_candidate').setStyle('display', 'block');
    $('div_new_member_form').setStyle('display', 'none');
    $('div_upload_resume_form').setStyle('display', 'none');
    
    $('li_resumes').setStyle('border', '1px solid #CCCCCC');
    $('li_profile').setStyle('border', '1px solid #0000FF');
    $('div_profile').setStyle('display', 'none');
    $('div_resumes').setStyle('display', 'block');
    
    var params = 'id=' + current_member_email_addr + '&action=get_resumes';
    params = params + '&order_by=' + resumes_order_by + ' ' + resumes_order;
    
    var uri = root + "/prs/resumes_privileged_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading resumes.');
                return false;
            } 
            
            var ids = xml.getElementsByTagName('id');
            var privates = xml.getElementsByTagName('private');
            var labels = xml.getElementsByTagName('name');
            var modified_ons = xml.getElementsByTagName('modified_date');
            var file_hashes = xml.getElementsByTagName('file_hash');
            var file_names = xml.getElementsByTagName('file_name');
            
            var html = '<table id="list" class="list">';
            if (ids.length <= 0) {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">Please click on the \"Upload Resume\" to upload resume.</div>';
            } else {
                for (var i=0; i < ids.length; i++) {
                    var resume_id = ids[i];
                    
                    html = html + '<tr id="'+ resume_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    
                    if (privates[i].childNodes[0].nodeValue == 'N') {
                        html = html + '<td class="private">&nbsp;</td>' + "\n";
                    } else {
                        html = html + '<td class="private">Private</td>' + "\n";
                    }
                    
                    html = html + '<td class="date">' + modified_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    if (file_hashes[i].childNodes.length > 0) {
                        html = html + '<td class="title"><span class="reupload"><a class="no_link" onClick="show_upload_resume_form(' + resume_id.childNodes[0].nodeValue + ');">Update File</a></span>&nbsp;<a href="resume.php?id=' + resume_id.childNodes[0].nodeValue + '&member=' + current_member_email_addr + '">' + labels[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    } else {
                        html = html + '<td class="title"><a class="no_link" onClick="show_resume_page(\'' + resume_id.childNodes[0].nodeValue + '\')">' + labels[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    }
                    
                    html = html + '<td class="actions"><a class="no_link" onClick="show_job_select_form(\'' + resume_id.childNodes[0].nodeValue + '\')">Refer Now</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_resumes_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading resumes...');
        }
    });
    
    request.send(params);
}

function save_profile() {
    if (isEmpty($('profile.firstname').value)) {
        alert('Firstname cannot be empty.');
        return false;
    }
    
    if (isEmpty($('profile.lastname').value)) {
        alert('Lastname cannot be empty.');
        return false;
    }
    
    if (isEmpty($('profile.phone_num').value)) {
        alert('Telephone cannot be empty.');
        return false;
    }
    
    if (isEmpty($('profile.zip').value)) {
        alert('Postal/Zip code cannot be empty.');
        return false;
    }
    
    if ($('profile.country').selectedIndex <= 0) {
        alert('A country must be chosen.');
        return false;
    }
    
    var params = 'id=' + id + '&user_id=' + user_id + '&action=save_profile';
    params = params + '&email_addr=' + $('profile.email_addr').value;
    params = params + '&firstname=' + $('profile.firstname').value;
    params = params + '&lastname=' + $('profile.lastname').value;
    params = params + '&phone_num=' + $('profile.phone_num').value;
    params = params + '&country=' + $('profile.country').options[$('profile.country').selectedIndex].value;
    params = params + '&zip=' + $('profile.zip').value;
    
    var uri = root + "/prs/resumes_privileged_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while saving profile. Please try again later.');
            }
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Saving profile...');
        }
    });
    
    request.send(params);
}

function add_new_candidate() {
    if (!validate_new_candidate_form()) {
        return false;
    }
    
    var params = 'id=' + id + '&user_id=' + user_id + '&action=add_new_candidate';
    params = params + '&member_email_addr=' + $('member_email_addr').value;
    params = params + '&member_firstname=' + $('member_firstname').value;
    params = params + '&member_lastname=' + $('member_lastname').value;
    params = params + '&member_phone_num=' + $('member_phone_num').value;
    params = params + '&member_country=' + $('country').options[$('country').selectedIndex].value;
    params = params + '&member_zip=' + $('zip').value;
    params = params + '&member_remarks=' + $('member_remarks').value;
    
    if ($('recommender_from_list').checked) {
        params = params + '&recommender_from=list';
        params = params + '&recommender_email_addr=' + $('recommender').options[$('recommender').selectedIndex].value;
    } else {
        params = params + '&recommender_from=new';
        params = params + '&recommender_email_addr=' + $('recommender_email_addr').value;
        params = params + '&recommender_firstname=' + $('recommender_firstname').value;
        params = params + '&recommender_lastname=' + $('recommender_lastname').value;
        params = params + '&recommender_phone_num=' + $('recommender_phone_num').value;
        params = params + '&recommender_remarks=' + $('recommender_remarks').value;
        params = params + '&recommender_region=' + $('recommender_region').value;
        
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
    }
    
    var uri = root + "/prs/resumes_privileged_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            switch (txt) {
                case '-1':
                    alert('Unable to create new recommender. No new candidate created.\n\nPlease try again later.');
                    break;
                case '-2':
                    alert('The candidate you want to create is already in the system.\n\nYou cannot overwrite or update once the candidate is created.');
                    show_candidates();
                    break;
                case '-3':
                    alert('Unable to create new candidate. No new candidate created.\n\nPlease try again later.')
                    break;
                case '-4':
                    alert('Unable to create new candidate activation token.\n\nThe new candidate has been created, but please contact system administrator to reset the candidate\'s token and password.');
                    show_candidates();
                    break;
                case '-5':
                    alert('Everything was created successfully, except for recommender\'s industries are not added into the system.\n\nPlease update through the Recommenders section.');
                    show_candidates();
                    break;
                case '-6':
                    alert('Everything was created successfully, except for default contact was not added into the system.\n\nPlease report this to system administrator');
                    show_candidates();
                    break;
                case '-7':
                    alert('Everything was created successfully, except for recommender\'s industries are not added into the system, and default contact was not added into the system.\n\nPlease update through the Recommenders section for the former, and report the latter to the system administrator.');
                    show_candidates();
                    break;
                default:
                    show_candidates();
                    break;
            }
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Saving candidate...');
        }
    });
    
    request.send(params);
}

function show_new_candidate_form() {
    $('div_candidates').setStyle('display', 'none');
    $('div_candidate').setStyle('display', 'none');
    $('div_new_member_form').setStyle('display', 'block');
    $('div_upload_resume_form').setStyle('display', 'none');
}

function upload_new_resume() {
    show_upload_resume_form(0);
}

function show_upload_resume_form(_resume_id) {
    $('candidate_name').set('html', current_member_name);
    $('resume_member_email_addr').value = current_member_email_addr;
    $('resume_id').value = '0';
    if (_resume_id > 0) {
        $('resume_id').value = _resume_id;
    }
    
    $('div_candidates').setStyle('display', 'none');
    $('div_candidate').setStyle('display', 'none');
    $('div_new_member_form').setStyle('display', 'none');
    $('div_upload_resume_form').setStyle('display', 'block');
}

function start_upload() {
    $('upload_progress').setStyle('display', 'block');
    set_status('Uploading resume...');
    return true;
}

function stop_upload(success) {
    var result = '';
    $('upload_progress').setStyle('display', 'none');
    if (success == 1) {
        show_current_candidate_resumes();
        return true;
    } else {
        set_status('An error occured while uploading the candidate\'s resume. Make sure the resume file meets the conditions stated below.');
        return false;
    }
}

function close_testimony_form() {
    $('div_testimony_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
    $('job_select_form.resume_id').value = '0';
}

function show_testimony_form() {
    if (isEmpty(jobs_list.selected_value)) {
        alert('You need to select a job.');
        return;
    }
    
    close_job_select_form();
    
    var job_title = '';
    for (var i=0; i < available_jobs.length; i++) {
        if (available_jobs[i].id == jobs_list.selected_value) {
            job_title = available_jobs[i].title;
            break;
        }
    }
    
    $('div_blanket').setStyle('display', 'block');
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_testimony_form').getStyle('height'));
    var div_width = parseInt($('div_testimony_form').getStyle('width'));
    
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
    
    $('div_testimony_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_testimony_form').setStyle('left', ((window_width - div_width) / 2));
    
    var testimony_form = $('div_testimony_form');
    var spans = testimony_form.getElementsByTagName('span');
    
    for (var i=0; i < spans.length; i++) {
        if (spans[i].id == 'testimony.candidate_name') {
            spans[i].innerHTML = current_member_name;
        }
        
        if (spans[i].id == 'testimony.job_title') {
            spans[i].innerHTML = job_title;
        } 
    }
    
    $('div_testimony_form').setStyle('display', 'block');
}

function close_job_select_form() {
    $('div_job_select_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_job_select_form(_resume_id) {
    $('div_blanket').setStyle('display', 'block');
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_job_select_form').getStyle('height'));
    var div_width = parseInt($('div_job_select_form').getStyle('width'));
    
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
    
    $('div_job_select_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_job_select_form').setStyle('left', ((window_width - div_width) / 2));
    
    $('job_select_form.candidate_name').set('html', current_member_name);
    $('job_select_form.resume_id').value = _resume_id;
    
    $('div_job_select_form').setStyle('display', 'block');
    show_available_jobs();
}

function set_filter() {
    filter_by = $('job_employer_filter').options[$('job_employer_filter').selectedIndex].value;
    show_available_jobs();
}

function refer() {
    var answer_1 = $('testimony_answer_1').value;
    var answer_2 = $('testimony_answer_2').value;
    var answer_3 = $('testimony_answer_3').value;
    var answer_4 = $('testimony_answer_4').value;
    var meet_requirements = ($('meet_req_yes').checked) ? 'Yes' : 'No';
    
    if (isEmpty(answer_1) || (meet_requirements == 'Yes' && isEmpty(answer_2)) || isEmpty(answer_3)) {
        alert('Please briefly answer all questions.');
        return false;
    } else if (answer_1.split(' ').length > 200 || answer_2.split(' ').length > 200 || 
               answer_3.split(' ').length > 200 || answer_4.split(' ').length > 200) {
        if (answer_1.split(' ').length > 200) {
            alert('Please keep your 1st answer below 200 words.');
        } else if (answer_2.split(' ').length > 200) {
            alert('Please keep your 2nd answer below 200 words.');
        } else if (answer_3.split(' ').length > 200) {
            alert('Please keep your 3rd answer below 200 words.');
        } else if (answer_4.split(' ').length > 200) {
            alert('Please keep your 4th and final answer below 200 words.');
        }
        return false;
    }
    
    var testimony = 'Experiences and Skillsets:<br/>' + answer_1 + '<br/><br/>';
    testimony = testimony + 'Meet Requirements: ' + meet_requirements + '<br/>Additional Comments:<br/>' + answer_2 + '<br/><br/>';
    testimony = testimony + 'Personaliy/Work Attitude:<br/>' + answer_3 + '<br/><br/>';
    testimony = testimony + 'Additional Recommendations: ' + ((isEmpty(answer_4)) ? 'None provided' : answer_4);
    
    var agreed = confirm('By clicking "OK", you confirm that you have screened the candidate\'s resume and have also assessed the candidate\'s suitability for this job position. Also, you acknowledge that the employer may contact you for further references regarding the candidate, and you agree to provide any other necessary information requested by the employer.\n\nOtherwise, you may click the "Cancel" button.');
    
    if (!agreed) {
        set_status('');
        close_testimony_form();
        return false;
    }
    
    var params = 'id=' + user_id + '&action=make_referral';
    params = params + '&referee=' + current_member_email_addr;
    params = params + '&job=' + jobs_list.selected_value;
    params = params + '&testimony=' + encodeURIComponent(testimony);
    params = params + '&resume=' + $('job_select_form.resume_id').value;
    
    var uri = root + "/prs/resumes_privileged_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            
            if (txt == 'ko') {
                alert('You have already referred this contact to the job. Please refer another contact.');
                close_testimony_form();
                set_status('');
                return false;
            } else if (txt == '-1') {
                alert('An error occurred when adding and approving contacts. Please contact system administrator.');
                close_testimony_form();
                set_status('');
                return false;
            } else if (txt == '-2') {
                alert('The candidate has removed Yellow Elevator as a contact. Please contact candidate for clarification.');
                close_testimony_form();
                set_status('');
                return false;
            }
            
            close_testimony_form();
            set_status('Referral successfully made.');
        },
        onRequest: function(instance) {
            set_status('Making referral...');
        }
    });
    
    request.send(params);
}

function set_mouse_events() {
    $('li_profile').addEvent('mouseover', function() {
        $('li_profile').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_profile').addEvent('mouseout', function() {
        $('li_profile').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_resumes').addEvent('mouseover', function() {
        $('li_resumes').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_resumes').addEvent('mouseout', function() {
        $('li_resumes').setStyles({
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
    
    $('li_back_1').addEvent('mouseover', function() {
        $('li_back_1').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_back_1').addEvent('mouseout', function() {
        $('li_back_1').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_back_2').addEvent('mouseover', function() {
        $('li_back_2').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_back_2').addEvent('mouseout', function() {
        $('li_back_2').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
}

function onDomReady() {
    initialize_page();
    list_available_industries('0');
    set_mouse_events();
    
    $('li_back').addEvent('click', show_candidates);
    $('li_back_1').addEvent('click', show_candidates);
    $('li_back_2').addEvent('click', show_current_candidate_resumes);
    $('li_profile').addEvent('click', show_current_candidate_profile);
    $('li_resumes').addEvent('click', show_current_candidate_resumes);
    
    $('add_new_candidate').addEvent('click', show_new_candidate_form);
    $('add_new_candidate_1').addEvent('click', show_new_candidate_form);
    
    $('save').addEvent('click', add_new_candidate);
    $('save_profile').addEvent('click', save_profile);
    
    $('upload_new_resume').addEvent('click', upload_new_resume);
    $('upload_new_resume_1').addEvent('click', upload_new_resume);
    
    $('jobs').addEvent('click', show_job_details);
    
    $('testimony_answer_1').addEvent('keypress', function() {
       update_word_count_of('word_count_q1', 'testimony_answer_1') 
    });

    $('testimony_answer_2').addEvent('keypress', function() {
       update_word_count_of('word_count_q2', 'testimony_answer_2') 
    });
    
    $('testimony_answer_3').addEvent('keypress', function() {
       update_word_count_of('word_count_q3', 'testimony_answer_3') 
    });
    
    $('testimony_answer_4').addEvent('keypress', function() {
       update_word_count_of('word_count_q4', 'testimony_answer_4') 
    });
    
    $('sort_joined_on').addEvent('click', function() {
        order_by = 'members.joined_on';
        ascending_or_descending();
        show_candidates();
    });
    
    $('sort_added_by').addEvent('click', function() {
        order_by = 'employees.lastname';
        ascending_or_descending();
        show_candidates();
    });
    
    $('sort_candidate').addEvent('click', function() {
        order_by = 'members.lastname';
        ascending_or_descending();
        show_candidates();
    });
    
    $('sort_recommender').addEvent('click', function() {
        order_by = 'recommenders.lastname';
        ascending_or_descending();
        show_candidates();
    });
    
    $('sort_resumes_label').addEvent('click', function() {
        resumes_order_by = 'name';
        resumes_ascending_or_descending();
        show_current_candidate_resumes();
    });
    
    $('sort_resumes_modified_on').addEvent('click', function() {
        resumes_order_by = 'modified_on';
        ascending_or_descending();
        show_current_candidate_resumes();
    });
    
    if (!isEmpty(candidate_id)) {
        show_profile(candidate_id);
    } else {
        show_candidates();
    }
}

window.addEvent('domready', onDomReady);
