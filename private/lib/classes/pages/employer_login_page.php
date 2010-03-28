<?php
require_once dirname(__FILE__). "/../page.php";

class EmployerLoginPage extends Page {
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_employer_login_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employer_login.css">'. "\n";
    }
    
    public function insert_employer_login_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employer_login.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline scripts for welcome page goes here.
    }
    
    public function show($_error = "") {
        $this->begin();
        $this->top('Employer Sign In');
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div class="login_form">
            <form method="post" onSubmit="return false;">
                <label for="id">User ID:</label><br/>
                <input type="text" id="id" name="id" value="" />
                <br/><br/>
                <label for="password">Password:</label><br/>
                <input type="password" id="password" name="password" />
                <div class="button_bar left">
                    <a href="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root'] ?>/contact.php">Forgot Password? Call Support.</a>
                </div>
                <div class="button_bar right">
                    <input type="submit" class="login" id="login" value="Sign In" />
                </div>
            </form>
        </div>
        <div class="login_form contact_drop">
            <a href="#" class="signup" onClick="show_contact_drop_form();"><img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/contact_sign_up.jpg" /></a>
        </div>
        
        <!-- popup goes here -->
        <div id="contact_drop_form" class="popup_window">
            <form method="post" onSubmit="return false;">
                <table class="drop_contact">
                    <tr>
                        <td colspan="2" class="title">Drop Us Your Contact</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="company">Company Name:</label></td>
                        <td><input type="text" id="company" name="company" value="" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="phone">Contact Number:</label></td>
                        <td><input type="text" id="phone" name="phone" value="" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="email">E-mail Address:</label></td>
                        <td><input type="text" id="email" name="email" value="" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="contact">Contact Person:</label></td>
                        <td><input type="text" id="contact" name="contact" value="" /></td>
                    </tr>
                    <tr>
                        <td class="buttons" colspan="2">
                            <input type="button" class="drop" id="drop" value="Drop My Contact Now" />
                            <input type="button" class="drop" onClick="close_contact_drop_form();" value="Cancel" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        
        <!--div id="div_blanket"></div>
        <div id="div_contact_drop_form">
            
        </div-->
        <?php
    }
}
?>