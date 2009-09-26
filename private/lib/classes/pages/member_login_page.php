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
    
    private function generate_password_reset_questions() {
        $mysqli = Database::connect();
        $query = "SELECT * FROM password_reset_questions";
        $questions = $mysqli->query($query);
        
        echo '<select class="field" id="forget_password_question" name="forget_password_question">'. "\n";
        echo '<option value="0" selected>Please select a password hint.</option>'. "\n";    
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        foreach ($questions as $question) {
            if ($question['id'] != $selected) {
                echo '<option value="'. $question['id']. '">'. $question['question']. '</option>'. "\n";
            } else {
                echo '<option value="'. $question['id']. '" selected>'. $question['question']. '</option>'. "\n";
            }
        }
        
        echo '</select>'. "\n";
    }
    
    public function show($_error = "") {
        $this->begin();
        $this->top("Yellow Elevator&nbsp;&nbsp;<span style=\"color: #FC8503;\">Members</span>");
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
                                <td class="icons"><img style="border: none;" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root'] ?>/common/images/misc/member_login/01.jpg" /></td>
                                <td>Create and manage multiple resumes</td>
                            </tr>
                            <tr>
                                <td class="icons"><img style="border: none;" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root'] ?>/common/images/misc/member_login/02.jpg" /></td>
                                <td>Manage your contacts</td>
                            </tr>
                            <tr>
                                <td class="icons"><img style="border: none;" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root'] ?>/common/images/misc/member_login/03.jpg" /></td>
                                <td>Refer jobs to your contacts easily and get referred to better jobs</td>
                            </tr>
                            <tr>
                                <td class="icons"><img style="border: none;" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root'] ?>/common/images/misc/member_login/04.jpg" /></td>
                                <td>Manage and keep track of the referrals you make</td>
                            </tr>
                            <tr>
                                <td class="icons"><img style="border: none;" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root'] ?>/common/images/misc/member_login/05.jpg" /></td>
                                <td>Earn and manage the rewards for every successful job referral that you make</td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td class="login_form">
                    <form method="post" id="login_form" onSubmit="return false">
                        <div class="login">
                            <table class="login">
                                <tr>
                                    <td class="title">Member Sign In</td>
                                </tr>
                                <tr>
                                    <td><label for="id">E-mail:</label><br/>
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
                                        <span class="forgot_password"><a class="no_link" onClick="show_get_password_hint();">Reset Password</a></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </form>
                    
                </td>
            </tr>
        </table>
        
        <div style="text-align: center;">
            <a href="sign_up.php"><img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/50_bonus_banner.jpg" /></a>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_get_password_hint_form">
            <form onSubmit="retun false;">
                <table class="get_password_hint_form">
                    <tr>
                        <td class="label"><label for="email_addr">Email:</label></td>
                        <td class="field"><input class="field" type="text" id="email_addr" name="email_addr" /></td>
                    </tr>
                </table>
                <p class="button"><input type="button" value="Cancel" onClick="close_get_password_hint();" />&nbsp;<input type="button" value="Get My Password Hint" onClick="get_password_hint();" /></p>
            </form>
        </div>
        
        <div id="div_reset_password_form">
            <form onSubmit="retun false;">
                <input type="hidden" id="hint_id" name="hint_id" />
                <table class="reset_password_form">
                    <tr>
                        <td class="question"><label for="hint_answer"><span id="password_hint"></span></label></td>
                    </tr>
                    <tr>
                        <td><textarea class="field" id="hint_answer" name="hint_answer"></textarea></td>
                    </tr>
                </table>
                <p class="button"><input type="button" value="Cancel" onClick="close_reset_password();" />&nbsp;<input type="button" value="Reset My Password" onClick="reset_password();" /></p>
            </form>
        </div>
        <?php
    }
}
?>