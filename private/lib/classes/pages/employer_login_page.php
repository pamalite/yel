<?php
require_once dirname(__FILE__). "/../page.php";

class EmployerLoginPage extends Page {
    
    function __construct($_job = '') {
        parent::__construct();
        
        $this->job_to_redirect = $_job;
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_employer_login_css() {
        $this->insert_css('employer_login.css');
    }
    
    public function insert_employer_login_scripts() {
        $this->insert_scripts('employer_login.js');
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline scripts for welcome page goes here.
    }
    
    public function show($_error = "") {
        $this->begin();
        $this->top('Employer Login');
        
        $page = file_get_contents(dirname(__FILE__). '/../../../html/employer_login_page.html');
        $page = str_replace('%root%', $this->url_root, $page);
        
        echo $page;
    }
}
?>