var selected_tab = 'li_referred';
var order_by = 'num_referrals';
var order = 'desc';
var filter_by = '';
var referred_order_by = 'referrals.referred_on';
var referred_order = 'desc';
var suggested_order_by = 'score';
var suggested_order = 'desc';
var shortlisted_order_by = 'referrals.shortlisted_on';
var shortlisted_order = 'desc';

var used_suggested = 'N';
var employ_referral_id = 0;

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function get_salary_from_job(job_id) {
    $('pay').set('html', '0.00');
    
    var params = 'id=' + job_id + '&action=get_salary';
    
    var uri = root + "/employers/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while trying to get salary.');
                return false;
            }
            
            var salaries = xml.getElementsByTagName('salary');
            set_status('');
            $('pay').set('html', salaries[0].childNodes[0].nodeValue);
        },
        onRequest: function(instance) {
            set_status('Loading salary...');
        }
    });
    
    request.send(params);
}

function get_salary_currency_from_job(job_id) {
    var params = 'id=' + job_id + '&action=get_salary_currency';
    
    var uri = root + "/employers/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while trying to get salary currency.');
                return false;
            }
            
            set_status('');
            return txt;
        },
        onRequest: function(instance) {
            set_status('Loading currency...');
        }
    });
    
    request.send(params);
}

function referred_ascending_or_descending() {
    if (referred_order == 'desc') {
        referred_order = 'asc';
    } else {
        referred_order = 'desc';
    }
}

function suggested_ascending_or_descending() {
    if (suggested_order == 'desc') {
        suggested_order = 'asc';
    } else {
        suggested_order = 'desc';
    }
}

function shortlisted_ascending_or_descending() {
    if (shortlisted_order == 'desc') {
        shortlisted_order = 'asc';
    } else {
        shortlisted_order = 'desc';
    }
}

function show_resume_page(resume_id) {
    var popup = window.open('resume.php?id=' + resume_id, '', 'scrollbars');
    if (!popup) {
        alert('Popup blocker was detected. Please allow pop-up windows for YellowElevator.com and try again.');
    }
}

function verify_resume_viewing(resume_id, referral_id) {
    var params = 'id=' + referral_id + '&action=set_verify_resume_viewing';
    
    var uri = root + "/employers/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while trying to confirm that you agreed on the resume viewing terms.\n\nPlease try again.');
                return false;
            }
            
            show_resume_page(resume_id);
            $('new_' + referral_id).setStyle('visibility', 'hidden');
            
            if ($('shortlist_new_' + referral_id) != null) {
                $('shortlist_new_' + referral_id).setStyle('visibility', 'hidden');
            }
            get_employer_referrals_count();
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading...');
        }
    });
    
    request.send(params);
}

function show_resume(resume_id, referral_id) {
    var is_new = $('new_' + referral_id).getStyle('visibility');
    
    if (is_new != 'hidden') {
        var agree = confirm('Please confirm that you wish to view the whole resume.\n\nClick "OK" to confirm or "Cancel" to decline.');
        
        if (agree) {
            verify_resume_viewing(resume_id, referral_id);
        }
    } else {
        show_resume_page(resume_id);
    }
}

function remove_referred_candidates() {
    var used_suggested = 'N';
    var inputs = '';
    if ($('div_referred').getStyle('display') == 'block') {
        inputs = $('referred_candidates_list').getElementsByTagName('input');
    } else if ($('div_suggested').getStyle('display') == 'block') {
        inputs = $('suggested_candidates_list').getElementsByTagName('input');
        used_suggested = 'Y';
    } else {
        inputs = $('shortlisted_candidates_list').getElementsByTagName('input');
    }
    
    var payload = '<candidates>' + "\n";
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
    
    payload = payload + '</candidates>';
    
    if (count <= 0) {
        set_status('Please select at least one candidate.');
        return false;
    }
    
    var proceed = confirm('Are you sure to remove the selected candidates?');
    if (!proceed) {
        return false;
    }
    
    var params = 'id=0';
    params = params + '&action=remove_candidates';
    params = params + '&used_suggested=' + used_suggested;
    params = params + '&payload=' + payload;

    var uri = root + "/employers/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while removing candidates.');
                return false;
            }
            
            for (i=0; i < inputs.length; i++) {
                var attributes = inputs[i].attributes;
                if (attributes.getNamedItem('type').value == 'checkbox') {
                    if (inputs[i].checked) {
                        $(inputs[i].id).setStyle('display', 'none');
                        $('tr_referred_testimony_' + inputs[i].id).setStyle('display', 'none');
                    }
                }
            }
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading candidates...');
        }
    });
    
    request.send(params);
}

function employ() {    
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

function show_employ_form_with(from_suggested, referral_id, _candidate_name) {
    employ_referral_id = referral_id;
    
    if (from_suggested) {
        used_suggested = 'Y';
    }
    
    /*var currency = get_salary_currency_from_job($('job_id').value);
    if (!currency) {
        currency = 'MYR';
    }*/
    
    //$('currency').set('html', currency.toUpperCase());
    $('employ_job_title').set('html', $('title').get('html'));
    $('candidate_name').set('html', _candidate_name);
    
    var today = new Date();
    $('year_label').set('html', today.getFullYear());
    list_months_in((parseInt(today.getMonth())+1), 'month_list', 'month_list_dropdown', 'month_list_dropdown');
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_employ_form').getStyle('height'));
    var div_width = parseInt($('div_employ_form').getStyle('width'));
    
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
    
    $('div_employ_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_employ_form').setStyle('left', ((window_width - div_width) / 2));
    
    $('div_blanket').setStyle('display', 'block');
    $('div_employ_form').setStyle('display', 'block');
    
    $('month_list_dropdown').addEvent('change', function() {
        if ($('month_list_dropdown').options[$('month_list_dropdown').selectedIndex].value < (parseInt(today.getMonth())+1)) {
            $('year_label').set('html', (parseInt(today.getFullYear())+1));
        } else {
            $('year_label').set('html', today.getFullYear());
        }
    });
}

function close_employ_form() {
    used_suggested = 'N';
    employ_referral_id = 0;
    $('div_employ_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function shortlist(referral_id, from_suggested) {
    var used_suggested = (from_suggested) ? 'Y' : 'N';
    var params = 'id=' + referral_id + '&used_suggested=' + used_suggested;
    params = params + '&action=shortlist_referral';
    
    var uri = root + "/employers/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while shortlisting candidate.');
                return false;
            }
            
            if ($('div_referred').getStyle('display') == 'block') {
                show_referred_candidates();
            } else if ($('div_suggested').getStyle('display') == 'block') {
                show_suggested_candidates();
            } else {
                show_shortlisted_candidates();
            }
            
            update_candidate_counts_with($('job_id').value);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Shortlisting candidate...');
        }
    });
    
    request.send(params);
}

function unshortlist(referral_id) {
    var params = 'id=' + referral_id;
    params = params + '&action=unshortlist_referral';
    
    var uri = root + "/employers/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while un-shortlisting candidate.');
                return false;
            }
            
            if ($('div_referred').getStyle('display') == 'block') {
                show_referred_candidates();
            } else if ($('div_suggested').getStyle('display') == 'block') {
                show_suggested_candidates();
            } else {
                show_shortlisted_candidates();
            }
            
            update_candidate_counts_with($('job_id').value);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Un-shortlisting candidate...');
        }
    });
    
    request.send(params);
}

function reject(referral_id, from_suggested) {
    var used_suggested = (from_suggested) ? 'Y' : 'N';
    var params = 'id=' + referral_id + '&used_suggested=' + used_suggested;
    params = params + '&action=reject_candidate';
    
    var uri = root + "/employers/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while rejecting candidate.');
                return false;
            }
            
            if ($('div_referred').getStyle('display') == 'block') {
                show_referred_candidates();
            } else if ($('div_suggested').getStyle('display') == 'block') {
                show_suggested_candidates();
            } else {
                show_shortlisted_candidates();
            }
            
            update_candidate_counts_with($('job_id').value);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Rejecting candidate...');
        }
    });
    
    request.send(params);
}

function unreject(referral_id) {
    var params = 'id=' + referral_id;
    params = params + '&action=unreject_candidate';
    
    var uri = root + "/employers/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while un-rejecting candidate.');
                return false;
            }
            
            if ($('div_referred').getStyle('display') == 'block') {
                show_referred_candidates();
            } else if ($('div_suggested').getStyle('display') == 'block') {
                show_suggested_candidates();
            } else {
                show_shortlisted_candidates();
            }
            
            update_candidate_counts_with($('job_id').value);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Un-rejecting candidate...');
        }
    });
    
    request.send(params);
}

function show_referred_jobs() {
    $('job_id').value = '';
    div_descs = new Array();
    
    $('div_referred_jobs').setStyle('display', 'block');
    $('div_referrals').setStyle('display', 'none');
    
    var params = 'id=' + id;
    params = params + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employers/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading referred jobs.');
                return false;
            }
            
            var has_referrals = false;
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no referrals at the moment.</div>';
            } else {
                var ids = xml.getElementsByTagName('id');
                var industries = xml.getElementsByTagName('industry');
                var titles = xml.getElementsByTagName('title');
                var created_ons = xml.getElementsByTagName('created_on');
                var expire_ons = xml.getElementsByTagName('expire_on');
                var referrals = xml.getElementsByTagName('num_referrals');
                var new_referrals = xml.getElementsByTagName('new_referrals_count');
                
                for (var i=0; i < ids.length; i++) {
                    var job_id = ids[i];

                    html = html + '<tr id="'+ job_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";

                    html = html + '<td class="industry">' + industries[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title"><a class="no_link" onClick="toggle_description(\'' + job_id.childNodes[0].nodeValue + '\')">' + titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="date">' + created_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + expire_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    var new_referrals_count = '';
                    if (new_referrals[i].childNodes[0].nodeValue != '0') {
                        new_referrals_count = '[' + new_referrals[i].childNodes[0].nodeValue + ' new]';
                    }
                    
                    html = html + '<td class="referrals"><a class="no_link" onClick="show_suggested_candidates_with(\'' + job_id.childNodes[0].nodeValue + '\', \'' + titles[i].childNodes[0].nodeValue + '\', \'' + industries[i].childNodes[0].nodeValue + '\');" >' + referrals[i].childNodes[0].nodeValue + '&nbsp;<span style="vertical-align: top; font-size: 7pt;">'+ new_referrals_count + '</span></a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                    html = html + '<tr onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td colspan="5"><div class="description" id="desc_' + job_id.childNodes[0].nodeValue + '"></div></td>' + "\n";
                    html = html + '</tr>';
                }
                html = html + '</table>';
                
                has_referrals = true;
            }
            
            $('div_list').set('html', html);
            
            if (has_referrals) {
                var ids = xml.getElementsByTagName('id');
                var descriptions = xml.getElementsByTagName('description');

                for (var i=0; i < ids.length; i++) {
                    var job_id = ids[i].childNodes[0].nodeValue;

                    $('desc_' + job_id).set('html', descriptions[i].childNodes[0].nodeValue);
                }
            }
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading currently referred jobs...');
        }
    });
    
    request.send(params);
}

function show_referred_candidates() {
    show_referred_candidates_with('', '', '');
}

function show_referred_candidates_with(job_id, _title, _industry) {
    if ($('job_id').value == '') {
        $('job_id').value = job_id;
        $('title').set('html', _title);
        $('industry_label').set('html', _industry);
    }
    
    get_salary_from_job($('job_id').value);
    update_candidate_counts_with($('job_id').value);
    
    $('div_referred_jobs').setStyle('display', 'none');
    $('div_referrals').setStyle('display', 'block');
    $('div_referred').setStyle('display', 'block');
    $('div_suggested').setStyle('display', 'none');
    $('div_shortlisted').setStyle('display', 'none');
    $('li_referred').setStyle('border', '1px solid #CCCCCC');
    $('li_suggested').setStyle('border', '1px solid #0000FF');
    $('li_shortlisted').setStyle('border', '1px solid #0000FF');
    
    var params = 'id=' + $('job_id').value + '&action=get_referred_candidates';
    params = params + '&order_by=' + referred_order_by + ' ' + referred_order;
    
    if (!isEmpty(filter_by)) {
        params = params + '&filter_by=' + filter_by;
    }
    
    var uri = root + "/employers/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            //set_status(txt);
            //return;
            if (txt == 'ko') {
                set_status('An error occured while loading referred candidates.');
                return false;
            }
            
            if (txt == '0') {
                //show_referred_jobs();
                var html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">Sorry, there is no candidate found with the selected filter. It may because all candidates had been Recommended and scored.</div>';
                $('div_referred_candidates_list').set('html', html);
                set_status('');
                return false;
            }
            
            var html = '<table id="referred_candidates_list" class="list">';
            var ids = xml.getElementsByTagName('id');
            var resumes = xml.getElementsByTagName('resume');
            var members = xml.getElementsByTagName('referrer');
            var referees = xml.getElementsByTagName('candidate');
            var referred_ons = xml.getElementsByTagName('formatted_referred_on');
            var shortlisted_ons = xml.getElementsByTagName('shortlisted_on');
            var acknowledged_ons = xml.getElementsByTagName('formatted_acknowledged_on');
            var testimonies = xml.getElementsByTagName('testimony');
            var used_suggesteds = xml.getElementsByTagName('used_suggested');
            var agreed_terms_ons = xml.getElementsByTagName('employer_agreed_terms_on');
            var employed_ons = xml.getElementsByTagName('formatted_employed_on');
            var employer_rejected_ons = xml.getElementsByTagName('formatted_employer_rejected_on');
            var member_email_addrs = xml.getElementsByTagName('referrer_email_addr');
            var candidate_email_addrs = xml.getElementsByTagName('candidate_email_addr');
            var member_phone_nums = xml.getElementsByTagName('referrer_phone_num');
            var candidate_phone_nums = xml.getElementsByTagName('candidate_phone_num');
            
            for (var i=0; i < ids.length; i++) {
                var referral_id = ids[i];
                
                html = html + '<tr id="'+ referral_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                html = html + '<td class="checkbox"><input type="checkbox" id="'+ referral_id.childNodes[0].nodeValue + '" name="id" /></td>' + "\n";
                //html = html + '<td class="id">' + referral_id.childNodes[0].nodeValue + '</td>' + "\n";
                
                var new_referral = '<img id="new_' + referral_id.childNodes[0].nodeValue + '"  src="' + root + '/common/images/icons/new.png"';
                if (agreed_terms_ons[i].childNodes[0].nodeValue == '-1') {
                    new_referral = new_referral + ' style="visibility: visible; vertical-align: middle;"';
                } else {
                    new_referral = new_referral + ' style="visibility: hidden; vertical-align: middle;"';
                }
                new_referral = new_referral + ' />';
                html = html + '<td class="indicator">' + new_referral + '</td>' + "\n";
                
                var view_testimony_link = '<a class="no_link" onClick="toggle_testimony(\'referred_\', \'' + referral_id.childNodes[0].nodeValue + '\');">Testimony</a>';
                
                if (testimonies[i].childNodes.length > 0) {
                    view_testimony_link = view_testimony_link + '&nbsp;<a href="' + root + '/employers/testimony_pdf.php?id=' + referral_id.childNodes[0].nodeValue + '"><img style="border: none;" src="' + root + '/common/images/icons/pdf.gif" /></a>';
                }
                
                var view_resume_link = '<span style="text-decoration: line-through;">Resume</span>';
                if (resumes[i].childNodes.length > 0) {
                    view_resume_link = '<a class="no_link" onClick="show_resume(\'' + resumes[i].childNodes[0].nodeValue + '\', \'' + referral_id.childNodes[0].nodeValue + '\');">Resume</a>'
                }
                
                //html = html + '<td class="view"><a class="no_link" onClick="toggle_testimony(\'referred_\', \'' + referral_id.childNodes[0].nodeValue + '\');">Testimony</a>&nbsp;|&nbsp;' + view_resume_link + '</td>' + "\n";
                
                html = html + '<td class="member">' + members[i].childNodes[0].nodeValue + '&nbsp;(' + view_testimony_link + ')<hr style="border: none; width: 75%;" /><span style="font-size: 8pt;"><span style="font-weight: bold;">Phone:</span>&nbsp;' + member_phone_nums[i].childNodes[0].nodeValue + '<br/><a href="mailto:' + member_email_addrs[i].childNodes[0].nodeValue + '">Send e-mail</a></span></td>' + "\n";
                
                html = html + '<td class="referee">' + referees[i].childNodes[0].nodeValue + '&nbsp;(' + view_resume_link + ')<hr style="border: none; width: 75%;" /><span style="font-size: 8pt;"><span style="font-weight: bold;">Phone:</span>&nbsp;' + candidate_phone_nums[i].childNodes[0].nodeValue + '<br/><a href="mailto:' + candidate_email_addrs[i].childNodes[0].nodeValue + '">Send e-mail</a></span></td>' + "\n";
                
                html = html + '<td class="date">' + referred_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                
                var acknowledged_on = '';
                if (acknowledged_ons[i].childNodes.length > 0) {
                    acknowledged_on = acknowledged_ons[i].childNodes[0].nodeValue;
                }
                
                html = html + '<td class="date">' + acknowledged_on + '</td>' + "\n";
                
                var already_used_suggested = 'false';
                if (used_suggesteds[i].childNodes[0].nodeValue == 'Y') {
                    already_used_suggested = 'true';
                }
                
                var shortlist = '<a class="no_link" onClick="shortlist(\'' + referral_id.childNodes[0].nodeValue + '\', ' + already_used_suggested + ');">Shortlist</a>';
                if (shortlisted_ons[i].childNodes.length > 0) {
                    shortlist = '<a class="no_link" onClick="unshortlist(\'' + referral_id.childNodes[0].nodeValue + '\');">Un-shortlist</a>';
                }
                
                if (employed_ons[i].childNodes.length <= 0) {
                    if (employer_rejected_ons[i].childNodes.length <= 0) {
                        html = html + '<td class="employ"><a class="no_link" onClick="show_employ_form_with(' + already_used_suggested + ', \'' + referral_id.childNodes[0].nodeValue+ '\', \'' + add_slashes(referees[i].childNodes[0].nodeValue) + '\');">Comfirm Employed</a><br/>' + shortlist + '&nbsp;|&nbsp;<a class="no_link" onClick="reject(\'' + referral_id.childNodes[0].nodeValue + '\', ' + already_used_suggested + ');">Reject</a>&nbsp;|&nbsp;<a class="no_link" onClick="show_remarks_form(\'' + referral_id.childNodes[0].nodeValue + '\');">Remarks</a></td>' + "\n";
                    } else {
                        html = html + '<td class="employ"><span style="font-size: 9pt; vertical-align: middle; color: #666666;">Rejected on ' + employer_rejected_ons[i].childNodes[0].nodeValue + '</span>' + '&nbsp;|&nbsp;<a class="no_link" onClick="unreject(\'' + referral_id.childNodes[0].nodeValue + '\');">Un-reject</a>&nbsp;|&nbsp;<a class="no_link" onClick="show_employer_remarks_form(\'' + referral_id.childNodes[0].nodeValue + '\');">Remarks</a></td>' + "\n";
                    }
                } else {
                    html = html + '<td class="employ"><span style="font-size: 9pt; vertical-align: middle; color: #666666;">Employed on ' + employed_ons[i].childNodes[0].nodeValue + '</span></td>' + "\n";
                }
                
                
                html = html + '</tr>' + "\n";
                html = html + '<tr id="tr_referred_testimony_'+ referral_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                
                var testimony = 'No testimony found.';
                if (testimonies[i].childNodes.length > 0) {
                    testimony = testimonies[i].childNodes[0].nodeValue;
                    testimony = testimony.replace(/&amp;/g, '&');
                    testimony = testimony.replace(/&lt;/g, '<');
                    testimony = testimony.replace(/&gt;/g, '>');
                }
                html = html + '<td colspan="9"><div class="description" id="referred_testimony_' + referral_id.childNodes[0].nodeValue + '">' + testimony + '</div></td>' + "\n";
                html = html + '</tr>';
            }
            html = html + '</table>';
            
            $('div_referred_candidates_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading currently referred candidates...');
        }
    });
    
    request.send(params);
}

function show_suggested_candidates() {
    show_suggested_candidates_with('', '', '');
}

function show_suggested_candidates_with(job_id, _title, _industry) {
    if ($('job_id').value == '') {
        $('job_id').value = job_id;
        $('title').set('html', _title);
        $('industry_label').set('html', _industry);
    }
    
    get_salary_from_job($('job_id').value);
    update_candidate_counts_with($('job_id').value);
    
    $('div_referred_jobs').setStyle('display', 'none');
    $('div_referrals').setStyle('display', 'block');
    $('div_referred').setStyle('display', 'none');
    $('div_suggested').setStyle('display', 'block');
    $('div_shortlisted').setStyle('display', 'none');
    $('li_suggested').setStyle('border', '1px solid #CCCCCC');
    $('li_referred').setStyle('border', '1px solid #0000FF');
    $('li_shortlisted').setStyle('border', '1px solid #0000FF');
    
    var params = 'id=' + $('job_id').value + '&action=get_suggested_candidates';
    params = params + '&order_by=' + suggested_order_by + ' ' + suggested_order;
    
    var uri = root + "/employers/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading shortlisted candidates.');
                return false;
            }
            
            if (txt == '0') {
                var html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">None of the candidates\' resumes match the job ad. Please refer to \'All\' to view the resumes.</div>';
                $('div_suggested_candidates_list').set('html', html);
                set_status('');
                return false;
            }
            
            var ids = xml.getElementsByTagName('id');
            var scores = xml.getElementsByTagName('score_percentage');
            var resumes = xml.getElementsByTagName('resume');
            var members = xml.getElementsByTagName('referrer');
            var referees = xml.getElementsByTagName('candidate');
            var referred_ons = xml.getElementsByTagName('formatted_referred_on');
            var shortlisted_ons = xml.getElementsByTagName('shortlisted_on');
            var acknowledged_ons = xml.getElementsByTagName('formatted_acknowledged_on');
            var testimonies = xml.getElementsByTagName('testimony');
            var used_suggesteds = xml.getElementsByTagName('used_suggested');
            var agreed_terms_ons = xml.getElementsByTagName('employer_agreed_terms_on');
            //var employed_ons = xml.getElementsByTagName('formatted_employed_on');
            var employer_rejected_ons = xml.getElementsByTagName('formatted_employer_rejected_on');
            var member_email_addrs = xml.getElementsByTagName('referrer_email_addr');
            var candidate_email_addrs = xml.getElementsByTagName('candidate_email_addr');
            var member_phone_nums = xml.getElementsByTagName('referrer_phone_num');
            var candidate_phone_nums = xml.getElementsByTagName('candidate_phone_num');
            
            var html = '<table id="suggested_candidates_list" class="list">';
            for (var i=0; i < ids.length; i++) {
                var referral_id = ids[i];
                
                html = html + '<tr id="'+ referral_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                html = html + '<td class="checkbox"><input type="checkbox" id="'+ referral_id.childNodes[0].nodeValue + '" name="id" /></td>' + "\n";
                //html = html + '<td class="id">' + referral_id.childNodes[0].nodeValue + '</td>' + "\n";
                
                var new_referral = '<img id="new_' + referral_id.childNodes[0].nodeValue + '"  src="' + root + '/common/images/icons/new.png"';
                if (agreed_terms_ons[i].childNodes[0].nodeValue == '-1') {
                    new_referral = new_referral + ' style="visibility: visible; vertical-align: middle;"';
                } else {
                    new_referral = new_referral + ' style="visibility: hidden; vertical-align: middle;"';
                }
                new_referral = new_referral + ' />';
                html = html + '<td class="indicator">' + new_referral + '</td>' + "\n";
                
                html = html + '<td class="score"><img src="' + root + '/common/images/match_bar.jpg" style="height: 4px; width: ' + Math.floor(scores[i].childNodes[0].nodeValue) + '%; vertical-align: middle;" /></td>' + "\n";
                
                var view_testimony_link = '<a class="no_link" onClick="toggle_testimony(\'suggested_\', \'' + referral_id.childNodes[0].nodeValue + '\');">Testimony</a>';
                
                if (testimonies[i].childNodes.length > 0) {
                    view_testimony_link = view_testimony_link + '&nbsp;<a href="' + root + '/employers/testimony_pdf.php?id=' + referral_id.childNodes[0].nodeValue + '"><img style="border: none;" src="' + root + '/common/images/icons/pdf.gif" /></a>';
                }
                
                var view_resume_link = '<span style="text-decoration: line-through;">Resume</span>';
                if (resumes[i].childNodes.length > 0) {
                    view_resume_link = '<a class="no_link" onClick="show_resume(\'' + resumes[i].childNodes[0].nodeValue + '\', \'' + referral_id.childNodes[0].nodeValue + '\');">Resume</a>'
                }
                
                //html = html + '<td class="view"><a class="no_link" onClick="toggle_testimony(\'suggested_\', \'' + referral_id.childNodes[0].nodeValue + '\');">Testimony</a>&nbsp;|&nbsp;' + view_resume_link + '</td>' + "\n";
                
                html = html + '<td class="member">' + members[i].childNodes[0].nodeValue + '&nbsp;(' + view_testimony_link + ')<hr style="border: none; width: 75%;" /><span style="font-size: 8pt;"><span style="font-weight: bold;">Phone:</span>&nbsp;' + member_phone_nums[i].childNodes[0].nodeValue + '<br/><a href="mailto:' + member_email_addrs[i].childNodes[0].nodeValue + '">Send e-mail</a></span></td>' + "\n";
                
                html = html + '<td class="referee">' + referees[i].childNodes[0].nodeValue + '&nbsp;(' + view_resume_link + ')<hr style="border: none; width: 75%;" /><span style="font-size: 8pt;"><span style="font-weight: bold;">Phone:</span>&nbsp;' + candidate_phone_nums[i].childNodes[0].nodeValue + '<br/><a href="mailto:' + candidate_email_addrs[i].childNodes[0].nodeValue + '">Send e-mail</a></span></td>' + "\n";
                
                html = html + '<td class="date">' + referred_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                
                var acknowledged_on = '';
                if (acknowledged_ons[i].childNodes.length > 0) {
                    acknowledged_on = acknowledged_ons[i].childNodes[0].nodeValue;
                }
                
                html = html + '<td class="date">' + acknowledged_on + '</td>' + "\n";
                
                var shortlist = '<a class="no_link" onClick="shortlist(\'' + referral_id.childNodes[0].nodeValue + '\', true);">Shortlist</a>';
                if (shortlisted_ons[i].childNodes.length > 0) {
                    shortlist = '<a class="no_link" onClick="unshortlist(\'' + referral_id.childNodes[0].nodeValue + '\');">Un-shortlist</a>';
                }
                
                //if (employed_ons[i].childNodes.length <= 0) {
                    if (employer_rejected_ons[i].childNodes.length <= 0) {
                        html = html + '<td class="employ"><a class="no_link" onClick="show_employ_form_with(true, \'' + referral_id.childNodes[0].nodeValue+ '\', \'' + add_slashes(referees[i].childNodes[0].nodeValue) + '\');">Confirm Employed</a><br/>' + shortlist + '&nbsp;|&nbsp;<a class="no_link" onClick="reject(\'' + referral_id.childNodes[0].nodeValue + '\', true);">Reject</a>&nbsp;|&nbsp;<a class="no_link" onClick="show_remarks_form(\'' + referral_id.childNodes[0].nodeValue + '\');">Remarks</a></td>' + "\n";
                    } else {
                        html = html + '<td class="employ"><span style="font-size: 9pt; vertical-align: middle; color: #666666;">Rejected on ' + employer_rejected_ons[i].childNodes[0].nodeValue + '</span>' + '&nbsp;|&nbsp;<a class="no_link" onClick="unreject(\'' + referral_id.childNodes[0].nodeValue + '\');">Un-reject</a>&nbsp;|&nbsp;<a class="no_link" onClick="show_remarks_form(\'' + referral_id.childNodes[0].nodeValue + '\');">Remarks</a></td>' + "\n";
                    }
                    
                //} else {
                //    html = html + '<td class="employ"><span style="font-size: 9pt; vertical-align: middle; color: #666666;">Employed on ' + employed_ons[i].childNodes[0].nodeValue + '</span></td>' + "\n";
                //}
                
                html = html + '</tr>' + "\n";
                html = html + '<tr id="tr_referred_testimony_'+ referral_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                
                var testimony = 'No testimony found.';
                if (testimonies[i].childNodes.length > 0) {
                    testimony = testimonies[i].childNodes[0].nodeValue;
                    testimony = testimony.replace(/&amp;/g, '&');
                    testimony = testimony.replace(/&lt;/g, '<');
                    testimony = testimony.replace(/&gt;/g, '>');
                }
                html = html + '<td colspan="9"><div class="description" id="suggested_testimony_' + referral_id.childNodes[0].nodeValue + '">' + testimony + '</div></td>' + "\n";
                html = html + '</tr>';
            }
            html = html + '</table>';
            
            $('div_suggested_candidates_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading currently referred candidates...');
        }
    });
    
    request.send(params);
}

function show_shortlisted_candidates() {
    show_shortlisted_candidates_with('', '', '');
}

function show_shortlisted_candidates_with(job_id, _title, _industry) {
    if ($('job_id').value == '') {
        $('job_id').value = job_id;
        $('title').set('html', _title);
        $('industry_label').set('html', _industry);
    }
    
    get_salary_from_job($('job_id').value);
    
    $('div_referred_jobs').setStyle('display', 'none');
    $('div_referrals').setStyle('display', 'block');
    $('div_referred').setStyle('display', 'none');
    $('div_suggested').setStyle('display', 'none');
    $('div_shortlisted').setStyle('display', 'block');
    $('li_shortlisted').setStyle('border', '1px solid #CCCCCC');
    $('li_referred').setStyle('border', '1px solid #0000FF');
    $('li_suggested').setStyle('border', '1px solid #0000FF');
    
    var params = 'id=' + $('job_id').value + '&action=get_shortlisted_candidates';
    params = params + '&order_by=' + shortlisted_order_by + ' ' + shortlisted_order;
    
    var uri = root + "/employers/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading referred candidates.');
                return false;
            }
            
            if (txt == '0') {
                var html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There is no candidate shortlisted for this job.</div>';
                $('div_shortlisted_candidates_list').set('html', html);
                set_status('');
                return false;
            }
            
            var ids = xml.getElementsByTagName('id');
            var scores = xml.getElementsByTagName('score_percentage');
            var resumes = xml.getElementsByTagName('resume');
            var members = xml.getElementsByTagName('referrer');
            var referees = xml.getElementsByTagName('candidate');
            var referred_ons = xml.getElementsByTagName('formatted_referred_on');
            var shortlisted_ons = xml.getElementsByTagName('formatted_shortlisted_on');
            var acknowledged_ons = xml.getElementsByTagName('formatted_acknowledged_on');
            var testimonies = xml.getElementsByTagName('testimony');
            var used_suggesteds = xml.getElementsByTagName('used_suggested');
            var agreed_terms_ons = xml.getElementsByTagName('employer_agreed_terms_on');
            var member_email_addrs = xml.getElementsByTagName('referrer_email_addr');
            var candidate_email_addrs = xml.getElementsByTagName('candidate_email_addr');
            var member_phone_nums = xml.getElementsByTagName('referrer_phone_num');
            var candidate_phone_nums = xml.getElementsByTagName('candidate_phone_num');
            
            var html = '<table id="shortlisted_candidates_list" class="list">';
            for (var i=0; i < ids.length; i++) {
                var referral_id = ids[i];
                
                html = html + '<tr id="'+ referral_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                html = html + '<td class="checkbox"><input type="checkbox" id="'+ referral_id.childNodes[0].nodeValue + '" name="id" /></td>' + "\n";
                //html = html + '<td class="id">' + referral_id.childNodes[0].nodeValue + '</td>' + "\n";
                
                var new_referral = '<img id="shortlist_new_' + referral_id.childNodes[0].nodeValue + '"  src="' + root + '/common/images/icons/new.png"';
                if (agreed_terms_ons[i].childNodes[0].nodeValue == '-1') {
                    new_referral = new_referral + ' style="visibility: visible; vertical-align: middle;"';
                } else {
                    new_referral = new_referral + ' style="visibility: hidden; vertical-align: middle;"';
                }
                new_referral = new_referral + ' />';
                html = html + '<td class="indicator">' + new_referral + '</td>' + "\n";
                
                var view_testimony_link = '<a class="no_link" onClick="toggle_testimony(\'shortlisted_\', \'' + referral_id.childNodes[0].nodeValue + '\');">Testimony</a>';
                
                if (testimonies[i].childNodes.length > 0) {
                    view_testimony_link = view_testimony_link + '&nbsp;<a href="' + root + '/employers/testimony_pdf.php?id=' + referral_id.childNodes[0].nodeValue + '"><img style="border: none;" src="' + root + '/common/images/icons/pdf.gif" /></a>';
                }

                var view_resume_link = '<span style="text-decoration: line-through;">Resume</span>';
                if (resumes[i].childNodes.length > 0) {
                    view_resume_link = '<a class="no_link" onClick="show_resume(\'' + resumes[i].childNodes[0].nodeValue + '\', \'' + referral_id.childNodes[0].nodeValue + '\');">Resume</a>'
                }
                
                //html = html + '<td class="view"><a class="no_link" onClick="toggle_testimony(\'shortlisted_\', \'' + referral_id.childNodes[0].nodeValue + '\');">Testimony</a>&nbsp;|&nbsp;' + view_resume_link + '</td>' + "\n";
                
                html = html + '<td class="member">' + members[i].childNodes[0].nodeValue + '&nbsp;(' + view_testimony_link + ')<hr style="border: none; width: 75%;" /><span style="font-size: 8pt;"><span style="font-weight: bold;">Phone:</span>&nbsp;' + member_phone_nums[i].childNodes[0].nodeValue + '<br/><a href="mailto:' + member_email_addrs[i].childNodes[0].nodeValue + '">Send e-mail</a></span></td>' + "\n";
                
                html = html + '<td class="referee">' + referees[i].childNodes[0].nodeValue + '&nbsp;(' + view_resume_link + ')<hr style="border: none; width: 75%;" /><span style="font-size: 8pt;"><span style="font-weight: bold;">Phone:</span>&nbsp;' + candidate_phone_nums[i].childNodes[0].nodeValue + '<br/><a href="mailto:' + candidate_email_addrs[i].childNodes[0].nodeValue + '">Send e-mail</a></span></td>' + "\n";
                
                html = html + '<td class="date">' + referred_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                
                var acknowledged_on = '';
                if (acknowledged_ons[i].childNodes.length > 0) {
                    acknowledged_on = acknowledged_ons[i].childNodes[0].nodeValue;
                }
                
                html = html + '<td class="date">' + acknowledged_on + '</td>' + "\n";
                html = html + '<td class="date">' + shortlisted_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                
                var already_used_suggested = 'false';
                if (used_suggesteds[i].childNodes[0].nodeValue == 'Y') {
                    already_used_suggested = 'true';
                }
                
                var shortlist = '';
                if (shortlisted_ons[i].childNodes.length > 0) {
                    shortlist = '<br/><a class="no_link" onClick="unshortlist(\'' + referral_id.childNodes[0].nodeValue + '\');">Un-shortlist</a>';
                }
                
                html = html + '<td class="employ"><a class="no_link" onClick="show_employ_form_with(' + already_used_suggested + ', \'' + referral_id.childNodes[0].nodeValue+ '\', \'' + add_slashes(referees[i].childNodes[0].nodeValue) + '\');">Confirmed Employed</a>' + shortlist + '&nbsp;|&nbsp;<a class="no_link" onClick="show_remarks_form(\'' + referral_id.childNodes[0].nodeValue + '\');">Remarks</a></td>' + "\n";
                html = html + '</tr>' + "\n";
                html = html + '<tr id="tr_referred_testimony_'+ referral_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                
                var testimony = 'No testimony found.';
                if (testimonies[i].childNodes.length > 0) {
                    testimony = testimonies[i].childNodes[0].nodeValue;
                    testimony = testimony.replace(/&amp;/g, '&');
                    testimony = testimony.replace(/&lt;/g, '<');
                    testimony = testimony.replace(/&gt;/g, '>');
                }
                html = html + '<td colspan="9"><div class="description" id="shortlisted_testimony_' + referral_id.childNodes[0].nodeValue + '">' + testimony + '</div></td>' + "\n";
                html = html + '</tr>';
            }
            html = html + '</table>';
            
            $('div_shortlisted_candidates_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading currently referred candidates...');
        }
    });
    
    request.send(params);
}

function toggle_description(job_id) {
    if ($('desc_' + job_id).getStyle('display') == 'none') {
        $('desc_' + job_id).setStyle('display', 'block');
    } else {
        $('desc_' + job_id).setStyle('display', 'none');
    }
}

function toggle_testimony(prefix, referral_id) {
    if ($(prefix + 'testimony_' + referral_id).getStyle('display') == 'none') {
        $(prefix + 'testimony_' + referral_id).setStyle('display', 'block');
    } else {
        $(prefix + 'testimony_' + referral_id).setStyle('display', 'none');
    }
}

function show_description(job_id) {
    if (job_id == '0' || job_id == null) {
        job_id = $('job_id').value;
    }
    
    $('description').set('html', '');
    $('job_title').set('html', '');
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_description').getStyle('height'));
    var div_width = parseInt($('div_description').getStyle('width'));
    
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
    
    $('div_description').setStyle('top', ((window_height - div_height) / 2));
    $('div_description').setStyle('left', ((window_width - div_width) / 2));
    
    var params = 'id=' + job_id;
    params = params + '&action=get_description';
    
    var uri = root + "/employers/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while retrieving description.');
                return;
            }
            
            var descriptions = xml.getElementsByTagName('description');
            var titles = xml.getElementsByTagName('title');
            
            $('job_title').set('html', titles[0].childNodes[0].nodeValue);
            if (descriptions[0].childNodes.length > 0) {
                $('description').set('html', descriptions[0].childNodes[0].nodeValue);
            } else {
                $('description').set('html', 'No description given.');
            }
            
            $('div_blanket').setStyle('display', 'block');
            $('div_description').setStyle('display', 'block');
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading description...');
        }
    });
    
    request.send(params);
}

function close_description() {
    $('div_description').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_resume_viewing_terms() {
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_resume_viewing_terms').getStyle('height'));
    var div_width = parseInt($('div_resume_viewing_terms').getStyle('width'));
    
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
    
    $('div_resume_viewing_terms').setStyle('top', ((window_height - div_height) / 2));
    $('div_resume_viewing_terms').setStyle('left', ((window_width - div_width) / 2));    
    $('div_blanket').setStyle('display', 'block');
    $('div_resume_viewing_terms').setStyle('display', 'block');
}

function close_resume_viewing_terms() {
    $('div_resume_viewing_terms').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function save_remarks() {
    if (isEmpty($('remarks_field').value)) {
        close_remarks_form();
        return;
    }
    
    var params = 'id=' + $('remarks_referral_id').value + '&action=save_remarks';
    params = params + '&remarks=' + encodeURIComponent($('remarks_field').value);
    
    var uri = root + "/employers/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            close_remarks_form();
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Saving remarks...');
        }
    });
    
    request.send(params);
}

function close_remarks_form() {
    $('div_employer_remarks').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_remarks_form(_referral_id) {
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_employer_remarks').getStyle('height'));
    var div_width = parseInt($('div_employer_remarks').getStyle('width'));
    
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
    
    $('div_employer_remarks').setStyle('top', ((window_height - div_height) / 2));
    $('div_employer_remarks').setStyle('left', ((window_width - div_width) / 2));
    
    $('remarks_referral_id').value = _referral_id;
    
    var params = 'id=' + _referral_id;
    params = params + '&action=get_remark';
    
    var uri = root + "/employers/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while retrieving referral details.');
                return;
            }
            
            var candidates = xml.getElementsByTagName('candidate');
            var titles = xml.getElementsByTagName('title');
            var remarks = xml.getElementsByTagName('remark');
            
            $('remarks_job_title').set('html', titles[0].childNodes[0].nodeValue);
            $('remarks_candidate_name').set('html', candidates[0].childNodes[0].nodeValue);
            
            if (remarks[0].childNodes.length <= 0) {
                $('remarks_field').value = '';
            } else {
                $('remarks_field').value = remarks[0].childNodes[0].nodeValue;
            }
            
            $('div_blanket').setStyle('display', 'block');
            $('div_employer_remarks').setStyle('display', 'block');
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading description...');
        }
    });
    
    request.send(params);
}

function select_all_referred_candidates() {
    var inputs = $('referred_candidates_list').getElementsByTagName('input');
    
    if ($('remove_all_referred').checked) {
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

function select_all_suggested_candidates() {
    var inputs = $('suggested_candidates_list').getElementsByTagName('input');
    
    if ($('remove_all_suggested').checked) {
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

function select_all_shortlisted_candidates() {
    var inputs = $('shortlisted_candidates_list').getElementsByTagName('input');
    
    if ($('remove_all_shortlisted').checked) {
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

function update_suggested_count(_job_id) {
    var params = 'id=' + _job_id;
    params = params + '&action=get_suggested_candidates_count';
    
    var uri = root + "/employers/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (parseInt(txt) > 0) {
                $('suggested_count').set('html', '&nbsp;(' + txt + ')');
            } else {
                $('suggested_count').set('html', '');
            }
        }
    });
    
    request.send(params);
}

function update_referred_count(_job_id) {
    var params = 'id=' + _job_id;
    params = params + '&action=get_referred_candidates_count';
    
    var uri = root + "/employers/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (parseInt(txt) > 0) {
                $('referred_count').set('html', '&nbsp;(' + txt + ')');
            } else {
                $('referred_count').set('html', '');
            }
        }
    });
    
    request.send(params);
}

function update_shortlisted_count(_job_id) {
    var params = 'id=' + _job_id;
    params = params + '&action=get_shortlisted_candidates_count';
    
    var uri = root + "/employers/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (parseInt(txt) > 0) {
                $('shortlisted_count').set('html', '&nbsp;(' + txt + ')');
            } else {
                $('shortlisted_count').set('html', '');
            }
        }
    });
    
    request.send(params);
}

function update_candidate_counts_with(_job_id) {
    if (_job_id == '0' || _job_id == null) {
        _job_id = $('job_id').value;
    }
    
    update_suggested_count(_job_id);
    update_referred_count(_job_id);
    update_shortlisted_count(_job_id);
}

function set_mouse_events() {
    $('li_referred_jobs').addEvent('mouseover', function() {
        $('li_referred_jobs').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_referred_jobs').addEvent('mouseout', function() {
        $('li_referred_jobs').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_referred').addEvent('mouseover', function() {
        $('li_referred').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_referred').addEvent('mouseout', function() {
        $('li_referred').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_suggested').addEvent('mouseover', function() {
        $('li_suggested').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_suggested').addEvent('mouseout', function() {
        $('li_suggested').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_shortlisted').addEvent('mouseover', function() {
        $('li_shortlisted').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_shortlisted').addEvent('mouseout', function() {
        $('li_shortlisted').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
}

function onDomReady() {
    initialize_page();
    set_mouse_events();
    get_employer_referrals_count();
    
    $('li_referred_jobs').addEvent('click', show_referred_jobs);
    $('li_referred').addEvent('click', show_referred_candidates);
    $('li_suggested').addEvent('click', show_suggested_candidates);
    $('li_shortlisted').addEvent('click', show_shortlisted_candidates);
    $('remove_all_referred').addEvent('click', select_all_referred_candidates);
    $('remove_all_suggested').addEvent('click', select_all_suggested_candidates);
    $('remove_all_shortlisted').addEvent('click', select_all_shortlisted_candidates);
    $('remove_referred_candidates').addEvent('click', remove_referred_candidates);
    $('remove_referred_candidates_1').addEvent('click', remove_referred_candidates);
    $('remove_referred_candidates_2').addEvent('click', remove_referred_candidates);
    $('remove_referred_candidates_3').addEvent('click', remove_referred_candidates);
    $('remove_referred_candidates_4').addEvent('click', remove_referred_candidates);
    $('remove_referred_candidates_5').addEvent('click', remove_referred_candidates);
    
    $('filter_by').addEvent('change', function() {
        filter_by = $('filter_by').options[$('filter_by').selectedIndex].value;
        show_referred_candidates();
    });
    
    $('day').addEvent('focus', function() {
        if ($('day').value == 'dd') {
            $('day').value = '';
        }
    });
    
    $('day').addEvent('blur', function() {
        if ($('day').value == '' || $('day').value > 31) {
            $('day').value = 'dd';
        }
    });
    
    $('sort_industry').addEvent('click', function() {
        order_by = 'industry';
        ascending_or_descending();
        show_referred_jobs();
    });
    
    $('sort_title').addEvent('click', function() {
        order_by = 'title';
        ascending_or_descending();
        show_referred_jobs();
    });
    
    $('sort_created_on').addEvent('click', function() {
        order_by = 'created_on';
        ascending_or_descending();
        show_referred_jobs();
    });
    
    $('sort_expire_on').addEvent('click', function() {
        order_by = 'expire_on';
        ascending_or_descending();
        show_referred_jobs();
    });
    
    $('sort_referrals').addEvent('click', function() {
        order_by = 'num_referrals';
        ascending_or_descending();
        show_referred_jobs();
    });
    
    $('sort_referred_member').addEvent('click', function() {
        referred_order_by = 'referrer';
        referred_ascending_or_descending();
        show_referred_candidates();
    });
    
    $('sort_referred_referee').addEvent('click', function() {
        referred_order_by = 'candidate';
        referred_ascending_or_descending();
        show_referred_candidates();
    });
    
    $('sort_referred_referred_on').addEvent('click', function() {
        referred_order_by = 'referrals.referred_on';
        referred_ascending_or_descending();
        show_referred_candidates();
    });
    
    $('sort_referred_acknowledged_on').addEvent('click', function() {
        referred_order_by = 'referrals.referee_acknowledged_on';
        referred_ascending_or_descending();
        show_referred_candidates();
    });
    
    $('sort_suggested_score').addEvent('click', function() {
        suggested_order_by = 'score';
        suggested_ascending_or_descending();
        show_suggested_candidates();
    });
    
    $('sort_suggested_member').addEvent('click', function() {
        suggested_order_by = 'referrer';
        suggested_ascending_or_descending();
        show_suggested_candidates();
    });
    
    $('sort_suggested_referee').addEvent('click', function() {
        suggested_order_by = 'candidate';
        suggested_ascending_or_descending();
        show_suggested_candidates();
    });
    
    $('sort_suggested_referred_on').addEvent('click', function() {
        suggested_order_by = 'referrals.suggested_on';
        suggested_ascending_or_descending();
        show_suggested_candidates();
    });
    
    $('sort_suggested_acknowledged_on').addEvent('click', function() {
        suggested_order_by = 'referrals.referee_acknowledged_on';
        suggested_ascending_or_descending();
        show_suggested_candidates();
    });
    
    $('sort_shortlisted_member').addEvent('click', function() {
        shortlisted_order_by = 'referrer';
        shortlisted_ascending_or_descending();
        show_shortlisted_candidates();
    });
    
    $('sort_shortlisted_referee').addEvent('click', function() {
        shortlisted_order_by = 'candidate';
        shortlisted_ascending_or_descending();
        show_shortlisted_candidates();
    });
    
    $('sort_shortlisted_referred_on').addEvent('click', function() {
        shortlisted_order_by = 'referrals.referred_on';
        shortlisted_ascending_or_descending();
        show_shortlisted_candidates();
    });
    
    $('sort_shortlisted_acknowledged_on').addEvent('click', function() {
        shortlisted_order_by = 'referrals.referee_acknowledged_on';
        shortlisted_ascending_or_descending();
        show_shortlisted_candidates();
    });
    
    $('sort_shortlisted_shortlisted_on').addEvent('click', function() {
        shortlisted_order_by = 'referrals.shortlisted_on';
        shortlisted_ascending_or_descending();
        show_shortlisted_candidates();
    });
    
    if (job_to_list > 0) {
        show_suggested_candidates_with(job_to_list, job_to_list_title, job_to_list_industry);
    } else {
        show_referred_jobs();
    }
}

window.addEvent('domready', onDomReady);
