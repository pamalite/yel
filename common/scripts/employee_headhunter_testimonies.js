var order_by = 'referrals.referred_on';
var order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function show_testimonies() {
    var params = 'id=0&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employees/headhunter_testimonies_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading members.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no testimonies from IRCs yet.</div>';
            } else {
                var ids = xml.getElementsByTagName('id');
                var members = xml.getElementsByTagName('member');
                var member_email_addrs = xml.getElementsByTagName('member_email_addr');
                var member_phone_nums = xml.getElementsByTagName('member_phone_num');
                var referees = xml.getElementsByTagName('referee');
                var referee_email_addrs = xml.getElementsByTagName('referee_email_addr');
                var referee_phone_nums = xml.getElementsByTagName('referee_phone_num');
                var referred_ons = xml.getElementsByTagName('formatted_referred_on');
                var jobs = xml.getElementsByTagName('job_title');
                var job_ids = xml.getElementsByTagName('job_id');
                
                for (var i=0; i < ids.length; i++) {
                    var referral_id = ids[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ referral_id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="date">' + referred_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="member"><a href="mailto: ' + member_email_addrs[i].childNodes[0].nodeValue + '">' + members[i].childNodes[0].nodeValue + '</a><br/><div class="phone_num"><strong>Tel:</strong> ' + member_phone_nums[i].childNodes[0].nodeValue + '<br/><strong>E-mail:</strong> ' + member_email_addrs[i].childNodes[0].nodeValue + '</div></td>' + "\n";
                    html = html + '<td class="referee"><a href="mailto: ' + referee_email_addrs[i].childNodes[0].nodeValue + '">' + referees[i].childNodes[0].nodeValue + '</a><br/><div class="phone_num"><strong>Tel:</strong> ' + referee_phone_nums[i].childNodes[0].nodeValue + '<br/><strong>E-mail:</strong> ' + referee_email_addrs[i].childNodes[0].nodeValue + '</div></td>' + "\n";
                    html = html + '<td class="job"><a href="../job/' + job_ids[i].childNodes[0].nodeValue + '">' + jobs[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="testimony"><a class="no_link" onClick="show_testimony_form(' + referral_id + ');">View</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
            }
            html = html + '</table>';
            
            $('div_testimonies_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading testimonies...');
        }
    });
    
    request.send(params);
}

function close_testimony_form() {
    $('referral_id').value = '';
    $('div_testimony_form').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_testimony_form(_referral_id) {
    $('referral_id').value = _referral_id;
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_testimony_form').getStyle('height'));
    var div_width = parseInt($('div_testimony_form').getStyle('width'));
    
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
    
    $('div_testimony_form').setStyle('top', ((window_height - div_height) / 2));
    $('div_testimony_form').setStyle('left', ((window_width - div_width) / 2));
    
    var params = 'id=' + _referral_id;
    params = params + '&action=get_testimony';
    
    var uri = root + "/employees/headhunter_testimonies_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while retrieving testimony.');
                return;
            }
            
            var member = xml.getElementsByTagName('member');
            var referee = xml.getElementsByTagName('referee');
            var job = xml.getElementsByTagName('job_title');
            var testimony = xml.getElementsByTagName('testimony_texts');
            
            $('member').set('html', member[0].childNodes[0].nodeValue);
            $('referee').set('html', referee[0].childNodes[0].nodeValue);
            $('job').set('html', job[0].childNodes[0].nodeValue);
            
            var testimony_texts = '';
            if (testimony[0].childNodes.length > 0) {
                testimony_texts = testimony[0].childNodes[0].nodeValue.replace('&lt;br/&gt;' , '<br/>');
                testimony_texts = testimony_texts.replace(/<br\/>/g, "\n");
            }
            $('testimony').value = testimony_texts;
            
            $('div_blanket').setStyle('display', 'block');
            $('div_testimony_form').setStyle('display', 'block');
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading testimony...');
        }
    });
    
    request.send(params);
}

function approve_testimony() {
    if (isEmpty($('testimony').value)) {
        alert('Testimony cannot be empty!');
        return false;
    }
    
    var testimony_texts = $('testimony').value.replace(/\n/g, '<br/>');
    
    var params = 'id=' + $('referral_id').value;
    params = params + '&action=approve_testimony';
    params = params + '&testimony=' + testimony_texts;
    
    var uri = root + "/employees/headhunter_testimonies_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while approving testimony.');
                return;
            }
            
            close_testimony_form();
            set_status('');
            show_testimonies();
        },
        onRequest: function(instance) {
            set_status('Approving testimony...');
        }
    });
    
    request.send(params);
}

function onDomReady() {
    set_root();
    get_unapproved_photos_count();
    get_employee_rewards_count();
    get_employee_tokens_count();
    
    $('sort_referee').addEvent('click', function() {
        order_by = 'referees.lastname';
        ascending_or_descending();
        show_testimonies();
    });
    
    $('sort_member').addEvent('click', function() {
        order_by = 'members.lastname';
        ascending_or_descending();
        show_testimonies();
    });
    
    $('sort_referred_on').addEvent('click', function() {
        order_by = 'referrals.referred_on';
        ascending_or_descending();
        show_testimonies();
    });
    
    $('sort_job').addEvent('click', function() {
        order_by = 'jobs.title';
        ascending_or_descending();
        show_testimonies();
    });
    
    show_testimonies();
}

window.addEvent('domready', onDomReady);
