function HTMLTable(_id, _css_class) {
    this.id = _id;
    this.css_class = _css_class;
    this.items = new Array();
    this.row_ids = new Array();
    
    this.set = function(_row, _column, _content, _id, _css_class) {
        if (_row >= this.items.length) {
            this.items[_row] = new Array();
        }
        
        if (_id === undefined || _id == null) {
            _id = '';
        }
        
        if (_css_class === undefined || _css_class == null) {
            _css_class = '';
        }
        
        this.items[_row][_column] = '<td id="' + _id + '" class="' + _css_class + '">' + _content + '</td>' + "\n";
    };
    
    this.clear_all = function() {
        this.items = new Array();
    };
    
    this.clear = function(_row, _column) {
        if (_row < this.items.length && _column < this.items[_row].length) {
            this.items[_row][_column] = '' + "\n";
        }
    };
    
    this.set_column_span = function(_row, _column, _spanning) {
        if (_row < this.items.length && _column < this.items[_row].length) {
            var item = this.items[_row][_column];
            var front = '';
            var back = '';
            for (var i=0; i < item.length; i++) {
                if (item.substr(i, 1) == '>') {
                    back = item.substr(i, item.length);
                    break;
                } else {
                    front = front + item.substr(i, 1);
                }
            }
            
            item = front + ' colspan="' + _spanning + '"' + back;
            this.items[_row][_column] = item;
        }
    };
    
    this.set_row_span = function(_row, _column, _spanning) {
        if (_row < this.items.length && _column < this.items[_row].length) {
            var item = this.items[_row][_column];
            var front = '';
            var back = '';
            for (var i=0; i < item.length; i++) {
                if (item.substr(i, 1) == '>') {
                    back = item.substr(i, item.length);
                    break;
                } else {
                    front = front + item.substr(i, 1);
                }
            }
            
            item = front + ' rowspan="' + _spanning + '"' + back;
            this.items[_row][_column] = item;
        }
    };
    
    this.get_html = function() {
        if (!this.id) {
            this.id = '';
        }
        
        if (!this.css_class) {
            this.css_class = '';
        }
        
        var html = '<table id="' + this.id + '" class="' + this.css_class + '">' + "\n";
        
        for (var i=0; i < this.items.length; i++) {
            html = html + '<tr>' + "\n";           
            for (var j=0; j < this.items[i].length; j++) {
                if (this.items[i][j] !== undefined) {
                    html = html + this.items[i][j];
                }
            }
            html = html + '</tr>' + "\n";
        }
        
        html = html + '</table>' + "\n";
        
        return html;
    };
}