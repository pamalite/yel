function ListBox(_div_id, _name, _multiselect) {
    this.div_id = _div_id;
    this.name = _name;
    this.multiselect = (_multiselect == true || _multiselect == 1) ? true : false;
    this.selected_item = '';
    this.selected_value = '';
    this.selected_index = '';
    this.items = new Array();
    this.values = new Array();
    this.selected_indices = new Array();
    
    this.get_selected_values = function() {
        var item_value_pairs = new Array();
        var counter = 0;
        for (var i=0; i < this.selected_indices.length; i++) {
            if (this.selected_indices[i] != -1) {
                item_value_pairs[counter] = this.items[this.selected_indices[i]] + '|' + this.values[this.selected_indices[i]];
                counter++;
            }
        }
        
        return item_value_pairs;
    };
    
    this.add_item = function(_item, _value) {
        this.items[this.items.length] = _item;
        this.values[this.values.length] = _value;
    };
    
    this.remove_item = function(_index) {
        var temp_items = new Array();
        var temp_values = new Array();
        
        for (var i=0; i < this.items.length; i++) {
            if (i != _index) {
                temp_items[i] = this.items[i];
                temp_values[i] = this.values[i];
            }
        }
        
        this.items = temp_items;
        this.values = temp_values;
        
        temp_items = null;
        temp_values = null;
    };
    
    this.clear = function() {
        this.selected_item = '';
        this.selected_value = '';
        this.selected_index = '';
        this.items = new Array();
        this.values = new Array();
        this.selected_indices = new Array();
    }
    
    this.add_items = function(_items, _values) {
        if (_items.length != _values.length) {
            return false;
        }
        
        this.items = new Array();
        this.values = new Array();
        
        for (var i=0; i < _items.length; i++) {
            this.items[i] = _items[i];
            this.values[i] = _values[i];
        }
    };
    
    this.show = function() {
        $(this.div_id).set('html', '');
        
        var html = '';
        for (var i=0; i < this.items.length; i++) {
            html = html + '<div class="list_item" id="' + this.name + '_item_' + i + '" onClick="' + this.name + '.click(\'' + i + '\');" onMouseOver="' + this.name + '.mouse_in(\'' + i + '\');" onMouseOut="' + this.name + '.mouse_out(\'' + i + '\');">' + this.items[i] + '</div>' + "\n";
        }
        
        $(this.div_id).set('html', html);
    };
    
    this.click = function(_index) {
        if (!_multiselect) {
            for (var i=0; i < this.items.length; i++) {
                $(this.name + '_item_' + i).setStyle('background-color', '#FFFFFF');
                $(this.name + '_item_' + i).setStyle('color', '#000000');
            }

            $(this.name + '_item_' + _index).setStyle('background-color', '#6699FF');
            $(this.name + '_item_' + _index).setStyle('color', '#FFFFFF');

            this.selected_item = this.items[_index];
            this.selected_value = this.values[_index];
            this.selected_index = _index;
        } else {
            if ($(this.name + '_item_' + _index).getStyle('background-color') == '#ffffff') {
                // not selected; select it
                $(this.name + '_item_' + _index).setStyle('background-color', '#6699FF');
                $(this.name + '_item_' + _index).setStyle('color', '#FFFFFF');
                
                this.selected_indices[this.selected_indices.length] = _index;
                
                // for knowing which item was last selected.
                this.selected_item = this.items[_index];
                this.selected_value = this.values[_index];
                this.selected_index = _index;
            } else {
                // selected; deselect it
                $(this.name + '_item_' + _index).setStyle('background-color', '#FFFFFF');
                $(this.name + '_item_' + _index).setStyle('color', '#000000');
                
               for (var i=0; i < this.selected_indices.length; i++) {
                    if (this.selected_indices[i] == _index) {
                        this.selected_indices[i] = -1;
                    }
                }
                
                // for knowing which item was last selected.
                this.selected_item = '';
                this.selected_value = '';
                this.selected_index = '';
            }
        }
    };
    
    this.declick = function() {
        for (var i=0; i < this.items.length; i++) {
            $(this.name + '_item_' + i).setStyle('background-color', '#FFFFFF');
            $(this.name + '_item_' + i).setStyle('color', '#000000');
        }
        
        this.selected_item = '';
        this.selected_value = '';
        this.selected_index = '';
        this.selected_indices = new Array();
    }
    
    this.mouse_in = function(_index) {
        $(this.name + '_item_' + _index).setStyle('border', '1px dashed #6699FF');
    };
    
    this.mouse_out = function(_index) {
        $(this.name + '_item_' + _index).setStyle('border', '1px solid #FFFFFF');
    };
}