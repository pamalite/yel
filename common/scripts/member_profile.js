function verify() {
    if ($('mini_keywords').value == 'Job title or keywords' ||
        $('mini_keywords').value == '') {
        alert('You need to enter at least a keyword to begin searching.');
        return false;
    }
    
    return true;
}

function validate() {
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
    
    if ($('tertiary_industry').options[$('tertiary_industry').selectedIndex].value == 0) {
        alert('You must at least choose a tertiary industry.');
        $('tertiary_industry').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        var field = $('tertiary_industry');
        field.style.borderColor = '';
        field.style.borderStyle = '';
    }
    
    if ($('forget_password_answer').value == '') {
        alert('Forgot password answer cannot be empty');
        return false;
    }
    
    if ($('phone_num').value == '') {
        alert('Telephone number cannot be empty.');
        return false;
    }
    
    if ($('zip').value == '') {
        alert('Zip/Postal code cannot be empty.');
        return false;
    }
    
    if ($('password').value != '') {
        if ($('password').value != $('password_confirm').value) {
            alert('The passwords you entered do not match.');
            return false;
        }
    }
    
    if ($('address').value == '') {
        alert('Mailing Address cannot be empty.');
        return false;
    } 
    
    if ($('state').value == '') {
        alert('State/Province code cannot be empty.');
        return false;
    } 
    
    if ($('zip').value == '') {
        alert('Zip/Postal code cannot be empty.');
        return false;
    } 
    
    if ($('country').options[$('country').selectedIndex].value == 0) {
        alert('Country of residence must be provided.');
        return false;
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
    
    var params = 'email_addr=' + email_addr;
    params = params + '&primary_industry=' + $('primary_industry').value;
    params = params + '&secondary_industry=' + $('secondary_industry').value;
    params = params + '&tertiary_industry=' + $('tertiary_industry').value;
    params = params + '&forget_password_question=' + $('forget_password_question').value;
    params = params + '&forget_password_answer=' + $('forget_password_answer').value;
    params = params + '&phone_num=' + $('phone_num').value;
    params = params + '&address=' + $('address').value;
    params = params + '&state=' + $('state').value;
    params = params + '&zip=' + $('zip').value;
    params = params + '&country=' + $('country').value;
    
    if ($('like_newsletter').checked) {
        params = params + '&like_newsletter=Y';
        if (!$('filter_jobs').disabled) {
            if ($('filter_jobs').checked) {
                params = params + '&filter_jobs=Y';
            } else {
                params = params + '&filter_jobs=N';
            }
        } else {
            params = params + '&filter_jobs=N';
        }
    } else {
        params = params + '&like_newsletter=N';
        params = params + '&filter_jobs=N';
    }
    
    if (password != '') {
        params = params + '&password=' + password;
    }

    var uri = root + "/members/profile_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ok') {
                set_status('Your profile was successfully saved and updated.');
                if ($('note')) {
                    $('note').setStyle('display', 'none');
                }
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

function unsubscribe() {
    if (!confirm('Are you sure you want to unsubscribe from Yellow Elevator?')) {
        return false;
    }
    
    var params = 'action=unsubscribe&email_addr=' + email_addr;
    params = params + '&reason=' + $('reason').value;
    
    var uri = root + "/members/profile_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ok') {
                alert('Thank you for trying Yellow Elevator!');
                location.replace(root + '/members/logout.php');
            } else {
                set_status('Sorry! We are not able to unsubscribe you at the moment. Please try again later.');
            }
        },
        onRequest: function(instance) {
            set_status('Unsubscribing...');
        }
    });
    
    request.send(params);
}

function close_unsubscribe_form() {
    $('reason').value = '';
    $('div_unsubscribe_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_unsubscribe_form() {
    $('div_blanket').setStyle('display', 'block');
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_unsubscribe_form').getStyle('height'));
    var div_width = parseInt($('div_unsubscribe_form').getStyle('width'));
    
    if (typeof window.innerHeight != 'undefined') {
        window_height = window.innerHeight;
    } else {
        window_height = document.documentElement.clientHeight;
    }
    
    if (typeof window.innerWidth != 'undefined') {
        window_width = window.innerWidth;
    } else {
        window_width = document.documentElement.clientWidth;
    }
    
    $('div_unsubscribe_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_unsubscribe_form').setStyle('left', ((window_width - div_width) / 2));
    $('div_unsubscribe_form').setStyle('display', 'block');
}

function checked_profile() {
    var params = 'action=checked_profile&email_addr=' + email_addr;
    
    var uri = root + "/members/profile_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while confirming profile. Please try again later.');
            } else {
                $('confirm_profile_form').setStyle('display', 'none');
                set_status('');
            }
        },
        onRequest: function(instance) {
            set_status('Processing...');
        }
    });
    
    request.send(params);
}

function show_confirm_profile() {
    var params = 'action=is_checked_profile&email_addr=' + email_addr;
    
    var uri = root + "/members/profile_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'N') {
                $('confirm_profile_form').setStyle('display', 'inline');
            } else {
                $('confirm_profile_form').setStyle('display', 'none');
            }
        }
    });
    
    request.send(params);
}

function onDomReady() {
    set_root();
    get_employers_for_mini();
    get_industries_for_mini();
    set_mini_keywords();
    get_referrals_count();
    get_requests_count();
    get_jobs_employed_count();
    
    $('save').addEvent('click', save);
    $('save_1').addEvent('click', save);
    
    $('like_newsletter').addEvent('click', function() {
        if ($('like_newsletter').checked) {
            $('filter_jobs').disabled = false;
        } else {
            $('filter_jobs').checked = false;
            $('filter_jobs').disabled = true;
        }
    });
    
    show_confirm_profile();
    
    var suggest_url = root + '/common/php/search_suggest.php';
    new Autocompleter.Ajax.Json('mini_keywords', suggest_url, {
        'postVar': 'keywords',
        'minLength' : 1,
        'overflow' : true,
        'delay' : 50
    });
}

window.addEvent('domready', onDomReady);
