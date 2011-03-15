<?php
require_once dirname(__FILE__). "/../../utilities.php";

class PrivacyPage extends Page {
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_privacy_css() {
        $this->insert_css('privacy.css');
    }
    
    public function insert_privacy_scripts() {
        $this->insert_scripts('');
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline scripts for welcome page goes here.
    }
    
    public function show() {
        $this->begin();
        $this->top("Privacy Policy");
        
        $page = file_get_contents(dirname(__FILE__). '/../../../html/privacy_page.html');
        echo $page;
    }
}
?>