var has_industries = false;

function validate() {
    if ($('citizenship').options[$('citizenship').selectedIndex].value == 0) {
        alert('Nationality must be provided.');
        return false;
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
    
    // has_industries = false;
    // var industry_count = 0;
    // for (var i=0; i < $('industry').options.length; i++) {
    //     if ($('industry').options[i].selected) {
    //         industry_count++;
    //         has_industries = true;
    //     }
    //     
    //     if (industry_count > 3) {
    //         alert('You can only select your top 3 specilizations.');
    //         has_industries = false;
    //         return false;
    //     }
    // }
    // 
    // if (!has_industries) {
    //     alert('You must at least choose a specialization.');
    // }
    
    return true;
}

function save_profile() {
    if (!validate()) {
        return false;
    }
    
    var password = '';
    if ($('password').value != '' && $('password_confirm').value != '') {
        password = md5($('password').value);
    }
    
    var params = 'id=' + id + '&action=save_profile';
    params = params + '&citizenship=' + $('citizenship').options[$('citizenship').selectedIndex].value;
    params = params + '&forget_password_question=' + $('forget_password_question').value;
    params = params + '&forget_password_answer=' + $('forget_password_answer').value;
    params = params + '&phone_num=' + $('phone_num').value;
    params = params + '&address=' + $('address').value;
    params = params + '&state=' + $('state').value;
    params = params + '&zip=' + $('zip').value;
    params = params + '&country=' + $('country').options[$('country').selectedIndex].value;
    
    if (password != '') {
        params = params + '&password=' + password;
    }
    
    // var count = 0;
    // var industry = '';
    // for (var i=0; i < $('industry').options.length; i++) {
    //     if ($('industry').options[i].selected) {
    //         industry = industry + $('industry').options[i].value;
    //         
    //         if (count < 2) {
    //             industry = industry + ',';
    //         }
    //         
    //         count++;
    //     }
    //     
    //     if (count >= 3) {
    //         break;
    //     }
    // }
    // params = params + '&industries=' + industry;
    
    var uri = root + "/members/profile_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ok') {
                set_status('Your profile was successfully saved and updated.');
                // window.scrollTo(0, 0);
            } else {
                alert('Sorry! We are not able to save and update your profile at the moment. Please try again later.');
            }
        },
        onRequest: function(instance) {
            set_status('Saving and updating...');
        }
    });
    
    request.send(params);
}

function save_highlights() {
    var params = 'id=' + id + '&action=save_highlights';
    
    if ($('like_newsletter').checked) {
        params = params + '&like_newsletter=Y';
    }
    
    if (!$('filter_jobs').disabled && $('filter_jobs').checked) {
        params = params + '&filter_jobs=Y';
    } else if (!$('filter_jobs').disabled && !$('filter_jobs').checked) {
        params = params + '&filter_jobs=N';
    }
    
    var uri = root + "/members/profile_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ok') {
                set_status('Your profile was successfully saved and updated.');
                // window.scrollTo(0, 0);
            } else {
                alert('Sorry! We are not able to save and update your profile at the moment. Please try again later.');
            }
        },
        onRequest: function(instance) {
            set_status('Saving and updating...');
        }
    });
    
    request.send(params);
}

function save_bank() {
    var params = 'id=' + id + '&action=save_bank';
    params = params + '&bank_id=' + $('bank_id').value;
    params = params + '&bank=' + $('bank_name').value;
    params = params + '&account=' + $('account').value;
    
    var uri = root + "/members/profile_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ok') {
                set_status('Your profile was successfully saved and updated.');
                // window.scrollTo(0, 0);
            } else {
                alert('Sorry! We are not able to save and update your profile at the moment. Please try again later.');
            }
        },
        onRequest: function(instance) {
            set_status('Saving and updating...');
        }
    });
    
    request.send(params);
}

function close_unsubscribe_popup(_is_unsubscribe) {
    if (_is_unsubscribe) {
        if (!confirm('Are you sure you want to unsubscribe from Yellow Elevator?')) {
            return false;
        }
        
        var params = 'action=unsubscribe&id=' + id;
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
                    alert('Sorry! We are not able to unsubscribe you at the moment. Please try again later.');
                    return false;
                }
            },
            onRequest: function(instance) {
                set_status('Unsubscribing...');
            }
        });

        request.send(params);
    }
    
    close_window('unsubscribe_window');
}

function show_unsubscribe_popup() {
    show_window('unsubscribe_window');
    // window.scrollTo(0, 0);
}

function close_upload_photo_popup(_is_upload) {
    if (_is_upload) {
        if (isEmpty($('my_file').value)) {
            alert('You need to select a photo to upload.');
            return false;
        }
        
        close_safari_connection();
        return true;
    } else {
        close_window('upload_photo_window');
    }
}

function show_upload_photo_popup() {
    show_window('upload_photo_window');
}

function set_mouse_events() {
    $('li_profile').addEvent('mouseover', function() {
        $('li_profile').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_profile').addEvent('mouseout', function() {
        $('li_profile').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_bank').addEvent('mouseover', function() {
        $('li_bank').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_bank').addEvent('mouseout', function() {
        $('li_bank').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_highlights').addEvent('mouseover', function() {
        $('li_highlights').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_highlights').addEvent('mouseout', function() {
        $('li_highlights').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
}

function onDomReady() {
    set_mouse_events();
    
    // $('industry').addEvent('change', function() {
    //     var count = 0;
    //     for (var i=0; i < $('industry').options.length; i++) {
    //         if ($('industry').options[i].selected) {
    //             count++;
    //         }
    //         
    //         if (count > 3) {
    //             $('industry').options[i].selected = false;
    //             break;
    //         }
    //     }
    // });
    
    $('li_profile').addEvent('click', function() {
        set_status('');
        
        $('profile').setStyle('display', 'block');
        $('bank').setStyle('display', 'none');
        $('highlights').setStyle('display', 'none');
        
        $('li_profile').setStyle('border', '1px solid #AAAAAA');
        $('li_profile').setStyle('border-bottom', '1px solid #FFFFFF');
        $('li_bank').setStyle('border', '1px solid #0000FF');
        $('li_bank').setStyle('border-bottom', 'none');
        $('li_highlights').setStyle('border', '1px solid #0000FF');
        $('li_highlights').setStyle('border-bottom', 'none');
    });
    
    $('li_bank').addEvent('click', function() {
        set_status('');
        
        $('profile').setStyle('display', 'none');
        $('bank').setStyle('display', 'block');
        $('highlights').setStyle('display', 'none');
        
        $('li_bank').setStyle('border', '1px solid #AAAAAA');
        $('li_bank').setStyle('border-bottom', '1px solid #FFFFFF');
        $('li_profile').setStyle('border', '1px solid #0000FF');
        $('li_profile').setStyle('border-bottom', 'none');
        $('li_highlights').setStyle('border', '1px solid #0000FF');
        $('li_highlights').setStyle('border-bottom', 'none');
    });
    
    $('li_highlights').addEvent('click', function() {
        set_status('');
        
        $('profile').setStyle('display', 'none');
        $('bank').setStyle('display', 'none');
        $('highlights').setStyle('display', 'block');
        
        $('li_highlights').setStyle('border', '1px solid #AAAAAA');
        $('li_highlights').setStyle('border-bottom', '1px solid #FFFFFF');
        $('li_bank').setStyle('border', '1px solid #0000FF');
        $('li_bank').setStyle('border-bottom', 'none');
        $('li_profile').setStyle('border', '1px solid #0000FF');
        $('li_profile').setStyle('border-bottom', 'none');
    });
    
    $('like_newsletter').addEvent('click', function() {
        if ($('like_newsletter').checked) {
            $('filter_jobs').disabled = false;
        } else {
            $('filter_jobs').checked = false;
            $('filter_jobs').disabled = true;
        }
    });
}

function onLoaded() {
    initialize_page();
}

window.addEvent('domready', onDomReady);
window.addEvent('load', onLoaded);
