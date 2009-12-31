var order_by = 'purchased_on';
var order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function show_slots() {
    var params = 'id=0&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employees/slots_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading referrals.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no slots purchased at the moment.</div>';
            } else {
                var txn_ids = xml.getElementsByTagName('transaction_id');
                var employers = xml.getElementsByTagName('employer');
                var employer_ids = xml.getElementsByTagName('employer_id');
                var currencies = xml.getElementsByTagName('currency');
                var prices = xml.getElementsByTagName('price_per_slot');
                var qtys = xml.getElementsByTagName('number_of_slot');
                var amounts = xml.getElementsByTagName('total_amount');
                var purchased_ons = xml.getElementsByTagName('formatted_purchased_on');
                var on_holds = xml.getElementsByTagName('on_hold');
                
                for (var i=0; i < txn_ids.length; i++) {
                    var on_hold = on_holds[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ i + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="date">' + purchased_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    if (on_hold == '1') {
                        html = html + '<td class="trans_id"><span style="color: #FF0000;">' + txn_ids[i].childNodes[0].nodeValue + '</span></td>' + "\n";
                    } else {
                        html = html + '<td class="trans_id">' + txn_ids[i].childNodes[0].nodeValue + '</td>' + "\n";
                    }
                    
                    html = html + '<td class="currency">' + currencies[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="price_per_slot">' + prices[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="number_of_slots">' + qtys[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="amount">' + amounts[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    if (on_hold == '1') {
                        html = html + '<td class="action"><input type="button" value="Received" onClick="confirm_payment(\'' + txn_ids[i].childNodes[0].nodeValue + '\', \'' + employer_ids[i].childNodes[0].nodeValue + '\');" /></td>' + "\n";
                    } else {
                        html = html + '<td class="action"><input type="button" value="Received" disabled /></td>' + "\n";
                    }
                    
                    html = html + '</tr>' + "\n";
                }
            }
            html = html + '</table>';
            
            $('div_slots_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading slots...');
        }
    });
    
    request.send(params);
}

function confirm_payment(_txn_id, _employer_id) {
    var payment_id = prompt('Please confirm the payment for ' + _employer_id + ' [' + _txn_id + '] has been received with the receipt/cheque number.');
    
    if (isEmpty(payment_id) || payment_id == false) {
        return false;
    }
    
    var params = 'id=' + _employer_id + '&txn_id=' + _txn_id;
    params = params + '&action=confirm_payment';
    params = params + '&payment_id=' + payment_id;
    
    var uri = root + "/employees/slots_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while confirming payment.');
                return false;
            }
            
            set_status('');
            show_slots();
        },
        onRequest: function(instance) {
            set_status('Confirming payment...');
        }
    });
    
    request.send(params);
}

function onDomReady() {
    set_root();
    get_unapproved_photos_count();
    get_employee_rewards_count();
    get_employee_tokens_count();
    
    $('sort_employer').addEvent('click', function() {
        order_by = 'employer';
        ascending_or_descending();
        show_slots();
    });
    
    $('sort_purchased_on').addEvent('click', function() {
        order_by = 'purchased_on';
        ascending_or_descending();
        show_slots();
    });
    
    show_slots();
}

window.addEvent('domready', onDomReady);
