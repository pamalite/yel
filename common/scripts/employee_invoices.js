var selected_tab = 'li_new';
var order_by = 'issued_on';
var order = 'desc';
var paid_order_by = 'paid_on';
var paid_order = 'desc';

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

function show_invoice_page(_invoice_id) {
    var popup = window.open('invoice.php?id=' + _invoice_id, '', 'scrollbars');
    
    if (!popup) {
        alert('Popup blocker was detected. Please allow pop-up windows for YellowElevator.com and try again.');
    }
}

function show_new_invoices() {
    selected_tab = 'li_new';
    $(selected_tab).setStyle('border', '1px solid #CCCCCC');
    $('li_paid').setStyle('border', '1px solid #0000FF');
    $('div_paid_invoices').setStyle('display', 'none');
    $('div_new_invoices').setStyle('display', 'block');
    
    var params = 'id=0&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employees/invoices_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading invoices.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no new invoices at the moment.</div>';
            } else {
                var ids = xml.getElementsByTagName('id');
                var employers = xml.getElementsByTagName('employer');
                var currencies = xml.getElementsByTagName('currency');
                var padded_ids = xml.getElementsByTagName('padded_id');
                var types = xml.getElementsByTagName('type');
                var payable_bys = xml.getElementsByTagName('formatted_payable_by');
                var expireds = xml.getElementsByTagName('expired');
                var issued_ons = xml.getElementsByTagName('formatted_issued_on');
                var amount_payables = xml.getElementsByTagName('amount_payable');
                
                for (var i=0; i < ids.length; i++) {
                    var invoice = ids[i];
                    
                    html = html + '<tr id="'+ invoice.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    
                    if (expireds[i].childNodes[0].nodeValue == 'expired') {
                        //html = html + '<td class="expired" style="background-color: #FF0000;">&nbsp;</td>' + "\n";
                        html = html + '<td class="expired"><img class="warning" src="' + root + '/common/images/icons/expired.png" /></td>' + "\n";
                    } else if (expireds[i].childNodes[0].nodeValue == 'nearly') {
                        //html = html + '<td class="expired" style="background-color: #FFFF00;">&nbsp;</td>' + "\n";
                        html = html + '<td class="expired"><img class="warning" src="' + root + '/common/images/icons/just_expired.png" /></td>' + "\n";
                    } else {
                        html = html + '<td class="expired">&nbsp;</td>' + "\n";
                    }
                    
                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + issued_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + payable_bys[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    var type = 'Other';
                    switch (types[i].childNodes[0].nodeValue) {
                        case 'R':
                            type = 'Service Fee';
                            break;
                        case 'J':
                            type = 'Subscription';
                            break;
                    }

                    html = html + '<td class="type">' + type + '</td>' + "\n";
                    html = html + '<td class="invoice"><a class="no_link" onClick="show_invoice_page(\'' + invoice.childNodes[0].nodeValue + '\')">' + padded_ids[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="amount">' + currencies[i].childNodes[0].nodeValue + ' ' + amount_payables[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="payment_received"><a class="no_link" onClick="show_confirm_payment_form(\'' + invoice.childNodes[0].nodeValue + '\', \'' + padded_ids[i].childNodes[0].nodeValue + '\', \'' + employers[i].childNodes[0].nodeValue + '\', \'' + amount_payables[i].childNodes[0].nodeValue + '\')">Payment Received</a></td>' + "\n";
                    html = html + '<td class="pdf"><a href="invoice_pdf.php?id=' + invoice.childNodes[0].nodeValue + '"><img src="' + root + '/common/images/icons/pdf.gif" /></a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_new_invoices_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading invoices...');
        }
    });
    
    request.send(params);
}

function show_paid_invoices() {
    selected_tab = 'li_paid';
    $(selected_tab).setStyle('border', '1px solid #CCCCCC');
    $('li_new').setStyle('border', '1px solid #0000FF');
    $('div_paid_invoices').setStyle('display', 'block');
    $('div_new_invoices').setStyle('display', 'none');
    
    var params = 'id=0&paid_invoices=1';
    params = params + '&order_by=' + paid_order_by + ' ' + paid_order;
    
    var uri = root + "/employees/invoices_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading receipts.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no receipts at the moment.</div>';
            } else {
                var ids = xml.getElementsByTagName('id');
                var employers = xml.getElementsByTagName('employer');
                var currencies = xml.getElementsByTagName('currency');
                var padded_ids = xml.getElementsByTagName('padded_id');
                var types = xml.getElementsByTagName('type');
                var paid_ons = xml.getElementsByTagName('formatted_paid_on');
                var issued_ons = xml.getElementsByTagName('formatted_issued_on');
                var amount_payables = xml.getElementsByTagName('amount_payable');
                
                for (var i=0; i < ids.length; i++) {
                    var invoice = ids[i];

                    html = html + '<tr id="'+ invoice.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + issued_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + paid_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    var type = 'Other';
                    switch (types[i].childNodes[0].nodeValue) {
                        case 'R':
                            type = 'Service Fee';
                            break;
                        case 'J':
                            type = 'Job';
                            break;
                    }

                    html = html + '<td class="type">' + type + '</td>' + "\n";
                    html = html + '<td class="invoice"><a class="no_link" onClick="show_invoice_page(\'' + invoice.childNodes[0].nodeValue + '\')">' + padded_ids[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="amount">' + currencies[i].childNodes[0].nodeValue + ' ' + amount_payables[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="pdf"><a href="invoice_pdf.php?id=' + invoice.childNodes[0].nodeValue + '"><img src="' + root + '/common/images/icons/pdf.gif" style="border: none; vertical-align: middle;"/></a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_paid_invoices_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading receipts...');
        }
    });
    
    request.send(params);
}

function close_confirm_payment_form() {
    $('invoice_id').value = 0;
    $('div_confirm_payment_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_confirm_payment_form(_invoice_id, padded_invoice_id, employer, amount_payable) {
    $('invoice_id').value = _invoice_id;
    $('invoice').set('html', padded_invoice_id);
    $('employer').set('html', employer);
    $('amount_payable').set('html', amount_payable);
    
    var today = new Date();
    list_months_in((parseInt(today.getMonth())+1), 'month_list', 'month_list_dropdown', 'month_list_dropdown');
    $('day').value = today.getDate();
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_confirm_payment_form').getStyle('height'));
    var div_width = parseInt($('div_confirm_payment_form').getStyle('width'));
    
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
    
    $('div_confirm_payment_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_confirm_payment_form').setStyle('left', ((window_width - div_width) / 2));
    
    $('div_blanket').setStyle('display', 'block');
    $('div_confirm_payment_form').setStyle('display', 'block');
    
}

function confirm_payment() {
    
    var year = $('year').options[$('year').selectedIndex].value;
    var month = $('month_list_dropdown').options[$('month_list_dropdown').selectedIndex].value;
    var day = parseInt($('day').value);
    if (isNaN(day) || day < 1 || day > 31) {
        alert('Day must be in the range of 1-31, inclusive.');
        return false;
    }
    
    var payment_mode = $('payment_mode').options[$('payment_mode').selectedIndex].value;
    if (payment_mode == 'CHQ' && (isEmpty($('payment_number').value) || parseInt($('payment_number').value) <= 0)) {
        alert('Cheque number cannot empty or zero.');
        return false;
    } else {
        if (isEmpty($('payment_number').value)) {
            $('payment_number').value = 0;
        }
    }
    
    var paid_on = year + '-' + month + '-' + day;
    var msg = 'You have entered the following payment details:' + "\n\n";
    msg = msg + 'Invoice: \t\t\t' + $('invoice').get('html') + "\n";
    msg = msg + 'Paid On: \t\t\t' + $('day').value + ' ' + $('month_list_dropdown').options[$('month_list_dropdown').selectedIndex].text + ', ' + $('year').value + "\n";
    msg = msg + 'Payment Mode: \t' + $('payment_mode').options[$('payment_mode').selectedIndex].text + "\n";
    msg = msg + 'Cheque/Receipt: \t' + $('payment_number').value + "\n\n";
    msg = msg + 'Are you sure to proceed?';
    
    var proceed = confirm(msg);
    if (!proceed) {
        return;
    } 
    
    var params = 'id=' + $('invoice_id').value;
    params = params + '&action=confirm_payment';
    params = params + '&paid_on=' + paid_on;
    params = params + '&paid_through=' + payment_mode;
    params = params + '&paid_id=' + $('payment_number').value;
    
    var uri = root + "/employees/invoices_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while confirming payment.');
                return false;
            }
            
            set_status('');
            close_confirm_payment_form();
            show_new_invoices();
        },
        onRequest: function(instance) {
            set_status('Confirming payment...');
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
    
    $('li_paid').addEvent('mouseover', function() {
        $('li_paid').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_paid').addEvent('mouseout', function() {
        $('li_paid').setStyles({
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
    
    $('li_new').addEvent('click', show_new_invoices);
    $('li_paid').addEvent('click', show_paid_invoices);
    
    $('sort_employer').addEvent('click', function() {
        order_by = 'employer';
        ascending_or_descending();
        show_new_invoices();
    });
    
    $('sort_issued_on').addEvent('click', function() {
        order_by = 'issued_on';
        ascending_or_descending();
        show_new_invoices();
    });
    
    $('sort_payable_by').addEvent('click', function() {
        order_by = 'payable_by';
        ascending_or_descending();
        show_new_invoices();
    });
    
    $('sort_invoice').addEvent('click', function() {
        order_by = 'invoices.id';
        ascending_or_descending();
        show_new_invoices();
    });
    
    $('sort_type').addEvent('click', function() {
        order_by = 'type';
        ascending_or_descending();
        show_new_invoices();
    });
    
    $('sort_amount').addEvent('click', function() {
        order_by = 'amount_payable';
        ascending_or_descending();
        show_new_invoices();
    });
    
    $('sort_paid_employer').addEvent('click', function() {
        paid_order_by = 'employer';
        paid_ascending_or_descending();
        show_paid_invoices();
    });
    
    $('sort_paid_issued_on').addEvent('click', function() {
        paid_order_by = 'issued_on';
        paid_ascending_or_descending();
        show_paid_invoices();
    });
    
    $('sort_paid_paid_on').addEvent('click', function() {
        paid_order_by = 'payable_by';
        paid_ascending_or_descending();
        show_paid_invoices();
    });
    
    $('sort_paid_invoice').addEvent('click', function() {
        paid_order_by = 'invoices.id';
        paid_ascending_or_descending();
        show_paid_invoices();
    });
    
    $('sort_paid_type').addEvent('click', function() {
        paid_order_by = 'type';
        paid_ascending_or_descending();
        show_paid_invoices();
    });
    
    $('sort_paid_amount').addEvent('click', function() {
        paid_order_by = 'amount_payable';
        paid_ascending_or_descending();
        show_paid_invoices();
    });
    
    show_new_invoices();
}

window.addEvent('domready', onDomReady);
