var order_by = 'bank';
var order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function close_bank_form() {
    $('bank_id').value = '0';
    $('div_bank_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_bank_form() {
    show_bank_form_with(0, '', '');
}

function show_bank_form_with(_bank_id, _bank, _account) {
    $('bank_id').value = _bank_id;
    $('bank').value = _bank;
    $('account').value = _account;
    
    $('div_blanket').setStyle('display', 'block');
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_bank_form').getStyle('height'));
    var div_width = parseInt($('div_bank_form').getStyle('width'));
    
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
    
    $('div_bank_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_bank_form').setStyle('left', ((window_width - div_width) / 2));
    $('div_bank_form').setStyle('display', 'block');
}

function delete_banks() {
    var inputs = $('list').getElementsByTagName('input');
    var payload = '<banks>' + "\n";
    var count = 0;
    
    for(i=0; i < inputs.length; i++) {
        var attributes = inputs[i].attributes;
        if (attributes.getNamedItem('type').value == 'checkbox' && 
            attributes.getNamedItem('name').value == 'id') {
            if (inputs[i].checked) {
                payload = payload + '<id>' + inputs[i].id + '</id>' + "\n";
                count++;
            }
        }
    }
    
    payload = payload + '</banks>';
    
    if (count <= 0) {
        set_status('Please select at least one account.');
        return false;
    }
    
    var proceed = confirm('Are you sure to delete the selected accounts?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=0';
    params = params + '&action=delete_bank';
    params = params + '&payload=' + payload;
    
    var uri = root + "/members/banks_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while deleting selected banks.');
                return false;
            }
            
            set_status('');
            show_banks();
        },
        onRequest: function(instance) {
            set_status('Deleting banks...');
        }
    });
    
    request.send(params);
}

function save_bank_account() {
    if (isEmpty($('bank').value) || isEmpty($('account').value)) {
        alert('You need to fill in all fields.');
        return false;
    }
    
    var params = 'id=' + $('bank_id').value + '&member=' + id;
    params = params + '&action=save_bank';
    params = params + '&bank=' + $('bank').value;
    params = params + '&account=' + $('account').value;
    
    var uri = root + "/members/banks_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while saving bank account.');
                return false;
            }
            
            set_status('');
            close_bank_form();
            show_banks();
        },
        onRequest: function(instance) {
            set_status('Saving bank account...');
        }
    });
    
    request.send(params);
}

function show_banks() {
    var params = 'id=' + id;
    params = params + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/members/banks_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading bank accounts.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">Please click on the \"Add Bank Account\" button to get started.</div>';
                
                $('delete_accounts').disabled = true;
                $('delete_accounts_1').disabled = true;
            } else {
                var ids = xml.getElementsByTagName('id');
                var banks = xml.getElementsByTagName('bank');
                var accounts = xml.getElementsByTagName('account');
                
                for (var i=0; i < ids.length; i++) {
                    var bank_id = ids[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ bank_id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="checkbox"><input type="checkbox" id="'+ bank_id + '" name="id" /></td>' + "\n";
                    html = html + '<td class="bank">' + banks[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="account">' + accounts[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="edit"><a class="no_link" onClick="show_bank_form_with(\'' + bank_id + '\', \'' + banks[i].childNodes[0].nodeValue + '\', \'' + accounts[i].childNodes[0].nodeValue + '\');">Edit</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
            }
            html = html + '</table>';
            
            $('div_list').set('html', html);
            set_status('');
            
            $('delete_accounts').disabled = false;
            $('delete_accounts_1').disabled = false;
        },
        onRequest: function(instance) {
            set_status('Loading bank accounts...');
        }
    });
    
    request.send(params);
}

function select_all_accounts() {
    var inputs = $('list').getElementsByTagName('input');
    
    if ($('select_all').checked) {
        for (i=0; i < inputs.length; i++) {
            var attributes = inputs[i].attributes;
            if (attributes.getNamedItem('type').value == 'checkbox' &&
                attributes.getNamedItem('name').value == 'id') {
                inputs[i].checked = true;
            }
        }
    } else {
        for (i=0; i < inputs.length; i++) {
            var attributes = inputs[i].attributes;
            if (attributes.getNamedItem('type').value == 'checkbox' &&
                attributes.getNamedItem('name').value == 'id') {
                inputs[i].checked = false;
            }
        }
    }
}

function toggle_banner() {
    var height = $('div_banner').getStyle('height');
    var params = 'id=' + id + '&action=set_hide_banner';
    
    if (parseInt(height) >= 100) {
        $('hide_show_label').set('html', 'Show');
        $('div_banner').tween('height', '15px');
        params = params + '&hide=1';
    } else {
        $('hide_show_label').set('html', 'Hide');
        $('div_banner').tween('height', '225px');
        params = params + '&hide=0';
    }
    
    var uri = root + "/members/banks_action.php";
    var request = new Request({
        url: uri,
        method: 'post'
    });
    
    request.send(params);
}

function hide_show_banner() {
    var params = 'id=' + id + '&action=get_hide_banner';
    
    var uri = root + "/members/banks_action.php";
    var request = new Request({
        url: uri,
        method: 'post', 
        onSuccess: function(txt, xml) {
            if (txt == '1') {
                $('hide_show_label').set('html', 'Show');
                $('div_banner').setStyle('height', '15px');
            } else {
                $('hide_show_label').set('html', 'Hide');
                $('div_banner').setStyle('height', '225px');
            }
        }
    });
    
    request.send(params);
}

function onDomReady() {
    initialize_page();
    get_employers_for_mini();
    get_industries_for_mini();
    set_mini_keywords();
    get_referrals_count();
    get_requests_count();
    get_jobs_employed_count();
    
    hide_show_banner();
    
    $('delete_accounts').addEvent('click', delete_banks);
    $('delete_accounts_1').addEvent('click', delete_banks);
    $('add_new_account').addEvent('click', show_bank_form);
    $('add_new_account_1').addEvent('click', show_bank_form);
    $('select_all').addEvent('click', select_all_accounts);
    
    $('sort_bank').addEvent('click', function() {
        order_by = 'bank';
        ascending_or_descending();
        show_banks();
    });
    
    $('sort_account').addEvent('click', function() {
        order_by = 'account';
        ascending_or_descending();
        show_banks();
    });
    
    show_banks();
}

window.addEvent('domready', onDomReady);
