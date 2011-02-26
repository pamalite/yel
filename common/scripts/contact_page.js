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
