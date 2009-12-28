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

function buy_slot() {
    var month = $('month_list_dropdown').options[$('month_list_dropdown').selectedIndex].value;
    var day = parseInt($('day').value);
    if (isNaN(day) || day < 1 || day > 31) {
        alert('Day must be in the range of 1-31, inclusive.');
        return false;
    }
    
    if (parseFloat($('salary').value) < 1 || isNaN($('salary').value)) {
        alert('Annual salary cannot be less than 1.00.');
        return false;
    }
    
    var commence = $('year_label').get('html') + '-' + month + '-' + day;
    
    var is_employment = confirm('You are about confirm this employment.\n\nPlease click \'OK\' to confirm the employment, or \'Cancel\' if you are requesting for a replacement instead.');
    
    if (!is_employment) {
        alert('Please call or e-mail us if you want to request for a replacement instead.');
        set_status('');
        close_employ_form();
        return false;
    }
    
    var params = 'id=' + employ_referral_id;
    params = params + '&action=employ_candidate';
    params = params + '&employer=' + id;
    params = params + '&commence=' + commence;
    params = params + '&salary=' + $('salary').value;
    params = params + '&used_suggested=' + used_suggested;
    
    var uri = root + "/employers/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while employing candidate.');
                return false;
            }
            
            if (txt == '-1') {
                alert('Seems like your account is not ready. Please contact your account manager about this problem.\n\nReminder: Please allow up to 24 hours for your account to be prepared by your account manager, upon receiving the new account e-mail.');
                return false;
            }
            
            set_status('');
            close_employ_form();
            show_referred_candidates();
        },
        onRequest: function(instance) {
            set_status('Employing candidate...');
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
    
    $('discount').set('html', discount + '%');
    $('total_amount').set('html', amount);
}

function onDomReady() {
    set_root();
    get_employer_referrals_count();
    
    get_slots_left();
    show_purchase_histories();
}

window.addEvent('domready', onDomReady);
