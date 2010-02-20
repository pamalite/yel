var selected_tab = 'li_profile';
var order_by = 'recommenders.added_on';
var order = 'desc';
var filter_by = '0';
var candidates_order_by = 'members.joined_on';
var candidates_order = 'desc';

var current_recommender_email_addr = '';
var current_recommender_name = '';
var email_addrs = new Array();

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function candidates_ascending_or_descending() {
    if (candidates_order == 'desc') {
        candidates_order = 'asc';
    } else {
        candidates_order = 'desc';
    }
}

function validate_new_recommender_form() {
    if (!isEmail($('email_addr').value)) {
        alert('The e-mail address provided is not valid.');
        return false;
    }
    
    if (isEmpty($('firstname').value)) {
        alert('Firstnames cannot be empty.');
        return false;
    }
    
    if (isEmpty($('lastname').value)) {
        alert('Lastnames cannot be empty.');
        return false;
    }
    
    var selected_count = 0;
    for (var i=0; i < $('industries').options.length; i++) {
        if ($('industries').options[i].selected) {
            selected_count++;
        }
    }
    
    if (selected_count <= 0) {
        var msg = 'Are you sure not to classify the recommender with any of the specilizations?';
        if (!confirm(msg)) {
            return false;
        }
    }
    
    return true;
}

function update_filter() {
    var params = 'id=0&action=get_filters';
    
    var uri = root + "/prs/recommenders_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            var ids = xml.getElementsByTagName('id');
            var industries = xml.getElementsByTagName('industry');
            
            var html = '<select id="recommender_filter" name="recommender_filter" onChange="refresh_recommenders();">' + "\n";
            html = html + '<option value="0">all specializations</option>' + "\n";
            html = html + '<option value="-1" disabled>&nbsp;</option>' + "\n";
            
            for (var i=0; i < ids.length; i++) {
                var id = ids[i].childNodes[0].nodeValue;
                var industry = industries[i].childNodes[0].nodeValue;
                
                if (id == filter_by) {
                    html = html + '<option value="'+ id + '" selected>' + industry + '</option>' + "\n";
                } else {
                    html = html + '<option value="'+ id + '">' + industry + '</option>' + "\n";
                }
            }
            
            html = html + '</select>' + "\n";
            
            $('recommender_filters_dropdown').set('html', html);
        },
        onRequest: function(instance) {
            set_status('Loading specilizations...');
        }
    });
    
    request.send(params);
}

function refresh_recommenders() {
    filter_by = $('recommender_filter').options[$('recommender_filter').selectedIndex].value;
    show_recommenders();
}

function show_recommenders() {
    $('div_recommenders').setStyle('display', 'block');
    $('div_recommender').setStyle('display', 'none');
    $('div_new_recommender_form').setStyle('display', 'none');
    
    var params = 'id=' + id + '&order_by=' + order_by + ' ' + order;
    params = params + '&filter_by=' + filter_by;
    
    update_filter();
    
    var uri = root + "/prs/recommenders_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading recommenders.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no recommenders at the moment.</div>';
            } else {
                var email_addrs = xml.getElementsByTagName('email_addr');
                var recommenders = xml.getElementsByTagName('recommender_name');
                var phone_nums = xml.getElementsByTagName('phone_num');
                var added_ons = xml.getElementsByTagName('formatted_added_on');
                var remarks = xml.getElementsByTagName('remarks');
                var regions = xml.getElementsByTagName('region');
                
                for (var i=0; i < email_addrs.length; i++) {
                    var id = email_addrs[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="checkbox"><input type="checkbox" onClick="sync_mailing_list(\'' + id + '\');" /></td>' + "\n";
                    html = html + '<td class="date">' + added_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    var region = '';
                    if (regions[i].childNodes.length > 0) {
                        region = regions[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="region">' + region + '</td>' + "\n";
                    
                    var phone_num = 'N/A';
                    if (phone_nums[i].childNodes.length > 0) {
                        phone_num = phone_nums[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="recommender"><a href="mailto: ' + id + '">' + recommenders[i].childNodes[0].nodeValue + '</a><br/><div class="phone_num"><strong>Tel:</strong> ' + phone_num + '<br/><strong>E-mail:</strong> ' + id + '</div></td>' + "\n";
                    
                    if (remarks[i].childNodes.length > 0) {
                        html = html + '<td class="recommender">' + remarks[i].childNodes[0].nodeValue + '</td>' + "\n";
                    } else {
                        html = html + '<td class="recommender">&nbsp;</td>' + "\n";
                    }
                    
                    html = html + '<td class="actions"><a class="no_link" onClick="show_profile(\'' + id + '\');">View Profile &amp; Candidates</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
            }
            html = html + '</table>';
            
            $('div_recommenders_list').set('html', html);
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading privileged requests...');
        }
    });
    
    request.send(params);
}

function show_current_recommender_profile() {
    show_profile(current_recommender_email_addr);
}

function show_profile(_recommender_email_addr) {
    current_recommender_email_addr = _recommender_email_addr;
    
    $('div_recommenders').setStyle('display', 'none');
    $('div_recommender').setStyle('display', 'block');
    $('div_new_recommender_form').setStyle('display', 'none');
    
    $('li_profile').setStyle('border', '1px solid #CCCCCC');
    $('li_candidates').setStyle('border', '1px solid #0000FF');
    $('div_profile').setStyle('display', 'block');
    $('div_candidates').setStyle('display', 'none');
    
    // unselect all industries
    for (var i=0; i < $('profile.industries').options.length; i++) {
        $('profile.industries').options[i].selected = false;
    }
    
    var params = 'id=' + current_recommender_email_addr + '&action=get_profile';
    
    var uri = root + "/prs/recommenders_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading recommender.');
                return false;
            } 
            
            var firstname = xml.getElementsByTagName('firstname');
            var lastname = xml.getElementsByTagName('lastname');
            var phone_num = xml.getElementsByTagName('phone_num');
            var remarks = xml.getElementsByTagName('remarks');
            var regions = xml.getElementsByTagName('region');
            var added_on = xml.getElementsByTagName('formatted_added_on');
            var industries = xml.getElementsByTagName('industry');
            
            $('profile.added_on').set('html', added_on[0].childNodes[0].nodeValue);
            $('profile.firstname').value = firstname[0].childNodes[0].nodeValue;
            $('profile.lastname').value = lastname[0].childNodes[0].nodeValue;
            
            current_recommender_name = firstname[0].childNodes[0].nodeValue + ', ' + lastname[0].childNodes[0].nodeValue;
            
            $('profile.email_addr').set('html', current_recommender_email_addr);
            
            var phone = '';
            if (phone_num[0].childNodes.length > 0) {
                phone = phone_num[0].childNodes[0].nodeValue;
            }
            $('profile.phone_num').value = phone;
            
            if (industries.length > 0) {
                for (var j=0; j < industries.length; j++) {
                    for (var i=0; i < $('profile.industries').options.length; i++) {
                        if (industries[j].childNodes[0].nodeValue == $('profile.industries').options[i].value) {
                            $('profile.industries').options[i].selected = true;
                        }
                    }
                }
            }
            
            $('profile.remarks').value = '';
            if (remarks[0].childNodes.length > 0) {
                $('profile.remarks').value = remarks[0].childNodes[0].nodeValue;
            }
            
            $('profile.region').value = '';
            if (regions[0].childNodes.length > 0) {
                $('profile.region').value = regions[0].childNodes[0].nodeValue;
            }
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading recommender...');
        }
    });
    
    request.send(params);
}

function save_profile() {
    if (isEmpty($('profile.firstname').value)) {
        alert('Firstnames cannot be empty.');
        return false;
    }
    
    if (isEmpty($('profile.lastname').value)) {
        alert('Lastnames cannot be empty.');
        return false;
    }
    
    var selected_count = 0;
    for (var i=0; i < $('profile.industries').options.length; i++) {
        if ($('profile.industries').options[i].selected) {
            selected_count++;
        }
    }
    
    if (selected_count <= 0) {
        var msg = 'Are you sure not to classify the recommender with any of the specilizations?';
        if (!confirm(msg)) {
            return false;
        }
    }
    
    var params = 'id=' + current_recommender_email_addr + '&action=update_profile';
    params = params + '&firstname=' + $('profile.firstname').value;
    params = params + '&lastname=' + $('profile.lastname').value;
    params = params + '&phone_num=' + $('profile.phone_num').value;
    params = params + '&remarks=' + $('profile.remarks').value;
    params = params + '&region=' + $('profile.region').value;
    
    if (selected_count <= 0) {
        params = params + '&industries=0';
    } else {
        var count = 0;
        var industries_str = '';
        for (var i=0; i < $('profile.industries').options.length; i++) {
            if ($('profile.industries').options[i].selected) {
                if (count == 0) {
                    industries_str = $('profile.industries').options[i].value;
                } else {
                    industries_str = industries_str + ',' + $('profile.industries').options[i].value;
                }
                
                count++;
            }
        }
        params = params + '&industries=' + industries_str;
    }
    
    var uri = root + "/prs/recommenders_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            switch (txt) {
                case '-1':
                    alert('Unable to update recommender.\n\nPlease try again later.');
                    break;
                case '-2':
                    alert('Recommender\'s industries are not added into the system.\n\nPlease try again later.');
                    show_recommenders();
                    break;
                default:
                    show_recommenders();
                    break;
            }
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Saving recp,,emder...');
        }
    });
    
    request.send(params);
}

function update_recommender_industries() {
    var params = 'id=' + current_recommender_email_addr + '&action=get_recommender_industries';
    
    var uri = root + "/prs/recommenders_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                $('recommender_industries').set('html', 'An error occured while loading industries.');
                return false;
            } 
            
            if (txt == '0') {
                $('recommender_industries').set('html', 'No industries associated to this recommender.');
            } else {
                var industries = xml.getElementsByTagName('industry');
                
                var html = '';
                for (var i=0; i < industries.length; i++) {
                    html = html + '<span class="specialization">' + industries[i].childNodes[0].nodeValue + '</span>&nbsp;&nbsp;&nbsp;';
                }
                
                $('recommender_industries').set('html', html);
            }
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading industries...');
        }
    });
    
    request.send(params);
}

function show_current_recommender_candidates() {
    show_candidates(current_recommender_email_addr);
}

function show_candidates(_recommender_email_addr) {
    current_recommender_email_addr = _recommender_email_addr;
    
    $('div_recommenders').setStyle('display', 'none');
    $('div_recommender').setStyle('display', 'block');
    $('div_new_recommender_form').setStyle('display', 'none');
    
    $('li_candidates').setStyle('border', '1px solid #CCCCCC');
    $('li_profile').setStyle('border', '1px solid #0000FF');
    $('div_profile').setStyle('display', 'none');
    $('div_candidates').setStyle('display', 'block');
    
    $('recommender_name').set('html', current_recommender_name);
    update_recommender_industries();
    
    var params = 'id=' + current_recommender_email_addr + '&action=get_candidates';
    params = params + '&order_by=' + candidates_order_by + ' ' + candidates_order;
    
    var uri = root + "/prs/recommenders_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading resumes.');
                return false;
            } 
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">No candidates recommended at the moment.</div>';
            } else {
                var ids = xml.getElementsByTagName('email_addr');
                var members = xml.getElementsByTagName('member');
                var joined_ons = xml.getElementsByTagName('formatted_joined_on');
                var phone_nums = xml.getElementsByTagName('phone_num');
                
                for (var i=0; i < ids.length; i++) {
                    var id = ids[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    
                    html = html + '<td class="date">' + joined_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    var phone_num = 'N/A';
                    if (phone_nums[i].childNodes.length > 0) {
                        phone_num = phone_nums[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="candidate"><a href="mailto: ' + id + '">' + members[i].childNodes[0].nodeValue + '</a><br/><div class="phone_num"><strong>Tel:</strong> ' + phone_num + '<br/><strong>E-mail:</strong> ' + id + '</div></td>' + "\n";
                    html = html + '<td class="actions"><a href="resumes_privileged.php?candidate=' + id + '">View Profile &amp; Resumes</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_candidates_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading candidates...');
        }
    });
    
    request.send(params);
}

function add_new_recommender() {
    if (!validate_new_recommender_form()) {
        return false;
    }
    
    var params = 'id=' + id + '&action=add_new_recommender';
    params = params + '&email_addr=' + $('email_addr').value;
    params = params + '&firstname=' + $('firstname').value;
    params = params + '&lastname=' + $('lastname').value;
    params = params + '&phone_num=' + $('phone_num').value;
    params = params + '&remarks=' + $('remarks').value;
    params = params + '&region=' + $('region').value;
        
    var industries = '';
    for (var i=0; i < $('industries').options.length; i++) {
        if ($('industries').options[i].selected) {
            if (isEmpty(industries)) {
                industries = $('industries').options[i].value;
            } else {
                industries = industries + ',' + $('industries').options[i].value;
            }
        }
    }
    params = params + '&industries=' + industries;
    
    var uri = root + "/prs/recommenders_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            switch (txt) {
                case '-1':
                    alert('Recommender already exists in the system.');
                    break;
                case '-2':
                    alert('Unable to create new candidate. No new candidate created.\n\nPlease try again later.');
                    show_recommenders();
                    break;
                case '-3':
                    alert('Everything was created successfully, except for recommender\'s industries are not added into the system.\n\nPlease update later.');
                    show_recommenders();
                    break;
                default:
                    show_recommenders();
                    break;
            }
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Saving new recommender...');
        }
    });
    
    request.send(params);
}

function show_new_recommender_form() {
    $('div_recommenders').setStyle('display', 'none');
    $('div_recommender').setStyle('display', 'none');
    $('div_new_recommender_form').setStyle('display', 'block');
}

function sync_mailing_list(_email_addr) {
    if (email_addrs.length <= 0) {
        email_addrs[0] = _email_addr;
    } else {
        var already_added = false;
        var new_list = new Array();
        var new_list_count = 0;
        for (var i=0; i < email_addrs.length; i++) {
            if (email_addrs[i] == _email_addr) {
                already_added = true;
            } else {
                new_list[new_list_count] = email_addrs[i];
                new_list_count++;
            }
        }
        
        if (!already_added) {
            new_list[new_list.length] = _email_addr;
        }
        
        email_addrs = new_list;
    }
}

function close_email_form() {
    $('div_email_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
    set_status('');
}

function show_email_form() {
    if (email_addrs.length <= 0) {
        alert('You need to select at least one recommender.');
        return;
    }
    
    $('div_blanket').setStyle('display', 'block');
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_email_form').getStyle('height'));
    var div_width = parseInt($('div_email_form').getStyle('width'));
    
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
    
    $('div_email_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_email_form').setStyle('left', ((window_width - div_width) / 2));
    
    $('div_email_form').setStyle('display', 'block');
}

function send_email_to_list() {
    if (isEmpty($('email_message').value) || isEmpty($('email_subject').value)) {
        alert('You need to enter a subject and a message to be send.');
        return;
    }
    
    var params = 'id=' + id + '&action=send_email_to_list';
    params = params + '&subject=' + $('email_subject').value;
    params = params + '&message=' + $('email_message').value;
    
    var emails = '';
    for (var i=0; i < email_addrs.length; i++) {
        if (i == 0) {
            emails = email_addrs[i];
        } else {
            emails = emails + ',' + email_addrs[i];
        }
    }
    params = params + '&emails=' + emails;
    
    var uri = root + "/prs/recommenders_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            close_email_form();
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Sending message to recommenders...');
        }
    });

    request.send(params);
}

function set_mouse_events() {
    $('li_profile').addEvent('mouseover', function() {
        $('li_profile').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_profile').addEvent('mouseout', function() {
        $('li_profile').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_candidates').addEvent('mouseover', function() {
        $('li_candidates').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_candidates').addEvent('mouseout', function() {
        $('li_candidates').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_back').addEvent('mouseover', function() {
        $('li_back').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_back').addEvent('mouseout', function() {
        $('li_back').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_back_1').addEvent('mouseover', function() {
        $('li_back_1').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_back_1').addEvent('mouseout', function() {
        $('li_back_1').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
}

function onDomReady() {
    set_root();
    list_available_industries('0');
    set_mouse_events();
    
    $('li_back').addEvent('click', show_recommenders);
    $('li_back_1').addEvent('click', show_recommenders);
    $('li_profile').addEvent('click', show_current_recommender_profile);
    $('li_candidates').addEvent('click', show_current_recommender_candidates);
    
    $('add_new_recommender').addEvent('click', show_new_recommender_form);
    $('add_new_recommender_1').addEvent('click', show_new_recommender_form);
    
    $('save').addEvent('click', save_profile);
    $('add').addEvent('click', add_new_recommender);
    
    $('sort_added_on').addEvent('click', function() {
        order_by = 'recommenders.added_on';
        ascending_or_descending();
        show_recommenders();
    });
    
    $('sort_region').addEvent('click', function() {
        order_by = 'recommenders.region';
        ascending_or_descending();
        show_recommenders();
    });
    
    $('sort_recommender').addEvent('click', function() {
        order_by = 'recommenders.lastname';
        ascending_or_descending();
        show_recommenders();
    });
    
    $('sort_joined_on').addEvent('click', function() {
        candidates_order_by = 'joined_on';
        candidates_ascending_or_descending();
        show_current_recommender_candidates();
    });
    
    $('sort_candidate').addEvent('click', function() {
        candidates_order_by = 'lastname';
        candidates_ascending_or_descending();
        show_current_recommender_candidates();
    });
    
    show_recommenders();
}

window.addEvent('domready', onDomReady);
