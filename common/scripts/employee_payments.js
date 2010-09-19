var order_by = 'invoices.issued_on';
var order = 'asc';
var receipt_order_by = 'invoices.paid_on';
var receipt_order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function receipt_ascending_or_descending() {
    if (receipt_order == 'desc') {
        receipt_order = 'asc';
    } else {
        receipt_order = 'desc';
    }
}

function sort_by(_table, _column) {
    switch (_table) {
        case 'invoices':
            order_by = _column;
            ascending_or_descending();
            update_invoices_list();
            break;
        case 'receipts':
            receipt_order_by = _column;
            receipt_ascending_or_descending();
            update_receipts_list();
            break;
    }
}

function filter_invoices() {
    update_invoices_list();
}

function filter_receipts() {
    update_receipts_list();
}

function show_invoice_page(_invoice_id) {
    var popup = window.open('invoice.php?id=' + _invoice_id, '', 'scrollbars');
    
    if (!popup) {
        alert('Popup blocker was detected. Please allow pop-up windows for YellowElevator.com and try again.');
    }
}

function show_receipt_page(_invoice_id) {
    show_invoice_page(_invoice_id);
}

function update_invoices_list() {
    var params = 'id=0&action=get_invoices&order_by=' + order_by + ' ' + order;
    params = params + '&filter=' + $('invoices_filter').options[$('invoices_filter').selectedIndex].value;
    
    var uri = root + "/employees/payments_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            //set_status('<pre>' + txt + '</pre>');
            //return;
            if (txt == 'ko') {
                alert('An error occured while loading invoices.');
                return false;
            }
            
            if (txt == '0') {
                $('div_invoices').set('html', '<div class="empty_results">No invoices issued at this moment.</div>');
            } else {
                var ids = xml.getElementsByTagName('id');
                var padded_ids = xml.getElementsByTagName('padded_id');
                var employers = xml.getElementsByTagName('employer');
                var fax_nums = xml.getElementsByTagName('fax_num');
                var phone_nums = xml.getElementsByTagName('phone_num');
                var contact_persons = xml.getElementsByTagName('contact_person');
                var email_addrs = xml.getElementsByTagName('email_addr');
                var amount_payables = xml.getElementsByTagName('amount_payable');
                var currencies = xml.getElementsByTagName('currency');
                var expireds = xml.getElementsByTagName('expired');
                var issued_ons = xml.getElementsByTagName('formatted_issued_on');
                var payable_bys = xml.getElementsByTagName('formatted_payable_by');
                
                var invoices_table = new FlexTable('invoices_table', 'invoices');

                var header = new Row('');
                header.set(0, new Cell('&nbsp;', '', 'header expiry_status'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('invoices', 'invoices.issued_on');\">Issued On</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('invoices', 'employers.name');\">Employer</a>", '', 'header'));
                header.set(3, new Cell("<a class=\"sortable\" onClick=\"sort_by('invoices', 'invoices.id');\">Invoice</a>", '', 'header'));
                header.set(4, new Cell('Payable By', '', 'header'));
                header.set(5, new Cell('Amount Payable', '', 'header'));
                header.set(6, new Cell('Actions', '', 'header action'));
                invoices_table.set(0, header);
                
                for (var i=0; i < ids.length; i++) {
                    var row = new Row('');
                    
                    var status = '';
                    if (expireds[i].childNodes[0].nodeValue == 'expired') {
                        status = '<img class="warning" src="../common/images/icons/expired.png" />';
                    } else if (expireds[i].childNodes[0].nodeValue == 'nearly') {
                        status = '<img class="warning" src="../common/images/icons/just_expired.png" />';
                    }
                    row.set(0, new Cell(status, '', 'cell expiry_status'));
                    
                    row.set(1, new Cell(issued_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var contacts = employers[i].childNodes[0].nodeValue;
                    var fax_num = '';
                    
                    if (fax_nums[i].childNodes.length > 0) {
                        fax_num = fax_nums[i].childNodes[0].nodeValue;
                    }
                    contacts = contacts + '<div class="contacts">';
                    contacts = contacts +  '<span class="contact_label">Tel.:</span> ' + phone_nums[i].childNodes[0].nodeValue + '<br/>';
                    contacts = contacts +  '<span class="contact_label">Fax.:</span> ' + fax_num + '<br/>';
                    contacts = contacts +  '<span class="contact_label">E-mail:</span> <a href="mailto:' + email_addrs[i].childNodes[0].nodeValue + '">' + email_addrs[i].childNodes[0].nodeValue + '</a><br/>';
                    contacts = contacts +  '<span class="contact_label">Contact:</span> ' + contact_persons[i].childNodes[0].nodeValue + '<br/>';
                    contacts = contacts + '</div>';
                    row.set(2, new Cell(contacts, '', 'cell'));
                    
                    row.set(3, new Cell('<a class="no_link" onClick="show_invoice_page(' + ids[i].childNodes[0].nodeValue + ');">' + padded_ids[i].childNodes[0].nodeValue + '</a>&nbsp;<a href="invoice_pdf.php?id=' + ids[i].childNodes[0].nodeValue + '"><img src="../common/images/icons/pdf.gif" /></a>', '', 'cell'));
                    row.set(4, new Cell(payable_bys[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var amount = currencies[i].childNodes[0].nodeValue + '$&nbsp;' + amount_payables[i].childNodes[0].nodeValue;
                    row.set(5, new Cell(amount, '', 'cell'));
                    
                    var actions = '';
                    actions = '<input type="button" value="Paid" onClick="show_payment_popup(' + ids[i].childNodes[0].nodeValue + ', \'' + padded_ids[i].childNodes[0].nodeValue + '\');" /><input type="button" value="Resend" onClick="show_resend_popup(' + ids[i].childNodes[0].nodeValue + ', \'' + padded_ids[i].childNodes[0].nodeValue + '\');" />';                    
                    row.set(6, new Cell(actions, '', 'cell action'));
                    
                    invoices_table.set((parseInt(i)+1), row);
                }
                
                $('div_invoices').set('html', invoices_table.get_html());
                set_status('');
            }
        },
        onRequest: function(instance) {
            set_status('Loading invoices...');
        }
    });
    
    request.send(params);
}

function update_receipts_list() {
    var params = 'id=0&action=get_receipts&order_by=' + order_by + ' ' + order;
    params = params + '&filter=' + $('receipts_filter').options[$('receipts_filter').selectedIndex].value;
    
    var uri = root + "/employees/payments_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            //set_status('<pre>' + txt + '</pre>');
            //return;
            if (txt == 'ko') {
                alert('An error occured while loading receipts.');
                return false;
            }
            
            if (txt == '0') {
                $('div_receipts').set('html', '<div class="empty_results">No receipts issued at this moment.</div>');
            } else {
                var ids = xml.getElementsByTagName('id');
                var padded_ids = xml.getElementsByTagName('padded_id');
                var employers = xml.getElementsByTagName('employer');
                var amount_payables = xml.getElementsByTagName('amount_payable');
                var currencies = xml.getElementsByTagName('currency');
                var expireds = xml.getElementsByTagName('expired');
                var issued_ons = xml.getElementsByTagName('formatted_issued_on');
                var paid_ons = xml.getElementsByTagName('formatted_paid_on');
                var paid_throughs = xml.getElementsByTagName('paid_through');
                var paid_ids = xml.getElementsByTagName('paid_id');
                
                var receipts_table = new FlexTable('receipts_table', 'receipts');

                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('receipts', 'invoices.issued_on');\">Issued On</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('receipts', 'employers.name');\">Employer</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('receipts', 'invoices.id');\">Receipt</a>", '', 'header'));
                header.set(3, new Cell('Paid On', '', 'header'));
                header.set(4, new Cell('Amount Paid', '', 'header'));
                header.set(5, new Cell('Payment', '', 'header payment'));
                receipts_table.set(0, header);
                
                for (var i=0; i < ids.length; i++) {
                    var row = new Row('');
                    
                    row.set(0, new Cell(issued_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    row.set(1, new Cell(employers[i].childNodes[0].nodeValue, '', 'cell'));
                    row.set(2, new Cell('<a class="no_link" onClick="show_receipt_page(' + ids[i].childNodes[0].nodeValue + ');">' + padded_ids[i].childNodes[0].nodeValue + '</a>&nbsp;<a href="invoice_pdf.php?id=' + ids[i].childNodes[0].nodeValue + '"><img src="../common/images/icons/pdf.gif" /></a>', '', 'cell'));
                    row.set(3, new Cell(paid_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var amount = currencies[i].childNodes[0].nodeValue + '$&nbsp;' + amount_payables[i].childNodes[0].nodeValue;
                    row.set(4, new Cell(amount, '', 'cell'));
                    
                    var payment = 'By Cash';
                    if (paid_throughs[i].childNodes[0].nodeValue != 'CSH') {
                        payment = 'Bank Receipt #:<br/>' + paid_ids[i].childNodes[0].nodeValue;
                    }
                    row.set(5, new Cell(payment, '', 'cell payment'));
                    
                    receipts_table.set((parseInt(i)+1), row);
                }
                
                $('div_receipts').set('html', receipts_table.get_html());
                set_status('');
            }
        },
        onRequest: function(instance) {
            set_status('Loading receipts...');
        }
    });
    
    request.send(params);
}

function show_invoices() {
    $('receipts').setStyle('display', 'none');
    $('invoices').setStyle('display', 'block');
    
    $('item_receipts').setStyle('background-color', '');
    $('item_invoices').setStyle('background-color', '#CCCCCC');
}

function show_receipts() {
    $('receipts').setStyle('display', 'block');
    $('invoices').setStyle('display', 'none');
    
    $('item_receipts').setStyle('background-color', '#CCCCCC');
    $('item_invoices').setStyle('background-color', '');
}

function show_payment_popup(_invoice_id, _padded_id) {
    $('lbl_invoice').set('html', _padded_id);
    $('invoice_id').value = _invoice_id;
    show_window('paid_window');
    window.scrollTo(0, 0);
}

function close_payment_popup(_is_confirmed) {
    if (_is_confirmed) {
        var year = $('year').options[$('year').selectedIndex].value;
        var month = $('month').options[$('month').selectedIndex].value;
        var day = $('day').options[$('day').selectedIndex].value;
        var paid_on = year + '-' + month + '-' + day;
        
        var paid_through = $('payment_mode').options[$('payment_mode').selectedIndex].value;
        var paid_id = $('payment_number').value;
        if (paid_through != 'CSH' && isEmpty(paid_id)) {
            alert('You need to enter the bank receipt number for this transaction.');
            return;
        }
        
        var msg = 'You have entered the following payment details:' + "\n\n";
        msg = msg + 'Invoice: \t\t\t' + $('lbl_invoice').get('html') + "\n";
        msg = msg + 'Paid On: \t\t\t' + paid_on + "\n";
        msg = msg + 'Payment Mode: \t' + paid_through + "\n";
        msg = msg + 'Bank Receipt #: \t' + paid_id + "\n\n";
        msg = msg + 'Are you sure to proceed?';

        var proceed = confirm(msg);
        if (!proceed) {
            return;
        }
        
        var params = 'id=' + $('invoice_id').value;
        params = params + '&action=confirm_payment';
        params = params + '&paid_on=' + paid_on;
        params = params + '&paid_through=' + paid_through;
        params = params + '&paid_id=' + paid_id;
        
        var uri = root + "/employees/payments_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    alert('An error occured while confirming payment.');
                    return false;
                }

                set_status('');
                close_window('paid_window');
                
                if ($('div_receipts') == null) {
                    location.replace('payments.php');
                } else {
                    update_invoices_list();
                    update_receipts_list();
                }
            },
            onRequest: function(instance) {
                set_status('Confirming payment...');
            }
        });
        
        request.send(params);
        return;
    }
    
    close_window('paid_window');
}

function show_resend_popup(_invoice_id, _padded_id) {
    $('lbl_resend_invoice').set('html', _padded_id);
    $('resend_invoice_id').value = _invoice_id;
    
    var params = 'id=' + _invoice_id + '&action=get_employer_info';
    var uri = root + "/employees/payments_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            //set_status(txt);
            //return;
            if (txt == 'ko') {
                alert('An error occured while getting employer details.');
                return false;
            }
            
            var employer_name = xml.getElementsByTagName('name');
            var contact_person = xml.getElementsByTagName('contact_person');
            var email_addr = xml.getElementsByTagName('email_addr');
            
            $('employer_name').set('html', employer_name[0].childNodes[0].nodeValue);
            $('contact_person').set('html', contact_person[0].childNodes[0].nodeValue);
            $('recipients').value = email_addr[0].childNodes[0].nodeValue;
            
            set_status('');
            show_window('resend_window');
        },
        onRequest: function(instance) {
            set_status('Getting employer details...');
        }
    });
    
    request.send(params);
    window.scrollTo(0, 0);
}

function close_resend_popup(_is_resend) {
    if (_is_resend) {
        var recipients = $('recipients').value;
        
        if (isEmpty(recipients)) {
            var proceed = confirm('You have not entered an email to be send to.\n\nIf you proceed, the default email address will be used.');
            if (!proceed) {
                return;
            }
        }
        
        var params = 'id=' + $('resend_invoice_id').value;
        params = params + '&action=resend';
        params = params + '&recipients=' + recipients;
        alert(params);
        var uri = root + "/employees/payments_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    alert('An error occured while resending invoice.');
                    return false;
                }

                set_status('');
                close_window('resend_window');
            },
            onRequest: function(instance) {
                set_status('Resending invoice...');
            }
        });
        
        request.send(params);
        return;
    }
    
    close_window('resend_window');
}

function onDomReady() {
    initialize_page();
}

window.addEvent('domready', onDomReady);
