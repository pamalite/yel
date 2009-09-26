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
        $this->top("Yellow Elevator&nbsp;&nbsp;<span style=\"color: #FC8503;\">Employers</span>");
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <table class="content">
            <tr>
                <td class="content">
                    <div class="content">
                        <table class="description">
                            <tr>
                                <td class="icons"><img style="border: none;" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root'] ?>/common/images/misc/employer_login/01.jpg" /></td>
                                <td>Create and manage your job ads</td>
                            </tr>
                            <tr>
                                <td class="icons"><img style="border: none;" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root'] ?>/common/images/misc/employer_login/02.jpg" /></td>
                                <td>Manage and track resumes of referred candidates</td>
                            </tr>
                            <tr>
                                <td class="icons"><img style="border: none;" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root'] ?>/common/images/misc/employer_login/03.jpg" /></td>
                                <td>Keep track of successful employments</td>
                            </tr>
                            <tr>
                                <td style="padding-top: 25px;" colspan="2">&quot;Identifying better candidates for your organization through the power of job referrals.&quot;</td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td class="login_form">
                    <form method="post" id="login_form" onSubmit="return false">
                        <div class="login">
                            <table class="login">
                                <tr>
                                    <td class="title">Employer Sign In</td>
                                </tr>
                                <tr>
                                    <td><label for="id">User ID:</label><br/>
                                    <input type="text" id="id" name="id" value=""></td>
                                </tr>
                                <tr>
                                    <td><label for="password">Password:</label><br/>
                                    <input type="password" id="password" name="password"></td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="submit" class="login" id="login" value="Sign In">
                                        &nbsp;
                                        <span class="forgot_password"><a href="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root'] ?>/contact.php">Forgot Password? Call Support.</a></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </form>
                    <div class="login">
                        <table class="login">
                            <tr>
                                <td style="text-align: center; font-weight: bold; font-size: 12pt; padding-bottom: 5px;">
                                    <a href="#" class="signup" onClick="show_contact_drop_form();"><img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/contact_sign_up.jpg" /></a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
        
        <div id="div_blanket"></div>
        <div id="div_contact_drop_form">
            <form method="post" id="contact_drop_form" onSubmit="return false;">
                <table class="drop_contact">
                    <tr>
                        <td colspan="2" class="title">Drop Us Your Contact</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="company">Company Name:</label></td>
                        <td><input type="text" id="company" name="company" value=""></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="phone">Contact Number:</label></td>
                        <td><input type="text" id="phone" name="phone" value=""></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="email">E-mail Address:</label></td>
                        <td><input type="text" id="email" name="email" value=""></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="contact">Contact Person:</label></td>
                        <td><input type="text" id="contact" name="contact" value=""></td>
                    </tr>
                    <tr>
                        <td class="buttons" colspan="2">
                            <input type="button" class="drop" onClick="close_contact_drop_form();" value="Cancel">
                            &nbsp;
                            <input type="button" class="drop" id="drop" value="Drop My Contact Now">
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }
}
?>