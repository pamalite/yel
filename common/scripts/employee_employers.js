var order_by = 'employers.joined_on';
var order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function sort_by(_table, _column) {
    switch (_table) {
        case 'employers':
            order_by = _column;
            ascending_or_descending();
            show_employers();
            break;
    }
}

function show_employers() {
    var params = 'id=' + user_id + '&order_by=' + order_by + ' ' + order;
    params = params + '&action=get_employers';
    
    var uri = root + "/employees/employers_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            // set_status('<pre>' + txt + '</pre>');
            // return;
            if (txt == 'ko') {
                alert('An error occured while loading employers.');
                return false;
            }
            
            if (txt == '0') {
                $('div_employers').set('html', '<div class="empty_results">No employers at this moment.</div>');
            } else {
                var ids = xml.getElementsByTagName('id');
                var employers = xml.getElementsByTagName('name');
                var phone_nums = xml.getElementsByTagName('phone_num');
                var faxes = xml.getElementsByTagName('fax_num');
                var emails = xml.getElementsByTagName('email_addr');
                var contacts = xml.getElementsByTagName('contact_person');
                var registered_bys = xml.getElementsByTagName('employee');
                var joined_ons = xml.getElementsByTagName('formatted_joined_on');
                var first_logins = xml.getElementsByTagName('formatted_first_login');
                var is_actives = xml.getElementsByTagName('active');
                
                var employers_table = new FlexTable('employers_table', 'employers');

                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('employers', 'employers.joined_on');\">Joined On</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('employers', 'employers.name');\">Employer</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('employers', 'employees.lastname');\">Registered By</a>", '', 'header'));
                header.set(3, new Cell("<a class=\"sortable\" onClick=\"sort_by('employers', 'employer_sessions.first_login');\">First Login</a>", '', 'header'));
                header.set(4, new Cell('&nbsp;', '', 'header action'));
                employers_table.set(0, header);
                
                for (var i=0; i < ids.length; i++) {
                    var row = new Row('');
                    
                    row.set(0, new Cell(joined_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var short_desc = '<a class="no_link employer_link" onClick="show_employer(\'' + ids[i].childNodes[0].nodeValue + '\');">' + employers[i].childNodes[0].nodeValue + '</a>' + "\n";
                    short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Tel.:</span> ' + phone_nums[i].childNodes[0].nodeValue + '</div>' + "\n";
                    
                    var fax = '';
                    if (faxes[i].childNodes.length > 0) {
                        fax = faxes[i].childNodes[0].nodeValue;
                    }
                    short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Fax:</span> ' + fax + '</div>' + "\n";
                    short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Email:</span> ' + emails[i].childNodes[0].nodeValue + '</div>' + "\n";
                    short_desc = short_desc +  '<div class="small_contact"><span style="font-weight: bold;">Contact:</span> ' + contacts[i].childNodes[0].nodeValue + '</div>' + "\n";
                    row.set(1, new Cell(short_desc, '', 'cell'));
                    
                    row.set(2, new Cell(registered_bys[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    var first_login = '';
                    if (first_logins[i].childNodes.length > 0) {
                        first_login = first_logins[i].childNodes[0].nodeValue;
                    }
                    row.set(3, new Cell(first_login, '', 'cell'));
                    
                    var actions = '';
                    if (is_actives[i].childNodes[0].nodeValue == 'Y') {
                        actions = '<input type="button" id="activate_button_' + i + '" value="De-activate" onClick="activate_employer(\'' + ids[i].childNodes[0].nodeValue + '\', \'' + i + '\');" />';
                        actions = actions + '<input type="button" id="password_reset_' + i + '" value="Reset Password" onClick="reset_password(\'' + ids[i].childNodes[0].nodeValue + '\');" />';
                    } else {
                        actions = '<input type="button" id="activate_button_' + i + '" value="Activate" onClick="activate_employer(\'' + ids[i].childNodes[0].nodeValue + '\', \'' + i + '\');" />';
                        actions = actions + '<input type="button" id="password_reset_' + i + '" value="Reset Password" onClick="reset_password(\'' + ids[i].childNodes[0].nodeValue + '\');" disabled />';
                    }
                    actions = actions + '<input type="button" value="New From" onClick="add_new_employer(\'' + ids[i].childNodes[0].nodeValue + '\');" />';
                    
                    row.set(4, new Cell(actions, '', 'cell action'));
                    employers_table.set((parseInt(i)+1), row);
                }
                
                $('div_employers').set('html', employers_table.get_html());
                set_status('');
            }
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
    
    var uri = root + "/employees/employers_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while reseting password.');
                return false;
            }
            
            set_status('Password successfully reset! An e-mail has been send to the employer. ');
        },
        onRequest: function(instance) {
            set_status('Resetting password...');
        }
    });
    
    request.send(params);
}

function deactivate_employer(_id, _idx) {
    var proceed = confirm('Are you sure to de-activate employer?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=' + _id + '&action=deactivate';
    
    var uri = root + "/employees/employers_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while deactivating employer.');
                return false;
            }
            
            set_status('');
            $('activate_button_' + _idx).value = 'Activate';
            $('password_reset_' + _idx).disabled = true;
        },
        onRequest: function(instance) {
            set_status('De-activating employers...');
        }
    });
    
    request.send(params);
}

function activate_employer(_id, _idx) {
    if ($('activate_button_' + _idx).value == 'De-activate') {
        return deactivate_employer(_id, _idx);
    }
    
    var proceed = confirm('Are you sure to activate employer?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=' + _id + '&action=activate';
    
    var uri = root + "/employees/employers_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while activating employer.');
                return false;
            }
            
            set_status('');
            $('activate_button_' + _idx).value = 'De-activate';
            $('password_reset_' + _idx).disabled = false;
        },
        onRequest: function(instance) {
            set_status('Activating employers...');
        }
    });
    
    request.send(params);
}

function show_employer(_id) {
    $('id').value = _id;
    $('from_employer').value = '';
    $('employer_page_form').submit();
}

function add_new_employer() {
    $('id').value = '';
    $('from_employer').value = '';
    if (arguments.length > 0) {
        $('from_employer').value = arguments[0];
    }
    
    $('employer_page_form').submit();
}

function onDomReady() {
    initialize_page();
}

window.addEvent('domready', onDomReady);
