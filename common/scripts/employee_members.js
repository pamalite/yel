var order_by = 'members.joined_on';
var order = 'desc';
var applications_order_by = 'requested_on';
var applications_order = 'desc';
var applications_filter = '';

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
            show_members();
            break;
        case 'applications':
            applications_order_by = _column;
            applications_ascending_or_descending();
            show_applications();
            break;
    }
}

function show_members() {
    $('applications').setStyle('display', 'none');
    $('members').setStyle('display', 'block');
    $('member_search').setStyle('display', 'none');
    
    $('item_applications').setStyle('background-color', '');
    $('item_members').setStyle('background-color', '#CCCCCC');
    $('item_search').setStyle('background-color', '');
    
    if (arguments.length > 0) {
        // do not load from db unless is being sorted
        return;
    }
    
    var params = 'id=' + user_id + '&order_by=' + order_by + ' ' + order;
    params = params + '&action=get_members';
    
    var uri = root + "/employees/members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            //set_status('<pre>' + txt + '</pre>');
            //return;
            if (txt == 'ko') {
                alert('An error occured while loading members.');
                return false;
            }
            
            if (txt == '0') {
                $('div_members').set('html', '<div class="empty_results">No members at this moment.</div>');
            } else {
                var emails = xml.getElementsByTagName('email_addr');
                var members = xml.getElementsByTagName('member');
                var phone_nums = xml.getElementsByTagName('phone_num');
                var employees = xml.getElementsByTagName('employee');
                var joined_ons = xml.getElementsByTagName('formatted_joined_on');
                var last_logins = xml.getElementsByTagName('formatted_last_login');
                var is_actives = xml.getElementsByTagName('active');
                
                var members_table = new FlexTable('members_table', 'members');

                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('members', 'members.joined_on');\">Joined On</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('members', 'members.lastname');\">Member</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('members', 'employees.lastname');\">Added By</a>", '', 'header'));
                header.set(3, new Cell("<a class=\"sortable\" onClick=\"sort_by('members', 'member_sessions.last_login');\">First Login</a>", '', 'header'));
                header.set(4, new Cell('&nbsp;', '', 'header action'));
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
                    
                    short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Email:</span><a href="mailto:' + emails[i].childNodes[0].nodeValue + '">' + emails[i].childNodes[0].nodeValue + '</a></div>' + "\n";
                    row.set(1, new Cell(short_desc, '', 'cell'));
                    
                    row.set(2, new Cell(employees[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var last_login = '';
                    if (last_logins[i].childNodes.length > 0) {
                        last_login = last_logins[i].childNodes[0].nodeValue;
                    }
                    row.set(3, new Cell(last_login, '', 'cell'));
                    
                    var actions = '';
                    if (is_actives[i].childNodes[0].nodeValue == 'Y') {
                        actions = '<input type="button" id="activate_button_' + i + '" value="De-activate" onClick="activate_member(\'' + emails[i].childNodes[0].nodeValue + '\', \'' + i + '\');" />';
                        actions = actions + '<input type="button" id="password_reset_' + i + '" value="Reset Password" onClick="reset_password(\'' + emails[i].childNodes[0].nodeValue + '\');" />';
                        actions = actions + '<input type="button" value="Pick a Resume to Refer" onClick="show_resumes_page(\'' + emails[i].childNodes[0].nodeValue + '\');" />';
                    } else {
                        actions = '<input type="button" id="activate_button_' + i + '" value="Activate" onClick="activate_member(\'' + emails[i].childNodes[0].nodeValue + '\', \'' + i + '\');" />';
                    }
                    
                    row.set(4, new Cell(actions, '', 'cell action'));
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

function filter_applications() {
    applications_filter = $('applications_filter').options[$('applications_filter').selectedIndex].value;
    show_applications();
}

function show_applications() {
    $('applications').setStyle('display', 'block');
    $('members').setStyle('display', 'none');
    $('member_search').setStyle('display', 'none');
    
    $('item_applications').setStyle('background-color', '#CCCCCC');
    $('item_members').setStyle('background-color', '');
    $('item_search').setStyle('background-color', '');
    
    if (arguments.length > 0) {
        // do not load from db unless is being sorted
        return;
    }
    
    var params = 'id=' + user_id + '&order_by=' + applications_order_by + ' ' + applications_order;
    params = params + '&action=get_applications';
    params = params + '&filter=' + applications_filter;
    
    var uri = root + "/employees/members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            // set_status('<pre>' + txt + '</pre>');
            // return;
            if (txt == 'ko') {
                alert('An error occured while loading applications.');
                return false;
            }
            
            if (txt == '0') {
                $('div_applications').set('html', '<div class="empty_results">No applications at this moment.</div>');
            } else {
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
                
                var applications_table = new FlexTable('applications_table', 'applications');

                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('applications', 'referral_buffers.requested_on');\">Requested On</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('applications', 'referral_buffers.referrer_name');\">Referrer</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('applications', 'referral_buffers.candidate_name');\">Candidate</a>", '', 'header'));
                header.set(3, new Cell('Notes', '', 'header'));
                header.set(4, new Cell('Resume', '', 'header'));
                header.set(5, new Cell('Quick Actions', '', 'header action'));
                applications_table.set(0, header);
                
                for (var i=0; i < ids.length; i++) {
                    var is_cannot_signup = false;
                    var row = new Row('');
                    
                    row.set(0, new Cell(requested_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    // referrer column
                    if (referrer_emails[i].childNodes[0].nodeValue.substr(0, 5) == 'team.' &&
                        referrer_emails[i].childNodes[0].nodeValue.substr(7) == '@yellowelevator.com') {
                        row.set(1, new Cell('Self Applied', '', 'cell'));
                    } else {
                        var referrer_phone_num = '';
                        var referrer_email = '';
                        if (referrer_phones[i].childNodes.length > 0) {
                            referrer_phone_num = referrer_phones[i].childNodes[0].nodeValue;
                        }

                        if (referrer_emails[i].childNodes.length > 0) {
                            referrer_email = referrer_emails[i].childNodes[0].nodeValue;
                        }

                        var short_desc = '<span style="font-weight: bold;">' + referrer_names[i].childNodes[0].nodeValue + '</span>' + "\n";
                        
                        if (isEmpty(referrer_phone_num)) {
                            is_cannot_signup = true;
                            short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Tel.:</span> <a class="no_link small_contact_edit" onClick="edit_referrer_phone(' + ids[i].childNodes[0].nodeValue + ');">Add Phone Number</a></div>' + "\n";
                        } else {
                            short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Tel.:</span> ' + referrer_phone_num + '</div>' + "\n";
                        }
                        
                        if (isEmpty(referrer_email)) {
                            is_cannot_signup = true;
                            short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Email:</span> <a class="no_link small_contact_edit" onClick="edit_referrer_email(' + ids[i].childNodes[0].nodeValue + ');">Add Email</a></div>' + "\n";
                        } else {
                            short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Email:</span><a href="mailto:' + referrer_email + '"> ' + referrer_email + '</a></div>' + "\n";
                        }
                        row.set(1, new Cell(short_desc, '', 'cell'));
                    }
                    
                    // candidate column
                    var candidate_phone_num = '';
                    var candidate_email = '';
                    if (candidate_phones[i].childNodes.length > 0) {
                        candidate_phone_num = candidate_phones[i].childNodes[0].nodeValue;
                    }
                    
                    if (candidate_emails[i].childNodes.length > 0) {
                        candidate_email = candidate_emails[i].childNodes[0].nodeValue;
                    }
                    
                    short_desc = '<span style="font-weight: bold;">' + candidate_names[i].childNodes[0].nodeValue + '</span>' + "\n";
                    
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
                    
                    row.set(2, new Cell(short_desc, '', 'cell'));
                    
                    var add_update_notes = 'Add';
                    if (has_notes[i].childNodes[0].nodeValue == '1') {
                        add_update_notes = 'Update';
                    }
                    row.set(3, new Cell('<a class="no_link" onClick="show_notes_popup(\'' + ids[i].childNodes[0].nodeValue +  '\');">' + add_update_notes + '</a>', '', 'cell'));
                    
                    if (resume_ids[i].childNodes.length > 0) {
                        row.set(4, new Cell('<a href="resume.php?id=' + resume_ids[i].childNodes[0].nodeValue + '">View Resume</a>', '', 'cell'));
                    } else if (resume_file_hashes[i].childNodes.length > 0) {
                        row.set(4, new Cell('<a href="resume.php?id=' + ids[i].childNodes[0].nodeValue + '&hash=' + resume_file_hashes[i].childNodes[0].nodeValue + '">View Resume</a>', '', 'cell'));
                    } else {
                        row.set(4, new Cell('Sign Up to Upload', '', 'cell'));
                    }
                    
                    var actions = '';
                    actions = '<input type="button" value="Delete" onClick="delete_application(\'' + ids[i].childNodes[0].nodeValue + '\');" />';
                    
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

function check_member_from(_app_id) {
    var params = 'id=' + _app_id;
    params = params + '&action=check_member';
    
    var uri = root + "/employees/members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        async: false,
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == '0') {
                return 'ok';
            }
            
            var names = xml.getElementsByTagName('name');
            var phones = xml.getElementsByTagName('phone');
            var created_ons = xml.getElementsByTagName('created_on');
            
            $('conflict_app_id').value = _app_id;
            $('buffered_name').set('html', names[0].childNodes[0].nodeValue);
            $('existing_name').set('html', names[1].childNodes[0].nodeValue);
            $('buffered_phone').set('html', phones[0].childNodes[0].nodeValue);
            $('existing_phone').set('html', phones[1].childNodes[0].nodeValue);
            $('buffered_created_on').set('html', created_ons[0].childNodes[0].nodeValue);
            $('existing_created_on').set('html', created_ons[1].childNodes[0].nodeValue);
            
            return '-1';
        },
        onRequest: function(instance) {
            set_status('Checking application...');
        }
    });
    
    request.send(params);
}

function close_conflict_popup() {
    close_window('conflicts_window');
    
    if (arguments.length > 0) {
        if (arguments[0] == 0) {
            make_member_from($('conflict_app_id').value, 'buffered');
        } else {
            make_member_from($('conflict_app_id').value, 'existing');
        }
    }
}

function make_member_from(_app_id) {
    var resolute_using = '';
    
    if (arguments.length <= 1) {
        if (!confirm('You sure to sign up selected application?')) {
            return;
        }
        
        if (check_member_from(_app_id) != 'ok') {
            show_window('conflicts_window');
            return;
        }
    } else {
        resolute_using = arguments[1];
    }
    
    var params = 'id=' + _app_id;
    params = params + '&action=sign_up';
    params = params + '&resolution=' + resolute_using;
    
    var uri = root + "/employees/members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            var err_msg = 'Cannot sign up the selected application.' + "\n\n";
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
            
            set_status('');
            location.replace('member.php?member_email_addr=' + txt);
        },
        onRequest: function(instance) {
            set_status('Signing up application...');
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

function onDomReady() {
    initialize_page();
}

window.addEvent('domready', onDomReady);
