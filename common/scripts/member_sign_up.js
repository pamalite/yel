function reset_field(_field) {
    _field.style.borderColor = '';
    _field.style.borderStyle = '';
}

function validate_sign_up() {
    if ($('firstname').value == '') {
        alert('First Name cannot be empty.');
        $('firstname').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        reset_field($('firstname'));
    }
    
    if ($('lastname').value == '') {
        alert('Last Name cannot be empty.');
        $('lastname').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        reset_field($('lastname'));
    }
    
    if (!isEmail($('email_addr').value)) {
        alert('The e-mail address provided is not valid.');
        $('email_addr').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        reset_field($('email_addr'));
    }
    
    if ($('password').value != '' && $('password').value != $('password_confirm').value) {
        alert('The passwords you entered do not match.');
        $('password').setStyle('border', '2px solid #FF0000');
        $('password_confirm').setStyle('border', '2px solid #FF0000');
        return false;
    } else if ($('password').value == '') {
        alert('Password cannot be empty.');
        $('password').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        reset_field($('password'));
        reset_field($('password_confirm'))
    }
    
    if ($('forget_password_question').options[$('forget_password_question').selectedIndex].value == 0) {
        alert('You must at least choose a password hint.');
        $('forget_password_question').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        reset_field($('forget_password_question'));
    }
    
    if ($('forget_password_answer').value == '') {
        alert('The answer to your password hint cannot be empty');
        $('forget_password_answer').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        reset_field($('forget_password_answer'));
    }
    
    if ($('phone_num').value == '') {
        alert('Telephone number cannot be empty.');
        $('phone_num').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        reset_field($('phone_num'));
    }
    
    if (!$('agreed_terms').checked) {
        alert('You need to agree with our Terms and Conditions before you can sign up.');
        return false;
    }
    
    return true;
}

function validate_job_profile() {
    if ($('specialization').selectedIndex == 0) {
        alert('You need to select a specialization.');
        $('specialization').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        reset_field($('specialization'));
    }
    
    if (isEmpty($('position_title').value)) {
        alert('Job Title cannot be empty.');
        $('position_title').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        reset_field($('position_title'));
    }
    
    if (isEmpty($('work_from_year').value) || $('work_from_month').selectedIndex == 0) {
        alert('Duration (beginning) cannot be empty.');
        $('work_from_year').setStyle('border', '2px solid #FF0000');
        $('work_from_month').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        if (isNaN($('work_from_year').value)) {
            alert('Only numbers are accepted for year.');
            $('work_from_year').setStyle('border', '2px solid #FF0000');
            return false;
        }
        
        reset_field($('work_from_year'));
        reset_field($('work_from_month'));
    }
    
    if ($('work_to_present').checked == false) {
        if (isEmpty($('work_to_year').value) || $('work_to_month').selectedIndex == 0) {
            alert('Duration (ending) cannot be empty.');
            $('work_to_year').setStyle('border', '2px solid #FF0000');
            $('work_to_month').setStyle('border', '2px solid #FF0000');
            return false;
        } else {
            if (isNaN($('work_from_year').value)) {
                alert('Only numbers are accepted for year.');
                $('work_from_year').setStyle('border', '2px solid #FF0000');
                return false;
            }
            
            reset_field($('work_to_year'));
            reset_field($('work_to_month'));
        }
    }
    
    if (isEmpty($('company').value)) {
        alert('Employer cannot be empty.');
        $('company').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        reset_field($('company'));
    }
    
    if ($('emp_desc').selectedIndex == 0) {
        alert('You need to select your employer\'s description.');
        $('emp_desc').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        reset_field($('emp_desc'));
    }
    
    if ($('emp_specialization').selectedIndex == 0) {
        alert('You need to select your employer\'s specialization.');
        $('emp_specialization').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        reset_field($('emp_specialization'));
    }
    
    if (isNaN($('organization_size').value)) {
        alert('Only numbers are accepted for Number of Direct Reports.');
        $('organization_size').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        reset_field($('organization_size'));
    }
    
    if (isNaN($('total_work_years').value)) {
        alert('Only numbers are accepted for Number of Direct Reports.');
        $('total_work_years').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        reset_field($('total_work_years'));
    }
    return true;
}

function sign_up() {
    if (!validate_sign_up()) {
        return;
    }
    
    var params = 'email_addr=' + $('email_addr').value + '&action=sign_up';
    params = params + '&firstname=' + $('firstname').value;
    params = params + '&lastname=' + $('lastname').value;
    params = params + '&password=' + $('password').value;
    params = params + '&forget_password_question=' + $('forget_password_question').value;
    params = params + '&forget_password_answer=' + $('forget_password_answer').value;
    params = params + '&phone_num=' + $('phone_num').value;
    
    var uri = root + "/members/sign_up_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == 'ok') {
                $('div_sign_up').setStyle('display', 'none');
                $('div_job_profile').setStyle('display', 'block');
                $('member_email_addr').value = $('email_addr').value;
                update_overtexts();
                window.scrollTo(0, 0);
                return;
            }
            
            var responses = txt.split(' - ');
            if (responses[1] == 'email_taken') {
                alert('The e-mail address is already signed up with us.' + "\n\n" + 'Please use another one.');
            } else if (responses[1] == 'error_create' || responses[1] == 'error_update') {
                alert('An error occured when signing up.' + "\n\n" + 'Please try again later.');
            } else if (responses[1] == 'error_activation') {
                alert('An error when trying to send an activation email.' + "\n\n" + 'Please contact us to have the problem sorted.');
            } else if (responses[1] == 'captcha') {
                alert('The security code was incorrectly entered.');
            }
        },
        onRequest: function(instance) {
            set_status('Signing up...');
        }
    });
    
    request.send(params);
}

function save_job_profile() {
    if (!validate_job_profile()) {
        return;
    }
    
    var work_from = $('work_from_year').value + '-' + $('work_from_month').options[$('work_from_month').selectedIndex].value + '-00';
    
    var work_to = 'NULL';
    if ($('work_to_present').checked == false) {
        work_to = $('work_to_year').value + '-' + $('work_to_month').options[$('work_to_month').selectedIndex].value + '-00';
    }
    
    var params = 'email_addr=' + $('member_email_addr').value + '&action=add_job_profile';
    params = params + '&specialization=' + $('specialization').value;
    params = params + '&position_title=' + $('position_title').value;
    params = params + '&position_superior_title=' + $('position_superior_title').value;
    params = params + '&organization_size=' + $('organization_size').value;
    params = params + '&work_from=' + work_from;
    params = params + '&work_to=' + work_to;
    params = params + '&employer=' + $('company').value;
    params = params + '&emp_desc=' + $('emp_desc').value;
    params = params + '&emp_specialization=' + $('emp_specialization').value;
    params = params + '&total_work_years=' + $('total_work_years').value;
    params = params + '&seeking=' + $('seeking').value.replace("\n", '<br/>');
    
    var uri = root + "/members/sign_up_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == 'ko - error_update') {
                alert('An error occured when saving your profile.' + "\n\n" + 'Please try again later after you have activated your account and signed in.');
                return;
            }
            
            alert('Congratulations! Your profile has been successfully saved.' + "\n\n" + 'Please remember to activate your account in order to sign in.');
            
            location.replace(root);
        },
        onRequest: function(instance) {
            set_status('Signing up...');
        }
    });
    
    request.send(params);
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

function update_overtexts() {
    new OverText($('position_title'));
    new OverText($('position_superior_title'));
    new OverText($('work_from_year'));
    new OverText($('work_to_year'));
}

function onDomReady() {
    initialize_page();
}

window.addEvent('domready', onDomReady);
