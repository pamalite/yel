function save_password() {
    if (isEmpty($('password').value) || isEmpty($('password2').value)) {
        alert('Password cannot be empty.');
        return;
    }
    
    if ($('password').value != $('password2').value) {
        alert('The passwords entered do not match.');
        return;
    }
    
    var params = 'id=' + id + '&action=save_password&password=' + md5($('password').value);
    
    var uri = root + "/employers/profile_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == 'ko') {
                alert('An error occured while trying to save password. Please try again later.');
                return false;
            }
            
            alert('Password saved successfully!');
            $('password').value = '';
            $('password2').value = '';    
        },
        onRequest: function(instance) {
            set_status('Saving password...');
        }
    });
    
    request.send(params);
}

function validate_profile_form() {
    if (!isEmail($('email_addr').value)) {
        alert('Please provide a valid e-mail address.');
        return false;
    }
    
    if (isEmpty($('contact_person').value)) {
        alert('Contact person cannot be empty.');
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
        alert('You need to select a country where you are located.');
        return false;
    }
    
    return true;
    
}

function save_profile() {
    if (!validate_profile_form()) {
        return false;
    }
    
    var params = 'id=' + id;
    params = params + '&action=save_profile';
    params = params + '&email_addr=' + $('email_addr').value;
    params = params + '&contact_person=' + $('contact_person').value;
    params = params + '&phone_num=' + $('phone_num').value;
    params = params + '&fax_num=' + $('fax_num').value;
    params = params + '&address=' + encodeURIComponent($('address').value);
    params = params + '&state=' + $('state').value;
    params = params + '&zip=' + $('zip').value;
    params = params + '&country=' + $('country').options[$('country').selectedIndex].value;
    params = params + '&website_url=' + encodeURIComponent($('website_url').value);
    params = params + '&summary=' + encodeURIComponent($('summary').value);
    
    var uri = root + "/employers/profile_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            // set_status('<pre>' + txt + '</pre>');
            // return;
            set_status('');
            if (txt == 'ko') {
                alert('An error occured while saving contact details. Please try again later.');
                return false;
            }
        },
        onRequest: function(instance) {
            set_status('Saving...');
        }
    });
    
    request.send(params);
}

function onDomReady() {
    initialize_page();
}

window.addEvent('domready', onDomReady);