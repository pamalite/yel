var order = 'desc';
var order_by = 'referrals.referred_on';
var filter = '';
var jobs_list = new ListBox('jobs_selector', 'jobs_list', true);

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function sort_by(_table, _column) {
    switch (_table) {
        case 'referrals':
            order_by = _column;
            ascending_or_descending();
            update_applications(filter);
            break;
    }
}

function filter_applications() {
    filter = $('filter').options[$('filter').selectedIndex].value;
    update_applications(filter);
}

function go_back() {
    location.replace('members.php?page=members');
}

function reset_password() {
    var proceed = confirm('Are you sure to reset the password?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=' + member_id;
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

function validate_profile_form() {
    // is new employer?
    if (member_id == '0') {
        if (!isEmail($('email_addr').value)) {
            alert('Please provide a valid e-mail address.');
            return false;
        }
    }
    
    if (isEmpty($('firstname').value)) {
        alert('Firstname cannot be empty.');
        return false;
    }
    
    if (isEmpty($('lastname').value)) {
        alert('Lastname cannot be empty.');
        return false;
    }
    
    if (isEmpty($('phone_num').value)) {
        alert('Telephone number cannot be empty.');
        return false;
    }
    
    if (isEmpty($('zip').value)) {
        alert('Zip/Postal code cannot be empty.');
        return false;
    }
    
    if ($('country').options[$('country').selectedIndex].value == '0') {
        alert('You need to select a country of residence.');
        return false;
    }
    
    if ($('citizenship').options[$('citizenship').selectedIndex].value == '0') {
        alert('You need to select a country of citizenship.');
        return false;
    }
    
    if ($('gender').options[$('gender').selectedIndex].value == '') {
        alert('You need to select a gender.');
        return false;
    }
    
    if (isEmpty($('ethnicity').value)) {
        alert('You need to provide the ethnicity of the member.');
        return false;
    }
    
    if ($('birthdate_month').options[$('birthdate_month').selectedIndex].value == '') {
        alert('You need to select the birthdate month.');
        return false;
    }
    
    if ($('birthdate_day').options[$('birthdate_day').selectedIndex].value == '') {
        alert('You need to select the birthdate day.');
        return false;
    }
    
    if (isEmpty($('birthdate_year').value) || parseInt($('birthdate_year').value) <= 0) {
        alert('You need to provide a valid year for birthdate year');
        return false;
    }
    
    return true;
    
}

function show_profile() {
    $('member_profile').setStyle('display', 'block');
    $('member_resumes').setStyle('display', 'none');
    $('member_career').setStyle('display', 'none');
    $('member_notes').setStyle('display', 'none');
    $('member_connections').setStyle('display', 'none');
    $('member_applications').setStyle('display', 'none');
    
    $('item_profile').setStyle('background-color', '#CCCCCC');
    
    if (member_id != "0") {
        $('item_resumes').setStyle('background-color', '');
        $('item_career').setStyle('background-color', '');
        $('item_notes').setStyle('background-color', '');
        $('item_connections').setStyle('background-color', '');
        $('item_applications').setStyle('background-color', '');
    }
}

function save_profile() {
    if (!validate_profile_form()) {
        return false;
    }
    
    var mode = 'update';
    if (member_id == '0') {
        mode = 'create';
    }
    
    var params = 'id=' + member_id;
    params = params + '&action=save_profile';
    params = params + '&employee=' + user_id;
    params = params + '&firstname=' + $('firstname').value;
    params = params + '&lastname=' + $('lastname').value;
    params = params + '&phone_num=' + $('phone_num').value;
    params = params + '&address=' + $('address').value;
    params = params + '&state=' + $('state').value;
    params = params + '&zip=' + $('zip').value;
    params = params + '&country=' + $('country').options[$('country').selectedIndex].value;
    params = params + '&citizenship=' + $('citizenship').options[$('citizenship').selectedIndex].value;
    params = params + '&hrm_gender=' + $('gender').options[$('gender').selectedIndex].value;
    params = params + '&hrm_ethnicity=' + $('ethnicity').value;
    params = params + '&hrm_birthdate=' + $('birthdate_year').value + '-' + $('birthdate_month').options[$('birthdate_month').selectedIndex].value + '-' + $('birthdate_day').options[$('birthdate_day').selectedIndex].value;
    
    if (mode == 'create') {
        params = params + '&email_addr=' + $('email_addr').value;
    }
    //alert(params);
    var uri = root + "/employees/member_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            // set_status('<pre>' + txt + '</pre>');
            // return;
            set_status('');
            if (txt == 'ko') {
                alert('An error occured while saving profile. Please makesure the email address does not already exist in the system.');
                return false;
            }
            
            if (mode == 'create') {
                location.replace('member.php?member_email_addr=' + $('email_addr').value);
                return;
            }
            
            show_profile();
        },
        onRequest: function(instance) {
            set_status('Saving profile...');
        }
    });
    
    request.send(params);
}

function approve_photo() {
    var params = 'id=' + member_id;
    params = params + '&action=approve_photo';
    
    var uri = root + "/employees/member_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            // set_status('<pre>' + txt + '</pre>');
            // return;
            set_status('');
            if (txt == 'ko') {
                alert('An error occured while approving photo.');
                return false;
            }
            
            $('accept_btn').disabled = true;
        },
        onRequest: function(instance) {
            set_status('Approving photo...');
        }
    });
    
    request.send(params);
}

function reject_photo() {
    var confirm = window.confirm('Are you sure to reject the uploaded photo?' + "\n\nOnce rejected, the photo will be deleted.");
    if (!confirm) {
        return false;
    }
    
    var params = 'id=' + member_id;
    params = params + '&action=reject_photo';
    
    var uri = root + "/employees/member_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            // set_status('<pre>' + txt + '</pre>');
            // return;
            set_status('');
            if (txt == 'ko') {
                alert('An error occured while rejecting photo.');
                return false;
            }
            
            $('photo_buttons').setStyle('display', 'none');
            $('photo_area').set('html', 'Photo rejected.');
        },
        onRequest: function(instance) {
            set_status('Rejecting photo...');
        }
    });
    
    request.send(params);
}

function show_resumes() {
    $('member_profile').setStyle('display', 'none');
    $('member_resumes').setStyle('display', 'block');
    $('member_career').setStyle('display', 'none');
    $('member_notes').setStyle('display', 'none');
    $('member_connections').setStyle('display', 'none');
    $('member_applications').setStyle('display', 'none');
    
    $('item_profile').setStyle('background-color', '');
    $('item_resumes').setStyle('background-color', '#CCCCCC');
    $('item_career').setStyle('background-color', '');
    $('item_notes').setStyle('background-color', '');
    $('item_connections').setStyle('background-color', '');
    $('item_applications').setStyle('background-color', '');
}

function update_resume(_resume_id) {
    show_upload_resume_popup(_resume_id);
}

function show_upload_resume_popup(_resume_id) {
    $('resume_id').value = _resume_id;
    $('upload_field').setStyle('display', 'block');
    show_window('upload_resume_window');
    window.scrollTo(0, 0);
}

function close_upload_resume_popup(_is_upload) {
    if (_is_upload) {
        if (isEmpty($('my_file').value)) {
            alert('You need to select a resume to upload.');
            return false;
        }
        
        close_safari_connection();
        return true;
    } else {
        close_window('upload_resume_window');
    }
}

function show_career() {
    $('member_profile').setStyle('display', 'none');
    $('member_resumes').setStyle('display', 'none');
    $('member_career').setStyle('display', 'block');
    $('member_notes').setStyle('display', 'none');
    $('member_connections').setStyle('display', 'none');
    $('member_applications').setStyle('display', 'none');
    
    $('item_profile').setStyle('background-color', '');
    $('item_resumes').setStyle('background-color', '');
    $('item_career').setStyle('background-color', '#CCCCCC');
    $('item_notes').setStyle('background-color', '');
    $('item_connections').setStyle('background-color', '');
    $('item_applications').setStyle('background-color', '');
}

function save_career() {
    if (isNaN($('expected_salary').value) || isNaN($('expected_salary_end').value) ||
        isNaN($('current_salary').value) || isNaN($('current_salary_end').value)) {
        alert('Salary fields must be a number.' + "\n\nEnter 0 if no value required.");
        return false;
    }
    
    if (isNaN($('notice_period').value)) {
        alert('Notice period must be a number.' + "\n\nEnter 0 if no value required.");
        return false;
    }
    
    var seeking = $('seeking').value.replace(/\n/g, "<br/>");
    seeking = add_slashes(seeking);
    
    var reason_leaving = $('reason_for_leaving').value.replace(/\n/g, "<br/>");
    reason_leaving = add_slashes(reason_leaving);
    
    var current_position = $('current_position').value.replace(/\n/g, "<br/>");
    current_position = add_slashes(current_position);
    
    var params = 'id=' + member_id;
    params = params + '&action=save_career';
    params = params + '&is_seeking=' + $('is_active_seeking_job').options[$('is_active_seeking_job').selectedIndex].value;
    params = params + '&can_travel=' + $('can_travel_relocate').options[$('can_travel_relocate').selectedIndex].value;
    params = params + '&seeking=' + seeking;
    params = params + '&reason_leaving=' + reason_leaving;
    params = params + '&current_position=' + current_position;
    params = params + '&notice_period=' + $('notice_period').value;
    params = params + '&expected_currency=' + $('expected_salary_currency').options[$('expected_salary_currency').selectedIndex].value;
    params = params + '&expected_salary=' + $('expected_salary').value;
    params = params + '&expected_salary_end=' + $('expected_salary_end').value;
    params = params + '&current_currency=' + $('current_salary_currency').options[$('current_salary_currency').selectedIndex].value;
    params = params + '&current_salary=' + $('current_salary').value;
    params = params + '&current_salary_end=' + $('current_salary_end').value;
    
    var uri = root + "/employees/member_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            if (txt == 'ko') {
                alert('An error occured while saving career profile.');
                return false;
            }
        },
        onRequest: function(instance) {
            set_status('Saving career profile...');
        }
    });
    
    request.send(params);
}

function show_notes() {
    $('member_profile').setStyle('display', 'none');
    $('member_resumes').setStyle('display', 'none');
    $('member_career').setStyle('display', 'none');
    $('member_notes').setStyle('display', 'block');
    $('member_connections').setStyle('display', 'none');
    $('member_applications').setStyle('display', 'none');
    
    $('item_profile').setStyle('background-color', '');
    $('item_resumes').setStyle('background-color', '');
    $('item_career').setStyle('background-color', '');
    $('item_notes').setStyle('background-color', '#CCCCCC');
    $('item_connections').setStyle('background-color', '');
    $('item_applications').setStyle('background-color', '');
}

function save_notes() {
    var params = 'id=' + member_id;
    params = params + '&action=save_notes';
    params = params + '&notes=' + encodeURIComponent($('extra_notes').value);
    
    var uri = root + "/employees/member_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            // set_status('<pre>' + txt + '</pre>');
            // return;
            set_status('');
            if (txt == 'ko') {
                alert('An error occured while saving notes.');
                return false;
            }
        },
        onRequest: function(instance) {
            set_status('Saving notes...');
        }
    });
    
    request.send(params);
}

function show_connections() {
    $('member_profile').setStyle('display', 'none');
    $('member_resumes').setStyle('display', 'none');
    $('member_career').setStyle('display', 'none');
    $('member_notes').setStyle('display', 'none');
    $('member_connections').setStyle('display', 'block');
    $('member_applications').setStyle('display', 'none');
    
    $('item_profile').setStyle('background-color', '');
    $('item_resumes').setStyle('background-color', '');
    $('item_career').setStyle('background-color', '');
    $('item_notes').setStyle('background-color', '');
    $('item_connections').setStyle('background-color', '#CCCCCC');
    $('item_applications').setStyle('background-color', '');
}

function update_referees() {
    var params = 'id=' + member_id;
    params = params + '&action=get_referees';
    
    var uri = root + "/employees/member_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            //set_status('<pre>' + txt + '</pre>');
            //return;
            set_status('');
            if (txt == 'ko') {
                alert('An error occured while getting candidates.');
                return false;
            }
            
            if (txt == '0') {
                $('div_referees').set('html', '<div class="empty_results">No candidates found.</div>');
                return;
            } else {
                var candidates = xml.getElementsByTagName('referee');
                var email_addrs = xml.getElementsByTagName('email_addr');
                
                var referees_table = new FlexTable('referees_table', 'referees');
                var header = new Row('');
                header.set(0, new Cell("Candidate (I referred who?)", '', 'header'));
                header.set(1, new Cell("&nbsp;", '', 'header actions'));
                referees_table.set(0, header);
                
                for (var i=0; i < candidates.length; i++) {
                    var row = new Row('');
                    row.set(0, new Cell('<a href="member.php?member_email_addr=' + email_addrs[i].childNodes[0].nodeValue + '">' + candidates[i].childNodes[0].nodeValue + '</a>', '', 'cell'));
                    
                    var action = '<a class="no_link" onClick="remove_referee(\'' + add_slashes(email_addrs[i].childNodes[0].nodeValue) + '\');">Remove</a>'
                    row.set(1, new Cell(action, '', 'cell actions'));
                    
                    referees_table.set((parseInt(i)+1), row);
                }
                
                $('div_referees').set('html', referees_table.get_html());
            }
        },
        onRequest: function(instance) {
            set_status('Loading connections...');
        }
    });
    
    request.send(params);
}

function update_referrers() {
    var params = 'id=' + member_id;
    params = params + '&action=get_referrers';
    
    var uri = root + "/employees/member_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            //set_status('<pre>' + txt + '</pre>');
            //return;
            set_status('');
            if (txt == 'ko') {
                alert('An error occured while getting referrers.');
                return false;
            }
            
            if (txt == '0') {
                $('div_referrers').set('html', '<div class="empty_results">No referrers found.</div>');
                return;
            } else {
                var referrers = xml.getElementsByTagName('referrer');
                var email_addrs = xml.getElementsByTagName('email_addr');
                
                var referrers_table = new FlexTable('referrers_table', 'referrers');
                var header = new Row('');
                header.set(0, new Cell("Referrer (Who referred me?)", '', 'header'));
                header.set(1, new Cell("&nbsp;", '', 'header actions'));
                referrers_table.set(0, header);
                
                for (var i=0; i < referrers.length; i++) {
                    var row = new Row('');
                    row.set(0, new Cell('<a href="member.php?member_email_addr=' + email_addrs[i].childNodes[0].nodeValue + '">' + referrers[i].childNodes[0].nodeValue + '</a>', '', 'cell'));
                    
                    var action = '<a class="no_link" onClick="remove_referrer(\'' + add_slashes(email_addrs[i].childNodes[0].nodeValue) + '\');">Remove</a>&nbsp|&nbsp;<a class="no_link" onClick="reward(\'' + add_slashes(email_addrs[i].childNodes[0].nodeValue) + '\');">Reward</a>'
                    row.set(1, new Cell(action, '', 'cell actions'));
                    
                    referrers_table.set((parseInt(i)+1), row);
                }
                
                $('div_referrers').set('html', referrers_table.get_html());
            }
        },
        onRequest: function(instance) {
            set_status('Loading connections...');
        }
    });
    
    request.send(params);
}

function remove_referrer(_referrer_email) {
    var is_confirm = confirm("Are you sure to remove referrer (" + _referrer_email + ")?");
    if (!is_confirm) {
        return;
    }
    
    var params = 'id=' + member_id;
    params = params + '&action=remove_referrer';
    params = params + '&employee=' + user_id;
    params = params + '&referrer=' + _referrer_email;
    
    var uri = root + "/employees/member_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            // set_status('<pre>' + txt + '</pre>');
            // return;
            set_status('');
            if (txt == 'ko') {
                alert('An error occured while removing referrer from connection.');
                return false;
            }
            
            update_referrers();
        },
        onRequest: function(instance) {
            set_status('Saving connections...');
        }
    });
    
    request.send(params);
}

function remove_referee(_referee_email) {
    var is_confirm = confirm("Are you sure to remove candidate (" + _referee_email + ")?");
    if (!is_confirm) {
        return;
    }
    
    var params = 'id=' + member_id;
    params = params + '&action=remove_referee';
    params = params + '&employee=' + user_id;
    params = params + '&referee=' + _referee_email;
    
    var uri = root + "/employees/member_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            // set_status('<pre>' + txt + '</pre>');
            // return;
            set_status('');
            if (txt == 'ko') {
                alert('An error occured while removing candidate from connection.');
                return false;
            }
            
            update_referees();
        },
        onRequest: function(instance) {
            set_status('Saving connections...');
        }
    });
    
    request.send(params);
}

function show_add_referrers_popup() {
    show_window('add_referrers_window');
    window.scrollTo(0, 0);
}

function close_add_referrers_popup(_is_submit) {
    if (_is_submit) {
        var potentials = new Array();
        var counter = 0;
        for (var i=0; i < $('referrers').options.length; i++) {
            if ($('referrers').options[i].selected) {
                potentials[counter] = $('referrers').options[i].value;
                counter++;
            }
        }
        
        if (potentials.length <= 0) {
            alert('You need to select at least one referrer.');
            return false;
        }
        
        var referrers = '';
        for (var i=0; i < potentials.length; i++) {
            referrers = referrers + potentials[i];
            if (i < potentials.length - 1) {
                referrers = referrers + ';';
            }
        }
        
        var params = 'id=' + member_id;
        params = params + '&action=add_referrer';
        params = params + '&employee=' + user_id;
        params = params + '&referrers=' + referrers;
        
        var uri = root + "/employees/member_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                // set_status('<pre>' + txt + '</pre>');
                // return;
                set_status('');
                if (txt == 'ko') {
                    alert('An error occured while adding referrers.');
                    return false;
                }
                
                close_window('add_referrers_window');
                update_referrers();
            },
            onRequest: function(instance) {
                set_status('Added referrers...');
            }
        });

        request.send(params);
    } else {
        close_window('add_referrers_window');
    }
}

function show_add_candidates_popup() {
    show_window('add_candidates_window');
    window.scrollTo(0, 0);
}

function close_add_candidates_popup(_is_submit) {
    if (_is_submit) {
        var potentials = new Array();
        var counter = 0;
        for (var i=0; i < $('candidates').options.length; i++) {
            if ($('candidates').options[i].selected) {
                potentials[counter] = $('candidates').options[i].value;
                counter++;
            }
        }
        
        if (potentials.length <= 0) {
            alert('You need to select at least one candidate.');
            return false;
        }
        
        var candidates = '';
        for (var i=0; i < potentials.length; i++) {
            candidates = candidates + potentials[i];
            if (i < potentials.length - 1) {
                candidates = candidates + ';';
            }
        }
        
        var params = 'id=' + member_id;
        params = params + '&action=add_referee';
        params = params + '&employee=' + user_id;
        params = params + '&referees=' + candidates;
        
        var uri = root + "/employees/member_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                // set_status('<pre>' + txt + '</pre>');
                // return;
                set_status('');
                if (txt == 'ko') {
                    alert('An error occured while adding candidates.');
                    return false;
                }
                
                close_window('add_candidates_window');
                update_referees();
            },
            onRequest: function(instance) {
                set_status('Added candidates...');
            }
        });

        request.send(params);
    } else {
        close_window('add_candidates_window');
    }
}

function show_applications() {
    $('member_profile').setStyle('display', 'none');
    $('member_resumes').setStyle('display', 'none');
    $('member_career').setStyle('display', 'none');
    $('member_notes').setStyle('display', 'none');
    $('member_connections').setStyle('display', 'none');
    $('member_applications').setStyle('display', 'block');
    
    $('item_profile').setStyle('background-color', '');
    $('item_resumes').setStyle('background-color', '');
    $('item_career').setStyle('background-color', '');
    $('item_notes').setStyle('background-color', '');
    $('item_connections').setStyle('background-color', '');
    $('item_applications').setStyle('background-color', '#CCCCCC');
}

function update_applications() {
    var params = 'id=' + member_id;
    params = params + '&action=get_applications';
    params = params + '&order_by=' + order_by + ' ' + order;
    params = params + '&filter=' + filter;
    
    var uri = root + "/employees/member_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            //set_status('<pre>' + txt + '</pre>');
            //return;
            set_status('');
            if (txt == 'ko') {
                alert('An error occured while getting applications.');
                return false;
            }
            
            if (txt == '0') {
                $('applications').set('html', '<div class="empty_results">No applications found.</div>');
                return;
            } else {
                var ids = xml.getElementsByTagName('id');
                var jobs = xml.getElementsByTagName('job');
                var job_ids = xml.getElementsByTagName('job_id');
                var employers = xml.getElementsByTagName('employer');
                var employer_ids = xml.getElementsByTagName('employer_id');
                var referrer_names = xml.getElementsByTagName('referrer_name');
                var referrers = xml.getElementsByTagName('referrer');
                var referred_ons = xml.getElementsByTagName('formatted_referred_on');
                var viewed_ons = xml.getElementsByTagName('formatted_employer_agreed_terms_on');
                var employed_ons = xml.getElementsByTagName('formatted_employed_on');
                var rejected_ons = xml.getElementsByTagName('formatted_employer_rejected_on');
                var deleted_ons = xml.getElementsByTagName('formatted_employer_removed_on');
                var has_testimonies = xml.getElementsByTagName('has_testimony');
                var has_remarks = xml.getElementsByTagName('has_employer_remarks');
                var resume_ids = xml.getElementsByTagName('resume_id');
                var resume_files = xml.getElementsByTagName('file_name');
                
                var applications_table = new FlexTable('applications_table', 'applications');
                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'employers.name');\">Employers</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'jobs.title');\">Job</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'members.lastname');\">Referrer</a>", '', 'header'));
                header.set(3, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'referrals.referred_on');\">Applied On</a>", '', 'header'));
                header.set(4, new Cell("Status", '', 'header'));
                header.set(5, new Cell("Testimony", '', 'header'));
                header.set(6, new Cell("Resume Submitted", '', 'header'));
                applications_table.set(0, header);
                
                for (var i=0; i < ids.length; i++) {
                    var row = new Row('');
                    row.set(0, new Cell('<a href="employer.php?id=' + employer_ids[i].childNodes[0].nodeValue + '">' + employers[i].childNodes[0].nodeValue + '</a>', '', 'cell'));
                    row.set(1, new Cell('<a class="no_link" onClick="show_job_desc(' + job_ids[i].childNodes[0].nodeValue + ');">' + jobs[i].childNodes[0].nodeValue + '</a>', '', 'cell'));
                    row.set(2, new Cell('<a href="member.php?member_email_addr=' + add_slashes(referrers[i].childNodes[0].nodeValue) + '">' + referrer_names[i].childNodes[0].nodeValue + '</a>', '', 'cell'));
                    row.set(3, new Cell(referred_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var status = '<span class="not_viewed_yet">Not Viewed Yet</span>';
                    if (viewed_ons[i].childNodes.length > 0) {
                        status = '<span class="viewed">Viewed On:</span> ' + viewed_ons[i].childNodes[0].nodeValue;
                    }
                    
                    if (employed_ons[i].childNodes.length > 0) {
                        status = '<span class="employed">Employed On:</span> ' + employed_ons[i].childNodes[0].nodeValue;
                    }
                    
                    if (rejected_ons[i].childNodes.length > 0) {
                        status = '<span class="rejected">Rejected On:</span> ' + viewed_ons[i].childNodes[0].nodeValue;
                    }
                    
                    if (deleted_ons[i].childNodes.length > 0) {
                        status = '<span class="deleted">Deleted On:</span> ' + viewed_ons[i].childNodes[0].nodeValue;
                    }
                    
                    if (has_remarks[i].childNodes[0].nodeValue == '1') {
                        status = status + '<br/><a class="no_link" onClick="show_employer_remarks(' + ids[i].childNodes[0].nodeValue + ');">Employer Remarks</a>';
                    }
                    row.set(4, new Cell(status, '', 'cell testimony'));
                    
                    var testimony = 'None Provided';
                    if (has_testimonies[i].childNodes[0].nodeValue == '1') {
                        testimony = '<a class="no_link" onClick="show_testimony(' + ids[i].childNodes[0].nodeValue + ');">Show</a>';
                    }
                    row.set(5, new Cell(testimony, '', 'cell testimony'));
                    
                    row.set(6, new Cell('<a href="resume.php?id=' + resume_ids[i].childNodes[0].nodeValue + '">' + resume_files[i].childNodes[0].nodeValue + '</a>', '', 'cell testimony'));
                    
                    applications_table.set((parseInt(i)+1), row);
                }
                
                $('applications').set('html', applications_table.get_html());
            }
        },
        onRequest: function(instance) {
            set_status('Loading connections...');
        }
    });
    
    request.send(params);
}

function show_testimony(_referral_id) {
    var params = 'id=' + _referral_id;
    params = params + '&action=get_testimony';
    
    var uri = root + "/employees/member_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (isEmpty(txt)) {
                alert('Testimony not found!');
                return;
            }
            
            $('testimony').set('html', txt);
            set_status('');
            show_window('testimony_window');
            window.scrollTo(0, 0);
        },
        onRequest: function(instance) {
            set_status('Loading testimony...');
        }
    });
    
    request.send(params);
}

function close_testimony() {
    close_window('testimony_window');
}

function show_job_desc(_job_id) {
    var params = 'id=' + _job_id;
    params = params + '&action=get_job_desc';
    
    var uri = root + "/employees/member_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (isEmpty(txt)) {
                alert('Job not found!');
                return;
            }
            
            $('job_desc').set('html', txt);
            set_status('');
            show_window('job_desc_window');
            window.scrollTo(0, 0);
        },
        onRequest: function(instance) {
            set_status('Loading job desccription...');
        }
    });
    
    request.send(params);
}

function close_job_desc() {
    close_window('job_desc_window');
}

function show_employer_remarks(_referral_id) {
    var params = 'id=' + _referral_id;
    params = params + '&action=get_employer_remarks';
    
    var uri = root + "/employees/member_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (isEmpty(txt)) {
                alert('Remarks not found!');
                return;
            }
            
            $('employer_remarks').set('html', txt);
            set_status('');
            show_window('employer_remarks_window');
            window.scrollTo(0, 0);
        },
        onRequest: function(instance) {
            set_status('Loading employer remarks...');
        }
    });
    
    request.send(params);
}

function close_employer_remarks() {
    close_window('employer_remarks_window');
}

function show_apply_job_popup(_resume_id, _resume_file_name) {
    $('resume_id').value = _resume_id;
    $('resume_file_name').set('html', _resume_file_name);
    show_window('apply_job_window');
    window.scrollTo(0, 0);
    filter_jobs();
}

function close_apply_job_popup(_is_apply_job) {
    if (_is_apply_job) {
        var selected_jobs = jobs_list.get_selected_values();
        
        if (selected_jobs.length <= 0 && isEmpty($('selected_jobs').value)) {
            alert('Please select at least one job.');
            return;
        }
        
        if (!confirm('Confirm to apply the selected jobs for candidate?')) {
            return;
        }
        
        var params = 'id=' + member_id;
        params = params + '&action=apply_job';
        params = params + '&employee=' + user_id;
        params = params + '&resume=' + $('resume_id').value;
        
        var selected_job_str = $('selected_jobs').value;
        if (selected_jobs.length > 0) {
            selected_job_str = selected_job_str + ',';
            for (var i=0; i < selected_jobs.length; i++) {
                var item_value = selected_jobs[i].split('|');
                selected_job_str = selected_job_str + item_value[item_value.length-1];

                if (i < selected_jobs.length-1) {
                    selected_job_str = selected_job_str + ',';
                }
            }
        }
        params = params + '&jobs=' + selected_job_str;
        
        var referrer = '';
        if ($('apply_job_referrer').options != null) {
            referrer = $('apply_job_referrer').options[$('apply_job_referrer').selectedIndex].value;
        } else {
            referrer = $('apply_job_referrer').value;
        }
        params = params + '&referrer=' + referrer;
        
        var uri = root + "/employees/member_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    alert('An error occured while applying job.');
                    return;
                }
                
                if (txt.indexOf('failed_jobs') > -1) {
                    var job_titles = xml.getElementsByTagName('title');
                    var employers = xml.getElementsByTagName('employer');
                    var expire_ons = xml.getElementsByTagName('expire_on');
                    
                    var error_msg = 'The following jobs failed to apply:' + "\n\n";
                    for (var i=0; i < job_titles.length; i++) {
                        error_msg = error_msg + '- ' + job_titles[i].childNodes[0].nodeValue + ' (' + employers[i].childNodes[0].nodeValue + ') [exp: ' + expire_ons[i].childNodes[0].nodeValue + ']' + "\n";
                    }
                    
                    alert(error_msg);
                    return;
                }
                
                set_status('');
                close_window('apply_job_window');
                location.replace('member.php?member_email_addr=' + member_id + '&page=applications');
            },
            onRequest: function(instance) {
                set_status('Applying job...');
            }
        });

        request.send(params);
    } else {
        close_window('apply_job_window');
    }
}

function filter_jobs() {
    if ($('employers') == null) {
        $('jobs_selector').set('html', '<span class="no_employers">No opened jobs found.</span>');
        $('job_description').set('html', '');
        $('apply_btn').disabled = true;
        return;
    }
    
    $('apply_btn').disabled = false;
    var employer = $('employers').options[$('employers').selectedIndex].value;
    var params = 'id=' + employer + '&action=get_filtered_jobs';
    var uri = root + "/employees/member_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while retrieving jobs.');
                return;
            }
            
            if (txt == '0') {
                $('jobs_selector').set('html', '<span class="no_employers">No opened jobs found.</span>');
                $('job_description').set('html', '');
                return;
            }
            
            jobs_list.clear();
            $('counter_lbl').set('html', '0');
            var ids = xml.getElementsByTagName('id');
            var titles = xml.getElementsByTagName('title');
            var industries = xml.getElementsByTagName('industry');
            var expirys = xml.getElementsByTagName('formatted_expire_on');
            
            for (var i=0; i < ids.length; i++) {
                var item = '<span class="job_item">' + titles[i].childNodes[0].nodeValue + '</span><br/><span class="job_industry_item">' + industries[i].childNodes[0].nodeValue + '</span><br/><span class="job_expiry_item">Expire On: ' + expirys[i].childNodes[0].nodeValue + '</span>';
                jobs_list.add_item(item, ids[i].childNodes[0].nodeValue);
            }
            
            jobs_list.show();
        }
    });
    
    request.send(params);
}

function get_job_description() {
    var counter = parseInt($('counter_lbl').get('html'));
    if (jobs_list.items.length <= 0 || isEmpty(jobs_list.selected_value)) {
        counter = counter - 1;
        $('counter_lbl').set('html', counter);
        return;
    }
    
    counter = counter + 1;
    $('counter_lbl').set('html', counter);
    var params = 'id=' + jobs_list.selected_value + '&action=get_job_desc';
    var uri = root + "/employees/member_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while retrieving job description.');
                return;
            }
            
            if (txt == '0') {
                $('jobs_selector').set('html', '<span class="no_employers">No opened jobs found.</span>');
                $('job_description').set('html', '');
                return;
            }
            
            $('job_description').set('html', txt);
        }
    });
    
    request.send(params);
}

function clear_pre_selected_jobs() {
    if (confirm('Are you sure to clear all pre-selected jobs?')) {
        $('pre_selected_jobs_list').set('html', '(None Selected)');
        $('selected_jobs').value = '';
    }
}

function update_job_profiles() {
    
}

function validate_job_profile() {
    if ($('specialization').selectedIndex == 0) {
        alert('You need to select a specialization.');
        return false;
    } 
    
    if (isEmpty($('position_title').value)) {
        alert('Job Title cannot be empty.');
        return false;
    }
    
    if (isEmpty($('work_from_year').value) || $('work_from_month').selectedIndex == 0) {
        alert('Duration (beginning) cannot be empty.');
        return false;
    } else {
        if (isNaN($('work_from_year').value)) {
            alert('Only numbers are accepted for year.');
            return false;
        }        
    }
    
    if ($('work_to_present').checked == false) {
        if (isEmpty($('work_to_year').value) || $('work_to_month').selectedIndex == 0) {
            alert('Duration (ending) cannot be empty.');
            return false;
        } else {
            if (isNaN($('work_from_year').value)) {
                alert('Only numbers are accepted for year.');
                return false;
            }
        }
    }
    
    if (isEmpty($('company').value)) {
        alert('Employer cannot be empty.');
        return false;
    }
    
    if ($('emp_desc').selectedIndex == 0) {
        alert('You need to select your employer\'s description.');
        return false;
    }
    
    if ($('emp_specialization').selectedIndex == 0) {
        alert('You need to select your employer\'s specialization.');
        return false;
    }
    
    if (isNaN($('organization_size').value)) {
        alert('Only numbers are accepted for Number of Direct Reports.');
        return false;
    } 
    
    return true;
}

function toggle_work_to() {
    $('work_to_month').selectedIndex = 0;
    $('work_to_year').value = '';
    
    if ($('work_to_present').checked) {
        $('work_to_dropdown').setStyle('display', 'none');
    } else {
        $('work_to_dropdown').setStyle('display', 'inline');
    }
}

function show_job_profile_popup(_id) {
    if (_id <= 0) {
        // new
        $('job_profile_id').value = 0;
        $('specialization').selectedIndex = 0;
        $('position_title').value = '';
        $('position_superior_title').value = '';
        $('organization_size').value = '';
        $('work_from_month').selectedIndex = 0;
        $('work_from_year').value = 'yyyy';
        $('work_to_month').selectedIndex = 0;
        $('work_to_year').value = 'yyyy';
        $('company').value = '';
        $('emp_desc').selectedIndex = 0;
        $('emp_specialization').selectedIndex = 0;
        
        show_window('job_profile_window');
        window.scrollTo(0, 0);
    } else {
        // load
        var params = 'id=' + member_id + '&action=get_job_profile';
        
        var uri = root + "/employees/member_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                set_status('');
                
                if (txt == 'ko') {
                    alert('An error occured when loading job profile. Please try again later.');
                    return;
                }
                
                var specialization = xml.getElementsByTagName('specialization');
                var position_title = xml.getElementsByTagName('position_title');
                var position_superior = xml.getElementsByTagName('position_superior_title');
                var org_size = xml.getElementsByTagName('organization_size');
                var work_from = xml.getElementsByTagName('work_from');
                var work_to = xml.getElementsByTagName('work_to');
                var company = xml.getElementsByTagName('employer');
                var emp_desc = xml.getElementsByTagName('employer_description');
                var emp_specialization = xml.getElementsByTagName('employer_specialization');
                
                $('job_profile_id').value = _id;
                $('position_title').value = position_title[0].childNodes[0].nodeValue;
                $('position_superior_title').value = position_superior[0].childNodes[0].nodeValue;
                $('organization_size').value = org_size[0].childNodes[0].nodeValue;
                $('company').value = company[0].childNodes[0].nodeValue.replace('&amp;', '&');
                
                for (var i=0; i < $('specialization').options.length; i++) {
                    if ($('specialization').options[i].value == specialization[0].childNodes[0].nodeValue) {
                        $('specialization').selectedIndex = i;
                        break;
                    }
                }
                
                for (var i=0; i < $('emp_desc').options.length; i++) {
                    if ($('emp_desc').options[i].value == emp_desc[0].childNodes[0].nodeValue) {
                        $('emp_desc').selectedIndex = i;
                        break;
                    }
                }
                
                for (var i=0; i < $('emp_specialization').options.length; i++) {
                    if ($('emp_specialization').options[i].value == emp_specialization[0].childNodes[0].nodeValue) {
                        $('emp_specialization').selectedIndex = i;
                        break;
                    }
                }
                
                var work_from_items = work_from[0].childNodes[0].nodeValue.split('-');
                var work_from_month = work_from_items[1];
                var work_from_year = work_from_items[0];
                
                $('work_from_year').value = work_from_year;
                for (var i=0; i < $('work_from_month').options.length; i++) {
                    if ($('work_from_month').options[i].value == work_from_month) {
                        $('work_from_month').selectedIndex = i;
                        break;
                    }
                }
                
                var work_to_items = null;
                if (work_to[0].childNodes.length > 0) {
                    work_to_items = work_to[0].childNodes[0].nodeValue.split('-');
                }
                
                if (work_to_items == null) {
                    $('work_to_month').selectedIndex = 0;
                    $('work_to_year').value = '';
                    $('work_to_dropdown').setStyle('display', 'none');
                    $('work_to_present').checked = true;
                } else {
                    $('work_to_dropdown').setStyle('display', 'block');
                    $('work_to_present').checked = false;
                    
                    var work_to_month = work_to_items[1];
                    var work_to_year = work_to_items[0];
                    
                    $('work_to_year').value = work_to_year;
                    for (var i=0; i < $('work_to_month').options.length; i++) {
                        if ($('work_to_month').options[i].value == work_to_month) {
                            $('work_to_month').selectedIndex = i;
                            break;
                        }
                    }
                } 
                
                show_window('job_profile_window');
                window.scrollTo(0, 0);
            }
        });

        request.send(params);
    }
}

function close_job_profile_popup(_is_save) {
    if (_is_save) {
        if (!validate_job_profile()) {
            return;
        }
        
        var work_from = $('work_from_year').value + '-' + $('work_from_month').options[$('work_from_month').selectedIndex].value + '-00';

        var work_to = 'NULL';
        if ($('work_to_present').checked == false) {
            work_to = $('work_to_year').value + '-' + $('work_to_month').options[$('work_to_month').selectedIndex].value + '-00';
        }
        
        var params = 'id=' + $('job_profile_id').value + '&action=save_job_profile';
        params = params + '&member=' + id;
        params = params + '&specialization=' + $('specialization').value;
        params = params + '&position_title=' + $('position_title').value;
        params = params + '&superior_title=' + $('position_superior_title').value;
        params = params + '&organization_size=' + $('organization_size').value;
        params = params + '&work_from=' + work_from;
        params = params + '&work_to=' + work_to;
        params = params + '&employer=' + encodeURIComponent($('company').value);
        params = params + '&emp_desc=' + $('emp_desc').value;
        params = params + '&emp_specialization=' + $('emp_specialization').value;
        
        var uri = root + "/employees/member_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                set_status('');

                if (txt == 'ko') {
                    alert('An error occured when saving job profile.' + "\n\n" + 'Please try again later.');
                    return;
                }
                
                close_window('job_profile_window');
            }
        });

        request.send(params);
    } else {
        close_window('job_profile_window');
    }
}

function delete_job_profile(_id) {
    var msg = 'Are you sure to delete the selected job profile?';
    
    if (confirm(msg)) {
        var params = 'id=' + _id + '&action=remove_job_profile';
        
        var uri = root + "/employees/member_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                set_status('');

                if (txt == 'ko') {
                    alert('An error occured when deleting job profile.' + "\n\n" + 'Please try again later.');
                    return;
                }
                
                location.reload(true);
            }
        });

        request.send(params);
    }
}

function onDomReady() {
    initialize_page();
    
    switch (current_page) {
        case 'resumes':
            show_resumes()
            break;
        case 'career':
            show_career();
            break;
        case 'notes':
            show_notes();
            break;
        case 'applications':
            show_applications();
            break;
        case 'referrers':
            show_connections();
            break;
        default:
            show_profile();
            break;
    }
    
    $('jobs_selector').addEvent('click', get_job_description);
}

window.addEvent('domready', onDomReady);
