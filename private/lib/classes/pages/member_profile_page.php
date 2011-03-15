<?php
require_once dirname(__FILE__). "/../../utilities.php";

class MemberProfilePage extends Page {
    private $member = NULL;
    private $error_message = '';
    
    function __construct($_session) {
        parent::__construct();
        
        $this->member = new Member($_session['id'], $_session['sid']);
    }
    
    public function set_error($_error) {
        switch ($_error) {
            case '1':
                $this->error_message = 'An error occured when trying to upload your photo.\\n\\nPlease try again later. Please make sure that the file you are uploading is listed in the resume upload window.\\n\\nIf problem persist, please contact our technical support for further assistance.';
                break;
            default:
                $this->error_message = '';
        }
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_profile_css() {
        $this->insert_css('member_profile.css');
    }
    
    public function insert_member_profile_scripts() {
        $this->insert_scripts('member_profile.js');
    }
    
    public function insert_inline_scripts() {
        $script = 'var id = "'. $this->member->getId(). '";'. "\n";
        
        if (!empty($this->error_message)) {
            $script .= "alert(\"". $this->error_message. "\");\n";
        }
        
        $this->header = str_replace('<!-- %inline_javascript% -->', $script, $this->header);
    }
    
    private function generate_countries($_selected, $_name = 'country') {
        $criteria = array(
            'columns' => "country_code, country", 
            'order' => "country"
        );
        $countries = Country::find($criteria);
        
        echo '<select class="field" id="'. $_name. '" name="'. $_name. '">'. "\n";
        
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
        
        foreach ($questions as $question) {
            if ($question['id'] != $_selected) {
                echo '<option value="'. $question['id']. '">'. $question['question']. '</option>'. "\n";
            } else {
                echo '<option value="'. $question['id']. '" selected>'. $question['question']. '</option>'. "\n";
            }
        }
        
        echo '</select>'. "\n";
    }
    
    private function generate_industries($_id) {
        $expertises = $this->member->getIndustries();
        
        $criteria = array('columns' => "id, industry, parent_id");
        $industries = Industry::find($criteria);
        
        echo '<select class="multiselect" id="'. $_id. '" name="'. $_id. '" multiple>'. "\n";
        
        foreach ($industries as $industry) {
            $css_class = '';
            $spacing = '';
            if (is_null($industry['parent_id'])) {
                $css_class = 'class = "main_industry"';
            } else {
                $spacing = '&nbsp;&nbsp;&nbsp;';
            }
            
            $selected = false;
            foreach ($expertises as $expertise) {
                if ($expertise['id'] == $industry['id']) {
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
    
    public function show() {
        $this->begin();
        $this->top_search("Profile");
        $this->menu('member', 'profile');
        
        $profile = desanitize($this->member->get());
        $bank = $this->member->getBankAccount();
        if (empty($bank) || $bank === false) {
            $bank[0]['id'] = 0;
            $bank[0]['bank'] = '';
            $bank[0]['account'] = '';
        }
        
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
        
        <div id="profile" class="profile">
            <form id="profile_form" method="post" onSubmit="return false;">
                <!-- div class="profile_photo_area">
                    <div class="photo">
                    <?php
                    if ($this->member->hasPhoto()) {
                    ?>
                        <img id="photo_image" class="photo_image" src="candidate_photo.php?id=<?php echo $this->member->getId(); ?>" />
                    <?php
                    } else {
                    ?>
                        <div style="text-align: center; margin: auto;">
                            Upload your photo here by clicking the "Upload Photo" button.
                        </div>
                    <?php
                    }
                    ?>
                    </div>
                    <div class="upload_button">
                        <input type="button" value="Upload Photo" onClick="show_upload_photo_popup();" />
                    </div>
                </div -->
                
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
                        <td class="label">Nationality:</td>
                        <td class="field">
                            <?php $this->generate_countries($profile[0]['citizenship'], 'citizenship'); ?>
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
                    
                    <!-- expertise -->
                    <!-- tr>
                        <td class="title" colspan="2">Top 3 Specializations</td>
                    </tr>
                    <tr>
                        <td class="specializations" colspan="2">
                            <div class="note">Please choose your top 3 industrial sector. We collect these information is to better understand the needs of our members.</div>
                            <?php $this->generate_industries('industry'); ?>
                        </td>
                    </tr -->
                    <!-- expertise -->
                    
                    <tr>
                        <td colspan="2">
                            <div class="buttons buttons_left">
                                <a class="no_link" onClick="show_unsubscribe_popup();">Remove My Account</a>
                            </div>
                            <div class="buttons buttons_right">
                                <input type="button" id="save" value="Save &amp; Update Profile" onClick="save_profile();" />
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        
        <div id="bank" class="bank">
            <input type="hidden" id="bank_id"value="<?php echo $bank[0]['id']; ?>" />
            <table class="profile_form">
                <tr>
                    <td class="title" colspan="2">Bank Account Information</td>
                </tr>
                <tr>
                    <td class="label"><label for="bank">Bank:</label></td>
                    <td class="field"><input class="field" type="text" id="bank_name" name="bank_name" value="<?php echo $bank[0]['bank']; ?>" /></td>
                </tr>
                <tr>
                    <td class="label"><label for="account">Account Number:</label></td>
                    <td class="field"><input class="field" type="text" id="account" name="account" value="<?php echo $bank[0]['account']; ?>" /></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="buttons buttons_right">
                            <input type="button" id="save" value="Save &amp; Update Profile" onClick="save_bank();" />
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="highlights" class="highlights">
            <table class="profile_form">
                <tr>
                    <td class="title" colspan="2">Weekly Highlights Preferences</td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-left: 15px; padding-top: 15px; padding-bottom: 15px;">
                        <?php
                            if ($profile[0]['like_newsletter'] == 'Y') {
                                ?><input type="checkbox" id="like_newsletter" name="like_newsletter" checked><?php
                            } else {
                                ?><input type="checkbox" id="like_newsletter" name="like_newsletter"><?php
                            }
                        ?>
                        &nbsp;
                        <label for="like_newsletter">Get Weekly Highlights of Latest Jobs To Refer To Your Contacts</label>
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
                        <label for="filter_jobs">Filter Weekly Highlights to Only my Primary and Secondary Specilizations</label>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="buttons buttons_right">
                            <input type="button" id="save" value="Save &amp; Update Profile" onClick="save_highlights();" />
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- popup windows go here -->
        <div id="upload_photo_window" class="popup_window">
            <div class="popup_window_title">Upload Photo</div>
            <form id="upload_photo_form" action="profile_action.php" method="post" enctype="multipart/form-data" onSubmit="return close_upload_photo_popup(true);">
                <div class="upload_photo_form">
                    <br/>
                    <input type="hidden" name="id" value="<?php echo $this->member->getId(); ?>" />
                    <input type="hidden" name="action" value="upload" />
                    <div id="upload_progress" style="text-align: center; width: 99%; margin: auto;">
                        Please wait while your photo is being uploaded... <br/><br/>
                        <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/progress/circle_big.gif" /><br/><br/>
                        NOTE: To Safari/Chrome (WebKit) on Mac OS X users, the mentioned browsers have a problem uploading any file through this page. Please try Firefox to upload your resume.
                    </div>
                    <div id="upload_field" class="upload_field">
                        <input id="my_file" name="my_file" type="file" />
                        <div style="font-size: 9pt; margin-top: 15px;">
                            <ol>
                                <li>Only GIF (*.gif), JPEG (*.jpg, *.jpeg), Portable Network Graphics (*.png), TIFF (*.tiff) or Bitmap (*.bmp) with the file size of less than 150KB are allowed.</li>
                                <li>Maximum photo resolution is 200 (width) x 220 (height) pixels.</li>
                                <li>You can update your photo by uploading a new one.</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="popup_window_buttons_bar">
                    <input type="submit" value="Upload Photo" />
                    <input type="button" value="Close" onClick="close_upload_photo_popup(false);" />
                </div>
            </form>
        </div>
        
        <div id="unsubscribe_window" class="popup_window">
            <div class="popup_window_title">Remove My Account</div>
            <div class="unsubscribe_form">
                <form onSubmit="return false;">
                    <label for="reason">Please tell us briefly why do you decide to unsubscribe from Yellow Elevator?</label>
                    <textarea id="reason" name="reason"></textarea>
                </form>
            </div>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Unsubscribe" onClick="close_unsubscribe_popup(true);" />
                <input type="button" value="Cancel" onClick="close_unsubscribe_popup(false);" />
            </div>
        </div>
        
        <?php
    }
}
?>