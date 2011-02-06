<?php
require_once dirname(__FILE__). "/../../utilities.php";

class FaqPage extends Page {
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_faq_css() {
        $this->insert_css('faq.css');
    }
    
    public function insert_faq_scripts() {
        $this->insert_scripts('');
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline scripts for welcome page goes here.
    }
    
    public function show() {
        $this->begin();
        $this->top("Frequently Asked Questions");
        
        $page = file_get_contents(dirname(__FILE__). '/../../../html/faq_page.html');
        
        echo $page;
    }
}
?>