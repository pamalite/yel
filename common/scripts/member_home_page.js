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

function toggle_the_rest_of_form(_is_active) {
    if (_is_active) {
        $('seeking_field').setStyle('display', 'block');
        $('seeking_edit').setStyle('display', 'block');
        $('pref_job_loc_1_field').setStyle('display', 'block');
        $('pref_job_loc_1_edit').setStyle('display', 'block');
        $('pref_job_loc_2_field').setStyle('display', 'block');
        $('pref_job_loc_2_edit').setStyle('display', 'block');
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
        $('pref_job_loc_1_field').setStyle('display', 'none');
        $('pref_job_loc_1_edit').setStyle('display', 'none');
        $('pref_job_loc_2_field').setStyle('display', 'none');
        $('pref_job_loc_2_edit').setStyle('display', 'none');
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
                
                if ($('choices_action').value == 'save_is_active_job_seeker') {
                    // toggle the rest            
                    toggle_the_rest_of_form((choice.toUpperCase() == 'YES'));
                }
                
                location.reload(true);
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
        params = params + '&text=' + encodeURIComponent(text);
        
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
                
                location.reload(true);
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
        var text = encodeURIComponent(add_slashes($('texts').value));
        
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
                
                location.reload(true);
            }
        });

        request.send(params);
    } else {
        close_window('texts_window');
    }
}

function show_ranges_popup(_title, _start, _end, _currency, _action) {
    $('ranges_title').set('html', _title);
    $('ranges_action').value = _action;
    
    $('range_start').value = _start;
    $('range_end').value = _end;
    
    for (var i=0; i < $('range_currency').options.length; i++) {
        if ($('range_currency').options[i].value == _currency) {
            $('range_currency').selectedIndex = i;
            break;
        }
    }
    
    show_window('ranges_window');
    window.scrollTo(0, 0);
}

function close_ranges_popup(_is_save) {
    if (_is_save) {
        if (isEmpty($('range_start').value)) {
            alert('You need to enter a starting value of a range.');
            return;
        } else {
            if (isNaN($('range_start').value)) {
                alert('The starting value must be a number.');
                return;
            }
        }
        
        var start = parseFloat($('range_start').value);
        var end = 0.00;
        if (!isEmpty($('range_end').value) && !isNaN($('range_end').value)) {
            end = parseFloat($('range_end').value);
            
            if (end < start) {
                alert('The ending value must be larger than the starting value.');
                return;
            } else if (end == start) {
                end = 0.00;
            }
        } 
        
        var params = 'id=' + id + '&action=' + $('ranges_action').value;
        params = params + '&start=' + start;
        params = params + '&end=' + end;
        params = params + '&currency=' + $('range_currency').options[$('range_currency').selectedIndex].value;
        
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
                
                location.reload(true);
            }
        });

        request.send(params);
    } else {
        close_window('ranges_window');
    }
}

function show_countries_popup(_title, _selected, _action) {
    $('countries_title').set('html', _title);
    $('countries_action').value = _action;
    
    var countries = $('pref_job_loc').options;
    for (var i=0; i < countries.length; i++) {
        if (countries[i].value == _selected) {
            $('pref_job_loc').selectedIndex = i;
            break;
        }
    }
    
    show_window('countries_window');
    window.scrollTo(0, 0);
}

function close_countries_popup(_is_save) {
    if (_is_save) {
        var action = $('countries_action').value;
        
        var pref = '1'
        if (action.substr(action.length-2) == '_2') {
            pref = '2'
        }
        
        var params = 'id=' + id + '&action=save_job_loc_pref&pref=' + pref;
        params = params + '&country=' + $('pref_job_loc').options[$('pref_job_loc').selectedIndex].value;
        
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
                
                location.reload(true);
            }
        });

        request.send(params);
    } else {
        close_window('countries_window');
    }
}


function validate_job_profile() {
    if ($('specialization').selectedIndex == 0) {
        alert('You need to select a specialization.');
        return false;
    } 
    
    if (isEmpty($('position_title').value)) {
        alert('Job Title cannot be empty.');
        return false;
    }
    
    if (isEmpty($('work_from_year').value) || $('work_from_month').selectedIndex == 0) {
        alert('Duration (beginning) cannot be empty.');
        return false;
    } else {
        if (isNaN($('work_from_year').value)) {
            alert('Only numbers are accepted for year.');
            return false;
        }        
    }
    
    if ($('work_to_present').checked == false) {
        if (isEmpty($('work_to_year').value) || $('work_to_month').selectedIndex == 0) {
            alert('Duration (ending) cannot be empty.');
            return false;
        } else {
            if (isNaN($('work_from_year').value)) {
                alert('Only numbers are accepted for year.');
                return false;
            }
        }
    }
    
    if (isEmpty($('company').value)) {
        alert('Employer cannot be empty.');
        return false;
    }
    
    if ($('emp_desc').selectedIndex == 0) {
        alert('You need to select your employer\'s description.');
        return false;
    }
    
    if ($('emp_specialization').selectedIndex == 0) {
        alert('You need to select your employer\'s specialization.');
        return false;
    }
    
    if (isNaN($('organization_size').value)) {
        alert('Only numbers are accepted for Number of Direct Reports.');
        return false;
    } 
    
    return true;
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

function show_job_profile_popup(_id) {
    if (_id <= 0) {
        // new
        $('job_profile_id').value = 0;
        $('specialization').selectedIndex = 0;
        $('position_title').value = '';
        $('position_superior_title').value = '';
        $('organization_size').value = '';
        $('work_from_month').selectedIndex = 0;
        $('work_from_year').value = 'yyyy';
        $('work_to_month').selectedIndex = 0;
        $('work_to_year').value = 'yyyy';
        $('company').value = '';
        $('emp_desc').selectedIndex = 0;
        $('emp_specialization').selectedIndex = 0;
        
        show_window('job_profile_window');
        window.scrollTo(0, 0);
    } else {
        // load
        var params = 'id=' + _id + '&action=get_job_profile';
        
        var uri = root + "/members/home_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                set_status('');
                
                if (txt == 'ko') {
                    alert('An error occured when loading job profile. Please try again later.');
                    return;
                }
                
                var specialization = xml.getElementsByTagName('specialization');
                var position_title = xml.getElementsByTagName('position_title');
                var position_superior = xml.getElementsByTagName('position_superior_title');
                var org_size = xml.getElementsByTagName('organization_size');
                var work_from = xml.getElementsByTagName('work_from');
                var work_to = xml.getElementsByTagName('work_to');
                var company = xml.getElementsByTagName('employer');
                var emp_desc = xml.getElementsByTagName('employer_description');
                var emp_specialization = xml.getElementsByTagName('employer_specialization');
                
                $('job_profile_id').value = _id;
                $('position_title').value = position_title[0].childNodes[0].nodeValue;
                $('position_superior_title').value = position_superior[0].childNodes[0].nodeValue;
                $('organization_size').value = org_size[0].childNodes[0].nodeValue;
                $('company').value = company[0].childNodes[0].nodeValue.replace('&amp;', '&');
                
                for (var i=0; i < $('specialization').options.length; i++) {
                    if ($('specialization').options[i].value == specialization[0].childNodes[0].nodeValue) {
                        $('specialization').selectedIndex = i;
                        break;
                    }
                }
                
                for (var i=0; i < $('emp_desc').options.length; i++) {
                    if ($('emp_desc').options[i].value == emp_desc[0].childNodes[0].nodeValue) {
                        $('emp_desc').selectedIndex = i;
                        break;
                    }
                }
                
                for (var i=0; i < $('emp_specialization').options.length; i++) {
                    if ($('emp_specialization').options[i].value == emp_specialization[0].childNodes[0].nodeValue) {
                        $('emp_specialization').selectedIndex = i;
                        break;
                    }
                }
                
                var work_from_items = work_from[0].childNodes[0].nodeValue.split('-');
                var work_from_month = work_from_items[1];
                var work_from_year = work_from_items[0];
                
                $('work_from_year').value = work_from_year;
                for (var i=0; i < $('work_from_month').options.length; i++) {
                    if ($('work_from_month').options[i].value == work_from_month) {
                        $('work_from_month').selectedIndex = i;
                        break;
                    }
                }
                
                var work_to_items = null;
                if (work_to[0].childNodes.length > 0) {
                    work_to_items = work_to[0].childNodes[0].nodeValue.split('-');
                }
                
                if (work_to_items == null) {
                    $('work_to_month').selectedIndex = 0;
                    $('work_to_year').value = '';
                    $('work_to_dropdown').setStyle('display', 'none');
                    $('work_to_present').checked = true;
                } else {
                    $('work_to_dropdown').setStyle('display', 'block');
                    $('work_to_present').checked = false;
                    
                    var work_to_month = work_to_items[1];
                    var work_to_year = work_to_items[0];
                    
                    $('work_to_year').value = work_to_year;
                    for (var i=0; i < $('work_to_month').options.length; i++) {
                        if ($('work_to_month').options[i].value == work_to_month) {
                            $('work_to_month').selectedIndex = i;
                            break;
                        }
                    }
                } 
                
                show_window('job_profile_window');
                window.scrollTo(0, 0);
            }
        });

        request.send(params);
    }
}

function close_job_profile_popup(_is_save) {
    if (_is_save) {
        if (!validate_job_profile()) {
            return;
        }
        
        var work_from = $('work_from_year').value + '-' + $('work_from_month').options[$('work_from_month').selectedIndex].value + '-00';

        var work_to = 'NULL';
        if ($('work_to_present').checked == false) {
            work_to = $('work_to_year').value + '-' + $('work_to_month').options[$('work_to_month').selectedIndex].value + '-00';
        }
        
        var params = 'id=' + $('job_profile_id').value + '&action=save_job_profile';
        params = params + '&member=' + id;
        params = params + '&specialization=' + $('specialization').value;
        params = params + '&position_title=' + $('position_title').value;
        params = params + '&superior_title=' + $('position_superior_title').value;
        params = params + '&organization_size=' + $('organization_size').value;
        params = params + '&work_from=' + work_from;
        params = params + '&work_to=' + work_to;
        params = params + '&employer=' + encodeURIComponent($('company').value);
        params = params + '&emp_desc=' + $('emp_desc').value;
        params = params + '&emp_specialization=' + $('emp_specialization').value;
        
        var uri = root + "/members/home_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                set_status('');

                if (txt == 'ko') {
                    alert('An error occured when saving your job profile.' + "\n\n" + 'Please try again later.');
                    return;
                }
                
                location.reload(true);
            }
        });

        request.send(params);
    } else {
        close_window('job_profile_window');
    }
}

function delete_job_profile(_id) {
    var msg = 'Are you sure to delete the selected job profile?';
    
    if (confirm(msg)) {
        var params = 'id=' + _id + '&action=remove_job_profile';
        
        var uri = root + "/members/home_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                set_status('');

                if (txt == 'ko') {
                    alert('An error occured when deleting your job profile.' + "\n\n" + 'Please try again later.');
                    return;
                }
                
                location.reload(true);
            }
        });

        request.send(params);
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