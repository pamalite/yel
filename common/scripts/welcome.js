var employers_index = 0;

function verify() {
    if ($('industry').options[$('industry').selectedIndex].value == 0 && 
        $('employer').options[$('employer').selectedIndex].value == 0 && 
        ($('keywords').value == 'Job title or keywords' || $('keywords').value == '')) {
        alert('Please select an employer, industry/sub-industry or enter the job title/keywords in order to do a search. You may choose to do all if you wish to do a more specific search.');
        return false;
    }
    
    if ($('keywords').value == 'Job title or keywords') {
        $('keywords').value = '';
    }
    
    return true;
}

function drop_contact_now() {
    if ($('company').value == '' || $('company').value == '-') {
        alert('You will need to provide the name of your company.');
        return false;
    } 
    
    if ($('phone').value == '' && $('email').value == '') {
        alert('You will need to provide at least a way to contact you. \n\n Perhaps an e-mail address or telephone number?');
        return false;
    }
    
    if ($('email').value != '') {
        if (!isEmail($('email').value)) {
            alert('Thank you for providing an email. However, it seems like the email address is incorrect. \n\n Please try again.');
            return false;
        }
    }
    
    if ($('contact').value == '') {
        var is_fine = confirm("Not to be rude, perhaps it is fine not to address you when we contact you?");
        if (!is_fine) {
            return false;
        }
    }
    
    var company = $('company').value;
    var phone = $('phone').value;
    var email = $('email').value;
    var contact = $('contact').value;
    var uri = root + "/common/php/drop_contact.php";
    var params = 'company=' + company + '&phone=' + phone + '&email=' + email + '&contact=' + contact;
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == 'ok') {
                alert('Great news! We have successfully received your contact and you will hear from us shortly.');
                close_contact_drop_form();
            } else {
                alert('Somehow your contact drop is not working. Perhaps you should try again later.');
            }
        }
    });
    
    request.send(params);
    
    return false;
}

function close_contact_drop_form() {
    $('div_contact_drop_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_contact_drop_form() {
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_contact_drop_form').getStyle('height'));
    var div_width = parseInt($('div_contact_drop_form').getStyle('width'));
    
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
    
    $('div_contact_drop_form').setStyle('top', ((window_height - div_height) / 5));
    $('div_contact_drop_form').setStyle('left', ((window_width - div_width) / 2));
    
    $('div_blanket').setStyle('display', 'block');
    $('div_contact_drop_form').setStyle('display', 'block');
    
    return true;
}

function set_employers_mouse_events() {
    var employers = new Array();
    var number_of_tabs = 0;
    
    for (var i=0; i < $('employer_tabs').childNodes.length; i++) {
        if ($('employer_tabs').childNodes[i].nodeName == 'DIV') {
            number_of_tabs++;
        }
    }
    
    for (var i=0; i < number_of_tabs; i++) {
        var tab = 'employers_' + i;
        employers[i] = new Fx.Tween(tab);
        
        if (i > 0) {
            employers[i].set('display', 'none');
        }
    }
    
    $('toggle_right').addEvent('click', function(e) {
        e.stop();
        
        employers[employers_index].start('opacity', '0');
        employers[employers_index].set('display', 'none');
        
        employers_index++;
        if (employers_index >= number_of_tabs) {
            employers_index = 0;
        }
        
        employers[employers_index].set('display', 'block');
        employers[employers_index].set('opacity', '0');
        employers[employers_index].start('opacity', '1');
    });
    
    $('toggle_left').addEvent('click', function(e) {
        e.stop();
        
        employers[employers_index].start('opacity', '0');
        employers[employers_index].set('display', 'none');
        
        employers_index--;
        if (employers_index < 0) {
            employers_index = number_of_tabs - 1;
        }
        
        employers[employers_index].set('display', 'block');
        employers[employers_index].set('opacity', '0');
        employers[employers_index].start('opacity', '1');
    });
    
    /*$$('.top_employers').addEvents({
        'mouseenter': function() {
            $('td_toggle_left').tween('opacity', '1');
            $('td_toggle_right').tween('opacity', '1');
        },
        'mouseleave': function() {
            $('td_toggle_left').tween('opacity', '0');
            $('td_toggle_right').tween('opacity', '0');
        }
    });*/
}

function show_tour() {
    var popup = window.open('tour/index.php', '', 'width=500, height=400, scrollbars');
    if (!popup) {
        alert('Popup blocker was detected. Please allow pop-up windows for YellowElevator.com and try again.');
    }
}

function onDomReady() {
    initialize_page();
    get_employers();
    get_industries();
    get_potential_rewards();
    set_employers_mouse_events();
    //get_job_count();
    //show_industries_in('industries');
    
    $('keywords').addEvent('focus', function() {
        if ($('keywords').value == 'Job title or keywords') { 
            $('keywords').value = ''; 
        }
    });
    
    $('keywords').addEvent('blur', function() {
        if ($('keywords').value == '') { 
            $('keywords').value = 'Job title or keywords';
        }
    });
    
    //this.addEvent('resize', show_dimensions);
    //show_dimensions();
}

window.addEvent('domready', onDomReady);
