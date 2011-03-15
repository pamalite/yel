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
                $('banks_list').set('html', 'No bank account stored.');
                return;
            }
            
            var ids = xml.getElementsByTagName('id');
            var banks = xml.getElementsByTagName('bank');
            var accounts = xml.getElementsByTagName('account');
            
            var html = '<select class="field" id="bank_account">';
            for (var i=0; i < ids.length; i++) {
                html = html + '<option value="' + ids[i].childNodes[0].nodeValue + '">' + banks[i].childNodes[0].nodeValue + ' (' + accounts[i].childNodes[0].nodeValue + ')</option>';
            }
            html = html + '</select>';
            $('banks_list').set('html', html);
            set_status('');
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
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('paid_rewards', 'referral_rewards.paid_on');\">Awarded On</a>", '', 'header'));
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
    $('referral_id').value = _referral_id;
    
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
            var currencies = xml.getElementsByTagName('currency');
            
            get_banks(referrer_ids[0].childNodes[0].nodeValue);
            
            $('lbl_referrer').set('html', referrers[0].childNodes[0].nodeValue);
            
            // var amount = currencies[0].childNodes[0].nodeValue + '$ ' + total_rewards[0].childNodes[0].nodeValue;
            // $('lbl_reward').set('html', amount);
            
            $('lbl_reward_currency').set('html', currencies[0].childNodes[0].nodeValue);
            $('amount').value = total_rewards[0].childNodes[0].nodeValue;
            
            show_window('award_window');
            // window.scrollTo(0, 0);
        },
        onRequest: function(instance) {
            set_status('Loading paid rewards...');
        }
    });
    
    request.send(params);
}

function close_award_popup(_is_award) {
    if (_is_award) {
        var params = 'id=' + $('referral_id').value + '&action=award';
        
        if ($('award_as_gift').checked) {
            params = params + '&award_mode=gift';
            params = params + '&gift=' + $('gift').value;
        } else {
            if (isNaN($('amount').value) || $('amount').value <= 0) {
                alert('Reward amount must be a number and more then $0.00');
                return;
            }
            
            params = params + '&award_mode=money';
            params = params + '&bank=' + $('bank_account').options[$('bank_account').selectedIndex].value;
            params = params + '&payment_mode=' + $('payment_mode').options[$('payment_mode').selectedIndex].value;
            params = params + '&receipt=' + $('receipt').value;
            // params = params + '&amount=' + $('lbl_reward').get('html').substr(5);
            params = params + '&amount=' + $('amount').value;
        }
        
        var uri = root + "/employees/rewards_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                //set_status('<pre>' + txt + '</pre>');
                //return;
                if (txt == 'ko') {
                    alert('An error occured while awarding reward.');
                    return false;
                }
                
                
                
                if ($('div_paid_rewards') == null) {
                    location.replace('rewards.php');
                } else {
                    update_new_rewards_list();
                    update_paid_rewards_list();
                }
                
                close_window('award_window');
                set_status('');
            },
            onRequest: function(instance) {
                set_status('Loading paid rewards...');
            }
        });

        request.send(params);
        
    } else {
        close_window('award_window');
    }
}

function onDomReady() {
    initialize_page();
}

window.addEvent('domready', onDomReady);
