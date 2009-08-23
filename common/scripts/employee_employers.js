var selected_tab = 'li_profile';
var order_by = 'joined_on';
var order = 'desc';

var copy_from_employer = '0';
var salary_range_dirtied = false;
var is_profile_dirty = false;

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function profile_is_dirty() {
    is_profile_dirty = true;
}

function is_valid_user_id(_user_id) {
    if (_user_id.length > 10) {
        return false;
    }
    
    var allowed_characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890_';
    for (var i=0; i < _user_id.length; i++) {
        var allowed = false;
        for (var j=0; j < allowed_characters.length; j++) {
            if (_user_id.charAt(i) == allowed_characters.charAt(j)) {
                allowed = true;
                break;
            }
        }
        
        if (!allowed) {
            return false;
        }
    }
    
    return true;
}

function count_user_id_characters() {
    if (!is_valid_user_id($('user_id').value)) {
        $('user_id_warning').set('html', 'Over 10 Characters!!!');
    } else {
        $('user_id_warning').set('html', '');
    }
}

function validate_profile_form() {
    if (isEmpty($('business_license').value)) {
        set_status('Business license cannot be empty.');
        return false;
    }
    
    if ($('employer_id').value == '0') {
        if (isEmpty($('user_id').value)) {
            set_status('User ID cannot be empty');
            return false;
        }
        
        if (isEmpty($('password').value)) {
            set_status('Password cannot be empty');
            return false;
        }
        
        if (!is_valid_user_id($('user_id').value)) {
            set_status('User ID must be at most 10 characters.');
            return false;
        }
    }
    
    if (isEmpty($('email').value)) {
        set_status('E-mail address cannot be empty.');
        return false;
    } else if (!isEmail($('email').value)) {
        set_status('Please provide a valid e-mail address.');
        return false;
    }
    
    if (isEmpty($('name').value)) {
        set_status('Business name cannot be empty.');
        return false;
    }
    
    if (isEmpty($('contact_person').value)) {
        set_status('Contact person cannot be empty.');
        return false;
    }
    
    if (isEmpty($('phone_num').value)) {
        set_status('Telephone number cannot be empty.');
        return false;
    }
    
    if (isEmpty($('zip').value)) {
        set_status('Zip/Postal code cannot be empty.');
        return false;
    }
    
    if ($('country_dropdown').options[$('country_dropdown').selectedIndex].value == '0') {
        set_status('You need to select a country where this employer is located at.');
        return false;
    }
    
    if (isEmpty($('working_months').value)) {
        set_status('Working months cannot be empty.');
        return false;
    } else if (!isNumeric($('working_months').value)) {
        set_status('Working months must be a number from 1-12.');
        return false;
    } else if (parseInt($('working_months').value) < 1 || parseInt($('working_months').value) > 12) {
        set_status('Working months must be a number from 1-12.');
        return false;
    }
    
    /*if (isEmpty($('bonus_months').value)) {
        set_status('Bonus months cannot be empty.');
        return false;
    } else if (!isNumeric($('bonus_months').value)) {
        set_status('Bonus months must be a number from 1-12.');
        return false;
    } else if (parseInt($('bonus_months').value) < 1 || parseInt($('bonus_months').value) > 12) {
        set_status('Bonus months must be a number from 1-12.');
        return false;
    }
    */
    
    return true;
}

function copy_fees_and_extras() {
    var params = 'id=' + $('employer_id').value + '&employer=' + copy_from_employer + '&action=copy_fees_and_extras';
    
    var uri = root + "/employees/employers_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while copying service fees and extra fees.');
                return false;
            }
            
            copy_from_employer = '0';
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Copying service fees and extra fees...');
        }
    });
    
    request.send(params);
}

function show_employers() {
    $('div_employers').setStyle('display', 'block');
    $('div_employer').setStyle('display', 'none');
    
    var params = 'id=0&employee=' + id + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employees/employers_action.php";
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
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">You have not created any employers.</div>';
                
                $('deactivate_employers').disabled = true;
                $('deactivate_employers_1').disabled = true;
            } else {
                var ids = xml.getElementsByTagName('id');
                var employers = xml.getElementsByTagName('name');
                var joined_ons = xml.getElementsByTagName('formatted_joined_on');
                var first_logins = xml.getElementsByTagName('formatted_first_login');
                var is_actives = xml.getElementsByTagName('active');
                
                for (var i=0; i < ids.length; i++) {
                    var employer_id = ids[i].childNodes[0].nodeValue;
                    var active = is_actives[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ employer_id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    
                    if (active == 'Y') {
                        html = html + '<td class="checkbox"><input type="checkbox" id="'+ employer_id + '" /></td>' + "\n";
                    } else {
                        html = html + '<td class="checkbox">&nbsp;</td>' + "\n";
                    }
                    
                    html = html + '<td class="user_id">' + employer_id + '</td>' + "\n";
                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + joined_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    var first_login = '<span style="font-size: 7pt; color: #666666;">Pending...</span>';
                    if (first_logins[i].childNodes.length > 0) {
                        first_login = first_logins[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="date">' + first_login + '</td>' + "\n";
                    
                    if (active == 'Y') {
                        html = html + '<td class="actions"><input type="button" value="Update" onClick="show_employer(\'' + employer_id + '\');" />&nbsp;<input type="button" value="New from this Employer" onClick="new_from_employer(\'' + employer_id + '\');" /></td>' + "\n";
                    } else {
                        html = html + '<td class="actions"><input type="button" value="Activate" onClick="activate_employer(\'' + employer_id + '\');" />&nbsp;<input type="button" value="New from this Employer" onClick="new_from_employer(\'' + employer_id + '\');" /></td>' + "\n";
                    }
                    
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

function add_new_employer() {
    $('employer_id').value = '0';
    copy_from_employer = '0';
    $('div_employers').setStyle('display', 'none');
    $('div_employer').setStyle('display', 'block');
    
    selected_tab = 'li_profile';
    $('li_profile').setStyle('border', '1px solid #CCCCCC');
    $('li_service_fees').setStyle('border', '1px solid #0000FF');
    $('li_extra_fees').setStyle('border', '1px solid #0000FF');
    $('div_profile').setStyle('display', 'block');
    $('div_service_fees').setStyle('display', 'none');
    $('div_extra_fees').setStyle('display', 'none');
    
    $('user_id_placeholder').set('html', '<input class="field" type="text" id="user_id" name="user_id" value="" maxlength="10" onChange="count_user_id_characters(); profile_is_dirty();" /><br/><span style="font-size:9pt; color: #666666;">Acceptable characters are a-z, A-Z, 0-9.<br/>No spacing allowed, but underscore (_) is permitted.</span>');
    $('password_placeholder').set('html', '<input class="field" type="password" id="password" name="password" value="'+ generate_random_string_of(6) + '" disabled />');
    $('business_license').value = '';
    $('name').value = '';
    $('email').value = '';
    $('contact_person').value = '';
    $('phone_num').value = '';
    $('address').value = '';
    $('state').value = '';
    $('zip').value = '';
    list_countries_in('0', 'country_dropdown_list', 'country_dropdown', 'country_dropdown', false, 'profile_is_dirty();');
    $('working_months').value = '12';
    //$('bonus_months').value = '1';
    $('payment_terms_days').selectedIndex = 0;
}

function new_from_employer(_employer_id) {
    $('employer_id').value = '0';
    copy_from_employer = _employer_id;
    $('div_employers').setStyle('display', 'none');
    $('div_employer').setStyle('display', 'block');

    selected_tab = 'li_profile';
    $('li_profile').setStyle('border', '1px solid #CCCCCC');
    $('li_service_fees').setStyle('border', '1px solid #0000FF');
    $('li_extra_fees').setStyle('border', '1px solid #0000FF');
    $('div_profile').setStyle('display', 'block');
    $('div_service_fees').setStyle('display', 'none');
    $('div_extra_fees').setStyle('display', 'none');
    
    var params = 'id=' + _employer_id + '&action=get_employer';
    
    var uri = root + "/employees/employers_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading employer.');
                return false;
            }
            
            var ids = xml.getElementsByTagName('id');
            var licenses = xml.getElementsByTagName('license_num');
            var names = xml.getElementsByTagName('name');
            var phone_nums = xml.getElementsByTagName('phone_num');
            var email_addrs = xml.getElementsByTagName('email_addr');
            var contact_persons = xml.getElementsByTagName('contact_person');
            var addresses = xml.getElementsByTagName('address');
            var states = xml.getElementsByTagName('state');
            var zips = xml.getElementsByTagName('zip');
            var countries = xml.getElementsByTagName('country');
            var website_urls = xml.getElementsByTagName('website_url');
            var working_months = xml.getElementsByTagName('working_months');
            //var bonus_months = xml.getElementsByTagName('bonus_months');
            var payment_terms_days = xml.getElementsByTagName('payment_terms_days');
            
            var new_user_id = ids[0].childNodes[0].nodeValue + '_1';
            if (new_user_id.length > 10) {
                var temp = '';
                for(var i=0; i < 8; i++) {
                    temp = temp + new_user_id.charAt(i);
                }
                
                new_user_id = temp + '_1';
            }
            
            $('user_id_placeholder').set('html', '<input class="field" type="text" id="user_id" name="user_id" value="' + new_user_id + '" maxlength="10" /><br/><span style="font-size:9pt; color: #666666;">Acceptable characters are a-z, A-Z, 0-9.<br/>No spacing allowed, but underscore (_) is permitted.</span>');
            $('password_placeholder').set('html', '<input class="field" type="password" id="password" name="password" value="' + generate_random_string_of(6) + '" disabled />');
            $('business_license').value = '';
            $('name').value = names[0].childNodes[0].nodeValue;
            $('email').value = email_addrs[0].childNodes[0].nodeValue;
            $('contact_person').value = contact_persons[0].childNodes[0].nodeValue;
            $('phone_num').value = phone_nums[0].childNodes[0].nodeValue;
            
            var address = '';
            if (addresses[0].childNodes.length > 0) {
                address = addresses[0].childNodes[0].nodeValue;
            }
            $('address').value = address;
            
            var state = '';
            if (states[0].childNodes.length > 0) {
                state = states[0].childNodes[0].nodeValue;
            }
            $('state').value = state;
            
            $('zip').value = zips[0].childNodes[0].nodeValue;
            list_countries_in(countries[0].childNodes[0].nodeValue, 'country_dropdown_list', 'country_dropdown', 'country_dropdown', false, 'profile_is_dirty();');
            $('working_months').value = working_months[0].childNodes[0].nodeValue;
            //$('bonus_months').value = bonus_months[0].childNodes[0].nodeValue;
            
            $('payment_terms_days').selectedIndex = 0;
            for (var i=0; i < $('payment_terms_days').options.length; i++) {
                if ($('payment_terms_days').options[i].value == payment_terms_days[0].childNodes[0].nodeValue) {
                    $('payment_terms_days').selectedIndex = i;
                    break;
                }
            }
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading employer profile...');
        }
    });
    
    request.send(params);
}

function show_employer(_employer_id) {
    $('employer_id').value = _employer_id;
    copy_from_employer = '0';
    show_employer_profile();
}

function show_employer_profile() {
    $('div_employers').setStyle('display', 'none');
    $('div_employer').setStyle('display', 'block');

    selected_tab = 'li_profile';
    $('li_profile').setStyle('border', '1px solid #CCCCCC');
    $('li_service_fees').setStyle('border', '1px solid #0000FF');
    $('li_extra_fees').setStyle('border', '1px solid #0000FF');
    $('div_profile').setStyle('display', 'block');
    $('div_service_fees').setStyle('display', 'none');
    $('div_extra_fees').setStyle('display', 'none');
    
    if ($('employer_id').value == '0') {
        if (copy_from_employer != '0') {
            new_from_employer(copy_from_employer);
        } else {
            add_new_employer();
        }
        return;
    }
    
    var params = 'id=' + $('employer_id').value + '&action=get_employer';
    
    var uri = root + "/employees/employers_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading employer.');
                return false;
            }
            
            var ids = xml.getElementsByTagName('id');
            var licenses = xml.getElementsByTagName('license_num');
            var names = xml.getElementsByTagName('name');
            var phone_nums = xml.getElementsByTagName('phone_num');
            var email_addrs = xml.getElementsByTagName('email_addr');
            var contact_persons = xml.getElementsByTagName('contact_person');
            var addresses = xml.getElementsByTagName('address');
            var states = xml.getElementsByTagName('state');
            var zips = xml.getElementsByTagName('zip');
            var countries = xml.getElementsByTagName('country');
            var website_urls = xml.getElementsByTagName('website_url');
            var working_months = xml.getElementsByTagName('working_months');
            //var bonus_months = xml.getElementsByTagName('bonus_months');
            var payment_terms_days = xml.getElementsByTagName('payment_terms_days');
            var website_urls = xml.getElementsByTagName('website_url');
            
            $('user_id_placeholder').set('html', ids[0].childNodes[0].nodeValue);
            $('password_placeholder').set('html', '<input type="button" value="Reset Password" onClick="reset_password();" />');
            $('business_license').value = licenses[0].childNodes[0].nodeValue;
            $('name').value = names[0].childNodes[0].nodeValue;
            $('email').value = email_addrs[0].childNodes[0].nodeValue;
            $('contact_person').value = contact_persons[0].childNodes[0].nodeValue;
            $('phone_num').value = phone_nums[0].childNodes[0].nodeValue;
            
            var address = '';
            if (addresses[0].childNodes.length > 0) {
                address = addresses[0].childNodes[0].nodeValue;
            }
            $('address').value = address;
            
            var state = '';
            if (states[0].childNodes.length > 0) {
                state = states[0].childNodes[0].nodeValue;
            }
            $('state').value = state;
            
            $('zip').value = zips[0].childNodes[0].nodeValue;
            list_countries_in(countries[0].childNodes[0].nodeValue, 'country_dropdown_list', 'country_dropdown', 'country_dropdown', false, 'profile_is_dirty();');
            $('working_months').value = working_months[0].childNodes[0].nodeValue;
            //$('bonus_months').value = bonus_months[0].childNodes[0].nodeValue;
            
            $('payment_terms_days').selectedIndex = 0;
            for (var i=0; i < $('payment_terms_days').options.length; i++) {
                if ($('payment_terms_days').options[i].value == payment_terms_days[0].childNodes[0].nodeValue) {
                    $('payment_terms_days').selectedIndex = i;
                    break;
                }
            }
            
            $('website_url').value = '';
            if (website_urls[0].childNodes.length > 0) {
                $('website_url').value = website_urls[0].childNodes[0].nodeValue;
            }
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading employer profile...');
        }
    });
    
    request.send(params);
}

function show_service_fees() {
    if (is_profile_dirty) {
        alert('Please save the profile before proceeding.');
        return false;
    }
    
    $('div_employers').setStyle('display', 'none');
    $('div_employer').setStyle('display', 'block');
    
    selected_tab = 'li_service_fees';
    $('li_profile').setStyle('border', '1px solid #0000FF');
    $('li_service_fees').setStyle('border', '1px solid #CCCCCC');
    $('li_extra_fees').setStyle('border', '1px solid #0000FF');
    $('div_profile').setStyle('display', 'none');
    $('div_service_fees').setStyle('display', 'block');
    $('div_extra_fees').setStyle('display', 'none');
    
    var employer_id = $('employer_id').value;
    if (copy_from_employer != '0') {
        employer_id = copy_from_employer;
    }
    
    var params = 'id=' + employer_id + '&action=get_fees';
    
    var uri = root + "/employees/employers_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading service fees.');
                return false;
            }
            
            var html = '<table id="service_fees_list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There is no service fee created for this employer.</div>';
                
                $('delete_service_fees').disabled = true;
                $('delete_service_fees_1').disabled = true;
            } else {
                var ids = xml.getElementsByTagName('id');
                var salary_starts = xml.getElementsByTagName('salary_start');
                var salary_ends = xml.getElementsByTagName('salary_end');
                var guarantee_months = xml.getElementsByTagName('guarantee_months');
                var service_fees = xml.getElementsByTagName('service_fee');
                var discounts = xml.getElementsByTagName('discount');
                var reward_percentages = xml.getElementsByTagName('reward_percentage');
                
                for (var i=0; i < ids.length; i++) {
                    var service_fee_id = ids[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ service_fee_id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="checkbox"><input type="checkbox" id="'+ service_fee_id + '" /></td>' + "\n";
                    html = html + '<td class="salary_start">' + salary_starts[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    var salary_end = salary_ends[i].childNodes[0].nodeValue;
                    if (parseFloat(salary_end) == 0.00) {
                        salary_end = '&infin;';
                    }
                    html = html + '<td class="salary_end">' + salary_end + '</td>' + "\n";
                    html = html + '<td class="guaranteed_months">' + guarantee_months[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="service_fee">' + service_fees[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="discount">' + discounts[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="reward_percentage">' + reward_percentages[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    html = html + '<td class="actions"><input type="button" value="Update" onClick="show_service_fee_form(\'' + service_fee_id + '\');" /></td>' + "\n";               
                    html = html + '</tr>' + "\n";
                }
                
                $('delete_service_fees').disabled = false;
                $('delete_service_fees_1').disabled = false;
                
            }
            html = html + '</table>';
            
            $('div_service_fees_list').set('html', html);
            $('div_service_fees_list').setStyle('display', 'block');
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading fees...');
        }
    });
    
    request.send(params);
}

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

function show_extra_fees() {
    if (is_profile_dirty) {
        alert('Please save the profile before proceeding.');
        return false;
    }
    
    $('div_employers').setStyle('display', 'none');
    $('div_employer').setStyle('display', 'block');
    
    selected_tab = 'li_extra_fees';
    $('li_profile').setStyle('border', '1px solid #0000FF');
    $('li_service_fees').setStyle('border', '1px solid #0000FF');
    $('li_extra_fees').setStyle('border', '1px solid #CCCCCC');
    $('div_profile').setStyle('display', 'none');
    $('div_service_fees').setStyle('display', 'none');
    $('div_extra_fees').setStyle('display', 'block');
    
    var employer_id = $('employer_id').value;
    if (copy_from_employer != '0') {
        employer_id = copy_from_employer;
    }
    
    var params = 'id=' + employer_id + '&action=get_charges';
    
    var uri = root + "/employees/employers_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading extra charges.');
                return false;
            }
            
            var html = '<table id="extra_fees_list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There is no extra charges imposed on this employer.</div>';
                
                $('delete_extra_fees').disabled = true;
                $('delete_extra_fees_1').disabled = true;
            } else {
                var ids = xml.getElementsByTagName('id');
                var labels = xml.getElementsByTagName('label');
                var charges = xml.getElementsByTagName('charges');
                
                for (var i=0; i < ids.length; i++) {
                    var extra_fee_id = ids[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ extra_fee_id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="checkbox"><input type="checkbox" id="'+ extra_fee_id + '" /></td>' + "\n";
                    html = html + '<td class="charge_label">' + labels[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="amount">' + charges[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="actions"><input type="button" value="Update" onClick="show_extra_fee_form(\'' + extra_fee_id + '\');" /></td>' + "\n";               
                    html = html + '</tr>' + "\n";
                }
                
                $('delete_extra_fees').disabled = false;
                $('delete_extra_fees_1').disabled = false;
                
            }
            html = html + '</table>';
            
            $('div_extra_fees_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading extra charges...');
        }
    });
    
    request.send(params);
}

function close_extra_fee_form() {
    $('extra_fee_id').value = '0';
    $('div_extra_fee_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_new_extra_fee_form() {
    show_extra_fee_form(0);
}

function show_extra_fee_form(_id) {
    $('extra_fee_id').value = _id;
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_extra_fee_form').getStyle('height'));
    var div_width = parseInt($('div_extra_fee_form').getStyle('width'));
    
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
    
    $('div_extra_fee_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_extra_fee_form').setStyle('left', ((window_width - div_width) / 2));
    
    if (_id != '0') {
        var params = 'id=' + _id + '&action=get_charge';

        var uri = root + "/employees/employers_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    alert('An error occured while loading extra fee.');
                    return false;
                }

                if (txt != '0') {
                    var ids = xml.getElementsByTagName('id');
                    var labels = xml.getElementsByTagName('label');
                    var charges = xml.getElementsByTagName('charges');
                    
                    $('charge_label').value = labels[0].childNodes[0].nodeValue;
                    $('amount').value = charges[0].childNodes[0].nodeValue;
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
    $('div_extra_fee_form').setStyle('display', 'block');
}

function deactivate_employers() {
    var inputs = $('list').getElementsByTagName('input');
    var payload = '<employers>' + "\n";
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
    
    payload = payload + '</employers>';
    
    if (count <= 0) {
        set_status('Please select at least one employer.');
        return false;
    }
    
    var proceed = confirm('Are you sure to deactivate the selected employers?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=0';
    params = params + '&action=deactivate';
    params = params + '&payload=' + payload;
    
    var uri = root + "/employees/employers_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while deactivating selected employers.');
                return false;
            }
            
            set_status('');
            show_employers();
        },
        onRequest: function(instance) {
            set_status('Loading employers...');
        }
    });
    
    request.send(params);
}

function activate_employer(_employer_id) {
    var proceed = confirm('Are you sure to activate the employer?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=' + _employer_id;
    params = params + '&action=activate';
    
    var uri = root + "/employees/employers_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while activating employer.');
                return false;
            }
            
            set_status('');
            show_employers();
        },
        onRequest: function(instance) {
            set_status('Loading employers...');
        }
    });
    
    request.send(params);
}

function reset_password() {
    var proceed = confirm('Are you sure to reset the password?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=' + $('employer_id').value;
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

function save_profile() {
    if (!validate_profile_form()) {
        return false;
    }
    
    var mode = 'update';
    var employer_id = $('employer_id').value;
    if (employer_id == '0') {
        employer_id = $('user_id').value;
        mode = 'create';
    }
    
    var params = 'id=' + $('employer_id').value;
    params = params + '&action=save_profile';
    params = params + '&employee=' + id;
    params = params + '&license_num=' + $('business_license').value
    params = params + '&email_addr=' + $('email').value;
    params = params + '&name=' + $('name').value;
    params = params + '&contact_person=' + $('contact_person').value;
    params = params + '&phone_num=' + $('phone_num').value;
    params = params + '&address=' + $('address').value;
    params = params + '&state=' + $('state').value;
    params = params + '&zip=' + $('zip').value;
    params = params + '&country=' + $('country_dropdown').options[$('country_dropdown').selectedIndex].value;
    params = params + '&working_months=' + $('working_months').value;
    //params = params + '&bonus_months=' + $('bonus_months').value;
    params = params + '&payment_terms_days=' + $('payment_terms_days').options[$('payment_terms_days').selectedIndex].value;
    params = params + '&website_url=' + $('website_url').value;
    
    if (mode == 'create') {
        params = params + '&user_id=' + $('user_id').value;
        params = params + '&password=' + $('password').value;
    }
    
    var uri = root + "/employees/employers_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while saving profile. Please makesure the Business License and User ID do not already exist in the system.');
                return false;
            }
            
            if (mode == 'create') {
                if (employer_id.length > 10) {
                    employer_id = employer_id.substr(0, 10);
                }
                $('employer_id').value = employer_id;
                
                if (copy_from_employer != '0') {
                    var proceed_copy = confirm('Do you want the service fees and extra fees to be copied too?');
                    if (proceed_copy) {
                        copy_fees_and_extras();
                    }
                }
            }
            
            is_profile_dirty = false;
            show_employer_profile();
            set_status('Profile successfully saved!');
        },
        onRequest: function(instance) {
            set_status('Saving profile...');
        }
    });
    
    request.send(params);
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

function delete_fees() {
    var inputs = $('service_fees_list').getElementsByTagName('input');
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
        set_status('Please select at least one service fee.');
        return false;
    }
    
    var proceed = confirm('Are you sure to delete the selected service fees?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=0';
    params = params + '&action=delete_fees';
    params = params + '&payload=' + payload;
    
    var uri = root + "/employees/employers_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while deleting selected fees.');
                return false;
            }
            
            set_status('');
            show_service_fees();
        },
        onRequest: function(instance) {
            set_status('Loading service fees...');
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

function set_mouse_events() {
    $('li_profile').addEvent('mouseover', function() {
        $('li_profile').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_profile').addEvent('mouseout', function() {
        $('li_profile').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_service_fees').addEvent('mouseover', function() {
        $('li_service_fees').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_service_fees').addEvent('mouseout', function() {
        $('li_service_fees').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_extra_fees').addEvent('mouseover', function() {
        $('li_extra_fees').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_extra_fees').addEvent('mouseout', function() {
        $('li_extra_fees').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_employers').addEvent('mouseover', function() {
        $('li_employers').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_employers').addEvent('mouseout', function() {
        $('li_employers').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
}

function onDomReady() {
    set_root();
    set_mouse_events();
    get_unapproved_photos_count();
    
    $('li_employers').addEvent('click', show_employers);
    $('li_profile').addEvent('click', show_employer_profile);
    $('li_service_fees').addEvent('click', show_service_fees);
    $('li_extra_fees').addEvent('click', show_extra_fees);
    
    $('add_new_employer').addEvent('click', add_new_employer);
    $('add_new_employer_1').addEvent('click', add_new_employer);
    $('save_profile').addEvent('click', save_profile);
    $('save_profile_1').addEvent('click', save_profile);
    $('add_new_service_fee').addEvent('click', show_new_service_fee_form);
    $('add_new_service_fee_1').addEvent('click', show_new_service_fee_form);
    $('add_new_extra_fee').addEvent('click', show_new_extra_fee_form);
    $('add_new_extra_fee_1').addEvent('click', show_new_extra_fee_form);
    $('deactivate_all').addEvent('click', select_all_employers);
    $('delete_all_service_fees').addEvent('click', select_all_service_fees);
    $('delete_all_extra_fees').addEvent('click', select_all_extra_fees);
    
    $('salary_start').addEvent('change', function() {
        salary_range_dirtied = true;
    });
    
    $('salary_end').addEvent('change', function() {
        salary_range_dirtied = true;
    });
    
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
    
    $('sort_first_login').addEvent('click', function() {
        order_by = 'employer_sessions.first_login';
        ascending_or_descending();
        show_employers();
    });
    
    show_employers();
}

window.addEvent('domready', onDomReady);
