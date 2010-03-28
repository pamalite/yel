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
        echo 'var email_addr = "'. $this->member->getId(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function generate_countries($_selected) {
        $criteria = array(
            'columns' => "country_code, country", 
            'order' => "country"
        );
        $countries = Country::find($criteria);
        
        echo '<select class="field" id="country" name="country">'. "\n";
        
        foreach ($countries as $country) {
            if ($country['country_code'] != $_selected) {
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
    
    private function generate_industries($_id, $_selected) {
        $mysqli = Database::connect();
        $query = "SELECT * FROM industries";
        $industries = $mysqli->query($query);
        
        echo '<select class="field" id="'. $_id. '" name="'. $_id. '">'. "\n";
        
        if (empty($_selected) || is_null($_selected) || $_selected == '0') {
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
        $this->top_search("Profile");
        $this->menu('member', 'profile');
        
        $profile = desanitize($this->member->get());
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_tabs">
            <ul>
                <li id="li_profile">Profile</li>
                <li id="li_bank">Bank</li>
                <li id="li_highlights">Highlights</li>
            </ul>
        </div>
        
        <div class="profile">
            <form id="profile" method="post" onSubmit="return false;">
                <div class="profile_photo_area">
                    <div class="photo">
                    <?php
                    if ($this->member->hasPhoto()) {
                    ?>
                        <img class="the_photo" src="candidate_photo.php?id=<?php echo $this->member->getId(); ?>" widt="200" height="220" />
                    <?php
                    } else {
                    ?>
                        Upload your photo here by clicking the "Upload Button" below.<br/><br/>
                        The dimension of your photo should be at most 200px wide &amp; 220px high, and filled most parts of this box.<br/><br/>
                        The size of your photo is normal than 500KB, and can be a JPEG, PNG, BMP or GIF format.<br/><br/>
                        Tip: If you see scroll bars after you have uploaded your photo, that means your photo is too big.
                    <?php
                    }
                    ?>
                    </div>
                    <div class="upload_button">
                        <input type="button" value="Upload Photo" onClick="show_upload_photo_window();" />
                    </div>
                </div>
                
                <table class="profile_form">
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
                            <?php $this->generate_countries($profile[0]['country']) ?>
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
                        <td colspan="2">
                            <table class="buttons">
                                <tr>
                                    <td class="left"><a class="no_link" onClick="show_unsubscribe_form();">Remove My Account</a></td>
                                    <td class="right">
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