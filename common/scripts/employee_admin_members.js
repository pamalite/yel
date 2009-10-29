var order_by = 'joined_on';
var order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function show_members() {
    var params = 'id=0&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employees/admin_members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading members.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no members.</div>';
            } else {
                var ids = xml.getElementsByTagName('email_addr');
                var members = xml.getElementsByTagName('fullname');
                var joined_ons = xml.getElementsByTagName('formatted_joined_on');
                var actives = xml.getElementsByTagName('active');
                
                for (var i=0; i < ids.length; i++) {
                    var member_id = ids[i].childNodes[0].nodeValue;
                    var active = actives[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ member_id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="email_addr">' + member_id + '</td>' + "\n";
                    html = html + '<td class="member">' + members[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + joined_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    if (active == 'Y') {
                        html = html + '<td class="actions"><input type="button" value="Reset Password" onClick="reset_password(\'' + member_id + '\');" /></td>' + "\n";
                    } else {
                        html = html + '<td class="actions"><input type="button" value="Activate" onClick="activate_member(\'' + member_id + '\');" /></td>' + "\n";
                    }
                    
                    html = html + '</tr>' + "\n";
                }
            }
            html = html + '</table>';
            
            $('div_members_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading members...');
        }
    });
    
    request.send(params);
}

function reset_password(_id) {
    var proceed = confirm('Are you sure to reset the password?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=' + _id;
    params = params + '&action=reset_password';
    
    var uri = root + "/employees/admin_members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while reseting password.');
                return false;
            }
            
            alert('Password successfully reset! An e-mail has been send to the member. ');
        },
        onRequest: function(instance) {
            set_status('Resetting password...');
        }
    });
    
    request.send(params);
}

function activate_member(_id) {
    var proceed = confirm('Are you sure to re-activate the member?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=' + _id;
    params = params + '&action=activate';
    
    var uri = root + "/employees/admin_members_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while activating member.');
                return false;
            }
            
            show_members();
            alert('Password successfully re-activated the member! An e-mail has been send to the member. ');
        },
        onRequest: function(instance) {
            set_status('Activating member...');
        }
    });
    
    request.send(params);
}

function onDomReady() {
    set_root();
    get_unapproved_photos_count();
    get_employee_rewards_count();
    get_employee_tokens_count();
    
    $('sort_email_addr').addEvent('click', function() {
        order_by = 'email_addr';
        ascending_or_descending();
        show_members();
    });
    
    $('sort_member').addEvent('click', function() {
        order_by = 'fullname';
        ascending_or_descending();
        show_members();
    });
    
    $('sort_joined_on').addEvent('click', function() {
        order_by = 'joined_on';
        ascending_or_descending();
        show_members();
    });
    
    show_members();
}

window.addEvent('domready', onDomReady);
