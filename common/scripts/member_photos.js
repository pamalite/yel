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

function onDomReady() {
    set_root();
    get_employers_for_mini();
    get_industries_for_mini();
    set_mini_keywords();
    get_referrals_count();
    get_requests_count();
    get_jobs_employed_count();
    
    var suggest_url = root + '/common/php/search_suggest.php';
    new Autocompleter.Ajax.Json('mini_keywords', suggest_url, {
        'postVar': 'keywords',
        'minLength' : 1,
        'overflow' : true,
        'delay' : 50
    });
}

window.addEvent('domready', onDomReady);
