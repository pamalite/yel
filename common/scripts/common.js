var root = "";

function set_root() {
    root = location.protocol + "//" + location.hostname + "/yel";
}

function set_status(msg) {
    $('span_status').set('html', msg);
    
    if (msg == '') {
        $('span_status').setStyle('visibility', 'hidden');
    } else {
        $('span_status').setStyle('visibility', 'visible');
    }
}

function sha1(msg) {
    return SHA1(msg);
}

function md5(msg) {
    return MD5(msg);
}

function mm_to_month(mm) {
    switch (parseInt(mm)) {
        case 1:
            return 'January';
        case 2:
            return 'February';
        case 3:
            return 'March';
        case 4:
            return 'April';
        case 5:
            return 'May';
        case 6:
            return 'June';
        case 7:
            return 'July';
        case 8:
            return 'August';
        case 9:
            return 'September';
        case 10:
            return 'October';
        case 11:
            return 'November';
        case 12:
            return 'December';
    }
}

function left_trim(aString) {
    var output = '';
    for (var i=0; i < aString.length; i++) {
        if (aString.charAt(i) != ' ') {
            output = aString.substr(i);
            break;
        }
    }
    return output;
}

function right_trim(aString) {
    var output = '';
    for (var i=aString.length-1; i >= 0; i--) {
        if (aString.charAt(i) != ' ') {
            output = aString.substr(0, (parseInt(i)+1));
            break;
        }
    }
    return output;
}

function trim(aString) {
    return left_trim(right_trim(aString));
}

function remove_nbsp(aString) {
    var data = encodeURIComponent(aString).split('%C2%A0%C2%A0%C2%A0');
    for (var i=0; i < data.length; i++) {
        if (!isEmpty(data[i])) {
            return decodeURIComponent(data[i]);
            break;
        }
    }
}

function add_slashes(aString) {
    var output = '';
    for (var i=0; i < aString.length; i++) {
        if (aString.charAt(i) == "\'") {
            output = output + "\\";
        }
        
        output = output + aString.charAt(i);
    }
    
    return output;
}

function strip_slashes(aString) {
    
}

function pad(aString, maxlength, pad_character) {
    aString = trim(aString);
    var output = '';
    var pads_needed = maxlength - aString.length;
    
    if (pads_needed <= 0) {
        return aString;
    }
    
    for (var i=0; i < pads_needed; i++) {
        output = output + pad_character;
    }
    
    return output + aString;
}

function isEmail(aString) {
    // look for @
    if (aString.search("@") <= 0) {
        return false;
    } else {
        var offset = aString.search("@")+1;

        // make sure no duplicates of @
        if (offset < aString.length) {
            for (i = offset; i < aString.length; i++) {
                if (aString.charAt(i) == "@") {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    // make sure the last character is not a dot
    for (i=aString.length-1; i >= 0; i--) {
        if (aString.charAt(i) == ".") {
            if (i == (aString.length - 1)) {
                return false;
            } else {
                if (aString.charAt(i-1) != ".") {
                    foundDot = true;
                    break;
                } else {
                    return false;
                }
            }
        }
    }

    return true;
}

function isEmpty(strInput) {
    str= strInput.replace(/\s/g, '');
    
    if (str.length <= 0) {
        return true;
    } else {
        return false;
    }
}

function isNumeric(strInput) {
    var numericChars = ["0","1","2","3","4","5","6","7","8","9","-","."];
    var boolFound = true;
    
    for (var i=0; i < strInput.length; i++) {
        if (boolFound) {
            for (var j=0; j < numericChars.length; j++) {
                if (strInput.charAt(i) == numericChars[j]) {
                    boolFound = true;
                    break;
                } else {
                    boolFound = false;
                }
            }
        } else {
            break;
        }
    }
    
    return boolFound;
}

function list_months_in(selected, placeholder, used_id, used_name) {
    var html = '<select id="' + used_id + '" name="' + used_name + '">' + "\n";
    
    if (selected == '0') {
        html = html + '<option value="0" selected>Select a month.</option>' + "\n";
        html = html + '<option value="0" disabled>&nbsp;</option>' + "\n";
    }
    
    for (var mm=1; mm <= 12; mm++) {
        if (mm == selected) {
            html = html + '<option value="' + mm + '" selected>' + mm_to_month(mm) + '</option>' + "\n";
        } else {
            html = html + '<option value="' + mm + '">' + mm_to_month(mm) + '</option>' + "\n";
        }
    }
    
    html = html + '</select>' + "\n";
    
    $(placeholder).set('html', html);
}

function generate_random_string_of(length) {
    var characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    var output = '';
    for (var i=0; i < length; i++) {
        var index = Math.floor(Math.random() * characters.length);
        output = output + characters.charAt(index);
    }
    
    return output;
}

function get_industries() {
    var industry_id = 0;
    if (arguments.length > 0) {
        industry_id = arguments[0];
    }
    
    list_industries_in(industry_id, 'industry_drop_down', 'industry', 'industry', true);
}

function get_employers() {
    var employer_id = '';
    if (arguments.length > 0) {
        employer_id = arguments[0];
    }
    
    list_all_employers_in(employer_id, 'employer_drop_down', 'employer', 'employer', true);
}

function get_industries_for_mini() {
    var industry_id = 0;
    if (arguments.length > 0) {
        industry_id = arguments[0];
    }
    
    list_industries_in(industry_id, 'mini_industry_drop_down', 'mini_industry', 'industry', true);
}

function get_employers_for_mini() {
    var employer_id = 0;
    if (arguments.length > 0) {
        employer_id = arguments[0];
    }
    
    list_all_employers_in(employer_id, 'mini_employer_drop_down', 'mini_employer', 'employer', true);
}

function list_industries_in(selected, placeholder, used_id, used_name, for_search, on_change) {
    var add_event = '';
    if (arguments.length == 6) {
        if (!isEmpty(arguments[5])) {
            add_event = 'onChange="' + on_change + '"';
        }
    }
    
    var uri = root + "/common/php/industries.php";
    if (for_search) { 
        uri = root + "/common/php/industries_with_job_count.php";
    }
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            var ids = xml.getElementsByTagName('id');
            var industries = xml.getElementsByTagName('name');
            var mains = xml.getElementsByTagName('main');
            var job_counts = null;
            if (for_search) {
                job_counts = xml.getElementsByTagName('job_count');
            }
            var html = '<select class="field" id="' + used_id + '" name="' + used_name + '" '+ add_event + '>' + "\n";
            
            if (selected == '0') {
                if (for_search) {
                    html = html + '<option value="0" selected>Any Specialization</option>' + "\n";
                } else {
                    html = html + '<option value="0" selected>Select an Specialization</option>' + "\n";
                }
                html = html + '<option value="0" disabled>&nbsp;</option>' + "\n";
            } else {
                if (for_search) {
                    html = html + '<option value="0">Any Industry</option>' + "\n";
                    html = html + '<option value="0" disabled>&nbsp;</option>' + "\n";
                }
            }
            
            for (i = 0; i < ids.length; i++) {
                var css_class = spacing = "";
                if (mains[i].childNodes[0].nodeValue == 'Y') {
                    css_class = "class=\"main_industry\"";
                } else {
                    spacing = "&nbsp;&nbsp;&nbsp;";
                }
                
                var job_count = 0;
                var display_job_count = '';
                //for_search = false;
                if (for_search) {
                    if (job_counts[i].childNodes.length > 0) {
                        job_count = job_counts[i].childNodes[0].nodeValue;
                    }
                    
                    if (job_count != 0){
                        display_job_count = "&nbsp;&nbsp;&nbsp(" + job_count + ")";
                    }
                    
                    if (ids[i].childNodes[0].nodeValue == selected) {
                        html = html + '<option ' + css_class + ' value="' + ids[i].childNodes[0].nodeValue + '" selected>' + spacing + industries[i].childNodes[0].nodeValue + display_job_count + '</option>' + "\n";
                    } else {
                        html = html + '<option ' + css_class + ' value="' + ids[i].childNodes[0].nodeValue + '">' + spacing + industries[i].childNodes[0].nodeValue + display_job_count + '</option>' + "\n";
                    }
                } else {
                    if (ids[i].childNodes[0].nodeValue == selected) {
                        html = html + '<option ' + css_class + ' value="' + ids[i].childNodes[0].nodeValue + '" selected>' + spacing + industries[i].childNodes[0].nodeValue + '</option>' + "\n";
                    } else {
                        html = html + '<option ' + css_class + ' value="' + ids[i].childNodes[0].nodeValue + '">' + spacing + industries[i].childNodes[0].nodeValue + '</option>' + "\n";
                    }
                }
            }
            
            html = html + '</select>' + "\n";
            
            $(placeholder).set('html', html);
        },
        onFailure: function() {
             $(placeholder).set('html', 'Error loading industries.');
        },
        onRequest: function(instance) {
            $(placeholder).set('html', 'Loading industries...');
        }
    });
     
    request.send();
}

function list_all_countries_in(selected, placeholder, used_id, used_name, for_search, on_change) {
    var add_event = '';
    if (arguments.length == 6) {
        if (!isEmpty(arguments[5])) {
            add_event = 'onChange="' + on_change + '"';
        }
    }
    
    var uri = root + "/common/php/countries_all.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            var country_codes = xml.getElementsByTagName('country_code');
            var countries = xml.getElementsByTagName('name');
            
            var html = '<select id="' + used_id + '" name="' + used_name + '" ' + add_event + '>' + "\n";
            if (selected == '' || selected == null || selected == '0') {
                if (for_search) {
                    html = html + '<option value="0" selected>any country</option>' + "\n";
                } else {
                    html = html + '<option value="0" selected>Select a country</option>' + "\n";
                }
                html = html + '<option value="0" disabled>&nbsp;</option>' + "\n";
            } else {
                if (for_search) {
                    html = html + '<option value="0">any country</option>' + "\n";
                    html = html + '<option value="0" disabled>&nbsp;</option>' + "\n";
                }
            }
            
            for (i = 0; i < country_codes.length; i++) {
                if (country_codes[i].childNodes[0].nodeValue == selected) {
                    html = html + '<option value="' + country_codes[i].childNodes[0].nodeValue + '" selected>' + countries[i].childNodes[0].nodeValue + '</option>' + "\n";
                } else {
                    html = html + '<option value="' + country_codes[i].childNodes[0].nodeValue + '">' + countries[i].childNodes[0].nodeValue + '</option>' + "\n";
                }
            }
            
            html = html + '</select>' + "\n";
            
            $(placeholder).set('html', html);
        },
        onRequest: function(instance) {
            $(placeholder).set('html', 'Loading countries...');
        }
    });
     
    request.send();
}

function list_countries_in(selected, placeholder, used_id, used_name, for_search, on_change) {
    var add_event = '';
    if (arguments.length == 6) {
        if (!isEmpty(arguments[5])) {
            add_event = 'onChange="' + on_change + '"';
        }
    }
    
    var uri = root + "/common/php/countries.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            var country_codes = xml.getElementsByTagName('country_code');
            var countries = xml.getElementsByTagName('name');
            
            var html = '<select id="' + used_id + '" name="' + used_name + '" ' + add_event + '>' + "\n";
            if (selected == '' || selected == null || selected == '0') {
                if (for_search) {
                    html = html + '<option value="0" selected>any country</option>' + "\n";
                } else {
                    html = html + '<option value="0" selected>Select a country</option>' + "\n";
                }
                html = html + '<option value="0" disabled>&nbsp;</option>' + "\n";
            } else {
                if (for_search) {
                    html = html + '<option value="0">any country</option>' + "\n";
                    html = html + '<option value="0" disabled>&nbsp;</option>' + "\n";
                }
            }
            
            for (i = 0; i < country_codes.length; i++) {
                if (country_codes[i].childNodes[0].nodeValue == selected) {
                    html = html + '<option value="' + country_codes[i].childNodes[0].nodeValue + '" selected>' + countries[i].childNodes[0].nodeValue + '</option>' + "\n";
                } else {
                    html = html + '<option value="' + country_codes[i].childNodes[0].nodeValue + '">' + countries[i].childNodes[0].nodeValue + '</option>' + "\n";
                }
            }
            
            html = html + '</select>' + "\n";
            
            $(placeholder).set('html', html);
        },
        onRequest: function(instance) {
            $(placeholder).set('html', 'Loading countries...');
        }
    });
     
    request.send();
}

function list_skill_levels_in(selected, placeholder, used_id, used_name) {
    var levels = ['A', 'B', 'C'];
    var level_names = ['Beginner', 'Intermediate', 'Advanced'];
    var html = '<select class="field" id="' + used_id + '" name="' + used_name + '">' + "\n";
            
    if (selected == '' || selected == null) {
        html = html + '<option value="0" selected>Select a level</option>' + "\n";
        html = html + '<option value="0" disabled>&nbsp;</option>' + "\n";
    }
    
    for (i = 0; i < levels.length; i++) {
        if (levels[i] == selected) {
            html = html + '<option value="' + levels[i] + '" selected>' + level_names[i] + '</option>' + "\n";
        } else {
            html = html + '<option value="' + levels[i] + '">' + level_names[i] + '</option>' + "\n";
        }
    }
    
    html = html + '</select>' + "\n";
    
    $(placeholder).set('html', html);
}

function list_all_employers_in(selected, placeholder, used_id, used_name, for_search, on_change) {
    var add_event = '';
    if (arguments.length == 6) {
        if (!isEmpty(arguments[5])) {
            add_event = 'onChange="' + on_change + '"';
        }
    }
    
    var uri = root + "/common/php/employers.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            var ids = xml.getElementsByTagName('id');
            var names = xml.getElementsByTagName('name');
            
            var html = '<select id="' + used_id + '" name="' + used_name + '" ' + add_event + '>' + "\n";
            if (selected == '' || selected == null || selected == '0') {
                if (for_search) {
                    html = html + '<option value="0" selected>Any Employer</option>' + "\n";
                } else {
                    html = html + '<option value="0" selected>Select an employer</option>' + "\n";
                }
                html = html + '<option value="0" disabled>&nbsp;</option>' + "\n";
            } else {
                if (for_search) {
                    html = html + '<option value="0">Any Employer</option>' + "\n";
                    html = html + '<option value="0" disabled>&nbsp;</option>' + "\n";
                }
            }
            
            for (i = 0; i < ids.length; i++) {
                if (ids[i].childNodes[0].nodeValue == selected) {
                    html = html + '<option value="' + ids[i].childNodes[0].nodeValue + '" selected>' + names[i].childNodes[0].nodeValue + '</option>' + "\n";
                } else {
                    html = html + '<option value="' + ids[i].childNodes[0].nodeValue + '">' + names[i].childNodes[0].nodeValue + '</option>' + "\n";
                }
            }
            
            html = html + '</select>' + "\n";
            
            $(placeholder).set('html', html);
        },
        onRequest: function(instance) {
            $(placeholder).set('html', 'Loading employers...');
        }
    });
     
    request.send();
}

function get_potential_rewards() {
    var uri = root + "/common/php/potential_rewards.php";
    var request = new Request({
        url: uri,
        onSuccess: function(txt, xml) {
            var rewards = xml.getElementsByTagName('sumReward');
            var countries = xml.getElementsByTagName('country_code');
            var currencies = xml.getElementsByTagName('currency');
            var html = '<div class="rewards_title">Total Potential Rewards to Members</div>';
            
            if (rewards.length <= 0) {
                html = html + 'No jobs posted at the moment.' + "\n";
            } else {
                for (i=0; i < rewards.length; i++) {
                    html = html + '<img class="flag" src="' + root + "/common/images/flags/" + countries[i].childNodes[0].nodeValue + '.gif" />&nbsp;' + currencies[i].childNodes[0].nodeValue + '&nbsp;' + rewards[i].childNodes[0].nodeValue + "&nbsp;&nbsp;\n";
                }
            }
            
            $('total_potential_rewards').set('html', html);
        },
        onFailure: function() {
            $('total_potential_rewards').set('html', 'Error loading potential rewards.');
        },
        onRequest: function(instance) {
            $('total_potential_rewards').set('html', 'Loading potential rewards...');
        }
    });
    
    request.send();
}

function get_job_count() {
    var uri = root + "/common/php/job_count.php";
    var request = new Request({
        url: uri,
        onSuccess: function(txt, xml) {
            var job_count = xml.getElementsByTagName('jobcount');
            
            var count = job_count[0].childNodes[0].nodeValue;
            if (parseInt(count) <= 0) {
                count = 'no';
            }
            var html = 'There are now <span class="job_count">' + count + '</span> jobs available.';
            $('job_count').set('html', html);
        },
        onFailure: function() {
            $('job_count').set('html', 'Lost count!');
        },
        onRequest: function(instance) {
            $('job_count').set('html', 'Counting jobs available...');
        }
    });
     
    request.send();
}

function get_referrals_count() {
    var uri = root + "/common/php/referrals_count.php";
    var request = new Request({
        url: uri,
        onSuccess: function(txt, xml) {
            var num_responses = xml.getElementsByTagName('num_responses');
            var num_views = xml.getElementsByTagName('num_views');
            var num_rewards = xml.getElementsByTagName('num_rewards');
            
            var counts = '';
            
            // check new referrals
            if (parseInt(num_responses[0].childNodes[0].nodeValue) > 0) {
                counts = 'R : ' + num_responses[0].childNodes[0].nodeValue;
            }
            
            // check new read resumes
            if (parseInt(num_views[0].childNodes[0].nodeValue) > 0) {
                if (!isEmpty(counts)) {
                    counts = counts + ' | V : ' + num_views[0].childNodes[0].nodeValue;
                } else {
                    counts = 'V : ' + num_views[0].childNodes[0].nodeValue;
                }
            }
            
            // check new rewards
            if (parseInt(num_rewards[0].childNodes[0].nodeValue) > 0) {
                if (!isEmpty(counts)) {
                    counts = counts + ' | $ : ' + num_rewards[0].childNodes[0].nodeValue;
                } else {
                    counts = '$ : ' + num_rewards[0].childNodes[0].nodeValue;
                }
            }
            
            if ($('referrals_count') != null) {
                if (isEmpty(counts)) {
                    $('referrals_count').setStyle('display', 'none');
                } else {
                    $('referrals_count').setStyle('display', 'inline');
                    $('referrals_count').set('html', ' (' + counts + ')');
                }
            }
        }
    });
     
    request.send();
}

function get_requests_count() {
    var uri = root + "/common/php/requests_count.php";
    var request = new Request({
        url: uri,
        onSuccess: function(txt, xml) {
            if (txt == '0') {
                $('requests_count').setStyle('display', 'none');
            } else {
                $('requests_count').setStyle('display', 'inline');
                $('requests_count').set('html', ' (' + txt + ')');
            }
        }
    });
     
    request.send();
}

function get_jobs_employed_count() {
    var uri = root + "/common/php/jobs_employed_count.php";
    var request = new Request({
        url: uri,
        onSuccess: function(txt, xml) {
            if (txt == '0') {
                $('jobs_employed_count').setStyle('display', 'none');
            } else {
                $('jobs_employed_count').setStyle('display', 'inline');
                $('jobs_employed_count').set('html', ' (' + txt + ')');
            }
        }
    });
     
    request.send();
}

function get_employer_referrals_count() {
    var uri = root + "/common/php/employer_referrals_count.php";
    var request = new Request({
        url: uri,
        onSuccess: function(txt, xml) {
            if (txt == '0') {
                $('emp_referrals_count').setStyle('display', 'none');
            } else {
                $('emp_referrals_count').setStyle('display', 'inline');
                $('emp_referrals_count').set('html', ' (' + txt + ')');
            }
        }
    });
     
    request.send();
}

function get_unapproved_photos_count() {
    var uri = root + "/common/php/unapproved_photos_count.php";
    var request = new Request({
        url: uri,
        onSuccess: function(txt, xml) {
            if (txt == '0') {
                $('unapproved_photos_count').setStyle('display', 'none');
            } else {
                $('unapproved_photos_count').setStyle('display', 'inline');
                $('unapproved_photos_count').set('html', '  (' + txt + ')');
            }
        }
    });
    
    request.send();
}

function get_employee_rewards_count() {
    var uri = root + "/common/php/employee_rewards_count.php";
    var request = new Request({
        url: uri,
        onSuccess: function(txt, xml) {
            if ($('rewards_count') != null) {
                if (txt == '0') {
                    $('rewards_count').setStyle('display', 'none');
                } else {
                    $('rewards_count').setStyle('display', 'inline');
                    $('rewards_count').set('html', ' (' + txt + ')');
                }
            }
        }
    });
     
    request.send();
}

function get_employee_tokens_count() {
    var uri = root + "/common/php/employee_tokens_count.php";
    var request = new Request({
        url: uri,
        onSuccess: function(txt, xml) {
            if ($('tokens_count') != null) {
                if (txt == '0') {
                    $('tokens_count').setStyle('display', 'none');
                } else {
                    $('tokens_count').setStyle('display', 'inline');
                    $('tokens_count').set('html', ' (' + txt + ')');
                }
            }
        }
    });
     
    request.send();
}

function show_industries_in(placeholder) {
    var uri = root + "/common/php/industries_with_job_count.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            var ids = xml.getElementsByTagName('id');
            var industries = xml.getElementsByTagName('name');
            var mains = xml.getElementsByTagName('main');
            var job_counts = xml.getElementsByTagName('job_count');
            
            var html = '<table class="industries"><tr>' + "\n";
            var counter = 0;
            for (var i = 0; i < ids.length; i++) {
                var css_styles = '';
                if (mains[i].childNodes[0].nodeValue == 'Y') {
                    css_styles = 'style="font-weight: bold;"';
                    
                    if (i > 0) {
                        html = html + '<br/>';
                    }
                    
                    if (counter == 0 || counter == 4) {
                        if (counter == 0) {
                            html = html + '<td>' + "\n";
                        } else {
                            html = html + '</td><td>' + "\n";
                            counter = 0;
                        }
                    }  
                    
                    counter++;
                } 
                
                var job_count = '';
                if (job_counts[i].childNodes.length > 0) {
                    if (parseInt(job_counts[i].childNodes[0].nodeValue) > 0) {
                        job_count = '&nbsp;(' + job_counts[i].childNodes[0].nodeValue + ')';
                    } else {
                        job_count = '';
                    }
                }
                
                html = html + '<a class="industry_item" href="' + root + '/search.php?industry=' + ids[i].childNodes[0].nodeValue + '&keywords=" ' + css_styles + '>' + industries[i].childNodes[0].nodeValue + job_count + '</a><br/>' + "\n";
            }
            
            html = html + '</td></tr></table>' + "\n";
            
            $(placeholder).set('html', html);
        },
        onFailure: function() {
            $(placeholder).set('html', 'Error loading industries.');
        },
        onRequest: function(instance) {
            $(placeholder).set('html', 'Loading industries...');
        }
    });
     
    request.send();
}

function verify_mini() {
    if ($('mini_industry').options[$('mini_industry').selectedIndex].value == 0 && 
        $('mini_employer').options[$('mini_employer').selectedIndex].value == 0 && 
        ($('mini_keywords').value == 'Job title or keywords' || $('mini_keywords').value == '')) {
        alert('Please select an industry/sub-industry or enter the job title/keywords in order to do a search. You may choose to do all if you wish to do a more specific search');
        return false;
    }
    
    if ($('mini_keywords').value == 'Job title or keywords') {
        $('mini_keywords').value = '';
    }
    
    return true;
}

function prs_verify_mini() {
    // if ($('mini_keywords').value == '') {
    //     alert('Please enter some keywords in order to do a search.');
    //     return false;
    // }
    
    return true;
}


function set_mini_keywords() {
    if (arguments.length > 0) {
        return;
    }
    
    $('mini_keywords').addEvent('focus', function() {
       if ($('mini_keywords').value == 'Job title or keywords') { 
           $('mini_keywords').value = '';
       }
    });

    $('mini_keywords').addEvent('blur', function() {
       if ($('mini_keywords').value == '') { 
           $('mini_keywords').value = 'Job title or keywords';
       }
    });
}

function update_word_count_of(_counter_id, _field_id) {
    var word_count = 0;
    
    if (!isEmpty($(_field_id).value)) {
        word_count = $(_field_id).value.split(' ').length;
    }
    
    $(_counter_id).set('html', word_count);
    
    if (word_count > 200) {
        $(_counter_id).setStyle('font-weight', 'bold');
        $(_counter_id).setStyle('color', '#ff0000');
    } else {
        $(_counter_id).setStyle('font-weight', 'normal');
        $(_counter_id).setStyle('color', '#000000');
    }
}

function list_available_industries(_industry) {
    var params = 'id=0&action=get_available_industries';
    
    var uri = root + "/prs/search_resume_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            var ids = xml.getElementsByTagName('id');
            var industries = xml.getElementsByTagName('industry_name');
            
            var html = '<select id="mini_industry" name="industry" onChange="refresh_search();">' + "\n";
            
            if (_industry == '0' || isEmpty(_industry)) {
                html = html + '<option value="0" selected>all specializations</option>' + "\n";
            } else {
                html = html + '<option value="0">all specializations</option>' + "\n";
            }
            
            html = html + '<option value="-1" disabled>&nbsp;</option>' + "\n";
            for (var i=0; i < ids.length; i++) {
                var id = ids[i].childNodes[0].nodeValue;
                var industry = industries[i].childNodes[0].nodeValue;
                
                if (id == _industry) {
                    html = html + '<option value="'+ id + '" selected>' + industry + '</option>' + "\n";
                } else {
                    html = html + '<option value="'+ id + '">' + industry + '</option>' + "\n";
                }
            }
            
            html = html + '</select>' + "\n";
            
            $('mini_industry_drop_down').set('html', html);
        },
        onRequest: function(instance) {
            $('mini_industry_drop_down').set('html', 'Loading specializations...');
        }
    });
    
    request.send(params);
}
