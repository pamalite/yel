var order_by = 'member_saved_jobs.saved_on';
var order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function toggle_description(job_id) {
    if ($('description_' + job_id).getStyle('display') == 'none') {
        $('description_' + job_id).setStyle('display', 'block');
    } else {
        $('description_' + job_id).setStyle('display', 'none');
    }
}

function remove_jobs() {
    var inputs = $('list').getElementsByTagName('input');
    var payload = '<jobs>' + "\n";
    var count = 0;
    
    for(i=0; i < inputs.length; i++) {
        var attributes = inputs[i].attributes;
        if (attributes.getNamedItem('type').value == 'checkbox') {
            if (inputs[i].checked) {
                payload = payload + '<id>' + inputs[i].id + '</id>' + "\n";
                count++;
            }
        }
    }
    
    payload = payload + '</jobs>';
    
    if (count <= 0) {
        set_status('Please select at least one job.');
        return false;
    }
    
    var proceed = confirm('Are you sure to remove the selected jobs?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=' + id;
    params = params + '&action=remove_from_saved_jobs';
    params = params + '&payload=' + payload;

    var uri = root + "/members/saved_jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while closing selected jobs.');
                return false;
            }
            
            for (i=0; i < inputs.length; i++) {
                var attributes = inputs[i].attributes;
                if (attributes.getNamedItem('type').value == 'checkbox') {
                    if (inputs[i].checked) {
                        $(inputs[i].id).setStyle('display', 'none');
                        $('desc_' + inputs[i].id).setStyle('display', 'none');
                    }
                }
            }
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading currently saved jobs...');
        }
    });
    
    request.send(params);
}

function show_saved_jobs() {
    var params = 'id=' + id + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/members/saved_jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading saved jobs.');
                return false;
            }
            
            var has_saved_jobs = false;
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">You have no saved jobs at the moment. You may search jobs from the <a href="' + root + '/index.php">main</a> page, or from the search field on top, and save them here to be reivewed later.</div>';
            } else {
                var ids = xml.getElementsByTagName('id');
                var industries = xml.getElementsByTagName('industry');
                var employers = xml.getElementsByTagName('employer');
                var titles = xml.getElementsByTagName('title');
                //var descriptions = xml.getElementsByTagName('description');
                var created_ons = xml.getElementsByTagName('formatted_created_on');
                var expire_ons = xml.getElementsByTagName('formatted_expire_on');
                var saved_ons = xml.getElementsByTagName('formatted_saved_on');
            
                for (i=0; i < ids.length; i++) {
                    var job_id = ids[i];
                
                    html = html + '<tr id="'+ job_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="checkbox"><input type="checkbox" id="'+ job_id.childNodes[0].nodeValue + '" /></td>' + "\n";
                    html = html + '<td class="industry">' + industries[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title"><a href="#" onClick="toggle_description(\'' + job_id.childNodes[0].nodeValue + '\')">' + titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="date">' + created_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + expire_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + saved_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '</tr>' + "\n";
                    html = html + '<tr id="desc_' + job_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td colspan="8"><div class="description" id="description_' + job_id.childNodes[0].nodeValue + '"></div></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
                
                has_saved_jobs = true;
            }
            
            $('div_list').set('html', html);
            
            if (has_saved_jobs) {
                var ids = xml.getElementsByTagName('id');
                var descriptions = xml.getElementsByTagName('description');
                
                for (i=0; i < ids.length; i++) {
                    var job_id = ids[i].childNodes[0].nodeValue;
                    
                    $('description_' + job_id).set('html', descriptions[i].childNodes[0].nodeValue);
                }
            }
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading saved jobs...');
        }
    });
    
    request.send(params);
}

function select_all_jobs() {
    var inputs = $('list').getElementsByTagName('input');
    
    if ($('close_all').checked) {
        for (i=0; i < inputs.length; i++) {
            var attributes = inputs[i].attributes;
            if (attributes.getNamedItem('type').value == 'checkbox') {
                inputs[i].checked = true;
            }
        }
    } else {
        for (i=0; i < inputs.length; i++) {
            var attributes = inputs[i].attributes;
            if (attributes.getNamedItem('type').value == 'checkbox') {
                inputs[i].checked = false;
            }
        }
    }
}

function set_mouse_events() {
    /*$('li_open').addEvent('mouseover', function() {
        $('li_open').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_open').addEvent('mouseout', function() {
        $('li_open').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });*/
}

function onDomReady() {
    set_root();
    get_employers_for_mini();
    get_industries_for_mini();
    set_mini_keywords();
    get_referrals_count();
    get_requests_count();
    get_jobs_employed_count();
    set_mouse_events();
    
    $('remove_jobs').addEvent('click', remove_jobs);
    $('remove_jobs_1').addEvent('click', remove_jobs);
    $('close_all').addEvent('click', select_all_jobs);
    
    $('sort_industry').addEvent('click', function() {
        order_by = 'industries.industry';
        ascending_or_descending();
        show_saved_jobs();
    });
    
    $('sort_title').addEvent('click', function() {
        order_by = 'title';
        ascending_or_descending();
        show_saved_jobs();
    });
    
    $('sort_employer').addEvent('click', function() {
        order_by = 'employer';
        ascending_or_descending();
        show_saved_jobs();
    });
    
    $('sort_created_on').addEvent('click', function() {
        order_by = 'jobs.created_on';
        ascending_or_descending();
        show_saved_jobs();
    });
    
    $('sort_expire_on').addEvent('click', function() {
        order_by = 'jobs.expire_on';
        ascending_or_descending();
        show_saved_jobs();
    });
    
    $('sort_saved_on').addEvent('click', function() {
        order_by = 'member_saved_jobs.saved_on';
        ascending_or_descending();
        show_saved_jobs();
    });
    
    show_saved_jobs();
    
    var suggest_url = root + '/common/php/search_suggest.php';
    new Autocompleter.Ajax.Json('mini_keywords', suggest_url, {
        'postVar': 'keywords',
        'minLength' : 1,
        'overflow' : true,
        'delay' : 50
    });
}

window.addEvent('domready', onDomReady);
