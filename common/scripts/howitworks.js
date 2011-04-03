var howitworks_slider = '';
var howitworks_state = false;
var howitworks_recommender = '';
var howitworks_candidate = '';

function initialize_howitworks() {
    howitworks_slider = new Fx.Slide('howitworks', {
        mode: 'vertical'
    });
    howitworks_slider.hide();
    
    howitworks_recommender = $('howitworks_recommender');
    howitworks_candidate = $('howitworks_candidate');
}

function toggle_howitworks() {
    howitworks_slider.toggle().chain(function() {
        OverText.update();
    });
    
    if (howitworks_state == true) {
        $('howitworks_arrow').set('html', '&darr;');
        howitworks_state = false;
    } else {
        $('howitworks_arrow').set('html', '&uarr;');
        howitworks_state = true;
    }
}

function howitworks_switch_to(_div) {
    if (_div == 'recommender') {
        howitworks_candidate.fade('out');
        howitworks_candidate.setStyle('display', 'none');
        howitworks_recommender.fade('in');
        howitworks_recommender.setStyle('display', 'block');
        $('howitworks_toggle_recommender').setStyle('background-color', '#d9d9d9');
        $('howitworks_toggle_candidate').setStyle('background-color', '#fff');
    } else {
        howitworks_recommender.fade('out');
        howitworks_recommender.setStyle('display', 'none');
        howitworks_candidate.setStyle('display', 'block');
        howitworks_candidate.fade('in');
        $('howitworks_toggle_recommender').setStyle('background-color', '#fff');
        $('howitworks_toggle_candidate').setStyle('background-color', '#d9d9d9');
    }
}