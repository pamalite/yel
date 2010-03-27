var selected_tab = 'li_invoices';
var order_by = 'issued_on';
var order = 'asc';
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

function sort_by(_table, _column) {
    switch (_table) {
        case 'invoices':
            order_by = _column;
            ascending_or_descending();
            show_invoices();
            break;
        case 'receipts':
            paid_order_by = _column;
            paid_ascending_or_descending();
            show_receipts();
            break;
    }
}

function show_invoice_page(_invoice_id) {
    var popup = window.open('invoice.php?id=' + _invoice_id, '', 'scrollbars');
    
    if (!popup) {
        alert('Popup blocker was detected. Please allow pop-up windows for YellowElevator.com and try again.');
    }
}

function show_invoices() {
    selected_tab = 'li_invoices';
    $(selected_tab).setStyle('border', '1px solid #AAAAAA');
    $(selected_tab).setStyle('border-bottom', '1px solid #EEEEEE');
    $(selected_tab).setStyle('background-color', '#EEEEEE');
    $('li_receipts').setStyle('border', '1px solid #0000FF');
    $('li_receipts').setStyle('border-bottom', 'none');
    $('li_receipts').setStyle('background-color', '#FFFFFF');
    $('div_invoices').setStyle('display', 'block');
    $('div_receipts').setStyle('display', 'none');
    
    var params = 'id=' + id + '&action=get_invoices';
    params = params + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employers/invoices_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == 'ko') {
                alert('An error occured while loading invoices.');
                return false;
            }
            
            if (txt == '0') {
               $('div_invoices').set('html', '<div class="empty_results">No invoices issued at this moment.</div>');
            } else {
                var ids = xml.getElementsByTagName('id');
                var padded_ids = xml.getElementsByTagName('padded_id');
                var types = xml.getElementsByTagName('type');
                var payable_bys = xml.getElementsByTagName('formatted_payable_by');
                var expireds = xml.getElementsByTagName('expired');
                var issued_ons = xml.getElementsByTagName('formatted_issued_on');
                
                var invoices_table = new FlexTable('invoices_table', 'payments');
                
                var header = new Row('');
                header.set(0, new Cell("&nbsp;", '', 'header cell_indicator'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('invoices', 'issued_on');\">Issued On</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('invoices', 'payable_by');\">Payable By</a>", '', 'header'));
                header.set(3, new Cell("<a class=\"sortable\" onClick=\"sort_by('invoices', 'type');\">Type</a>", '', 'header'));
                header.set(4, new Cell("<a class=\"sortable\" onClick=\"sort_by('invoices', 'id');\">Invoice</a>", '', 'header'));
                invoices_table.set(0, header);
                
                for (var i=0; i < ids.length; i++) {
                    var row = new Row('');
                    
                    if (parseInt(expireds[i].childNodes[0].nodeValue) <= 0) {
                        row.set(0, new Cell('<img src="../common/images/icons/expired.png" />', '', 'cell cell_indicator'));
                    } else {
                        row.set(0, new Cell('&nbsp;', '', 'cell cell_indicator'));
                    }
                    
                    row.set(1, new Cell(issued_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    row.set(2, new Cell(payable_bys[i].childNodes[0].nodeValue, '', 'cell'));
                    row.set(3, new Cell(types[i].childNodes[0].nodeValue, '', 'cell'));
                    row.set(4, new Cell('<a class="no_link" onClick="show_invoice_page(' + ids[i].childNodes[0].nodeValue + ');">' + padded_ids[i].childNodes[0].nodeValue + '</a>', '', 'cell'));
                    invoices_table.set((parseInt(i)+1), row);
                }
                
                $('div_invoices').set('html', invoices_table.get_html());
            }
        },
        onRequest: function(instance) {
            set_status('Loading invoices...');
        }
    });
    
    request.send(params);
}

function show_receipts() {
    selected_tab = 'li_receipts';
    $(selected_tab).setStyle('border', '1px solid #AAAAAA');
    $(selected_tab).setStyle('border-bottom', '1px solid #EEEEEE');
    $(selected_tab).setStyle('background-color', '#EEEEEE');
    $('li_invoices').setStyle('border', '1px solid #0000FF');
    $('li_invoices').setStyle('border-bottom', 'none');
    $('li_invoices').setStyle('background-color', '#FFFFFF');
    $('div_invoices').setStyle('display', 'none');
    $('div_receipts').setStyle('display', 'block');
    
    var params = 'id=' + id + '&action=get_invoices&paid_invoices=1';
    params = params + '&order_by=' + paid_order_by + ' ' + paid_order;
    
    var uri = root + "/employers/invoices_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == 'ko') {
                alert('An error occured while loading invoices.');
                return false;
            }
            
            if (txt == '0') {
               $('div_receipts').set('html', '<div class="empty_results">No receipts issued at this moment.</div>');
            } else {
                var ids = xml.getElementsByTagName('id');
                var padded_ids = xml.getElementsByTagName('padded_id');
                var types = xml.getElementsByTagName('type');
                var paid_ons = xml.getElementsByTagName('formatted_paid_on');
                var issued_ons = xml.getElementsByTagName('formatted_issued_on');
                
                var receipts_table = new FlexTable('receipts_table', 'payments');
                
                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('receipts', 'issued_on');\">Issued On</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('receipts', 'paid_on');\">Payable By</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('receipts', 'type');\">Type</a>", '', 'header'));
                header.set(3, new Cell("<a class=\"sortable\" onClick=\"sort_by('receipts', 'id');\">Invoice</a>", '', 'header'));
                receipts_table.set(0, header);
                
                for (var i=0; i < ids.length; i++) {
                    var row = new Row('');
                    
                    row.set(0, new Cell(issued_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    row.set(1, new Cell(paid_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    row.set(2, new Cell(types[i].childNodes[0].nodeValue, '', 'cell'));
                    row.set(3, new Cell('<a class="no_link" onClick="show_invoice_page(' + ids[i].childNodes[0].nodeValue + ');">' + padded_ids[i].childNodes[0].nodeValue + '</a>', '', 'cell'));
                    receipts_table.set((parseInt(i)+1), row);
                }
                
                $('div_receipts').set('html', receipts_table.get_html());
            }
        },
        onRequest: function(instance) {
            set_status('Loading receipts...');
        }
    });
    
    request.send(params);
}

function set_mouse_events() {
    $('li_invoices').addEvent('mouseover', function() {
        $('li_invoices').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_invoices').addEvent('mouseout', function() {
        $('li_invoices').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_receipts').addEvent('mouseover', function() {
        $('li_receipts').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_receipts').addEvent('mouseout', function() {
        $('li_receipts').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
}

function onDomReady() {
    set_root();
    set_mouse_events();
    
    $('li_invoices').addEvent('click', show_invoices);
    $('li_receipts').addEvent('click', show_receipts);
}

window.addEvent('domready', onDomReady);
