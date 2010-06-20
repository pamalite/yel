var order = 'desc';
var order_by = 'created_on';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function sort_by(_table, _column) {
    switch (_table) {
        case 'jobs':
            order_by = _column;
            ascending_or_descending();
            show_updated_jobs();
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
    $('member_applications').setStyle('display', 'none');
    $('job').setStyle('display', 'none');
    
    $('item_profile').setStyle('background-color', '#CCCCCC');
    
    if (member_id != "0") {
        $('item_resumes').setStyle('background-color', '');
        $('item_notes').setStyle('background-color', '');
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
