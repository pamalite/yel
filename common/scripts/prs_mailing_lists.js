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
    $('div_mailing_lists').setStyle('display', 'block');
    $('div_candidates').setStyle('display', 'none');
    
    var params = 'id=' + id + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/prs/mailing_lists_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading mailing lists.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There is no mailing list at the moment.</div>';
            } else {
                var ids = xml.getElementsByTagName('id');
                var labels = xml.getElementsByTagName('label');
                var counts = xml.getElementsByTagName('number_of_candidates');
                var employees = xml.getElementsByTagName('employee_name');
                var created_ons = xml.getElementsByTagName('formatted_created_on');
                
                for (var i=0; i < ids.length; i++) {
                    var id = ids[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="date">' + created_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="employee">' + employees[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    var label = 'Unlabeled';
                    if (labels[i].childNodes.length > 0) {
                        label = labels[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="label"><a class="no_link" onClick="show_candidates(\'' + id + '\', \'' + label + '\');">' + label + '</a></td>' + "\n";
                    html = html + '<td class="count">' + counts[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="actions"><a class="no_link" onClick="remove_list(\'' + id + '\');">Remove</a>&nbsp;|&nbsp;<a class="no_link" onClick="rename_list(\'' + id + '\');">Rename</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
            }
            html = html + '</table>';
            
            $('div_mailing_lists_list').set('html', html);
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading mailing lists...');
        }
    });
    
    request.send(params);
}

function add_new_mailing_list() {
    var new_label = prompt('Please enter a label for this new mailing list.');
    
    if (!isEmpty(new_label)) {
        var params = 'id=' + id + '&action=add_mailing_list';
        params = params + '&label=' + new_label;
        
        var uri = root + "/prs/mailing_lists_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    alert('An error occurred while adding the new mailing list. Please try again.');
                    return;
                }
                
                set_status('');
                show_mailing_lists();
            },
            onRequest: function(instance) {
                set_status('Adding new mailing list...');
            }
        });

        request.send(params);
    }
}

function rename_list(_id) {
    var new_label = prompt('Please enter a new label for this mailing list.');
    
    if (!isEmpty(new_label)) {
        var params = 'id=' + _id + '&action=rename_mailing_list';
        params = params + '&label=' + new_label;
        
        var uri = root + "/prs/mailing_lists_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    alert('An error occurred while renaming the mailing list. Please try again.');
                    return;
                }
                
                set_status('');
                show_mailing_lists();
            },
            onRequest: function(instance) {
                set_status('Renaming mailing list...');
            }
        });

        request.send(params);
    }
}

function remove_list(_id) {
    var confirmed = confirm('Are you sure to remove this mailing list?');
    
    if (confirmed) {
        var params = 'id=' + _id + '&action=remove_mailing_list';
        
        var uri = root + "/prs/mailing_lists_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    alert('An error occurred while removing the mailing list. Please try again.');
                    return;
                }
                
                set_status('');
                show_mailing_lists();
            },
            onRequest: function(instance) {
                set_status('Removing mailing list...');
            }
        });

        request.send(params);
    }
}

function show_current_mailing_list_candidates() {
    show_candidates(current_mailing_list_id, current_label);
}

function show_candidates(_id, _label) {
    current_mailing_list_id = _id;
    current_label = _label
    
    $('div_mailing_lists').setStyle('display', 'none');
    $('div_candidates').setStyle('display', 'block');
    $('mailing_list_label').set('html', current_label);
    
    var params = 'id=' + current_mailing_list_id + '&action=get_candidates';
    params = params + '&order_by=' + candidates_order_by + ' ' + candidates_order;
    
    var uri = root + "/prs/mailing_lists_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading candidates.');
                return false;
            } 
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">No candidates added to list.</div>';
            } else {
                var ids = xml.getElementsByTagName('email_addr');
                var members = xml.getElementsByTagName('candidate_name');
                var joined_ons = xml.getElementsByTagName('formatted_joined_on');
                var phone_nums = xml.getElementsByTagName('phone_num');
                var added_bys = xml.getElementsByTagName('added_by');
                
                for (var i=0; i < ids.length; i++) {
                    var id = ids[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    
                    html = html + '<td class="date">' + joined_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    var link = 'resumes_privileged.php?candidate=' + id;
                    if (added_bys[i].childNodes.length > 0) {
                        link = 'resumes.php?candidate=' + id;
                    }
                    
                    var phone_num = 'N/A';
                    if (phone_nums[i].childNodes.length > 0) {
                        phone_num = phone_nums[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="candidate"><a href="' + link + '">' + members[i].childNodes[0].nodeValue + '</a><br/><div class="phone_num"><strong>Tel:</strong> ' + phone_num + '<br/><strong>E-mail:</strong> ' + id + '</div></td>' + "\n";
                    html = html + '<td class="actions"><a class="no_link" onClick="remove_candidate_from_list(' + current_mailing_list_id + ', \'' + id + '\');">Remove</a></td>' + "\n";
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

function remove_candidate_from_list(_id, _email_addr) {
    var confirmed = confirm('Are you sure to remove candidate from this mailing list?');
    
    if (confirmed) {
        var params = 'id=' + _id + '&action=remove_candidate&candidate=' + _email_addr;
        
        var uri = root + "/prs/mailing_lists_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    alert('An error occurred while removing candidate from the mailing list. Please try again.');
                    return;
                }
                
                set_status('');
                show_current_mailing_list_candidates();
            },
            onRequest: function(instance) {
                set_status('Removing candidate from mailing list...');
            }
        });

        request.send(params);
    }
}

function close_email_form() {
    $('div_email_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
    set_status('');
}

function show_email_form() {
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
    
    $('mailing_list_id').value = current_mailing_list_id;
    $('list_label').set('html', current_label);
    
    $('div_email_form').setStyle('display', 'block');
}

function send_email_to_list() {
    if (isEmpty($('email_message').value) || isEmpty($('email_subject').value)) {
        alert('You need to enter a subject and a message to be send.');
        return;
    }
    
    var params = 'id=' + $('mailing_list_id').value + '&action=send_email_to_list';
    params = params + '&employee=' + id;
    params = params + '&subject=' + $('email_subject').value;
    params = params + '&message=' + $('email_message').value;
    
    var uri = root + "/prs/mailing_lists_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            close_email_form();
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Sending message to mailing list...');
        }
    });

    request.send(params);
}

function set_mouse_events() {    
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
}

function onDomReady() {
    initialize_page();
    list_available_industries('0');
    set_mouse_events();
    
    $('li_back').addEvent('click', show_mailing_lists);
    $('add_new_list').addEvent('click', add_new_mailing_list);
    $('add_new_list_1').addEvent('click', add_new_mailing_list);
    
    $('sort_added_on').addEvent('click', function() {
        order_by = 'candidates_mailing_lists.created_on';
        ascending_or_descending();
        show_mailing_lists();
    });
    
    $('sort_added_by').addEvent('click', function() {
        order_by = 'members.lastname';
        ascending_or_descending();
        show_mailing_lists();
    });
    
    $('sort_label').addEvent('click', function() {
        order_by = 'candidates_mailing_lists.label';
        ascending_or_descending();
        show_mailing_lists();
    });
    
    $('sort_count').addEvent('click', function() {
        order_by = 'number_of_candidates';
        ascending_or_descending();
        show_mailing_lists();
    });
    
    $('sort_joined_on').addEvent('click', function() {
        candidates_order_by = 'members.joined_on';
        candidates_ascending_or_descending();
        show_current_mailing_list_candidates();
    });
    
    $('sort_candidate').addEvent('click', function() {
        candidates_order_by = 'members.lastname';
        candidates_ascending_or_descending();
        show_current_mailing_list_candidates();
    });
    
    show_mailing_lists();
}

window.addEvent('domready', onDomReady);
