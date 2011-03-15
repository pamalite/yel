var order_by = 'num_referred';
var order = 'desc';
var employers_order_by = 'num_referred';
var employers_order = 'desc';
var referrals_order_by = 'referrals.referred_on';
var referrals_order = 'desc';

var selected_employer_id = '';
var selected_job_id = '';
var selected_job_title = '';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function employers_ascending_or_descending() {
    if (employers_order == 'desc') {
        employers_order = 'asc';
    } else {
        employers_order = 'desc';
    }
}

function referrals_ascending_or_descending() {
    if (referrals_order == 'desc') {
        referrals_order = 'asc';
    } else {
        referrals_order = 'desc';
    }
}

function show_resume_page(resume_id) {
    var popup = window.open('../employees/resume.php?id=' + resume_id, '', 'scrollbars');
    
    if (!popup) {
        alert('Popup blocker was detected. Please allow pop-up windows for YellowElevator.com and try again.');
    }
}

function show_employers() {
    $('div_jobs').setStyle('display', 'none');
    $('div_referrals').setStyle('display', 'none');
    $('div_employers').setStyle('display', 'block');
    
    selected_employer_id = '';
    
    var params = 'id=0';
    params = params + '&order_by=' + employers_order_by + ' ' + employers_order;
    
    var uri = root + "/employees/applications_action.php";
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
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">No employers created at the moment.</div>';
            } else {
                var ids = xml.getElementsByTagName('id');
                var employers = xml.getElementsByTagName('name');
                var open_jobs = xml.getElementsByTagName('num_open');
                var num_referreds = xml.getElementsByTagName('num_referred');
                var num_submitteds = xml.getElementsByTagName('num_submitted');
                var num_kivs = xml.getElementsByTagName('num_kiv');
                
                for (i=0; i < ids.length; i++) {
                    var id = ids[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    
                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    if (open_jobs[i].childNodes[0].nodeValue == '0') {
                        html = html + '<td class="count">' + open_jobs[i].childNodes[0].nodeValue + '</td>' + "\n";
                    } else {
                        html = html + '<td class="count"><a class="no_link" onClick="show_jobs(\'' + id + '\');">' + open_jobs[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    }
                    html = html + '<td class="count">' + num_referreds[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="count">' + num_submitteds[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="count">' + num_kivs[i].childNodes[0].nodeValue + '</td>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_employers_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading employers...');
        }
    });
    
    request.send(params);
}

function show_jobs_with_selected_employer() {
    show_jobs(selected_employer_id);
}

function set_employer_name() {
    var params = 'id=' + selected_employer_id + '&action=get_employer_name';
    
    var uri = root + "/employees/applications_action.php";
    
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            var name = xml.getElementsByTagName('name');
            $('employer_name').set('html', name[0].childNodes[0].nodeValue);
        }
    });
    
    request.send(params);
}

function show_jobs(_employer_id) {
    selected_employer_id = _employer_id;
    
    $('div_jobs').setStyle('display', 'block');
    $('div_referrals').setStyle('display', 'none');
    $('div_employers').setStyle('display', 'none');
    
    set_employer_name();
    
    var params = 'id=' + selected_employer_id + '&action=get_jobs';
    params = params + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employees/applications_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading opened jobs.');
                return false;
            }
            
            var ids = xml.getElementsByTagName('id');
            var industries = xml.getElementsByTagName('industry');
            var titles = xml.getElementsByTagName('title');
            var created_ons = xml.getElementsByTagName('created_on');
            var expire_ons = xml.getElementsByTagName('expire_on');
            var num_referreds = xml.getElementsByTagName('num_referred');
            var num_submitteds = xml.getElementsByTagName('num_submitted');
            var num_kivs = xml.getElementsByTagName('num_kiv');
            
            var html = '<table id="list" class="list">';
            if (ids.length <= 0) {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">No jobs added at the moment.</div>';
            } else {
                for (i=0; i < ids.length; i++) {
                    var job_id = ids[i];
                    
                    html = html + '<tr id="'+ job_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    
                    html = html + '<td class="industry">' + industries[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    html = html + '<td class="title"><a href="../job/' + job_id.childNodes[0].nodeValue + '" target="_new">' + titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="date">' + created_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + expire_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="count"><a class="no_link" onClick="show_referrals(\'' + job_id.childNodes[0].nodeValue + '\', \'referred\');">' + num_referreds[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="count"><a class="no_link" onClick="show_referrals(\'' + job_id.childNodes[0].nodeValue + '\', \'submitted\');">' + num_submitteds[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="count"><a class="no_link" onClick="show_referrals(\'' + job_id.childNodes[0].nodeValue + '\', \'kiv\');">' + num_kivs[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_jobs_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading currently opened jobs...');
        }
    });
    
    request.send(params);
}

function set_job_title() {
    var params = 'id=' + selected_job_id + '&action=get_job_title';
    
    var uri = root + "/employees/applications_action.php";
    
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            var title = xml.getElementsByTagName('title');
            $('job_title').set('html', '<a href="../job/' + selected_job_id + '" target="_new">' + title[0].childNodes[0].nodeValue + '</a>');
        }
    });
    
    request.send(params);
}

function show_referrals_with_selected_job() {
    show_referrals(selected_job_id, false);
}

function show_referrals(_job_id, _sort_by) {
    selected_job_id = _job_id;
    
    $('div_jobs').setStyle('display', 'none');
    $('div_referrals').setStyle('display', 'block');
    $('div_employers').setStyle('display', 'none');
    
    set_job_title();
    
    var params = 'id=' + selected_job_id + '&action=get_referrals';
    
    switch (_sort_by) {
        case 'submitted':
            referrals_order_by = 'referrals.member_confirmed_on';
            referrals_order = 'desc';            
            break;
        case 'kiv':
            referrals_order_by = 'referrals.employer_agreed_terms_on';
            referrals_order = 'desc';
            break;
    }
    params = params + '&order_by=' + referrals_order_by + ' ' + referrals_order;
    
    var uri = root + "/employees/applications_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading referrals.');
                return false;
            }
            
            var ids = xml.getElementsByTagName('id');
            var candidate_names = xml.getElementsByTagName('candidate_name');
            var candidate_email_addrs = xml.getElementsByTagName('candidate_email_addr');
            var candidate_phone_nums = xml.getElementsByTagName('candidate_phone_num');
            var referrer_names = xml.getElementsByTagName('referrer_name');
            var referrer_email_addrs = xml.getElementsByTagName('referrer_email_addr');
            var referrer_phone_nums = xml.getElementsByTagName('referrer_phone_num');
            var referred_ons = xml.getElementsByTagName('formatted_referred_on');
            var viewed_ons = xml.getElementsByTagName('formatted_employer_viewed_on');
            var confirmed_ons = xml.getElementsByTagName('formatted_confirmed_on');
            var resume_ids = xml.getElementsByTagName('resume_id');
            var resume_names = xml.getElementsByTagName('resume_name');
            
            var html = '<table id="closed_list" class="list">';
            if (ids.length <= 0) {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no referrals at the moment.</div>';
            } else {
                for (i=0; i < ids.length; i++) {
                    var referral_id = ids[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ referral_id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    
                    html = html + '<td class="date">' + referred_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="referrer"><a href="mailto: ' + referrer_email_addrs[i].childNodes[0].nodeValue + '">' + referrer_names[i].childNodes[0].nodeValue + '</a><br/><div class="phone_num"><strong>Tel:</strong> ' + referrer_phone_nums[i].childNodes[0].nodeValue + '<br/><strong>E-mail:</strong> ' + referrer_email_addrs[i].childNodes[0].nodeValue + '</div></td>' + "\n";
                    html = html + '<td class="candidate"><a href="mailto: ' + candidate_email_addrs[i].childNodes[0].nodeValue + '">' + candidate_names[i].childNodes[0].nodeValue + '</a><br/><div class="phone_num"><strong>Tel:</strong> ' + candidate_phone_nums[i].childNodes[0].nodeValue + '<br/><strong>E-mail:</strong> ' + candidate_email_addrs[i].childNodes[0].nodeValue + '</div></td>' + "\n";
                    
                    var confirmed_on = '<span style="font-size: 9pt; color: #CCCCCC;">Pending...</span>';
                    if (confirmed_ons[i].childNodes.length > 0) {
                        confirmed_on = confirmed_ons[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="date">' + confirmed_on + '</td>' + "\n";
                    
                    var viewed_on = '<span style="font-size: 9pt; color: #CCCCCC;">Pending...</span>';
                    if (viewed_ons[i].childNodes.length > 0) {
                        viewed_on = viewed_ons[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="date">' + viewed_on + '</td>' + "\n";
                    
                    html = html + '<td class="links"><a class="no_link" onClick="show_resume_page(' + resume_ids[i].childNodes[0].nodeValue + ');">' + resume_names[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '</tr>' + "\n";

                }
                html = html + '</table>';
            }
            
            $('div_referrals_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading referrals...');
        }
    });
    
    request.send(params);
}

function set_mouse_events() {
    $('li_back').addEvent('mouseover', function() {
        $('li_back').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_back').addEvent('mouseout', function() {
        $('li_back').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_back_1').addEvent('mouseover', function() {
        $('li_back_1').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_back_1').addEvent('mouseout', function() {
        $('li_back_1').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
}

function onDomReady() {
    initialize_page();
    set_mouse_events();
    get_unapproved_photos_count();
    get_employee_rewards_count();
    get_employee_tokens_count();
    
    $('li_back').addEvent('click', show_employers);
    $('li_back_1').addEvent('click', show_jobs_with_selected_employer);
    
    // --- begin employers ---
    $('sort_employer').addEvent('click', function() {
        employers_order_by = 'employers.name';
        employers_ascending_or_descending();
        show_employers();
    });
    
    $('sort_open').addEvent('click', function() {
        employers_order_by = 'num_open';
        employers_ascending_or_descending();
        show_employers();
    });
    
    $('sort_referred').addEvent('click', function() {
        employers_order_by = 'num_referred';
        employers_ascending_or_descending();
        show_employers();
    });
    
    $('sort_submitted').addEvent('click', function() {
        employers_order_by = 'num_submitted';
        employers_ascending_or_descending();
        show_employers();
    });
    
    $('sort_viewed').addEvent('click', function() {
        employers_order_by = 'num_kiv';
        employers_ascending_or_descending();
        show_employers();
    });
    // --- end employers ---
    
    // --- begin jobs --- 
    $('sort_industry').addEvent('click', function() {
        order_by = 'industries.industry';
        ascending_or_descending();
        show_jobs_with_selected_employer();
    });
    
    $('sort_title').addEvent('click', function() {
        order_by = 'jobs.title';
        ascending_or_descending();
        show_jobs_with_selected_employer();
    });
    
    $('sort_created_on').addEvent('click', function() {
        order_by = 'jobs.created_on';
        ascending_or_descending();
        show_jobs_with_selected_employer();
    });
    
    $('sort_expire_on').addEvent('click', function() {
        order_by = 'jobs.expire_on';
        ascending_or_descending();
        show_jobs_with_selected_employer();
    });
    
    $('sort_jobs_referred').addEvent('click', function() {
        order_by = 'num_referred';
        ascending_or_descending();
        show_jobs_with_selected_employer();
    });
    
    $('sort_jobs_submitted').addEvent('click', function() {
        order_by = 'num_submitted';
        ascending_or_descending();
        show_jobs_with_selected_employer();
    });
    
    $('sort_jobs_viewed').addEvent('click', function() {
        order_by = 'num_kiv';
        ascending_or_descending();
        show_jobs_with_selected_employer();
    });
    // --- end jobs ---
    
    // --- begin referrals ---
    $('sort_referred_on').addEvent('click', function() {
        referrals_order_by = 'referrals.created_on';
        referrals_ascending_or_descending();
        show_referrals_with_selected_job();
    });
    
    $('sort_referrer').addEvent('click', function() {
        referrals_order_by = 'referrers.lastname';
        referrals_ascending_or_descending();
        show_referrals_with_selected_job();
    });
    
    $('sort_candidate').addEvent('click', function() {
        referrals_order_by = 'members.lastname';
        referrals_ascending_or_descending();
        show_referrals_with_selected_job();
    });
    
    $('sort_submitted_on').addEvent('click', function() {
        referrals_order_by = 'referrals.member_confirmed_on';
        referrals_ascending_or_descending();
        show_referrals_with_selected_job();
    });
    
    $('sort_employer_viewed_on').addEvent('click', function() {
        referrals_order_by = 'referrals.employer_agreed_terms_on';
        referrals_ascending_or_descending();
        show_referrals_with_selected_job();
    });
    // --- end referrals ---
    
    show_employers();
}

window.addEvent('domready', onDomReady);
