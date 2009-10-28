var order_by = 'candidates_mailing_lists.created_on';
var order = 'desc';
var candidates_order_by = 'members.lastname';
var candidates_order = 'desc';

var current_mailing_list_id = 0;
var current_label = '';

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

function show_mailing_lists() {
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
    
    var params = 'id=' + id + '&recommender=' + current_recommender_email_addr + '&action=get_candidates';
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

function onDomReady() {
    set_root();

    $('add_new_mailing_list').addEvent('click', function() {
        var new_label = prompt('Please enter the label of this mailing list.');
        add_new_mailing_list(new_label);
    });
    
    $('add_new_mailing_list_1').addEvent('click', function() {
        var new_label = prompt('Please enter the label of this mailing list.');
        add_new_mailing_list(new_label);
    });
    
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
    
    show_mailing_lists();
}

window.addEvent('domready', onDomReady);
