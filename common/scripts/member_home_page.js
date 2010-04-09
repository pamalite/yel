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

function onDomReady() {
    set_root();
    
    if ($('div_hrm_census').getStyle('display') != 'none') {
        new OverText($('birthdate_year'));
        new OverText($('ethnicity_txt'));
    }
    
    /*var suggest_url = root + '/common/php/search_suggest.php';
    new Autocompleter.Ajax.Json('mini_keywords', suggest_url, {
        'postVar': 'keywords',
        'minLength' : 1,
        'overflow' : true,
        'delay' : 50
    });*/
}

window.addEvent('domready', onDomReady);