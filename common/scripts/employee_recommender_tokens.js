var order_by = 'recommender_tokens.presented_on';
var order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function show_recommender_tokens() {
    var params = 'id=0&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employees/recommender_tokens_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading tokens.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no tokens presented to recommenders at the moment.</div>';
            } else {
                var email_addrs = xml.getElementsByTagName('email_addr');
                var recommenders = xml.getElementsByTagName('recommender');
                var phone_nums = xml.getElementsByTagName('phone_num');
                var tokens = xml.getElementsByTagName('token_presented');
                var presented_ons = xml.getElementsByTagName('formatted_presented_on');
                
                for (var i=0; i < email_addrs.length; i++) {
                    html = html + '<tr id="'+ i + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="date">' + presented_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    var phone_num = 'N/A';
                    if (phone_nums[i].childNodes.length > 0) {
                        phone_num = phone_nums[i].childNodes[0].nodeValue;
                    }
                    html = html + '<td class="recommender"><a href="mailto: ' + email_addrs[i].childNodes[0].nodeValue + '">' + recommenders[i].childNodes[0].nodeValue + '</a><br/><div class="phone_num"><strong>Tel:</strong> ' + phone_num + '<br/><strong>E-mail:</strong> ' + email_addrs[i].childNodes[0].nodeValue + '</div></td>' + "\n";
                    
                    html = html + '<td class="token">' + tokens[i].childNodes[0].nodeValue + '</td>' + "\n";
                    
                    html = html + '</tr>' + "\n";
                }
            }
            html = html + '</table>';
            
            $('div_tokens_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading tokens...');
        }
    });
    
    request.send(params);
}

function onDomReady() {
    initialize_page();
    get_unapproved_photos_count();
    get_employee_rewards_count();
    get_employee_tokens_count();
    
    $('sort_recommender').addEvent('click', function() {
        order_by = 'recommenders.lastname';
        ascending_or_descending();
        show_recommender_tokens();
    });
    
    $('sort_token').addEvent('click', function() {
        order_by = 'recommender_tokens.token';
        ascending_or_descending();
        show_recommender_tokens();
    });
    
    $('sort_presented_on').addEvent('click', function() {
        order_by = 'recommender_tokens.presented_on';
        ascending_or_descending();
        show_recommender_tokens();
    });
    
    show_recommender_tokens();
}

window.addEvent('domready', onDomReady);
