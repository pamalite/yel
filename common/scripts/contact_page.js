function validate_form() {
    if (isEmpty($('contact_name').value)) {
        alert('Contact Name cannot be empty.');
        return false;
    }
    
    if (!isEmail($('email_addr').value)) {
        alert('Email Address cannot be empty or it is invalid');
        return false;
    }
    
    if (isEmpty($('message').value)) {
        alert('You need to at least type a message.');
        return false;
    }
    
    return true;
}

function show_warning() {
    if ($('category').options[$('category').selectedIndex].value == 'others') {
        var msg = "Thank you for your interest in Yellow Elevator!\n\nHowever, if you are interested in connecting with our consultants, please click the 'Get Connected' button on the right. We will not entertain any career exploration requests through this page.\n\nWe apologize for any inconvenience caused.";
        alert(msg);
    }
}

function onDomReady() {
    
}

function onLoaded() {
    initialize_page();
    
    if (has_captcha_error) {
        alert('Please try to enter the reCAPTCHA words again.');
    }
}

window.addEvent('domready', onDomReady);
window.addEvent('load', onLoaded);
