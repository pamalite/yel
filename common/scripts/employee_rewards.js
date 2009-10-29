var selected_tab = 'li_new';
var order_by = 'employed_on';
var order = 'asc';
var partially_paid_order_by = 'last_paid_on';
var partially_paid_order = 'desc';
var fully_paid_order_by = 'fully_paid_on';
var fully_paid_order = 'desc';
var payments_order_by = 'referral_rewards.paid_on';
var payments_order = 'asc';

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

function partially_paid_ascending_or_descending() {
    if (partially_paid_order == 'desc') {
        partially_paid_order = 'asc';
    } else {
        partially_paid_order = 'desc';
    }
}

function fully_paid_ascending_or_descending() {
    if (fully_paid_order == 'desc') {
        fully_paid_order = 'asc';
    } else {
        fully_paid_order = 'desc';
    }
}

function payments_ascending_or_descending() {
    if (payments_order == 'desc') {
        payments_order = 'asc';
    } else {
        payments_order = 'desc';
    }
}

function show_invoice_page(_invoice_id) {
    var popup = window.open('invoice.php?id=' + _invoice_id, '', 'scrollbars');
    
    if (!popup) {
        alert('Popup blocker was detected. Please allow pop-up windows for YellowElevator.com and try again.');
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
    
    var tmp = $('reward').get('html');
    var raw_reward = tmp.split(' ');
    var calculated_reward = raw_reward[0];
    if (isEmpty($('amount').value) || parseFloat($('amount').value) <= 0.00) {
        alert('Amount cannot be empty or less than or equals to 0.');
        return false;
    } else if (parseFloat($('amount').value) > parseFloat(calculated_reward)) {
        alert('Amount cannot be more than the calculated reward.');
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
    
    var uri = root + "/employees/rewards_action.php";
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
            if (selected_tab == 'li_new') {
                show_new_rewards();
            } else if (selected_tab == 'li_partially_paid') {
                show_partially_paid_rewards();
            } else {
                show_payments();
            }
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
    $('li_partially_paid').setStyle('border', '1px solid #0000FF');
    $('li_fully_paid').setStyle('border', '1px solid #0000FF');
    $('div_partially_paid_rewards').setStyle('display', 'none');
    $('div_fully_paid_rewards').setStyle('display', 'none');
    $('div_new_rewards').setStyle('display', 'block');
    $('div_payments').setStyle('display', 'none');
    
    var params = 'id=0&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employees/rewards_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading new rewards.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no new rewards at the moment.</div>';
            } else {
                var referrals = xml.getElementsByTagName('referral');
                var invoices = xml.getElementsByTagName('invoice');
                var padded_invoices = xml.getElementsByTagName('padded_invoice');
                var employers = xml.getElementsByTagName('employer');
                var currencies = xml.getElementsByTagName('currency');
                var member_ids = xml.getElementsByTagName('member_id');
                var jobs = xml.getElementsByTagName('title');
                var members = xml.getElementsByTagName('member');
                var employed_ons = xml.getElementsByTagName('formatted_employed_on');
                var employed_on_timestamps = xml.getElementsByTagName('employed_on');
                var coe_received_ons = xml.getElementsByTagName('formatted_contract_received_on');
                var referee_confirmed_ons = xml.getElementsByTagName('formatted_referee_confirmed_on');
                var rewards = xml.getElementsByTagName('total_reward');
                
                for (var i=0; i < referrals.length; i++) {
                    var invoice = invoices[i].childNodes[0].nodeValue;
                    var referral = referrals[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ referral + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="invoice"><a class="no_link" onClick="show_invoice_page(\'' + invoice + '\')">' + padded_invoices[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title">' + jobs[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="member">' + members[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + employed_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="reward"><a class="no_link" onClick="show_payment_plan(\'' + rewards[i].childNodes[0].nodeValue + '\', \'' + employed_on_timestamps[i].childNodes[0].nodeValue + '\', \'' + employed_ons[i].childNodes[0].nodeValue + '\', \'' + currencies[i].childNodes[0].nodeValue + '\');">' + currencies[i].childNodes[0].nodeValue + ' ' + rewards[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="action"><a class="no_link" onClick="show_payment_form(\'' + referral + '\', \'' + rewards[i].childNodes[0].nodeValue + '\', \'' + member_ids[i].childNodes[0].nodeValue + '\', \'' + currencies[i].childNodes[0].nodeValue + '\');">Pay Referrer</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_new_rewards_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading new rewards...');
        }
    });
    
    request.send(params);
}

function show_partially_paid_rewards() {
    selected_tab = 'li_partially_paid';
    $(selected_tab).setStyle('border', '1px solid #CCCCCC');
    $('li_new').setStyle('border', '1px solid #0000FF');
    $('li_fully_paid').setStyle('border', '1px solid #0000FF');
    $('div_partially_paid_rewards').setStyle('display', 'block');
    $('div_fully_paid_rewards').setStyle('display', 'none');
    $('div_new_rewards').setStyle('display', 'none');
    $('div_payments').setStyle('display', 'none');
    
    var params = 'id=0&action=get_partially_paid&order_by=' + partially_paid_order_by + ' ' + partially_paid_order;
    
    var uri = root + "/employees/rewards_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading partially rewards.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no partially paid rewards at the moment.</div>';
            } else {
                var referrals = xml.getElementsByTagName('referral');
                var invoices = xml.getElementsByTagName('invoice');
                var padded_invoices = xml.getElementsByTagName('padded_invoice');
                var employers = xml.getElementsByTagName('employer');
                var currencies = xml.getElementsByTagName('currency');
                var member_ids = xml.getElementsByTagName('member_id');
                var jobs = xml.getElementsByTagName('title');
                var members = xml.getElementsByTagName('member');
                var employed_ons = xml.getElementsByTagName('formatted_employed_on');
                var employed_on_timestamps = xml.getElementsByTagName('employed_on');
                var coe_received_ons = xml.getElementsByTagName('formatted_contract_received_on');
                var referee_confirmed_ons = xml.getElementsByTagName('formatted_referee_confirmed_on');
                var last_paid_ons = xml.getElementsByTagName('formatted_last_paid_on');
                var paids = xml.getElementsByTagName('paid');
                var rewards = xml.getElementsByTagName('total_reward');
                
                for (var i=0; i < referrals.length; i++) {
                    var invoice = invoices[i].childNodes[0].nodeValue;
                    var referral = referrals[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ referral + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="invoice"><a class="no_link" onClick="show_invoice_page(\'' + invoice + '\')">' + padded_invoices[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title">' + jobs[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="member">' + members[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + employed_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + last_paid_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="reward">' + currencies[i].childNodes[0].nodeValue + ' ' + paids[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="reward"><a class="no_link" onClick="show_payment_plan(\'' + rewards[i].childNodes[0].nodeValue + '\', \'' + employed_on_timestamps[i].childNodes[0].nodeValue + '\', \'' + employed_ons[i].childNodes[0].nodeValue + '\', \'' + currencies[i].childNodes[0].nodeValue + '\');">' + currencies[i].childNodes[0].nodeValue + ' ' + rewards[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="action"><a class="no_link" onClick="show_payments_with(\'' + referral + '\', \'' + rewards[i].childNodes[0].nodeValue + '\', \'' + member_ids[i].childNodes[0].nodeValue + '\', \'' + jobs[i].childNodes[0].nodeValue + '\', \'' + employers[i].childNodes[0].nodeValue + '\', \'' + currencies[i].childNodes[0].nodeValue + '\')">Payments</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_partially_paid_rewards_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading partially paid rewards...');
        }
    });
    
    request.send(params);
}

function show_fully_paid_rewards() {
    selected_tab = 'li_fully_paid';
    $(selected_tab).setStyle('border', '1px solid #CCCCCC');
    $('li_new').setStyle('border', '1px solid #0000FF');
    $('li_partially_paid').setStyle('border', '1px solid #0000FF');
    $('div_partially_paid_rewards').setStyle('display', 'none');
    $('div_fully_paid_rewards').setStyle('display', 'block');
    $('div_new_rewards').setStyle('display', 'none');
    $('div_payments').setStyle('display', 'none');
    
    var params = 'id=0&action=get_fully_paid&order_by=' + fully_paid_order_by + ' ' + fully_paid_order;
    
    var uri = root + "/employees/rewards_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading fully rewards.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no fully paid rewards at the moment.</div>';
            } else {
                var referrals = xml.getElementsByTagName('referral');
                var invoices = xml.getElementsByTagName('invoice');
                var padded_invoices = xml.getElementsByTagName('padded_invoice');
                var employers = xml.getElementsByTagName('employer');
                var currencies = xml.getElementsByTagName('currency');
                var member_ids = xml.getElementsByTagName('member_id');
                var jobs = xml.getElementsByTagName('title');
                var members = xml.getElementsByTagName('member');
                var employed_ons = xml.getElementsByTagName('formatted_employed_on');
                var employed_on_timestamps = xml.getElementsByTagName('employed_on');
                var coe_received_ons = xml.getElementsByTagName('formatted_contract_received_on');
                var referee_confirmed_ons = xml.getElementsByTagName('formatted_referee_confirmed_on');
                var fully_paid_ons = xml.getElementsByTagName('formatted_fully_paid_on');
                var paids = xml.getElementsByTagName('paid');
                var rewards = xml.getElementsByTagName('total_reward');
                
                for (var i=0; i < referrals.length; i++) {
                    var invoice = invoices[i].childNodes[0].nodeValue;
                    var referral = referrals[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ referral + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="invoice"><a class="no_link" onClick="show_invoice_page(\'' + invoice + '\')">' + padded_invoices[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title">' + jobs[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="member">' + members[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + employed_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + fully_paid_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="reward">' + currencies[i].childNodes[0].nodeValue + ' ' + paids[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="reward"><a class="no_link" onClick="show_payment_plan(\'' + rewards[i].childNodes[0].nodeValue + '\', \'' + employed_on_timestamps[i].childNodes[0].nodeValue + '\', \'' + employed_ons[i].childNodes[0].nodeValue + '\', \'' + currencies[i].childNodes[0].nodeValue + '\');">' + currencies[i].childNodes[0].nodeValue + ' ' + rewards[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="action"><a class="no_link" onClick="show_payments_with(\'' + referral + '\', \'' + rewards[i].childNodes[0].nodeValue + '\', \'' + member_ids[i].childNodes[0].nodeValue + '\', \'' + jobs[i].childNodes[0].nodeValue + '\', \'' + employers[i].childNodes[0].nodeValue + '\', \'' + currencies[i].childNodes[0].nodeValue + '\')">Payments</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_fully_paid_rewards_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading fully paid rewards...');
        }
    });
    
    request.send(params);
}

function show_payments_with(_referral, _reward, _member_id, _job_title, _employer_name, _currency) {
    payments_referral = _referral;
    payments_reward = _reward;
    payments_member_id = _member_id;
    payments_job_title = _job_title;
    payments_employer_name = _employer_name;
    payments_currency = _currency;
    
    show_payments();
}

function show_payments() {
    display_member_name_in('member_name', payments_member_id);
    //$('member_name').set('html', _member);
    $('total_reward').set('html', payments_currency + ' ' + payments_reward);
    $('job_title').set('html', payments_job_title);
    $('job_employer').set('html', payments_employer_name);
    $('payment_info.currency').set('html', payments_currency);
    $('payment_info.total_paid.currency').set('html', payments_currency);
    $('total_amount').set('html', '0.00');
    
    selected_tab = 'div_payments';
    $('div_partially_paid_rewards').setStyle('display', 'none');
    $('div_fully_paid_rewards').setStyle('display', 'none');
    $('div_new_rewards').setStyle('display', 'none');
    $('div_payments').setStyle('display', 'block');
    
    var params = 'id=' + payments_referral + '&action=get_payment_history';
    params = params + '&order_by=' + payments_order_by + ' ' + payments_order;
    
    var uri = root + "/employees/rewards_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading payment history.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no payments made yet.</div>';
            } else {
                var ids = xml.getElementsByTagName('id');
                var rewards = xml.getElementsByTagName('reward');
                var paid_ons = xml.getElementsByTagName('formatted_paid_on');
                var payment_modes = xml.getElementsByTagName('paid_through');
                var banks = xml.getElementsByTagName('bank');
                var cheques = xml.getElementsByTagName('cheque');
                var receipts = xml.getElementsByTagName('receipt');
                var total_reward = 0.00;
                
                for (var i=0; i < ids.length; i++) {
                    var id = ids[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="date">' + paid_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    var payment_mode = 'Not Applicable';
                    switch (payment_modes[i].childNodes[0].nodeValue) {
                        case 'CSH':
                            payment_mode = 'Cash';
                            break;
                        case 'CHQ':
                            payment_mode = 'Cheque';
                            break;
                        case 'CDB':
                            payment_mode = 'Bank on-behalf';
                            break;
                        case 'IBT':
                            payment_mode = 'Bank Transfer';
                            break;
                    }
                    
                    html = html + '<td class="mode">' + payment_mode + '</td>' + "\n";
                    
                    var bank = 'Not Applicable';
                    if (banks[i].childNodes.length > 0) {
                        bank = banks[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="bank">' + bank + '</td>' + "\n";
                    
                    var cheque = 'Not Applicable';
                    if (cheques[i].childNodes.length > 0) {
                        cheque = cheques[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="cheque">' + cheque + '</td>' + "\n";
                    
                    var receipt = 'Not Applicable';
                    if (receipts[i].childNodes.length > 0) {
                        receipt = receipts[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="receipt">' + receipt + '</td>' + "\n";
                    html = html + '<td class="reward">' + rewards[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '</tr>' + "\n";
                    
                    total_reward = parseFloat(total_reward) + parseFloat(rewards[i].childNodes[0].nodeValue);
                }
                html = html + '</table>';
                
                total_reward = (Math.round(total_reward * 100) / 100);
                $('total_amount').set('html', total_reward);
            }
            
            
            var button = '<input type="button" value="Confirm New Payment" onClick="show_payment_form(\'' + payments_referral + '\', \'' + (Math.round((payments_reward - total_reward) * 100) / 100) + '\', \'' + payments_member_id + '\', \'' + payments_currency + '\')" />';
            if (parseFloat(total_reward) >= parseFloat(payments_reward)) {
                button = '<input type="button" value="Confirm New Payment" disabled />';
            }
            
            $('payments_button').set('html', button);
            $('payments_button_1').set('html', button);
            $('div_payments_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading payment history...');
        }
    });
    
    request.send(params);
}

function close_payment_plan() {
    $('div_payment_plan').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_payment_plan(_reward, _employed_on, _formatted_employed_on, _currency) {
    $('div_blanket').setStyle('display', 'block');
    $('payment_plan.currency').set('html', _currency);
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_payment_plan').getStyle('height'));
    var div_width = parseInt($('div_payment_plan').getStyle('width'));
    
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
    
    $('div_payment_plan').setStyle('top', ((window_height - div_height) / 2));
    $('div_payment_plan').setStyle('left', ((window_width - div_width) / 2));
    
    var params = 'id=0&action=get_payment_plan';
    params = params + '&total_reward=' + _reward + '&employed_on=' + _employed_on;
    
    var uri = root + "/employees/rewards_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading payment plan.');
                return false;
            }
            
            $('plan_reward').set('html', _currency + ' ' + _reward);
            $('plan_employed_on').set('html', _formatted_employed_on);
            
            var due_days = xml.getElementsByTagName('due_day');
            var due_ons = xml.getElementsByTagName('due_on');
            var amounts = xml.getElementsByTagName('amount');
            
            var html = '<table id="list" class="list">';
            for (var i=0; i < due_days.length; i++) {
                html = html + '<tr id="'+ i + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                html = html + '<td class="days">' + due_days[i].childNodes[0].nodeValue + '</td>' + "\n";
                html = html + '<td class="date">' + due_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                html = html + '<td class="amount">' + amounts[i].childNodes[0].nodeValue + '</td>' + "\n";
                html = html + '</tr>' + "\n";
            }
            html = html + '</table>';
            
            $('payment_plan_list').set('html', html);
            $('div_payment_plan').setStyle('display', 'block');
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading payment plan...');
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
    
    $('li_partially_paid').addEvent('mouseover', function() {
        $('li_partially_paid').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_partially_paid').addEvent('mouseout', function() {
        $('li_partially_paid').setStyles({
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
    get_employee_rewards_count();
    get_employee_tokens_count();
    
    $('li_new').addEvent('click', show_new_rewards);
    $('li_partially_paid').addEvent('click', show_partially_paid_rewards);
    $('li_fully_paid').addEvent('click', show_fully_paid_rewards);
    
    $('sort_invoice').addEvent('click', function() {
        order_by = 'invoice';
        ascending_or_descending();
        show_new_rewards();
    });
    
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
        order_by = 'member';
        ascending_or_descending();
        show_new_rewards();
    });
    
    $('sort_employed_on').addEvent('click', function() {
        order_by = 'employed_on';
        ascending_or_descending();
        show_new_rewards();
    });
    
    $('sort_reward').addEvent('click', function() {
        order_by = 'total_reward';
        ascending_or_descending();
        show_new_rewards();
    });
    
    $('sort_partially_paid_invoice').addEvent('click', function() {
        partially_paid_order_by = 'invoice';
        partially_paid_ascending_or_descending();
        show_partially_paid_rewards();
    });
    
    $('sort_partially_paid_employer').addEvent('click', function() {
        partially_paid_order_by = 'employer';
        partially_paid_ascending_or_descending();
        show_partially_paid_rewards();
    });
    
    $('sort_partially_paid_title').addEvent('click', function() {
        partially_paid_order_by = 'title';
        partially_paid_ascending_or_descending();
        show_partially_paid_rewards();
    });
    
    $('sort_partially_paid_member').addEvent('click', function() {
        partially_paid_order_by = 'member';
        partially_paid_ascending_or_descending();
        show_partially_paid_rewards();
    });
    
    $('sort_partially_paid_employed_on').addEvent('click', function() {
        partially_paid_order_by = 'employed_on';
        partially_paid_ascending_or_descending();
        show_partially_paid_rewards();
    });
    
    $('sort_partially_paid_reward').addEvent('click', function() {
        partially_paid_order_by = 'total_reward';
        partially_paid_ascending_or_descending();
        show_partially_paid_rewards();
    });
    
    $('sort_partially_paid_paid').addEvent('click', function() {
        partially_paid_order_by = 'paid';
        partially_paid_ascending_or_descending();
        show_partially_paid_rewards();
    });
    
    $('sort_partially_paid_last_paid_on').addEvent('click', function() {
        partially_paid_order_by = 'last_paid_on';
        partially_paid_ascending_or_descending();
        show_partially_paid_rewards();
    });
    
    $('sort_fully_paid_invoice').addEvent('click', function() {
        fully_paid_order_by = 'invoice';
        fully_paid_ascending_or_descending();
        show_fully_paid_rewards();
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
        fully_paid_order_by = 'member';
        fully_paid_ascending_or_descending();
        show_fully_paid_rewards();
    });
    
    $('sort_fully_paid_employed_on').addEvent('click', function() {
        fully_paid_order_by = 'employed_on';
        fully_paid_ascending_or_descending();
        show_fully_paid_rewards();
    });
    
    $('sort_fully_paid_reward').addEvent('click', function() {
        fully_paid_order_by = 'total_reward';
        fully_paid_ascending_or_descending();
        show_fully_paid_rewards();
    });
    
    $('sort_fully_paid_paid').addEvent('click', function() {
        fully_paid_order_by = 'paid';
        fully_paid_ascending_or_descending();
        show_fully_paid_rewards();
    });
    
    $('sort_fully_paid_fully_paid_on').addEvent('click', function() {
        fully_paid_order_by = 'fully_paid_on';
        fully_paid_ascending_or_descending();
        show_fully_paid_rewards();
    });
    
    $('sort_payments_paid_on').addEvent('click', function() {
        payments_order_by = 'referral_rewards.paid_on';
        payments_ascending_or_descending();
        show_payments();
    });
    
    $('sort_payments_paid_on').addEvent('click', function() {
        payments_order_by = 'referral_rewards.paid_on';
        payments_ascending_or_descending();
        show_payments();
    });
    
    $('sort_payments_payment_mode').addEvent('click', function() {
        payments_order_by = 'referral_rewards.paid_through';
        payments_ascending_or_descending();
        show_payments();
    });
    
    $('sort_payments_bank').addEvent('click', function() {
        payments_order_by = 'bank';
        payments_ascending_or_descending();
        show_payments();
    });
    
    $('sort_payments_cheque').addEvent('click', function() {
        payments_order_by = 'referral_rewards.cheque';
        payments_ascending_or_descending();
        show_payments();
    });
    
    $('sort_payments_receipt').addEvent('click', function() {
        payments_order_by = 'referral_rewards.receipt';
        payments_ascending_or_descending();
        show_payments();
    });
    
    show_new_rewards();
}

window.addEvent('domready', onDomReady);
