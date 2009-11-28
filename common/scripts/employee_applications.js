var order_by = 'jobs.created_on';
var order = 'desc';
var employers_order_by = 'num_referred';
var employers_order = 'desc';
var referrals_order_by = 'referrals.referred_on';
var referrals_order = 'desc';

var selected_employer_id = '';
var selected_employer_currency = 'MYR';
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
    selected_employer_currency = '';
    
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
                var num_kivs = xml.getElementsByTagName('num_kiv');
                
                for (i=0; i < ids.length; i++) {
                    var id = ids[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    
                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="count"><a class="no_link" onClick="show_jobs(\'' + id + '\');">' + open_jobs[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="count"><a class="no_link" onClick="show_referrals(\'' + id + '\', false);">' + num_referreds[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="count"><a class="no_link" onClick="show_referrals(\'' + id + '\', true);">' + num_kivs[i].childNodes[0].nodeValue + '</a></td>' + "\n";
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

function get_employer_currency() {
    var params = 'id=' + selected_employer_id + '&action=get_currency';
    
    var uri = root + "/employees/jobs_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            selected_employer_currency = txt;
            
            $('currency').value = selected_employer_currency;
            $('employer.currency').set('html', selected_employer_currency);
            $('employer.currency_1').set('html', selected_employer_currency);
        }
    });
    
    request.send(params);
}

function show_jobs_with_selected_employer() {
    show_jobs(selected_employer_id);
}

function show_jobs(_employer_id) {
    selected_employer_id = _employer_id;
    
    if (isEmpty(selected_employer_currency)) {
        get_employer_currency();
    }
    
    $('div_jobs').setStyle('display', 'block');
    $('div_employers').setStyle('display', 'none');
    
    selected_tab = 'li_open';
    $(selected_tab).setStyle('border', '1px solid #CCCCCC');
    $('li_closed').setStyle('border', '1px solid #0000FF');
    $('div_open').setStyle('display', 'block');
    $('div_closed').setStyle('display', 'none');
    $('div_job_info').setStyle('display', 'none');
    $('div_job_form').setStyle('display', 'none');
    $('open_back_arrow').setStyle('display', 'none');
    $('closed_back_arrow').setStyle('display', 'none');
    
    var params = 'id=' + selected_employer_id + '&action=get_jobs';
    params = params + '&order_by=' + order_by + ' ' + order;
    params = params + '&closed=N';
    
    var uri = root + "/employees/jobs_action.php";
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
            var expired_days = xml.getElementsByTagName('expired_days');
            var closeds = xml.getElementsByTagName('closed');
            
            var html = '<table id="list" class="list">';
            if (ids.length <= 0) {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">Please click on the \"Add New Job Ad\" button to get started.</div>';
                
                $('close_jobs').disabled = true;
                $('close_jobs_1').disabled = true;
            } else {
                var odd = true;
                for (i=0; i < ids.length; i++) {
                    var job_id = ids[i];
                    
                    html = html + '<tr id="'+ job_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    
                    html = html + '<td class="checkbox"><input type="checkbox" id="'+ job_id.childNodes[0].nodeValue + '" /></td>' + "\n";
                    //html = html + '<td class="id">' + job_id.childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="industry">' + industries[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    html = html + '<td class="title"><a href="#" onClick="show_job(\'' + job_id.childNodes[0].nodeValue + '\')">' + titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="date">' + created_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    if (parseInt(expired_days[i].childNodes[0].nodeValue) > 0) {
                        html = html + '<td class="date"><span style="font-weight: bold; color: #FF0000;">' + expire_ons[i].childNodes[0].nodeValue + '</span></td>' + "\n";
                    } else {
                        html = html + '<td class="date">' + expire_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    }
                    
                    var closed = closeds[i].childNodes[0].nodeValue;
                    if (closed == 'N') {
                        html = html + '<td class="new_from"><input type="button" class="mini_button" value="New From This Job" onClick="new_from_job(\'' + job_id.childNodes[0].nodeValue + '\')" /></td>' + "\n";
                    } else {
                        html = html + '<td class="new_from"><input type="button" class="mini_button" value="Update" onClick="show_update_job(\'' + job_id.childNodes[0].nodeValue + '\')" /></td>' + "\n";
                    }
                    // html = html + '<td class="new_from"><input type="button" class="mini_button" value="New From This Job" onClick="new_from_job(\'' + job_id.childNodes[0].nodeValue + '\')" /></td>' + "\n";
                    html = html + '</tr>' + "\n";

                    if (odd) {
                        odd = false;
                    } else {
                        odd = true;
                    }
                }
                html = html + '</table>';
                
                $('close_jobs').disabled = false;
                $('close_jobs_1').disabled = false;
            }
            
            $('div_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading currently opened jobs...');
        }
    });
    
    request.send(params);
}

function show_referrals_with_selected_job() {
    show_referrals(selected_job_id, false);
}

function show_referrals(_job_id, _show_kiv_first) {
    selected_job_id = _job_id;
    
    if (isEmpty(selected_employer_currency)) {
        get_employer_currency();
    }
    
    $('div_jobs').setStyle('display', 'block');
    $('div_employers').setStyle('display', 'none');
    
    selected_tab = 'li_closed';
    $(selected_tab).setStyle('border', '1px solid #CCCCCC');
    $('li_open').setStyle('border', '1px solid #0000FF');
    $('div_closed').setStyle('display', 'block');
    $('div_open').setStyle('display', 'none');
    $('div_job_info').setStyle('display', 'none');
    $('div_job_form').setStyle('display', 'none');
    $('open_back_arrow').setStyle('display', 'none');
    $('closed_back_arrow').setStyle('display', 'none');
    
    var params = 'id=' + selected_employer_id + '&action=get_jobs';
    params = params + '&order_by=' + order_by + ' ' + order;
    params = params + '&closed=Y';
    
    var uri = root + "/employers/jobs_action.php";
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
            
            var html = '<table id="closed_list" class="list">';
            if (ids.length <= 0) {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no closed jobs at the moment.</div>';
            } else {
                var odd = true;
                for (i=0; i < ids.length; i++) {
                    var job_id = ids[i];
                    
                    html = html + '<tr id="'+ job_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    
                    //html = html + '<td class="id">' + job_id.childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="industry">' + industries[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title"><a href="#" onClick="show_job(\'' + job_id.childNodes[0].nodeValue + '\')">' + titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="date">' + created_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + expire_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="new_from"><input type="button" class="mini_button" value="New From This Job" onClick="new_from_job(\'' + job_id.childNodes[0].nodeValue + '\')" /></td>' + "\n";
                    html = html + '</tr>' + "\n";

                    if (odd) {
                        odd = false;
                    } else {
                        odd = true;
                    }
                }
                html = html + '</table>';
            }
            
            $('div_closed_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading closed jobs...');
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
    set_root();
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
        show_jobswith_selected_employer();
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
        show_open_jobs_with_selected_employer();
    });
    
    $('sort_jobs_referred').addEvent('click', function() {
        order_by = 'num_referred';
        ascending_or_descending();
        show_jobs_with_selected_employer();
    });
    
    $('sort_jobs_viewed').addEvent('click', function() {
        order_by = 'num_kiv';
        ascending_or_descending();
        show_open_jobs_with_selected_employer();
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
    
    $('sort_employer_viewed_on').addEvent('click', function() {
        referrals_order_by = 'referrals.employer_agreed_terms_on';
        referrals_ascending_or_descending();
        show_referrals_with_selected_job();
    });
    // --- end referrals ---
    
    show_employers();
}

window.addEvent('domready', onDomReady);
