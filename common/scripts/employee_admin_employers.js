var order_by = 'employers.joined_on';
var order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function show_employers() {
    var params = 'id=0&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employees/admin_employers_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading employers.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no employers.</div>';
            } else {
                var ids = xml.getElementsByTagName('id');
                var employers = xml.getElementsByTagName('name');
                var joined_ons = xml.getElementsByTagName('formatted_joined_on');
                var days_lefts = xml.getElementsByTagName('days_left');
                
                for (var i=0; i < ids.length; i++) {
                    var employer_id = ids[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ employer_id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="user_id">' + employer_id + '</td>' + "\n";
                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="days">' + days_lefts[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + joined_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="actions"><input type="button" value="Reset Password" onClick="reset_password(\'' + employer_id + '\');" /></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
            }
            html = html + '</table>';
            
            $('div_employers_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading employers...');
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
    
    var uri = root + "/employees/admin_employers_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while reseting password.');
                return false;
            }
            
            alert('Password successfully reset! An e-mail has been send to the employer. ');
        },
        onRequest: function(instance) {
            set_status('Resetting password...');
        }
    });
    
    request.send(params);
}

function onDomReady() {
    set_root();
    get_unapproved_photos_count();
    
    $('sort_employer').addEvent('click', function() {
        order_by = 'employers.name';
        ascending_or_descending();
        show_employers();
    });
    
    $('sort_user_id').addEvent('click', function() {
        order_by = 'employers.id';
        ascending_or_descending();
        show_employers();
    });
    
    $('sort_joined_on').addEvent('click', function() {
        order_by = 'employers.joined_on';
        ascending_or_descending();
        show_employers();
    });
    
    $('sort_days_left').addEvent('click', function() {
           order_by = 'days_left';
           ascending_or_descending();
           show_employers();
       });
    
    show_employers();
}

window.addEvent('domready', onDomReady);
