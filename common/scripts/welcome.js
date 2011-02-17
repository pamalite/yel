var employers_index = 0;

function verify() {
    if ($('industry').options[$('industry').selectedIndex].value == 0 && 
        $('employer').options[$('employer').selectedIndex].value == 0 && 
        $('country').options[$('country').selectedIndex].value == '' && 
        ($('keywords').value == 'Job title or keywords' || $('keywords').value == '')) {
        alert('Please select an employer, industry/sub-industry or enter the job title/keywords in order to do a search. You may choose to do all if you wish to do a more specific search.');
        return false;
    }
    
    if ($('keywords').value == 'Job title or keywords') {
        $('keywords').value = '';
    }
    
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
}

function onDomReady() {
    set_employers_mouse_events();
}

function onLoaded() {
    initialize_page();
    
    $('connect').addEvent('mouseover', function(e) {
        $('connect').src = root + '/common/images/connect_with_recruitment_hover.jpg';
    });
    
    $('connect').addEvent('mouseout', function(e) {
        $('connect').src = root + '/common/images/connect_with_recruitment.jpg';
    });
    
    $('cash_bonus').addEvent('mouseover', function(e) {
        $('cash_bonus').src = root + '/common/images/get_cash_bonus_hover.jpg';
    });
    
    $('cash_bonus').addEvent('mouseout', function(e) {
        $('cash_bonus').src = root + '/common/images/get_cash_bonus.jpg';
    });
    
    $('7_years').addEvent('mouseover', function(e) {
        $('7_years').src = root + '/common/images/7years_work_experience_hover.jpg';
    });
    
    $('7_years').addEvent('mouseout', function(e) {
        $('7_years').src = root + '/common/images/7years_work_experience.jpg';
    });
    
    $('get_connected_1').addEvent('mouseover', function(e) {
        $('get_connected_1').src = root + '/common/images/get_connected_but_hover.jpg';
    });
    
    $('get_connected_1').addEvent('mouseout', function(e) {
        $('get_connected_1').src = root + '/common/images/get_connected_but.jpg';
    });
    
    $('get_connected_2').addEvent('mouseover', function(e) {
        $('get_connected_2').src = root + '/common/images/get_connected_but_hover.jpg';
    });
    
    $('get_connected_2').addEvent('mouseout', function(e) {
        $('get_connected_2').src = root + '/common/images/get_connected_but.jpg';
    });
}

window.addEvent('domready', onDomReady);
window.addEvent('load', onLoaded);
