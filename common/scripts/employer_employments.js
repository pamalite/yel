var order_by = 'employed_on';
var order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function show_resume_page(resume_id) {
    var popup = window.open('resume.php?id=' + resume_id, '', 'scrollbars');
    
    if (!popup) {
        alert('Popup blocker was detected. Please allow pop-up windows for YellowElevator.com and try again.');
    }
}

function show_employed_jobs() {
    var params = 'id=' + id;
    params = params + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employers/employments_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading employed jobs.');
                return false;
            }
            
            var has_employments = false;
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no employments at the moment.</div>';
            } else {
                var ids = xml.getElementsByTagName('id');
                var industries = xml.getElementsByTagName('industry');
                var titles = xml.getElementsByTagName('title');
                var employed_ons = xml.getElementsByTagName('formatted_employed_on');
                var commence_ons = xml.getElementsByTagName('formatted_work_commence_on');
                var candidates = xml.getElementsByTagName('candidate');
                var resumes = xml.getElementsByTagName('resume');

                for (var i=0; i < ids.length; i++) {
                    var referral_id = ids[i];

                    html = html + '<tr id="'+ referral_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";

                    html = html + '<td class="industry">' + industries[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title"><a class="no_link" onClick="toggle_description(\'' + referral_id.childNodes[0].nodeValue + '\')">' + titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";

                    var view_resume_link = '<span style="text-decoration: line-through;">Resume</span>';
                    if (resumes[i].childNodes.length > 0) {
                        view_resume_link = '<a class="no_link" onClick="show_resume_page(\'' + resumes[i].childNodes[0].nodeValue + '\', \'' + referral_id.childNodes[0].nodeValue + '\')">' + candidates[i].childNodes[0].nodeValue + '</a>'
                    }

                    html = html + '<td class="title">' + view_resume_link + '</td>' + "\n";
                    html = html + '<td class="date">' + employed_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + commence_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '</tr>' + "\n";
                    html = html + '<tr onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td colspan="5"><div class="description" id="desc_' + referral_id.childNodes[0].nodeValue + '"></div></td>' + "\n";
                    html = html + '</tr>';
                }
                html = html + '</table>';
                
                has_employments = true;
            }
            
            $('div_list').set('html', html);
            
            if (has_employments) {
                var ids = xml.getElementsByTagName('id');
                var descriptions = xml.getElementsByTagName('description');
                
                for (var i=0; i < ids.length; i++) {
                    var referral_id = ids[i].childNodes[0].nodeValue;

                    $('desc_' + referral_id).set('html', descriptions[i].childNodes[0].nodeValue);
                }
            }
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading currently employed jobs...');
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

function onDomReady() {
    initialize_page();
    get_employer_referrals_count();
    
    $('sort_industry').addEvent('click', function() {
        order_by = 'industry';
        ascending_or_descending();
        show_employed_jobs();
    });
    
    $('sort_title').addEvent('click', function() {
        order_by = 'title';
        ascending_or_descending();
        show_employed_jobs();
    });
    
    $('sort_employed_on').addEvent('click', function() {
        order_by = 'employed_on';
        ascending_or_descending();
        show_employed_jobs();
    });
    
    $('sort_commence_on').addEvent('click', function() {
        order_by = 'work_commence_on';
        ascending_or_descending();
        show_employed_jobs();
    });
    
    $('sort_candidate').addEvent('click', function() {
        order_by = 'candidate';
        ascending_or_descending();
        show_employed_jobs();
    });
    
    show_employed_jobs();
}

window.addEvent('domready', onDomReady);
