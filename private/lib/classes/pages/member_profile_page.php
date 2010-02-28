<?php
require_once dirname(__FILE__). "/../../utilities.php";

class MemberProfilePage extends Page {
    private $member = NULL;
    
    function __construct($_session) {
        $this->member = new Member($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_profile_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/member_profile.css">'. "\n";
    }
    
    public function insert_member_profile_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/member_profile.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var email_addr = "'. $this->member->id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function generateCountries($selected) {
        //$countries = Country::getAllWithDisplay();
        $countries = Country::getAll();
        
        echo '<select class="field" id="country" name="country">'. "\n";
        
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
    
    public function show() {
        $this->begin();
        $this->top_search($this->member->get_name(). "&nbsp;&nbsp;<span style=\"color: #FC8503;\">Profile</span>");
        $this->menu('member', 'profile');
        
        $profile = desanitize($this->member->get());
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div class="profile">
            <form id="profile" method="post" onSubmit="return false;">
                <table class="profile_form">
                    <tr>
                        <td  class="buttons_bar" colspan="2"><input type="button" id="save" value="Save &amp; Update Profile" /></td>
                    </tr>
                    <tr>
                        <td class="label">First Name / Given Names:</td>
                        <td class="field"><?php echo $profile[0]['firstname']; ?></td>
                    </tr>
                    <tr>
                        <td class="label">Last Name / Surname:</td>
                        <td class="field"><?php echo $profile[0]['lastname']; ?></td>
                    </tr>
                    <tr>
                        <td class="title" colspan="2">Expertise</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="primary_industry">Primary/Majoring Specialization:</label></td>
                        <td class="field">
                            <?php $this->generate_industries('primary_industry', $profile[0]['primary_industry']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="secondary_industry">Secondary/Minoring Specialization:</label></td>
                        <td class="field">
                            <?php $this->generate_industries('secondary_industry', $profile[0]['secondary_industry']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="tertiary_industry">Tertiary/Minoring Specialization:</label></td>
                        <td class="field">
                            <?php 
                                $this->generate_industries('tertiary_industry', $profile[0]['tertiary_industry']); 
                                
                                if (empty($profile[0]['primary_industry']) ||
                                    is_null($profile[0]['primary_industry']) ||
                                    empty($profile[0]['secondary_industry']) ||
                                    is_null($profile[0]['secondary_industry']) || 
                                    empty($profile[0]['tertiary_industry']) ||
                                    is_null($profile[0]['tertiary_industry'])) {
                                    ?>
                            <p id="note" style="font-style: italic; color: #666666; font-size: 9pt;">Please choose your primary and secondary industrial sector. If you are a fresh graduate, just choose the industry that is closest to your majoring. If you consider yourself to be an expert in only one industry, select the same industry for both. We collect these information is to better understand the needs of our members.</p>
                                    <?php
                                } 
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="title" colspan="2">Sign In Details</td>
                    </tr>
                    <tr>
                        <td class="label">E-mail Address:</td>
                        <td class="field">
                            <input id="email_addr" type="hidden" value="<?php echo $profile[0]['email_addr']; ?>" />
                            <?php echo $profile[0]['email_addr']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="password">New Password:</label></td>
                        <td class="field"><input class="field" type="password" id="password" name="password" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="password_confirm">Confirm New Password:</label></td>
                        <td class="field"><input class="field" type="password" id="password_confirm" name="password_confirm" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="forget_password_question">Forgot password question:</label></td>
                        <td class="field">
                            <?php $this->generate_password_reset_questions($profile[0]['forget_password_question']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="forget_password_answer">Forgot passsword answer:</label></td>
                        <td class="field"><input class="field" type="text" id="forget_password_answer" name="forget_password_answer" value="<?php echo $profile[0]['forget_password_answer'] ?>" /></td>
                    </tr>
                    <tr>
                        <td class="title" colspan="2">Contact Details</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="phone_num">Telephone Number:</label></td>
                        <td class="field"><input class="field" type="text" id="phone_num" name="phone_num" value="<?php echo $profile[0]['phone_num']; ?>" maxlength="20" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="address">Mailing Address:</label></td>
                        <td class="field"><textarea id="address" name="address"><?php echo $profile[0]['address']; ?></textarea></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="state">State/Province:</label></td>
                        <td class="field"><input class="field" type="text" id="state" name="state" value="<?php echo $profile[0]['state']; ?>" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="zip">Zip/Postal Code:</label></td>
                        <td class="field"><input class="field" type="text" id="zip" name="zip" value="<?php echo $profile[0]['zip']; ?>" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="country">Country:</label></td>
                        <td class="field">
                            <?php $this->generateCountries($profile[0]['country']) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="title" colspan="2">Weekly Highlights Preferences</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding-left: 25%; padding-top: 15px; padding-bottom: 15px;">
                            <?php
                                if ($profile[0]['like_newsletter'] == 'Y') {
                                    ?><input type="checkbox" id="like_newsletter" name="like_newsletter" checked><?php
                                } else {
                                    ?><input type="checkbox" id="like_newsletter" name="like_newsletter"><?php
                                }
                            ?>
                            &nbsp;
                            <label for="like_newsletter">Get Weekly Highlights of Latest Jobs To Refer To Your Contacts:</label>
                            <br/>
                            <?php
                                if ($profile[0]['like_newsletter'] == 'Y') {
                                    if ($profile[0]['filter_jobs'] == 'Y') {
                                        ?><input type="checkbox" id="filter_jobs" name="filter_jobs" checked><?php
                                    } else {
                                        ?><input type="checkbox" id="filter_jobs" name="filter_jobs"><?php
                                    }
                                } else {
                                    ?><input type="checkbox" id="filter_jobs" name="filter_jobs" disabled><?php
                                }
                            ?>
                            &nbsp;
                            <label for="filter_jobs">Filter Weekly Highlights to Only my Primary and Secondary Specilizations:</label>
                        </td>
                    </tr>
                    <tr>
                        <td class="title" colspan="2">Reward Payment Details</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="phone_num">Bank Accounts:</label></td>
                        <td class="field">
                            <a href="banks.php">Click here to create/update your bank accounts.</a><br/>
                            <span style="font-style: italic; font-size: 9pt;">
                                With your bank account details, you can directly receive your rewards from us for every successful job referral you make.
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table class="buttons">
                                <tr>
                                    <td class="left"><a class="no_link" onClick="show_unsubscribe_form();">Unsubscribe</a></td>
                                    <td class="right">
                                        <span id="confirm_profile_form">
                                            <input type="checkbox" id="confirm_profile" onClick="checked_profile();" /><label for="confirm_profile">I verified that my profile is correct.</label>
                                            &nbsp;&nbsp;&nbsp;
                                        </span>
                                        <input type="button" id="save_1" value="Save &amp; Update Profile" />
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_unsubscribe_form">
            <form onSubmit="return false;">
                <p><label for="reason">Please tell us briefly why do you decide to unsubscribe from Yellow Elevator?</label></p>
                <p><textarea id="reason" name="reason"></textarea></p>
                <p class="button"><input type="button" value="Cancel" onClick="close_unsubscribe_form();" />&nbsp;<input type="button" value="Unsubscribe" onClick="unsubscribe();" /></p>
            </form>
        </div>
        <?php
    }
}
?>