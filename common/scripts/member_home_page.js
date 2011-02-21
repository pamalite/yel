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

function show_career_summary_popup() {
    show_window('career_summary_window');
}

function close_career_summary_popup(_is_save) {
    if (_is_save) {
        var seeking = encodeURIComponent($('seeking').value);
        var reason_leaving = encodeURIComponent($('reason_leaving').value);
        // var current_pos = encodeURIComponent($('current_pos').value);
        
        var contact_me = '0';
        if ($('contact_me').checked) {
            contact_me = '1';
        }
        
        if (!isEmpty($('expected_sal_start').value) && isNaN($('expected_sal_start').value)) {
            alert('Expected salary must be a number.');
            return;
        } else if (!isEmpty($('expected_sal_end').value) && isNaN($('expected_sal_end').value)) {
            alert('Expected salary must be a number.');
            return;
        } else if (!isEmpty($('expected_sal_start').value) && !isEmpty($('expected_sal_end').value)) {
            if (parseInt($('expected_sal_start').value) > parseInt($('expected_sal_end').value)) {
                alert('Expected salary range is invalid.');
                return;
            }
        }
        
        if (!isEmpty($('current_sal_start').value) && isNaN($('current_sal_start').value)) {
            alert('Expected salary must be a number.');
            return;
        } else if (!isEmpty($('current_sal_end').value) && isNaN($('current_sal_end').value)) {
            alert('Expected salary must be a number.');
            return;
        } else if (!isEmpty($('current_sal_start').value) && !isEmpty($('current_sal_end').value)) {
            if (parseInt($('current_sal_start').value) > parseInt($('current_sal_end').value)) {
                alert('Expected salary range is invalid.');
                return;
            }
        }
        
        var params = 'id=' + id + '&action=save_career_summary';
        params = params + '&is_active=' + $('is_active').options[$('is_active').selectedIndex].value;
        params = params + '&contact_me=' + contact_me;
        params = params + '&can_travel=' + $('can_travel').options[$('can_travel').selectedIndex].value;
        params = params + '&expected_sal_currency=' + $('expected_sal_currency').options[$('expected_sal_currency').selectedIndex].value;
        params = params + '&current_sal_currency=' + $('current_sal_currency').options[$('current_sal_currency').selectedIndex].value;
        params = params + '&pref_job_loc_1=' + $('pref_job_loc_1').options[$('pref_job_loc_1').selectedIndex].value;
        params = params + '&pref_job_loc_2=' + $('pref_job_loc_2').options[$('pref_job_loc_2').selectedIndex].value;
        params = params + '&seeking=' + seeking;
        // params = params + '&current_pos=' + current_pos;
        params = params + '&reason_leaving=' + reason_leaving;
        params = params + '&notice_period=' + $('notice_period').value;
        params = params + '&expected_sal_start=' + $('expected_sal_start').value;
        params = params + '&expected_sal_end=' + $('expected_sal_end').value;
        params = params + '&current_sal_start=' + $('current_sal_start').value;
        params = params + '&current_sal_end=' + $('current_sal_end').value;
        
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
        close_window('career_summary_window');
    }
}

function validate_job_profile() {
    // if ($('specialization').selectedIndex == 0) {
    //     alert('You need to select a specialization.');
    //     return false;
    // } 
    
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
    
    // if (isNaN($('organization_size').value)) {
    //     alert('Only numbers are accepted for Number of Direct Reports.');
    //     return false;
    // } 
    
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
        // $('specialization').selectedIndex = 0;
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
        // window.scrollTo(0, 0);
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
                
                // var specialization = xml.getElementsByTagName('specialization');
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
                
                // for (var i=0; i < $('specialization').options.length; i++) {
                //     if ($('specialization').options[i].value == specialization[0].childNodes[0].nodeValue) {
                //         $('specialization').selectedIndex = i;
                //         break;
                //     }
                // }
                
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
                // window.scrollTo(0, 0);
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
        // params = params + '&specialization=' + $('specialization').value;
        params = params + '&position_title=' + $('position_title').value;
        params = params + '&superior_title=' + $('position_superior_title').value;
        params = params + '&organization_size=' + encodeURIComponent($('organization_size').value);
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

function onDomReady() {}

function onLoaded() {
    initialize_page();
}

window.addEvent('domready', onDomReady);
window.addEvent('load', onLoaded);