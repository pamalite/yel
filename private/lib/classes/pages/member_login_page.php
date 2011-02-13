<?php
require_once dirname(__FILE__). "/../page.php";

class MemberLoginPage extends Page {
    private $job_to_redirect = '';
    
    function __construct($_job = '') {
        parent::__construct();
        
        $this->job_to_redirect = $_job;
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_member_login_css() {
        $this->insert_css('member_login.css');
    }
    
    public function insert_member_login_scripts() {
        $this->insert_scripts('member_login.js');
    }
    
    public function insert_inline_scripts($_signed_up = false) {
        $script = '';
        
        if (!empty($this->job_to_redirect)) {
            $script = 'var job_to_redirect = "?job='. $this->job_to_redirect. '";'. "\n";
        } else {
            $script = 'var job_to_redirect = "";'. "\n";
        }
        
        if ($_signed_up == 'success') {
            $script .= 'var signed_up = true;'. "\n";
        } else {
            $script .= 'var signed_up = false;'. "\n";
        }
        
        if ($_signed_up == 'activated') {
            $script .= 'var activated = true;'. "\n";
        } else {
            $script .= 'var activated = false;'. "\n";
        }
        
        $this->header = str_replace('<!-- %inline_javascript% -->', $script, $this->header);
    }
    
    public function show($_error = "") {
        $this->begin();
        $this->top("Candidate Login");
        
        $page = file_get_contents(dirname(__FILE__). '/../../../html/member_login_page.html');
        
        echo $page;
    }
}
?>