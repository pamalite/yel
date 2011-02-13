var order_by = 'referred_on';
var order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function sort_by(_table, _column) {
    order_by = _column;
    ascending_or_descending();
    show_recommendations();
}

function delete_buffered(_referral_id) {
    var is_ok = confirm('Are you sure to delete this recommendation?' + "\n\n" + 'This operation cannot be undone.');
    if (!is_ok) {
        return false;
    }
    
    var params = 'id=' + _referral_id + '&action=delete_buffered';
    
    var uri = root + "/members/recommendations_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == 'ko') {
                alert('An error occured while deleting recommendation.');
                return false;
            }
            
            show_recommendations();
        },
        onRequest: function(instance) {
            set_status('Deleting recommendation...');
        }
    });
    
    request.send(params);
}

function show_recommendations() {
    var params = 'id=' + id + '&action=get_recommendations';
    params = params + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/members/recommendations_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == 'ko') {
                alert('An error occured while loading recommendations.');
                return false;
            }
            
            if (txt == '0') {
               $('div_recommendations').set('html', '<div class="empty_results">No recommendations made.</div>');
            } else {
                // set_status('<pre>' + txt + '</pre>');
                // return;
                var tabs = xml.getElementsByTagName('tab');
                var ids = xml.getElementsByTagName('id');
                var job_ids = xml.getElementsByTagName('job_id');
                var jobs = xml.getElementsByTagName('job');
                var employers = xml.getElementsByTagName('employer');
                var referred_ons = xml.getElementsByTagName('formatted_referred_on');
                var candidates = xml.getElementsByTagName('candidate_name');
                var candidate_emails = xml.getElementsByTagName('candidate_email');
                
                var recommendations_table = new FlexTable('recommendations_table', 'recommendations');
                
                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'referred_on');\">Recommended On</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'job');\">Position</a>", '', 'header'));
                header.set(2, new Cell("<a class=\"sortable\" onClick=\"sort_by('referrals', 'candidate_name');\">Candidate</a>", '', 'header'));
                header.set(3, new Cell("&nbsp;", '', 'header actions'));
                recommendations_table.set(0, header);
                
                recommendations = new Array();
                for (var i=0; i < ids.length; i++) {
                    var row = new Row('');
                    
                    // referred on
                    row.set(0, new Cell(referred_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    
                    // position
                    var position = '<div class="candidate_name"><a href="../job/' + job_ids[i].childNodes[0].nodeValue + '">' + jobs[i].childNodes[0].nodeValue + '</a></div><br/>';
                    position = position + '<div class="small_contact"><span style="font-weight: bold;">Employer: </span>' + employers[i].childNodes[0].nodeValue + '</div>';
                    row.set(1, new Cell(position, '', 'cell'));
                    
                    // candidate
                    var candidate = '<div class="candidate_name">' + candidates[i].childNodes[0].nodeValue + '</div><br/>';
                    candidate = candidate + '<div class="small_contact"><span style="font-weight: bold;">E-mail:</span><a href="mailto:' + candidate_emails[i].childNodes[0].nodeValue + '">' + candidate_emails[i].childNodes[0].nodeValue + '</a></div>';
                    row.set(2, new Cell(candidate, '', 'cell'));
                    
                    // action
                    var action = 'Processed';
                    if (tabs[i].childNodes[0].nodeValue == 'buf') {
                        action = '<a class="no_link" onClick="delete_buffered(\'' + ids[i].childNodes[0].nodeValue + '\');">delete</a>';
                    }
                    row.set(3, new Cell(action, '', 'cell actions'));
                    
                    recommendations_table.set((parseInt(i)+1), row);
                }
                
                $('div_recommendations').set('html', recommendations_table.get_html());
            }
        },
        onRequest: function(instance) {
            set_status('Loading recommendations...');
        }
    });
    
    request.send(params);
}

function onDomReady() {}

function onLoaded() {
    initialize_page();
}

window.addEvent('domready', onDomReady);
window.addEvent('load', onLoaded);
