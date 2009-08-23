function validate() {
    if ($('email').value == '') {
        set_status('Email address cannot be empty.');
        return false;
    } else {
        if (!isEmail($('email').value)) {
            set_status('You will need to provide us a valid email address.');
            return false;
        }
    } 
    
    if ($('name').value == '') {
        set_status('Business name cannot be empty.');
        return false;
    }
    
    if ($('contact_person').value == '') {
        set_status('Contact person cannot be empty.');
        return false;
    }
    
    if ($('phone_num').value == '') {
        set_status('Telephone number cannot be empty.');
        return false;
    }
    
    if ($('zip').value == '') {
        set_status('Zip/Postal code cannot be empty.');
        return false;
    }
    
    if ($('password').value != '') {
        if ($('password').value != $('password_confirm').value) {
            set_status('The passwords you entered do not match.');
            return false;
        }
    }
    
    return true;
}

function save() {
    if (!validate()) {
        return false;
    }
    
    var password = '';
    if ($('password').value != '' && $('password_confirm').value != '') {
        password = md5($('password').value);
    }
    
    var params = 'id=' + id;
    params = params + '&name=' + $('name').value;
    params = params + '&phone_num=' + $('phone_num').value;
    params = params + '&fax_num=' + $('fax_num').value;
    params = params + '&email_addr=' + $('email').value;
    params = params + '&contact_person=' + $('contact_person').value;
    params = params + '&address=' + $('address').value;
    params = params + '&state=' + $('state').value;
    params = params + '&zip=' + $('zip').value;
    params = params + '&country=' + $('country').value;
    params = params + '&website_url=' + $('website_url').value;
    params = params + '&about=' + $('about').value;
    
    if (password != '') {
        params = params + '&password=' + password;
    }
    
    var uri = root + "/employers/profile_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ok') {
                set_status('Your profile was successfully saved and updated.');
            } else {
                set_status('Sorry! We are not able to save and update your profile at the moment. Please try again later.');
            }
        },
        onRequest: function(instance) {
            set_status('Saving and updating...');
        }
    });
    
    request.send(params);
}

function onDomReady() {
    set_root();
    get_employer_referrals_count();
    
    $('save').addEvent('click', save);
    $('save_1').addEvent('click', save);
    
}

window.addEvent('domready', onDomReady);
