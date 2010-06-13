var order_by = 'members.joined_on';
var order = 'desc';
var applications_order_by = 'requested_on';
var applications_order = 'desc';

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
            order_by = _column;
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
                    
                    var short_desc = '<a class="no_link member_link" onClick="show_member(\'' + emails[i].childNodes[0].nodeValue + '\');">' + members[i].childNodes[0].nodeValue + '</a>' + "\n";
                    
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
                    } else {
                        actions = '<input type="button" id="activate_button_' + i + '" value="Activate" onClick="activate_member(\'' + emails[i].childNodes[0].nodeValue + '\', \'' + i + '\');" />';
                        actions = actions + '<input type="button" id="password_reset_' + i + '" value="Reset Password" onClick="reset_password(\'' + emails[i].childNodes[0].nodeValue + '\');" disabled />';
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
    
    var params = 'id=' + user_id + '&order_by=' + order_by + ' ' + order;
    params = params + '&action=get_applications';
    
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
                var has_testimonys = xml.getElementsByTagName('has_testimony');
                
                var applications_table = new FlexTable('applications_table', 'applications');

                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('applications', 'referral_buffers.requested_on');\">Requested On</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('applications', 'referral_buffers.referrer_name');\">Referrer</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('applications', 'referral_buffers.candidate_name');\">Candidate</a>", '', 'header'));
                header.set(3, new Cell('Notes', '', 'header'));
                header.set(4, new Cell('Resume', '', 'header'));
                header.set(5, new Cell('Quick Action', '', 'header action'));
                applications_table.set(0, header);
                
                for (var i=0; i < ids.length; i++) {
                    var row = new Row('');
                    
                    row.set(0, new Cell(requested_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    // referrer column
                    var referrer_phone_num = 'Not Provided';
                    var referrer_email = 'Not Provided';
                    if (referrer_phones[i].childNodes.length > 0) {
                        referrer_phone_num = referrer_phones[i].childNodes[0].nodeValue;
                    }
                    
                    if (referrer_emails[i].childNodes.length > 0) {
                        referrer_email = referrer_emails[i].childNodes[0].nodeValue;
                    }
                    
                    var short_desc = '<a class="no_link member_link" onClick="show_member(\'' + referrer_email + '\');">' + referrer_names[i].childNodes[0].nodeValue + '</a>' + "\n";
                    short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Tel.:</span> ' + referrer_phone_num + '</div>' + "\n";
                    short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Email:</span><a href="mailto:' + referrer_email + '">' + referrer_email + '</a></div>' + "\n";
                    row.set(1, new Cell(short_desc, '', 'cell'));
                    
                    // candidate column
                    var candidate_phone_num = 'Not Provided';
                    var candidate_email = 'Not Provided';
                    if (candidate_phones[i].childNodes.length > 0) {
                        candidate_phone_num = candidate_phones[i].childNodes[0].nodeValue;
                    }
                    
                    if (candidate_emails[i].childNodes.length > 0) {
                        candidate_email = candidate_emails[i].childNodes[0].nodeValue;
                    }
                    
                    short_desc = '<a class="no_link member_link" onClick="show_member(\'' + candidate_email + '\');">' + candidate_names[i].childNodes[0].nodeValue + '</a>' + "\n";
                    short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Tel.:</span> ' + candidate_phone_num + '</div>' + "\n";
                    short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Email:</span><a href="mailto:' + candidate_email + '">' + candidate_email + '</a></div>' + "\n";
                    row.set(2, new Cell(short_desc '', 'cell'));
                    
                    if (has_testimonys[i].childNodes[0].value == '1') {
                        row.set(3, new Cell('<a ', '', 'cell'));
                    } else {
                        row.set(3, new Cell('', '', 'cell'));
                    }
                    
                    
                    var actions = '';
                    if (is_actives[i].childNodes[0].nodeValue == 'Y') {
                        actions = '<input type="button" id="activate_button_' + i + '" value="De-activate" onClick="activate_member(\'' + emails[i].childNodes[0].nodeValue + '\', \'' + i + '\');" />';
                        actions = actions + '<input type="button" id="password_reset_' + i + '" value="Reset Password" onClick="reset_password(\'' + emails[i].childNodes[0].nodeValue + '\');" />';
                    } else {
                        actions = '<input type="button" id="activate_button_' + i + '" value="Activate" onClick="activate_member(\'' + emails[i].childNodes[0].nodeValue + '\', \'' + i + '\');" />';
                        actions = actions + '<input type="button" id="password_reset_' + i + '" value="Reset Password" onClick="reset_password(\'' + emails[i].childNodes[0].nodeValue + '\');" disabled />';
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
    $('referral_buffer_id').value = '';
    $('member_page_form').submit();
}

function show_application(_id) {
    $('member_email_addr').value = '';
    $('referral_buffer_id').value = _id;
    $('member_page_form').submit();
}

function add_new_member() {
    $('member_email_addr').value = '';
    $('referral_buffer_id').value = '';
    $('member_page_form').submit();
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
}

function show_search_members() {
    $('applications').setStyle('display', 'none');
    $('members').setStyle('display', 'none');
    $('member_search').setStyle('display', 'block');
    
    $('item_applications').setStyle('background-color', '');
    $('item_members').setStyle('background-color', '');
    $('item_search').setStyle('background-color', '#CCCCCC');
    
}

function onDomReady() {
    initialize_page();
}

window.addEvent('domready', onDomReady);
