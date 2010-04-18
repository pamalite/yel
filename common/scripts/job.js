function close_refer_popup(_proceed_refer) {
    if (_proceed_refer) {
        
    }
    close_window('refer_window');
}

function show_refer_popup() {
    show_window('refer_window');
    //window.scrollTo(0, 0);
}

function close_apply_popup(_proceed_refer) {
    if (_proceed_refer) {
        
    }
    close_window('apply_window');
}

function show_apply_popup() {
    show_window('apply_window');
    //window.scrollTo(0, 0);
}

function onDomReady() {
    initialize_page();
    
    if (!isEmpty(show_popup)) {
        if (show_popup == 'refer') {
            show_refer_popup();
        } else if (show_popup == 'apply') {
            show_apply_popup();
        }
    }
}

window.addEvent('domready', onDomReady);
