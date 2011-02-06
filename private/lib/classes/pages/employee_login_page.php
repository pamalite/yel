<?php
require_once dirname(__FILE__). "/../page.php";

class EmployeeLoginPage extends Page {
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_employee_login_css() {
        $this->insert_css('employee_login.css');
    }
    
    public function insert_employee_login_scripts() {
        $this->insert_scripts('employee_login.js');
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline scripts for welcome page goes here.
    }
    
    public function show($_error = "") {
        $this->begin();
        $this->top('Employee Sign In');
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div class="login_form">
            <form method="post" onSubmit="return false;">
                <label for="id">User ID:</label><br/>
                <input type="text" class="field" id="id" name="id" value="" />
                <br/><br/>
                <label for="password">Password:</label><br/>
                <input type="password" class="field" id="password" name="password" />
                <div class="button_bar right">
                    <input type="submit" class="login" id="login" value="Sign In" />
                </div>
            </form>
        </div>

        <?php
    }
}
?>