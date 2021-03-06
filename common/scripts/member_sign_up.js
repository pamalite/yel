var seed = "";
var sid = "";

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
    
    // if (isNaN($('organization_size').value)) {
    //     alert('Only numbers are accepted for Number of Direct Reports.');
    //     $('organization_size').setStyle('border', '2px solid #FF0000');
    //     return false;
    // } else {
    //     reset_field($('organization_size'));
    // }
    
    if (isNaN($('total_work_years').value)) {
        alert('Only numbers are accepted for Number of Direct Reports.');
        $('total_work_years').setStyle('border', '2px solid #FF0000');
        return false;
    } else {
        reset_field($('total_work_years'));
    }
    return true;
}

function validate_hrm_questions() {
    var date = new Date();
    var current_year = date.get('year');
    var ethnicity = $('ethnicity').getSelected();
    
    if (ethnicity[0].value == 'other' && isEmpty($('ethnicity_txt').value)) {
        alert('You need to provide an ethnicity.');
        $('ethnicity_txt').setStyle('border', '2px solid #FF0000');
        return false;
    }
    
    if (!isEmpty($('birthdate_year').value)) {
        if (isNaN($('birthdate_year').value)) {
            alert('Birthdate Year must be a number.');
            $('birthdate_year').setStyle('border', '2px solid #FF0000');
            return false;
        }
        
        var birthdate_year = parseInt($('birthdate_year').value);
        if (parseInt(current_year) -  birthdate_year > 65) {
            alert('Invalid birth year has been entered.');
            $('birthdate_year').setStyle('border', '2px solid #FF0000');
            return false;
        }
        
        var birthdate_day = $('birthdate_day').getSelected();
        var birthdate_month = $('birthdate_month').getSelected();
        
        if (isEmpty(birthdate_day[0].value) || isEmpty(birthdate_month[0].value)) {
            alert('You need to enter the full birthdate.');
            $('birthdate_day').setStyle('border', '2px solid #FF0000');
            $('birthdate_month').setStyle('border', '2px solid #FF0000');
            return false;
        }
        
        var is_leap_year = false;
        if (birthdate_year % 400 == 0) {
            is_leap_year = true;
        } else if (birthdate_year % 100 == 0) {
            is_leap_year = false;
        } else if (birthdate_year % 4 == 0) {
            is_leap_year = true;
        }
        
        if ((is_leap_year && parseInt(birthdate_day[0].value) > 29 && birthdate_month[0].value == '02') || 
            (!is_leap_year && parseInt(birthdate_day[0].value) > 28 && birthdate_month[0].value == '02')) {
            alert('Invalid day entered.');
            $('birthdate_day').setStyle('border', '2px solid #FF0000');
            return false;
        }
        
        switch (birthdate_month[0].value) {
            case '04':
            case '06':
            case '09':
            case '11':
                if (parseInt(birthdate_day[0].value) > 30) {
                    return false;
                }
        }
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
    params = params + '&phone_num=' + $('phone_num').value;
    params = params + '&recaptcha_challenge=' + encodeURIComponent($('recaptcha_challenge_field').value);
    params = params + '&recaptcha_response=' + encodeURIComponent($('recaptcha_response_field').value);
    
    var uri = root + "/members/sign_up_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            if (txt == 'ok - is_exists') {
                alert('Congratulations! You have successfully signed up with us.' + "\n\n" + 'It looks like we already have your profile in our system. Please sign in to check it out.' + "\n\n" + 'An activation e-mail has been sent to your registered email account to re-activate your account with us. Upon re-activation, please sign in with your registered email address and your newly created password.' + "\n\n" +  'Please check your Junk / Spam mail boxes if you do not receive the activation e-mail in your Inbox. Thank you.');

                location.replace(root);
                return;
            }
            
            if (txt == 'ok') {
                $('div_sign_up').setStyle('display', 'none');
                $('div_job_profile').setStyle('display', 'block');
                $('member_email_addr').value = $('email_addr').value;
                update_overtexts();
                // window.scrollTo(0, 0);
                return;
            }
            
            var responses = txt.split(' - ');
            if (responses[1] == 'suspended') {
                alert('The e-mail address is already registered with YellowElevator.com, but it is suspended by our consultants.' + "\n\n" + 'Please contact us at "team.my@yellowelevator.com" to re-activate your account.');
            } else if (responses[1] == 'error_create' || responses[1] == 'error_update') {
                alert('An error occured when signing up.' + "\n\n" + 'Please try again later.');
            } else if (responses[1] == 'error_activation') {
                alert('An error when trying to send an activation email.' + "\n\n" + 'Please contact us to have the problem sorted.');
            } else if (responses[1] == 'captcha') {
                alert('The the reCAPTCHA text was incorrectly entered.');
                Recaptcha.reload();
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
    
    if (!validate_hrm_questions()) {
        return;
    }
    
    var gender = $('gender').getSelected();
    var ethnicity = $('ethnicity').getSelected();
    if (ethnicity[0].value == 'other') {
        ethnicity[0].value = $('ethnicity_txt').value;
    }
    
    var birthdate_day = $('birthdate_day').getSelected();
    var birthdate_month = $('birthdate_month').getSelected();
    var birthdate_year = $('birthdate_year').value;
    var birthdate = 'NULL';
    if (!isEmpty(birthdate_year)) {
        birthdate = birthdate_year + '-' + birthdate_month[0].value + '-' + birthdate_day[0].value;
    }
    
    var params = 'email_addr=' + $('member_email_addr').value + '&action=add_job_profile';
    // params = params + '&specialization=' + $('specialization').value;
    params = params + '&position_title=' + $('position_title').value;
    params = params + '&position_superior_title=' + $('position_superior_title').value;
    params = params + '&organization_size=' + encodeURIComponent($('organization_size').value);
    params = params + '&work_from=' + work_from;
    params = params + '&work_to=' + work_to;
    params = params + '&employer=' + $('company').value;
    params = params + '&emp_desc=' + $('emp_desc').value;
    params = params + '&emp_specialization=' + $('emp_specialization').value;
    params = params + '&total_work_years=' + $('total_work_years').value;
    params = params + '&seeking=' + $('seeking').value.replace(/\n/g, '<br/>');
    params = params + '&gender=' + gender[0].value;
    params = params + '&ethnicity=' + ethnicity[0].value;
    params = params + '&birthdate=' + birthdate;
    
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
            
            //alert('Congratulations! Your Current Career Profile has been successfully submitted.' + "\n\n" + 'One of our Recruitment Consultants will get in touch with you through email shortly to guide you how to sign into your account.');
            alert('Congratulations! Your Current Career Profile has been successfully submitted.' + "\n\n" + 'An email is send to you to activate your account. Please also check your junk or spam mail boxes for the email.');
            
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
    new OverText($('organization_size'));
    new OverText($('work_from_year'));
    new OverText($('work_to_year'));
    new OverText($('seeking'), { wrap: true });
    new OverText($('birthdate_year'));
}

function show_promo_tnc() {
    window.open(root + '/common/images/promotions/starbucks/t_n_c.jpg', '', 'width=420px,height=810px,scrollbar=no,menubar=no,location=no');
}

function on_linkedin_auth() {
    set_status('Please wait... Signing up through LinkedIn...');
    
    IN.API.Profile("me").result(function(me) {
        var linkedin_id = me.values[0].id;
        var linkedin_firstname = me.values[0].firstName;
        var linkedin_lastname = me.values[0].lastName;
        
        if (isEmpty(linkedin_id) || linkedin_id == null) {
            alert('Cannot sign up through LinkedIn. Please use normal sign up instead.');
            set_status('');
            return;
        }
        
        var params = 'id=' + linkedin_id + '&action=linkedin_auth';
        var request = new Request({
            url: 'login_action.php',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    alert('Cannot sign up through LinkedIn. Please use normal sign up instead.');
                    set_status('');
                    return;
                }
                
                var new_linkedin = false;
                var member_id = '';
                if (isEmpty(txt)) {
                    member_id = prompt('Please enter your email address again.');
                    if (member_id == false) {
                        alert('You have chosen not to sign up through LinkedIn.' + "\n\n" + 'Cannot sign up through LinkedIn. Please use normal sign up instead.');
                        set_status('');
                        return;
                    }
                    
                    while (!isEmail(member_id)) {
                        member_id = prompt('The email address entered is invalid.' + "\n\n" + 'Please enter your email address again.');
                        
                        if (member_id == false) {
                            alert('Sign Up via LinkedIn was canceled.');
                            set_status('');
                            return;
                        }
                    }
                    
                    new_linkedin = true;
                } else {
                    if (!isEmail(txt)) {
                        alert('Cannot sign up through LinkedIn. Please use normal sign up instead.');
                        set_status('');
                        return;
                    }
                    
                    member_id = txt;
                }
                
                login_via_linkedin(member_id, linkedin_id, linkedin_firstname, 
                                   linkedin_lastname, new_linkedin);
            }
        });

        request.send(params);
    });
}

function login_via_linkedin(_member_id, _linkedin_id, _linkedin_firstname,
                            _linkedin_lastname, _is_new) {
    var params = 'id=' + _member_id + '&action=linkedin_login';
    params = params + '&linkedin_id=' + _linkedin_id;
    params = params + '&linkedin_firstname=' + _linkedin_firstname;
    params = params + '&linkedin_lastname=' + _linkedin_lastname;
    if (isEmpty(seed) || isEmpty(sid)) {
        params = params + '&sid=&hash=';
    } else {
        var hash = sha1(_member_id + md5(_linkedin_id) + seed);
        params = params + '&sid=' + sid + '&hash=' + hash;
    }
    
    if (_is_new) {
        params = params + '&is_new=1';
    } else {
        params = params + '&is_new=0';
    }
    
    var request = new Request({
        url: 'login_action.php',
        onSuccess: function(txt, xml) {
            if (xml.getElementsByTagName('errors').length != 0) {
                
                var errors = xml.getElementsByTagName('error');
                var msg = errors[0].childNodes[0].nodeValue;
                if (msg == 'create_error') {
                    alert('An error occured while signing up with LinkedIn account.');
                }

                if (msg == 'update_error') {
                    alert('An error occured while associating your existing account with LinkedIn.');
                }

                if (msg == 'hacking_detected') {
                    alert('Another LinkedIn user has already used this email address.');
                }

                if (msg == "bad_login") {
                    location.replace(root + '/errors/failed_login.php?dir=members');
                }
                
                set_status('');
                logout_from_linkedin();
                return;
            }
            
            var status = xml.getElementsByTagName('status');
            
            if (status[0].childNodes[0].nodeValue == 'ok') {
                location.replace('home.php');
            }
        }
    });

    request.send(params);
}

function get_seed() {
    var seed_uri = 'members/seed.php';
    var request = new Request({
        url: seed_uri,
        onSuccess: function(txt, xml) {
            // set_status("");
            
            if (xml.getElementsByTagName('errors').length != 0) {
                location.replace(root + '/errors/temporarily_down.php');
            }
            
            var sids = xml.getElementsByTagName('id');
            var seeds = xml.getElementsByTagName('seed');
            
            sid = sids[0].childNodes[0].nodeValue;
            seed = seeds[0].childNodes[0].nodeValue;
        }
    });
    
    request.send();
}

function onDomReady() {
    get_seed();
    
    $('promo_button').addEvent('click', show_promo_tnc);
    $('promo_button').addEvent('mouseover', function(_event) {
        $('promo_button').setStyle('cursor', 'pointer');
    });
}

function onLoaded() {
    initialize_page();
}

window.addEvent('domready', onDomReady);
window.addEvent('load', onLoaded);
