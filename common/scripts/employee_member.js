var resumes_order = 'desc';
var resumes_order_by = 'modified_on';

function resumes_ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function sort_by(_table, _column) {
    switch (_table) {
        case 'resumes':
            order_by = _column;
            ascending_or_descending();
            show_resumes();
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
