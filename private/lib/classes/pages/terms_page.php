<?php
require_once dirname(__FILE__). "/../../utilities.php";

class TermsPage extends Page {
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_terms_css() {
        $this->insert_css('terms.css');
    }
    
    public function insert_terms_scripts() {
        $this->insert_scripts('');
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline scripts for welcome page goes here.
    }
    
    public function show() {
        $this->begin();
        $this->top("Terms of Use");
        
        $page = file_get_contents(dirname(__FILE__). '/../../../html/terms_page.html');
        
        echo $page;
    }
}
?>