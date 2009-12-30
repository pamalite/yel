var added_admin_fee = false;

function get_slots_left() {
    $('num_slots').set('html', '(Free Job Publishing Activated)');
    $('num_slots').setStyle('color', '#CCCCCC');
    $('slots_expiry').set('html', '(Not Applicable)');
    $('slots_expiry').setStyle('color', '#666666');
    
    var params = 'id=' + id + '&action=get_slots_left';
    
    var uri = root + "/employers/slots_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                $('num_slots').set('html', '(Error)');
                $('num_slots').setStyle('color', '#FF0000');
                return false;
            }
            
            if (txt != '-1') {
                var slots = xml.getElementsByTagName('slots');
                var expired = xml.getElementsByTagName('expired');
                var expire_on = xml.getElementsByTagName('expire_on');

                $('slots_expiry').set('html', expire_on[0].childNodes[0].nodeValue);

                if (parseInt(expired[0].childNodes[0].nodeValue) < 0) {
                    $('num_slots').set('html', '(All slots are expired.)');
                    $('num_slots').setStyle('color', '#FF0000');
                    $('slots_expiry').setStyle('color', '#FF0000');
                } else {
                    if (parseInt(slots[0].childNodes[0].nodeValue) == 0) {
                        $('num_slots').set('html', '(You have no more slots left.)');
                        $('num_slots').setStyle('color', '#FF0000');
                    } else {
                        if (parseInt(slots[0].childNodes[0].nodeValue) <= 2) {
                            $('num_slots').set('html', slots[0].childNodes[0].nodeValue);
                            $('num_slots').setStyle('color', '#FFAE00');
                        } else {
                            $('num_slots').set('html', slots[0].childNodes[0].nodeValue);
                            $('num_slots').setStyle('color', '#079607');
                        }
                    }
                }
            }
        }
    });
    
    request.send(params);
}

function show_purchase_histories() {
    var params = 'id=' + id;
    
    var uri = root + "/employers/slots_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading slots purchase histories.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There is no past purchase of slots.</div>';
            } else {
                var price_per_slots = xml.getElementsByTagName('price_per_slot');
                var number_of_slots = xml.getElementsByTagName('number_of_slot');
                var total_amounts = xml.getElementsByTagName('total_amount');
                var purchased_ons = xml.getElementsByTagName('formatted_purchased_on');
                
                for (var i=0; i < price_per_slots.length; i++) {
                    html = html + '<tr onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="date">' + purchased_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="number_of_slots">' + number_of_slots[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="price_per_slot">' + price_per_slots[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="amount">' + total_amounts[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading slots purchase histories...');
        }
    });
    
    request.send(params);
    
}

function buy_slots() {
    var qty = $('qty').value;
    
    if (isNaN(qty) || isEmpty(qty) || parseInt(qty) <= 0) {
        alert('You must purchase at least 1 slot.');
        return false;
    }
    
    var is_confirmed = confirm('You are about to purchase ' + qty + ' slot(s) at ' + $('currency').value + '$ ' + $('total_amount').get('html') + ".\n\nPlease click 'OK' to proceed to payment portal or 'Cancel' to continue using the available slots.");
    
    if (!is_confirmed) {
        set_status('');
        close_buy_slots_form();
        return false;
    }
    
    var payment_method = 'credit_card';
    if ($('payment_method_paypal').checked) {
        payment_method = 'paypal';
    } else if ($('payment_method_cheque').checked) {
        payment_method = 'cheque';
    }
    
    var params = 'id=' + id;
    params = params + '&action=buy_slots';
    params = params + '&currency=' + $('currency').value;
    params = params + '&price=' + parseFloat($('price_per_slot').get('html'));
    params = params + '&qty=' + parseInt(qty);
    params = params + '&amount=' + parseFloat($('total_amount').get('html'));
    params = params + '&payment_method=' + payment_method;
    
    var uri = root + "/employers/slots_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            close_buy_slots_form();
            set_status('<pre>' + txt + '</pre>');
            return;
            if (txt == 'ko') {
                alert('An error occured while purchasing slots.');
                return false;
            }
            
            if (txt == '-1') {
                alert('A payment instruction has been send to your email account. Please follow the instruction to lodge your payment.');
            }
            
            set_status('');
            close_buy_slots_form();
            show_purchase_histories();
            get_slots_left();
            
            
        },
        onRequest: function(instance) {
            set_status('Purchasing slots...');
        }
    });
    
    request.send(params);
}

function show_buy_slots_form() {
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_buy_slots_form').getStyle('height'));
    var div_width = parseInt($('div_buy_slots_form').getStyle('width'));
    
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
    
    if (window_height <= div_height) {
        $('div_buy_slots_form').setStyle('height', window_height);
        $('div_buy_slots_form').setStyle('top', 0);
        window.scrollTo(0, 0);
    } else {
        $('div_buy_slots_form').setStyle('top', ((window_height - div_height) / 2));
    }
    $('div_buy_slots_form').setStyle('left', ((window_width - div_width) / 2));
    
    $('div_blanket').setStyle('display', 'block');
    $('div_buy_slots_form').setStyle('display', 'block');
}

function close_buy_slots_form() {
    $('div_buy_slots_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function calculate_fee() {
    var price = parseFloat($('price_per_slot').get('html'));
    var qty = parseInt($('qty').value);
    var discount = 0;
    var amount = 0.00;
    
    if (qty <= 0 || isEmpty($('qty').value) || isNaN($('qty').value)) {
        $('total_amount').set('html', '0.00');
        return;
    } else if (qty > 5 && qty <= 15) {
        discount = 10;
    } else if (qty > 15 && qty <= 25) {
        discount = 15;
    } else if (qty > 25 && qty <= 35) {
        discount = 20;
    } else if (qty > 35) {
        discount = 25;
    } 
    
    amount = (price * qty) - ((price * qty) * (discount / 100));
    if (added_admin_fee) {
        amount = amount + (amount * 0.05);
    }
    
    $('discount').set('html', discount + '%');
    $('total_amount').set('html', amount);
}

function add_admin_fee() {
    if ($('payment_method_cheque').checked) {
        added_admin_fee = true;
        calculate_fee();
    }
}

function remove_admin_fee() {
    if (added_admin_fee) {
        added_admin_fee = false;
        calculate_fee();
    }
}

function onDomReady() {
    set_root();
    get_employer_referrals_count();
    
    get_slots_left();
    show_purchase_histories();
}

window.addEvent('domready', onDomReady);
