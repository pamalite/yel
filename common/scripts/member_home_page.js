var jobs_order_by = 'referrals.referred_on';
var jobs_order = 'desc';
var rewards_order_by = 'referrals.employed_on';
var rewards_order = 'desc';
var approvals_order_by = 'member_referees.referred_on';
var approvals_order = 'desc';
var acknowledgements_order_by = 'member_referees.referred_on';
var acknowledgements_order = 'desc';

var referral = 0;

function verify() {
    if ($('mini_keywords').value == 'Job title or keywords' ||
        $('mini_keywords').value == '') {
        alert('You need to enter at least a keyword to begin searching.');
        return false;
    }
    
    return true;
}

function jobs_ascending_or_descending() {
    if (jobs_order == 'desc') {
        jobs_order = 'asc';
    } else {
        jobs_order = 'desc';
    }
}

function approvals_ascending_or_descending() {
    if (approvals_order == 'desc') {
        approvals_order = 'asc';
    } else {
        approvals_order = 'desc';
    }
}

function rewards_ascending_or_descending() {
    if (rewards_order == 'desc') {
        rewards_order = 'asc';
    } else {
        rewards_order = 'desc';
    }
}

function acknowledgements_ascending_or_descending() {
    if (acknowledgements_order == 'desc') {
        acknowledgements_order = 'asc';
    } else {
        acknowledgements_order = 'desc';
    }
}

function show_guide_page(_guide_page) {
    if (!isEmpty(_guide_page)) {
        var popup = window.open('guides/' + _guide_page, '', 'width=800px, height=600px, scrollbars');

        if (!popup) {
            alert('Popup blocker was detected. Please allow pop-up windows for YellowElevator.com and try again.');
        }
    }
}

function close_job_info() {
    $('div_job_info').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_job_info(job_id) {
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_job_info').getStyle('height'));
    var div_width = parseInt($('div_job_info').getStyle('width'));
    
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
    
    $('div_job_info').setStyle('top', ((window_height - div_height) / 2));
    $('div_job_info').setStyle('left', ((window_width - div_width) / 2));
    
    var params = 'id=' + job_id;
    params = params + '&action=get_job_info';
    
    var uri = root + "/members/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while retrieving job_info.');
                return;
            }
            
            var titles = xml.getElementsByTagName('title');
            var descriptions = xml.getElementsByTagName('description');
            var industries = xml.getElementsByTagName('industry');
            var employers = xml.getElementsByTagName('employer');
            var currencies = xml.getElementsByTagName('currency');
            var countries = xml.getElementsByTagName('country_name');
            var states = xml.getElementsByTagName('state');
            var potential_rewards = xml.getElementsByTagName('potential_reward');
            var salaries = xml.getElementsByTagName('salary');
            var salary_ends = xml.getElementsByTagName('salary_end');
            var salary_negotiables = xml.getElementsByTagName('salary_negotiable');
            var created_ons = xml.getElementsByTagName('created_on');
            var expire_ons = xml.getElementsByTagName('expire_on');
            
            $('job.title').set('html', titles[0].childNodes[0].nodeValue);
            $('job.currency').set('html', currencies[0].childNodes[0].nodeValue);
            $('job.currency_1').set('html', currencies[0].childNodes[0].nodeValue);
            $('job.potential_reward').set('html', potential_rewards[0].childNodes[0].nodeValue);
            $('job.industry').set('html', industries[0].childNodes[0].nodeValue);
            $('job.employer').set('html', employers[0].childNodes[0].nodeValue);
            $('job.country').set('html', countries[0].childNodes[0].nodeValue);
            
            var state = 'n/a';
            if (states[0].childNodes.length > 0) {
                state = states[0].childNodes[0].nodeValue;
            }
            $('job.state').set('html', state);
            $('job.salary').set('html', salaries[0].childNodes[0].nodeValue);
            
            if (salary_ends[0].childNodes.length <= 0) {
                $('job.salary_end').set('html', '');
            } else {
                $('job.salary_end').set('html', '-&nbsp;' + salary_ends[0].childNodes[0].nodeValue);
            }
            
            var negotiable = 'Not Negotiable';
            if (salary_negotiables[0].childNodes[0].nodeValue == 'Y') {
                negotiable = 'Negotiable';
            }
            
            $('job.salary_negotiable').set('html', negotiable);
            $('job.description').set('html', descriptions[0].childNodes[0].nodeValue);
            $('job.created_on').set('html', created_ons[0].childNodes[0].nodeValue);
            $('job.expire_on').set('html', expire_ons[0].childNodes[0].nodeValue);
            
            $('div_blanket').setStyle('display', 'block');
            $('div_job_info').setStyle('display', 'block');
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading job_info...');
        }
    });
    
    request.send(params);
}

function close_acknowledge_form() {
    $('div_acknowledge_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
    referral = 0;
}

function show_acknowledge_form(referral_id, job_title) {
    referral = referral_id;
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_acknowledge_form').getStyle('height'));
    var div_width = parseInt($('div_acknowledge_form').getStyle('width'));
    
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
    
    $('div_acknowledge_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_acknowledge_form').setStyle('left', ((window_width - div_width) / 2));
    
    var params = 'id=' + id;
    params = params + '&action=has_resumes';
    
    var uri = root + "/members/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            if (txt == '0') {
                alert('You have yet to create a resume. You need to have a resume before you can accept a job referral. Please create one in \'Resumes\'.\n\nIf you have already created a resume, you will need to make it non-private.');
                referral = 0;
                return false;
            }
            
            $('job_title').set('html', job_title);
            $('div_blanket').setStyle('display', 'block');
            $('div_acknowledge_form').setStyle('display', 'block');
        },
        onRequest: function(instance) {
            set_status('Checking resumes...');
        }
    });
    
    request.send(params);
}

function acknowledge() {
    if ($('resume').options[$('resume').selectedIndex].value == '0') {
        alert('Please choose a resume to proceed.');
        return false;
    }
    
    var params = 'id=' + referral + '&resume=' + $('resume').options[$('resume').selectedIndex].value;
    params = params + '&action=acknowledge_job';
    
    var uri = root + "/members/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while acknowledging the referred job.');
                return false;
            }
            
            set_status('');
            if (txt == '-1') {
                alert('Sorry, this job only accepts resumes created online.');
                return false;
            } else if (txt == '-2') {
                alert('Sorry, this job only accepts uploaded file resumes.');
                return false;
            }
            
            close_acknowledge_form();
            show_referred_jobs();
        },
        onRequest: function(instance) {
            set_status('Acknowledging referred job...');
        }
    });
    
    request.send(params);
}

function reject_job(referral_id, job_title) {
    var sure = confirm('Clicking \'OK\' will remove "' + job_title + '" from this section permanently. Do you wish to continue?');
    
    if (!sure) {
        return false;
    }
    
    var params = 'id=' + referral_id;
    params = params + '&action=reject_job';
    
    var uri = root + "/members/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while ignoring the referred job.');
                return false;
            }
            
            set_status('');
            show_referred_jobs();
        },
        onRequest: function(instance) {
            set_status('Ignoring referred job...');
        }
    });
    
    request.send(params);
}

function show_referred_jobs() {
    $('div_jobs').setStyle('display', 'block');
    //$('div_rewards').setStyle('display', 'none');
    $('div_approvals').setStyle('display', 'none');
    //$('div_acknowledgements').setStyle('display', 'none');
    $('li_jobs').setStyle('border', '1px solid #CCCCCC');
    //$('li_rewards').setStyle('border', '1px solid #0000FF');
    $('li_approvals').setStyle('border', '1px solid #0000FF');
    //$('li_acknowledgements').setStyle('border', '1px solid #0000FF');
    
    var params = 'id=' + id + '&action=get_recent_referred_jobs';
    params = params + '&order_by=' + jobs_order_by + ' ' + jobs_order;
    
    var uri = root + "/members/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading referred jobs.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">You are not referred to any jobs at the moment.</div>';
                
                $('referred_count').set('html', '');
            } else {
                var ids = xml.getElementsByTagName('id');
                var jobs = xml.getElementsByTagName('job_id');
                var industries = xml.getElementsByTagName('industry');
                var titles = xml.getElementsByTagName('title');
                var referrers = xml.getElementsByTagName('referrer');
                var referred_ons = xml.getElementsByTagName('formatted_referred_on');
                var employers = xml.getElementsByTagName('employer');
                
                for (var i=0; i < ids.length; i++) {
                    var referral_id = ids[i].childNodes[0].nodeValue;
                    var job_id = jobs[i].childNodes[0].nodeValue;

                    html = html + '<tr id="'+ referral_id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";

                    html = html + '<td class="industry">' + industries[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title"><a class="no_link" onClick="show_job_info(\'' + job_id + '\');">' + titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="title">' + referrers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + referred_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="acknowledge"><a class="no_link" onClick="show_acknowledge_form(\'' + referral_id + '\', \'' + titles[i].childNodes[0].nodeValue + '\');">Accept</a>&nbsp;|&nbsp;<a class="no_link" onClick="reject_job(\'' + referral_id + '\', \'' + titles[i].childNodes[0].nodeValue + '\');">Ignore</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
                
                $('referred_count').set('html', ' (' + ids.length + ')');
            }
            
            $('div_referred_jobs_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading currently referred jobs...');
        }
    });
    
    request.send(params);
}

function approve_contact(referee_id, contact_email) {
    var params = 'id=' + referee_id + '&member=' + id + '&contact=' + contact_email;
    params = params + '&action=approve_contact';
    
    var uri = root + "/members/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while approving contact.');
                return false;
            }
            
            set_status('');
            show_approvals();
        },
        onRequest: function(instance) {
            set_status('Approving contact...');
        }
    });
    
    request.send(params);
}

function reject_contact(referee_id) {
    var params = 'id=' + referee_id;
    params = params + '&action=reject_contact';
    
    var uri = root + "/members/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while ignoring contact.');
                return false;
            }
            
            set_status('');
            show_approvals();
        },
        onRequest: function(instance) {
            set_status('Ignoring contact...');
        }
    });
    
    request.send(params);
}

function show_approvals() {
    $('div_jobs').setStyle('display', 'none');
    //$('div_rewards').setStyle('display', 'none');
    $('div_approvals').setStyle('display', 'block');
    //$('div_acknowledgements').setStyle('display', 'none');
    $('li_jobs').setStyle('border', '1px solid #0000FF');
    //$('li_rewards').setStyle('border', '1px solid #0000FF');
    $('li_approvals').setStyle('border', '1px solid #CCCCCC');
    //$('li_acknowledgements').setStyle('border', '1px solid #0000FF');
    
    var params = 'id=' + id + '&action=get_approvals';
    params = params + '&order_by=' + approvals_order_by + ' ' + approvals_order;
    
    var uri = root + "/members/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading approvals.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no contacts to approve at the moment.<br/><br/>In order to view jobs referred to you by members who are not in your <a href="' + root + '/members/candidates.php">Contacts</a> yet, you need to approve adding them first before you can see the jobs referred to you in <a class="no_link" onClick="show_referred_jobs();">Jobs Referred To Me</a>.</div>';
                
                $('approval_count').set('html', '');
            } else {
                var ids = xml.getElementsByTagName('id');
                var emails = xml.getElementsByTagName('email_addr');
                var names = xml.getElementsByTagName('member_name');
                var referred_ons = xml.getElementsByTagName('formatted_referred_on');
                
                for (var i=0; i < ids.length; i++) {
                    var id = ids[i].childNodes[0].nodeValue;

                    html = html + '<tr id="'+ id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="title">' + emails[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title">' + names[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + referred_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="approve"><a class="no_link" onClick="approve_contact(\'' + id + '\', \'' + emails[i].childNodes[0].nodeValue + '\');">Approve</a>&nbsp;|&nbsp;<a class="no_link" onClick="reject_contact(\'' + id + '\');">Ignore</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
                
                $('approval_count').set('html', ' (' + ids.length + ')');
            }
            
            $('div_approvals_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading approvals...');
        }
    });
    
    request.send(params);
}

function show_rewards() {
    $('div_jobs').setStyle('display', 'none');
    $('div_rewards').setStyle('display', 'block');
    $('div_approvals').setStyle('display', 'none');
    //$('div_acknowledgements').setStyle('display', 'none');
    $('li_jobs').setStyle('border', '1px solid #0000FF');
    $('li_rewards').setStyle('border', '1px solid #CCCCCC');
    $('li_approvals').setStyle('border', '1px solid #0000FF');
    //$('li_acknowledgements').setStyle('border', '1px solid #0000FF');
    
    var params = 'id=' + id + '&action=get_rewards';
    params = params + '&order_by=' + rewards_order_by + ' ' + rewards_order;
    
    var uri = root + "/members/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading rewards.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">You have no rewards at the moment.</div>';
                
                $('rewards_count').set('html', '');
            } else {
                var ids = xml.getElementsByTagName('id');
                var job_ids = xml.getElementsByTagName('job_id');
                var referee_ids = xml.getElementsByTagName('referee_id');
                var industries = xml.getElementsByTagName('industry');
                var titles = xml.getElementsByTagName('title');
                var candidates = xml.getElementsByTagName('candidate');
                var employed_ons = xml.getElementsByTagName('formatted_employed_on');
                var work_commence_ons = xml.getElementsByTagName('formatted_work_commence_on');
                var rewards = xml.getElementsByTagName('total_reward');
                var currencies = xml.getElementsByTagName('currency');
                var paid_rewards = xml.getElementsByTagName('paid_reward');
                
                for (var i=0; i < ids.length; i++) {
                    var referral_id = ids[i].childNodes[0].nodeValue;
                    var job_id = job_ids[i].childNodes[0].nodeValue;
                    var referee_id = referee_ids[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ referral_id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="title">' + industries[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title"><a class="no_link" onClick="show_job_info(\'' + job_id + '\');">' + titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="title"><a href="candidates.php?id=' + referee_id + '&candidate=' + candidates[i].childNodes[0].nodeValue + '">' + candidates[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="date">' + employed_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + work_commence_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="reward">' + currencies[i].childNodes[0].nodeValue + '&nbsp;' + rewards[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="reward">' + currencies[i].childNodes[0].nodeValue + '&nbsp;' + paid_rewards[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
                
                $('rewards_count').set('html', ' (' + ids.length + ')');
            }
            
            $('div_rewards_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading rewards...');
        }
    });
    
    request.send(params);
}

function show_acknowledgements() {
    $('div_jobs').setStyle('display', 'none');
    $('div_rewards').setStyle('display', 'none');
    $('div_approvals').setStyle('display', 'none');
    $('div_acknowledgements').setStyle('display', 'block');
    $('li_jobs').setStyle('border', '1px solid #0000FF');
    $('li_rewards').setStyle('border', '1px solid #0000FF');
    $('li_approvals').setStyle('border', '1px solid #0000FF');
    $('li_acknowledgements').setStyle('border', '1px solid #CCCCCC');
    
    var params = 'id=' + id + '&action=get_acknowledgements';
    params = params + '&order_by=' + acknowledgements_order_by + ' ' + acknowledgements_order;
    
    var uri = root + "/members/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading acknowledgements.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no responses from the referred candidates.</div>';
                
                $('responses_count').set('html', '');
            } else {
                var ids = xml.getElementsByTagName('id');
                var job_ids = xml.getElementsByTagName('job_id');
                var referee_ids = xml.getElementsByTagName('referee_id');
                var employers = xml.getElementsByTagName('employer');
                var titles = xml.getElementsByTagName('title');
                var candidates = xml.getElementsByTagName('candidate');
                var referred_ons = xml.getElementsByTagName('formatted_referred_on');
                var acknowledged_ons = xml.getElementsByTagName('formatted_acknowledged_on');
                var rewards = xml.getElementsByTagName('potential_reward');
                var currencies = xml.getElementsByTagName('currency');
                
                for (var i=0; i < ids.length; i++) {
                    var referral_id = ids[i].childNodes[0].nodeValue;
                    var job_id = job_ids[i].childNodes[0].nodeValue;
                    var referee_id = referee_ids[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ referral_id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="title">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title"><a class="no_link" onClick="show_job_info(\'' + job_id + '\');">' + titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="title"><a href="candidates.php?id=' + referee_id + '&candidate=' + candidates[i].childNodes[0].nodeValue + '">' + candidates[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="date">' + referred_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + acknowledged_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="reward">' + currencies[i].childNodes[0].nodeValue + '&nbsp;' + rewards[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
                
                $('responses_count').set('html', ' (' + ids.length + ')');
            }
            
            $('div_acknowledgements_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading rewards...');
        }
    });
    
    request.send(params);
}

function count_items_and_show_tab() {
    var params = 'id=' + id + '&action=get_counts';
    
    var uri = root + "/members/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                return false;
            }
            
            var referrals = xml.getElementsByTagName('referrals');
            var approvals = xml.getElementsByTagName('approvals');
            var rewards = xml.getElementsByTagName('rewards');
            var responses = xml.getElementsByTagName('responses');
            
            if (referrals[0].childNodes[0].nodeValue > 0) {
                $('referred_count').set('html', ' (' + referrals[0].childNodes[0].nodeValue + ')');
            }
            
            if (approvals[0].childNodes[0].nodeValue > 0) {
                $('approval_count').set('html', ' (' + approvals[0].childNodes[0].nodeValue + ')');
            }
            
            //if (rewards[0].childNodes[0].nodeValue > 0) {
            //    $('rewards_count').set('html', ' (' + rewards[0].childNodes[0].nodeValue + ')');
            //}
            
            //if (responses[0].childNodes[0].nodeValue > 0) {
            //    $('responses_count').set('html', ' (' + responses[0].childNodes[0].nodeValue + ')');
            //}
            
            if (approvals[0].childNodes[0].nodeValue > 0 && referrals[0].childNodes[0].nodeValue <= 0) {
                show_approvals();
            } else {
                show_referred_jobs();
            }
        }
    });
    
    request.send(params);
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
        $('div_banner').tween('height', '175px');
        params = params + '&hide=0';
    }
    
    var uri = root + "/members/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post'
    });
    
    request.send(params);
}

function hide_show_banner() {
    var params = 'id=' + id + '&action=get_hide_banner';
    
    var uri = root + "/members/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post', 
        onSuccess: function(txt, xml) {
            if (txt == '1') {
                $('hide_show_label').set('html', 'Show');
                $('div_banner').setStyle('height', '15px');
            } else {
                $('hide_show_label').set('html', 'Hide');
                $('div_banner').setStyle('height', '175px');
            }
        }
    });
    
    request.send(params);
}

function set_mouse_events() {
    $('li_jobs').addEvent('mouseover', function() {
        $('li_jobs').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_jobs').addEvent('mouseout', function() {
        $('li_jobs').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    /*$('li_rewards').addEvent('mouseover', function() {
        $('li_rewards').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_rewards').addEvent('mouseout', function() {
        $('li_rewards').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });*/
    
    $('li_approvals').addEvent('mouseover', function() {
        $('li_approvals').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_approvals').addEvent('mouseout', function() {
        $('li_approvals').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    /*$('li_acknowledgements').addEvent('mouseover', function() {
        $('li_acknowledgements').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_acknowledgements').addEvent('mouseout', function() {
        $('li_acknowledgements').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });*/
}

function onDomReady() {
    set_root();
    get_employers_for_mini();
    get_industries_for_mini();
    set_mini_keywords();
    get_potential_rewards();
    get_job_count();
    get_referrals_count();
    get_requests_count();
    get_jobs_employed_count();
    set_mouse_events();
    
    hide_show_banner();
    
    $('li_jobs').addEvent('click', show_referred_jobs);
    //$('li_rewards').addEvent('click', show_rewards);
    $('li_approvals').addEvent('click', show_approvals);
    //$('li_acknowledgements').addEvent('click', show_acknowledgements);
    
    $('sort_jobs_industry').addEvent('click', function() {
        jobs_order_by = 'industry';
        jobs_ascending_or_descending();
        show_referred_jobs();
    });
    
    $('sort_jobs_employer').addEvent('click', function() {
        jobs_order_by = 'employer';
        jobs_ascending_or_descending();
        show_referred_jobs();
    });
    
    $('sort_jobs_title').addEvent('click', function() {
        jobs_order_by = 'title';
        jobs_ascending_or_descending();
        show_referred_jobs();
    });
    
    $('sort_jobs_referrer').addEvent('click', function() {
        jobs_order_by = 'referrer';
        jobs_ascending_or_descending();
        show_referred_jobs();
    });
    
    $('sort_jobs_referred_on').addEvent('click', function() {
        jobs_order_by = 'referrals.referred_on';
        jobs_ascending_or_descending();
        show_referred_jobs();
    });
    
    $('sort_approvals_member').addEvent('click', function() {
        approvals_order_by = 'member_name';
        approvals_ascending_or_descending();
        show_approvals();
    });
    
    $('sort_approvals_referred_on').addEvent('click', function() {
        approvals_order_by = 'member_referees.referred_on';
        approvals_ascending_or_descending();
        show_approvals();
    });
    
    $('sort_rewards_industry').addEvent('click', function() {
        rewards_order_by = 'industries.industry';
        rewards_ascending_or_descending();
        show_rewards();
    });
    
    /*$('sort_rewards_title').addEvent('click', function() {
        rewards_order_by = 'jobs.title';
        rewards_ascending_or_descending();
        show_rewards();
    });
    
    $('sort_rewards_candidate').addEvent('click', function() {
        rewards_order_by = 'candidate';
        rewards_ascending_or_descending();
        show_rewards();
    });
    
    $('sort_rewards_employed_on').addEvent('click', function() {
        rewards_order_by = 'referrals.employed_on';
        rewards_ascending_or_descending();
        show_rewards();
    });
    
    $('sort_rewards_work_commence_on').addEvent('click', function() {
        rewards_order_by = 'referrals.work_commence_on';
        rewards_ascending_or_descending();
        show_rewards();
    });
    
    $('sort_rewards_reward').addEvent('click', function() {
        rewards_order_by = 'referrals.total_reward';
        rewards_ascending_or_descending();
        show_rewards();
    });
    
    $('sort_rewards_paid').addEvent('click', function() {
        rewards_order_by = 'paid_reward';
        rewards_ascending_or_descending();
        show_rewards();
    });
    
    /*$('sort_acknowledgements_employer').addEvent('click', function() {
        acknowledgements_order_by = 'employers.employer';
        acknowledgements_ascending_or_descending();
        show_acknowledgements();
    });
    
    $('sort_acknowledgements_title').addEvent('click', function() {
        acknowledgements_order_by = 'jobs.title';
        acknowledgements_ascending_or_descending();
        show_acknowledgements();
    });
    
    $('sort_acknowledgements_candidate').addEvent('click', function() {
        acknowledgements_order_by = 'candidate';
        acknowledgements_ascending_or_descending();
        show_acknowledgements();
    });
    
    $('sort_acknowledgements_referred_on').addEvent('click', function() {
        acknowledgements_order_by = 'referrals.referred_on';
        acknowledgements_ascending_or_descending();
        show_acknowledgements();
    });
    
    $('sort_acknowledgements_acknowledged_on').addEvent('click', function() {
        acknowledgements_order_by = 'referrals.referee_acknowledged_on';
        acknowledgements_ascending_or_descending();
        show_acknowledgements();
    });
    
    $('sort_acknowledgements_reward').addEvent('click', function() {
        acknowledgements_order_by = 'jobs.potential_reward';
        acknowledgements_ascending_or_descending();
        show_acknowledgements();
    });*/
    
    count_items_and_show_tab();
    
    var suggest_url = root + '/common/php/search_suggest.php';
    new Autocompleter.Ajax.Json('mini_keywords', suggest_url, {
        'postVar': 'keywords',
        'minLength' : 1,
        'overflow' : true,
        'delay' : 50
    });
}

window.addEvent('domready', onDomReady);
