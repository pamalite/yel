function get_completeness_status() {
    var params = 'id=' + id + '&action=get_completeness_status';
    
    var uri = root + "/members/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == '0') {
                var html = '<span style="color: #666666;">An error occurred while retrieving completeness status.</span>';
                $('div_completeness').set('html', html);
                return;
            } 
            
            var checked_profiles = xml.getElementsByTagName('checked_profile');
            var has_banks = xml.getElementsByTagName('has_bank');
            var has_resumes = xml.getElementsByTagName('has_resume');
            var has_photos = xml.getElementsByTagName('has_photo');
            
            var total = parseInt(checked_profiles[0].childNodes[0].nodeValue) + parseInt(has_banks[0].childNodes[0].nodeValue) + parseInt(has_resumes[0].childNodes[0].nodeValue) + parseInt(has_photos[0].childNodes[0].nodeValue);
            var completeness = (total / 4) * 100;
            if (completeness <= 0) {
                $('progress_bar').setStyle('display', 'none');
            } else {
                $('progress_bar').setStyle('width', (completeness - 1) + '%');
            }
            
            $('progress_percent').set('html', completeness + '%');
            
            var progress_details = '';
            if (checked_profiles[0].childNodes[0].nodeValue == '0') {
                    progress_details = 'Please <a href="' + root + '/members/profile.php">verify</a> your profile is correct, and have your password changed.<br/>';
            }
            
            if (has_banks[0].childNodes[0].nodeValue == '0') {
                    progress_details = progress_details + 'Please <a href="' + root + '/members/banks.php">provide</a> at least a bank account to ease transfer of rewards and bonuses.<br/>';
            }
            
            if (has_resumes[0].childNodes[0].nodeValue == '0') {
                    progress_details = progress_details + 'Please <a href="' + root + '/members/resumes.php">create/upload</a> your resume.<br/>';
            }
            
            if (has_photos[0].childNodes[0].nodeValue == '0') {
                    progress_details = progress_details + 'Please <a href="' + root + '/members/photos.php">upload</a> a photo of yourself.<br/>';
            }
            
            if (!isEmpty(progress_details)) {
                $('details').set('html', progress_details);
            } else {
                $('details').setStyle('display', 'none');
            }
        }
    });
    
    request.send(params);
}

function save_census_answers() {
    var gender = '';
    if (isEmpty($('gender').value)) {
        alert('You need to select your gender.');
        return false;
    } else {
        gender = $('gender').value;
    }
    
    var ethnicity = '';
    if (isEmpty($('ethnicity').value)) {
        alert('You need select your ethnicity.');
        return false;
    } else {
        ethnicity = $('ethnicity').value;
        if ($('ethnicity').value == 'other') {
            if (isEmpty($('ethnicity_txt').value)) {
                alert('You need to state your ethnicity if it is not listed.');
                return false;
            } else {
                ethnicity = $('ethnicity_txt').value;
            }
        }
    }
    
    var birthdate = '';
    if (isNaN($('birthdate_year').value) || parseInt($('birthdate_year').value) <= 0) {
        alert('Birth year must be a 4 digit number.');
        return false;
    } else {
        birthdate = $('birthdate_year').value;
    }
    
    if (isEmpty($('birthdate_month').value)) {
        alert('A birth month must be selected.');
        return false;
    } else {
        birthdate = birthdate + '-' + $('birthdate_month').value;
    }
    
    if (isEmpty($('birthdate_day').value)) {
        alert('A birth day must be selected.');
        return false;
    } else {
        birthdate = birthdate + '-' + $('birthdate_day').value;
    }
    
    var params = 'id=' + id + '&action=save_census_answers';
    params = params + '&gender=' + gender;
    params = params + '&ethnicity=' + ethnicity;
    params = params + '&birthdate=' + birthdate;
    
    var uri = root + "/members/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured when saving survey answers. Please try again later.');
                return false;
            }
            
            set_status('');
            $('div_hrm_census').setStyle('display', 'none');
        },
        onRequest: function() {
            set_status('Saving One-Time Survey...');
        }
    });
    
    request.send(params);
}

function save_answers() {
    var is_active_seeking_job = $('is_seeking_job').options[$('is_seeking_job').selectedIndex].value;
    
    if (is_active_seeking_job == '1') {
        if (!isEmpty($('expected_salary').value)) {
            if (isNaN($('expected_salary').value)) {
                alert('Expected salary range must be a number.');
                return false;
            }
        }

        if (!isEmpty($('expected_salary_end').value)) {
            if (isNaN($('expected_salary_end').value)) {
                alert('Expected salary range must be a number.');
                return false;
            }
        }

        if (!isEmpty($('current_salary_end').value)) {
            if (isNaN($('current_salary_end').value)) {
                alert('Current salary range must be a number.');
                return false;
            }
        }

        if (!isEmpty($('current_salary_end').value)) {
            if (isNaN($('current_salary_end').value)) {
                alert('Current salary range must be a number.');
                return false;
            }
        }
        
        if (!isEmpty($('notice_period').value)) {
            if (isNaN($('notice_period').value)) {
                alert('Notice period must be a number.');
                return false;
            }
        }
    }
    
    var params = 'id=' + id + '&action=save_answers';
    params = params + '&is_active_seeking_job=' + is_active_seeking_job;
    
    if (is_active_seeking_job == '1') {
        params = params + '&seeking=' + $('seeking').value;
        params = params + '&expected_salary=' + $('expected_salary').value;
        
        if (isEmpty($('expected_salary_end').value)) {
            params = params + '&expected_salary_end=0';
        } else {
            params = params + '&expected_salary_end=' + $('expected_salary_end').value;
        }
        
        params = params + '&can_travel_relocate=' + $('can_travel_relocate').options[$('can_travel_relocate').selectedIndex].value;
        params = params + '&reason_for_leaving=' + $('reason_for_leaving').value;
        params = params + '&current_position=' + $('current_position').value;
        
        params = params + '&current_salary=' + $('current_salary').value;
        
        if (isEmpty($('current_salary_end').value)) {
            params = params + '&current_salary_end=0';
        } else {
            params = params + '&current_salary_end=' + $('current_salary_end').value;
        }
        
        if (isEmpty($('notice_period').value)) {
            params = params + '&notice_period=0';
        } else {
            params = params + '&notice_period=' + $('notice_period').value;
        }
    }
    
    var uri = root + "/members/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured when saving answers. Please try again later.');
                return false;
            }
            
            set_status('');
        },
        onRequest: function() {
            set_status('Saving One-Time Survey...');
        }
    });
    
    request.send(params);
}

function toggle_the_rest_of_form(_is_active) {
    if ($('is_seeking_job').options[$('is_seeking_job').selectedIndex].value == '0') {
        $('seeking_field').setStyle('display', 'block');
        $('seeking_edit').setStyle('display', 'block');
        $('expected_salary_field').setStyle('display', 'block');
        $('expected_salary_edit').setStyle('display', 'block');
        $('travel_field').setStyle('display', 'block');
        $('travel_edit').setStyle('display', 'block');
        $('leaving_field').setStyle('display', 'block');
        $('leaving_edit').setStyle('display', 'block');
        $('current_job_field').setStyle('display', 'block');
        $('current_job_edit').setStyle('display', 'block');
        $('current_salary_field').setStyle('display', 'block');
        $('current_salary_edit').setStyle('display', 'block');
        $('notice_period_field').setStyle('display', 'block');
        $('notice_period_edit').setStyle('display', 'block');
    } else {
        $('seeking_field').setStyle('display', 'none');
        $('seeking_edit').setStyle('display', 'none');
        $('expected_salary_field').setStyle('display', 'none');
        $('expected_salary_edit').setStyle('display', 'none');
        $('travel_field').setStyle('display', 'none');
        $('travel_edit').setStyle('display', 'none');
        $('leaving_field').setStyle('display', 'none');
        $('leaving_edit').setStyle('display', 'none');
        $('current_job_field').setStyle('display', 'none');
        $('current_job_edit').setStyle('display', 'none');
        $('current_salary_field').setStyle('display', 'none');
        $('current_salary_edit').setStyle('display', 'none');
        $('notice_period_field').setStyle('display', 'none');
        $('notice_period_edit').setStyle('display', 'none');
    }
}

function show_choices_popup(_title, _choices_str, _selected, _action) {
    $('choices_title').set('html', _title);
    $('choices_action').value = _action;
    
    var choices = _choices_str.split('|');
    if (choices.length <= 0) {
        return;
    }
    
    var html = '<select id="choices" class="choices">' + "\n";
    for (var i=0; i < choices.length; i++) {
        if (choices[i].toUpperCase() == _selected.toUpperCase()) {
            html = html + '<option value="' + choices[i] + '" selected>' + choices[i] + '</option>' + "\n";
        } else {
            html = html + '<option value="' + choices[i] + '">' + choices[i] + '</option>' + "\n";
        }
    }
    html = html + '</select>' + "\n";
    
    $('choices_dropdown').set('html', html);
    
    show_window('choices_window');
    window.scrollTo(0, 0);
}

function close_choices_popup(_is_save) {
    if (_is_save) {
        var choice = $('choices').options[$('choices').selectedIndex].value;
        var params = 'id=' + id + '&action=' + $('choices_action').value + '&choice=' + choice;
        
        var uri = root + "/members/home_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                set_status('');
                
                if (txt == 'ko') {
                    alert('An error occured when saving. Please try again later.');
                    return;
                }
                
                if ($('choices_action') == 'save_is_active_job_seeker') {
                    // toggle the rest            
                    toggle_the_rest_of_form((choice.toUpperCase() == 'YES'));
                }
                
                close_window('choices_window');
            }
        });

        request.send(params);
    } else {
        close_window('choices_window');
    }
}

function show_notes_popup(_title, _texts, _action) {
    $('notes_title').set('html', _title);
    $('notes_action').value = _action;
    
    $('notes').value = _texts.replace(/<br\/>/g, "\n");
    
    show_window('notes_window');
    window.scrollTo(0, 0);
}

function close_notes_popup(_is_save) {
    if (_is_save) {
        var text = $('notes').value.replace(/\n/g, "<br/>");
        text = add_slashes(text);
        
        if (isEmpty(text)) {
            alert('You need to enter some texts in order to save.' + "\n\n" + 'You can click the \'Cancel\' button to close this popup window.');
            return;
        }
        
        var params = 'id=' + id + '&action=' + $('notes_action').value;
        params = params + '&text=' + text;
        
        var uri = root + "/members/home_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                set_status('');
                
                if (txt == 'ko') {
                    alert('An error occured when saving. Please try again later.');
                    return;
                }
                
                close_window('notes_window');
            }
        });

        request.send(params);
    } else {
        close_window('notes_window');
    }
}

function show_texts_popup(_title, _texts, _action) {
    $('texts_title').set('html', _title);
    $('texts_action').value = _action;
    
    $('texts').value = _texts;
    
    show_window('texts_window');
    window.scrollTo(0, 0);
}

function close_texts_popup(_is_save) {
    if (_is_save) {
        var text = add_slashes($('texts').value);
        
        if (isEmpty(text)) {
            alert('You need to enter some texts in order to save.' + "\n\n" + 'You can click the \'Cancel\' button to close this popup window.');
            return;
        }
        
        var params = 'id=' + id + '&action=' + $('texts_action').value;
        params = params + '&text=' + text;
        
        var uri = root + "/members/home_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                set_status('');
                
                if (txt == 'ko') {
                    alert('An error occured when saving. Please try again later.');
                    return;
                }
                
                close_window('texts_window');
            }
        });

        request.send(params);
    } else {
        close_window('texts_window');
    }
}

function onDomReady() {
    initialize_page();
    
    if ($('div_hrm_census').getStyle('display') != 'none') {
        new OverText($('birthdate_year'));
        new OverText($('ethnicity_txt'));
    }
}

window.addEvent('domready', onDomReady);