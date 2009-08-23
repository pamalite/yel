function validate() {
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
    
    if (!isEmail($('email_addr').value)) {
        alert('The e-mail address provided is not valid.');
        $('email_addr').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        var field = $('email_addr');
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
    
    if ($('feedback').value == '') {
        alert('Feedback cannot be empty.');
        $('feedback').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        var field = $('feedback');
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
    
    return true;
}

function onDomReady() {
    set_root();
    
    if (!isEmpty(error_message)) {
        set_status(error_message);
    }
}

window.addEvent('domready', onDomReady);
