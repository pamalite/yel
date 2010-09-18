var order_by = 'referrals.employed_on';
var order = 'asc';
var paid_order_by = 'invoices.paid_on';
var paid_order = 'desc';
var banks = new Array();

function Bank(_id, _bank, _account) {
    this.id = _id;
    this.bank = _bank;
    this.account = _account;
}

function get_banks(_member_id) {
    banks = new Array();
    
    var params = 'id=' + _member_id + '&action=get_banks';
    
    var uri = root + "/employees/rewards_action.php";
    var request = new Request({
        url: uri,
        async: false,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == '0') {
                return 0;
            }
            
            var ids = xml.getElementsByTagName('id');
            var banks = xml.getElementsByTagName('bank');
            var accounts = xml.getElementsByTagName('accounts');
            
            for (var i=0; i < ids.length; i++) {
                var a_bank = new Bank(ids[i].childNodes[0].nodeValue, 
                                      banks[i].childNodes[0].nodeValue,
                                      accounts[i].childNodes[0].nodeValue);
                banks[banks.length] = a_bank;
            }
            
            return true;
        },
        onRequest: function(instance) {
            set_status('Loading banks...');
        }
    });
    
    request.send(params);
}

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function paid_ascending_or_descending() {
    if (paid_order == 'desc') {
        paid_order = 'asc';
    } else {
        paid_order = 'desc';
    }
}

function sort_by(_table, _column) {
    switch (_table) {
        case 'new_rewards':
            order_by = _column;
            ascending_or_descending();
            update_new_rewards_list();
            break;
        case 'paid_rewards':
            paid_order_by = _column;
            paid_ascending_or_descending();
            update_paid_rewards_list();
            break;
    }
}

function show_invoice_page(_invoice_id) {
    var popup = window.open('invoice.php?id=' + _invoice_id, '', 'scrollbars');
    
    if (!popup) {
        alert('Popup blocker was detected. Please allow pop-up windows for YellowElevator.com and try again.');
    }
}

function update_new_rewards_list() {
    var params = 'id=0&action=get_new_rewards&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employees/rewards_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            //set_status('<pre>' + txt + '</pre>');
            //return;
            if (txt == 'ko') {
                alert('An error occured while loading new rewards.');
                return false;
            }
            
            if (txt == '0') {
                $('div_new_rewards').set('html', '<div class="empty_results">No rewards being offered at this moment.</div>');
            } else {
                var ids = xml.getElementsByTagName('referral');
                var invoices = xml.getElementsByTagName('invoice');
                var padded_ids = xml.getElementsByTagName('padded_invoice');
                var employers = xml.getElementsByTagName('employer');
                var member_ids = xml.getElementsByTagName('member_id');
                var members = xml.getElementsByTagName('member');
                var phone_nums = xml.getElementsByTagName('phone_num');
                var total_rewards = xml.getElementsByTagName('total_reward');
                var currencies = xml.getElementsByTagName('currency');
                var employed_ons = xml.getElementsByTagName('formatted_employed_on');
                var jobs = xml.getElementsByTagName('title');
                
                var new_rewards_table = new FlexTable('new_rewards_table', 'new_rewards');

                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('new_rewards', 'referrals.employed_on');\">Employed On</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('new_rewards', 'jobs.title');\">Job</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('new_rewards', 'members.lastname');\">Referrer</a>", '', 'header'));
                header.set(3, new Cell('Receipt', '', 'header'));
                header.set(4, new Cell('Reward', '', 'header'));
                header.set(5, new Cell('Actions', '', 'header action'));
                new_rewards_table.set(0, header);
                
                for (var i=0; i < ids.length; i++) {
                    var row = new Row('');
                    
                    row.set(0, new Cell(employed_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var job = jobs[i].childNodes[0].nodeValue;
                    job = job + '<div class="small_contact"><span class="contact_label">Employer:</span> ' + employers[i].childNodes[0].nodeValue + '</div>';
                    row.set(1, new Cell(job, '', 'cell'));
                    
                    var referrer = members[i].childNodes[0].nodeValue;
                    if (member_ids[i].childNodes[0].nodeValue.substr(0, 5) == 'team' && 
                        member_ids[i].childNodes[0].nodeValue.substr(7) == '@yellowelevator.com') {
                        referrer = 'Yellow Elevator';
                    } else {
                        referrer = referrer + '<div class="small_contact"><span class="contact_label">Tel.:</span> ' + phone_nums[i].childNodes[0].nodeValue + '</div>';
                        referrer = referrer +  '<div class="small_contact"><span class="contact_label">Email: </span><a href="mailto:' + member_ids[i].childNodes[0].nodeValue + '">' + member_ids[i].childNodes[0].nodeValue + '</a></div>';
                    }
                    row.set(2, new Cell(referrer, '', 'cell'));
                    
                    row.set(3, new Cell('<a class="no_link" onClick="show_invoice_page(' + invoices[i].childNodes[0].nodeValue + ');">' + padded_ids[i].childNodes[0].nodeValue + '</a>&nbsp;<a href="invoice_pdf.php?id=' + invoices[i].childNodes[0].nodeValue + '"><img src="../common/images/icons/pdf.gif" /></a>', '', 'cell'));
                    
                    var amount = currencies[i].childNodes[0].nodeValue + '$&nbsp;' + total_rewards[i].childNodes[0].nodeValue;
                    row.set(4, new Cell(amount, '', 'cell'));
                    
                    var actions = '';
                    actions = '<input type="button" value="Award" onClick="show_award_popup(' + ids[i].childNodes[0].nodeValue + ');" />';                    
                    row.set(6, new Cell(actions, '', 'cell action'));
                    
                    new_rewards_table.set((parseInt(i)+1), row);
                }
                
                $('div_new_rewards').set('html', new_rewards_table.get_html());
                set_status('');
            }
        },
        onRequest: function(instance) {
            set_status('Loading new rewards...');
        }
    });
    
    request.send(params);
}

function update_paid_rewards_list() {
    var params = 'id=0&action=get_paid_rewards&order_by=' + paid_order_by + ' ' + paid_order;
    
    var uri = root + "/employees/rewards_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            //set_status('<pre>' + txt + '</pre>');
            //return;
            if (txt == 'ko') {
                alert('An error occured while loading paid rewards.');
                return false;
            }
            
            if (txt == '0') {
                $('div_paid_rewards').set('html', '<div class="empty_results">No rewards awarded at this moment.</div>');
            } else {
                var ids = xml.getElementsByTagName('referral');
                var invoices = xml.getElementsByTagName('invoice');
                var padded_ids = xml.getElementsByTagName('padded_invoice');
                var employers = xml.getElementsByTagName('employer');
                var member_ids = xml.getElementsByTagName('member_id');
                var members = xml.getElementsByTagName('member');
                var phone_nums = xml.getElementsByTagName('phone_num');
                var total_rewards = xml.getElementsByTagName('total_reward');
                var currencies = xml.getElementsByTagName('currency');
                var paid_ons = xml.getElementsByTagName('formatted_paid_on');
                var jobs = xml.getElementsByTagName('title');
                var gifts = xml.getElementsByTagName('gift');
                var paid_rewards = xml.getElementsByTagName('paid_reward');
                
                var paid_rewards_table = new FlexTable('paid_rewards_table', 'paid_rewards');

                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('paid_rewards', 'referral_rewards.paid_on');\">Employed On</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('paid_rewards', 'jobs.title');\">Job</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('paid_rewards', 'members.lastname');\">Referrer</a>", '', 'header'));
                header.set(3, new Cell('Receipt', '', 'header'));
                header.set(4, new Cell('Reward', '', 'header'));
                header.set(5, new Cell('Given Award', '', 'header'));
                paid_rewards_table.set(0, header);
                
                for (var i=0; i < ids.length; i++) {
                    var row = new Row('');
                    
                    row.set(0, new Cell(paid_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var job = jobs[i].childNodes[0].nodeValue;
                    job = job + '<div class="small_contact"><span class="contact_label">Employer:</span> ' + employers[i].childNodes[0].nodeValue + '</div>';
                    row.set(1, new Cell(job, '', 'cell'));
                    
                    var referrer = members[i].childNodes[0].nodeValue;
                    if (member_ids[i].childNodes[0].nodeValue.substr(0, 5) == 'team' && 
                        member_ids[i].childNodes[0].nodeValue.substr(7) == '@yellowelevator.com') {
                        referrer = 'Yellow Elevator';
                    } else {
                        referrer = referrer + '<div class="small_contact"><span class="contact_label">Tel.:</span> ' + phone_nums[i].childNodes[0].nodeValue + '</div>';
                        referrer = referrer +  '<div class="small_contact"><span class="contact_label">Email: </span><a href="mailto:' + member_ids[i].childNodes[0].nodeValue + '">' + member_ids[i].childNodes[0].nodeValue + '</a></div>';
                    }
                    row.set(2, new Cell(referrer, '', 'cell'));
                    
                    row.set(3, new Cell('<a class="no_link" onClick="show_invoice_page(' + invoices[i].childNodes[0].nodeValue + ');">' + padded_ids[i].childNodes[0].nodeValue + '</a>&nbsp;<a href="invoice_pdf.php?id=' + invoices[i].childNodes[0].nodeValue + '"><img src="../common/images/icons/pdf.gif" /></a>', '', 'cell'));
                    
                    var amount = currencies[i].childNodes[0].nodeValue + '$&nbsp;' + total_rewards[i].childNodes[0].nodeValue;
                    row.set(4, new Cell(amount, '', 'cell'));
                    
                    var award = amount;
                    if (gifts[i].childNodes.length > 0) {
                        award = gifts[i].childNodes[0].nodeValue;
                    }                    
                    row.set(6, new Cell(award, '', 'cell action'));
                    
                    paid_rewards_table.set((parseInt(i)+1), row);
                }
                
                $('div_paid_rewards').set('html', paid_rewards_table.get_html());
                set_status('');
            }
        },
        onRequest: function(instance) {
            set_status('Loading paid rewards...');
        }
    });
    
    request.send(params);
}

function show_new_rewards() {
    $('new_rewards').setStyle('display', 'block');
    $('paid_rewards').setStyle('display', 'none');
    
    $('item_new_rewards').setStyle('background-color', '#CCCCCC');
    $('item_paid_rewards').setStyle('background-color', '');
}

function show_paid_rewards() {
    $('new_rewards').setStyle('display', 'none');
    $('paid_rewards').setStyle('display', 'block');
    
    $('item_new_rewards').setStyle('background-color', '');
    $('item_paid_rewards').setStyle('background-color', '#CCCCCC');
}

function show_award_popup(_referral_id) {
    $('referrer_id').value = _invoice_id;
    
    var params = 'id=' + _referral_id + '&action=get_reward_details';
    
    var uri = root + "/employees/rewards_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            //set_status('<pre>' + txt + '</pre>');
            //return;
            if (txt == 'ko') {
                alert('An error occured while loading reward details.');
                return false;
            }
            
            var referrers = xml.getElementsByTagName('member');
            var referrer_ids = xml.getElementsByTagName('member_id');
            var total_rewards = xml.getElementsByTagName('total_reward');
            var currencies = xml.getElementsByTagName('currencies');
            
            if (get_banks(referrer_ids)) {
                // populate drop down
            } else {
                $('banks_list').set('html', 'No bank accounts stored.');
            }
            
            $('lbl_referrer').set('html', referrers[0].childNodes[0].nodeValue);
            
            var amount = currencies[0].childNodes[0].nodeValue + '$ ' + total_rewards[0].nodeValue;
            $('lbl_reward').set('html', amount);
            
            show_window('award_window');
            window.scrollTo(0, 0);
        },
        onRequest: function(instance) {
            set_status('Loading paid rewards...');
        }
    });
    
    request.send(params);
}

function close_award_popup(_is_award) {
    if (_is_award) {
        
    }
    
    close_window('award_window');
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

function onDomReady() {
    initialize_page();
}

window.addEvent('domready', onDomReady);
