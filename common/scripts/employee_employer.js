var is_profile_dirty = false;

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

function reset_password() {
    var proceed = confirm('Are you sure to reset the password?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=' + employer_id;
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

function profile_is_dirty() {
    is_profile_dirty = true;
}

function validate_profile_form() {
    if (isEmpty($('business_license').value)) {
        alert('Business license cannot be empty.');
        return false;
    }
    
    // is new employer?
    if (employer_id == '0') {
        if (isEmpty($('user_id').value)) {
            alert('User ID cannot be empty');
            return false;
        }
        
        /*if (isEmpty($('free_postings').value) || isNaN($('free_postings').value)) {
            $('free_postings').value = '0';
        }
        
        if (parseInt($('free_postings').value) < 0) {
            set_status('Free Job Postings must be either 0 or more.');
            return false;
        }
        
        if (isEmpty($('paid_postings').value) || isNaN($('paid_postings').value)) {
            $('paid_postings').value = '0';
        }
        
        if (parseInt($('paid_postings').value) < 0) {
            set_status('Paid Job Postings must be either 0 or more.');
            return false;
        }*/
    }
    
    if (!isEmail($('email').value)) {
        alert('Please provide a valid e-mail address.');
        return false;
    }
    
    if (isEmpty($('name').value)) {
        alert('Business name cannot be empty.');
        return false;
    }
    
    if (isEmpty($('contact_person').value)) {
        alert('Contact person cannot be empty.');
        return false;
    }
    
    if (isEmpty($('phone_num').value)) {
        alert('Telephone number cannot be empty.');
        return false;
    }
    
    if (isEmpty($('zip').value)) {
        alert('Zip/Postal code cannot be empty.');
        return false;
    }
    
    if ($('country').options[$('country').selectedIndex].value == '0') {
        alert('You need to select a country where this employer is located at.');
        return false;
    }
    
    /*if (isEmpty($('working_months').value)) {
        set_status('Working months cannot be empty.');
        return false;
    } else if (!isNumeric($('working_months').value)) {
        set_status('Working months must be a number from 1-12.');
        return false;
    } else if (parseInt($('working_months').value) < 1 || parseInt($('working_months').value) > 12) {
        set_status('Working months must be a number from 1-12.');
        return false;
    }*/
    
    return true;
    
}

function show_profile() {
    $('employer_profile').setStyle('display', 'block');
    $('employer_fees').setStyle('display', 'none');
    $('employer_jobs').setStyle('display', 'none');
    $('employer_subscriptions').setStyle('display', 'none');
    
    $('item_profile').setStyle('background-color', '#CCCCCC');
    $('item_fees').setStyle('background-color', '');
    $('item_jobs').setStyle('background-color', '');
    $('item_subscriptions').setStyle('background-color', '');
}

function save_profile() {
    if (!validate_profile_form()) {
        return false;
    }
    
    var mode = 'update';
    if (employer_id == '0') {
        mode = 'create';
    }
    
    var params = 'id=' + employer_id;
    params = params + '&action=save_profile';
    params = params + '&employee=' + user_id;
    params = params + '&license_num=' + $('business_license').value
    params = params + '&email_addr=' + $('email').value;
    params = params + '&name=' + $('name').value;
    params = params + '&contact_person=' + $('contact_person').value;
    params = params + '&phone_num=' + $('phone_num').value;
    params = params + '&address=' + $('address').value;
    params = params + '&state=' + $('state').value;
    params = params + '&zip=' + $('zip').value;
    params = params + '&country=' + $('country').options[$('country').selectedIndex].value;
    params = params + '&website_url=' + $('website_url').value;
    // params = params + '&working_months=' + $('working_months').value;
    //     params = params + '&payment_terms_days=' + $('payment_terms_days').options[$('payment_terms_days').selectedIndex].value;
    // params = params + '&paid_postings=' + $('paid_postings').value;
    //     params = params + '&subscription_period=' + $('subscription_period').options[$('subscription_period').selectedIndex].value;
    
    if (mode == 'create') {
        params = params + '&user_id=' + $('user_id').value;
        // params = params + '&free_postings=' + $('free_postings').value;
    } 
    
    var uri = root + "/employees/employer_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            // set_status('<pre>' + txt + '</pre>');
            // return;
            set_status('');
            if (txt == 'ko') {
                alert('An error occured while saving profile. Please makesure the Business License and User ID do not already exist in the system.');
                return false;
            }
            
            if (mode == 'create') {
                employer_id = $('user_id').value;
                
                if (!isEmpty(from_employer)) {
                    var proceed_copy = confirm('Do you want the service feess to be copied too?');
                    if (proceed_copy) {
                        copy_fees();
                    }
                }
            }
            
            is_profile_dirty = false;
            
            show_profile();
            
        },
        onRequest: function(instance) {
            set_status('Saving profile...');
        }
    });
    
    request.send(params);
}

function copy_fees() {
    var params = 'id=' + employer_id + '&employer=' + from_employer + '&action=copy_fees';
    
    var uri = root + "/employees/employer_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while copying service fees.');
                return false;
            }
            
            from_employer = '0';
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Copying service fees and extra fees...');
        }
    });
    
    request.send(params);
}

function show_fees() {
    $('employer_profile').setStyle('display', 'none');
    $('employer_fees').setStyle('display', 'block');
    $('employer_jobs').setStyle('display', 'none');
    $('employer_subscriptions').setStyle('display', 'none');
    
    $('item_profile').setStyle('background-color', '');
    $('item_fees').setStyle('background-color', '#CCCCCC');
    $('item_jobs').setStyle('background-color', '');
    $('item_subscriptions').setStyle('background-color', '');
}

function show_updated_fees() {
    var params = 'id=' + employer_id + '&action=get_fees';
    
    var uri = root + "/employees/employer_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            // set_status('<pre>' + txt + '</pre>');
            // return;
            if (txt == 'ko') {
                a('An error occured while loading fees.');
                return false;
            }
            
            if (txt == '0') {
                $('fees').set('html', '<div class="empty_results">No fees set at this moment.</div>');
            } else {
                var ids = xml.getElementsByTagName('id');
                var salary_starts = xml.getElementsByTagName('salary_start');
                var salary_ends = xml.getElementsByTagName('salary_end');
                var service_fees = xml.getElementsByTagName('service_fee');
                var reward_percentages = xml.getElementsByTagName('reward_percentage');
                var guarantee_months = xml.getElementsByTagName('guarantee_months');
                
                var fees_table = new FlexTable('fees_table', 'fees_table');

                var header = new Row('');
                header.set(0, new Cell('Annual Salary From', '', 'header'));
                header.set(1, new Cell('Annual Salary Until', '', 'header'));
                header.set(2, new Cell('Guaranteed Period (in months)', '', 'header'));
                header.set(3, new Cell('Service Fee (%)', '', 'header'));
                header.set(4, new Cell('Reward (%)', '', 'header'));
                header.set(5, new Cell('&nbsp;', '', 'header action'));
                fees_table.set(0, header);
                
                for (var i=0; i < ids.length; i++) {
                    var row = new Row('');
                    
                    row.set(0, new Cell(salary_starts[i].childNodes[0].nodeValue, '', 'cell'));
                    row.set(1, new Cell(salary_ends[i].childNodes[0].nodeValue, '', 'cell'));
                    row.set(2, new Cell(guarantee_months[i].childNodes[0].nodeValue, '', 'cell center'));
                    row.set(3, new Cell(service_fees[i].childNodes[0].nodeValue, '', 'cell center'));
                    row.set(4, new Cell(reward_percentages[i].childNodes[0].nodeValue, '', 'cell center'));
                    
                    var actions = '<input type="button" value="Delete" onClick="delete_fee(' + ids[i].childNodes[0].nodeValue + ');" />';
                    actions = actions + '<input type="button" value="Update" onClick="show_fee_window(' + ids[i].childNodes[0].nodeValue + ', ' + salary_starts[i].childNodes[0].nodeValue + ', ' + salary_ends[i].childNodes[0].nodeValue + ', ' + guarantee_months[i].childNodes[0].nodeValue + ', ' + service_fees[i].childNodes[0].nodeValue + ', ' + reward_percentages[i].childNodes[0].nodeValue + ');" />';
                    row.set(5, new Cell(actions, '', 'cell action'));
                    fees_table.set((parseInt(i)+1), row);
                }
                
                $('fees').set('html', fees_table.get_html());
                set_status('');
            }
        },
        onRequest: function(instance) {
            set_status('Loading fees...');
        }
    });
    
    request.send(params);
}

function delete_fee(_id) {
    var proceed = confirm('Are you sure to delete the service fee?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=' + _id;
    params = params + '&action=delete_fee';
    
    var uri = root + "/employees/employer_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == 'ko') {
                alert('An error occured while deleting fee.');
                return false;
            }
            
            show_updated_fees();
        },
        onRequest: function(instance) {
            set_status('Loading service fee...');
        }
    });
    
    request.send(params);
}

/*

function close_service_fee_form() {
    $('service_fee_id').value = '0';
    salary_range_dirtied = false;
    $('div_service_fee_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_new_service_fee_form() {
    salary_range_dirtied = true;
    show_service_fee_form(0);
}

function show_service_fee_form(_id) {
    $('service_fee_id').value = _id;
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_service_fee_form').getStyle('height'));
    var div_width = parseInt($('div_service_fee_form').getStyle('width'));
    
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
    
    $('div_service_fee_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_service_fee_form').setStyle('left', ((window_width - div_width) / 2));
    
    if (_id != '0') {
        var params = 'id=' + _id + '&action=get_fee';

        var uri = root + "/employees/employers_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    alert('An error occured while loading service fee.');
                    return false;
                }

                if (txt != '0') {
                    var ids = xml.getElementsByTagName('id');
                    var salary_starts = xml.getElementsByTagName('salary_start');
                    var salary_ends = xml.getElementsByTagName('salary_end');
                    var guarantee_months = xml.getElementsByTagName('guarantee_months');
                    var service_fees = xml.getElementsByTagName('service_fee');
                    var discounts = xml.getElementsByTagName('discount');
                    var reward_percentages = xml.getElementsByTagName('reward_percentage');
                    
                    $('salary_start').value = salary_starts[0].childNodes[0].nodeValue;
                    $('salary_end').value = salary_ends[0].childNodes[0].nodeValue;
                    $('guarantee_months').value = guarantee_months[0].childNodes[0].nodeValue;
                    $('service_fee').value = service_fees[0].childNodes[0].nodeValue;
                    $('discount').value = discounts[0].childNodes[0].nodeValue;
                    $('reward_percentage').value = reward_percentages[0].childNodes[0].nodeValue;
                }

                set_status('');
            },
            onRequest: function(instance) {
                set_status('Loading fee...');
            }
        });

        request.send(params);
    }
    
    $('div_blanket').setStyle('display', 'block');
    $('div_service_fee_form').setStyle('display', 'block');
}




function save_service_fee() {
    if (!isNumeric($('salary_start').value)) {
        alert('Salary Start must be a number.');
        return false;
    } else if (parseFloat($('salary_start').value) < 1.00) {
        alert('Salary Start must be at least 1.00.');
        return false;
    }
    
    if (!isNumeric($('salary_end').value)) {
        alert('Salary End must be a number.');
        return false;
    } else if (parseFloat($('salary_end').value) < 0.00) {
        alert('Salary End can only accept 0.00 as an infinity number.');
        return false;
    }
    
    if (parseFloat($('salary_end').value) > 0.00 && 
        parseFloat($('salary_start').value) >= parseFloat($('salary_end').value)) {
        alert('Salary Start must be less than Salary End.');
        return false;
    } 
    
    if (!isNumeric($('guarantee_months').value)) {
        alert('Guaranteed Months must be a number from 0 to 12.');
        return false;
    } else if (parseInt($('guarantee_months').value) < 0 || parseInt($('guarantee_months').value) > 12) {
        alert('Guaranteed Months can only accept 0 to 12.');
        return false;
    }
    
    if (!isNumeric($('service_fee').value)) {
        alert('Service Fee must be a number from 0.00 to 100.00.');
        return false;
    } else if (parseFloat($('service_fee').value) < 0.00 || parseFloat($('guarantee_months').value) > 100.00) {
        alert('Service Fee can only accept 0.00 to 100.00.');
        return false;
    }
    
    if (!isNumeric($('discount').value)) {
        alert('Discount must be a number from 0.00 to 100.00.');
        return false;
    } else if (parseFloat($('discount').value) < 0.00 || parseFloat($('discount').value) > 100.00) {
        alert('Discount can only accept 0.00 to 100.00.');
        return false;
    }
    
    if (!isNumeric($('reward_percentage').value)) {
        alert('Reward must be a number from 0.00 to 100.00.');
        return false;
    } else if (parseFloat($('reward_percentage').value) < 0.00 || parseFloat($('reward_percentage').value) > 100.00) {
        alert('Reward can only accept 0.00 to 100.00.');
        return false;
    }
    
    var params = 'id=' + $('service_fee_id').value;
    params = params + '&action=save_service_fee';
    params = params + '&employer=' + $('employer_id').value;
    params = params + '&salary_start=' + $('salary_start').value;
    params = params + '&salary_end=' + $('salary_end').value;
    params = params + '&guarantee_months=' + $('guarantee_months').value;
    params = params + '&service_fee=' + $('service_fee').value;
    params = params + '&discount=' + $('discount').value;
    params = params + '&reward_percentage=' + $('reward_percentage').value;
    
    if (salary_range_dirtied) {
        params = params + '&salary_range_check=1';
    }
    
    var uri = root + "/employees/employers_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while saving service fee.');
                salary_range_dirtied = false;
                return false;
            } 
            
            if (txt == '-1') {
                alert('The salary range you specified already exists, or is overlapping with an existing service fee.\n\nPlease either specify a new range or remove the existing range to allow this new service fee to be entered.');
                set_status('');
                salary_range_dirtied = false;
                return false;
            }
            
            close_service_fee_form();
            show_service_fees();
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Saving service fee...');
        }
    });
    
    request.send(params);
}

function delete_charges() {
    var inputs = $('extra_fees_list').getElementsByTagName('input');
    var payload = '<fees>' + "\n";
    var count = 0;
    
    for(i=0; i < inputs.length; i++) {
        var attributes = inputs[i].attributes;
        if (attributes.getNamedItem('type').value == 'checkbox') {
            if (inputs[i].checked) {
                payload = payload + '<id>' + inputs[i].id + '</id>' + "\n";
                count++;
            }
        }
    }
    
    payload = payload + '</fees>';
    
    if (count <= 0) {
        set_status('Please select at least one extra charge.');
        return false;
    }
    
    var proceed = confirm('Are you sure to delete the selected extra charges?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=0';
    params = params + '&action=delete_charges';
    params = params + '&payload=' + payload;
    
    var uri = root + "/employees/employers_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while deleting selected charges.');
                return false;
            }
            
            set_status('');
            show_extra_fees();
        },
        onRequest: function(instance) {
            set_status('Loading extra charges...');
        }
    });
    
    request.send(params);
}

function save_extra_fee() {
    if (isEmpty($('charge_label').value)) {
        alert('You need to provide a label for this charge.');
        return false;
    }
    
    if (!isNumeric($('amount').value)) {
        alert('Amount must be a number.');
        return false;
    } 
    
    var params = 'id=' + $('extra_fee_id').value;
    params = params + '&action=save_extra_charge';
    params = params + '&employer=' + $('employer_id').value;
    params = params + '&label=' + $('charge_label').value;
    params = params + '&charges=' + $('amount').value;
    
    var uri = root + "/employees/employers_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while saving extra fee.');
                return false;
            } 
            
            close_extra_fee_form();
            show_extra_fees();
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Saving extra charge...');
        }
    });
    
    request.send(params);
}

function select_all_employers() {
    var inputs = $('list').getElementsByTagName('input');
    
    if ($('deactivate_all').checked) {
        for (i=0; i < inputs.length; i++) {
            var attributes = inputs[i].attributes;
            if (attributes.getNamedItem('type').value == 'checkbox') {
                inputs[i].checked = true;
            }
        }
    } else {
        for (i=0; i < inputs.length; i++) {
            var attributes = inputs[i].attributes;
            if (attributes.getNamedItem('type').value == 'checkbox') {
                inputs[i].checked = false;
            }
        }
    }
}

function select_all_service_fees() {
    var inputs = $('service_fees_list').getElementsByTagName('input');
    
    if ($('delete_all_service_fees').checked) {
        for (i=0; i < inputs.length; i++) {
            var attributes = inputs[i].attributes;
            if (attributes.getNamedItem('type').value == 'checkbox') {
                inputs[i].checked = true;
            }
        }
    } else {
        for (i=0; i < inputs.length; i++) {
            var attributes = inputs[i].attributes;
            if (attributes.getNamedItem('type').value == 'checkbox') {
                inputs[i].checked = false;
            }
        }
    }
}

function select_all_extra_fees() {
    var inputs = $('extra_fees_list').getElementsByTagName('input');
    
    if ($('delete_all_extra_fees').checked) {
        for (i=0; i < inputs.length; i++) {
            var attributes = inputs[i].attributes;
            if (attributes.getNamedItem('type').value == 'checkbox') {
                inputs[i].checked = true;
            }
        }
    } else {
        for (i=0; i < inputs.length; i++) {
            var attributes = inputs[i].attributes;
            if (attributes.getNamedItem('type').value == 'checkbox') {
                inputs[i].checked = false;
            }
        }
    }
}
*/

function onDomReady() {
    initialize_page();
}

window.addEvent('domready', onDomReady);
