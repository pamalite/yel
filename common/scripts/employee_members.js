var current_section = 'new_applicants';
var new_applicants_order_by = 'referral_buffers.requested_on';
var new_applicants_order = 'desc';
var new_applicants_filter = '';
var applications_filter = '';
var applicants_order_by = 'member_jobs.applied_on';
var applicants_order = 'desc';
var applicants_filter = '';
var members_order_by = 'members.lastname';
var members_order = 'asc';
var members_filter = '';
var filter_by_employer_only = true;
var filter_only_non_attached = false;
var filter_is_dirty = false;
var applicants_page = 1;
var new_applicants_page = 1;
var sliding_filter_fx = '';
var sliding_search_fx = '';
var return_page = '';

function new_applicants_ascending_or_descending() {
    if (new_applicants_order == 'desc') {
        new_applicants_order = 'asc';
    } else {
        new_applicants_order = 'desc';
    }
}

function applicants_ascending_or_descending() {
    if (applicants_order == 'desc') {
        applicants_order = 'asc';
    } else {
        applicants_order = 'desc';
    }
}

function members_ascending_or_descending() {
    if (members_order == 'desc') {
        members_order = 'asc';
    } else {
        members_order = 'desc';
    }
}

function sort_by(_table, _column) {
    switch (_table) {
        case 'applicants':
            applicants_order_by = _column;
            applicants_ascending_or_descending();
            update_applicants();
            break;
        case 'new_applicants':
            new_applicants_order_by = _column;
            new_applicants_ascending_or_descending();
            update_new_applicants();
            break;
        case 'members':
            members_order_by = _column;
            members_ascending_or_descending();
            update_members();
            break;
    }
}

function toggle_main_filter() {
    sliding_filter_fx.toggle();
}

function toggle_search() {
    sliding_search_fx.toggle();
}

function swap_filter_with_search(_is_search) {
    if (_is_search) {
        $('div_main_filter').setStyle('display', 'none');
        $('div_main_filter_toggle').setStyle('display', 'none');
        // $('div_search_form').setStyle('display', 'block');
        // $('div_search_filter_toggle').setStyle('display', 'block');
    } else {
        $('div_main_filter').setStyle('display', 'block');
        $('div_main_filter_toggle').setStyle('display', 'block');
        // $('div_search_form').setStyle('display', 'none');
        // $('div_search_filter_toggle').setStyle('display', 'none');
    }
}

function populate_jobs_list() {
    var ids = '';
    for (var i=0; i < $('employers').options.length; i++) {
        if ($('employers').options[i].selected) {
            ids = ids + $('employers').options[i].value + ',';
        }
    }
    
    if (isEmpty(ids)) {
        alert('You must at least select an employer.');
        return;
    }
    
    ids = ids.substr(0, ids.length-1);
    
    var params = 'id=0&employer_ids=' + ids;
    params = params + '&action=get_jobs';
    
    var uri = root + "/employees/members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            // set_status('<pre>' + txt + '</pre>');
            // return;
            if (txt == 'ko') {
                alert('An error occured while getting jobs.');
                return false;
            }
            
            if (txt == '0') {
                $('jobs_list_message_box').set('html', 'Please select another employer. There are no jobs published.');
                $('jobs_list').setStyle('border', '1px dashed #888888');
                $('jobs_list_message_box').setStyle('display', 'block');
                $('jobs_list_placeholder').setStyle('display', 'none');
            } else {
                var titles = xml.getElementsByTagName('job_title');
                var ids = xml.getElementsByTagName('id');
                var employer_ids = xml.getElementsByTagName('employer');
                
                $('jobs').length = 0;
                for (var i=0; i < ids.length; i++) {
                    var title = '[' + employer_ids[i].childNodes[0].nodeValue + '][' + ids[i].childNodes[0].nodeValue + '] ' + titles[i].childNodes[0].nodeValue;
                    var option = new Option(title, ids[i].childNodes[0].nodeValue );
                    $('jobs').options[$('jobs').options.length] = option;
                }
                
                $('jobs_list').setStyle('border', 'none');
                $('jobs_list_message_box').setStyle('display', 'none');
                $('jobs_list_placeholder').setStyle('display', 'block');
                set_status('');
            }
        },
        onRequest: function(instance) {
            set_status('Loading jobs...');
        }
    });
    
    request.send(params);
}

function toggle_add_button() {
    var has_selected = false;
    for (var i=0; i < $('jobs').options.length; i++) {
        if ($('jobs').options[i].selected) {
            has_selected = true;
            break;
        }
    }
    
    if (has_selected) {
        $('add_new_btn').disabled = false;
    } else {
        $('add_new_btn').disabled = true;
    }
}

function do_filter() {
    filter_is_dirty = true;
    
    if (current_section == 'applicants') {
        filter_applicants();
    } else if (current_section == 'new_applicants') {
        filter_new_applicants();
    }
}

function filter_applicants() {
    filter_only_non_attached = false;
    filter_by_employer_only = true;
    if ($('jobs') != null) {
        for (var i=0; i < $('jobs').options.length; i++) {
            if ($('jobs').options[i].selected) {
                filter_by_employer_only = false;
                break;
            }
        }
    }
    
    applicants_filter = '';
    if (filter_by_employer_only) {
        for (var i=0; i < $('employers').options.length; i++) {
            if ($('employers').options[i].selected) {
                applicants_filter = applicants_filter + $('employers').options[i].value + ',';
            }
        }
    } else {
        for (var i=0; i < $('jobs').options.length; i++) {
            if ($('jobs').options[i].selected) {
                applicants_filter = applicants_filter + $('jobs').options[i].value + ',';
            }
        }
    }
    
    if (!isEmpty(applicants_filter)) {
        applicants_filter = applicants_filter.substr(0, applicants_filter.length-1);
    }
    
    update_applicants();
}

function show_applicants() {
    current_section = 'applicants';
    return_page = 'applicants';
    
    $('add_new_btn').setStyle('visibility', 'hidden');
    
    $('new_applicants').setStyle('display', 'none');
    $('applicants').setStyle('display', 'block');
    $('members').setStyle('display', 'none');
    
    $('item_new_applicants').setStyle('background-color', '');
    $('item_applicants').setStyle('background-color', '#CCCCCC');
    $('item_members').setStyle('background-color', '');
    
    swap_filter_with_search(false);
    
    if (filter_is_dirty) {
        filter_is_dirty = false;
        filter_applicants();
    }
}

function update_applicants() {
    var selected_page = 1;
    var selected_page_index = $('applicants_pages').selectedIndex;
    if ($('applicants_pages').options.length > 0) {
        selected_page = $('applicants_pages').options[selected_page_index].value;
    }
    
    var params = 'id=' + user_id + '&order_by=' + applicants_order_by + ' ' + applicants_order;
    params = params + '&action=get_applicants';
    params = params + '&page=' + selected_page;
    
    if (filter_only_non_attached) {
        params = params + '&non_attached=1';
    }
    
    if (!isEmpty(applicants_filter)) {
        if (filter_by_employer_only) {
            params = params + '&employers=' + applicants_filter;
        } else {
            params = params + '&jobs=' + applicants_filter;
        }
    }
    
    var uri = root + "/employees/members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            // set_status('<pre>' + txt + '</pre>');
            // return;
            if (txt == 'ko') {
                alert('An error occured while loading applicants.');
                return false;
            }
            
            $('applicants_pages').length = 0;
            $('total_applicants_pages').set('html', '0');
            
            if (txt == '0') {
                set_status('');
                $('div_applicants').set('html', '<div class="empty_results">No applicants to show.</div>');
            } else {
                var total_pages = xml.getElementsByTagName('total_pages');
                
                $('total_applicants_pages').set('html', total_pages[0].childNodes[0].nodeValue);
                if (selected_page_index >= parseInt(total_pages[0].childNodes[0].nodeValue)) {
                    selected_page_index = parseInt(total_pages[0].childNodes[0].nodeValue)-1;
                }
                
                for (var i=0; i < parseInt(total_pages[0].childNodes[0].nodeValue); i++) {
                    var an_option = '';
                    if (i == selected_page_index) {
                        an_option = new Option((i+1), (i+1), true, true);
                    } else {
                        an_option = new Option((i+1), (i+1));
                    }
                    $('applicants_pages').options[$('applicants_pages').length] = an_option;
                }
                
                var ids = xml.getElementsByTagName('member_job_id');
                var emails = xml.getElementsByTagName('email_addr');
                var members = xml.getElementsByTagName('member_name');
                var phone_nums = xml.getElementsByTagName('phone_num');
                var progress_notes = xml.getElementsByTagName('progress_notes');
                var job_ids = xml.getElementsByTagName('job_id');
                var employer_ids = xml.getElementsByTagName('employer_id');
                var job_titles = xml.getElementsByTagName('job_title');
                var resume_ids = xml.getElementsByTagName('resume_id');
                var resumes = xml.getElementsByTagName('resume_name');
                var applied_resume_ids = xml.getElementsByTagName('app_resume_id');
                var applied_resumes = xml.getElementsByTagName('app_resume_name');
                var yel_resumes = xml.getElementsByTagName('num_yel_resumes');
                var self_resumes = xml.getElementsByTagName('num_self_resumes');
                var applied_jobs = xml.getElementsByTagName('num_attached_jobs');
                var referred_ons = xml.getElementsByTagName('formatted_referred_on');
                var applied_ons = xml.getElementsByTagName('formatted_applied_on');
                var agreed_terms_ons = xml.getElementsByTagName('formatted_employer_agreed_terms_on');
                var employed_ons = xml.getElementsByTagName('formatted_employed_on');
                var employer_rejected_ons = xml.getElementsByTagName('formatted_employer_rejected_on');
                
                var applicants_table = new FlexTable('applicants_table', 'applicants');

                var header = new Row('');
                header.set(0, new Cell('&nbsp;', '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('applicants', 'member_jobs.applied_on');\">Applied On</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('applicants', 'members.lastname');\">Member</a>", '', 'header'));
                header.set(3, new Cell('Job Applied', '', 'header'));
                header.set(4, new Cell('Resume', '', 'header'));
                header.set(5, new Cell('Status', '', 'header'));
                header.set(6, new Cell('Progress', '', 'header'));
                applicants_table.set(0, header);
                
                for (var i=0; i < emails.length; i++) {
                    var row = new Row('');
                    
                    // delete
                    if (referred_ons[i].childNodes.length <= 0) {
                        row.set(0, new Cell('<input type="button" value="delete" onClick="delete_application(\'' + ids[i].childNodes[0].nodeValue + '\', false);" />', '', 'cell'));
                    } else {
                        row.set(0, new Cell('<input type="button" value="delete" disabled/>', '', 'cell'));
                    }
                    
                    // applied on
                    row.set(1, new Cell(applied_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    // member details
                    var short_desc = '<a class="member_link" href="member.php?member_email_addr=' + emails[i].childNodes[0].nodeValue + '&page=career" target="_new">' + members[i].childNodes[0].nodeValue + '</a>' + "\n";
                    
                    var phone_num = '';
                    if (phone_nums[i].childNodes.length > 0) {
                        phone_num = phone_nums[i].childNodes[0].nodeValue;
                    }
                    short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Tel.:</span> ' + phone_num + '</div>' + "\n";
                    
                    short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Email:</span> <a href="mailto:' + emails[i].childNodes[0].nodeValue + '">' + emails[i].childNodes[0].nodeValue + '</a></div>' + "\n";
                    short_desc = short_desc + '<br/><a href="member.php?member_email_addr=' + emails[i].childNodes[0].nodeValue + '&page=referrers" target="_new">View Referrers</a>' + "\n";
                    row.set(2, new Cell(short_desc, '', 'cell'));
                    
                    // job applied
                    var job_details = '[' + employer_ids[i].childNodes[0].nodeValue + '] ' + job_titles[i].childNodes[0].nodeValue + '<br/><br/>';
                    if (parseInt(applied_jobs[i].childNodes[0].nodeValue) > 1) {
                        job_details = job_details + '<a class="no_link" onClick="show_jobs_popup(false, \'' + emails[i].childNodes[0].nodeValue + '\', true);">View others</a>';
                    }
                    row.set(3, new Cell(job_details, '', 'cell'));
                    
                    // resume details
                    var resume_details = '<a href="resume.php?id=' + applied_resume_ids[i].childNodes[0].nodeValue + '">Applied</a>';
                    
                    if (resume_ids[i].childNodes.length > 0) {
                        resume_details = '<a href="resume.php?id=' + resume_ids[i].childNodes[0].nodeValue + '">Submitted</a>';
                    }
                    
                    resume_details = resume_details + '&nbsp;|&nbsp;<a class="no_link" onClick="show_resumes_page(\'' + add_slashes(emails[i].childNodes[0].nodeValue) + '\')">All/Refer</a><br/><br/>';
                    resume_details = resume_details + '<span style="color: #666666;">YEL: ' + yel_resumes[i].childNodes[0].nodeValue + "</span><br/>\n";
                    resume_details = resume_details + '<span style="color: #666666;">Self: ' + self_resumes[i].childNodes[0].nodeValue + "</span><br/>\n";
                    row.set(4, new Cell(resume_details, '', 'cell'));
                    
                    // status
                    var status = 'N/A';
                    if (referred_ons[i].childNodes.length > 0) {
                        status = '<span class="referred">Referred On:</span> ' + referred_ons[i].childNodes[0].nodeValue;
                    }
                    
                    if (agreed_terms_ons[i].childNodes.length > 0) {
                        status = status + '<br/><span class="viewed">Viewed On:</span> ' + agreed_terms_ons[i].childNodes[0].nodeValue;
                    }
                    
                    if (employed_ons[i].childNodes.length > 0) {
                        status = status + '<br/><span class="employed">Employed On:</span> ' + employed_ons[i].childNodes[0].nodeValue;
                    } else if (employer_rejected_ons[i].childNodes.length > 0) {
                        status = status + '<br/><span class="rejected">Rejected On:</span> ' + employer_rejected_ons[i].childNodes[0].nodeValue;
                    }
                    row.set(5, new Cell(status, '', 'cell'));
                    
                    // progress
                    var progress = '<a class="no_link" onClick="show_progress_popup(\'' + ids[i].childNodes[0].nodeValue + '\', \'0\');">Add</a>';
                    if (progress_notes[i].childNodes.length > 0) {
                        var lines = progress_notes[i].childNodes[0].nodeValue.split("\n");
                        var notes_str = '';
                        for (var l=0; l < lines.length; l++) {
                            notes_str = notes_str + lines[l] + '<br/>';
                        }
                        progress = '<div class="progress_cell">' + notes_str  + '</div>';
                        progress = progress + '<br/><a class="no_link" onClick="show_progress_popup(\'' + ids[i].childNodes[0].nodeValue + '\', \'0\');">Update</a>';
                    }
                    row.set(6, new Cell(progress, '', 'cell progress_cell'));
                    
                    applicants_table.set((parseInt(i)+1), row);
                }
                
                $('div_applicants').set('html', applicants_table.get_html());
                set_status('');
            }
        },
        onRequest: function(instance) {
            set_status('Loading applicants...');
        }
    });
    
    request.send(params);    
}

function reset_password(_id) {
    var proceed = confirm('Are you sure to reset the password?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=' + _id;
    params = params + '&action=reset_password';
    
    var uri = root + "/employees/members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while reseting password.');
                return false;
            }
            
            set_status('Password successfully reset! An e-mail has been send to the member. ');
        },
        onRequest: function(instance) {
            set_status('Resetting password...');
        }
    });
    
    request.send(params);
}

function deactivate_member(_id, _idx) {
    var proceed = confirm('Are you sure to de-activate member?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=' + _id + '&action=deactivate';
    
    var uri = root + "/employees/members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while deactivating member.');
                return false;
            }
            
            set_status('');
            $('activate_button_' + _idx).value = 'Activate';
            $('password_reset_' + _idx).disabled = true;
        },
        onRequest: function(instance) {
            set_status('De-activating member...');
        }
    });
    
    request.send(params);
}

function activate_member(_id, _idx) {
    if ($('activate_button_' + _idx).value == 'De-activate') {
        return deactivate_member(_id, _idx);
    }
    
    var proceed = confirm('Are you sure to activate member?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=' + _id + '&action=activate';
    
    var uri = root + "/employees/members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while activating member.');
                return false;
            }
            
            set_status('');
            $('activate_button_' + _idx).value = 'De-activate';
            $('password_reset_' + _idx).disabled = false;
        },
        onRequest: function(instance) {
            set_status('Activating member...');
        }
    });
    
    request.send(params);
}

function show_member(_email_addr) {
    $('member_email_addr').value = _email_addr;
    $('member_page_form').submit();
}

function add_new_member() {
    $('member_email_addr').value = '';
    $('member_page_form').submit();
}

function show_non_attached() {
    new_applicants_filter = '';
    applicants_filter = '';
    members_filter = '';
    filter_only_non_attached = true;
    
    if (current_section == 'applicants') {
        update_applicants();
    } else {
        update_new_applicants();
    }
}

function filter_new_applicants() {
    filter_only_non_attached = false;
    applications_filter = $('applications_filter').options[$('applications_filter').selectedIndex].value;
    
    filter_by_employer_only = true;
    if ($('jobs') != null) {
        for (var i=0; i < $('jobs').options.length; i++) {
            if ($('jobs').options[i].selected) {
                filter_by_employer_only = false;
                break;
            }
        }
    }
    
    new_applicants_filter = '';
    if (filter_by_employer_only) {
        for (var i=0; i < $('employers').options.length; i++) {
            if ($('employers').options[i].selected) {
                new_applicants_filter = new_applicants_filter + $('employers').options[i].value + ',';
            }
        }
    } else {
        for (var i=0; i < $('jobs').options.length; i++) {
            if ($('jobs').options[i].selected) {
                new_applicants_filter = new_applicants_filter + $('jobs').options[i].value + ',';
            }
        }
    }
    
    if (!isEmpty(new_applicants_filter)) {
        new_applicants_filter = new_applicants_filter.substr(0, new_applicants_filter.length-1);
    }
    
    update_new_applicants();
}

function show_new_applicants() {
    current_section = 'new_applicants';
    return_page = '';
    
    $('add_new_btn').setStyle('visibility', 'visible');
    
    $('new_applicants').setStyle('display', 'block');
    $('applicants').setStyle('display', 'none');
    $('members').setStyle('display', 'none');
    
    $('item_new_applicants').setStyle('background-color', '#CCCCCC');
    $('item_applicants').setStyle('background-color', '');
    $('item_members').setStyle('background-color', '');
    
    swap_filter_with_search(false);
    
    if (filter_is_dirty) {
        filter_is_dirty = false;
        filter_new_applicants();
    }
}

function update_new_applicants() {
    var selected_page = 1;
    var selected_page_index = $('pages').selectedIndex;
    if ($('pages').options.length > 0) {
        selected_page = $('pages').options[selected_page_index].value;
    }
    
    var params = 'id=' + user_id + '&order_by=' + new_applicants_order_by + ' ' + new_applicants_order;
    params = params + '&action=get_new_applicants';
    params = params + '&show_only=' + applications_filter;
    params = params + '&page=' + selected_page;
    
    if (filter_only_non_attached) {
        params = params + '&non_attached=1';
    }
    
    if (!isEmpty(new_applicants_filter)) {
        if (filter_by_employer_only) {
            params = params + '&employers=' + new_applicants_filter;
        } else {
            params = params + '&jobs=' + new_applicants_filter;
        }
    }
    
    var uri = root + "/employees/members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            //set_status('<pre>' + txt + '</pre>');
            //return;
            if (txt == 'ko') {
                alert('An error occured while loading new applicants.');
                return false;
            }
            
            $('pages').length = 0;
            $('total_pages').set('html', '0');
            
            if (txt == '0') {
                set_status('');
                $('applications_filter').disabled = true;
                $('div_new_applicants').set('html', '<div class="empty_results">No new applicants to show.</div>');
            } else {
                $('applications_filter').disabled = false;
                
                var total_pages = xml.getElementsByTagName('total_pages');
                
                $('total_pages').set('html', total_pages[0].childNodes[0].nodeValue);
                if (selected_page_index >= parseInt(total_pages[0].childNodes[0].nodeValue)) {
                    selected_page_index = parseInt(total_pages[0].childNodes[0].nodeValue)-1;
                }
                
                for (var i=0; i < parseInt(total_pages[0].childNodes[0].nodeValue); i++) {
                    var an_option = '';
                    if (i == selected_page_index) {
                        an_option = new Option((i+1), (i+1), true, true);
                    } else {
                        an_option = new Option((i+1), (i+1));
                    }
                    $('pages').options[$('pages').length] = an_option;
                }
                
                var ids = xml.getElementsByTagName('id');
                var referrer_emails = xml.getElementsByTagName('referrer_email');
                var referrer_names = xml.getElementsByTagName('referrer_name');
                var referrer_phones = xml.getElementsByTagName('referrer_phone');
                var candidate_emails = xml.getElementsByTagName('candidate_email');
                var candidate_names = xml.getElementsByTagName('candidate_name');
                var candidate_phones = xml.getElementsByTagName('candidate_phone');
                var resume_ids = xml.getElementsByTagName('existing_resume_id');
                var resume_file_hashes = xml.getElementsByTagName('resume_file_hash');
                var requested_ons = xml.getElementsByTagName('formatted_requested_on');
                var progress_notes = xml.getElementsByTagName('progress_notes');
                var jobs = xml.getElementsByTagName('job');
                var employers = xml.getElementsByTagName('employer');
                var applied_jobs = xml.getElementsByTagName('num_jobs_attached');
                var is_members = xml.getElementsByTagName('is_member');
                
                var new_applicants_table = new FlexTable('new_applicants_table', 'new_applicants');

                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('new_applicants', 'referral_buffers.requested_on');\">Created On</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('new_applicants', 'referral_buffers.candidate_name');\">Applicant</a>", '', 'header'));
                header.set(2, new Cell('Job Applied', '', 'header'));
                header.set(3, new Cell('Resume', '', 'header'));
                header.set(4, new Cell('Progress', '', 'header'));
                header.set(5, new Cell('&nbsp;', '', 'header action'));
                new_applicants_table.set(0, header);
                
                for (var i=0; i < ids.length; i++) {
                    var is_cannot_signup = false;
                    var row = new Row('');
                    
                    // requested on
                    row.set(0, new Cell(requested_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    // applicant details
                    // referrer link
                    var referrer_short_details = '';
                    if (referrer_emails[i].childNodes.length > 0 && 
                        referrer_emails[i].childNodes[0].nodeValue.substr(0, 5) == 'team.' &&
                        referrer_emails[i].childNodes[0].nodeValue.substr(7) == '@yellowelevator.com') {
                        referrer_short_details = '<div class="tiny_contact">Self Applied</div>' + "\n";
                    } else {
                        var referrer_phone_num = '';
                        var referrer_email = '';
                        if (referrer_phones[i].childNodes.length > 0) {
                            referrer_phone_num = referrer_phones[i].childNodes[0].nodeValue;
                        }

                        if (referrer_emails[i].childNodes.length > 0) {
                            referrer_email = referrer_emails[i].childNodes[0].nodeValue;
                        }

                        if (isEmpty(referrer_phone_num) || isEmpty(referrer_email)) {
                            is_cannot_signup = true;
                            referrer_short_details = '<div class="tiny_contact">Ref: <a class="no_link" onClick="show_referrer_popup(' + ids[i].childNodes[0].nodeValue + ');">' + referrer_names[i].childNodes[0].nodeValue + ' (Incomplete!)</a></div>' + "\n";
                        } else {
                            referrer_short_details = '<div class="tiny_contact">Ref: <a class="no_link" onClick="show_referrer_popup(' + ids[i].childNodes[0].nodeValue + ');">' + referrer_names[i].childNodes[0].nodeValue + '</a></div>' + "\n";
                        }
                    }
                    
                    // candidate details
                    var candidate_phone_num = '';
                    var candidate_email = '';
                    if (candidate_phones[i].childNodes.length > 0) {
                        candidate_phone_num = candidate_phones[i].childNodes[0].nodeValue;
                    }
                    
                    if (candidate_emails[i].childNodes.length > 0) {
                        candidate_email = candidate_emails[i].childNodes[0].nodeValue;
                    }
                    
                    var short_desc = '<span style="font-weight: bold;">' + candidate_names[i].childNodes[0].nodeValue + '</span>' + "\n";
                    
                    if (isEmpty(candidate_phone_num)) {
                        is_cannot_signup = true;
                        short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Tel.:</span> <a class="no_link small_contact_edit" onClick="edit_candidate_phone(' + ids[i].childNodes[0].nodeValue + ');">Add Phone Number</a></div>' + "\n";
                    } else {
                        short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Tel.:</span> ' + candidate_phone_num + ' <a class="no_link small_contact_edit" onClick="edit_candidate_phone(' + ids[i].childNodes[0].nodeValue + ');">edit</a></div>' + "\n";
                    }
                    
                    if (isEmpty(candidate_email)) {
                        is_cannot_signup = true;
                        short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Email:</span> <a class="no_link small_contact_edit" onClick="edit_candidate_email(' + ids[i].childNodes[0].nodeValue + ');">Add Email</a></div>' + "\n";
                    } else {
                        short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Email:</span><a href="mailto:' + candidate_email + '"> ' + candidate_email + '</a> <a class="no_link small_contact_edit" onClick="edit_candidate_email(' + ids[i].childNodes[0].nodeValue + ');">edit</a></div>' + "\n";
                    }
                    
                    var candidate_details = short_desc + '<br />' + referrer_short_details;
                    row.set(1, new Cell(candidate_details, '', 'cell'));
                    
                    // job applied
                    var job_desc = 'N/A';
                    if (jobs[i].childNodes.length > 0) {
                        job_desc = '[' + employers[i].childNodes[0].nodeValue + '] ' + jobs[i].childNodes[0].nodeValue;
                    }
                    
                    if (parseInt(applied_jobs[i].childNodes[0].nodeValue) > 1) {
                        if (candidate_emails[i].childNodes.length <= 0) {
                            job_desc = job_desc + '<br/><br/><a class="no_link" onClick="show_jobs_popup(false, \'' + candidate_names[i].childNodes[0].nodeValue + '\');">View other jobs</a>'; 
                        } else {
                            job_desc = job_desc + '<br/><br/><a class="no_link" onClick="show_jobs_popup(true, \'' + candidate_emails[i].childNodes[0].nodeValue + '\');">View other jobs</a>'; 
                        }
                    }
                    row.set(2, new Cell(job_desc, '', 'cell'));
                    
                    // resume
                    if (resume_ids[i].childNodes.length > 0) {
                        row.set(3, new Cell('<a href="resume.php?id=' + resume_ids[i].childNodes[0].nodeValue + '">View Resume</a>', '', 'cell'));
                    } else if (resume_file_hashes[i].childNodes.length > 0) {
                        row.set(3, new Cell('<a href="resume.php?id=' + ids[i].childNodes[0].nodeValue + '&hash=' + resume_file_hashes[i].childNodes[0].nodeValue + '">View Resume</a>', '', 'cell'));
                    } else {
                        row.set(3, new Cell('N/A', '', 'cell'));
                    }
                    
                    // progress
                    var progress = '<a class="no_link" onClick="show_progress_popup(\'' + ids[i].childNodes[0].nodeValue + '\', \'1\');">Add</a>';
                    if (progress_notes[i].childNodes.length > 0) {
                        var lines = progress_notes[i].childNodes[0].nodeValue.split("\n");
                        var notes_str = '';
                        for (var l=0; l < lines.length; l++) {
                            notes_str = notes_str + lines[l] + '<br/>';
                        }
                        progress = '<div class="progress_cell">' + notes_str  + '</div>';
                        progress = progress + '<br/><a class="no_link" onClick="show_progress_popup(\'' + ids[i].childNodes[0].nodeValue + '\', \'1\');">Update</a>';
                    }
                    row.set(4, new Cell(progress, '', 'cell progress_cell'));
                    
                    // actions
                    var actions = '';
                    actions = '<input type="button" value="Delete" onClick="delete_application(\'' + ids[i].childNodes[0].nodeValue + '\', true);" />';
                    
                    if (is_cannot_signup) {
                        actions = actions + '<input type="button" value="Sign Up" disabled />';
                    } else {
                        if (is_members[i].childNodes[0].nodeValue == '0') {
                            actions = actions + '<input type="button" value="Sign Up" onClick="make_member_from(\'' + ids[i].childNodes[0].nodeValue + '\');" />';
                        } else {
                            actions = actions + '<input type="button" value="Transfer" onClick="make_member_from(\'' + ids[i].childNodes[0].nodeValue + '\');" />';
                        }
                    }
                    row.set(5, new Cell(actions, '', 'cell action'));
                    
                    new_applicants_table.set((parseInt(i)+1), row);
                }
                
                $('div_new_applicants').set('html', new_applicants_table.get_html());
                set_status('');
            }
        },
        onRequest: function(instance) {
            set_status('Loading new applicants...');
        }
    });
    
    request.send(params);
}

function show_members() {
    $('new_applicants').setStyle('display', 'none');
    $('applicants').setStyle('display', 'none');
    $('members').setStyle('display', 'block');
    
    $('item_new_applicants').setStyle('background-color', '');
    $('item_applicants').setStyle('background-color', '');
    $('item_members').setStyle('background-color', '#CCCCCC');
    
    swap_filter_with_search(true);
}

function update_members() {
    var is_show_all = false;
    var selected_page = 1;
    var selected_page_index = $('members_pages').selectedIndex;
    if ($('members_pages').options.length > 0) {
        selected_page = $('members_pages').options[selected_page_index].value;
    }
    
    if (arguments.length == 1) {
        is_show_all = true;
        selected_page = 1;
    }
    
    var params = 'id=0&order_by=' + members_order_by + ' ' + members_order;
    params = params + '&action=get_members';
    params = params + '&page=' + selected_page;
    
    if (!is_show_all) {
        params = params + '&show_all=0';
        params = params + '&email=' + $('search_email').value;
        params = params + '&name=' + encodeURIComponent($('search_name').value);
        params = params + '&position=' + encodeURIComponent($('search_position').value);
        params = params + '&employer=' + encodeURIComponent($('search_employer').value);
        params = params + '&total_work_years=' + $('search_total_years').value;
        params = params + '&notice_period=' + $('search_notice_period').value;
        params = params + '&exp_sal_currency=' + $('search_expected_salary_currency').options[$('search_expected_salary_currency').selectedIndex].value;
        params = params + '&exp_sal_start=' + $('search_expected_salary_start').value;
        params = params + '&exp_sal_end=' + $('search_expected_salary_end').value;
        params = params + '&specialization=' + $('search_specialization').options[$('search_specialization').selectedIndex].value;
        params = params + '&emp_specialization=' + $('search_emp_specialization').options[$('search_emp_specialization').selectedIndex].value;
        params = params + '&emp_desc=' + $('search_emp_desc').options[$('search_emp_desc').selectedIndex].value;
        params = params + '&seeking=' + encodeURIComponent($('search_seeking').value);
    } else {
        params = params + '&show_all=1';
    }
    
    var uri = root + "/employees/members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            // set_status('<pre>' + txt + '</pre>');
            // return;
            set_status('');
            
            if (txt == 'ko') {
                alert('An error occured while loading members.');
                return false;
            }
            
            $('members_pages').length = 0;
            $('total_members_pages').set('html', '0');
            
            if (txt == '0') {
                set_status('');
                $('div_members').set('html', '<div class="empty_results">No members to show.</div>');
            } else {
                var total_pages = xml.getElementsByTagName('total_pages');
                
                $('total_members_pages').set('html', total_pages[0].childNodes[0].nodeValue);
                if (selected_page_index >= parseInt(total_pages[0].childNodes[0].nodeValue)) {
                    selected_page_index = parseInt(total_pages[0].childNodes[0].nodeValue)-1;
                }
                
                for (var i=0; i < parseInt(total_pages[0].childNodes[0].nodeValue); i++) {
                    var an_option = '';
                    if (i == selected_page_index) {
                        an_option = new Option((i+1), (i+1), true, true);
                    } else {
                        an_option = new Option((i+1), (i+1));
                    }
                    $('members_pages').options[$('members_pages').length] = an_option;
                }
                
                var emails = xml.getElementsByTagName('email_addr');
                var members = xml.getElementsByTagName('member_name');
                var phone_nums = xml.getElementsByTagName('phone_num');
                var is_actives = xml.getElementsByTagName('active');
                var joined_ons = xml.getElementsByTagName('formatted_joined_on');
                var total_years = xml.getElementsByTagName('total_work_years');
                var is_seeking_jobs = xml.getElementsByTagName('is_active_seeking_job');
                var can_travels = xml.getElementsByTagName('can_travel_relocate');
                var notice_periods = xml.getElementsByTagName('notice_period');
                var exp_sal_currencies = xml.getElementsByTagName('expected_salary_currency');
                var exp_sal_starts = xml.getElementsByTagName('expected_salary');
                var exp_sal_ends = xml.getElementsByTagName('expected_salary_end');
                
                var members_table = new FlexTable('members_table', 'members');

                var header = new Row('');
                header.set(0, new Cell('&nbsp;', '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('members', 'members.joined_on');\">Joined On</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('members', 'members.lastname');\">Member</a>", '', 'header'));
                header.set(3, new Cell("<a class=\"sortable\" onClick=\"sort_by('members', 'members.total_work_years');\">Total Work Years</a>", '', 'header'));
                header.set(4, new Cell("<a class=\"sortable\" onClick=\"sort_by('members', 'members.notice_period');\">Notice Period (Months)</a>", '', 'header'));
                header.set(5, new Cell("<a class=\"sortable\" onClick=\"sort_by('members', 'members.expected_salary');\">Expected Salary</a>", '', 'header'));
                header.set(6, new Cell('&nbsp;', '', 'header action'));
                members_table.set(0, header);
                
                for (var i=0; i < emails.length; i++) {
                    var row = new Row('');
                    
                    // active seeking job?
                    if (is_seeking_jobs[i].childNodes[0].nodeValue == 'Y') {
                        row.set(0, new Cell('<img src="../common/images/icons/expired.png" />', '', 'cell cell_indicator'));
                    } else {
                        row.set(0, new Cell('&nbsp;', '', 'cell cell_indicator'));
                    }
                    
                    // joined on
                    row.set(1, new Cell(joined_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    // member details
                    var short_desc = '<a class="member_link" href="member.php?member_email_addr=' + emails[i].childNodes[0].nodeValue + '&page=career" target="_new">' + members[i].childNodes[0].nodeValue + '</a>' + "\n";
                    
                    var phone_num = '';
                    if (phone_nums[i].childNodes.length > 0) {
                        phone_num = phone_nums[i].childNodes[0].nodeValue;
                    }
                    short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Tel.:</span> ' + phone_num + '</div>' + "\n";
                    
                    short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Email:</span> <a href="mailto:' + emails[i].childNodes[0].nodeValue + '">' + emails[i].childNodes[0].nodeValue + '</a></div>' + "\n";
                    row.set(2, new Cell(short_desc, '', 'cell'));
                    
                    // total work years
                    var total_work_years = 'N/A';
                    if (total_years[i].childNodes.length > 0) {
                        total_work_years = total_years[i].childNodes[0].nodeValue;
                    }
                    row.set(3, new Cell(total_work_years, '', 'cell'));
                    
                    // notice period
                    var notice_period = 'N/A';
                    if (notice_periods[i].childNodes.length > 0) {
                        notice_period = notice_periods[i].childNodes[0].nodeValue;
                    }
                    row.set(4, new Cell(notice_period, '', 'cell'));
                    
                    // salary
                    var sal_currency = '???';
                    if (exp_sal_currencies[i].childNodes.length > 0) {
                        sal_currency = exp_sal_currencies[i].childNodes[0].nodeValue;
                    }
                    
                    var sal_start = '';
                    if (exp_sal_starts[i].childNodes.length > 0) {
                        var salary = parseFloat(exp_sal_starts[i].childNodes[0].nodeValue);
                        if (salary > 0.00) {
                            sal_start = exp_sal_starts[i].childNodes[0].nodeValue;
                        }
                    } 
                    
                    var sal_end = '';
                    if (exp_sal_ends[i].childNodes.length > 0) {
                        var salary = parseFloat(exp_sal_ends[i].childNodes[0].nodeValue);
                        if (salary > 0.00) {
                            sal_end = exp_sal_ends[i].childNodes[0].nodeValue;
                        }
                    }
                    
                    var salary_str = 'N/A';
                    if (!isEmpty(sal_start)) {
                        salary_str = sal_currency + '$ ' + sal_start;
                    }
                    
                    if (isEmpty(sal_end) && !isEmpty(sal_start)) {
                        salary_str = 'from ' + salary_str;
                    } else if (!isEmpty(sal_end) && !isEmpty(sal_start)) {
                        salary_str = salary_str + ' to ' + sal_end;
                    }
                    row.set(5, new Cell(salary_str, '', 'cell'));
                    
                    var actions = '';
                    if (is_actives[i].childNodes[0].nodeValue == 'Y') {
                        actions = '<input type="button" id="activate_button_' + i + '" value="De-activate" onClick="activate_member(\'' + emails[i].childNodes[0].nodeValue + '\', \'' + i + '\');" />';
                        actions = actions + '<input type="button" id="password_reset_' + i + '" value="Reset Password" onClick="reset_password(\'' + emails[i].childNodes[0].nodeValue + '\');" />';
                    } else {
                        actions = '<input type="button" id="activate_button_' + i + '" value="Activate" onClick="activate_member(\'' + emails[i].childNodes[0].nodeValue + '\', \'' + i + '\');" />';
                    }                    
                    row.set(6, new Cell(actions, '', 'cell action'));
                    
                    members_table.set((parseInt(i)+1), row);
                }
                
                $('div_members').set('html', members_table.get_html());
                
                sliding_search_fx.slideOut();
            }
        },
        onRequest: function(instance) {
            set_status('Loading members...');
        }
    });
    
    request.send(params);
}

function show_resumes_page(_member_id) {
    var selected_jobs = '';
    if ($('jobs') != null) {
        for (var i=0; i < $('jobs').options.length; i++) {
            if ($('jobs').options[i].selected) {
                if (isEmpty(selected_jobs)) {
                    selected_jobs = $('jobs').options[i].value;
                } else {
                    selected_jobs = selected_jobs + ',' + $('jobs').options[i].value;
                }
            }
        }
    }
    window.open('member.php?member_email_addr=' + _member_id + '&page=resumes&selected_jobs=' + selected_jobs);
}

function delete_application(_app_id, _is_buffer) {
    if (!confirm('You sure to delete the selected application?')) {
        return;
    }
    
    var params = 'id=' + _app_id;
    params = params + '&action=delete_application';
    
    if (_is_buffer) {
        params = params + '&is_buffer=1';
    }
    
    var uri = root + "/employees/members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == 'ko') {
                alert('Cannot delete the application.');
            }
            
            if (_is_buffer) {
                update_new_applicants();
            } else {
                update_applicants();
            }
            
        },
        onRequest: function(instance) {
            set_status('Deleting application...');
        }
    });
    
    request.send(params);
}

function make_member_from(_app_id) {
    if (!confirm('Confirm to sign up the selected applicant?' + "\n\n" + 'All other application made to/for this candidate will be moved to Members section as well.' +"\n")) {
        return;
    }
    
    var params = 'id=' + _app_id;
    params = params + '&action=sign_up';
    
    var uri = root + "/employees/members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (!isEmail(txt)) {
                var err_msg = 'Cannot sign up the selected applicant.' + "\n\n";
                switch(txt) {
                    case 'ko:member':
                        alert(err_msg + 'The candidate cannot be signed up as a member.');
                        break;
                    case 'ko:resume':
                        alert(err_msg + 'Resume data error has occurred.');
                        break;
                    case 'ko:resume_copy':
                        alert(err_msg + 'Resume file copy/move error occurred.');
                        break;
                }
                return;
            }
            
            location.replace('member.php?member_email_addr=' + txt);
        },
        onRequest: function(instance) {
            set_status('Signing up applicant...');
        }
    });
    
    request.send(params);
}

function transfer_to_member(_app_id) {
    if (!confirm('Confirm to transfer the selected applicant?' + "\n\n" + 'All other application made to/for this candidate will be moved to Members section as well.' +"\n")) {
        return;
    }
    
    // this function is similar with sign up, but functions a little differently.
    // separting them is to manage them more easily
    var params = 'id=' + _app_id;
    params = params + '&action=sign_up';
    
    var uri = root + "/employees/members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (!isEmail(txt)) {
                var err_msg = 'Cannot transfer the selected applicant.' + "\n\n";
                switch(txt) {
                    case 'ko:member':
                        alert(err_msg + 'The candidate is not a member, and the system failed to create an account too.');
                        break;
                    case 'ko:resume':
                        alert(err_msg + 'Resume data error has occurred.');
                        break;
                    case 'ko:resume_copy':
                        alert(err_msg + 'Resume file copy/move error occurred.');
                        break;
                }
                return;
            }
            
            location.replace('member.php?member_email_addr=' + txt);
        },
        onRequest: function(instance) {
            set_status('Transferring applicant...');
        }
    });
    
    request.send(params);
}

function show_notes_popup(_app_id) {
    var params = 'id=' + _app_id;
    params = params + '&action=get_notes';
    
    if (isEmail(_app_id)) {
        $('app_id').value = '';
        $('notes_email').value = _app_id;
        params = params + '&is_app=0';
    } else {
        $('app_id').value = _app_id;
        $('notes_email').value = '';
        params = params + '&is_app=1';
    }
    
    var uri = root + "/employees/members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            txt = txt.replace('&lt;br/&gt;' , '<br/>');
            txt = txt.replace(/<br\/>/g, "\n");
            $('notes').value = txt;
            set_status('');
            show_window('notes_window');
            window.scrollTo(0, 0);
            $('notes').focus();
        },
        onRequest: function(instance) {
            set_status('Loading notes...');
        }
    });
    
    request.send(params);
}

function close_notes_popup(_is_save) {
    if (_is_save) {
        var params = '';
        if (isEmpty($('app_id').value)) {
            params = 'id=' + $('notes_email').value;
            params = params + '&is_app=0';
        } else {
            params = 'id=' + $('app_id').value;
            params = params + '&is_app=1';
        }
        params = params + '&action=update_notes';
        
        var notes = $('notes').value.replace("\n", '<br/>');
        params = params + '&notes=' + notes;
        
        var uri = root + "/employees/members_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                close_window('notes_window');
                set_status('');
                
                if (isEmpty($('app_id').value)) {
                    update_applicants();
                } else {
                    update_new_applicants();
                }
            },
            onRequest: function(instance) {
                set_status('Saving notes...');
            }
        });

        request.send(params);
    } else {
        close_window('notes_window');
    }
}

function edit_candidate_phone(_id) {
    var phone = prompt('Enter the candidate\'s phone number.');
    if (!isEmpty(phone)) {
        var params = 'id=' + _id + '&action=edit_candidate_phone&phone=' + phone;
        
        var uri = root + "/employees/members_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                set_status('');
                
                if (txt == 'ko') {
                    alert('An error while saving candidate\'s phone number.');
                    return;
                }
                
                update_new_applicants();
            },
            onRequest: function(instance) {
                set_status('Saving phone number...');
            }
        });

        request.send(params);
    } 
}

function edit_candidate_email(_id) {
    var email = prompt('Enter the candidate\'s email address.');
    if (isEmail(email)) {
        var params = 'id=' + _id + '&action=edit_candidate_email&email=' + email;
        
        var uri = root + "/employees/members_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                set_status('');
                
                if (txt == 'ko') {
                    alert('An error while saving candidate\'s email address.');
                    return;
                }
                
                update_new_applicants();
            },
            onRequest: function(instance) {
                set_status('Saving email address...');
            }
        });

        request.send(params);
    }
}

function edit_referrer_phone(_id) {
    var phone = prompt('Enter the referrer\'s phone number.');
    if (!isEmpty(phone)) {
        var params = 'id=' + _id + '&action=edit_referrer_phone&phone=' + phone;
        
        var uri = root + "/employees/members_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                set_status('');
                
                if (txt == 'ko') {
                    alert('An error while saving referrer\'s phone number.');
                    return;
                }
                
                update_new_applicants();
            },
            onRequest: function(instance) {
                set_status('Saving phone number...');
            }
        });

        request.send(params);
    } 
}

function edit_referrer_email(_id) {
    var email = prompt('Enter the referrer\'s email address.');
    if (isEmail(email)) {
        var params = 'id=' + _id + '&action=edit_referrer_email&email=' + email;
        
        var uri = root + "/employees/members_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                set_status('');
                
                if (txt == 'ko') {
                    alert('An error while saving referrer\'s email address.');
                    return;
                }
                
                update_new_applicants();
            },
            onRequest: function(instance) {
                set_status('Saving email address...');
            }
        });

        request.send(params);
    }
}

function auto_fill_referrer() {
    if ($('auto_fill_checkbox').checked) {
        $('referrer_name').value = 'YellowElevator.com';
        $('referrer_phone').value = '-';
        $('referrer_email_addr').value = $('sales_email_addr').value;
        $('referrer_name').disabled = true;
        $('referrer_phone').disabled = true;
        $('referrer_email_addr').disabled = true;
    } else {
        $('referrer_name').value = '';
        $('referrer_phone').value = '';
        $('referrer_email_addr').value = '';
        $('referrer_name').disabled = false;
        $('referrer_phone').disabled = false;
        $('referrer_email_addr').disabled = false;
    }
}

function show_new_application_popup() {
    $('new_applicant_jobs').value = '';
    
    var jobs = '';
    for (var i=0; i < $('jobs').options.length; i++) {
        if ($('jobs').options[i].selected) {
            jobs = jobs + $('jobs').options[i].value + ',';
        }
    }
    jobs = jobs.substr(0, jobs.length-1);
    
    $('new_applicant_jobs').value = jobs;
    
    show_window('new_application_window');
    window.scrollTo(0, 0);
}

function close_new_application_popup(_is_save) {
    if (_is_save) {
        if (isEmpty($('candidate_name').value) && 
            isEmpty($('candidate_phone').value) && 
            isEmpty($('candidate_email_addr').value)) {
            alert('At least ONE field of Candidate must NOT be empty.');
            return;
        }
        
        if (!isEmpty($('candidate_email_addr').value) && 
            !isEmail($('candidate_email_addr').value)) {
            alert('Candidate email is invalid.');
            return;
        }
        
        var referrer_is_yel = 0;
        if ($('auto_fill_checkbox').checked) {
            referrer_is_yel = 1;
        }
        
        if (!referrer_is_yel && 
            isEmpty($('referrer_name').value) && 
            isEmpty($('referrer_phone').value) && 
            isEmpty($('referrer_email_addr').value)) {
            
            if (confirm('Without stating the referrer, the system will default it to YellowElevator.' + "\n\n" + 'Are you sure to continue?')) {
                referrer_is_yel = 1;
            }
        } else if (!referrer_is_yel && 
                   !isEmpty($('referrer_email_addr').value) &&
                   !isEmail($('referrer_email_addr').value)) {
            alert('Referrer email is invalid.');
            return;
        }
        
        var params = 'id=' + user_id;
        params = params + '&action=add_new_application';
        params = params + '&referrer_is_yel=' + referrer_is_yel;
        params = params + '&candidate_name=' + $('candidate_name').value;
        params = params + '&candidate_phone=' + $('candidate_phone').value;
        params = params + '&candidate_email=' + $('candidate_email_addr').value;
        params = params + '&jobs=' + $('new_applicant_jobs').value;
        
        if (!referrer_is_yel) {
            params = params + '&referrer_name=' + $('referrer_name').value;
            params = params + '&referrer_phone=' + $('referrer_phone').value;
            params = params + '&referrer_email=' + $('referrer_email_addr').value;
        } else {
            params = params + '&referrer_email=' + $('sales_email_addr').value;
        }
        
        var notes = $('quick_notes').value.replace("\n", '<br/>');
        params = params + '&notes=' + notes;
        
        var uri = root + "/employees/members_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                close_window('new_application_window');
                set_status('');
                
                if (txt == 'ko') {
                    alert('An error while adding new application.');
                    return;
                }
                
                update_new_applicants();
            },
            onRequest: function(instance) {
                set_status('Saving new application...');
            }
        });

        request.send(params);
    } else {
        close_window('new_application_window');
    }
}

function show_referrer_popup(_id) {
    $('referral_buffer_id').value = _id;
    
    var params = 'id=' + _id + '&action=get_referrer';
    
    var uri = root + "/employees/members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error while retrieving referrer contacts.');
                return;
            }
            
            var candidate_name = xml.getElementsByTagName('candidate_name');
            var referrer_name = xml.getElementsByTagName('referrer_name');
            var referrer_email = xml.getElementsByTagName('referrer_email');
            var referrer_phone = xml.getElementsByTagName('referrer_phone');
            
            $('ref_candidate_name').set('html', candidate_name[0].childNodes[0].nodeValue);
            $('ref_referrer_name').value = referrer_name[0].childNodes[0].nodeValue;
            if (referrer_email[0].childNodes.length > 0) {
                $('ref_referrer_email').value = referrer_email[0].childNodes[0].nodeValue;
            } else {
                $('ref_referrer_email').value = '';
            }
            
            if (referrer_phone[0].childNodes.length > 0) {
                $('ref_referrer_phone').value = referrer_phone[0].childNodes[0].nodeValue;
            } else {
                $('ref_referrer_phone').value = '';
            }
            
            show_window('referrer_window');
            window.scrollTo(0, 0);
        }
    });

    request.send(params);
}

function close_referrer_popup(_is_save) {
    if (_is_save) {
        if (isEmpty($('ref_referrer_name').value)) {
            alert('You need at least a name to identify the referrer.');
            return;
        }
        
        if (isEmpty($('ref_referrer_phone').value) || !isEmail($('ref_referrer_email').value)) {
            if (!confirm('You will NOT be able to proceed to Sign Up or Transfer without a valid phone number and email address.' + "\n\n" + 'Are you sure to proceed?')) {
                return;
            }
        }
        
        var params = 'id=' + $('referral_buffer_id').value + '&action=save_referrer';
        params = params + '&referrer_name=' + encodeURIComponent($('ref_referrer_name').value);
        params = params + '&referrer_email=' + $('ref_referrer_email').value;
        params = params + '&referrer_phone=' + $('ref_referrer_phone').value;
        
        var uri = root + "/employees/members_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    alert('An error while saving referrer contacts.');
                    return;
                }
                
                close_window('referrer_window');
                update_new_applicants();
            }
        });

        request.send(params);
    } else {
        close_window('referrer_window');
    }
}

function show_jobs_popup(_use_email, _match) {
    var params = 'id=0&action=get_other_jobs';
    if (arguments.length < 3) {
        if (_use_email) {
            params = params + '&candidate_email=' + _match;
        } else {
            params = params + '&candidate_name=' + _match;
        }
        params = params + '&is_app=1';
    } else {
        params = params + '&email_addr=' + _match;
        params = params + '&is_app=0';
    }
    
    var uri = root + "/employees/members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            // set_status('<pre>' + txt + '</pre>');
            // return;
            if (txt == 'ko') {
                alert('An error while retrieving other applied jobs.' + "\n\n" + 'Perhaps this candidate is not attached to any job.');
                return;
            }
            
            var titles = xml.getElementsByTagName('job');
            var employers = xml.getElementsByTagName('employer');
            var requested_ons = xml.getElementsByTagName('formatted_requested_on');
            
            var jobs_table = new FlexTable('other_jobs_table', 'other_jobs');

            var header = new Row('');
            header.set(0, new Cell('Applied On', '', 'header'));
            header.set(1, new Cell('Job', '', 'header'));
            header.set(2, new Cell('Employer', '', 'header'));
            jobs_table.set(0, header);
            
            for (var i=0; i < titles.length; i++) {
                var row = new Row('');
                
                row.set(0, new Cell(requested_ons[i].childNodes[0].nodeValue, '', 'cell'));
                row.set(1, new Cell(titles[i].childNodes[0].nodeValue, '', 'cell'));
                row.set(2, new Cell(employers[i].childNodes[0].nodeValue, '', 'cell'));
                
                jobs_table.set((parseInt(i)+1), row);
            }
            
            $('div_other_jobs').set('html', jobs_table.get_html());
            
            show_window('other_jobs_window');
            window.scrollTo(0, 0);
        }
    });

    request.send(params);
}

function close_jobs_popup() {
    close_window('other_jobs_window');
}

function show_progress_popup(_id, _is_buffer) {
    $('progress_id').value = _id;
    $('progress_is_buffer').value = _is_buffer;
    
    var params = 'id=' + _id;
    params = params + '&action=get_progress_notes';
    params = params + '&is_buffer=' + _is_buffer;
    
    var uri = root + "/employees/members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            txt = txt.replace('&lt;br/&gt;' , '<br/>');
            txt = txt.replace(/<br\/>/g, "\n");
            $('progress_notes').value = txt;
            set_status('');
            show_window('progress_notes_window');
            window.scrollTo(0, 0);
            $('notes').focus();
        },
        onRequest: function(instance) {
            set_status('Loading progress notes...');
        }
    });
    
    request.send(params);
}

function close_progress_popup(_is_save) {
    if (_is_save) {
        params = 'id=' + $('progress_id').value;
        params = params + '&action=update_progress_notes';
        params = params + '&is_buffer=' + $('progress_is_buffer').value;
        
        var notes = $('progress_notes').value.replace("\n", '<br/>');
        params = params + '&notes=' + encodeURIComponent(notes);
        
        var uri = root + "/employees/members_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                close_window('progress_notes_window');
                set_status('');
                
                if (return_page == '') {
                    update_new_applicants();
                } else {
                    update_applicants();
                }
            },
            onRequest: function(instance) {
                set_status('Saving progress notes...');
            }
        });

        request.send(params);
    } else {
        close_window('progress_notes_window');
    }
}


function onDomReady() {
    initialize_page();
    
    switch (current_page) {
        case 'members':
            show_members();
            break;
        case 'applicants':
            show_applicants();
            break;
        default:
            show_new_applicants();
            break;
   }
   
   sliding_filter_fx = new Fx.Slide('div_main_filter', {
       mode: 'vertical'
   });
   
   sliding_search_fx = new Fx.Slide('div_search', {
       mode: 'vertical'
   });
}

window.addEvent('domready', onDomReady);
