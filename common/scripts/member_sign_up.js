function validate() {
    if ($('personal_id').value == '') {
        alert('You must have at least a passport or a National Identification Card to be a member of Yellow Elevator.');
        $('personal_id').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        var field = $('personal_id');
        field.style.borderColor = '';
        field.style.borderStyle = '';
    }
    
    if ($('firstname').value == '') {
        alert('Given Names cannot be empty.');
        $('firstname').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        var field = $('firstname');
        field.style.borderColor = '';
        field.style.borderStyle = '';
    }
    
    if ($('lastname').value == '') {
        alert('Last Name cannot be empty.');
        $('lastname').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        var field = $('lastname');
        field.style.borderColor = '';
        field.style.borderStyle = '';
    }
    
    if ($('primary_industry').options[$('primary_industry').selectedIndex].value == 0) {
        alert('You must at least choose a primary industry.');
        $('primary_industry').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        var field = $('primary_industry');
        field.style.borderColor = '';
        field.style.borderStyle = '';
    }
    
    if ($('secondary_industry').options[$('secondary_industry').selectedIndex].value == 0) {
        alert('You must at least choose a secondary industry.');
        $('secondary_industry').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        var field = $('secondary_industry');
        field.style.borderColor = '';
        field.style.borderStyle = '';
    }
    
    if (!isEmail($('email_addr').value)) {
        alert('The e-mail address provided is not valid.');
        $('email_addr').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        var field = $('email_addr');
        field.style.borderColor = '';
        field.style.borderStyle = '';
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
        var field = $('password');
        var field_1 = $('password_confirm');
        field.style.borderColor = '';
        field.style.borderStyle = '';
        field_1.style.borderColor = '';
        field_1.style.borderStyle = '';
    }
    
    if ($('forget_password_question').options[$('forget_password_question').selectedIndex].value == 0) {
        alert('You must at least choose a password hint.');
        $('forget_password_question').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        var field = $('forget_password_question');
        field.style.borderColor = '';
        field.style.borderStyle = '';
    }
    
    if ($('forget_password_answer').value == '') {
        alert('The answer to your password hint cannot be empty');
        $('forget_password_answer').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        var field = $('forget_password_answer');
        field.style.borderColor = '';
        field.style.borderStyle = '';
    }
    
    if ($('phone_num').value == '') {
        alert('Telephone number cannot be empty.');
        $('phone_num').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        var field = $('phone_num');
        field.style.borderColor = '';
        field.style.borderStyle = '';
    }
    
    if ($('address').value == '') {
        alert('Mailing Address cannot be empty.');
        $('address').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        var field = $('address');
        field.style.borderColor = '';
        field.style.borderStyle = '';
    }
    
    if ($('state').value == '') {
        alert('State/Province code cannot be empty.');
        $('state').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        var field = $('state');
        field.style.borderColor = '';
        field.style.borderStyle = '';
    }
    
    if ($('zip').value == '') {
        alert('Zip/Postal code cannot be empty.');
        $('zip').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        var field = $('zip');
        field.style.borderColor = '';
        field.style.borderStyle = '';
    }
    
    if ($('country').options[$('country').selectedIndex].value == 0) {
        alert('Country of residence must be provided.');
        $('country').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        var field = $('country');
        field.style.borderColor = '';
        field.style.borderStyle = '';
    }
    
    if ($('security_code').value == 0) {
        alert('Security code cannot be empty.');
        $('security_code').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        var field = $('security_code');
        field.style.borderColor = '';
        field.style.borderStyle = '';
    }
    
    if (!$('agreed_terms').checked) {
        alert('You need to agree with our Terms and Conditions before you can sign up.');
        return false;
    }
    
    return true;
}

function onDomReady() {
    set_root();
    
    if (!isEmpty(error_message)) {
        set_status(error_message);
    }
    
    $('profile').addEvent('submit', validate);
}

window.addEvent('domready', onDomReady);
