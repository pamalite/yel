<?php
require_once dirname(__FILE__). "/../../utilities.php";

class ContactPage extends Page {
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_contact_css() {
        $this->insert_css('contact.css');
    }
    
    public function insert_contact_scripts() {
        $this->insert_scripts('');
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline scripts for welcome page goes here.
    }
    
    public function show() {
        $this->begin();
        $this->top("Contact Us");
        
        $page = file_get_contents(dirname(__FILE__). '/../../../html/contact_page.html');
        $page = str_replace('%root%', $this->url_root, $page);
        
        echo $page;
    }
}
?>