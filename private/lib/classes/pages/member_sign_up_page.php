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
    
    private function generateCountries($_selected, $_name = 'country') {
        $criteria = array(
            'columns' => "country_code, country", 
            'order' => "country"
        );
        $countries = Country::find($criteria);
        
        echo '<select class="field" id="'. $_name. '" name="'. $_name. '">'. "\n";
        if (empty($_selected) || is_null($_selected) || $_selected == '0') {
            echo '<option value="0" selected>Please select a county.</option>'. "\n";    
        } else {
            echo '<option value="0">Please select an country.</option>'. "\n";
        }
        
        foreach ($countries as $country) {
            if ($country['country_code'] != $_selected) {
                echo '<option value="'. $country['country_code']. '">'. $country['country']. '</option>'. "\n";
            } else {
                echo '<option value="'. $country['country_code']. '" selected>'. $country['country']. '</option>'. "\n";
            }
        }
        
        echo '</select>'. "\n";
    }
    
    private function generate_password_reset_questions($_selected) {
        $mysqli = Database::connect();
        $query = "SELECT * FROM password_reset_questions";
        $questions = $mysqli->query($query);
        
        echo '<select class="field" id="forget_password_question" name="forget_password_question">'. "\n";
        
        if (empty($_selected) || is_null($_selected) || $_selected == '0') {
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
    
    private function generate_industries($_id, $_selecteds) {
        $criteria = array('columns' => "id, industry, parent_id");
        $industries = Industry::find($criteria);
        
        echo '<select class="multiselect" id="'. $_id. '" name="'. $_id. '[]" multiple>'. "\n";
        
        foreach ($industries as $industry) {
            $css_class = '';
            $spacing = '';
            if (is_null($industry['parent_id'])) {
                $css_class = 'class = "main_industry"';
            } else {
                $spacing = '&nbsp;&nbsp;&nbsp;';
            }
            
            $selected = false;
            foreach ($_selecteds as $expertise) {
                if ($expertise == $industry['id']) {
                    $selected = true;
                    break;
                }
            }
            
            if ($selected) {
                echo '<option value="'. $industry['id']. '" '. $css_class. ' selected>'. $spacing. $industry['industry']. '</option>'. "\n";
            } else {
                echo '<option value="'. $industry['id']. '" '. $css_class. '>'. $spacing. $industry['industry']. '</option>'. "\n";
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
            $this->top("Member Sign Up (Invited by ". $member->getFullName(). ")");
        } else {
            $this->top("Member Sign Up");
        }
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>

        <div class="profile">
            <form id="profile" method="post" action="sign_up_action.php">
                <input type="hidden" name="member" value="<?php echo $this->member ?>" />
                <input type="hidden" name="referee" value="<?php echo $this->referee ?>" />
                <table class="profile_form">
                    <tr>
                        <td class="instruction" colspan="2">Please fill up <span style="font-weight: bold; text-decoration: underline;">ALL</span> the fields.</td>
                    </tr>
                    <tr>
                        <td class="section_title" colspan="2">About You</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="firstname">First Name / Given Names:</label></td>
                        <td class="field"><input class="field" type="text" id="firstname" name="firstname" value="<?php echo $_session['firstname'] ?>"/></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="lastname">Last Name / Surname:</label></td>
                        <td class="field">
                            <input class="field" type="text" id="lastname" name="lastname" value="<?php echo $_session['lastname'] ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">&nbsp;</td>
                        <td class="note">Your full name will be used for processing payments (rewards) into your bank account. Please ensure that the name you enter is genuine and accurate. Failure to do so may result in you not receiving your rewards.</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="citizenship">Nationality:</label></td>
                        <td class="field">
                            <?php $this->generateCountries($_session['citizenship'], 'citizenship') ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="label center"><input type="checkbox" id="individual_headhunter" name="individual_headhunter" <?php echo ($_session['individual_headhunter'] == 'Y' || isset($_GET['indiv_hh'])) ? 'checked' : '' ?> />&nbsp;<label for="individual_headhunter">I am an Independent Recruitment Consultant</label></td>
                    </tr>
                    <tr>
                        <td class="section_title" colspan="2">Sign In Details</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="email_addr">E-mail Address:</label></td>
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
                        <td class="label"><label for="password">Password:</label></td>
                        <td class="field"><input class="field" type="password" id="password" name="password" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="password_confirm">Confirm Password:</label></td>
                        <td class="field"><input class="field" type="password" id="password_confirm" name="password_confirm" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="forget_password_question">Password hint:</label></td>
                        <td class="field">
                            <?php $this->generate_password_reset_questions($_session['forget_question']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="forget_password_answer">Password hint answer:</label></td>
                        <td class="field"><input class="field" type="text" id="forget_password_answer" name="forget_password_answer" value="<?php echo $_session['forget_answer'] ?>" /></td>
                    </tr>
                    <tr>
                        <td class="section_title" colspan="2">Contact Details</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="phone_num">Contact Number:</label></td>
                        <td class="field"><input class="field" type="text" id="phone_num" name="phone_num" value="<?php echo $_session['phone_num'] ?>"/></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="address">Mailing Address:</label></td>
                        <td class="field"><textarea id="address" name="address"><?php echo $_session['address'] ?></textarea></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="state">State/Province:</label></td>
                        <td class="field"><input class="field" type="text" id="state" name="state" value="<?php echo $_session['state'] ?>"/></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="zip">Zip/Postal Code:</label></td>
                        <td class="field"><input class="field" type="text" id="zip" name="zip" value="<?php echo $_session['zip'] ?>"/></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="country">Country:</label></td>
                        <td class="field">
                            <?php $this->generateCountries($_session['country']) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="section_title" colspan="2">Top 3 Specializations &amp; Preference</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="label center">Please choose your top 3 industrial sectors, or majors. We collect these information is to better understand the needs of our members.</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="center">
                            <?php $this->generate_industries('industry', $_session['industry']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="note center">
                            Hold down the CTRL or Command (Mac) key while selecting multiple specializations.
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="label center"><input type="checkbox" id="like_newsletter" name="like_newsletter" <?php echo ($_session['like_newsletter'] == 'N') ? '' : 'checked' ?> />&nbsp;<label for="like_newsletter">I want to receive weekly highlights of the latest jobs</label></td>
                    </tr>
                    <tr>
                        <td class="section_title" colspan="2">Security</td>
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
                            <input type="checkbox" id="agreed_terms" name="agreed_terms" />&nbsp;<span style="font-size: 10pt;vertical-align: middle;"><label for="agreed_terms">I have read, understood and accepted the <a target="_new" href="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/terms.php">Terms of Use of Yellow Elevator</a>.</label></span>&nbsp;<input type="submit" id="save" value="Sign Me Up!" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }
}

?>