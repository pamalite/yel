var order_by = 'id';
var order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function close_photo_preview() {
    $('photo').set('src', '');
    $('div_photo_preview').setStyle('display', 'none');
    $('div_blanket').setStyle('display', 'none');
}

function show_photo_preview(_id, _email_addr, _name) {
    var html = '<input type="button" onClick="close_photo_preview();" value="Close" />&nbsp;';
    html = html + '<input type="button" onClick="approve_photo(\'' + _id + '\', \'' + _email_addr + '\');" value="Approve" />&nbsp;';
    html = html + '<input type="button" onClick="disapprove_photo(\'' + _id + '\', \'' + _email_addr + '\');" value="Disapprove" />';
    
    $('member_name').set('html', _name);
    $('photo').set('src', 'photos_action.php?id=' + _email_addr);
    $('buttons').set('html', html);
    $('div_blanket').setStyle('display', 'block');
    
    var window_height = 0;
    var window_width = 0;
    var div_height = parseInt($('div_photo_preview').getStyle('height'));
    var div_width = parseInt($('div_photo_preview').getStyle('width'));
    
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
    
    $('div_photo_preview').setStyle('top', ((window_height - div_height) / 2));
    $('div_photo_preview').setStyle('left', ((window_width - div_width) / 2));
    $('div_photo_preview').setStyle('display', 'block');
}

function show_photos() {
    get_unapproved_photos_count();
    
    var params = 'id=0&action=get_photos&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/employees/photos_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading photos.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no photos to be approved at the moment.</div>';
            } else {
                var ids = xml.getElementsByTagName('id');
                var hashes = xml.getElementsByTagName('photo_hash');
                var members = xml.getElementsByTagName('member');
                var emails = xml.getElementsByTagName('email_addr');
                
                for (var i=0; i < ids.length; i++) {
                    var id = ids[i].childNodes[0].nodeValue;
                    var hash = hashes[i].childNodes[0].nodeValue;
                    
                    html = html + '<tr id="'+ id + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    html = html + '<td class="photo_id">' + id + '.' + hash + '</td>' + "\n";
                    html = html + '<td class="member"><a class="no_link" onClick="show_photo_preview(\'' + id + '\', \'' + emails[i].childNodes[0].nodeValue + '\', \'' + add_slashes(members[i].childNodes[0].nodeValue) + '\');">' + members[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
            }
            html = html + '</table>';
            
            $('div_photos_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading photos...');
        }
    });
    
    request.send(params);
}

function approve_photo(_photo_id, _email_addr) {
    var is_ok = confirm('Are you sure to APPROVE this photo?');
    if (!is_ok) {
        return false;
    }
    
    var params = 'id=' + _photo_id + '&member=' + _email_addr;
    params = params + '&action=approve_photo';
    
    var uri = root + "/employees/photos_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while approving photo.');
                return false;
            }
            
            set_status('');
            close_photo_preview();
            show_photos();
        },
        onRequest: function(instance) {
            set_status('Approving photo...');
        }
    });
    
    request.send(params);
}

function disapprove_photo(_photo_id, _email_addr) {
    var is_ok = confirm('Are you sure to DISAPPROVE this photo?');
    if (!is_ok) {
        return false;
    }
    
    var params = 'id=' + _photo_id + '&member=' + _email_addr;
    params = params + '&action=disapprove_photo';
    
    var uri = root + "/employees/photos_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                alert('An error occured while disapproving photo.');
                return false;
            }
            
            set_status('');
            close_photo_preview();
            show_photos();
        },
        onRequest: function(instance) {
            set_status('Disapproving photo...');
        }
    });
    
    request.send(params);
}

function onDomReady() {
    set_root();
    get_employee_rewards_count();
    get_employee_tokens_count();
    
    $('sort_photo_id').addEvent('click', function() {
        order_by = 'id';
        ascending_or_descending();
        show_photos();
    });
    
    $('sort_member').addEvent('click', function() {
        order_by = 'member';
        ascending_or_descending();
        show_photos();
    });
    
    show_photos();
}

window.addEvent('domready', onDomReady);
