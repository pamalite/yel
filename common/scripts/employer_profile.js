function save_password() {
    if (isEmpty($('password').value) || isEmpty($('password2').value)) {
        alert('Password cannot be empty.');
        return;
    }
    
    if ($('password').value != $('password2').value) {
        alert('The passwords entered do not match.');
        return;
    }
    
    var params = 'id=' + id + '&password=' + md5($('password').value);
    
    var uri = root + "/employers/profile_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == 'ko') {
                alert('An error occured while trying to save password. Please try again later.');
                return false;
            }
            
            alert('Password saved successfully!');
            $('password').value = '';
            $('password2').value = '';    
        },
        onRequest: function(instance) {
            set_status('Saving password...');
        }
    });
    
    request.send(params);
}

function onDomReady() {
    initialize_page();
}

window.addEvent('domready', onDomReady);