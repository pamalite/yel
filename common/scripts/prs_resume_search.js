var order_by = 'relevance';
var order = 'desc';
//var offset = 0;
var current_page = 1;
var total_pages = 1;
var filter_by = '0';

var mailing_lists_list = new ListBox('mailing_lists', 'mailing_lists_list', false);

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function show_mailing_lists() {
    $('mailing_lists').set('html', '');
    
    var params = 'id=0&action=get_mailing_lists';
    
    var uri = root + '/prs/search_resume_action.php';
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            mailing_lists_list.clear();
            
            if (txt == '0') {
                $('mailing_lists').set('html', 'No mailing list found.');
                $('to_existing').disabled = true;
                $('to_new').checked = true;
                return;
            } else {
                $('to_existing').disabled = false;
                $('to_existing').checked = true;
            }
            
            var ids = xml.getElementsByTagName('id');
            var labels = xml.getElementsByTagName('label');
            
            for (var i=0; i < ids.length; i++) {
                mailing_lists_list.add_item(labels[i].childNodes[0].nodeValue, ids[i].childNodes[0].nodeValue);
            }
            
            mailing_lists_list.show();
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading mailing lists...');
        }
    });
    
    request.send(params);
}

function show_pagination_dropdown() {
    var html = '<select id="pagination_dropdown" name="page" onChange="go_to_page();">' + "\n";
    for (var i = 1; i <= total_pages; i++) {
        if (i == current_page) {
            html = html + '<option value="' + i + '" selected>' + i + '</option>' + "\n";
        } else {
            html = html + '<option value="' + i + '">' + i + '</option>' + "\n";
        }
    }
    html = html + '</select>' + "\n";
    
    $('current_page').set('html', html);
    $('current_page_1').set('html', html);
}

function show_limit_dropdown() {
    var html = '<select id="limit_dropdown" name="limit" onChange="filter_resumes();">' + "\n";
    for (var i = 5; i <= 50; i += 5) {
        if (i == limit) {
            html = html + '<option value="' + i + '" selected>' + i + '</option>' + "\n";
        } else {
            html = html + '<option value="' + i + '">' + i + '</option>' + "\n";
        }
    }
    html = html + '</select>' + "\n";
    
    $('filter_limit_dropdown').set('html', html);
}

function filter_resumes() {
    // $('industry_dropdown').selectedIndex = industry;
    // $('country_dropdown').selectedIndex = country_code;
    
    industry = $('industry_dropdown').options[$('industry_dropdown').selectedIndex].value;
    country_code = $('country_dropdown').options[$('country_dropdown').selectedIndex].value;
    limit = $('limit_dropdown').options[$('limit_dropdown').selectedIndex].value;
    
    offset = 0;
    show_resumes();
}

function go_to_last_page() {
    offset = (parseInt(total_pages) - 1) * parseInt(limit);
    show_resumes();
}

function go_to_first_page() {
    offset = 0;
    show_resumes();
}

function go_to_next_page() {
    offset = parseInt(offset) + parseInt(limit);
    show_resumes();
}

function go_to_previous_page() {
    offset = parseInt(offset) - parseInt(limit);
    show_resumes();
}

function go_to_page() {
    offset = (parseInt($('pagination_dropdown').options[$('pagination_dropdown').selectedIndex].value) - 1) * parseInt(limit);
    show_resumes();
}

function show_resume_page(resume_id) {
    var popup = window.open('../employees/resume.php?id=' + resume_id, '', 'scrollbars');
    
    if (!popup) {
        alert('Popup blocker was detected. Please allow pop-up windows for YellowElevator.com and try again.');
    }
}

function list_industries_filter(_industry) {
    var params = 'id=0&action=get_available_industries';
    
    var uri = root + "/prs/search_resume_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            var ids = xml.getElementsByTagName('id');
            var industries = xml.getElementsByTagName('industry_name');
            
            var html = '<select id="industry_dropdown" name="industry_dropdown" onChange="filter_resumes();">' + "\n";
            
            if (_industry == '0' || isEmpty(_industry)) {
                html = html + '<option value="0" selected>all specializations</option>' + "\n";
            } else {
                html = html + '<option value="0">all specializations</option>' + "\n";
            }
            
            html = html + '<option value="-1" disabled>&nbsp;</option>' + "\n";
            for (var i=0; i < ids.length; i++) {
                var id = ids[i].childNodes[0].nodeValue;
                var industry = industries[i].childNodes[0].nodeValue;
                
                if (id == _industry) {
                    html = html + '<option value="'+ id + '" selected>' + industry + '</option>' + "\n";
                } else {
                    html = html + '<option value="'+ id + '">' + industry + '</option>' + "\n";
                }
            }
            
            html = html + '</select>' + "\n";
            
            $('filter_industry_dropdown').set('html', html);
        },
        onRequest: function(instance) {
            $('filter_industry_dropdown').set('html', 'Loading specializations...');
        }
    });
    
    request.send(params);
}

function list_countries_filter(_country) {
    var params = 'id=0&action=get_available_countries';
    
    var uri = root + "/prs/search_resume_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            var ids = xml.getElementsByTagName('country_code');
            var countries = xml.getElementsByTagName('country_name');
            
            var html = '<select id="country_dropdown" name="country_dropdown" onChange="filter_resumes();">' + "\n";
            
            if (isEmpty(_country)) {
                html = html + '<option value="" selected>all countries</option>' + "\n";
            } else {
                html = html + '<option value="">all countries</option>' + "\n";
            }
            
            html = html + '<option value="-1" disabled>&nbsp;</option>' + "\n";
            for (var i=0; i < ids.length; i++) {
                var id = ids[i].childNodes[0].nodeValue;
                var country = countries[i].childNodes[0].nodeValue;
                
                if (id == _country) {
                    html = html + '<option value="'+ id + '" selected>' + country + '</option>' + "\n";
                } else {
                    html = html + '<option value="'+ id + '">' + country + '</option>' + "\n";
                }
            }
            
            html = html + '</select>' + "\n";
            
            $('filter_country_dropdown').set('html', html);
        },
        onRequest: function(instance) {
            $('filter_country_dropdown').set('html', 'Loading countries...');
        }
    });
    
    request.send(params);
}

function show_resumes() {
    $('div_search_results').setStyle('display', 'block');
    
    var params = 'industry=' + industry;
    params = params + '&country_code=' + country_code;
    params = params + '&keywords=' + keywords;
    if (use_exact) {
        params = params + '&use_exact=1';
    }
    params = params + '&offset=' + offset;
    params = params + '&limit=' + limit;
    params = params + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/prs/search_resume_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko1' || txt == '01') {
                txt = txt.substr(0, (txt.length - 1));
                $('use_exact').checked = true;
            } else {
                $('use_exact').checked = false;
            }
            
            if (txt == 'ko') {
                set_status('An error occured while searching resumes.');
                return false;
            }
            
            if (txt == '0') {
                set_status('No job resume with the criteria.');
                $('div_list').set('html', '');
                $('current_page').set('html', '0');
                $('total_page').set('html', '0');
                $('current_page_1').set('html', '0');
                $('total_page_1').set('html', '0');
                show_limit_dropdown();
                return false;
            }
            
            var ids = xml.getElementsByTagName('resume_id');
            var matches = xml.getElementsByTagName('match_percentage');
            var members = xml.getElementsByTagName('member');
            var primary_industries = xml.getElementsByTagName('prime_industry');
            var secondary_industries = xml.getElementsByTagName('second_industry');
            var countries = xml.getElementsByTagName('country');
            var zips = xml.getElementsByTagName('zip');
            var email_addrs = xml.getElementsByTagName('email_addr');
            var phone_nums = xml.getElementsByTagName('phone_num');
            var added_bys = xml.getElementsByTagName('added_by');
            var joined_ons = xml.getElementsByTagName('formatted_joined_on');
            var labels = xml.getElementsByTagName('resume_label');
            var file_hashes = xml.getElementsByTagName('file_hash');
            var file_names = xml.getElementsByTagName('file_name');
            var total_results = xml.getElementsByTagName('total_results');
            var current_pages = xml.getElementsByTagName('current_page');
            var changed_country_code = xml.getElementsByTagName('changed_country_code');
            
            var total = total_results[0].childNodes[0].nodeValue;
            total_pages = Math.ceil(total / limit);
            current_page = current_pages[0].childNodes[0].nodeValue;
            
            var next_page_button_html = '';
            if (current_page < total_pages) {
                next_page_button_html = '<a class="no_link" onClick="go_to_next_page();"><img src="' + root + '/common/images/next_page.jpg" style="vertical-align: middle;" onMouseOver="this.src=root + \'/common/images/next_page_hover.jpg\'" onMouseOut="this.src=root + \'/common/images/next_page.jpg\'" /></a>&nbsp;&nbsp;';
                next_page_button_html = next_page_button_html + '<a class="no_link" onClick="go_to_last_page();"><img src="' + root + '/common/images/last_page.jpg" style="vertical-align: middle;" onMouseOver="this.src=root + \'/common/images/last_page_hover.jpg\'" onMouseOut="this.src=root + \'/common/images/last_page.jpg\'" /></a>';
            }
            
            var previous_page_button_html = '';
            if (current_page > 1) {
                previous_page_button_html = '<a class="no_link" onClick="go_to_first_page();"><img src="' + root + '/common/images/first_page.jpg" style="vertical-align: middle;" onMouseOver="this.src=root + \'/common/images/first_page_hover.jpg\'" onMouseOut="this.src=root + \'/common/images/first_page.jpg\'" /></a>&nbsp;&nbsp;';
                previous_page_button_html = previous_page_button_html +  '<a class="no_link" onClick="go_to_previous_page();"><img src="' + root + '/common/images/previous_page.jpg" style="vertical-align: middle;" onMouseOver="this.src=root + \'/common/images/previous_page_hover.jpg\'" onMouseOut="this.src=root + \'/common/images/previous_page.jpg\'" /></a>';
            }
            
            var html = '<table id="list" class="list">';
            for (var i=0; i < ids.length; i++) {
                var resume_id = ids[i].childNodes[0].nodeValue;
                
                html = html + '<tr id="'+ resume_id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                html = html + '<td class="match_percentage"><img src="' + root + '/common/images/match_bar.jpg" style="height: 4px; width: ' + Math.floor(matches[i].childNodes[0].nodeValue / 100 * 50) + 'px; vertical-align: middle;" /></td>' + "\n";
                html = html + '<td class="date">' + joined_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                
                var link = 'resumes_privileged.php?candidate=' + email_addrs[i].childNodes[0].nodeValue;
                if (added_bys[i].childNodes[0].nodeValue == '-1') {
                    link = 'resumes.php?candidate=' + email_addrs[i].childNodes[0].nodeValue;
                }
                html = html + '<td class="member"><a href="' + link + '">' + members[i].childNodes[0].nodeValue + '</a><br/><div class="phone_num"><strong>Tel:</strong> ' + phone_nums[i].childNodes[0].nodeValue + '<br/><strong>E-mail:</strong> ' + email_addrs[i].childNodes[0].nodeValue + '</div></td>' + "\n";
                html = html + '<td class="industry">' + primary_industries[i].childNodes[0].nodeValue + '</td>' + "\n";
                html = html + '<td class="industry">' + secondary_industries[i].childNodes[0].nodeValue + '</td>' + "\n";
                
                if (file_hashes[i].childNodes.length > 0) {
                    html = html + '<td class="title"><span class="reupload"><a href="resume.php?id=' + resume_id + '&member=' + email_addrs[i].childNodes[0].nodeValue + '">' + labels[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                } else {
                    html = html + '<td class="label"><a class="no_link" onClick="show_resume_page(\'' + resume_id + '\')">' + labels[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                }
                
                html = html + '<td class="country">' + countries[i].childNodes[0].nodeValue + '</td>' + "\n";
                html = html + '<td class="country">' + zips[i].childNodes[0].nodeValue + '</td>' + "\n";
                
                html = html + '<td class="action"><a class="no_link" onClick="show_email_add_form(\'' + email_addrs[i].childNodes[0].nodeValue + '\');">+</a></td>' + "\n";
                
                html = html + '</tr>' + "\n";
            }
            html = html + '</table>';
            
            $('div_list').set('html', html);
            $('current_page').set('html', current_page);
            $('total_page').set('html', total_pages);
            $('current_page_1').set('html', current_page);
            $('total_page_1').set('html', total_pages);
            $('next_page').set('html', next_page_button_html);
            $('next_page_1').set('html', next_page_button_html);
            $('previous_page').set('html', previous_page_button_html);
            $('previous_page_1').set('html', previous_page_button_html);
            
            show_pagination_dropdown();
            show_limit_dropdown();
            set_status('');
            
            if (changed_country_code[0].childNodes[0].nodeValue == '1') {
                country_code = '';
                list_countries_filter(country_code);
            }
        },
        onRequest: function(instance) {
            set_status('Searching resumes...');
        }
    });
    
    request.send(params);
    
    list_industries_filter(industry);
    list_countries_filter(country_code);
}

function add_email_to_list() {
    var params = 'employee=' + id + '&action=save_to_mailing_list';
    params = params + '&candidate=' + $('candidate_email').get('html');
    
    if ($('to_new').checked) {
        params = params + '&id=new&label=' + $('new_list_label').value;
    } else {
        if (isEmpty(mailing_lists_list.selected_value)) {
            alert('Please select one mailing list to be added to.');
            return;
        }
        
        params = params + '&id=' + mailing_lists_list.selected_value;
    }
    
    var uri = root + '/prs/search_resume_action.php';
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('Cannot create mailing list. Please try again later.');
                close_email_add_form();
                set_status('');
                return false;
            } else if (txt == '-1') {
                alert('Mailing list was successfully created, but an error occurred while adding the candidate to the newly created list. Please try again later.');
                close_email_add_form();
                set_status('');
                return false;
            }
            
            close_email_add_form();
            set_status('Mailing list was successfully saved.');
        },
        onRequest: function(instance) {
            set_status('Saving mailing list...');
        }
    });
    
    request.send(params);
}

function close_email_add_form() {
    $('div_email_add_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
    set_status('');
}

function show_email_add_form(_candidate_email) {
    $('div_blanket').setStyle('display', 'block');
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_email_add_form').getStyle('height'));
    var div_width = parseInt($('div_email_add_form').getStyle('width'));
    
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
    
    $('div_email_add_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_email_add_form').setStyle('left', ((window_width - div_width) / 2));
    
    $('candidate_email').set('html', _candidate_email);
    
    $('div_email_add_form').setStyle('display', 'block');
    show_mailing_lists();
}

function onDomReady() {
    set_root();
    list_available_industries(industry);
    set_mini_keywords(true);
    
    if (!isEmpty(keywords)) {
        $('mini_keywords').value = keywords;
    }
    
    $('sort_match_percentage').addEvent('click', function() {
        order_by = 'relevance';
        ascending_or_descending();
        show_resumes();
    });
    
    $('sort_primary_industry').addEvent('click', function() {
        order_by = 'prime_industry';
        ascending_or_descending();
        show_resumes();
    });
    
    $('sort_secondary_industry').addEvent('click', function() {
        order_by = 'second_industry';
        ascending_or_descending();
        show_resumes();
    });
    
    $('sort_member').addEvent('click', function() {
        order_by = 'members.lastname';
        ascending_or_descending();
        show_resumes();
    });
    
    $('sort_joined_on').addEvent('click', function() {
        order_by = 'members.joined_on';
        ascending_or_descending();
        show_resumes();
    });
    
    $('sort_country').addEvent('click', function() {
        order_by = 'countries.country';
        ascending_or_descending();
        show_resumes();
    });
    
    $('sort_zip').addEvent('click', function() {
        order_by = 'members.zip';
        ascending_or_descending();
        show_resumes();
    });
    
    show_resumes();
    
    // var suggest_url = root + '/common/php/search_suggest.php';
    // new Autocompleter.Ajax.Json('mini_keywords', suggest_url, {
    //     'postVar': 'keywords',
    //     'minLength' : 1,
    //     'overflow' : true,
    //     'delay' : 50
    // });
}

window.addEvent('domready', onDomReady);
