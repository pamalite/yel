var selected_tab = 'li_profile';
var order_by = 'recommenders.added_on';
var order = 'desc';
var filter_by = '0';
var candidates_order_by = 'members.joined_on';
var candidates_order = 'desc';

var current_recommender_email_addr = '';
var current_recommender_name = '';

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
                
                for (var i=0; i < email_addrs.length; i++) {
                    var id = email_addrs[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="date">' + added_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    var phone_num = 'N/A';
                    if (phone_nums[i].childNodes.length > 0) {
                        phone_num = phone_nums[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="recommender"><a href="mailto: ' + id + '">' + recommenders[i].childNodes[0].nodeValue + '</a><br/><div class="phone_num"><strong>Tel:</strong> ' + phone_num + '<br/><strong>E-mail:</strong> ' + id + '</div></td>' + "\n";
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

function show_current_candidate_resumes() {
    show_resumes(current_member_email_addr);
}

function show_resumes(_member_email_addr) {
    current_member_email_addr = _member_email_addr;
    
    $('div_candidates').setStyle('display', 'none');
    $('div_candidate').setStyle('display', 'block');
    $('div_new_member_form').setStyle('display', 'none');
    $('div_upload_resume_form').setStyle('display', 'none');
    
    $('li_resumes').setStyle('border', '1px solid #CCCCCC');
    $('li_profile').setStyle('border', '1px solid #0000FF');
    $('div_profile').setStyle('display', 'none');
    $('div_resumes').setStyle('display', 'block');
    
    var params = 'id=' + current_member_email_addr + '&action=get_resumes';
    params = params + '&order_by=' + resumes_order_by + ' ' + resumes_order;
    
    var uri = root + "/prs/resumes_privileged_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading resumes.');
                return false;
            } 
            
            var ids = xml.getElementsByTagName('id');
            var privates = xml.getElementsByTagName('private');
            var labels = xml.getElementsByTagName('name');
            var modified_ons = xml.getElementsByTagName('modified_date');
            var file_hashes = xml.getElementsByTagName('file_hash');
            var file_names = xml.getElementsByTagName('file_name');
            
            var html = '<table id="list" class="list">';
            if (ids.length <= 0) {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">Please click on the \"Upload Resume\" to upload resume.</div>';
            } else {
                for (var i=0; i < ids.length; i++) {
                    var resume_id = ids[i];
                    
                    html = html + '<tr id="'+ resume_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    
                    if (privates[i].childNodes[0].nodeValue == 'N') {
                        html = html + '<td class="private">&nbsp;</td>' + "\n";
                    } else {
                        html = html + '<td class="private">Private</td>' + "\n";
                    }
                    
                    html = html + '<td class="date">' + modified_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    if (file_hashes[i].childNodes.length > 0) {
                        html = html + '<td class="title"><span class="reupload"><a class="no_link" onClick="show_upload_resume_form(' + resume_id.childNodes[0].nodeValue + ');">Update File</a></span>&nbsp;<a href="resume.php?id=' + resume_id.childNodes[0].nodeValue + '&member=' + current_member_email_addr + '">' + labels[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    } else {
                        html = html + '<td class="title"><a class="no_link" onClick="show_resume_page(\'' + resume_id.childNodes[0].nodeValue + '\')">' + labels[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    }
                    
                    html = html + '<td class="actions"><a class="no_link" onClick="show_refer_now_form(\'' + resume_id.childNodes[0].nodeValue + '\')">Refer Now</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_resumes_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading resumes...');
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
    
    // $('li_back_2').addEvent('mouseover', function() {
    //     $('li_back_2').setStyles({
    //         'color': '#FF0000',
    //         'text-decoration': 'underline'
    //     });
    // });
    // 
    // $('li_back_2').addEvent('mouseout', function() {
    //     $('li_back_2').setStyles({
    //         'color': '#000000',
    //         'text-decoration': 'none'
    //     });
    // });
}

function onDomReady() {
    set_root();
    set_mouse_events();
    
    $('li_back').addEvent('click', show_recommenders);
    $('li_back_1').addEvent('click', show_recommenders);
    // $('li_back_2').addEvent('click', show_current_candidate_resumes);
    $('li_profile').addEvent('click', show_current_recommender_profile);
    // $('li_resumes').addEvent('click', show_current_candidate_resumes);
    
    $('add_new_recommender').addEvent('click', show_new_recommender_form);
    $('add_new_recommender_1').addEvent('click', show_new_recommender_form);
    
    $('save').addEvent('click', save_profile);
    $('add').addEvent('click', add_new_recommender);
    
    $('sort_added_on').addEvent('click', function() {
        order_by = 'recommenders.added_on';
        ascending_or_descending();
        show_recommenders();
    });
    
    $('sort_recommender').addEvent('click', function() {
        order_by = 'recommenders.lastname';
        ascending_or_descending();
        show_recommenders();
    });
        
    show_recommenders();
}

window.addEvent('domready', onDomReady);
