<?php
require_once dirname(__FILE__). "/../page.php";

class MemberLoginPage extends Page {
    private $job_to_redirect = '';
    
    function __construct($_job = '') {
        $this->job_to_redirect = $_job;
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_member_login_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/member_login.css">'. "\n";
    }
    
    public function insert_member_login_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/member_login.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts($_signed_up = false) {
        echo '<script type="text/javascript">'. "\n";
        if (!empty($this->job_to_redirect)) {
            echo 'var job_to_redirect = "?job='. $this->job_to_redirect. '";'. "\n";
        } else {
            echo 'var job_to_redirect = "";'. "\n";
        }
        
        if ($_signed_up == 'success') {
            echo 'var signed_up = true;'. "\n";
        } else {
            echo 'var signed_up = false;'. "\n";
        }
        
        if ($_signed_up == 'activated') {
            echo 'var activated = true;'. "\n";
        } else {
            echo 'var activated = false;'. "\n";
        }
        echo '</script>';
    }
    
    public function show($_error = "") {
        $this->begin();
        $this->top("Member Sign In");
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div class="login_form">
            <form method="post" onSubmit="return false;">
                <label for="id">E-mail:</label><br/>
                <input type="text" id="id" name="id" value="" />
                <br/><br/>
                <label for="password">Password:</label><br/>
                <input type="password" id="password" name="password" />
                <div class="button_bar left">
                    <a class="no_link" onClick="show_password_reset_window();">Reset Password</a>
                </div>
                <div class="button_bar right">
                    <a href="sign_up.php">Sign Up</a>&nbsp;
                    <input type="submit" class="login" id="login" value="Sign In" />
                </div>
            </form>
        </div>
        
        <!-- popup goes here -->
        <div id="div_password_reset_window" class="popup_window">
            <div class="window_title" id="window_title">Get My Password Hint</div>
            
            <form method="post" onSubmit="return false;">
                <div id="div_password_hint_form">
                    <table class="password_hint_form">
                        <tr>
                            <td class="label"><label for="email_addr">Email:</label></td>
                            <td class="field"><input class="field" type="text" id="email_addr" name="email_addr" /></td>
                        </tr>
                        <tr>
                            <td class="buttons" colspan="2">
                                <input type="button" value="Get My Password Hint" onClick="continue_password_reset();" />
                                <input type="button" onClick="close_password_reset_window(false);" value="Cancel" />
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div id="div_password_reset_form">
                    <table class="password_reset_form">
                        <tr>
                            <td class="question"><label for="hint_answer"><span id="password_hint"></span></label></td>
                        </tr>
                        <tr>
                            <td><input type="text" class="field" id="hint_answer" name="hint_answer" /></td>
                        </tr>
                        <tr>
                            <td class="buttons" colspan="2">
                                <input type="button" value="Reset My Password" onClick="close_password_reset_window(true);" />
                                <input type="button" onClick="close_password_reset_window(false);" value="Cancel" />
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
        </div>
        
        <?php
    }
}
?>