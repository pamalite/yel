<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployeeHomePage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        parent::__construct();
        
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_home_css() {
        $this->insert_css('employee_home_page.css');
    }
    
    public function insert_employee_home_scripts() {
        $this->insert_scripts('employee_home_page.js');
    }
    
    public function insert_inline_scripts() {
        $script = 'var id = "'. $this->employee->getId(). '";'. "\n";
        
        $this->header = str_replace('<!-- %inline_javascript% -->', $script, $this->header);
    }
    
    public function show() {
        $this->begin();
        $branch = $this->employee->getBranch();
        $this->top('Home - '. $branch[0]['country']);
        $this->menu_employee('home');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div class="deco">&nbsp;</div>
        <?php
    }
}
?>