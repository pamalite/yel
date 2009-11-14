var order_by = 'ucr.added_on';
var order = 'desc';

var current_candidate_email = '';
var current_referrer_email = '';
var current_job_id = 0;

// Job class for easy storage
// function Job(_id, _title, _employer, _industry, _currency, _salary, _description) {
//     this.id = _id;
//     this.title = _title;
//     this.employer = _employer;
//     this.industry = _industry;
//     this.currency = _currency;
//     this.salary = _salary;
//     this.description = _description;
// }
// var available_jobs = new Array();
// var jobs_list = new ListBox('jobs', 'jobs_list', false);
// var jobs_filter_by = '0';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function show_uploaded_resumes() {
    var params = 'id=' + id + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/prs/resumes_uploaded_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading resumes.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no users contributed resumes at the moment.</div>';
            } else {
                var job_ids = xml.getElementsByTagName('job_id');
                var job_titles = xml.getElementsByTagName('job');
                var candidate_email_addrs = xml.getElementsByTagName('candidate_email_addr');
                var candidate_names = xml.getElementsByTagName('candidate');
                var candidate_phone_nums = xml.getElementsByTagName('candidate_phone_num');
                var candidate_zips = xml.getElementsByTagName('candidate_zip');
                var candidate_countries = xml.getElementsByTagName('candidate_country');
                var referrer_email_addrs = xml.getElementsByTagName('referrer_email_addr');
                var referrer_names = xml.getElementsByTagName('referrer');
                var referrer_phone_nums = xml.getElementsByTagName('referrer_phone_num');
                var referrer_zips = xml.getElementsByTagName('referrer_zip');
                var referrer_countries = xml.getElementsByTagName('referrer_country');
                var added_ons = xml.getElementsByTagName('formatted_added_on');
                var file_names = xml.getElementsByTagName('resume_label');
                
                for (var i=0; i < job_ids.length; i++) {
                    html = html + '<tr id="'+ i + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="date">' + added_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="candidate"><a href="mailto: ' + candidate_email_addrs[i].childNodes[0].nodeValue + '">' + candidate_names[i].childNodes[0].nodeValue + '</a><br/><div class="phone_num"><strong>Tel:</strong> ' + candidate_phone_nums[i].childNodes[0].nodeValue + '<br/><strong>E-mail:</strong> ' + candidate_email_addrs[i].childNodes[0].nodeValue + '<br/><strong>Postcode:</strong> ' + candidate_zips[i].childNodes[0].nodeValue + '<br/><strong>Country:</strong> ' + candidate_countries[i].childNodes[0].nodeValue + '</div></td>' + "\n";
                    html = html + '<td class="candidate"><a href="mailto: ' + referrer_email_addrs[i].childNodes[0].nodeValue + '">' + referrer_names[i].childNodes[0].nodeValue + '</a><br/><div class="phone_num"><strong>Tel:</strong> ' + referrer_phone_nums[i].childNodes[0].nodeValue + '<br/><strong>E-mail:</strong> ' + referrer_email_addrs[i].childNodes[0].nodeValue + '<br/><strong>Postcode:</strong> ' + referrer_zips[i].childNodes[0].nodeValue + '<br/><strong>Country:</strong> ' + referrer_countries[i].childNodes[0].nodeValue + '</div></td>' + "\n";
                    html = html + '<td class="resume"><a href="../employees/resume.php?job_id=' + job_ids[i].childNodes[0].nodeValue + '&candidate_email=' + candidate_email_addrs[i].childNodes[0].nodeValue + '&referrer_email=' + referrer_email_addrs[i].childNodes[0].nodeValue + '">' + file_names[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="job"><a href="' + root + '/job/' + job_ids[i].childNodes[0].nodeValue + '">' + job_titles[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '<td class="actions"><a class="no_link" onClick="">Add To Privileged</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
            }
            html = html + '</table>';
            
            $('div_candidates_list').set('html', html);
            
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading resumes...');
        }
    });
    
    request.send(params);
}

function onDomReady() {
    set_root();
    list_available_industries('0');
    
    $('sort_added_on').addEvent('click', function() {
        order_by = 'ucr.added_on';
        ascending_or_descending();
        show_uploaded_resumes();
    });
    
    $('sort_candidate').addEvent('click', function() {
        order_by = 'ucr.candidate_lastname';
        ascending_or_descending();
        show_uploaded_resumes();
    });
    
    $('sort_referrer').addEvent('click', function() {
        order_by = 'ucr.referrer_firstname';
        ascending_or_descending();
        show_uploaded_resumes();
    });
    
    $('sort_job').addEvent('click', function() {
        order_by = 'jobs.title';
        ascending_or_descending();
        show_uploaded_resumes();
    });
    
    show_uploaded_resumes();
}

window.addEvent('domready', onDomReady);
