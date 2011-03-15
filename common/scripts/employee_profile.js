function validate() {
    if ($('alternate_email').value == '') {
        set_status('Alternate email address cannot be empty.');
        return false;
    } else {
        if (!isEmail($('alternate_email').value)) {
            set_status('You will need to provide a valid email address.');
            return false;
        }
    } 
    
    if ($('firstname').value == '') {
        set_status('First Name cannot be empty.');
        return false;
    }
    
    if ($('lastname').value == '') {
        set_status('Last Name cannot be empty.');
        return false;
    }
    
    if ($('phone_num').value == '') {
        set_status('Telephone number cannot be empty.');
        return false;
    }
    
    if ($('mobile').value == '') {
        set_status('Mobile number cannot be empty.');
        return false;
    }
    
    if ($('address').value == '') {
        set_status('Mailing Address number cannot be empty.');
        return false;
    }
    
    if ($('state').value == '') {
        set_status('State/Province cannot be empty.');
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
    
    var params = 'id=' + user_id;
    params = params + '&firstname=' + $('firstname').value;
    params = params + '&lastname=' + $('lastname').value;
    params = params + '&phone_num=' + $('phone_num').value;
    params = params + '&alternate_email=' + $('alternate_email').value;
    params = params + '&address=' + $('address').value;
    params = params + '&state=' + $('state').value;
    params = params + '&zip=' + $('zip').value;
    params = params + '&country=' + $('country').value;
    params = params + '&mobile=' + $('mobile').value;
    
    if (password != '') {
        params = params + '&password=' + password;
    }
    
    var uri = root + "/employees/profile_action.php";
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
    initialize_page();
    get_unapproved_photos_count();
    get_employee_rewards_count();
    get_employee_tokens_count();
    
    $('save').addEvent('click', save);
    $('save_1').addEvent('click', save);
    
}

window.addEvent('domready', onDomReady);
