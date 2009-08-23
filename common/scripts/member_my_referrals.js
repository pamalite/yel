var order_by = 'referred_on';
var order = 'desc';
var rewarded_order_by = 'referrals.employed_on';
var rewarded_order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function rewarded_ascending_or_descending() {
    if (rewarded_order == 'desc') {
        rewarded_order = 'asc';
    } else {
        rewarded_order = 'desc';
    }
}

function display_total_reward_earned() {
    var params = 'id=' + id;
    params = params + '&action=get_total_rewards_earned';
    
    var uri = root + "/members/my_referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            $('div_total_rewards').setStyle('display', 'none');
            
            if (txt == 'ko') {
                set_status('An error occured while retrieving reward earned.');
            } 
            
            if (txt != '0') {
                var rewards = xml.getElementsByTagName('reward_earned');
                var currencies = xml.getElementsByTagName('currency');
                
                if (rewards.length > 0) {
                    var html = ''
                    
                    for (var i=0; i < rewards.length; i++) {
                        html = html + '<span style="font-weight: bold; text-decoration: underline;">' + currencies[i].childNodes[0].nodeValue + ' ' + rewards[i].childNodes[0].nodeValue + '</span>';
                        
                        if (i < rewards.length-1) {
                            html = html + '&nbsp;&nbsp;&nbsp;';
                        }

                        if (i % 4 == 0 && i < rewards.length-1 && i > 0) {
                            html = html + '<br/>';
                        }
                    }

                    $('rewards').set('html', html);
                    $('div_total_rewards').setStyle('display', 'block');
                }
            } 
            
            set_status('');
            return;
        },
        onRequest: function(instance) {
            set_status('Loading total rewards...');
        }
    });
    
    request.send(params);
}

function show_referrals() {
    $('div_pendings').setStyle('display', 'block');
    $('div_rewardeds').setStyle('display', 'none');
    
    $('li_pendings').setStyle('border', '1px solid #CCCCCC');
    $('li_rewardeds').setStyle('border', '1px solid #0000FF');
    
    var params = 'id=' + id;
    params = params + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/members/my_referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading referrals.');
                return false;
            }
            
            var has_referrals = false;
            var html = '<table id="list" class="list">';
            
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">You have no outstanding referrals at the moment.</div>';
            } else {
                var ids = xml.getElementsByTagName('id');
                var job_ids = xml.getElementsByTagName('job_id');
                var referee_ids = xml.getElementsByTagName('referee_id');
                var employers = xml.getElementsByTagName('employer');
                var titles = xml.getElementsByTagName('title');
                var candidates = xml.getElementsByTagName('candidate');
                var referred_ons = xml.getElementsByTagName('formatted_referred_on');
                var acknowledged_ons = xml.getElementsByTagName('formatted_acknowledged_on');
                var agreed_terms_ons = xml.getElementsByTagName('formatted_employer_agreed_terms_on');
                var rewards = xml.getElementsByTagName('potential_reward');
                var currencies = xml.getElementsByTagName('currency');

                for (var i=0; i < ids.length; i++) {
                    var referral_id = ids[i].childNodes[0].nodeValue;

                    if (referral_id == '0') {
                        referral_id = 'invited_' + i;
                    }

                    html = html + '<tr id="'+ referral_id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";

                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title"><a class="no_link" onClick="toggle_description(\'' + referral_id + '\')">' + titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="title">' + candidates[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + referred_ons[i].childNodes[0].nodeValue + '</td>' + "\n";

                    var acknowledged_on = '<span style="font-size: 9pt; color: #AAAAAA;">Pending...</span>';
                    if (acknowledged_ons[i].childNodes.length > 0) {
                        acknowledged_on = acknowledged_ons[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="date">' + acknowledged_on + '</td>' + "\n";

                    var agreed_terms_on = '<span style="font-size: 9pt; color: #AAAAAA;">Pending...</span>';
                    if (agreed_terms_ons[i].childNodes.length > 0) {
                        agreed_terms_on = agreed_terms_ons[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="date">' + agreed_terms_on + '</td>' + "\n";

                    html = html + '<td class="reward">' + currencies[i].childNodes[0].nodeValue + '&nbsp;' + rewards[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '</tr>' + "\n";

                    html = html + '<tr onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td colspan="7"><div class="description" id="desc_' + referral_id + '"></div></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
                
                has_referrals = true;
            }
            
            $('div_list').set('html', html);
            
            if (has_referrals) {
                var ids = xml.getElementsByTagName('id');
                var descriptions = xml.getElementsByTagName('description');
                
                for (var i=0; i < ids.length; i++) {
                    var referral_id = ids[i].childNodes[0].nodeValue;
                    var job_desc = '<div>' + descriptions[i].childNodes[0].nodeValue + '</div>';

                    if (referral_id == '0') {
                        referral_id = 'invited_' + i;
                    }

                    $('desc_' + referral_id).set('html', job_desc);
                }
            }
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading currently referrals...');
        }
    });
    
    request.send(params);
}

function show_rewardeds() {
    $('div_pendings').setStyle('display', 'none');
    $('div_rewardeds').setStyle('display', 'block');
    
    $('li_pendings').setStyle('border', '1px solid #0000FF');
    $('li_rewardeds').setStyle('border', '1px solid #CCCCCC');
    
    mark_all_rewards_viewed();
    
    var params = 'id=' + id + '&action=get_rewards';
    params = params + '&order_by=' + rewarded_order_by + ' ' + rewarded_order;
    
    var uri = root + "/members/my_referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) { 
            if (txt == 'ko') {
                set_status('An error occured while loading rewards.');
                return false;
            }
            
            var has_rewards = false;
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">You have no rewards at the moment.</div>';
                
                $('rewards_count').set('html', '');
            } else {
                var ids = xml.getElementsByTagName('id');
                var job_ids = xml.getElementsByTagName('job_id');
                var referee_ids = xml.getElementsByTagName('referee_id');
                var employers = xml.getElementsByTagName('employer');
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
                    html = html + '<td class="title">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title"><a class="no_link" onClick="toggle_rewarded_description(\'' + referral_id + '\');">' + titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="title"><a href="candidates.php?id=' + referee_id + '&candidate=' + candidates[i].childNodes[0].nodeValue + '">' + candidates[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="date">' + employed_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + work_commence_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="reward">' + currencies[i].childNodes[0].nodeValue + '&nbsp;' + rewards[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="reward">' + currencies[i].childNodes[0].nodeValue + '&nbsp;' + paid_rewards[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '</tr>' + "\n";
                    
                    html = html + '<tr onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td colspan="7"><div class="description" id="rewarded_desc_' + referral_id + '"></div></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
                
                has_rewards = true;
            }
            
            $('div_rewarded_list').set('html', html);
            
            if (has_rewards) {
                var ids = xml.getElementsByTagName('id');
                var descriptions = xml.getElementsByTagName('description');
                
                for (var i=0; i < ids.length; i++) {
                    var referral_id = ids[i].childNodes[0].nodeValue;
                    
                    $('rewarded_desc_' + referral_id).set('html', descriptions[i].childNodes[0].nodeValue);
                }
            }
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading rewards...');
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

function toggle_rewarded_description(job_id) {
    if ($('rewarded_desc_' + job_id).getStyle('display') == 'none') {
        $('rewarded_desc_' + job_id).setStyle('display', 'block');
    } else {
        $('rewarded_desc_' + job_id).setStyle('display', 'none');
    }
}

function mark_all_rewards_viewed() {
    if (rewards_count > 0) {
        var params = 'id=' + id + '&action=mark_all_rewards_viewed';

        var uri = root + "/members/my_referrals_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                var referrals_count = parseInt($('referrals_count').get('html').substr(1, $('referrals_count').get('html').length-2));
                referrals_count = referrals_count - rewards_count;
                rewards_count = 0;
                
                if (referrals_count > 0) {
                    var html = '(' + referrals_count + ')'
                    $('referrals_count').set('html', html);
                } else {
                    $('referrals_count').setStyle('display', 'none');
                }
                
                $('rewards_count').setStyle('display', 'none');
            }
        });

        request.send(params);
    }
}

function set_mouse_events() {
    $('li_pendings').addEvent('mouseover', function() {
        $('li_pendings').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_pendings').addEvent('mouseout', function() {
        $('li_pendings').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_rewardeds').addEvent('mouseover', function() {
        $('li_rewardeds').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_rewardeds').addEvent('mouseout', function() {
        $('li_rewardeds').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
}

function onDomReady() {
    set_root();
    get_employers_for_mini();
    get_industries_for_mini();
    get_referrals_count();
    get_requests_count();
    get_jobs_employed_count();
    set_mini_keywords();
    set_mouse_events();
    
    $('li_pendings').addEvent('click', show_referrals);
    $('li_rewardeds').addEvent('click', show_rewardeds);
    
    $('sort_employer').addEvent('click', function() {
        order_by = 'employer';
        ascending_or_descending();
        show_referrals();
    });
    
    $('sort_title').addEvent('click', function() {
        order_by = 'title';
        ascending_or_descending();
        show_referrals();
    });
     
    $('sort_acknowledged_on').addEvent('click', function() {
        order_by = 'referee_acknowledged_on';
        ascending_or_descending();
        show_referrals();
    });
    
    $('sort_candidate').addEvent('click', function() {
        order_by = 'candidate';
        ascending_or_descending();
        show_referrals();
    });
    
    $('sort_referred_on').addEvent('click', function() {
        order_by = 'referred_on';
        ascending_or_descending();
        show_referrals();
    });
    
    $('sort_employer_view_resume_on').addEvent('click', function() {
        order_by = 'employer_agreed_terms_on';
        ascending_or_descending();
        show_referrals();
    });
    
    $('sort_reward').addEvent('click', function() {
        order_by = 'potential_reward';
        ascending_or_descending();
        show_referrals();
    });
    
    $('sort_rewarded_employer').addEvent('click', function() {
        rewarded_order_by = 'employer';
        rewarded_ascending_or_descending();
        show_referrals();
    });
    
    $('sort_rewarded_title').addEvent('click', function() {
        rewarded_order_by = 'jobs.title';
        rewarded_ascending_or_descending();
        show_rewardeds();
    });
    
    $('sort_rewarded_candidate').addEvent('click', function() {
        rewarded_order_by = 'candidate';
        rewarded_ascending_or_descending();
        show_rewardeds();
    });
    
    $('sort_rewarded_employed_on').addEvent('click', function() {
        rewarded_order_by = 'referrals.employed_on';
        rewarded_ascending_or_descending();
        show_rewardeds();
    });
    
    $('sort_rewarded_work_commence_on').addEvent('click', function() {
        rewarded_order_by = 'referrals.work_commence_on';
        rewarded_ascending_or_descending();
        show_rewardeds();
    });
    
    $('sort_rewarded_reward').addEvent('click', function() {
        rewarded_order_by = 'referrals.total_reward';
        rewarded_ascending_or_descending();
        show_rewardeds();
    });
    
    $('sort_rewarded_paid').addEvent('click', function() {
        rewarded_order_by = 'paid_reward';
        rewarded_ascending_or_descending();
        show_rewardeds();
    });
    
    show_referrals();
    display_total_reward_earned();
}

window.addEvent('domready', onDomReady);
