var current_section = 'applicants';
var order_by = 'members.joined_on';
var order = 'desc';
var members_filter = '';
var applications_order_by = 'referral_buffers.requested_on';
var applications_order = 'desc';
var applications_filter = '';
var candidates_filter = '';
var filter_by_employer_only = true;
var filter_only_non_attached = false;
var filter_is_dirty = false;
var applicants_page = 1;
var members_page = 1;
var sliding_filter_fx = '';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function applications_ascending_or_descending() {
    if (applications_order == 'desc') {
        applications_order = 'asc';
    } else {
        applications_order = 'desc';
    }
}

function sort_by(_table, _column) {
    switch (_table) {
        case 'members':
            order_by = _column;
            ascending_or_descending();
            update_members();
            break;
        case 'applications':
            applications_order_by = _column;
            applications_ascending_or_descending();
            update_applications();
            break;
    }
}

function toggle_main_filter() {
    sliding_filter_fx.toggle();
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
        filter_applications();
    } else if (current_section == 'members') {
        filter_members();
    }
}

function show_all() {
    if (current_section == 'applicants') {
        show_all_applications();
    } else {
        show_all_members();
    }
}

function show_all_members() {
    window.location = 'members.php?page=members';
}

function filter_members() {
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
    
    members_filter = '';
    if (filter_by_employer_only) {
        for (var i=0; i < $('employers').options.length; i++) {
            if ($('employers').options[i].selected) {
                members_filter = members_filter + $('employers').options[i].value + ',';
            }
        }
    } else {
        for (var i=0; i < $('jobs').options.length; i++) {
            if ($('jobs').options[i].selected) {
                members_filter = members_filter + $('jobs').options[i].value + ',';
            }
        }
    }
    
    if (!isEmpty(members_filter)) {
        members_filter = members_filter.substr(0, members_filter.length-1);
    }
    
    update_members();
}

function show_members() {
    current_section = 'members';
    $('add_new_btn').setStyle('visibility', 'hidden');
    
    $('applications').setStyle('display', 'none');
    $('members').setStyle('display', 'block');
    $('member_search').setStyle('display', 'none');
    
    $('item_applications').setStyle('background-color', '');
    $('item_members').setStyle('background-color', '#CCCCCC');
    $('item_search').setStyle('background-color', '');
    
    if (filter_is_dirty) {
        filter_is_dirty = false;
        filter_members();
    }
}

function update_members() {
    var selected_page = 1;
    var selected_page_index = $('member_pages').selectedIndex;
    if ($('member_pages').options.length > 0) {
        selected_page = $('member_pages').options[selected_page_index].value;
    }
    
    var params = 'id=' + user_id + '&order_by=' + order_by + ' ' + order;
    params = params + '&action=get_members';
    params = params + '&page=' + selected_page;
    
    if (filter_only_non_attached) {
        params = params + '&non_attached=1';
    }
    
    if (!isEmpty(members_filter)) {
        if (filter_by_employer_only) {
            params = params + '&employers=' + members_filter;
        } else {
            params = params + '&jobs=' + members_filter;
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
                alert('An error occured while loading members.');
                return false;
            }
            
            $('member_pages').length = 0;
            $('total_member_pages').set('html', '0');
            
            if (txt == '0') {
                set_status('');
                $('div_members').set('html', '<div class="empty_results">No members at this moment.</div>');
            } else {
                if ($('hide_show_lbl').get('html') == 'Hide Filter') {
                    toggle_main_filter();
                }
                
                var total_pages = xml.getElementsByTagName('total_pages');
                
                $('total_member_pages').set('html', total_pages[0].childNodes[0].nodeValue);
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
                    $('member_pages').options[$('member_pages').length] = an_option;
                }
                
                var emails = xml.getElementsByTagName('email_addr');
                var members = xml.getElementsByTagName('member_name');
                var phone_nums = xml.getElementsByTagName('phone_num');
                var has_notes = xml.getElementsByTagName('has_notes');
                var progress_notes = xml.getElementsByTagName('progress_notes');
                var joined_ons = xml.getElementsByTagName('formatted_joined_on');
                var yel_resumes = xml.getElementsByTagName('num_yel_resumes');
                var self_resumes = xml.getElementsByTagName('num_self_resumes');
                var is_actives = xml.getElementsByTagName('active');
                
                var members_table = new FlexTable('members_table', 'members');

                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('members', 'members.joined_on');\">Joined On</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('members', 'members.lastname');\">Member</a>", '', 'header'));
                header.set(2, new Cell('Notes', '', 'header'));
                header.set(3, new Cell('Resumes', '', 'header'));
                header.set(4, new Cell('Jobs', '', 'header'));
                header.set(5, new Cell('Progress', '', 'header'));
                header.set(6, new Cell('Quick Actions', '', 'header action'));
                members_table.set(0, header);
                
                for (var i=0; i < emails.length; i++) {
                    var row = new Row('');
                    
                    row.set(0, new Cell(joined_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var short_desc = '<a class="member_link" href="member.php?member_email_addr=' + emails[i].childNodes[0].nodeValue + '">' + members[i].childNodes[0].nodeValue + '</a>' + "\n";
                    
                    var phone_num = '';
                    if (phone_nums[i].childNodes.length > 0) {
                        phone_num = phone_nums[i].childNodes[0].nodeValue;
                    }
                    short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Tel.:</span> ' + phone_num + '</div>' + "\n";
                    
                    short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Email:</span> <a href="mailto:' + emails[i].childNodes[0].nodeValue + '">' + emails[i].childNodes[0].nodeValue + '</a></div>' + "\n";
                    short_desc = short_desc + '<br/><a href="member.php?member_email_addr=' + emails[i].childNodes[0].nodeValue + '&page=referrers">View Referrers</a>' + "\n";
                    row.set(1, new Cell(short_desc, '', 'cell'));
                    
                    if (has_notes[i].childNodes[0].nodeValue == '1') {
                        row.set(2, new Cell('<a class="no_link" onClick="show_notes_popup(\'' + emails[i].childNodes[0].nodeValue + '\');">Update</a>', '', 'cell'));
                    } else {
                        row.set(2, new Cell('<a class="no_link" onClick="show_notes_popup(\'' + emails[i].childNodes[0].nodeValue + '\');">Add</a>', '', 'cell'));
                    }
                    
                    var resume_details = '<a href="member.php?member_email_addr=' + emails[i].childNodes[0].nodeValue + '&page=resumes">View/Refer</a><br/><br/>';
                    resume_details = resume_details + '<span style="color: #666666;">YEL: ' + yel_resumes[i].childNodes[0].nodeValue + "</span><br/>\n";
                    resume_details = resume_details + '<span style="color: #666666;">Self: ' + self_resumes[i].childNodes[0].nodeValue + "</span><br/>\n";
                    row.set(3, new Cell(resume_details, '', 'cell'));
                    
                    row.set(4, new Cell('<a class="no_link" onClick="show_jobs_popup(false, \'' + emails[i].childNodes[0].nodeValue + '\', true);">View</a>', '', 'cell'));
                    
                    var progress = '<a class="no_link" onClick="show_progress_popup(\'' + emails[i].childNodes[0].nodeValue + '\');">Add</a>';
                    if (progress_notes[i].length > 0) {
                        progress = progress_notes[i].childNodes[0].nodeValue + '<br/><br/>';
                        progress = progress + '<a class="no_link" onClick="show_progress_popup(\'' + emails[i].childNodes[0].nodeValue + '\');">Update</a>';
                    }
                    row.set(5, new Cell(progress, '', 'cell'));
                    
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
                set_status('');
            }
        },
        onRequest: function(instance) {
            set_status('Loading members...');
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

function show_all_applications() {
    window.location = 'members.php';
}

function show_non_attached() {
    applications_filter = '';
    candidates_filter = '';
    members_filter = '';
    filter_only_non_attached = true;
    
    if (current_section == 'applicants') {
        update_applications();
    } else {
        update_members();
    }
}

function filter_applications() {
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
    
    candidates_filter = '';
    if (filter_by_employer_only) {
        for (var i=0; i < $('employers').options.length; i++) {
            if ($('employers').options[i].selected) {
                candidates_filter = candidates_filter + $('employers').options[i].value + ',';
            }
        }
    } else {
        for (var i=0; i < $('jobs').options.length; i++) {
            if ($('jobs').options[i].selected) {
                candidates_filter = candidates_filter + $('jobs').options[i].value + ',';
            }
        }
    }
    
    if (!isEmpty(candidates_filter)) {
        candidates_filter = candidates_filter.substr(0, candidates_filter.length-1);
    }
    
    update_applications();
}

function show_applications() {
    current_section = 'applicants';
    $('add_new_btn').setStyle('visibility', 'visible');
    
    $('applications').setStyle('display', 'block');
    $('members').setStyle('display', 'none');
    $('member_search').setStyle('display', 'none');
    
    $('item_applications').setStyle('background-color', '#CCCCCC');
    $('item_members').setStyle('background-color', '');
    $('item_search').setStyle('background-color', '');
    
    if (filter_is_dirty) {
        filter_is_dirty = false;
        filter_applications();
    }
}

function update_applications() {
    var selected_page = 1;
    var selected_page_index = $('pages').selectedIndex;
    if ($('pages').options.length > 0) {
        selected_page = $('pages').options[selected_page_index].value;
    }
    
    var params = 'id=' + user_id + '&order_by=' + applications_order_by + ' ' + applications_order;
    params = params + '&action=get_applications';
    params = params + '&show_only=' + applications_filter;
    params = params + '&page=' + selected_page;
    
    if (filter_only_non_attached) {
        params = params + '&non_attached=1';
    }
    
    if (!isEmpty(candidates_filter)) {
        if (filter_by_employer_only) {
            params = params + '&employers=' + candidates_filter;
        } else {
            params = params + '&jobs=' + candidates_filter;
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
                alert('An error occured while loading applications.');
                return false;
            }
            
            $('pages').length = 0;
            $('total_pages').set('html', '0');
            
            if (txt == '0') {
                set_status('');
                $('div_applications').set('html', '<div class="empty_results">No applications at this moment.</div>');
            } else {
                if ($('hide_show_lbl').get('html') == 'Hide Filter') {
                    toggle_main_filter();
                }
                
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
                var has_notes = xml.getElementsByTagName('has_notes');
                var jobs = xml.getElementsByTagName('job');
                var employers = xml.getElementsByTagName('employer');
                
                var applications_table = new FlexTable('applications_table', 'applications');

                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('applications', 'referral_buffers.requested_on');\">Created On</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('applications', 'referral_buffers.candidate_name');\">Candidate</a>", '', 'header'));
                header.set(2, new Cell('Notes', '', 'header'));
                header.set(3, new Cell('Job', '', 'header'));
                header.set(4, new Cell('Resume', '', 'header'));
                header.set(5, new Cell('Quick Actions', '', 'header action'));
                applications_table.set(0, header);
                
                for (var i=0; i < ids.length; i++) {
                    var is_cannot_signup = false;
                    var row = new Row('');
                    
                    row.set(0, new Cell(requested_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
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
                    
                    var add_update_notes = 'Add';
                    if (has_notes[i].childNodes[0].nodeValue == '1') {
                        add_update_notes = 'Update';
                    }
                    row.set(2, new Cell('<a class="no_link" onClick="show_notes_popup(\'' + ids[i].childNodes[0].nodeValue +  '\');">' + add_update_notes + '</a>', '', 'cell'));
                    
                    
                    var job_desc = 'N/A';
                    if (jobs[i].childNodes.length > 0) {
                        job_desc = '[' + employers[i].childNodes[0].nodeValue + '] ' + jobs[i].childNodes[0].nodeValue;
                    }
                    row.set(3, new Cell(job_desc, '', 'cell'));
                    
                    if (resume_ids[i].childNodes.length > 0) {
                        row.set(4, new Cell('<a href="resume.php?id=' + resume_ids[i].childNodes[0].nodeValue + '">View Resume</a>', '', 'cell'));
                    } else if (resume_file_hashes[i].childNodes.length > 0) {
                        row.set(4, new Cell('<a href="resume.php?id=' + ids[i].childNodes[0].nodeValue + '&hash=' + resume_file_hashes[i].childNodes[0].nodeValue + '">View Resume</a>', '', 'cell'));
                    } else {
                        row.set(4, new Cell('N/A', '', 'cell'));
                    }
                    
                    var actions = '';
                    actions = '<input type="button" value="Delete" onClick="delete_application(\'' + ids[i].childNodes[0].nodeValue + '\');" />';
                    
                    if (candidate_emails[i].childNodes.length <= 0) {
                        actions = actions + '<input type="button" value="Jobs" onClick="show_jobs_popup(false, \'' + candidate_names[i].childNodes[0].nodeValue + '\');" />';
                    } else {
                        actions = actions + '<input type="button" value="Jobs" onClick="show_jobs_popup(true, \'' + candidate_emails[i].childNodes[0].nodeValue + '\');" />';
                    }
                    
                    if (is_cannot_signup) {
                        actions = actions + '<input type="button" value="Sign Up" disabled />';
                    } else {
                        actions = actions + '<input type="button" value="Sign Up" onClick="make_member_from(\'' + ids[i].childNodes[0].nodeValue + '\');" />';
                    }
                    row.set(5, new Cell(actions, '', 'cell action'));
                    applications_table.set((parseInt(i)+1), row);
                }
                
                $('div_applications').set('html', applications_table.get_html());
                set_status('');
            }
        },
        onRequest: function(instance) {
            set_status('Loading applications...');
        }
    });
    
    request.send(params);
}

function show_search_members() {
    $('applications').setStyle('display', 'none');
    $('members').setStyle('display', 'none');
    $('member_search').setStyle('display', 'block');
    
    $('item_applications').setStyle('background-color', '');
    $('item_members').setStyle('background-color', '');
    $('item_search').setStyle('background-color', '#CCCCCC');
    
}

function show_resumes_page(_member_id) {
    location.replace('member.php?member_email_addr=' + _member_id + '&page=resumes');
}

function delete_application(_app_id) {
    if (!confirm('You sure to delete the selected application?')) {
        return;
    }
    
    var params = 'id=' + _app_id;
    params = params + '&action=delete_application';
    
    var uri = root + "/employees/members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('Cannot delete the application.');
            }
            
            set_status('');
            show_applications();
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
    $('app_id').value = _app_id;
    
    var params = 'id=' + _app_id;
    params = params + '&action=get_notes';
    
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
        var params = 'id=' + $('app_id').value;
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
                show_applications();
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
                
                show_applications();
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
                
                show_applications();
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
                
                show_applications();
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
                
                show_applications();
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
                
                show_applications();
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
                show_applications();
            }
        });

        request.send(params);
    } else {
        close_window('referrer_window');
    }
}

function show_jobs_popup(_use_email, _match) {
    var params = 'id=0&action=get_other_jobs';
    if (_use_email) {
        params = params + '&candidate_email=' + _match;
    } else {
        params = params + '&candidate_name=' + _match;
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

function onDomReady() {
    initialize_page();
    
    switch (current_page) {
       case 'members':
           show_members();
           break;
       default:
           show_applications();
           break;
   }
   
   sliding_filter_fx = new Fx.Slide('div_main_filter', {
       mode: 'vertical'
   });
}

window.addEvent('domready', onDomReady);
