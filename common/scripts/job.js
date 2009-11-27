var order_by = 'relevance';
var order = 'desc';
var filter_by = '0';
var candidates_list = new ListBox('candidates', 'candidates_list', true);
var referrers_list = new ListBox('referrers', 'referrers_list', true);

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function duplicated(emails, email) {
    if (isEmpty(emails)) {
        return false;
    }
    
    var temp = emails.split(',');
    for (var i=0; i < temp.length; i++) {
        if (email == temp[i]) {
            return true;
        }
    }
    
    return false;
}

function validate_quick_refer_form() {
    if (isEmpty($('qr_my_file').value)) {
        alert('You need to provide the candidate\'s file resume.');
        return false;
    }
    
    if ((!isEmail($('qr_candidate_email').value) && 
        $('qr_candidate_email_from_list').options[$('qr_candidate_email_from_list').selectedIndex].value == '0') || 
        (isEmail($('qr_candidate_email').value) &&
         $('qr_candidate_email_from_list').options[$('qr_candidate_email_from_list').selectedIndex].value != '0')) {
        alert('You need to either select candidate from your Contacts, or provide a new one by filling up the form.');
        return false;
    }
    
    if (isEmail($('qr_candidate_email').value) && 
        $('qr_candidate_email_from_list').options[$('qr_candidate_email_from_list').selectedIndex].value == '0') {
        if (isEmpty($('qr_candidate_phone').value)) {
            alert('Candidate\'s telephone number must be provided.');
            return false;
        }
        
        if (isEmpty($('qr_candidate_firstname').value)) {
            alert('Candidate\'s firstname must be provided.');
            return false;
        }
        
        if (isEmpty($('qr_candidate_lastname').value)) {
            alert('Candidate\'s lastname must be provided.');
            return false;
        }
        
        if (isEmpty($('qr_candidate_zip').value)) {
            alert('Candidate\'s current residential postcode/zip must be provided.');
            return false;
        }
        
        if ($('qr_candidate_country').options[$('qr_candidate_country').selectedIndex].value == '0') {
            alert('Candidate\'s current residential country must be provided.');
            return false;
        }
    }
    
    var answer_1 = $('testimony_answer_1').value;
    var answer_2 = $('testimony_answer_2').value;
    var answer_3 = $('testimony_answer_3').value;
    var answer_4 = $('testimony_answer_4').value;
    var meet_requirements = ($('meet_req_yes').checked) ? 'Yes' : 'No';
    
    if (isEmpty(answer_1) || (meet_requirements == 'Yes' && isEmpty(answer_2)) || isEmpty(answer_3)) {
        alert('Please briefly answer all questions.');
        return false;
    } else if (answer_1.split(' ').length > 200 || answer_2.split(' ').length > 200 || 
               answer_3.split(' ').length > 200 || answer_4.split(' ').length > 200) {
        if (answer_1.split(' ').length > 200) {
            alert('Please keep your 1st answer below 200 words.');
        } else if (answer_2.split(' ').length > 200) {
            alert('Please keep your 2nd answer below 200 words.');
        } else if (answer_3.split(' ').length > 200) {
            alert('Please keep your 3rd answer below 200 words.');
        } else if (answer_4.split(' ').length > 200) {
            alert('Please keep your 4th and final answer below 200 words.');
        }
        return false;
    }
    
    var agreed = confirm('By clicking "OK", you confirm that you have screened the candidate\'s resume and have also assessed the candidate\'s suitability for this job position. Also, you acknowledge that the employer may contact you for further references regarding the candidate, and you agree to provide any other necessary information requested by the employer.\n\nOtherwise, you may click the "Cancel" button.');
    
    if (!agreed) {
        set_status('');
        close_quick_refer_form();
        return false;
    }
    
    start_quick_refer_upload();
    return true;
}

function validate_quick_upload_form() {
    // if (isEmpty($('qu_my_file').value)) {
    //     alert('You need to provide the candidate\'s file resume.');
    //     return false;
    // }
    
    if (!isEmail($('qu_candidate_email').value)) {
        alert('You need to provide a valid candidate email.');
        return false;
    }
    
    if (isEmpty($('qu_candidate_phone').value)) {
        alert('Candidate\'s telephone number must be provided.');
        return false;
    }
    
    if (isEmpty($('qu_candidate_firstname').value)) {
        alert('Candidate\'s firstname must be provided.');
        return false;
    }
    
    if (isEmpty($('qu_candidate_lastname').value)) {
        alert('Candidate\'s lastname must be provided.');
        return false;
    }
    
    if (isEmpty($('qu_candidate_zip').value)) {
        alert('Candidate\'s current residential postcode/zip must be provided.' + "\nHowever, you can put your own postcode/zip if you do not know the candidate\'s.");
        return false;
    }
    
    if ($('qu_candidate_country').options[$('qu_candidate_country').selectedIndex].value == '0') {
        alert('Candidate\'s current residential country must be provided.' + "\nHowever, you can put your own country if you do not know the candidate\'s.");
        return false;
    }
    
    if (!isEmail($('qu_referrer_email').value)) {
        alert('You need to provide a valid email.');
        return false;
    }
    
    if (isEmpty($('qu_referrer_phone').value)) {
        alert('Your telephone number must be provided.');
        return false;
    }
    
    if (isEmpty($('qu_referrer_firstname').value)) {
        alert('Your firstname must be provided.');
        return false;
    }
    
    if (isEmpty($('qu_referrer_lastname').value)) {
        alert('Your lastname must be provided.');
        return false;
    }
    
    if (isEmpty($('qu_referrer_zip').value)) {
        alert('Your current residential postcode/zip must be provided.');
        return false;
    }
    
    if ($('qu_referrer_country').options[$('qu_referrer_country').selectedIndex].value == '0') {
        alert('Your current residential country must be provided.');
        return false;
    }
    
    if (($('qu_candidate_email').value == $('qu_referrer_email').value) || 
        ($('qu_candidate_phone').value == $('qu_referrer_phone').value)) {
        alert('You CANNOT apply for this job without a referral!' + "\n\n" + 'You will need to sign up and press the "Refer Me" button to refer yourself.');
        return false;
    }
    
    var agreed = confirm('By clicking "OK", you confirm that you have screened the candidate\'s resume and have also assessed the candidate\'s suitability for this job position. Also, you acknowledge that Yellow Elevator may contact you for further references regarding the candidate, and you agree to provide any other necessary information requested by Yellow Elevator.\n\nOtherwise, you may click the "Cancel" button.');
    
    if (!agreed) {
        set_status('');
        close_quick_upload_form();
        return false;
    }
    
    start_quick_upload();
    return true;
}

function show_candidates() {
    $('candidates').set('html', '');
    
    var params = 'id=' + id + '&action=get_candidates';
    params = params + '&filter_by=' + filter_by;
    
    var uri = root + "/search_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            
            if (txt == 'ko') {
                alert('An error occured while loading candidates.');
                return false;
            }
            
            candidates_list.clear();
            
            var ids = xml.getElementsByTagName('id');
            var referee_names = xml.getElementsByTagName('referee_name');
            var referee_emails = xml.getElementsByTagName('referee');
            
            for (var i=0; i < ids.length; i++) {
                candidates_list.add_item(referee_names[i].childNodes[0].nodeValue, referee_emails[i].childNodes[0].nodeValue);
            }
            
            candidates_list.show();
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading referees...');
        }
    });
    
    request.send(params);
}

function show_referrers() {
    $('candidates').set('html', '');
    
    var params = 'id=' + id + '&action=get_candidates';
    params = params + '&filter_by=' + filter_by;
    
    var uri = root + "/search_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while loading referrers.');
                return false;
            }
            
            referrers_list.clear();
            
            var ids = xml.getElementsByTagName('id');
            var contact_names = xml.getElementsByTagName('referee_name');
            var contact_emails = xml.getElementsByTagName('referee');
            
            for (var i=0; i < ids.length; i++) {
                referrers_list.add_item(contact_names[i].childNodes[0].nodeValue, contact_emails[i].childNodes[0].nodeValue);
            }
            
            referrers_list.show();
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading referrers...');
        }
    });
    
    request.send(params);
}

function save_job() {
    if (id <= 0) {
        window.location = root + '/members?job=' + $('job_id').value;
        navigator.reload();
    }
    
    var params = 'id=' + $('job_id').value;
    params = params + '&member=' + id;
    params = params + '&action=save_job_to_bin';
    
    var uri = root + "/search_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while saving job.');
                return false;
            } 
            
            if (txt == '-1') {
                alert('This job was previously saved.');
                set_status('');
                return true;
            }
            
            alert('Job was successfully saved.');
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Saving job...');
        }
    });
    
    request.send(params);
}

function close_refer_form() {
    $('div_refer_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_refer_job() {
    if (id <= 0) {
        window.location = root + '/members?job=' + $('job_id').value;
        navigator.reload();
    }
    
    $('div_blanket').setStyle('display', 'block');
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_refer_form').getStyle('height'));
    var div_width = parseInt($('div_refer_form').getStyle('width'));
    
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
    
    $('div_refer_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_refer_form').setStyle('left', ((window_width - div_width) / 2));
    
    $('job_title').set('html', $('job.title').get('html'));
    
    $('div_refer_form').setStyle('display', 'block');
    show_candidates();
}

function set_filter(_is_from_request_form) {
    if (!_is_from_request_form) {
        filter_by = $('network_filter').options[$('network_filter').selectedIndex].value;
        show_candidates();
    } else {
        filter_by = $('network_filter_request').options[$('network_filter_request').selectedIndex].value;
        show_referrers();
    }
    
}

function check_has_banks(_member) {
    var params = 'id=' + _member + '&action=has_banks';
    var uri = root + "/search_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == '0') {
                alert('Our system indicates that you have not provided us your bank account details. \n\nIf you like us to transfer your rewards directly into your bank account, please go to the "Bank Accounts" page to submit your bank account details. \n\nHowever, if you wish to receive your rewards by cheque instead, please ensure that your full name and mailing address in the "Profile" page is valid.');
            } 
        },
        onRequest: function(instance) {
            set_status('Checking reward matters...');
        }
    });
    
    request.send(params);
}

function check_referred_already() {
    if (!isEmpty(candidates_list.selected_value)) {
        var params = 'job=' + $('job_id').value + 
                      '&id=' + id + 
                      '&candidate=' + candidates_list.selected_value + 
                      '&action=referred_already';
        
        var uri = root + "/search_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == '1') {
                    alert("You have already referred " + candidates_list.selected_item + " to this job. \n\nPlease unselect " + candidates_list.selected_item + " from the Contacts list.");
                } 
                
                set_status('');
            },
            onRequest: function(instance) {
                set_status('Checking referral existence...');
            }
        });

        request.send(params);
    }
}

function refer() {
    var referee = '';
    var from = 'list'; // list or email
    if ($('from_list').checked) {
        var referees = candidates_list.get_selected_values();
        var number_of_referees = referees.length;
        
        if (number_of_referees <= 0) {
            alert('Please select at least a candidate.');
            return false;
        }
        
        for (var i=0; i < number_of_referees; i++) {
            var referee_details = referees[i].split('|');
            referee = referee + referee_details[1];

            if (i < number_of_referees-1) {
                referee = referee + '|';
            }
        }
    } else {
        if (isEmpty($('email_addr').value)) {
            alert('Please provide at least a valid email address of the candidate.');
            return false;
        }
        
        var temp = $('email_addr').value;
        temp = temp.replace(/ /g, ',');
        temp = temp.replace(/;/g, ',');
        temp = temp.replace(/\n/g, ',');
        var emails = temp.split(',');
        for (var i=0; i < emails.length; i++) {
            if (!isEmail(emails[i])) {
                if (!isEmpty(emails[i])) {
                    alert('One of your e-mail addresses is invalid- <strong>' + emails[i] + '</strong>');
                    return false;
                }
            }

            if (!isEmpty(emails[i]) && !duplicated(referee, emails[i])) {
                if (isEmpty(referee)) {
                    referee = emails[i];
                } else {
                    referee = referee + '|' + emails[i];
                }
            }
        }
        
        from = 'email';
    }
    
    // var answer_1 = $('testimony_answer_1').value;
    // var answer_2 = $('testimony_answer_2').value;
    // var answer_3 = $('testimony_answer_3').value;
    
    // if (isEmpty(answer_1) || isEmpty(answer_2) || isEmpty(answer_3)) {
    //     alert('Please briefly answer all questions.');
    //     return false;
    // } else if (answer_1.split(' ').length > 50 || answer_3.split(' ').length > 50 || answer_3.split(' ').length > 50) {
    //     if (answer_1.split(' ').length > 50) {
    //         alert('Please keep your 1st answer below 50 words.');
    //     } else if (answer_2.split(' ').length > 50) {
    //         alert('Please keep your 2nd answer below 50 words.');
    //     } else if (answer_3.split(' ').length > 50) {
    //         alert('Please keep your 3rd and final answer below 50 words.');
    //     }
    //     return false;
    // }
    // 
    // var testimony = answer_1 + '<br/>' + answer_2 + '<br/>' + answer_3;
    
    check_has_banks(id);
    
    var proceed = confirm('Your referred candidates will be requested to submit their resumes. As a referrer, you are responsible for screening your candidates\' resumes to confirm that they are suitable for this job position before recommending them.\n\nYou will be notified by email to check the "Referral Requests" section once the resumes are submitted.\n\nClick "OK" to continue or "Cancel" to make changes.');
    
    if (!proceed) {
        set_status('');
        return false;
    }
    
    var params = 'id=' + id + '&action=make_referral';
    params = params + '&from=' + from;
    params = params + '&referee=' + referee;
    params = params + '&job=' + $('job_id').value;
    // params = params + '&testimony=' + testimony;
    
    var uri = root + "/search_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while referring the candidate to the job. \n\nIt might because of this referral had been made before. Please try again.');
                close_refer_form();
                set_status('');
                return false;
            } else if (txt == '-900') {
                alert('An error occured while adding the potential candidate into your contacts list. Please try again.');
                close_refer_form();
                set_status('');
                return false;
            } else if (txt == '-901') {
                alert('An error occured while inviting the potential candidate to become a member. Please try again.');
                close_refer_form();
                set_status('');
                return false;
            } else if (txt == '-902') {
                alert('An error occured while reserving a member place for the potential candidate. Please try again.');
                close_refer_form();
                set_status('');
                return false;
            } else if (txt == '-903') {
                alert('Hmm... an error occured while adding the potential candidate into your contacts list after inviting and reserving a place. Please try again.');
                close_refer_form();
                set_status('');
                return false;
            } /*else if (txt == '-2') {
                alert('It appears that this candidate is not in your candidates list. The candidate will be notified before the referral can be made. \n\nYellow Elevator will automatically complete the referral process once the candidate approved the request of being added to your list.');
            } else if (txt == '-3') {
                alert('It appears that this candidate is not in a member of Yellow Elevator. The candidate will be notified before the referral can be made. \n\nYellowElevator.com will automatically complete the referral process once the candidate had signed up as a member. The candidate will be added into your contacts list automatically.');
            }*/
            
            close_refer_form();
            set_status('Your contact was successfully referred. A notification email has been sent to the referred contact. You may make another referrals.');
        },
        onRequest: function(instance) {
            set_status('Making referral...');
        }
    });
    
    request.send(params);
}

function close_refer_me() {
    $('div_acknowledge_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_refer_me() {
    if (id <= 0) {
        window.location = root + '/members?job=' + $('job_id').value;
        navigator.reload();
    }
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_acknowledge_form').getStyle('height'));
    var div_width = parseInt($('div_acknowledge_form').getStyle('width'));
    
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
    
    $('div_acknowledge_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_acknowledge_form').setStyle('left', ((window_width - div_width) / 2));
    
    var params = 'id=' + id;
    params = params + '&action=has_resumes';
    
    var uri = root + "/members/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            if (txt == '0') {
                alert('You have not created or uploaded your resume yet. In order to request for a referral, you need to either create or upload a resume at the "Resumes" section. \n\nIf you have already created one, please ensure that the \'Private\' check box is unchecked.');
                referral = 0;
                return false;
            }
            
            //$('ack.job_title').set('html', $('job.title').get('html'));
            $('div_blanket').setStyle('display', 'block');
            $('div_acknowledge_form').setStyle('display', 'block');
        },
        onRequest: function(instance) {
            set_status('Checking resumes...');
        }
    });
    
    request.send(params);
    show_referrers();
}

function refer_me() {
    if ($('resume').options[$('resume').selectedIndex].value == '0') {
        alert('Please choose a resume to proceed.');
        return false;
    }
    
    var referrer = '';
    var from = 'contacts';
    if ($('referrer_contacts').checked) {
        var referrers = referrers_list.get_selected_values();
        number_of_referrers = referrers.length;
        for (var i=0; i < number_of_referrers; i++) {
            var referrer_details = referrers[i].split('|');
            referrer = referrer + referrer_details[1];

            if (i < number_of_referrers-1) {
                referrer = referrer + '|';
            }
        }
    } else if ($('referrer_others').checked) {
        from = 'others';
        if (isEmpty($('referrer_emails').value)) {
            salert('You need to enter at least an e-mail address.');
            return false;
        }
        
        var temp = $('referrer_emails').value;
        temp = temp.replace(/ /g, ',');
        temp = temp.replace(/;/g, ',');
        temp = temp.replace(/\n/g, ',');
        var emails = temp.split(',');
        for (var i=0; i < emails.length; i++) {
            if (!isEmail(emails[i])) {
                if (!isEmpty(emails[i])) {
                    alert('One of your e-mail addresses is invalid- <strong>' + emails[i] + '</strong>');
                    return false;
                }
            }

            if (!isEmpty(emails[i]) && !duplicated(referrer, emails[i])) {
                if (isEmpty(referrer)) {
                    referrer = emails[i];
                } else {
                    referrer = referrer + '|' + emails[i];
                }
            }
        }
    } else {
        from = 'yel';
        referrer = 'initial@yellowelevator.com';
    }
    
    var params = 'id=' + id; 
    params = params + '&job=' + $('job_id').value;
    params = params + '&resume=' + $('resume').options[$('resume').selectedIndex].value;
    params = params + '&referrer=' + referrer;
    params = params + '&from=' + from;
    params = params + '&action=refer_me';
    
    var uri = root + "/search_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('Some of your requests were not send due to duplications.');
                return false;
            }
            
            set_status('');
            if (txt == '-1') {
                alert('Sorry, this job only accepts resumes created online.');
                return false;
            } else if (txt == '-2') {
                alert('Sorry, this job only accepts uploaded file resumes.');
                return false;
            }
            
            set_status('Your resume has been received, and you will be referred shortly.');
            close_refer_me();
        },
        onRequest: function(instance) {
            set_status('Referring you to job...');
        }
    });
    
    request.send(params);
}

function start_quick_refer_upload() {
    $('qr_upload_progress').setStyle('display', 'block');
    $('table_quick_refer_form').setStyle('display', 'none');
    return true;
}

function stop_quick_refer_upload(_error) {
    $('qr_upload_progress').setStyle('display', 'none');
    $('table_quick_refer_form').setStyle('display', 'block');
    set_status('');
    
    switch (_error) {
        case '1':
            close_quick_refer_form();
            set_status('The resume was successfully referred!<br/>However, the referral can only be completed when the new candidate signs up.');
            break;
        case '0':
            close_quick_refer_form();
            set_status('The resume was successfully referred!');
            break;
        case '-1':
            alert('You CANNOT refer yourself!');
            break;
        case '-2':
            alert('Unable to add candidate to Contacts. Please try again later.');
            break;
        case '-3':
            alert('Unable to create membership on-behalf. Please try again later.');
            break;
        case '-4':
            alert('Unable to create membership activation token. Please try again later.');
            break;
        case '-5':
            alert('Unable to create resume record. Please try again later.');
            break;
        case '-6':
            alert('An error occurred while reading uploaded file. Please ensure the resume file meet the requirements stated.');
            break;
        case '-7':
            alert('Unable to make the referral. Please try again later.');
            break;
        default:
            alert('An error occurred while referring the resume to the job. Please try again later.');
            break;
    }
}

function close_quick_refer_form() {
    $('div_quick_refer_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_quick_refer_form() {
    if (id <= 0) {
        window.location = root + '/members?job=' + $('job_id').value;
        navigator.reload();
    }
    
    $('div_blanket').setStyle('display', 'block');
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_quick_refer_form').getStyle('height'));
    var div_width = parseInt($('div_quick_refer_form').getStyle('width'));
    
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
    
    $('div_quick_refer_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_quick_refer_form').setStyle('left', ((window_width - div_width) / 2));
    
    var quick_refer_form = $('div_quick_refer_form');
    var spans = quick_refer_form.getElementsByTagName('span');
    
    for (var i=0; i < spans.length; i++) {
        if (spans[i].id == 'qr_job_title') {
            spans[i].innerHTML = $('job.title').get('html');
        } 
    }
    
    $('div_quick_refer_form').setStyle('display', 'block');
}

function close_quick_upload_form() {
    $('div_quick_upload_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_quick_upload_form() {
    $('div_blanket').setStyle('display', 'block');
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_quick_upload_form').getStyle('height'));
    var div_width = parseInt($('div_quick_upload_form').getStyle('width'));
    
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
    
    $('div_quick_upload_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_quick_upload_form').setStyle('left', ((window_width - div_width) / 2));
    
    $('qu_job_title').set('html', $('job.title').get('html'));
    
    $('div_quick_upload_form').setStyle('display', 'block');
}

function start_quick_upload() {
    $('qu_upload_progress').setStyle('display', 'block');
    $('table_quick_upload_form').setStyle('display', 'none');
    return true;
}

function stop_quick_upload(_error) {
    $('qu_upload_progress').setStyle('display', 'none');
    $('table_quick_upload_form').setStyle('display', 'block');
    set_status('');
    
    switch (_error) {
        case '0':
            close_quick_upload_form();
            set_status('Thanks for submitting the resume to us!<br/>We will contact you shortly when we found a suitable position for your candidate.');
            break;
        case '-1':
            alert('The file provided is NOT readable. Please makesure the file meet the requirements as stated.');
            break;
        case '-2':
            alert('Unable to buffer the upload. Please try again.');
            break;
        case '-3':
            alert('Unable to update system. Please try again.');
            break;
        default:
            alert('An error occurred while uploading resume. Please try again.');
            break;
    }
}

function onDomReady() {
    set_root();
    get_employers_for_mini();
    get_industries_for_mini();
    set_mini_keywords();
    
    if (id != '0') {
        get_referrals_count();
        get_requests_count();
        get_jobs_employed_count();
    }
    
    $('candidates').addEvent('click', function() {
        check_referred_already();
    });
    
    if (isEmpty(keywords)) {
        $('mini_keywords').value = 'Job title or keywords';
    } else {
        $('mini_keywords').value = keywords;
    }
    
    $('testimony_answer_1').addEvent('keypress', function() {
       update_word_count_of('word_count_q1', 'testimony_answer_1') 
    });
    
    $('testimony_answer_2').addEvent('keypress', function() {
       update_word_count_of('word_count_q2', 'testimony_answer_2') 
    });
    
    $('testimony_answer_3').addEvent('keypress', function() {
       update_word_count_of('word_count_q3', 'testimony_answer_3') 
    });
    
    $('testimony_answer_4').addEvent('keypress', function() {
       update_word_count_of('word_count_q4', 'testimony_answer_4') 
    });
    
    var suggest_url = root + '/common/php/search_suggest.php';
    new Autocompleter.Ajax.Json('mini_keywords', suggest_url, {
        'postVar': 'keywords',
        'minLength' : 1,
        'overflow' : true,
        'delay' : 50
    });
}

window.addEvent('domready', onDomReady);
