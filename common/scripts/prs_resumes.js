var selected_tab = 'li_profile';
var order_by = 'members.joined_on';
var order = 'desc';
var filter = '0';
var resumes_order_by = 'modified_on';
var resumes_order = 'desc';

var current_member_email_addr = '';
var current_member_name = '';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function resumes_ascending_or_descending() {
    if (resumes_order == 'desc') {
        resumes_order = 'asc';
    } else {
        resumes_order = 'desc';
    }
}

function update_filter() {
    var params = 'id=0&action=get_filters';
    
    var uri = root + "/prs/resumes_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            var ids = xml.getElementsByTagName('id');
            var industries = xml.getElementsByTagName('industry');
            
            var html = '<select id="candidate_filter" name="candidate_filter" onChange="refresh_candidates();">' + "\n";
            html = html + '<option value="0">all specializations</option>' + "\n";
            html = html + '<option value="-1" disabled>&nbsp;</option>' + "\n";
            
            for (var i=0; i < ids.length; i++) {
                var id = ids[i].childNodes[0].nodeValue;
                var industry = industries[i].childNodes[0].nodeValue;
                
                if (id == filter) {
                    html = html + '<option value="'+ id + '" selected>' + industry + '</option>' + "\n";
                } else {
                    html = html + '<option value="'+ id + '">' + industry + '</option>' + "\n";
                }
            }
            
            html = html + '</select>' + "\n";
            
            $('candidate_filters_dropdown').set('html', html);
        },
        onRequest: function(instance) {
            set_status('Loading specilizations...');
        }
    });
    
    request.send(params);
}

function refresh_candidates() {
    filter = $('candidate_filter').options[$('candidate_filter').selectedIndex].value;
    show_candidates();
}

function show_candidates() {
    $('div_candidates').setStyle('display', 'block');
    $('div_candidate').setStyle('display', 'none');
    
    var params = 'id=' + id + '&order_by=' + order_by + ' ' + order;
    params = params + '&filter_by=' + filter;
    
    update_filter();
    
    var uri = root + "/prs/resumes_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading candidates.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no candidates at the moment.</div>';
            } else {
                var email_addrs = xml.getElementsByTagName('email_addr');
                var members = xml.getElementsByTagName('member_name');
                var phone_nums = xml.getElementsByTagName('phone_num');
                var joined_ons = xml.getElementsByTagName('formatted_joined_on');
                
                for (var i=0; i < email_addrs.length; i++) {
                    var id = email_addrs[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="date">' + joined_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="candidate"><a href="mailto: ' + id + '">' + members[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="contact_data">' + phone_nums[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="contact_data">' + id + '</td>' + "\n";
                    html = html + '<td class="actions"><a class="no_link" onClick="show_profile(\'' + id + '\');">View Profile &amp; Resumes</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
            }
            html = html + '</table>';
            
            $('div_candidates_list').set('html', html);
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading candidates...');
        }
    });
    
    request.send(params);
}

function show_resume_page(resume_id) {
    var popup = window.open('../employees/resume.php?id=' + resume_id, '', 'scrollbars');
    
    if (!popup) {
        alert('Popup blocker was detected. Please allow pop-up windows for YellowElevator.com and try again.');
    }
}

function show_current_candidate_profile() {
    show_profile(current_member_email_addr);
}

function show_profile(_member_email_addr) {
    current_member_email_addr = _member_email_addr;
    
    $('div_candidates').setStyle('display', 'none');
    $('div_candidate').setStyle('display', 'block');
    $('div_new_member_form').setStyle('display', 'none');
    $('div_upload_resume_form').setStyle('display', 'none');
    
    $('li_profile').setStyle('border', '1px solid #CCCCCC');
    $('li_resumes').setStyle('border', '1px solid #0000FF');
    $('div_profile').setStyle('display', 'block');
    $('div_resumes').setStyle('display', 'none');
    
    
    var params = 'id=' + current_member_email_addr + '&action=get_profile';
    
    var uri = root + "/prs/resumes_privileged_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading candidate.');
                return false;
            } 
            
            var member_firstname = xml.getElementsByTagName('member_firstname');
            var member_lastname = xml.getElementsByTagName('member_lastname');
            var recommender_firstname = xml.getElementsByTagName('recommender_firstname');
            var recommender_lastname = xml.getElementsByTagName('recommender_lastname');
            var member_phone_num = xml.getElementsByTagName('member_phone_num');
            var country = xml.getElementsByTagName('country');
            var zip = xml.getElementsByTagName('zip');
            var recommender_email_addr = xml.getElementsByTagName('recommender_email_addr');
            var recommender_phone_num = xml.getElementsByTagName('recommender_phone_num');
            var member_joined_on = xml.getElementsByTagName('formatted_joined_on');
            
            $('profile.joined_on').set('html', member_joined_on[0].childNodes[0].nodeValue);
            $('profile.firstname').set('html', member_firstname[0].childNodes[0].nodeValue);
            $('profile.lastname').set('html', member_lastname[0].childNodes[0].nodeValue);
            
            current_member_name = member_firstname[0].childNodes[0].nodeValue + ', ' + member_lastname[0].childNodes[0].nodeValue;
            
            $('profile.email_addr').set('html', current_member_email_addr);
            $('profile.phone_num').set('html', member_phone_num[0].childNodes[0].nodeValue);
            $('profile.country').set('html', country[0].childNodes[0].nodeValue);
            
            var zip_code = 'N/A';
            if (zip[0].childNodes.length > 0) {
                zip_code = zip[0].childNodes[0].nodeValue;
            }
            $('profile.zip').set('html', zip_code);
            
            $('profile.recommender.firstname').set('html', recommender_firstname[0].childNodes[0].nodeValue);
            $('profile.recommender.lastname').set('html', recommender_lastname[0].childNodes[0].nodeValue);
            $('profile.recommender.email_addr').set('html', recommender_email_addr[0].childNodes[0].nodeValue);
            
            var phone_num = 'N/A';
            if (recommender_phone_num[0].childNodes.length > 0) {
                phone_num = recommender_phone_num[0].childNodes[0].nodeValue;
            }
            $('profile.recommender.phone_num').set('html', phone_num);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading candidate...');
        }
    });
    
    request.send(params);
}

function show_current_candidate_resumes() {
    show_resumes(current_member_email_addr);
}

function show_resumes(_member_email_addr) {
    current_member_email_addr = _member_email_addr;
    
    $('div_candidates').setStyle('display', 'none');
    $('div_candidate').setStyle('display', 'block');
    $('div_new_member_form').setStyle('display', 'none');
    $('div_upload_resume_form').setStyle('display', 'none');
    
    $('li_resumes').setStyle('border', '1px solid #CCCCCC');
    $('li_profile').setStyle('border', '1px solid #0000FF');
    $('div_profile').setStyle('display', 'none');
    $('div_resumes').setStyle('display', 'block');
    
    var params = 'id=' + current_member_email_addr + '&action=get_resumes';
    params = params + '&order_by=' + resumes_order_by + ' ' + resumes_order;
    
    var uri = root + "/prs/resumes_privileged_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading resumes.');
                return false;
            } 
            
            var ids = xml.getElementsByTagName('id');
            var privates = xml.getElementsByTagName('private');
            var labels = xml.getElementsByTagName('name');
            var modified_ons = xml.getElementsByTagName('modified_date');
            var file_hashes = xml.getElementsByTagName('file_hash');
            var file_names = xml.getElementsByTagName('file_name');
            
            var html = '<table id="list" class="list">';
            if (ids.length <= 0) {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">Please click on the \"Upload Resume\" to upload resume.</div>';
            } else {
                for (var i=0; i < ids.length; i++) {
                    var resume_id = ids[i];
                    
                    html = html + '<tr id="'+ resume_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    
                    if (privates[i].childNodes[0].nodeValue == 'N') {
                        html = html + '<td class="private">&nbsp;</td>' + "\n";
                    } else {
                        html = html + '<td class="private">Private</td>' + "\n";
                    }
                    
                    html = html + '<td class="date">' + modified_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    if (file_hashes[i].childNodes.length > 0) {
                        html = html + '<td class="title"><span class="reupload"><a class="no_link" onClick="show_upload_resume_form(' + resume_id.childNodes[0].nodeValue + ');">Update File</a></span>&nbsp;<a href="resume.php?id=' + resume_id.childNodes[0].nodeValue + '&member=' + current_member_email_addr + '">' + labels[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    } else {
                        html = html + '<td class="title"><a class="no_link" onClick="show_resume_page(\'' + resume_id.childNodes[0].nodeValue + '\')">' + labels[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    }
                    
                    html = html + '<td class="actions"><a class="no_link" onClick="show_refer_now_form(\'' + resume_id.childNodes[0].nodeValue + '\')">Refer Now</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_resumes_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading resumes...');
        }
    });
    
    request.send(params);
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
    
    $('li_resumes').addEvent('mouseover', function() {
        $('li_resumes').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_resumes').addEvent('mouseout', function() {
        $('li_resumes').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
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
}

function onDomReady() {
    set_root();
    set_mouse_events();
    
    $('li_back').addEvent('click', show_candidates);
    $('li_profile').addEvent('click', show_current_candidate_profile);
    $('li_resumes').addEvent('click', show_current_candidate_resumes);
    
    $('sort_joined_on').addEvent('click', function() {
        order_by = 'members.joined_on';
        ascending_or_descending();
        show_candidates();
    });
    
    $('sort_candidate').addEvent('click', function() {
        order_by = 'members.lastname';
        ascending_or_descending();
        show_candidates();
    });
    
    $('sort_resumes_label').addEvent('click', function() {
        resumes_order_by = 'name';
        resumes_ascending_or_descending();
        show_current_candidate_resumes();
    });
    
    $('sort_resumes_modified_on').addEvent('click', function() {
        resumes_order_by = 'modified_on';
        ascending_or_descending();
        show_current_candidate_resumes();
    });
    
    show_candidates();
}

window.addEvent('domready', onDomReady);
