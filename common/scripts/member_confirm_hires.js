var order_by = 'employed_on';
var order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function show_resume_page(resume_id) {
    var popup = window.open('resume_html.php?id=' + resume_id, '', 'scrollbars');
    
    if (!popup) {
        alert('Popup blocker was detected. Please allow pop-up windows for YellowElevator.com and try again.');
    }
}

function confirm_hire(referral_id, _job, _employer) {
    var is_ok = confirm('By clicking \'OK\', you confirm that you have been employed by ' + _employer + ' for the ' + _job + ' position. Would you like to proceed?');
    if (!is_ok) {
        return false;
    }
    
    var params = 'id=' + referral_id + '&action=confirm_hire';
    
    var uri = root + "/members/confirm_hires_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while confirming job employment.');
                return false;
            }
            
            show_employed_jobs();
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Confirming job employment...');
        }
    });
    
    request.send(params);
}

function show_employed_jobs() {
    var params = 'id=' + id;
    params = params + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/members/confirm_hires_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading employed jobs.');
                return false;
            }
            
            if (txt == '0') {
                var html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">Your resume has yet to be viewed by the respective employers.</div>';
                $('div_list').set('html', html);
                set_status('');
                return false;
            }
            
            var ids = xml.getElementsByTagName('id');
            var employers = xml.getElementsByTagName('employer');
            var titles = xml.getElementsByTagName('title');
            var agreed_terms_ons = xml.getElementsByTagName('formatted_employer_agreed_terms_on');
            var acknowledged_ons = xml.getElementsByTagName('formatted_referee_acknowledged_on');
            var referrers = xml.getElementsByTagName('referrer');
            var descriptions = xml.getElementsByTagName('description');
            var resumes = xml.getElementsByTagName('resume_name');
            var resume_ids = xml.getElementsByTagName('resume');
            
            var html = '<table id="list" class="list">';
            for (var i=0; i < ids.length; i++) {
                var referral_id = ids[i];
                
                html = html + '<tr id="'+ referral_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                
                html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                html = html + '<td class="title"><a class="no_link" onClick="toggle_description(\'' + referral_id.childNodes[0].nodeValue + '\')">' + titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                html = html + '<td class="title">' + referrers[i].childNodes[0].nodeValue + '</td>' + "\n";
                html = html + '<td class="title"><a class="no_link" onClick="show_resume_page(\'' + resume_ids[i].childNodes[0].nodeValue + '\')">' + resumes[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                html = html + '<td class="date">' + acknowledged_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                
                var agreed_terms_on = '<span style="font-size: 9pt; color: #AAAAAA;">Pending...</span>';
                var enable_confirm_button = false;
                if (agreed_terms_ons[i].childNodes.length > 0) {
                    agreed_terms_on = agreed_terms_ons[i].childNodes[0].nodeValue;
                    enable_confirm_button = true;
                }
                html = html + '<td class="date">' + agreed_terms_on + '</td>' + "\n";
                
                if (enable_confirm_button) {
                    // html = html + '<td class="confirm"><input class="button" type="button" value="I\'m Employed" onClick="confirm_hire(\'' + referral_id.childNodes[0].nodeValue + '\', \'' + titles[i].childNodes[0].nodeValue + '\', \'' + employers[i].childNodes[0].nodeValue + '\');" /></td>' + "\n";
                    html = html + '<td class="confirm"><input type="image" src="../common/images/i_am_employed_btn_enabled.gif" onClick="confirm_hire(\'' + referral_id.childNodes[0].nodeValue + '\', \'' + titles[i].childNodes[0].nodeValue + '\', \'' + employers[i].childNodes[0].nodeValue + '\');" /></td>' + "\n";
                } else {
                    //html = html + '<td class="confirm"><input class="button" type="button" value="I\'m Employed" disabled /></td>' + "\n";
                    html = html + '<td class="confirm"><img src="../common/images/i_am_employed_btn_disabled.gif" /></td>' + "\n";
                }
                
                html = html + '</tr>' + "\n";
                html = html + '<tr onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                html = html + '<td colspan="7"><div class="description" id="desc_' + referral_id.childNodes[0].nodeValue + '"></div></td>' + "\n";
                html = html + '</tr>';
            }
            html = html + '</table>';
            
            $('div_list').set('html', html);
            
            for (var i=0; i < ids.length; i++) {
                var referral_id = ids[i].childNodes[0].nodeValue;
                
                $('desc_' + referral_id).set('html', descriptions[i].childNodes[0].nodeValue);
            }
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading currently employed jobs...');
        }
    });
    
    request.send(params);
}

function toggle_description(job_id) {
    if ($('desc_' + job_id).getStyle('display') == 'none') {
        $('desc_' + job_id).setStyle('display', 'block');
    } else {
        $('desc_' + job_id).setStyle('display', 'none');
    }
}

function toggle_banner() {
    var height = $('div_banner').getStyle('height');
    var params = 'id=' + id + '&action=set_hide_banner';
    
    if (parseInt(height) >= 100) {
        $('hide_show_label').set('html', 'Show');
        $('div_banner').tween('height', '15px');
        params = params + '&hide=1';
    } else {
        $('hide_show_label').set('html', 'Hide');
        $('div_banner').tween('height', '270px');
        params = params + '&hide=0';
    }
    
    var uri = root + "/members/confirm_hires_action.php";
    var request = new Request({
        url: uri,
        method: 'post'
    });
    
    request.send(params);
}

function hide_show_banner() {
    var params = 'id=' + id + '&action=get_hide_banner';
    
    var uri = root + "/members/confirm_hires_action.php";
    var request = new Request({
        url: uri,
        method: 'post', 
        onSuccess: function(txt, xml) {
            if (txt == '1') {
                $('hide_show_label').set('html', 'Show');
                $('div_banner').setStyle('height', '15px');
            } else {
                $('hide_show_label').set('html', 'Hide');
                $('div_banner').setStyle('height', '270px');
            }
        }
    });
    
    request.send(params);
}

function onDomReady() {
    initialize_page();
    get_employers_for_mini();
    get_industries_for_mini();
    get_referrals_count();
    get_requests_count();
    get_jobs_employed_count();
    set_mini_keywords();
    
    hide_show_banner();
    
    $('sort_employer').addEvent('click', function() {
        order_by = 'employer';
        ascending_or_descending();
        show_employed_jobs();
    });
    
    $('sort_title').addEvent('click', function() {
        order_by = 'title';
        ascending_or_descending();
        show_employed_jobs();
    });
    
    $('sort_agreed_terms_on').addEvent('click', function() {
        order_by = 'employer_agreed_terms_on';
        ascending_or_descending();
        show_employed_jobs();
    });
    
    $('sort_acknowledged_on').addEvent('click', function() {
        order_by = 'referee_acknowledged_on';
        ascending_or_descending();
        show_employed_jobs();
    });
    
    $('sort_member').addEvent('click', function() {
        order_by = 'referrer';
        ascending_or_descending();
        show_employed_jobs();
    });
    
    $('sort_resume').addEvent('click', function() {
        order_by = 'resume_name';
        ascending_or_descending();
        show_employed_jobs();
    });
    
    show_employed_jobs();
    
    var suggest_url = root + '/common/php/search_suggest.php';
    new Autocompleter.Ajax.Json('mini_keywords', suggest_url, {
        'postVar': 'keywords',
        'minLength' : 1,
        'overflow' : true,
        'delay' : 50
    });
}

window.addEvent('domready', onDomReady);
