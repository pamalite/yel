var order = 'desc';
var order_by = 'referrals.referred_on';

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
            update_applications();
            break;
    }
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
    $('member_notes').setStyle('display', 'none');
    $('member_connections').setStyle('display', 'none');
    $('member_applications').setStyle('display', 'none');
    
    $('item_profile').setStyle('background-color', '#CCCCCC');
    
    if (member_id != "0") {
        $('item_resumes').setStyle('background-color', '');
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
    $('member_notes').setStyle('display', 'none');
    $('member_connections').setStyle('display', 'none');
    $('member_applications').setStyle('display', 'none');
    
    $('item_profile').setStyle('background-color', '');
    $('item_resumes').setStyle('background-color', '#CCCCCC');
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

function show_notes() {
    $('member_profile').setStyle('display', 'none');
    $('member_resumes').setStyle('display', 'none');
    $('member_notes').setStyle('display', 'block');
    $('member_connections').setStyle('display', 'none');
    $('member_applications').setStyle('display', 'none');
    
    $('item_profile').setStyle('background-color', '');
    $('item_resumes').setStyle('background-color', '');
    $('item_notes').setStyle('background-color', '#CCCCCC');
    $('item_connections').setStyle('background-color', '');
    $('item_applications').setStyle('background-color', '');
}

function save_notes() {
    if (isNaN($('expected_salary').value) || isNaN($('expected_salary_end').value) ||
        isNaN($('current_salary').value) || isNaN($('current_salary_end').value)) {
        alert('Salary fields must be a number.' + "\n\nEnter 0 if no value required.");
        return false;
    }
    
    if (isNaN($('notice_period').value)) {
        alert('Notice period must be a number.' + "\n\nEnter 0 if no value required.");
        return false;
    }
    
    var params = 'id=' + member_id;
    params = params + '&action=save_notes';
    params = params + '&employee=' + user_id;
    params = params + '&is_active_seeking_job=' + $('is_active_seeking_job').options[$('is_active_seeking_job').selectedIndex].value;
    params = params + '&seeking=' + encodeURIComponent($('seeking').value);
    params = params + '&expected_salary=' + $('expected_salary').value;
    params = params + '&expected_salary_end=' + $('expected_salary_end').value;
    params = params + '&can_travel_relocate=' + $('can_travel_relocate').options[$('can_travel_relocate').selectedIndex].value;
    params = params + '&reason_for_leaving=' + encodeURIComponent($('reason_for_leaving').value);
    params = params + '&current_position=' + encodeURIComponent($('current_position').value);
    params = params + '&current_salary=' + $('current_salary').value;
    params = params + '&current_salary_end=' + $('current_salary_end').value;
    params = params + '&notice_period=' + $('notice_period').value;
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
    $('member_notes').setStyle('display', 'none');
    $('member_connections').setStyle('display', 'block');
    $('member_applications').setStyle('display', 'none');
    
    $('item_profile').setStyle('background-color', '');
    $('item_resumes').setStyle('background-color', '');
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
    $('member_notes').setStyle('display', 'none');
    $('member_connections').setStyle('display', 'none');
    $('member_applications').setStyle('display', 'block');
    
    $('item_profile').setStyle('background-color', '');
    $('item_resumes').setStyle('background-color', '');
    $('item_notes').setStyle('background-color', '');
    $('item_connections').setStyle('background-color', '');
    $('item_applications').setStyle('background-color', '#CCCCCC');
}

function update_applications() {
    var params = 'id=' + member_id;
    params = params + '&action=get_applications';
    params = params + '&order_by=' + order_by + ' ' + order;
    
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
                var has_testimonies = xml.getElementsByTagName('has_testimony');
                
                var applications_table = new FlexTable('applications_table', 'applications');
                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'employers.name');\">Employers</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'jobs.title');\">Job</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'members.lastname');\">Referrer</a>", '', 'header'));
                header.set(3, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'referrals.referred_on');\">Applied On</a>", '', 'header'));
                header.set(4, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'referrals.employer_agreed_terms_on');\">Employer Viewed On</a>", '', 'header'));
                header.set(5, new Cell("Testimony", '', 'header'));
                applications_table.set(0, header);
                
                for (var i=0; i < ids.length; i++) {
                    var row = new Row('');
                    row.set(0, new Cell('<a href="employer.php?id=' + employer_ids[i].childNodes[0].nodeValue + '">' + employers[i].childNodes[0].nodeValue + '</a>', '', 'cell'));
                    row.set(1, new Cell('<a class="no_link" onClick="show_job_desc(' + job_ids[i].childNodes[0].nodeValue + ');">' + jobs[i].childNodes[0].nodeValue + '</a>', '', 'cell'));
                    row.set(2, new Cell('<a href="member.php?member_email_addr=' + add_slashes(referrers[i].childNodes[0].nodeValue) + '">' + referrer_names[i].childNodes[0].nodeValue + '</a>', '', 'cell'));
                    row.set(3, new Cell(referred_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var viewed_on = 'Not Viewed Yet';
                    if (viewed_ons[i].childNodes.length > 0) {
                        viewed_on = viewed_ons[i].childNodes[0].nodeValue;
                    }
                    row.set(4, new Cell(viewed_on, '', 'cell'));
                    
                    var testimony = 'None Provided';
                    if (has_testimonies[i].childNodes[0].nodeValue == '1') {
                        testimony = '<a class="no_link" onClick="show_testimony(' + ids[i].childNodes[0].nodeValue + ');">Show</a>';
                    }
                    row.set(5, new Cell(testimony, '', 'cell testimony'));
                    
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

function onDomReady() {
    initialize_page();
    
    switch (current_page) {
        case 'resumes':
            show_resumes()
            break;
        case 'notes':
            show_notes();
            break;
        case 'applications':
            show_applications();
            break;
        default:
            show_profile();
            break;
    }
}

window.addEvent('domready', onDomReady);
