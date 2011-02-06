<?php
require_once dirname(__FILE__). "/../../utilities.php";

class AboutPage extends Page {
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_about_css() {
        $this->insert_css('about.css');
    }
    
    public function insert_about_scripts() {
        $this->insert_scripts('about.js');
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline scripts for welcome page goes here.
    }
    
    public function show() {
        $this->begin();
        $this->top("About Us");
        
        $page = file_get_contents(dirname(__FILE__). '/../../../html/about_page.html');
        
        echo $page;
    }
}
?>