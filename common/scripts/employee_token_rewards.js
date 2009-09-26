var selected_tab = 'li_new';
var order_by = 'employed_on';
var order = 'asc';
var fully_paid_order_by = 'fully_paid_on';
var fully_paid_order = 'desc';

var payments_referral = '0';
var payments_reward = '0.00';
var payments_member_id = '';
var payments_job_title = '';
var payments_employer_name = '';
var payments_currency = '???';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function fully_paid_ascending_or_descending() {
    if (fully_paid_order == 'desc') {
        fully_paid_order = 'asc';
    } else {
        fully_paid_order = 'desc';
    }
}

function display_member_name_in(placeholder, _member_id) {
    var params = 'id=' + _member_id + '&action=get_member_name';
    
    var uri = root + "/employees/rewards_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            
            var names = xml.getElementsByTagName('fullname');
            
            $(placeholder).set('html', names[0].childNodes[0].nodeValue);
        },
        onRequest: function(instance) {
            $(placeholder).set('html', 'Loading name...');
        }
    });
     
    request.send(params);
}

function list_accounts_in(placeholder, used_id, used_name, member) {
    var params = 'id=' + member + '&action=get_banks';
    
    var uri = root + "/employees/rewards_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while retrieving bank accounts.');
                return false;
            }
            
            var html = '<select id="' + used_id + '" name="' + used_name + '">' + "\n";
            html = html + '<option value="0" selected>Select a bank account</option>' + "\n";
            html = html + '<option value="0" disabled>&nbsp;</option>' + "\n";
            
            if (txt == '0') {
                alert('This member has not setup a bank account yet.');
            } else {
                var ids = xml.getElementsByTagName('id');
                var banks = xml.getElementsByTagName('bank');
                var accounts = xml.getElementsByTagName('account');
                
                for (var i = 0; i < ids.length; i++) {
                    html = html + '<option value="' + ids[i].childNodes[0].nodeValue + '">' + banks[i].childNodes[0].nodeValue + ' (' + accounts[i].childNodes[0].nodeValue +  ')</option>' + "\n";
                }
            }
            html = html + '</select>' + "\n";
            
            $(placeholder).set('html', html);
        },
        onRequest: function(instance) {
            $(placeholder).set('html', 'Loading accounts...');
        }
    });
     
    request.send(params);
}

function confirm_payment() {
    if ($('referral_id').value == '0' || isEmpty($('referral_id').value)) {
        alert('This payment is corrupted');
        return false;
    }
    
    var payment_mode = $('payment_mode').options[$('payment_mode').selectedIndex].value;
    var bank = $('accounts_dropdown').options[$('accounts_dropdown').selectedIndex].value;
    
    if ((payment_mode == 'IBT' || payment_mode == 'CDB') && bank == '0') {
        alert('You have chosen bank transfer or bank on-behalf. However, you have not choose which bank account the reward was transferred into.');
        return false;
    }
    
    if (payment_mode == 'CHQ' && (isEmpty($('cheque').value) || $('cheque').value == '0')) {
        alert('You have chosen cheque. However, you have not entered the cheque number used.');
        return false;
    }
    
    var params = 'id=' + $('referral_id').value + '&action=confirm_payment';
    params = params + '&amount=' + $('amount').value;
    params = params + '&payment_mode=' + payment_mode;
    params = params + '&bank=' + bank;
    params = params + '&cheque=' + $('cheque').value;
    params = params + '&receipt=' + $('receipt').value;
    
    var uri = root + "/employees/token_rewards_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while confirming payment.');
                return false;
            }
            
            set_status('');
            close_payment_form();
            show_new_rewards();
        },
        onRequest: function(instance) {
            set_status('Confirming payment...');
        }
    });
     
    request.send(params);
}

function close_payment_form() {
    $('referral_id').value = '0';
    $('div_payment_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_payment_form(_referral, _reward, _member_id, _currency) {
    $('referral_id').value = _referral;
    $('reward').set('html', _currency + ' ' + _reward);
    $('payment_form.currency').set('html', _currency);
    $('amount').value = _reward;
    $('amount').disabled = true;
    display_member_name_in('member', _member_id);
    //$('member').set('html', _member);
    
    list_accounts_in('accounts_list', 'accounts_dropdown', 'accounts_dropdown', _member_id);
    $('div_blanket').setStyle('display', 'block');
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_payment_form').getStyle('height'));
    var div_width = parseInt($('div_payment_form').getStyle('width'));
    
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
    
    $('div_payment_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_payment_form').setStyle('left', ((window_width - div_width) / 2));
    $('div_payment_form').setStyle('display', 'block');
}

function show_new_rewards() {
    selected_tab = 'li_new';
    $(selected_tab).setStyle('border', '1px solid #CCCCCC');
    $('li_fully_paid').setStyle('border', '1px solid #0000FF');
    $('div_fully_paid_rewards').setStyle('display', 'none');
    $('div_new_rewards').setStyle('display', 'block');
    
    var params = 'id=0&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employees/token_rewards_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading new tokens.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no new tokens at the moment.</div>';
            } else {
                var referrals = xml.getElementsByTagName('referral');
                var employers = xml.getElementsByTagName('employer');
                var currencies = xml.getElementsByTagName('currency');
                var member_ids = xml.getElementsByTagName('candidate_id');
                var jobs = xml.getElementsByTagName('title');
                var members = xml.getElementsByTagName('candidate');
                var employed_ons = xml.getElementsByTagName('formatted_employed_on');
                var employed_on_timestamps = xml.getElementsByTagName('employed_on');
                var referee_confirmed_ons = xml.getElementsByTagName('formatted_referee_confirmed_on');
                var rewards = xml.getElementsByTagName('total_token_reward');
                
                for (var i=0; i < referrals.length; i++) {
                    var referral = referrals[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ referral + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title">' + jobs[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="member">' + members[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + employed_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    var referee_confirmed_on = '<span style="vertical-align: middle; font-size: 9pt; color: #666666;">Pending...</span>';
                    if (referee_confirmed_ons[i].childNodes.length > 0) {
                        referee_confirmed_on = referee_confirmed_ons[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="date">' + referee_confirmed_on + '</td>' + "\n";
                    
                    html = html + '<td class="reward">' + currencies[i].childNodes[0].nodeValue + ' ' + rewards[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="action"><a class="no_link" onClick="show_payment_form(\'' + referral + '\', \'' + rewards[i].childNodes[0].nodeValue + '\', \'' + member_ids[i].childNodes[0].nodeValue + '\', \'' + currencies[i].childNodes[0].nodeValue + '\');">Pay Candidate</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_new_rewards_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading new tokens...');
        }
    });
    
    request.send(params);
}

function show_fully_paid_rewards() {
    selected_tab = 'li_fully_paid';
    $(selected_tab).setStyle('border', '1px solid #CCCCCC');
    $('li_new').setStyle('border', '1px solid #0000FF');
    $('div_fully_paid_rewards').setStyle('display', 'block');
    $('div_new_rewards').setStyle('display', 'none');
    
    var params = 'id=0&action=get_fully_paid&order_by=' + fully_paid_order_by + ' ' + fully_paid_order;
    
    var uri = root + "/employees/token_rewards_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading fully paid tokens.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no fully paid tokens at the moment.</div>';
            } else {
                var referrals = xml.getElementsByTagName('referral');
                var employers = xml.getElementsByTagName('employer');
                var currencies = xml.getElementsByTagName('currency');
                var member_ids = xml.getElementsByTagName('candidate_id');
                var jobs = xml.getElementsByTagName('title');
                var members = xml.getElementsByTagName('candidate');
                var employed_ons = xml.getElementsByTagName('formatted_employed_on');
                var employed_on_timestamps = xml.getElementsByTagName('employed_on');
                var referee_confirmed_ons = xml.getElementsByTagName('formatted_referee_confirmed_on');
                var fully_paid_ons = xml.getElementsByTagName('formatted_fully_paid_on');
                var rewards = xml.getElementsByTagName('total_token_reward');
                var banks = xml.getElementsByTagName('bank');
                var accounts = xml.getElementsByTagName('account');
                var paid_throughs = xml.getElementsByTagName('paid_through');
                var cheques = xml.getElementsByTagName('cheque');
                var receipts = xml.getElementsByTagName('receipt');
                
                for (var i=0; i < referrals.length; i++) {
                    var referral = referrals[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ referral + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title">' + jobs[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="member">' + members[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + employed_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    var referee_confirmed_on = '<span style="vertical-align: middle; font-size: 9pt; color: #666666;">Pending...</span>';
                    if (referee_confirmed_ons[i].childNodes.length > 0) {
                        referee_confirmed_on = referee_confirmed_ons[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="date">' + referee_confirmed_on + '</td>' + "\n";
                    
                    html = html + '<td class="date">' + fully_paid_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="reward">' + currencies[i].childNodes[0].nodeValue + ' ' + rewards[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    var payment_details = '';
                    if (receipts[i].childNodes.length > 0) {
                        payment_details = '<span style="font-weight: bold;">Receipt:</span> ' + receipts[i].childNodes[0].nodeValue;
                    }
                    
                    if (cheques[i].childNodes.length > 0) {
                        payment_details = payment_details + ((!isEmpty(payment_details)) ? '<br/>' : '');
                        payment_details = payment_details + '<span style="font-weight: bold;">Cheque:</span> ' + cheques[i].childNodes[0].nodeValue;
                    }
                    
                    if (accounts[i].childNodes.length > 0) {
                        payment_details = payment_details + ((!isEmpty(payment_details)) ? '<br/>' : '');
                        payment_details = payment_details + '<span style="font-weight: bold;">Bank:</span> ' + accounts[i].childNodes[0].nodeValue + ' (' + banks[i].childNodes[0].nodeValue + ')';
                    }
                    
                    if (isEmpty(payment_details)) {
                        payment_details = 'Error!';
                    }
                    
                    html = html + '<td class="payment_details">' + payment_details + '</td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_fully_paid_rewards_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading fully paid token rewards...');
        }
    });
    
    request.send(params);
}

function set_mouse_events() {
    $('li_new').addEvent('mouseover', function() {
        $('li_new').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_new').addEvent('mouseout', function() {
        $('li_new').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_fully_paid').addEvent('mouseover', function() {
        $('li_fully_paid').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_fully_paid').addEvent('mouseout', function() {
        $('li_fully_paid').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
}

function onDomReady() {
    set_root();
    set_mouse_events();
    get_unapproved_photos_count();
    
    $('li_new').addEvent('click', show_new_rewards);
    $('li_fully_paid').addEvent('click', show_fully_paid_rewards);
    
    $('sort_employer').addEvent('click', function() {
        order_by = 'employer';
        ascending_or_descending();
        show_new_rewards();
    });
    
    $('sort_title').addEvent('click', function() {
        order_by = 'title';
        ascending_or_descending();
        show_new_rewards();
    });
    
    $('sort_member').addEvent('click', function() {
        order_by = 'candidate';
        ascending_or_descending();
        show_new_rewards();
    });
    
    $('sort_employed_on').addEvent('click', function() {
        order_by = 'employed_on';
        ascending_or_descending();
        show_new_rewards();
    });
    
    $('sort_confirmed_on').addEvent('click', function() {
        order_by = 'referee_confirmed_hired_on';
        ascending_or_descending();
        show_new_rewards();
    });
    
    $('sort_reward').addEvent('click', function() {
        order_by = 'total_token_reward';
        ascending_or_descending();
        show_new_rewards();
    });
    
    $('sort_fully_paid_employer').addEvent('click', function() {
        fully_paid_order_by = 'employer';
        fully_paid_ascending_or_descending();
        show_fully_paid_rewards();
    });
    
    $('sort_fully_paid_title').addEvent('click', function() {
        fully_paid_order_by = 'title';
        fully_paid_ascending_or_descending();
        show_fully_paid_rewards();
    });
    
    $('sort_fully_paid_member').addEvent('click', function() {
        fully_paid_order_by = 'candidate';
        fully_paid_ascending_or_descending();
        show_fully_paid_rewards();
    });
    
    $('sort_fully_paid_employed_on').addEvent('click', function() {
        fully_paid_order_by = 'employed_on';
        fully_paid_ascending_or_descending();
        show_fully_paid_rewards();
    });
    
    $('sort_fully_paid_confirmed_on').addEvent('click', function() {
        fully_paid_order_by = 'referee_confirmed_hired_on';
        fully_paid_ascending_or_descending();
        show_fully_paid_rewards();
    });
    
    $('sort_fully_paid_reward').addEvent('click', function() {
        fully_paid_order_by = 'total_token_reward';
        fully_paid_ascending_or_descending();
        show_fully_paid_rewards();
    });
    
    $('sort_fully_paid_fully_paid_on').addEvent('click', function() {
        fully_paid_order_by = 'fully_paid_on';
        fully_paid_ascending_or_descending();
        show_fully_paid_rewards();
    });
    
    show_new_rewards();
}

window.addEvent('domready', onDomReady);
