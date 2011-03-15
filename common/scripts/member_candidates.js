var selected_tab = 'li_cover_note';
var order_by = 'member_referees.referred_on';
var order = 'desc';
var filter_by = '0';
var find_order_by = 'joined_on';
var find_order = 'desc';
var history_order_by = 'referrals.referred_on';
var history_order = 'desc';

var emails = new Array();
var emails_from_vcard = new Array();
var search_criteria = '';
var contacts_list = new ListBox('contacts', 'contacts_list', true);
var username = '';
var service = '';
var selected_email_indices = new Array();

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function find_ascending_or_descending() {
    if (find_order == 'desc') {
        find_order = 'asc';
    } else {
        find_order = 'desc';
    }
}

function history_ascending_or_descending() {
    if (history_order == 'desc') {
        history_order = 'asc';
    } else {
        history_order = 'desc';
    }
}

function display_referee_networks_from(referee_id) {
    $('div_candidate_networks').set('html', '');
    
    if (isEmpty(referee_id)) {
        return;
    }
    
    var params = 'id=' + referee_id;
    params = params + '&action=get_referee_networks';
    
    var uri = root + "/members/candidate_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while retrieving networks.');
                return;
            }
            
            var industries = xml.getElementsByTagName('industry');
            var network_ids = xml.getElementsByTagName('network_id');
            
            var html = '';
            for (var i=0; i < industries.length; i++) {
                html = html + '<span class="network">' + industries[i].childNodes[0].nodeValue + '</span>&nbsp;';
            }
            
            $('div_candidate_networks').set('html', html);
            set_status('');
            return;
        },
        onRequest: function(instance) {
            set_status('Loading networks...');
        }
    });
    
    request.send(params);
}

function display_referee_contacts_from(referee_id) {
    $('email').set('html', '');
    $('phone').set('html', '');
    if (isEmpty(referee_id)) {
        return;
    }
    
    var params = 'id=' + referee_id;
    params = params + '&action=get_referee_contacts';
    
    var uri = root + "/members/candidate_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while retrieving referee contacts.');
                return;
            }
            
            var email_addrs = xml.getElementsByTagName('email_addr');
            var phone_nums = xml.getElementsByTagName('phone_num');
            
            $('email').set('html', email_addrs[0].childNodes[0].nodeValue);
            $('phone').set('html', phone_nums[0].childNodes[0].nodeValue);
            
            set_status('');
            return;
        },
        onRequest: function(instance) {
            set_status('Loading referee contacts...');
        }
    });
    
    request.send(params);
}

function display_total_reward_earned() {
    var params = 'id=' + id;
    params = params + '&action=get_total_rewards_earned';
    
    var uri = root + "/members/candidate_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            $('div_total_rewards').setStyle('display', 'none');
            
            if (txt == 'ko') {
                set_status('An error occured while retrieving reward earned.');
            } 
            
            if (txt != '0') {
                var rewards = xml.getElementsByTagName('reward_earned');
                var currencies = xml.getElementsByTagName('currency');
                
                var html = ''
                for (var i=0; i < rewards.length; i++) {
                    html = html + '<span style="font-weight: bold; text-decoration: underline;">' + currencies[i].childNodes[0].nodeValue + ' ' + rewards[i].childNodes[0].nodeValue + '</span>';
                    
                    if (i < rewards.length-1) {
                        html = html + '&nbsp;&nbsp;&nbsp;';
                    }
                    
                    if (i % 4 == 0 && i < rewards.length-1 && i > 0) {
                        html = html + '<br/>';
                    }
                }
                
                $('rewards').set('html', html);
                $('div_total_rewards').setStyle('display', 'block');
            }
            
            set_status('');
            return;
        },
        onRequest: function(instance) {
            set_status('Loading total rewards...');
        }
    });
    
    request.send(params);
}

function display_reward_earned_from(referee_id) {
    $('total_rewards').set('html', '0.00');
    var params = 'id=' + referee_id + '&member=' + id;
    params = params + '&action=get_reward_earned';
    
    var uri = root + "/members/candidate_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while retrieving reward earned.');
            }
            
            var reward_earneds = xml.getElementsByTagName('reward_earned');
            $('total_rewards').set('html', reward_earneds[0].childNodes[0].nodeValue);
            set_status('');
            return;
        },
        onRequest: function(instance) {
            set_status('Loading total rewards...');
        }
    });
    
    request.send(params);
}

function display_currency_symbol() {
    $('currency').set('html', 'MYR');
    var params = 'id=0&member=' + id;
    params = params + '&action=get_currency_symbol';
    
    var uri = root + "/members/candidate_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while retrieving currency symbol.');
                return;
            }
            
            var symbols = xml.getElementsByTagName('symbol');
            $('currency').set('html', symbols[0].childNodes[0].nodeValue);
            set_status('');
            return;
        },
        onRequest: function(instance) {
            set_status('Loading currency...');
        }
    });
    
    request.send(params);
}

function show_candidate_histories(referee_id) {
    $('li_candidates').set('html', '&lt;&lt; Contacts');
    $('div_history').set('html', '');
    if (isEmpty(referee_id)) {
        return;
    }
    
    var params = 'id=' + referee_id;
    params = params + '&action=get_candidate_histories';
    params = params + '&order_by=' + history_order_by + ' ' + history_order;
    
    var uri = root + "/members/candidate_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while retrieving histories.');
                return;
            }
            
            var html = '';
            if (txt == '0') {
                html = '<div style="text-align: center;padding-top: 10px;">This candidate has not been referred.</div>';
            } else {
                var ids = xml.getElementsByTagName('id');
                var jobs = xml.getElementsByTagName('title');
                var employers = xml.getElementsByTagName('name');
                var currencies = xml.getElementsByTagName('currency');
                var referred_ons = xml.getElementsByTagName('formatted_referred_on');
                var referee_acknowledged_ons = xml.getElementsByTagName('formatted_referee_acknowledged_on');
                var referee_acknowledged_others_ons = xml.getElementsByTagName('formatted_referee_acknowledged_others_on');
                var employed_ons = xml.getElementsByTagName('formatted_employed_on');
                var work_commence_ons = xml.getElementsByTagName('formatted_work_commence_on');
                var total_rewards = xml.getElementsByTagName('total_reward');
                var paid_rewards = xml.getElementsByTagName('paid_reward');

                html = '<table id="history_list" class="list">';
                for (var i=0; i < ids.length; i++) {
                    html = html + '<tr id="'+ ids[i].childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="testimony"><a href="#" onClick="show_testimony(\'' + ids[i].childNodes[0].nodeValue + '\')">View</a></td>' + "\n";
                    html = html + '<td class="job">' + jobs[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";

                    if (referred_ons[i].childNodes.length <= 0) {
                        html = html + '<td class="date"><span style="font-size: 9pt; color: #AAAAAA;">Pending...</span></td>' + "\n";
                    } else {
                        html = html + '<td class="date">' + referred_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    }

                    var acknowleged = '';
                    var taken_by_others = false;
                    if (referee_acknowledged_ons[i].childNodes.length <= 0) {
                        acknowledged = '<td class="date"><span style="font-size: 9pt; color: #AAAAAA;">Pending...</span></td>' + "\n";
                    } else {
                        acknowledged = '<td class="date">' + referee_acknowledged_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    }
                    
                    if (referee_acknowledged_others_ons[i].childNodes.length > 0) {
                        acknowledged = '<td class="date">[&bull;]&nbsp;' + referee_acknowledged_others_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                        taken_by_others = true;
                    }
                    
                    html = html + acknowledged;
                    if (!taken_by_others) {
                        if (employed_ons[i].childNodes.length <= 0) {
                            html = html + '<td class="date"><span style="font-size: 9pt; color: #AAAAAA;">Pending...</span></td>' + "\n";
                        } else {
                            html = html + '<td class="date">' + employed_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                        }

                        if (work_commence_ons[i].childNodes.length <= 0) {
                            html = html + '<td class="date"><span style="font-size: 9pt; color: #AAAAAA;">Pending...</span></td>' + "\n";
                        } else {
                            html = html + '<td class="date">' + work_commence_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                        }
                        
                        var total_reward = total_rewards[i].childNodes[0].nodeValue;
                        if (parseInt(total_reward) <= 0) {
                            total_reward = '<span style="font-size: 9pt; color: #AAAAAA;">Pending...</span>';
                        } else {
                            total_reward = currencies[i].childNodes[0].nodeValue + '&nbsp;' + total_reward;
                        }
                        html = html + '<td class="reward">' + total_reward + '</td>' + "\n";
                        
                        var paid_reward = paid_rewards[i].childNodes[0].nodeValue;
                        if (parseInt(paid_reward) <= 0) {
                            paid_reward = '<span style="font-size: 9pt; color: #AAAAAA;">Pending...</span>';
                        } else {
                            paid_reward = currencies[i].childNodes[0].nodeValue + '&nbsp;' +  paid_reward;
                        }
                        html = html + '<td class="reward">' + paid_reward + '</td>' + "\n";
                    } else {
                        html = html + '<td class="date">-</td>' + "\n";
                        html = html + '<td class="date">-</td>' + "\n";
                        html = html + '<td class="reward">-</td>' + "\n";
                        html = html + '<td class="reward">-</td>' + "\n";
                    }
                    
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
            }
            
            
            $('div_history').set('html', html);
            set_status('');

        },
        onRequest: function(instance) {
            set_status('Loading histories...');
        }
    });
    
    request.send(params);
}

function show_candidate(referee_id, referee_name) {
    $('div_search_candidates').setStyle('display', 'none');
    $('div_candidate').setStyle('display', 'block');
    $('div_candidates').setStyle('display', 'none');
    $('div_networks').setStyle('display', 'none');
    $('candidate_id').value = referee_id;
    
    $('div_candidate_name').set('html', referee_name);
    display_referee_networks_from(referee_id);
    display_referee_contacts_from(referee_id);
    //display_currency_symbol();
    //display_reward_earned_from(referee_id);
    show_candidate_histories(referee_id);
}

function close_testimony() {
    $('div_testimony').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_testimony(referral_id) {
    $('testimony').set('html', '');
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_testimony').getStyle('height'));
    var div_width = parseInt($('div_testimony').getStyle('width'));
    
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
    
    $('div_testimony').setStyle('top', ((window_height - div_height) / 2));
    $('div_testimony').setStyle('left', ((window_width - div_width) / 2));
    
    var params = 'id=' + referral_id;
    params = params + '&action=get_testimony';
    
    var uri = root + "/members/candidate_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while retrieving testimony.');
                return;
            }
            
            var testimonies = xml.getElementsByTagName('testimony');
            var html = 'No testimony found.';
            if (testimonies[0].childNodes.length > 0) {
                html = testimonies[0].childNodes[0].nodeValue.replace(/\n/g, '<br/>');
            }
            $('testimony').set('html', html);
            $('div_blanket').setStyle('display', 'block');
            $('div_testimony').setStyle('display', 'block');
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading testimony...');
        }
    });
    
    request.send(params);
}

function delete_candidates() {
    var inputs = $('list').getElementsByTagName('input');
    var payload = '<referees>' + "\n";
    var count = 0;
    
    for(i=0; i < inputs.length; i++) {
        var attributes = inputs[i].attributes;
        if (attributes.getNamedItem('type').value == 'checkbox' && 
            attributes.getNamedItem('name').value == 'id') {
            if (inputs[i].checked) {
                payload = payload + '<id>' + inputs[i].id + '</id>' + "\n";
                count++;
            }
        }
    }
    
    payload = payload + '</referees>';
    
    if (count <= 0) {
        set_status('Please select at least one contact.');
        return false;
    }
    
    var proceed = confirm('Are you sure to close the selected contacts?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=0&member=' + id;
    params = params + '&action=delete';
    params = params + '&payload=' + payload;
    
    var uri = root + "/members/candidate_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while deleting selected candidates.');
                return false;
            }
            
            for (i=0; i < inputs.length; i++) {
                var attributes = inputs[i].attributes;
                if (attributes.getNamedItem('type').value == 'checkbox' && 
                    attributes.getNamedItem('name').value == 'id') {
                    if (inputs[i].checked) {
                        $(inputs[i].id).setStyle('display', 'none');
                    }
                }
            }
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading candidates...');
        }
    });
    
    request.send(params);
}

function show_find_candidates() {
    $('div_search_candidates').setStyle('display', 'block');
    $('div_candidate').setStyle('display', 'none');
    $('div_candidates').setStyle('display', 'none');
    $('div_networks').setStyle('display', 'none');
    $('li_candidates').set('html', '&lt;&lt; Contacts');
    $('candidate_id').value = "0";
}

function find_candidates() {
    set_status('');
    var using = $('search_using').options[$('search_using').selectedIndex].value;
    var match = '';
    
    if (using == 'email_addr') {
        if (isEmpty($('match').value) && isEmpty(search_criteria)) {
            set_status('You must enter an e-mail address to search.');
            return;
        }

        if ($('match').value != id) {
            if (!isEmpty($('match').value)) {
                search_criteria = $('match').value;
            }
        } else {
            set_status('You cannot add yourself as your own contact.');
            return;
        }
    } else {
        if (isEmpty($('match').value) && isEmpty(search_criteria)) {
            set_status('You must enter a name to search.');
            return;
        }
        
        if (!isEmpty($('match').value)) {
            search_criteria = $('match').value;
        }
    }
    
    var params = 'id=' + id;
    params = params + '&action=find';
    params = params + '&using=' + using;
    params = params + '&result_order_by=' + find_order_by + ' ' + find_order;
    params = params + '&criteria=' + search_criteria;
    
    var uri = root + "/members/candidates_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while finding candidates.');
                return false;
            }
            
            var html = '<table id="result_list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center;padding-top: 10px;">Sorry, there are no results matching your search criteria. </div>';
            } else {
                var email_addrs = xml.getElementsByTagName('email_addr');
                var masked_email_addrs = xml.getElementsByTagName('masked_email_addr');
                var names = xml.getElementsByTagName('name');
                var joined_ons = xml.getElementsByTagName('joined_date')

                emails = new Array();
                for (var i=0; i < email_addrs.length; i++) {
                    var candidate_id = email_addrs[i].childNodes[0].nodeValue;

                    emails[i] = candidate_id;

                    html = html + '<tr id="'+ candidate_id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="email">' + masked_email_addrs[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="name">' + names[i].childNodes[0].nodeValue + '</td>' + "\n";
                    if (joined_ons[i].childNodes.length <= 0) {
                        html = html + '<td class="date">N/A</td>' + "\n";
                    } else {
                        html = html + '<td class="date">' + joined_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    }
                    html = html + '<td class="add"><a href="#" onClick="create_referee(\'' + i + '\', \'' + names[i].childNodes[0].nodeValue + '\')">Add</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
            }
            
            html = html + '</table>';
            $('div_search_result').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Searching...');
        }
    });
    
    request.send(params);
}

function create_referee(email_index, candidate_name) {
    if (isEmpty(email_index) || isEmpty(candidate_name)) {
        return false;
    }
    
    if (!confirm('You are about to send a request to ' + candidate_name + ' to seek for his/her approval to be added into your Contacts.\n\nDo you wish to proceed with your request?')) {
        return false;
    }
    
    var params = 'id=0&member=' + id;
    params = params + '&action=create_referee';
    params = params + '&referee=' + emails[email_index];
    //alert(params);
    var uri = root + "/members/candidate_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while sending approval to the candidate.');
                return false;
            }
            
            set_status('');
            find_candidates();
        },
        onRequest: function(instance) {
            set_status('Sending approval request to candidate...');
        }
    });
    
    request.send(params);
}

function show_candidates() {
    $('div_search_candidates').setStyle('display', 'none');
    $('div_candidate').setStyle('display', 'none');
    $('div_networks').setStyle('display', 'none');
    $('div_candidates').setStyle('display', 'block');
    $('div_invite_contacts').setStyle('display', 'none');
    
    $('li_invite').setStyle('border', '1px solid #0000FF');
    $('li_candidates').setStyle('border', '1px solid #CCCCCC');
    $('li_networks').setStyle('border', '1px solid #0000FF');
    
    $('li_candidates').set('html', 'Contacts');
    
    var params = 'id=' + id;
    params = params + '&order_by=' + order_by + ' ' + order;
    params = params + '&filter_by=' + filter_by;
    
    var uri = root + "/members/candidates_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading candidates.');
                return false;
            }
            
            var ids = xml.getElementsByTagName('id');
            var referee_names = xml.getElementsByTagName('referee_name');
            var referee_emails = xml.getElementsByTagName('referee');
            var referred_ons = xml.getElementsByTagName('referred_on');
            var networks = xml.getElementsByTagName('networks');
            var network_ids = xml.getElementsByTagName('network_ids');
            
            var html = '<table id="list" class="list">';
            if (ids.length <= 0) {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">Please click on the \"Add Contact\" button to get started.</div>' + "\n";
                
                $('delete_candidates').disabled = true;
                $('delete_candidates_1').disabled = true;
                $('network_dropdown').disabled = true;
            } else {
                for (var i=0; i < ids.length; i++) {
                    var referee_id = ids[i];
                    
                    html = html + '<tr id="'+ referee_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="checkbox"><input type="checkbox" id="'+ referee_id.childNodes[0].nodeValue + '" name="id" /></td>' + "\n";
                    //html = html + '<td class="id">' + referee_id.childNodes[0].nodeValue + '</td>' + "\n";
                    var labels_out = '';
                    if (networks[i].childNodes.length > 0) {
                        var labels = networks[i].childNodes[0].nodeValue.split(';');
                        var network_id = network_ids[i].childNodes[0].nodeValue.split(';');
                        for (var j=0; j < labels.length; j++) {
                            if (!isEmpty(labels[j])) {
                                labels_out = labels_out + '<span class="network">&nbsp;<a class="no_link" onClick="delete_referee_from_network(\'' + network_id[j] + '\', \'' + referee_id.childNodes[0].nodeValue + '\');"><b>X</b></a>&nbsp;|&nbsp;' + labels[j] + '</span>&nbsp;';
                            }
                        }
                    } 
                    html = html + '<td class="title">' + labels_out + '<a href="#" onClick="show_candidate(\'' + referee_id.childNodes[0].nodeValue + '\', \'' + add_slashes(referee_names[i].childNodes[0].nodeValue) + '\')">' + referee_names[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="date">' + referred_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
                
                $('delete_candidates').disabled = false;
                $('delete_candidates_1').disabled = false;
                $('network_dropdown').disabled = false;
            }
            
            $('div_list').set('html', html);
            set_status('');
            generate_network_filter(filter_by);
        },
        onRequest: function(instance) {
            set_status('Loading referees...');
        }
    });
    
    request.send(params);
    display_total_reward_earned();
}

function select_all_referees() {
    var inputs = $('list').getElementsByTagName('input');
    
    if ($('select_all').checked) {
        for (i=0; i < inputs.length; i++) {
            var attributes = inputs[i].attributes;
            if (attributes.getNamedItem('type').value == 'checkbox' &&
                attributes.getNamedItem('name').value == 'id') {
                inputs[i].checked = true;
            }
        }
    } else {
        for (i=0; i < inputs.length; i++) {
            var attributes = inputs[i].attributes;
            if (attributes.getNamedItem('type').value == 'checkbox' &&
                attributes.getNamedItem('name').value == 'id') {
                inputs[i].checked = false;
            }
        }
    }
}

function delete_networks() {
    var inputs = $('network_list').getElementsByTagName('input');
    var payload = '<networks>' + "\n";
    var count = 0;
    
    for(i=0; i < inputs.length; i++) {
        var attributes = inputs[i].attributes;
        if (attributes.getNamedItem('type').value == 'checkbox' && 
            attributes.getNamedItem('name').value == 'id') {
            if (inputs[i].checked) {
                payload = payload + '<id>' + inputs[i].id + '</id>' + "\n";
                count++;
            }
        }
    }
    
    payload = payload + '</networks>';
    
    if (count <= 0) {
        set_status('Please select at least one network.');
        return false;
    }
    
    var proceed = confirm('Are you sure to close the selected networks?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=0&member=' + id;
    params = params + '&action=delete_networks';
    params = params + '&payload=' + payload;
    
    var uri = root + "/members/candidate_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            //alert(txt);
            if (txt == 'ko') {
                set_status('An error occured while removing selected networks.');
                return false;
            }
            
            set_status('');
            show_networks();
            generate_networks_list();
        },
        onRequest: function(instance) {
            set_status('Removing selected networks...');
        }
    });
    
    request.send(params);
}

function add_network() {
    var network_industry = $('network_industry');
    var selected = network_industry.options[network_industry.selectedIndex];
    if (parseInt(selected.value) > 0) {
        var params = 'id=0&member=' + id;
        params = params + '&action=add_network';
        params = params + '&industry=' + selected.value;
        
        var uri = root + "/members/candidate_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    set_status('An error occured while creating network.');
                    return false;
                } else if (parseInt(txt) <= 0) {
                    set_status('Unable to create network.');
                    return false;
                }
                
                $('div_network_form').setStyle('display', 'none');
                generate_networks_list();
                
                if ($('div_networks').getStyle('display') != 'none') {
                    show_networks();
                    $('delete_networks').disabled = false;
                    $('delete_networks_1').disabled = false;
                }
                
                set_status('');
            },
            onRequest: function(instance) {
                set_status('Creating network...');
            }
        });

        request.send(params);
    }
}

function show_networks() {
    $('div_search_candidates').setStyle('display', 'none');
    $('div_candidate').setStyle('display', 'none');
    $('div_networks').setStyle('display', 'block');
    $('div_candidates').setStyle('display', 'none');
    $('div_invite_contacts').setStyle('display', 'none');
    $('div_total_rewards').setStyle('display', 'none');
    
    $('li_invite').setStyle('border', '1px solid #0000FF');
    $('li_candidates').setStyle('border', '1px solid #0000FF');
    $('li_networks').setStyle('border', '1px solid #CCCCCC');
    
    $('li_candidates').set('html', 'Contacts');
    
    var params = 'id=0&member=' + id;
    params = params + '&action=get_networks';
    
    var uri = root + "/members/candidate_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading networks.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">Networks helps you group and categorize your contacts into specific industries for easy reference. To create one, please click on "Add Network".</div>';
                
                $('delete_networks').disabled = true;
                $('delete_networks_1').disabled = true;
            } else {
                var ids = xml.getElementsByTagName('id');
                var networks = xml.getElementsByTagName('industry');

                var html = '<table id="network_list" class="list">';
                if (ids.length <= 0) {
                    html = '<tr id="0">' + "\n";
                    html = html + '<td colspan="3" style="text-align: center;">Please click on the \"Add Network\" button to get started.</td>' + "\n";
                    html = html + '</tr>' + "\n";

                    $('delete_networks').disabled = true;
                    $('delete_networks_1').disabled = true;
                } else {
                    for (var i=0; i < ids.length; i++) {
                        var network_id = ids[i];

                        html = html + '<tr id="'+ network_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                        html = html + '<td class="checkbox"><input type="checkbox" id="'+ network_id.childNodes[0].nodeValue + '" name="id" /></td>' + "\n";
                        //html = html + '<td class="id">' + network_id.childNodes[0].nodeValue + '</td>' + "\n";
                        html = html + '<td class="title">' + networks[i].childNodes[0].nodeValue + '</td>' + "\n";
                        html = html + '</tr>' + "\n";
                    }
                    
                    $('delete_networks').disabled = false;
                    $('delete_networks_1').disabled = false;
                }
                html = html + '</table>';
            }
            
            $('div_network_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading networks...');
        }
    });
    
    request.send(params);
}

function select_all_networks() {
    var inputs = $('network_list').getElementsByTagName('input');
    
    if ($('select_all_networks').checked) {
        for (i=0; i < inputs.length; i++) {
            var attributes = inputs[i].attributes;
            if (attributes.getNamedItem('type').value == 'checkbox' &&
                attributes.getNamedItem('name').value == 'id') {
                inputs[i].checked = true;
            }
        }
    } else {
        for (i=0; i < inputs.length; i++) {
            var attributes = inputs[i].attributes;
            if (attributes.getNamedItem('type').value == 'checkbox' &&
                attributes.getNamedItem('name').value == 'id') {
                inputs[i].checked = false;
            }
        }
    }
}

function generate_networks_list() {
    var params = 'id=0&member=' + id;
    params = params + '&action=get_networks';
    
    var uri = root + "/members/candidate_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while retrieving networks.');
                return false;
            }
            
            var ids = xml.getElementsByTagName('id');
            var industries = xml.getElementsByTagName('industry');
            
            var html = '<select id="network_dropdown" name="network_dropdown" onChange="add_to_network();">' + "\n";
            html = html + '<option value="0" selected>Add Selected To Network</option>' + "\n";
            html = html + '<option value="-1">Create a network</option>' + "\n";
            html = html + '<option value="0" disabled>&nbsp;</option>' + "\n";
            
            for (var i=0; i < ids.length; i++) {
                html = html + '<option value="' + ids[i].childNodes[0].nodeValue + '">' + industries[i].childNodes[0].nodeValue + '</option>' + "\n";
            }
            
            html = html + '</select>' + "\n";
            
            set_status('');
            $('networks_drop_down').set('html', html);
        },
        onRequest: function(instance) {
            set_status('Retrieving networks...');
        }
    });
    
    request.send(params);
}

function close_network_form() {
    $('div_network_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_network_form() {
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_network_form').getStyle('height'));
    var div_width = parseInt($('div_network_form').getStyle('width'));
    
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
    
    $('div_network_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_network_form').setStyle('left', ((window_width - div_width) / 2));
    $('div_blanket').setStyle('display', 'block');
    $('div_network_form').setStyle('display', 'block');
    list_industries_in(0, "network_industry_drop_down", "network_industry", "network_industry");
}

function add_to_network() {
    var inputs = $('list').getElementsByTagName('input');
    var selected = $('network_dropdown').options[$('network_dropdown').selectedIndex].value;
    
    if (parseInt(selected) == -1) {
        show_network_form();
        return false;
    }
    
    if (parseInt(selected) == 0) {
        return false;
    }
    
    var payload = '<referees>' + "\n";
    var count = 0;
    
    for(i=0; i < inputs.length; i++) {
        var attributes = inputs[i].attributes;
        if (attributes.getNamedItem('type').value == 'checkbox' && 
            attributes.getNamedItem('name').value == 'id') {
            if (inputs[i].checked) {
                payload = payload + '<id>' + inputs[i].id + '</id>' + "\n";
                count++;
            }
        }
    }
    
    payload = payload + '</referees>';
    
    if (count <= 0) {
        return false;
    }
    
    var params = 'id=0&network=' + selected;
    params = params + '&member=' + id;
    params = params + '&action=add_referees_into_network';
    params = params + '&payload=' + payload;
    
    var uri = root + "/members/candidate_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while adding candidates to network.<br/>Please make sure you DO NOT add the same contact to the same network again.');
                return false;
            }
            
            set_status('');
            show_candidates();
            generate_networks_list();
        },
        onRequest: function(instance) {
            set_status('Adding candidates to network...');
        }
    });
    
    request.send(params);
}

function generate_network_filter(selected) {
    var params = 'id=0&member=' + id;
    params = params + '&action=get_networks';

    var uri = root + "/members/candidate_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while retrieving networks.');
                return false;
            }

            var html = '<select id="network_filter" name="network_filter" onChange="set_filter();">' + "\n";
            if (selected == '0') {
                html = html + '<option value="0" selected>All</option>' + "\n";
            } else {
                html = html + '<option value="0">All</option>' + "\n";
            }
            
            html = html + '<option value="0" disabled>&nbsp;</option>' + "\n";
            
            if (txt != '0') {
                var ids = xml.getElementsByTagName('id');
                var industries = xml.getElementsByTagName('industry');

                for (var i=0; i < ids.length; i++) {
                    if (ids[i].childNodes[0].nodeValue == selected) {
                        html = html + '<option value="' + ids[i].childNodes[0].nodeValue + '" selected>' + industries[i].childNodes[0].nodeValue + '</option>' + "\n";
                    } else {
                        html = html + '<option value="' + ids[i].childNodes[0].nodeValue + '">' + industries[i].childNodes[0].nodeValue + '</option>' + "\n";
                    }
                }
            }

            html = html + '</select>' + "\n";

            set_status('');
            $('network_filter_drop_down').set('html', html);
        },
        onRequest: function(instance) {
            set_status('Retrieving networks...');
        }
    });

    request.send(params);
}

function set_filter() {
    filter_by = $('network_filter').options[$('network_filter').selectedIndex].value;
    show_candidates();
}

function delete_referee_from_network(_network, _referee, _display_candidates) {
    var params = 'id='+ _referee + '&network=' + _network;
    params = params + '&member=' + id;
    params = params + '&action=delete_referee_from_network';
    
    var uri = root + "/members/candidate_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while removing candidate from network.');
                return false;
            }
            
            set_status('');
            show_candidates();
            generate_networks_list();
        },
        onRequest: function(instance) {
            set_status('Removing candidates from network...');
        }
    });
    
    request.send(params);
}

function show_invites() {
    $('div_search_candidates').setStyle('display', 'none');
    $('div_candidate').setStyle('display', 'none');
    $('div_networks').setStyle('display', 'none');
    $('div_candidates').setStyle('display', 'none');
    $('div_invite_contacts').setStyle('display', 'block');
    $('div_total_rewards').setStyle('display', 'none');
    
    $('li_candidates').setStyle('border', '1px solid #0000FF');
    $('li_networks').setStyle('border', '1px solid #0000FF');
    $('li_invite').setStyle('border', '1px solid #CCCCCC');
    
    $('li_candidates').set('html', 'Contacts');
}

// ==== invite section starts ====

function duplicated(emails, email) {
    if (isEmpty(emails)) {
        return false;
    }
    
    var temp = emails.split(',');
    for (var i=0; i < temp.length; i++) {
        if (email == temp[i]) {
            return true;
        }
    }
    
    return false;
}

function validate() {
    if (isEmpty($('oi_service').value) || $('oi_service').value == '0') {
        //set_status('You need to select either e-mail or social networking service.');
        alert('You need to select either e-mail or social networking service.');
        return false;
    }
    
    if (isEmpty($('username').value)) {
        //set_status('You need to enter the username to the service.');
        alert('You need to enter the username to the service.');
        return false;
    }
    
    if (isEmpty($('password').value)) {
        //set_status('You need to enter the password to the service.');
        alert('You need to enter the password to the service.');
        return false;
    }
    
    return true;
}

function show_smart_invite() {
    selected_tab = 'li_smart_invite';
    $('div_smart_invite').setStyle('display', 'block');
    $('div_vcard_invite').setStyle('display', 'none');
    $('div_manual_invite').setStyle('display', 'none');
    
    $('li_smart_invite').setStyle('border', '1px solid #CCCCCC');
    $('li_vcard_invite').setStyle('border', '1px solid #0000FF');
    $('li_manual_invite').setStyle('border', '1px solid #0000FF');
    
    set_status('');
    
    $('get_contacts_form').setStyle('display', 'block');
    $('send_invite_form').setStyle('display', 'none');
}

function show_vcard_invite() {
    selected_tab = 'li_vcard_invite';
    $('div_smart_invite').setStyle('display', 'none');
    $('div_vcard_invite').setStyle('display', 'block');
    $('div_manual_invite').setStyle('display', 'none');
    
    $('li_smart_invite').setStyle('border', '1px solid #0000FF');
    $('li_vcard_invite').setStyle('border', '1px solid #CCCCCC');
    $('li_manual_invite').setStyle('border', '1px solid #0000FF');
    
    set_status('');
    
    $('get_vcard_contacts_form').setStyle('display', 'block');
    $('send_vcard_invites_form').setStyle('display', 'none');
}

function show_manual_invite() {
    selected_tab = 'li_manual_invite';
    $('div_smart_invite').setStyle('display', 'none');
    $('div_vcard_invite').setStyle('display', 'none');
    $('div_manual_invite').setStyle('display', 'block');
    
    $('li_smart_invite').setStyle('border', '1px solid #0000FF');
    $('li_vcard_invite').setStyle('border', '1px solid #0000FF');
    $('li_manual_invite').setStyle('border', '1px solid #CCCCCC');
    
    set_status('');
}

function add_remove_email_to_list(_index) {
    if ($('email_addr_' + _index).checked) {
        for (var i=0; i < selected_email_indices.length; i++) {
            if (selected_email_indices[i] == _index) {
                return;
            }
        }

        selected_email_indices[selected_email_indices.length] = _index;
    } else {
        var new_list = new Array();
        var count = 0;
        for (var i=0; i < selected_email_indices.length; i++) {
            if (selected_email_indices[i] != _index) {
                new_list[count] = selected_email_indices[i];
                count++;
            }
        }

        selected_email_indices = new_list;
    }
}

function start_upload() {
    $('upload_progress').setStyle('display', 'block');
    set_status('Uploading vCard...')
    return true;
}

function stop_upload() {
    $('upload_progress').setStyle('display', 'none');
    set_status('An error occured while reading the uploaded vCard.');
}

function parse_contacts(_txt) {
    $('upload_progress').setStyle('display', 'none');
    set_status('');
    var xml = '';
    emails_from_vcard = new Array();
    selected_email_indices = new Array();
    $('vcard_contacts').set('html', '');
    if (!isEmpty(_txt)) {
        try { // for IE
            xml = new ActiveXObject("Microsoft.XMLDOM");
            xml.async = "false";
            xml.loadXML(_txt);
        } catch(e) {
            parser = new DOMParser();
            xml = parser.parseFromString(_txt, "text/xml");
        }
        
        var contacts = xml.getElementsByTagName('contact');
        var email_count = 0;
        if (contacts.length > 0) {
            var html = '<table style="margin: auto; width: 100%; border: none;">';
            for (var i=0; i < contacts.length; i++) {
                var odd = (i % 2 == 0);
                
                var contact = contacts[i].childNodes;
                var name = '';
                var emails_list = new Array();
                for (var j=0; j < contact.length; j++) {
                    if (contact[j].nodeName == 'name') {
                        name = contact[j].childNodes[0].nodeValue;
                    } else if (contact[j].nodeName == 'emails') {
                        var emails = contact[j].childNodes;
                        var count = 0;
                        for (var k=0; k < emails.length; k++) {
                            if (emails[k].nodeName == 'email') {
                                emails_list[count] = emails[k].childNodes[0].nodeValue;
                                emails_from_vcard[email_count] = emails[k].childNodes[0].nodeValue;
                                count++;
                                email_count++;
                            }
                        }
                    }
                }
                
                var style = '';
                if (odd) {
                    style = 'background-color: #EEEEEE;';
                }
                
                html = html + '<tr>'
                html = html + '<td class="vcard_contact" style="vertical-align: top; ' + style + '">' + name + '</td>';
                html = html + '<td class="vcard_contact" style="vertical-align: top; ' + style + '">';
                for (var p=0; p < emails_list.length; p++) {
                    var index = -1;
                    for (var q = emails_from_vcard.length-1; q >= 0; q--) {
                        if (emails_from_vcard[q] == emails_list[p]) {
                            index = q;
                            break;
                        }
                    }
                    html = html + '<input type="checkbox" id="email_addr_' + index + '" value="' + index + '" onClick="add_remove_email_to_list(' + index + ');" />&nbsp;' + emails_list[p];
                    
                    if (p < emails_list.length-1) {
                        html = html + '<br/>';
                    }
                }
                html = html + '</td>';
                html = html + '</tr>';
            }
            html = html + '</table>';
            
            $('vcard_contacts').set('html', html);
            $('get_vcard_contacts_form').setStyle('display', 'none');
            $('send_vcard_invites_form').setStyle('display', 'block');
            
            if (contacts.length < 10) {
                $('vcard_contacts').setStyle('height', (25 * parseInt(contacts.length)));
            }
        }
    } else {
        alert('There are no contacts in your vCards that has e-mail addresses.');
    }
}

function send_invite_vcard() {
    var number_of_contacts = selected_email_indices.length;
    
    if (number_of_contacts <= 0) {
        alert('You need to at least select one of your contacts from the list.');
        return false;
    }
    
    var selected_contacts = '';
    for (var i=0; i < number_of_contacts; i++) {
        selected_contacts = selected_contacts + emails_from_vcard[selected_email_indices[i]];
        
        if (i < number_of_contacts-1) {
            selected_contacts = selected_contacts + ',';
        }
    }
    
    var params = 'id=' + id;
    params = params + '&email_addresses=' + selected_contacts;
    params = params + '&message=' + $('vcard_message').value;
    
    var uri = root + "/members/invites_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while sending out the invitation e-mails.');
                return false;
            }
            
            alert('Invitation e-mails were successfully send!');
        },
        onRequest: function(instance) {
            set_status('Sending invitation e-mails...');
        }
    });
    
    request.send(params);
}

function get_contacts() {
    if (!validate()) {
        return;
    }
    
    username = $('username').value;
    service = $('oi_service').value;
    
    var params = 'id=' + id + '&action=get_contacts';
    params = params + '&oi_service=' + $('oi_service').value;
    params = params + '&username=' + $('username').value;
    params = params + '&password=' + $('password').value;
    
    var uri = root + "/members/invites_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            contacts_list.clear();
            
            if (txt == '-1') {
                set_status('An error occured while retrieving your contacts.');
                return false;
            } else if (txt == '-2') {
                set_status('The login username and password are incorrect.');
                return false;
            } else if (txt == '-3') {
                set_status('Unable to retrieve your contacts. Please try again.');
                return false;
            }
            
            $('password').value = '';
            
            var oi_session_id = xml.getElementsByTagName('sessionid');
            var emails = xml.getElementsByTagName('email');
            var names = xml.getElementsByTagName('name');
            var plugin_type = xml.getElementsByTagName('plugin_type');
            var indices = xml.getElementsByTagName('index');
            
            if (emails.length <= 0) {
                alert('You do not have contacts in this service.');
                return false;
            }
            
            for (var i=0; i < indices.length; i++) {
                contacts_list.add_item(names[i].childNodes[0].nodeValue, indices[i].childNodes[0].nodeValue);
            }
            
            $('oi_session_id').value = oi_session_id[0].childNodes[0].nodeValue;
            $('oi_service_name').set('html', $('oi_service').options[$('oi_service').selectedIndex].text.substr(3));
            $('get_contacts_form').setStyle('display', 'none');
            $('send_invite_form').setStyle('display', 'block');
            set_status('');
            contacts_list.show();
        },
        onRequest: function(instance) {
            set_status('Retrieving your contacts...');
        }
    });
    
    request.send(params);
}

function send_invite_smart() {
    var contacts = contacts_list.get_selected_values();
    var number_of_contacts = contacts.length;
    
    if (number_of_contacts <= 0) {
        alert('You need to at least select one of your contacts from the list.');
        return false;
    }
    
    var selected_contacts = '';
    for (var i=0; i < number_of_contacts; i++) {
        var contact_details = contacts[i].split('|');
        selected_contacts = selected_contacts + contact_details[1];
        
        if (i < number_of_contacts-1) {
            selected_contacts = selected_contacts + '|';
        }
    }
    
    var params = 'id=' + id + '&action=smart_send_invites';
    params = params + '&oi_service=' + service;
    params = params + '&oi_session_id=' + $('oi_session_id').value;
    params = params + '&username=' + username;
    params = params + '&selected_contacts=' + selected_contacts;
    params = params + '&message=' + $('smart_message').value;
    
    var uri = root + "/members/invites_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while sending out the invitation e-mails.');
                return false;
            }
            
            alert('Invitation e-mails were successfully send!');
            
            username = '';
            service = '';
            $('oi_session_id').value = '';
            $('oi_service_name').set('html', '');
            $('get_contacts_form').setStyle('display', 'block');
            $('send_invite_form').setStyle('display', 'none');
        },
        onRequest: function(instance) {
            set_status('Sending invitation e-mails...');
        }
    });
    
    request.send(params);
}

function send_invite_manual() {
    if (isEmpty($('email_addresses').value) || isEmpty($('message').value)) {
        alert('You need to enter at least an e-mail address and a short message.');
        return false;
    }
    
    var temp = $('email_addresses').value;
    temp = temp.replace(/\n/g, ' ');
    var emails = temp.split(' ');
    var email_addresses = '';
    for (var i=0; i < emails.length; i++) {
        if (!isEmail(emails[i])) {
            if (!isEmpty(emails[i])) {
                alert('One of your e-mail addresses is invalid- ' + emails[i]);
                return false;
            }
        }
        
        if (!isEmpty(emails[i]) && !duplicated(email_addresses, emails[i])) {
            email_addresses = email_addresses + emails[i];

            if (i < (emails.length - 1)) {
                email_addresses = email_addresses + ',';
            }
        }
    }
    var params = 'id=' + id + '&message=' + $('message').value + '&email_addresses=' + email_addresses;
    
    var uri = root + "/members/invites_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while sending out the invitation e-mails.');
                return false;
            }
            
            alert('Invitation e-mails were successfully send!');
        },
        onRequest: function(instance) {
            set_status('Sending invitation e-mails...');
        }
    });
    
    request.send(params);
}

// ==== invite section ends ====

function set_mouse_events() {
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
    
    $('li_networks').addEvent('mouseover', function() {
        $('li_networks').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_networks').addEvent('mouseout', function() {
        $('li_networks').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_invite').addEvent('mouseover', function() {
        $('li_invite').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_invite').addEvent('mouseout', function() {
        $('li_invite').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_smart_invite').addEvent('mouseover', function() {
        $('li_smart_invite').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_smart_invite').addEvent('mouseout', function() {
        $('li_smart_invite').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_vcard_invite').addEvent('mouseover', function() {
        $('li_vcard_invite').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_vcard_invite').addEvent('mouseout', function() {
        $('li_vcard_invite').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_manual_invite').addEvent('mouseover', function() {
        $('li_manual_invite').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_manual_invite').addEvent('mouseout', function() {
        $('li_manual_invite').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
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
    
    var uri = root + "/members/candidates_action.php";
    var request = new Request({
        url: uri,
        method: 'post'
    });
    
    request.send(params);
}

function hide_show_banner() {
    var params = 'id=' + id + '&action=get_hide_banner';
    
    var uri = root + "/members/candidates_action.php";
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
    set_mini_keywords();
    get_referrals_count();
    get_requests_count();
    get_jobs_employed_count();
    set_mouse_events();
    
    hide_show_banner();
    
    $('delete_candidates').addEvent('click', delete_candidates);
    $('delete_candidates_1').addEvent('click', delete_candidates);
    $('delete_networks').addEvent('click', delete_networks);
    $('delete_networks_1').addEvent('click', delete_networks);
    $('add_candidate').addEvent('click', show_find_candidates);
    $('add_candidate_1').addEvent('click', show_find_candidates);
    $('add_network').addEvent('click', show_network_form);
    $('add_network_1').addEvent('click', show_network_form);
    $('select_all').addEvent('click', select_all_referees);
    $('select_all_networks').addEvent('click', select_all_networks);
    
    $('find_candidates').addEvent('click', find_candidates);
    $('clear_candidates').addEvent('click', function() {
        var html = '<div style="text-align: center;padding-top: 10px;">Please enter either the first or last names, or the e-mail address to find the candidate you want to add.</div>';
        $('div_search_result').set('html', html);
    });
    
    $('li_candidates').addEvent('click', show_candidates);
    $('li_networks').addEvent('click', show_networks);
    $('li_invite').addEvent('click', show_invites);
    
    $('li_smart_invite').addEvent('click', show_smart_invite);
    $('li_manual_invite').addEvent('click', show_manual_invite);
    $('li_vcard_invite').addEvent('click', show_vcard_invite);
    
    $('send').addEvent('click', send_invite_manual);
    $('send_smart').addEvent('click', send_invite_smart);
    $('send_vcard').addEvent('click', send_invite_vcard);
    $('get_contacts').addEvent('click', get_contacts);
    
    $('sort_name').addEvent('click', function() {
        order_by = 'referee_name';
        ascending_or_descending();
        show_candidates();
    });
    
    $('sort_referred_on').addEvent('click', function() {
        order_by = 'member_referees.referred_on';
        ascending_or_descending();
        show_candidates();
    });
    
    $('sort_search_name').addEvent('click', function() {
        find_order_by = 'name';
        find_ascending_or_descending();
        find_candidates();
    });
    
    $('sort_joined_on').addEvent('click', function() {
        find_order_by = 'joined_on';
        find_ascending_or_descending();
        find_candidates();
    });
    
    $('sort_history_by_job').addEvent('click', function() {
        history_order_by = 'jobs.title';
        history_ascending_or_descending();
        show_candidate_histories($('candidate_id').value);
    });
    
    $('sort_history_by_employer').addEvent('click', function() {
        history_order_by = 'employers.name';
        history_ascending_or_descending();
        show_candidate_histories($('candidate_id').value);
    });
    
    $('sort_history_by_referred_on').addEvent('click', function() {
        history_order_by = 'referrals.referred_on';
        history_ascending_or_descending();
        show_candidate_histories($('candidate_id').value);
    });
    
    $('sort_history_by_acknowledged_on').addEvent('click', function() {
        history_order_by = 'referee_acknowledged_on';
        history_ascending_or_descending();
        show_candidate_histories($('candidate_id').value);
    });
    
    $('sort_history_by_employed_on').addEvent('click', function() {
        history_order_by = 'employed_on';
        history_ascending_or_descending();
        show_candidate_histories($('candidate_id').value);
    });
    
    $('sort_history_by_commence_on').addEvent('click', function() {
        history_order_by = 'work_commence_on';
        history_ascending_or_descending();
        show_candidate_histories($('candidate_id').value);
    });
    
    $('sort_history_by_reward').addEvent('click', function() {
        history_order_by = 'total_reward';
        history_ascending_or_descending();
        show_candidate_histories($('candidate_id').value);
    });
    
    $('sort_history_by_paid').addEvent('click', function() {
        history_order_by = 'paid_reward';
        history_ascending_or_descending();
        show_candidate_histories($('candidate_id').value);
    });
    
    if (attr_get_referee_id > 0 && !isEmpty(add_slashes(attr_get_candidate))) {
        show_candidate(attr_get_referee_id, attr_get_candidate);
    } else {
        show_candidates();
    }
    
    var suggest_url = root + '/common/php/search_suggest.php';
    new Autocompleter.Ajax.Json('mini_keywords', suggest_url, {
        'postVar': 'keywords',
        'minLength' : 1,
        'overflow' : true,
        'delay' : 50
    });
}

window.addEvent('domready', onDomReady);
