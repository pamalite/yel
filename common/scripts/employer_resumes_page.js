function toggle_job_description(_idx) {
    if ($('inline_job_desc_' + _idx).getStyle('display') == 'none') {
        $('inline_job_desc_' + _idx).setStyle('display', 'block');
    } else {
        $('inline_job_desc_' + _idx).setStyle('display', 'none');
    }
}

function onDomReady() {
    set_root();
}

window.addEvent('domready', onDomReady);
