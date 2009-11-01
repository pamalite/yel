var selected_tab = 'li_buffer';
var order_by = 'privileged_referral_buffers.referred_on';
var order = 'desc';
var in_process_order_by = 'referrals.referred_on';
var in_process_order = 'desc';
var employed_order_by = 'referrals.employed_on';
var employed_order = 'desc';
var rejected_order_by = 'referrals.referred_on';
var rejected_order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function in_process_ascending_or_descending() {
    if (in_process_order == 'desc') {
        in_process_order = 'asc';
    } else {
        in_process_order = 'desc';
    }
}

function employed_ascending_or_descending() {
    if (employed_order == 'desc') {
        employed_order = 'asc';
    } else {
        employed_order = 'desc';
    }
}

function rejected_ascending_or_descending() {
    if (rejected_order == 'desc') {
        rejected_order = 'asc';
    } else {
        rejected_order = 'desc';
    }
}

function show_resume_page(resume_id) {
    var popup = window.open('../employees/resume.php?id=' + resume_id, '', 'scrollbars');
    
    if (!popup) {
        alert('Popup blocker was detected. Please allow pop-up windows for YellowElevator.com and try again.');
    }
}

function show_buffers() {
    $('div_buffers').setStyle('display', 'block');
    $('div_in_process').setStyle('display', 'none');
    $('div_employeds').setStyle('display', 'none');
    $('div_rejecteds').setStyle('display', 'none');
    
    $('li_buffer').setStyle('border', '1px solid #CCCCCC');
    $('li_in_process').setStyle('border', '1px solid #0000FF');
    $('li_employed').setStyle('border', '1px solid #0000FF');
    $('li_rejected').setStyle('border', '1px solid #0000FF');
    
    var params = 'id=' + user_id + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/prs/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading buffered referrals.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no buffered referrals at the moment.</div>';
            } else {
                var candidate_emails = xml.getElementsByTagName('candidate_email');
                var phone_nums = xml.getElementsByTagName('phone_num');
                var candidates = xml.getElementsByTagName('candidate');
                var job_ids = xml.getElementsByTagName('id');
                var job_titles = xml.getElementsByTagName('title');
                var referred_ons = xml.getElementsByTagName('formatted_referred_on');
                var employers = xml.getElementsByTagName('employer');
                var industries = xml.getElementsByTagName('industry');
                var resumes = xml.getElementsByTagName('resume');
                var resume_ids = xml.getElementsByTagName('resume_id');
                
                for (var i=0; i < candidate_emails.length; i++) {
                    html = html + '<tr id="'+ i + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="date">' + referred_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title">' + job_titles[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title">' + industries[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    html = html + '<td class="title"><span style="font-weight: bold;">' + candidates[i].childNodes[0].nodeValue + '</span><br/><div class="phone_num"><strong>Tel:</strong> ' + phone_nums[i].childNodes[0].nodeValue + '<br/><strong>E-mail:</strong> ' + candidate_emails[i].childNodes[0].nodeValue + '</div></td>' + "\n";
                    
                    html = html + '<tr><td class="extras" colspan="5">';
                    html = html + '<a class="no_link" onClick="show_resume_page(' + resume_ids[i].childNodes[0].nodeValue + ');">' + resumes[i].childNodes[0].nodeValue + '</a>&nbsp;&bull;&nbsp;<a class="no_link" onClick="show_testimony(\'' + candidate_emails[i].childNodes[0].nodeValue + '\', ' + job_ids[i].childNodes[0].nodeValue + ');">Testimony</a></td>' + "\n";
                    html = html + '</td></tr>';
                    
                    html = html + '</tr>' + "\n";
                }
            }
            html = html + '</table>';
            
            $('div_buffer_list').set('html', html);
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading buffered referrals...');
        }
    });
    
    request.send(params);
}

function show_rejecteds() {
    $('div_buffers').setStyle('display', 'none');
    $('div_in_process').setStyle('display', 'none');
    $('div_employeds').setStyle('display', 'none');
    $('div_rejecteds').setStyle('display', 'block');
    
    $('li_buffer').setStyle('border', '1px solid #0000FF');
    $('li_in_process').setStyle('border', '1px solid #0000FF');
    $('li_employed').setStyle('border', '1px solid #0000FF');
    $('li_rejected').setStyle('border', '1px solid #CCCCCC');
    
    var params = 'id=' + user_id + '&action=get_rejected&order_by=' + rejected_order_by + ' ' + rejected_order;
    
    var uri = root + "/prs/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            // set_status('<pre>' + txt + '</pre>');
            // return;
            if (txt == 'ko') {
                set_status('An error occured while loading rejected referrals.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no rejected referrals at the moment.</div>';
            } else {
                var candidate_emails = xml.getElementsByTagName('candidate_email');
                var phone_nums = xml.getElementsByTagName('phone_num');
                var candidates = xml.getElementsByTagName('candidate');
                var job_ids = xml.getElementsByTagName('job_id');
                var job_titles = xml.getElementsByTagName('title');
                var referred_ons = xml.getElementsByTagName('formatted_referred_on');
                var employers = xml.getElementsByTagName('employer');
                var industries = xml.getElementsByTagName('industry');
                var resumes = xml.getElementsByTagName('resume');
                var resume_ids = xml.getElementsByTagName('resume_id');
                var referral_ids = xml.getElementsByTagName('id');
                
                for (var i=0; i < candidate_emails.length; i++) {
                    html = html + '<tr id="'+ i + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="date">' + referred_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title">' + job_titles[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title">' + industries[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    html = html + '<td class="title"><span style="font-weight: bold;">' + candidates[i].childNodes[0].nodeValue + '</span><br/><div class="phone_num"><strong>Tel:</strong> ' + phone_nums[i].childNodes[0].nodeValue + '<br/><strong>E-mail:</strong> ' + candidate_emails[i].childNodes[0].nodeValue + '</div></td>' + "\n";
                    
                    html = html + '<tr><td class="extras" colspan="5">';
                    html = html + '<a class="no_link" onClick="show_resume_page(' + resume_ids[i].childNodes[0].nodeValue + ');">' + resumes[i].childNodes[0].nodeValue + '</a>&nbsp;&bull;&nbsp;<a class="no_link" onClick="show_testimony(' + referral_ids[i].childNodes[0].nodeValue + ');">Testimony</a></td>' + "\n";
                    html = html + '</td></tr>';
                    
                    html = html + '</tr>' + "\n";
                }
            }
            html = html + '</table>';
            
            $('div_rejected_list').set('html', html);
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading buffered referrals...');
        }
    });
    
    request.send(params);
}

function show_in_process() {
    $('div_buffers').setStyle('display', 'none');
    $('div_in_process').setStyle('display', 'block');
    $('div_employeds').setStyle('display', 'none');
    $('div_rejecteds').setStyle('display', 'none');
    
    $('li_buffer').setStyle('border', '1px solid #0000FF');
    $('li_in_process').setStyle('border', '1px solid #CCCCCC');
    $('li_employed').setStyle('border', '1px solid #0000FF');
    $('li_rejected').setStyle('border', '1px solid #0000FF');
    
    var params = 'id=' + user_id + '&action=get_in_process&order_by=' + in_process_order_by + ' ' + in_process_order;
    
    var uri = root + "/prs/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            // set_status('<pre>' + txt + '</pre>');
            // return;
            if (txt == 'ko') {
                set_status('An error occured while loading in process referrals.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no in process referrals at the moment.</div>';
            } else {
                var candidate_emails = xml.getElementsByTagName('candidate_email');
                var phone_nums = xml.getElementsByTagName('phone_num');
                var candidates = xml.getElementsByTagName('candidate');
                var job_ids = xml.getElementsByTagName('job_id');
                var job_titles = xml.getElementsByTagName('title');
                var referred_ons = xml.getElementsByTagName('formatted_referred_on');
                var employers = xml.getElementsByTagName('employer');
                var industries = xml.getElementsByTagName('industry');
                var resumes = xml.getElementsByTagName('resume');
                var resume_ids = xml.getElementsByTagName('resume_id');
                var resume_viewed_ons = xml.getElementsByTagName('formatted_agreed_terms_on');
                var referral_ids = xml.getElementsByTagName('id');
                
                for (var i=0; i < candidate_emails.length; i++) {
                    html = html + '<tr id="'+ i + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="date">' + referred_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="employer">' + employers[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title">' + job_titles[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title">' + industries[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    html = html + '<td class="title"><span style="font-weight: bold;">' + candidates[i].childNodes[0].nodeValue + '</span><br/><div class="phone_num"><strong>Tel:</strong> ' + phone_nums[i].childNodes[0].nodeValue + '<br/><strong>E-mail:</strong> ' + candidate_emails[i].childNodes[0].nodeValue + '</div></td>' + "\n";
                    
                    var resume_viewed_on = '<span style="font-size: 7pt; color: #CCCCCC;">Pending...</span>';
                    if (resume_viewed_ons[i].childNodes.length > 0) {
                        resume_viewed_on = resume_viewed_ons[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="date">' + resume_viewed_on + '</td>' + "\n";
                    
                    html = html + '<tr><td class="extras" colspan="6">';
                    html = html + '<a class="no_link" onClick="show_resume_page(' + resume_ids[i].childNodes[0].nodeValue + ');"> ' + resumes[i].childNodes[0].nodeValue + '</a>&nbsp;&bull;&nbsp;<a class="no_link" onClick="show_testimony(' + referral_ids[i].childNodes[0].nodeValue + ');">Testimony</a></td>' + "\n";
                    html = html + '</td></tr>';
                    
                    html = html + '</tr>' + "\n";
                }
            }
            html = html + '</table>';
            
            $('div_in_process_list').set('html', html);
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading in process referrals...');
        }
    });
    
    request.send(params);
}

function close_testimony() {
    $('div_testimony').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_testimony() {
    $('testimony').set('html', '');
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_testimony').getStyle('height'));
    var div_width = parseInt($('div_testimony').getStyle('width'));
    
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
    
    $('div_testimony').setStyle('top', ((window_height - div_height) / 2));
    $('div_testimony').setStyle('left', ((window_width - div_width) / 2));
    
    var params = '';
    if (arguments.length == 2) {
        var referee = arguments[0];
        var job_id = arguments[1];
        
        params = 'id=' + user_id + '&referee=' + referee + '&job=' + job_id;
        params = params + '&action=get_testimony_from_buffer';
    } else {
        var resume_id = arguments[0];
        
        params = 'id=' + resume_id;
        params = params + '&action=get_testimony_from_referrals';
    }
    
    var uri = root + "/prs/referrals_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while retrieving testimony.');
                return;
            }
            
            var testimonies = xml.getElementsByTagName('testimony');
            var html = 'No testimony found.';
            if (testimonies[0].childNodes.length > 0) {
                html = testimonies[0].childNodes[0].nodeValue.replace(/\n/g, '<br/>');
            }
            $('testimony').set('html', html);
            $('div_blanket').setStyle('display', 'block');
            $('div_testimony').setStyle('display', 'block');
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading testimony...');
        }
    });
    
    request.send(params);
}

function set_mouse_events() {
    $('li_buffer').addEvent('mouseover', function() {
        $('li_buffer').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_buffer').addEvent('mouseout', function() {
        $('li_buffer').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_in_process').addEvent('mouseover', function() {
        $('li_in_process').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_in_process').addEvent('mouseout', function() {
        $('li_in_process').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_employed').addEvent('mouseover', function() {
        $('li_employed').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_employed').addEvent('mouseout', function() {
        $('li_employed').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_rejected').addEvent('mouseover', function() {
        $('li_rejected').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_rejected').addEvent('mouseout', function() {
        $('li_rejected').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
}

function onDomReady() {
    set_root();
    list_available_industries('0');
    set_mouse_events();
    
    $('li_buffer').addEvent('click', show_buffers);
    $('li_in_process').addEvent('click', show_in_process);
    // $('li_employed').addEvent('click', show_employeds);
    $('li_rejected').addEvent('click', show_rejecteds);
    
    // buffer sorters
    $('sort_referred_on').addEvent('click', function() {
        order_by = 'privileged_referral_buffers.referred_on';
        ascending_or_descending();
        show_buffers();
    });
    
    $('sort_employer').addEvent('click', function() {
        order_by = 'employer';
        ascending_or_descending();
        show_buffers();
    });
    
    $('sort_title').addEvent('click', function() {
        order_by = 'title';
        ascending_or_descending();
        show_buffers();
    });
    
    $('sort_industry').addEvent('click', function() {
        order_by = 'industry';
        ascending_or_descending();
        show_buffers();
    });
    
    $('sort_candidate').addEvent('click', function() {
        order_by = 'members.lastname';
        ascending_or_descending();
        show_buffers();
    });
    
    // in process sorters
    $('sort_in_process_referred_on').addEvent('click', function() {
        order_by = 'referrals.referred_on';
        in_process_ascending_or_descending();
        show_in_process();
    });
    
    $('sort_in_process_employer').addEvent('click', function() {
        order_by = 'employer';
        in_process_ascending_or_descending();
        show_in_process();
    });
    
    $('sort_in_process_title').addEvent('click', function() {
        order_by = 'title';
        in_process_ascending_or_descending();
        show_in_process();
    });
    
    $('sort_in_process_industry').addEvent('click', function() {
        order_by = 'referrals.industry';
        in_process_ascending_or_descending();
        show_in_process();
    });
    
    $('sort_in_process_candidate').addEvent('click', function() {
        order_by = 'members.lastname';
        in_process_ascending_or_descending();
        show_in_process();
    });
    
    $('sort_in_process_employer_view_resume_on').addEvent('click', function() {
        order_by = 'referrals.employer_agreed_terms_on';
        in_process_ascending_or_descending();
        show_in_process();
    });
    
    // // employed sorters
    // $('sort_employed_referred_on').addEvent('click', function() {
    //     order_by = 'referrals.referred_on';
    //     employed_ascending_or_descending();
    //     show_employeds();
    // });
    // 
    // $('sort_employed_employer').addEvent('click', function() {
    //     order_by = 'employer';
    //     employed_ascending_or_descending();
    //     show_employeds();
    // });
    // 
    // $('sort_employed_title').addEvent('click', function() {
    //     order_by = 'title';
    //     employed_ascending_or_descending();
    //     show_employeds();
    // });
    // 
    // $('sort_employed_industry').addEvent('click', function() {
    //     order_by = 'industry';
    //     employed_ascending_or_descending();
    //     show_employeds();
    // });
    // 
    // $('sort_employed_candidate').addEvent('click', function() {
    //     order_by = 'members.lastname';
    //     employed_ascending_or_descending();
    //     show_employeds();
    // });
    // 
    // $('sort_employed_recommender').addEvent('click', function() {
    //     order_by = 'recommenders.lastname';
    //     employed_ascending_or_descending();
    //     show_employeds();
    // });
    // 
    // $('sort_employed_employed_on').addEvent('click', function() {
    //     order_by = 'referrals.employed_on';
    //     employed_ascending_or_descending();
    //     show_employeds();
    // });
    // 
    // $('sort_employed_invoice').addEvent('click', function() {
    //     order_by = 'invoice';
    //     employed_ascending_or_descending();
    //     show_employeds();
    // });
    // 
    // $('sort_employed_guarantee_expire').addEvent('click', function() {
    //     order_by = 'guarantee_expire_in';
    //     employed_ascending_or_descending();
    //     show_employeds();
    // });
    
    // rejected sorters
    $('sort_rejected_referred_on').addEvent('click', function() {
        order_by = 'referrals.referred_on';
        rejected_ascending_or_descending();
        show_rejecteds();
    });
    
    $('sort_rejected_employer').addEvent('click', function() {
        order_by = 'employer';
        rejected_ascending_or_descending();
        show_rejecteds();
    });
    
    $('sort_rejected_title').addEvent('click', function() {
        order_by = 'title';
        rejected_ascending_or_descending();
        show_rejecteds();
    });
    
    $('sort_rejected_industry').addEvent('click', function() {
        order_by = 'industry';
        rejected_ascending_or_descending();
        show_rejecteds();
    });
    
    $('sort_rejected_candidate').addEvent('click', function() {
        order_by = 'members.lastname';
        rejected_ascending_or_descending();
        show_rejecteds();
    });
    
    show_buffers();
}

window.addEvent('domready', onDomReady);
