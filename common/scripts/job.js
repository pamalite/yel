var refer_wizard_page = 'referrer';
var is_linkedin = false;
var is_facebook = false;
var refer_num_candidates = 0;

var connections_list = new ListBox('list_placeholder', 'connections_list', true);

function close_refer_popup(_proceed_refer) {
    refer_wizard_page = 'referrer';    
    close_window('refer_window');
}

function show_refer_popup() {
    // reset the wizard page
    refer_wizard_page = 'referrer';
    $('refer_next_btn').value = 'Next >';
    $('refer_next_btn').disabled = true;
    $('lbl_loading_status').set('html', 'Preparing next page...');
    
    // get connection names from LinkedIn and update the list
    if (typeof IN.API == 'undefined') {
        is_linkedin = false;
        $('refer_next_btn').disabled = false;
        $('lbl_loading_status').set('html', 'Done! Press Next to continue.');
    } else {
        IN.API.Connections("me")
            .fields("id", "firstName", "lastName", "threeCurrentPositions")
            .result(function(_connections, _metadata) {
                var connections = _connections.values;

                // check are there any connections. if not, default to form-based
                if (connections.length <= 0) {
                    is_linkedin = false;
                    $('refer_next_btn').disabled = false;
                    $('lbl_loading_status').set('html', 'Done! Press Next to continue.');
                    return;
                }

                is_linkedin = true;
                connections_list.clear();
                var i = 0;
                for (i=0; i < connections.length; i++) {
                    var first_name = connections[i].firstName;
                    var last_name = connections[i].lastName;
                    var id = connections[i].id;
                    
                    if (id != 'private') {
                        var item = '<img src="../common/images/icons/linkedin_icon_small.gif" /> <span class="connection_name">' + last_name + ', ' + first_name + '</span>';
                        var value = 'L:' + last_name + ', ' + first_name;
                        
                        if (connections[i].threeCurrentPositions._total > 0) {
                            var positions = connections[i].threeCurrentPositions.values;
                            item = item + '<br/><span class="connection_position">' + positions[0].title + ' @ ' + positions[0].company.name + '</span>';
                        }
                    }
                    
                    connections_list.add_item(item, value);
                }

                connections_list.show();
                $('refer_next_btn').disabled = false;
                $('lbl_loading_status').set('html', 'Done! Press Next to continue.');
            })
            .error(function() {
                is_linkedin = false;
                $('refer_next_btn').disabled = false;
                $('lbl_loading_status').set('html', 'Done! Press Next to continue.');
            });
    }
    
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
            if (refer_num_candidates > 0 && (is_linkedin || is_facebook)) {
                $('refer_next_btn').disabled = false;
            } else if (refer_num_candidates <= 0 && !is_linkedin && !is_facebook) {
                $('refer_next_btn').disabled = false;
            }
            $('refer_next_btn').value = 'Recommend Now';
            $('lbl_loading_status').set('html', 'Press Recommend Now to finish.');
            break;
        case 'candidates':
            // validate
            var is_single_candidate = true;
            if (refer_num_candidates <= 0) {
                if (is_linkedin || is_facebook) {
                    alert('You need to add at least one candidate.');
                    return false;
                }
                
                // normal validate
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
                
                if (isEmpty($('candidate_current_pos').value)) {
                    alert('Candidate current job position is empty.');
                    return false;
                }

                if (isEmpty($('candidate_current_emp').value)) {
                    alert('Candidate current employer is empty.');
                    return false;
                }
            } else {
                // mass validate
                is_single_candidate = false;
                
                for (var i=0; i < refer_num_candidates; i++) {
                    if (!isEmail($('refer_candidate_email_' + i).value)) {
                        alert('Candidate e-mail address is empty or not valid.');
                        return false;
                    }

                    if (isEmpty($('refer_candidate_phone_' + i).value)) {
                        alert('Candidate telephone number is empty.');
                        return false;
                    }

                    if (isEmpty($('refer_candidate_name_' + i).value)) {
                        alert('You need to at least provide candidate\'s name for us to contact.');
                        return false;
                    }
                    
                    if (isEmpty($('refer_candidate_pos_' + i).value)) {
                        alert('Candidate current position is empty.');
                        return false;
                    }
                    
                    
                    if (isEmpty($('refer_candidate_emp_' + i).value)) {
                        alert('Candidate current employer is empty.');
                        return false;
                    }
                }
            }
            
            // ask whether to reveal referrer name
            var is_reveal_name = confirm('Perhaps would you like to reveal your name (as the recommender) to your friends?' + "\n\nClick 'OK' for Yes, and 'Cancel' for No.");
            
            // confirm submission
            if (!confirm('Confirm to submit your recommendations?')) {
                return false;
            }
            
            // submit and close popup
            var params = 'job_id=' + $('job_id').value;
            params = params + '&referrer_email=' + $('referrer_email').value;
            params = params + '&referrer_phone=' + $('referrer_phone').value;
            params = params + '&referrer_name=' + $('referrer_name').value;
            
            if (is_reveal_name) {
                params = params + '&is_reveal_name=1';
            } else {
                params = params + '&is_reveal_name=0';
            }
            
            var xml = '<candidates>';
            if (is_single_candidate) {
                xml = xml + '<candidate>';
                xml = xml + '<email_addr>' + $('candidate_email').value + '</email_addr>';
                xml = xml + '<phone_num>' + $('candidate_phone').value + '</phone_num>';
                xml = xml + '<name>' + $('candidate_name').value + '</name>';
                xml = xml + '<current_position>' + $('candidate_current_pos').value + '</current_position>';
                xml = xml + '<current_employer>' + $('candidate_current_emp').value + '</current_employer>';
                xml = xml + '<social></social>';
                xml = xml + '</candidate>';
            } else {
                for (var i=0; i < refer_num_candidates; i++) {
                    xml = xml + '<candidate>';
                    xml = xml + '<email_addr>' + $('refer_candidate_email_' + i).value + '</email_addr>';
                    xml = xml + '<phone_num>' + $('refer_candidate_phone_' + i).value + '</phone_num>';
                    xml = xml + '<name>' + $('refer_candidate_name_' + i).value + '</name>';
                    xml = xml + '<current_position>' + $('refer_candidate_pos_' + i).value + '</current_position>';
                    xml = xml + '<current_employer>' + $('refer_candidate_emp_' + i).value + '</current_employer>';
                    xml = xml + '<social>' + $('refer_candidate_social_' + i).value + '</social>';
                    xml = xml + '</candidate>';
                }
                
            }
            xml = xml + '</candidates>';
            params = params + '&payload=' + xml;
            
            var uri = root + "/refer_action.php";
            var request = new Request({
                url: uri,
                method: 'post',
                onSuccess: function(txt, xml) {
                    // set_status('<pre>' + txt + '</pre>');
                    // return;
                    if (txt != 'ok') {
                        var msg = 'An error occured while submitting the following candidates. Please try again later.' + "\n";
                        var error_candidates = txt.split(',');
                        for (var i=0; i < error_candidates.length; i++) {
                            msg = msg + "\n" + error_candidates[i];
                        }
                        
                        alert(msg);
                        return false;
                    }
                    close_window('refer_window');
                }
            });
            request.send(params);
            
            return true;
    }
    
    show_window('refer_window');
    return true;
}

function add_candidates_to_list(_is_not_from_list) {
    var html = '';
    
    if (_is_not_from_list) {
        html = '<div class="refer_candidates" id="refer_candidate_' + refer_num_candidates + '">' + "\n";
        html = html + '<input type="hidden" id="refer_candidate_social_' + refer_num_candidates + '" value="" />' + "\n";
        html = html + 'Name: <input type="text" id="refer_candidate_name_' + refer_num_candidates + '" /><br/>' + "\n";
        html = html + 'Email: <input type="text" id="refer_candidate_email_' + refer_num_candidates + '" /><br/>' + "\n";
        html = html + 'Phone: <input type="text" id="refer_candidate_phone_' + refer_num_candidates + '" /><br/>' + "\n";
        html = html + 'Current Position: <input type="text" id="refer_candidate_pos_' + refer_num_candidates + '" /><br/>' + "\n";
        html = html + 'Current Employer: <input type="text" id="refer_candidate_emp_' + refer_num_candidates + '" /><br/>' + "\n";
        html = html + '<input type="button" value="Remove" onClick="remove_candidate_from_list(' + refer_num_candidates + ');" />' + "\n";
        html = html + '</div>';
        refer_num_candidates++;
    } else {
        var selected_candidates_raw = connections_list.get_selected_values();
        var selected_candidates = new Array();
        for (var i=0; i < selected_candidates_raw.length; i++) {
            var item_value = selected_candidates_raw[i].split('|');
            selected_candidates[i] = new Hash({
                text: item_value[0],
                value: item_value[1]
            });
        }
        
        var new_candidates = new Array();
        
        if (selected_candidates.length <= 0) {
            alert('You need to select at least one candidate from your connection.');
            return false;
        }
        
        // check any new one
        if (refer_num_candidates > 0) {
            for (var i=0; i < selected_candidates.length; i++) {
                var is_exists = false;
                var candidate_name = selected_candidates[i].value;
                var candidate_social = 'LinkedIn';
                if (selected_candidates[i].value.charAt(0) == 'F') {
                    candidate_social = 'Facebook';
                }
                
                candidate_name = candidate_name.substr(2);
                
                for (var j=0; j < refer_num_candidates; j++) {
                    if (candidate_name == $('refer_candidate_name_' + j).value) {
                        is_exists = true;
                        break;
                    }
                }
                
                if (!is_exists) {
                    new_candidates[new_candidates.length] = candidate_social + '|' + candidate_name;
                }
            }
        } else {
            for (var i=0; i < selected_candidates.length; i++) {
                var candidate_name = selected_candidates[i].value;
                var candidate_social = 'LinkedIn';
                if (selected_candidates[i].text.charAt(0) == 'F') {
                    candidate_social = 'Facebook';
                }
                
                new_candidates[new_candidates.length] = candidate_social + '|' + candidate_name;
            }
        }
        
        // add to list
        for (var i=0; i < new_candidates.length; i++) {
            var items = new_candidates[i].split('|');
            
            html = html + '<div class="refer_candidates" id="refer_candidate_' + refer_num_candidates + '">' + "\n";
            html = html + '<input type="hidden" id="refer_candidate_social_' + refer_num_candidates + '" value="' + items[0] + '" />' + "\n";
            html = html + 'Name: <input type="text" id="refer_candidate_name_' + refer_num_candidates + '" value="' + items[1] + '" /><br/>' + "\n";
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
    var new_idx = 0;
    for (var i=0; i < refer_num_candidates; i++) {
        if (i != _idx) {
            html = html + '<div class="refer_candidates" id="refer_candidate_' + new_idx + '">' + "\n";
            html = html + '<input type="hidden" id="refer_candidate_social_' + new_idx + '" value="' + $('refer_candidate_social_' + i).value + '" />' + "\n";
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
