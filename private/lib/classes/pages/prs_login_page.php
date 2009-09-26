<?php
require_once dirname(__FILE__). "/../page.php";

class PRSLoginPage extends Page {
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_prs_login_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_login.css">'. "\n";
    }
    
    public function insert_prs_login_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/prs_login.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline scripts for welcome page goes here.
    }
    
    public function show($_error = "") {
        $this->begin();
        $this->top("Yellow Elevator - Privileged Resumes System");
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <form method="post" id="login_form" onSubmit="return false">
            <div class="login">
                <table class="login">
                    <tr>
                        <td colspan="2" class="title">PRS Sign In</td>
                    </tr>
                    <tr>
                        <td class="id"><label for="id">User ID:</label></td>
                        <td><input type="text" id="id" name="id" value=""></td>
                    </tr>
                    <tr>
                        <td class="password"><label for="password">Password:</label></td>
                        <td><input type="password" id="password" name="password"></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <input type="submit" class="login" id="login" value="Sign In">
                        </td>
                    </tr>
                </table>
            </div>
        </form>
        <?php
    }
}
?>