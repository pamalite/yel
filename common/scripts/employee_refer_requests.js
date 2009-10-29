var order_by = 'requested_on';
var order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function show_requests() {
    var params = 'id=0&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employees/refer_requests_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading requests.');
                return false;
            }
            
            var has_requests = false;
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no requests.</div>';
            } else {
                var ids = xml.getElementsByTagName('id');
                var emails = xml.getElementsByTagName('member');
                var jobs = xml.getElementsByTagName('job_title');
                var members = xml.getElementsByTagName('fullname');
                var phone_nums = xml.getElementsByTagName('phone_num');
                var requested_ons = xml.getElementsByTagName('formatted_requested_on');
                var resumes = xml.getElementsByTagName('resume');
                
                for (var i=0; i < ids.length; i++) {
                    var id = ids[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="member"><a href="mailto: ' + emails[i].childNodes[0].nodeValue + '">' + members[i].childNodes[0].nodeValue + '</a><br/><div class="phone_num"><strong>Tel:</strong> ' + phone_nums[i].childNodes[0].nodeValue + '<br/><strong>E-mail:</strong> ' + emails[i].childNodes[0].nodeValue + '</div></td>' + "\n";
                    html = html + '<td class="job"><a class="no_link" onClick="toggle_description(\'' + ids[i].childNodes[0].nodeValue + '\')">' + jobs[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="date">' + requested_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="actions"><a class="no_link" onClick="show_resume_page(\'' + resumes[i].childNodes[0].nodeValue + '\');">View Resume</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                    html = html + '<tr onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td colspan="5"><div class="description" id="desc_' + ids[i].childNodes[0].nodeValue + '"></div></td>' + "\n";
                    html = html + '</tr>';
                }
                
                has_requests = true;
            }
            html = html + '</table>';
            
            $('div_requests_list').set('html', html);
            
            if (has_requests) {
                var ids = xml.getElementsByTagName('id');
                var descriptions = xml.getElementsByTagName('description');
                
                for (var i=0; i < ids.length; i++) {
                    var id = ids[i].childNodes[0].nodeValue;
                    
                    $('desc_' + id).set('html', descriptions[i].childNodes[0].nodeValue);
                }
            }
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading requests...');
        }
    });
    
    request.send(params);
}

function show_resume_page(resume_id) {
    var popup = window.open('resume.php?id=' + resume_id, '', 'scrollbars');
    
    if (!popup) {
        alert('Popup blocker was detected. Please allow pop-up windows for YellowElevator.com and try again.');
    }
}

function toggle_description(request_id) {
    if ($('desc_' + request_id).getStyle('display') == 'none') {
        $('desc_' + request_id).setStyle('display', 'block');
    } else {
        $('desc_' + request_id).setStyle('display', 'none');
    }
}

function onDomReady() {
    set_root();
    get_unapproved_photos_count();
    get_employee_rewards_count();
    get_employee_tokens_count();
    
    $('sort_job').addEvent('click', function() {
        order_by = 'job_title';
        ascending_or_descending();
        show_requests();
    });
    
    $('sort_member').addEvent('click', function() {
        order_by = 'fullname';
        ascending_or_descending();
        show_requests();
    });
    
    $('sort_requested_on').addEvent('click', function() {
        order_by = 'requested_on';
        ascending_or_descending();
        show_requests();
    });
    
    show_requests();
}

window.addEvent('domready', onDomReady);
