var refer_wizard_page = 'referrer';
var is_linkedin = false;
var is_facebook = false;

var refer_candidates = new Array();
var refer_num_candidates = 0;

function close_refer_popup(_proceed_refer) {
    refer_wizard_page = 'referrer';
    
    if (_proceed_refer) {
        
        
        if (!isEmail($('candidate_email').value)) {
            alert('Candidate e-mail address is empty or not valid.');
            return false;
        }
        
        if (isEmpty($('candidate_phone').value)) {
            alert('Candidate telephone number is empty.');
            return false;
        }
        
        if (isEmpty($('candidate_name').value)) {
            alert('You need to at least provide candidate\'s name for us to contact.');
            return false;
        }
        
        if (isEmpty($('candidate_resume').value)) {
            var proceed = confirm("It will be better if you provide us the candidate's resume. However, you may choose to proceed if you do not have it now.\n\nClick 'OK' to proceed, 'Cancel' otherwise.");
            
            if (!proceed) {
                return false;
            }
        }
        
        close_safari_connection();
        // $('refer_form').submit();
        $('refer_progress').setStyle('display', 'block');
        $('refer_form').setStyle('display', 'none');
        
        return true;
    }
    close_window('refer_window');
}

function show_refer_popup() {
    // reset the wizard page
    refer_wizard_page = 'referrer';
    $('refer_next_btn').setStyle('display', 'show');
    $('refer_next_btn').disabled = true;
    
    // get connection names from LinkedIn and update the list
    IN.API.Connections("me")
        .fields("id", "firstName", "lastName")
        .result(function(_connections, _metadata) {
            var connections = _connections.values;
            
            // check are there any connections. if not, default to form-based
            if (connections.length <= 0) {
                is_linkedin = false;
                $('refer_next_btn').disabled = false;
                return;
            }
            
            is_linkedin = true;
            var html = '<select class="connections_list" id="connections_list" multiple="1">';
            var i = 0;
            for (i=0; i < connections.length; i++) {
                var first_name = connections[i].firstName;
                var last_name = connections[i].lastName;
                var id = connections[i].id;
                
                var option = '<option value="' + last_name + ', ' + first_name + '">';
                option = option + 'LinkedIn: ' + last_name + ', ' + first_name;
                option = option + '</option>' + "\n";
                
                html = html + option;
            }
            html = html + "</select>";
            
            $('list_placeholder').set('html', html);
            $('refer_next_btn').disabled = false;
    });
    
    $('refer_referrer_contacts').setStyle('display', 'block');
    $('refer_candidates_social').setStyle('display', 'none');
    $('refer_candidates_default').setStyle('display', 'none');
    
    show_window('refer_window');
    // window.scrollTo(0, 0);
}

function show_next_refer_wizard_page() {
    switch (refer_wizard_page) {
        case 'referrer':
            // validate referrer contact form
            if (!isEmail($('referrer_email').value)) {
                alert('Your e-mail address is empty or not valid.');
                return false;
            }

            if (isEmpty($('referrer_phone').value)) {
                alert('Your telephone number is empty.');
                return false;
            }

            if (isEmpty($('referrer_name').value)) {
                alert('You need to at least provide your name for us to contact you.');
                return false;
            }
            
            refer_wizard_page = 'candidates';
            $('refer_referrer_contacts').setStyle('display', 'none');
            
            if (is_linkedin || is_facebook) {
                // logged in via linkedin or/and facebook
                $('refer_candidates_social').setStyle('display', 'block');
                $('refer_candidates_default').setStyle('display', 'none');
            } else {
                // use form-based instead
                $('refer_candidates_social').setStyle('display', 'none');
                $('refer_candidates_default').setStyle('display', 'block');
            }
            
            $('refer_next_btn').disabled = true;
            $('refer_next_btn').value = 'Recommend Now';
            break;
        case 'candidates':
            // TODO: validate the candiates list
            break;
    }
    
    show_window('refer_window');
    return true;
}

function add_candidates_to_list(_is_not_from_list) {
    var html = '';
    
    if (_is_not_from_list) {
        html = '<div class="refer_candidates" id="refer_candidate_' + refer_num_candidates + '">' + "\n";
        html = html + 'Name: <input type="text" id="refer_candidate_name_' + refer_num_candidates + '" /><br/>' + "\n";
        html = html + 'Email: <input type="text" id="refer_candidate_email_' + refer_num_candidates + '" /><br/>' + "\n";
        html = html + 'Phone: <input type="text" id="refer_candidate_phone_' + refer_num_candidates + '" /><br/>' + "\n";
        html = html + 'Current Position: <input type="text" id="refer_candidate_pos_' + refer_num_candidates + '" /><br/>' + "\n";
        html = html + 'Current Employer: <input type="text" id="refer_candidate_emp_' + refer_num_candidates + '" /><br/>' + "\n";
        html = html + '<input type="button" value="Remove" onClick="remove_candidate_from_list(' + refer_num_candidates + ');" />' + "\n";
        html = html + '</div>';
        refer_num_candidates++;
    } else {
        var selected_candidates = $('connections_list').getSelected();
        var new_candidates = new Array();
        
        if (selected_candidates.length <= 0) {
            alert('You need to select at least one candidate from your connection.');
            return false;
        }
        
        // check any new one
        if (refer_candidates.length > 0) {
            for (var i=0; i < selected_candidates.length; i++) {
                var is_exists = false;
                for (var j=0; j < refer_candidates.length; j++) {
                    if (selected_candidates[i].value == refer_candidates[j].name) {
                        is_exists = true;
                        break;
                    }
                }
                
                if (!is_exists) {
                    refer_candidates[refer_candidates.length] = new ReferCandidate(selected_candidates[i].value, '', '', '', '');
                    new_candidates[new_candidates.length] = selected_candidates[i].value;
                }
            }
        } else {
            for (var i=0; i < selected_candidates.length; i++) {
                refer_candidates[refer_candidates.length] = new ReferCandidate(selected_candidates[i].value, '', '', '', '');
                new_candidates[new_candidates.length] = selected_candidates[i].value;
            }
        }
        
        // add to list
        for (var i=0; i < new_candidates.length; i++) {
            html = html + '<div class="refer_candidates" id="refer_candidate_' + refer_num_candidates + '">' + "\n";
            html = html + 'Name: <input type="text" id="refer_candidate_name_' + refer_num_candidates + '" value="' + new_candidates[i] + '" /><br/>' + "\n";
            html = html + 'Email: <input type="text" id="refer_candidate_email_' + refer_num_candidates + '" /><br/>' + "\n";
            html = html + 'Phone: <input type="text" id="refer_candidate_phone_' + refer_num_candidates + '" /><br/>' + "\n";
            html = html + 'Current Position: <input type="text" id="refer_candidate_pos_' + refer_num_candidates + '" /><br/>' + "\n";
            html = html + 'Current Employer: <input type="text" id="refer_candidate_emp_' + refer_num_candidates + '" /><br/>' + "\n";
            html = html + '<input type="button" value="Remove" onClick="remove_candidate_from_list(' + refer_num_candidates + ');" />' + "\n";
            html = html + '</div>' + "\n";
            
            refer_num_candidates++;
        }
    }
    
    if ($('info_list_tip') != null) {
        // clear the tip
        $('info_list_placeholder').set('html', '');
    }
    
    $('info_list_placeholder').set('html', $('info_list_placeholder').get('html') + "\n" + html);
    
    $('refer_next_btn').disabled = false;
}

function remove_candidate_from_list(_idx) {
    var html = '';
    
    // remove from array
    var new_refer_candidates = new Array();
    var name = $('refer_candidate_name_' + _idx).value;
    for (var i=0; i < refer_candidates.length; i++) {
        if (refer_candidates[i].name != name) {
            new_refer_candidates[new_refer_candidates.length] = refer_candidates[i];
        }
    }
    refer_candidates = new_refer_candidates;
    
    // remove from visible list
    var new_idx = 0;
    for (var i=0; i < refer_num_candidates; i++) {
        if (i != _idx) {
            html = html + '<div class="refer_candidates" id="refer_candidate_' + new_idx + '">' + "\n";
            html = html + 'Name: <input type="text" id="refer_candidate_name_' + new_idx + '" value="' + $('refer_candidate_name_' + i).value + '" /><br/>' + "\n";
            html = html + 'Email: <input type="text" id="refer_candidate_email_' + new_idx + '" value="' + $('refer_candidate_email_' + i).value + '" /><br/>' + "\n";
            html = html + 'Phone: <input type="text" id="refer_candidate_phone_' + new_idx + '" value="' + $('refer_candidate_phone_' + i).value + '" /><br/>' + "\n";
            html = html + 'Current Position: <input type="text" id="refer_candidate_pos_' + new_idx + '" value="' + $('refer_candidate_pos_' + i).value + '" /><br/>' + "\n";
            html = html + 'Current Employer: <input type="text" id="refer_candidate_emp_' + new_idx + '" value="' + $('refer_candidate_emp_' + i).value + '" /><br/>' + "\n";
            html = html + '<input type="button" value="Remove" onClick="remove_candidate_from_list(' + new_idx + ');" />' + "\n";
            html = html + '</div>' + "\n";
            
            new_idx++;
        }
    }
    
    refer_num_candidates = new_idx;
    
    $('info_list_placeholder').set('html', html);
    
    if (refer_num_candidates <= 0) {
        $('refer_next_btn').disabled = true;
    }
}

function toggle_resume_upload() {
    if ($('existing_resume').options[$('existing_resume').selectedIndex].value != '0') {
        $('apply_resume').disabled = true;
    } else {
        $('apply_resume').disabled = false;
    }
}

function close_apply_popup(_proceed_refer) {
    if (_proceed_refer) {
        if (!isEmail($('apply_email').value)) {
            alert('Your e-mail address is empty or not valid.');
            return false;
        }
        
        if (isEmpty($('apply_phone').value)) {
            alert('Your telephone number is empty.');
            return false;
        }
        
        if (isEmpty($('apply_name').value)) {
            alert('You need to at least provide your name for us to contact you.');
            return false;
        }
        
        if (isEmpty($('apply_current_pos').value)) {
            alert('You need to provide your current position.');
            return false;
        }
        
        if (isEmpty($('apply_current_emp').value)) {
            alert('You need to provide your current company.');
            return false;
        }
        
        if ($('apply_resume').disabled) {
            if ($('existing_resume').selectedIndex == 0) {
                alert('You need to supply us a resume for screening.');
                return false;
            }
        } else {
            if (isEmpty($('apply_resume').value)) {
                alert('You need to supply us a resume for screening.');
                return false;
            }
        }
        
        close_safari_connection();
        // $('apply_form').submit();
        $('apply_progress').setStyle('display', 'block');
        $('apply_form').setStyle('display', 'none');
        
        return true;
    }
    close_window('apply_window');
}

function show_apply_popup() {
    $('buffer_id').value = buffer_id;
    
    if (!isEmpty(buffer_id)) {
        $('apply_current_emp').value = current_employer;
        $('apply_current_pos').value = current_position;
        
        if (id == '0') {
            $('apply_name').value = candidate_name;
            $('apply_email').value = candidate_email;
            $('apply_phone').value = candidate_phone;
        }
    }
    
    show_window('apply_window');
    // window.scrollTo(0, 0);
}

function onDomReady() {
    initialize_page();
    
    if (!isEmpty(show_popup)) {
        if (show_popup == 'refer') {
            show_refer_popup();
        } else if (show_popup == 'apply') {
            show_apply_popup();
        }
    }
    
    if (alert_error) {
        alert('An error occured while submitting your request. The following may trigger the error:' + "\n\n1. You have referred the same candidate to, or applied for, the same job before.\n2. The resume you uploaded exceeds 1MB and is not of DOC, PDF, TXT and HTML format.");
    }
    
    if (alert_success) {
        alert('Your request was successfully submitted.' + "\n\nWe will contact the candidate shortly.");
    }
}

window.addEvent('domready', onDomReady);
