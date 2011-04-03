var howitworks_slider = '';
var howitworks_state = false;
var howitworks_recommender = '';
var howitworks_candidate = '';

function initialize_howitworks() {
    if ($('howitworks') == null) {
        return;
    }
    
    $('howitworks').setStyle('display', 'block');
    howitworks_slider = new Fx.Slide($('howitworks'), {
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
    var recommender_tween = new Fx.Tween(howitworks_recommender);
    var candidate_tween = new Fx.Tween(howitworks_candidate);
    
    if (_div == 'recommender') {
        // sets the tab
        $('howitworks_toggle_recommender').setStyle('background-color', '#d9d9d9');
        $('howitworks_toggle_candidate').setStyle('background-color', '#fff');
        
        // sets the display
        candidate_tween.start('opacity', 1, 0).chain(function() {
            howitworks_candidate.setStyle('display', 'none');
            howitworks_recommender.setStyle('opacity', '0');
            howitworks_recommender.setStyle('display', 'block');
            recommender_tween.start('opacity', 0, 1);
        });
    } else {
        // sets the tab
        $('howitworks_toggle_recommender').setStyle('background-color', '#fff');
        $('howitworks_toggle_candidate').setStyle('background-color', '#d9d9d9');
        
        // sets the display
        recommender_tween.start('opacity', 1, 0).chain(function () {
            howitworks_recommender.setStyle('display', 'none');
            howitworks_candidate.setStyle('opacity', '0');
            howitworks_candidate.setStyle('display', 'block');
            candidate_tween.start('opacity', 0, 1);
        });
    }
}