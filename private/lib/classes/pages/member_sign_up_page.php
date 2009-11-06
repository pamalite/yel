<?php
require_once dirname(__FILE__). "/../../utilities.php";

class MemberSignUpPage extends Page {
    private $referee = '';
    private $member = '';
    private $error_message = '';
    
    function __construct($_referee = '', $_member = '') {
        if (!empty($_referee)) {
            $this->referee = desanitize($_referee);
        }
        
        if (!empty($_member)) {
            $this->member = desanitize($_member);
        }
        
        if ($_job > 0) {
            $this->job = desanitize($_job);
        }
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_sign_up_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/member_sign_up.css">'. "\n";
    }
    
    public function insert_member_sign_up_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/member_sign_up.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var referee = "'. $this->referee. '";'. "\n";
        echo 'var member = "'. $this->member. '";'. "\n";
        echo 'var error_message = "'. $this->error_message. '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function generateCountries($selected) {
        //$countries = Country::get_all_with_display();
        $countries = Country::get_all();
        
        echo '<select class="field" id="country" name="country">'. "\n";
        
        if (empty($selected) || is_null($selected) || $selected == '0') {
            echo '<option value="0" selected>Please select a country.</option>'. "\n";
        } else {
            echo '<option value="0">Please select a country.</option>'. "\n";
        }
        
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        foreach ($countries as $country) {
            if ($country['country_code'] != $selected) {
                echo '<option value="'. $country['country_code']. '">'. $country['country']. '</option>'. "\n";
            } else {
                echo '<option value="'. $country['country_code']. '" selected>'. $country['country']. '</option>'. "\n";
            }
        }
        
        echo '</select>'. "\n";
    }
    
    private function generate_password_reset_questions($selected) {
        $mysqli = Database::connect();
        $query = "SELECT * FROM password_reset_questions";
        $questions = $mysqli->query($query);
        
        echo '<select class="field" id="forget_password_question" name="forget_password_question">'. "\n";
        
        if (empty($selected) || is_null($selected) || $selected == '0') {
            echo '<option value="0" selected>Please select a password hint.</option>'. "\n";    
        } else {
            echo '<option value="0">Please select a password hint.</option>'. "\n";
        }
        
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
    
    private function generate_industries($_id, $selected) {
        $mysqli = Database::connect();
        $query = "SELECT * FROM industries";
        $industries = $mysqli->query($query);
        
        echo '<select class="field" id="'. $_id. '" name="'. $_id. '">'. "\n";
        
        if (empty($selected) || is_null($selected) || $selected == '0') {
            echo '<option value="0" selected>Please select an industry.</option>'. "\n";    
        } else {
            echo '<option value="0">Please select an industry.</option>'. "\n";
        }
        
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        foreach ($industries as $industry) {
            $css_class = '';
            $spacing = '';
            if (is_null($industry['parent_id'])) {
                $css_class = 'class = "main_industry"';
            } else {
                $spacing = '&nbsp;&nbsp;&nbsp;';
            }
            
            if ($industry['id'] != $selected) {
                echo '<option value="'. $industry['id']. '" '. $css_class. '>'. $spacing. $industry['industry']. '</option>'. "\n";
            } else {
                echo '<option value="'. $industry['id']. '" '. $css_class. ' selected>'. $spacing. $industry['industry']. '</option>'. "\n";
            }
        }
        
        echo '</select>'. "\n";
    }
    
    public function set_error($_error) {
        switch ($_error) {
            case '1':
                $this->error_message = 'Our system indicates that this e-mail address has already been registered with us. <br/>Please sign up with a different e-mail address. Thank you.';
                break;
            case '2':
                $this->error_message = 'The security code provided is invalid. Please try again.';
                break;
            default:
                $this->error_message = 'An unknown error occured.';
                break;
        }
    }
    
    public function show($_session) {
        $this->begin();
        
        if (!empty($this->member)) {
            $member = new Member($this->member);
            $this->top("Yellow Elevator&nbsp;&nbsp;<span style=\"color: #FC8503;\">Member Sign Up (Invited by ". $member->get_name(). ")</span>");
        } else {
            $this->top("Yellow Elevator&nbsp;&nbsp;<span style=\"color: #FC8503;\">Member Sign Up</span>");
        }
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div style="text-align: center;">
            <img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/50_banner.jpg" />
        </div>
        <div class="profile">
            <form id="profile" method="post" action="sign_up_action.php">
                <input type="hidden" name="member" value="<?php echo $this->member ?>" />
                <input type="hidden" name="referee" value="<?php echo $this->referee ?>" />
                <table class="profile_form">
                    <tr>
                        <td class="title" colspan="2">Member Sign Up Form</td>
                    </tr>
                    <tr>
                        <td class="instruction" colspan="2">Please fill up all fields, and fields marked with * are mandatory.</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="firstname">* First Name / Given Names:</label></td>
                        <td class="field"><input class="field" type="text" id="firstname" name="firstname" value="<?php echo $_session['firstname'] ?>"/></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="lastname">* Last Name / Surname:</label></td>
                        <td class="field">
                            <input class="field" type="text" id="lastname" name="lastname" value="<?php echo $_session['lastname'] ?>"/>
                            <p class="note">Your full name will be used for processing payments (rewards) into your bank account. Please ensure that the name you enter is genuine and accurate. Failure to do so may result in you not receiving your rewards.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="title" colspan="2">Expertise</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="primary_industry">* Primary/Majoring Specialization:</label></td>
                        <td class="field">
                            <?php $this->generate_industries('primary_industry', $_session['primary_industry']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="secondary_industry">* Secondary/Minoring Specialization:</label></td>
                        <td class="field">
                            <?php $this->generate_industries('secondary_industry', $_session['secondary_industry']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="tertiary_industry">* Tertiary/Minoring Specialization:</label></td>
                        <td class="field">
                            <?php $this->generate_industries('tertiary_industry', $_session['tertiary_industry']); ?>
                            <p class="note">Please choose your primary and secondary specializations. If you consider yourself to be an expert only in one specialization, select the same specialization as both your primary and secondary ones. The purpose of this information is for us to understand your needs better. If you are a fresh graduate, simply choose the specialization that is the closest to your major.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="title" colspan="2">Sign In Details</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="email_addr">* E-mail Address:</label></td>
                        <td class="field">
                            <?php
                                if (empty($this->referee)) {
                            ?>
                                    <input class="field" type="text" id="email_addr" name="email_addr" value="<?php echo $_session['email_addr'] ?>"/>
                            <?php
                                } else {
                            ?>
                                    <input id="email_addr" type="hidden" name="email_addr" value="<?php echo $this->referee; ?>" />
                            <?php
                                    echo $this->referee;
                                }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="password">* Password:</label></td>
                        <td class="field"><input class="field" type="password" id="password" name="password" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="password_confirm">* Confirm Password:</label></td>
                        <td class="field"><input class="field" type="password" id="password_confirm" name="password_confirm" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="forget_password_question">* Password hint:</label></td>
                        <td class="field">
                            <?php $this->generate_password_reset_questions($_session['forget_question']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="forget_password_answer">* Password hint answer:</label></td>
                        <td class="field"><input class="field" type="text" id="forget_password_answer" name="forget_password_answer" value="<?php echo $_session['forget_answer'] ?>" /></td>
                    </tr>
                    <tr>
                        <td class="title" colspan="2">Contact Details</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="phone_num">* Contact Number:</label></td>
                        <td class="field"><input class="field" type="text" id="phone_num" name="phone_num" value="<?php echo $_session['phone_num'] ?>"/></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="address">* Mailing Address:</label></td>
                        <td class="field"><textarea id="address" name="address"><?php echo $_session['address'] ?></textarea></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="state">* State/Province:</label></td>
                        <td class="field"><input class="field" type="text" id="state" name="state" value="<?php echo $_session['state'] ?>"/></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="zip">* Zip/Postal Code:</label></td>
                        <td class="field"><input class="field" type="text" id="zip" name="zip" value="<?php echo $_session['zip'] ?>"/></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="country">* Country:</label></td>
                        <td class="field">
                            <?php $this->generateCountries($_session['country']) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="like_newsletter">Get Weekly Highlights of Latest Jobs To Refer To Your Contacts:</label></td>
                        <td class="field"><input type="checkbox" id="like_newsletter" name="like_newsletter" <?php echo ($_session['like_newsletter'] == 'N') ? '' : 'checked' ?> /></td>
                    </tr>
                    <tr>
                        <td class="title" colspan="2">* Security</td>
                    </tr>
                    <tr>
                        <td class="instruction" colspan="2">Please type the characters as shown on the left.</td>
                    </tr>
                    <tr>
                        <td class="label"><img src="CaptchaSecurityImages.php?characters=6" /></td>
                        <td class="field"><input class="field" type="text" id="security_code" name="security_code" /></td>
                    </tr>
                    <tr>
                        <td class="buttons_bar" colspan="2">
                            <input type="checkbox" id="agreed_terms" name="agreed_terms" /><span style="font-size: 10pt;vertical-align: middle;"><label for="agreed_terms">I have read, understood and accepted the <a target="_new" href="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/terms.php">Terms of Use of Yellow Elevator</a>.</label></span>
                            &nbsp;
                            <input type="submit" id="save" value="Sign Me Up!" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }
}

?>