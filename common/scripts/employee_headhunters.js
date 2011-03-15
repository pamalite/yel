var order_by = 'members.joined_on';
var order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function show_members() {
    var params = 'id=0&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employees/headhunters_action.php";
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
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no IRCs.</div>';
            } else {
                var ids = xml.getElementsByTagName('email_addr');
                var members = xml.getElementsByTagName('fullname');
                var joined_ons = xml.getElementsByTagName('formatted_joined_on');
                var phone_nums = xml.getElementsByTagName('phone_num');
                var countries = xml.getElementsByTagName('country');
                
                for (var i=0; i < ids.length; i++) {
                    var member_id = ids[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ member_id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="date">' + joined_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="member">' + members[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="email_addr"><a href="mailto: ' + member_id + '">' + member_id + '</a></td>' + "\n";
                    html = html + '<td class="phone">' + phone_nums[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="country">' + countries[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
            }
            html = html + '</table>';
            
            $('div_members_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading members...');
        }
    });
    
    request.send(params);
}

function onDomReady() {
    initialize_page();
    get_unapproved_photos_count();
    get_employee_rewards_count();
    get_employee_tokens_count();
    
    $('sort_email_addr').addEvent('click', function() {
        order_by = 'members.email_addr';
        ascending_or_descending();
        show_members();
    });
    
    $('sort_member').addEvent('click', function() {
        order_by = 'fullname';
        ascending_or_descending();
        show_members();
    });
    
    $('sort_joined_on').addEvent('click', function() {
        order_by = 'members.joined_on';
        ascending_or_descending();
        show_members();
    });
    
    $('sort_country').addEvent('click', function() {
        order_by = 'countries.country';
        ascending_or_descending();
        show_members();
    });
    
    show_members();
}

window.addEvent('domready', onDomReady);
