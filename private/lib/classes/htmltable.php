<?php
class HTMLTable {
    private $id = '';
    private $is_tr_id = false;
    private $css_class = '';
    private $items = array();
    
    function __construct($_id = '', $_css_class = '') {
        if (!empty($_id)) {
            $this->id = $_id;
        }
        
        if (!empty($_css_class)) {
            $this->css_class = $_css_class;
        }
    }
    
    function is_tr_id($_is_tr_id) {
        $this->is_tr_id = $_is_tr_id;
    }
    
    function set($_row, $_column, $_content = '', $_id = '', $_css_class = '') {
        if ($_row >= count($this->items)) {
            $this->items[$_row] = array();
        }
        
        $this->items[$_row][$_column] = '<td id="'. $_id. '" class="'. $_css_class. '">'. $_content. '</td>'. "\n";
    }
    
    function set_column_span($_row, $_column, $_spanning = 0) {
        if ($_row < count($this->items) && $_column < count($this->items[$_row])) {
            $item = $this->items[$_row][$_column];
            $front = $back = '';
            for ($i=0; $i < strlen($item); $i++) {
                if (substr($item, $i, 1) == '>') {
                    $back = substr($item, $i);
                } else {
                    $front .= substr($item, $i, 1);
                }
            }
            
            $item = $front. ' colspan="'. $_spanning. '"'. $back;
            $this->items[$_row][$_column] = $item;
        }
    }
    
    function set_row_span($_row, $_column, $_spanning = 0) {
        if ($_row < count($this->items) && $_column < count($this->items[$_row])) {
            $item = $this->items[$_row][$_column];
            $front = $back = '';
            for ($i=0; $i < strlen($item); $i++) {
                if (substr($item, $i, 1) == '>') {
                    $back = substr($item, $i);
                } else {
                    $front .= substr($item, $i, 1);
                }
            }
            
            $item = $front. ' rowspan="'. $_spanning. '"'. $back;
            $this->items[$_row][$_column] = $item;
        }
    }
    
    function get_html() {
        $html = '<table id="'. $this->id. '" class="'. $this->css_class. '">'. "\n";
        
        foreach ($this->items as $i=>$columns) {
            $html .= ($this->is_tr_id) ? '<tr id="'. $i. '">'. "\n" : '<tr>'. "\n";
            foreach ($columns as $column) {
                $html .= $column;
            }
            $html .= '</tr>'. "\n";
        }
        
        $html .= '</table>'. "\n";
        
        return $html;
    }
}
?>
