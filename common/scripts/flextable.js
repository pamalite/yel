function Cell(_content, _id, _css_class) {
    this.content = _content;
    this.id = _id;
    this.css_class = _css_class;
    this.colspan = 0;
    this.rowspan = 0;
}

function Row(_id) {
    this.id = _id;
    this.cells = new Array();
    
    this.set = function(_column, _cell) {
        this.cells[_column] = _cell;
    };
    
    this.clear = function(_column) {
        this.cells[_column] = null;
    };
}

function FlexTable(_id, _css_class) {
    this.id = _id;
    this.css_class = _css_class;
    this.rows = new Array();
    
    this.set = function(_row_index, _row) {
        this.rows[_row_index] = _row;
    };
    
    this.clear_all = function() {
        this.rows = new Array();
    };
    
    this.clear = function(_row_index) {
        var cells = this.rows[_row_index].cells;
        for (var i=0; i < cells.length; i++) {
            cells[i].content = '&nbsp;';
        }
        this.rows[_row_index].cells = cells;
    };
    
    this.remove = function(_row_index) {
        var new_rows = new Array();
        var idx = 0;
        for (var i=0; i < this.rows.length; i++) {
            if (_row_index != i) {
                new_rows[idx] = this.rows[i];
                idx++;
            }
        }
        this.rows = new_rows;
    };
    
    this.get_html = function() {
        if (!this.id) {
            this.id = '';
        }
        
        if (!this.css_class) {
            this.css_class = '';
        }
        
        var html = '<table id="' + this.id + '" class="' + this.css_class + '">' + "\n";
        
        for (var i=0; i < this.rows.length; i++) {
            var row_id = '';
            if (this.rows[i].id) {
                row_id = 'id="' + this.rows[i].id + '"';
            }
            html = html + '<tr ' + row_id + '>' + "\n";
            for (var j=0; j < this.rows[i].cells.length; j++) {
                if (this.rows[i].cells[j] !== undefined) {
                    var column_id = '';
                    if (this.rows[i].cells[j].id) {
                        column_id = 'id="' + this.rows[i].cells[j].id + '"';
                    }
                    
                    var column_css = '';
                    if (this.rows[i].cells[j].css_class) {
                        column_css = 'class="' + this.rows[i].cells[j].css_class + '"';
                    }
                    
                    var rowspan = '';
                    if (this.rows[i].cells[j].rowspan > 0) {
                        rowspan = 'rowspan="' + this.rows[i].cells[j].rowspan;
                    }
                    
                    var colspan = '';
                    if (this.rows[i].cells[j].colspan > 0) {
                        colspan = 'colspan="' + this.rows[i].cells[j].colspan;
                    }
                    html = html + '<td ' + column_id + ' ' + column_css + ' ' + colspan + ' ' + rowspan + '>';
                    html = html + this.rows[i].cells[j].content;
                    html = html + '</td>' + "\n";
                }
            }
            html = html + '</tr>' + "\n";
        }
        
        html = html + '</table>' + "\n";
        
        return html;
    };
}