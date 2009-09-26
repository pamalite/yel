function toggle_tip(_id, _max_height) {
    var height = $(_id).getStyle('height');
    
    if (parseInt(height) >= _max_height) {
        $(_id).tween('height', '0px');
        $(_id).setStyle('border', 'none');
    } else {
        $(_id).tween('height', _max_height + 'px');
        $(_id).setStyle('border', '2px solid #ffcc00');
    }
}
