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

function show_invoice_page(invoice_id) {
    var popup = window.open('invoice.php?id=' + invoice_id, '', 'scrollbars');
    
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
    
    var params = 'id=' + id;
    params = params + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employers/invoices_action.php";
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
                var padded_ids = xml.getElementsByTagName('padded_id');
                var types = xml.getElementsByTagName('type');
                var payable_bys = xml.getElementsByTagName('formatted_payable_by');
                var expireds = xml.getElementsByTagName('expired');
                var issued_ons = xml.getElementsByTagName('formatted_issued_on');
                
                for (var i=0; i < ids.length; i++) {
                    var invoice_id = ids[i];
                    
                    html = html + '<tr id="'+ invoice_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    
                    if (expireds[i].childNodes[0].nodeValue == 'expired') {
                        //html = html + '<td class="expired" style="background-color: #FF0000;">&nbsp;</td>' + "\n";
                        html = html + '<td class="expired"><img class="warning" src="' + root + '/common/images/icons/expired.png" /></td>' + "\n";
                    } else if (expireds[i].childNodes[0].nodeValue == 'nearly') {
                        //html = html + '<td class="expired" style="background-color: #FFFF00;">&nbsp;</td>' + "\n";
                        html = html + '<td class="expired"><img class="warning" src="' + root + '/common/images/icons/just_expired.png" /></td>' + "\n";
                    } else {
                        html = html + '<td class="expired">&nbsp;</td>' + "\n";
                    }
                    
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
                        case 'P':
                            type = 'Job Posting';
                            break;
                    }

                    html = html + '<td class="type">' + type + '</td>' + "\n";
                    html = html + '<td class="invoice"><a class="no_link" onClick="show_invoice_page(\'' + invoice_id.childNodes[0].nodeValue + '\')">' + padded_ids[i].childNodes[0].nodeValue + '</a></td>' + "\n";
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
    
    var params = 'id=' + id + '&paid_invoices=1';
    params = params + '&order_by=' + paid_order_by + ' ' + paid_order;
    
    var uri = root + "/employers/invoices_action.php";
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
                var padded_ids = xml.getElementsByTagName('padded_id');
                var types = xml.getElementsByTagName('type');
                var paid_ons = xml.getElementsByTagName('formatted_paid_on');
                var issued_ons = xml.getElementsByTagName('formatted_issued_on');

                for (var i=0; i < ids.length; i++) {
                    var invoice_id = ids[i];

                    html = html + '<tr id="'+ invoice_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="date">' + issued_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + paid_ons[i].childNodes[0].nodeValue + '</td>' + "\n";

                    var type = 'Other';
                    switch (types[i].childNodes[0].nodeValue) {
                        case 'R':
                            type = 'Service Fee';
                            break;
                        case 'J':
                            type = 'Subscription';
                            break;
                        case 'P':
                            type = 'Job Posting';
                            break;
                    }

                    html = html + '<td class="type">' + type + '</td>' + "\n";
                    html = html + '<td class="invoice"><a class="no_link" onClick="show_invoice_page(\'' + invoice_id.childNodes[0].nodeValue + '\')">' + padded_ids[i].childNodes[0].nodeValue + '</a></td>' + "\n";
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
    get_employer_referrals_count();
    
    $('li_new').addEvent('click', show_new_invoices);
    $('li_paid').addEvent('click', show_paid_invoices);
    
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
        order_by = 'id';
        ascending_or_descending();
        show_new_invoices();
    });
    
    $('sort_type').addEvent('click', function() {
        order_by = 'type';
        ascending_or_descending();
        show_new_invoices();
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
        paid_order_by = 'invoice';
        paid_ascending_or_descending();
        show_paid_invoices();
    });
    
    $('sort_paid_type').addEvent('click', function() {
        paid_order_by = 'type';
        paid_ascending_or_descending();
        show_paid_invoices();
    });
    
    show_new_invoices();
}

window.addEvent('domready', onDomReady);
