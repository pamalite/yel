function stop_refer(_success) {
    var result = '';
    $('refer_progress').setStyle('display', 'none');
    $('refer_form').setStyle('display', 'block');
    if (_success == 1) {
        close_window('refer_window');
    } else {
        alert('An error occured while submitting your request. The following may trigger the error:' + "\n\n1. You have referred the same candidate to the same job before.\n2. The resume you uploaded exceeds 1MB and is not of DOC, PDF, TXT and HTML format.");
        return false;
    }
}

function stop_apply(_success) {
    var result = '';
    $('apply_progress').setStyle('display', 'none');
    $('apply_form').setStyle('display', 'block');
    if (_success == 1) {
        close_window('apply_window');
    } else {
        alert('An error occured while submitting your request. The following may trigger the error:' + "\n\n1. You have applied for the same job before.\n2. The resume you uploaded exceeds 1MB and is not of DOC, PDF, TXT and HTML format.");
        return false;
    }
}

function close_refer_popup(_proceed_refer) {
    if (_proceed_refer) {
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
        
        $('refer_form').submit();
        $('refer_progress').setStyle('display', 'block');
        $('refer_form').setStyle('display', 'none');
        
        return true;
    }
    close_window('refer_window');
}

function show_refer_popup() {
    show_window('refer_window');
    window.scrollTo(0, 0);
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
        
        $('apply_form').submit();
        $('apply_progress').setStyle('display', 'block');
        $('apply_form').setStyle('display', 'none');
        
        return true;
    }
    close_window('apply_window');
}

function show_apply_popup() {
    show_window('apply_window');
    window.scrollTo(0, 0);
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
}

window.addEvent('domready', onDomReady);
