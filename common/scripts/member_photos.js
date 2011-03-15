function delete_photo(_id) {
    var confirmed = confirm('Are you sure to remove this photo?');
    if (!confirmed) {
        return false;
    }
    
    var params = 'id=' + _id + '&action=delete' + '&member=' + id;
    var uri = root + "/members/photos_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while removing photo.');
                return false;
            }
            
            set_status('');
            location.reload();
        },
        onRequest: function(instance) {
            set_status('Removing photo...');
        }
    });
    
    request.send(params);
}

function start_upload() {
    $('upload_progress').setStyle('display', 'block');
    set_status('Uploading photo...')
    return true;
}

function stop_upload(success) {
    var result = '';
    $('upload_progress').setStyle('display', 'none');
    if (success == 1) {
        set_status('');
        location.reload();
        return true;
    } else {
        set_status('An error occured while uploading your photo. Make sure your photo meets the conditions stated below.');
        return false;
    }
}

function toggle_banner() {
    var height = $('div_banner').getStyle('height');
    var params = 'id=' + id + '&action=set_hide_banner';
    
    if (parseInt(height) >= 100) {
        $('hide_show_label').set('html', 'Show');
        $('div_banner').tween('height', '15px');
        params = params + '&hide=1';
    } else {
        $('hide_show_label').set('html', 'Hide');
        $('div_banner').tween('height', '230px');
        params = params + '&hide=0';
    }
    
    var uri = root + "/members/photos_action.php";
    var request = new Request({
        url: uri,
        method: 'post'
    });
    
    request.send(params);
}

function hide_show_banner() {
    var params = 'id=' + id + '&action=get_hide_banner';
    
    var uri = root + "/members/photos_action.php";
    var request = new Request({
        url: uri,
        method: 'post', 
        onSuccess: function(txt, xml) {
            if (txt == '1') {
                $('hide_show_label').set('html', 'Show');
                $('div_banner').setStyle('height', '15px');
            } else {
                $('hide_show_label').set('html', 'Hide');
                $('div_banner').setStyle('height', '230px');
            }
        }
    });
    
    request.send(params);
}

function onDomReady() {
    initialize_page();
    get_employers_for_mini();
    get_industries_for_mini();
    set_mini_keywords();
    get_referrals_count();
    get_requests_count();
    get_jobs_employed_count();
    
    hide_show_banner();
    
    var suggest_url = root + '/common/php/search_suggest.php';
    new Autocompleter.Ajax.Json('mini_keywords', suggest_url, {
        'postVar': 'keywords',
        'minLength' : 1,
        'overflow' : true,
        'delay' : 50
    });
}

window.addEvent('domready', onDomReady);
