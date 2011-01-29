<?php
require_once dirname(__FILE__). '/../../../config/job_profile.inc';
require_once dirname(__FILE__). "/../../utilities.php";

class MemberSignUpPage extends Page {
    private $member = '';
    private $error_message = '';
    
    function __construct($_member = '') {
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
        echo '</script>'. "\n";
    }
    
    private function generate_countries($_selected, $_name = 'country') {
        $criteria = array(
            'columns' => "country_code, country", 
            'order' => "country"
        );
        $countries = Country::find($criteria);
        
        echo '<select class="field" id="'. $_name. '" name="'. $_name. '">'. "\n";
        if (empty($_selected) || is_null($_selected) || $_selected == '0') {
            echo '<option value="0" selected>Please select a county.</option>'. "\n";    
        } else {
            echo '<option value="0">Please select a country.</option>'. "\n";
        }
        
        echo '<option value="0">&nbsp;</option>';
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
    
    private function generate_industries($_id, $_selecteds, $_is_multi=false) {
        $criteria = array('columns' => "id, industry, parent_id");
        $industries = Industry::find($criteria);
        
        if ($_is_multi) {
            echo '<select class="multiselect" id="'. $_id. '" name="'. $_id. '[]" multiple>'. "\n";
        } else {
            echo '<select class="field" id="'. $_id. '" name="'. $_id. '">'. "\n";
        }
        
        $options_str = '';
        $has_selected = false;
        foreach ($industries as $industry) {
            $css_class = '';
            $spacing = '';
            if (is_null($industry['parent_id'])) {
                $css_class = 'class = "main_industry"';
            } else {
                $spacing = '&nbsp;&nbsp;&nbsp;';
            }
            
            $selected = false;
            if (in_array($industry['id'], $_selecteds)) {
                $selected = true;
                $has_selected = true;
            }
            
            if ($selected) {
                $options_str .= '<option value="'. $industry['id']. '" '. $css_class. ' selected>'. $spacing. $industry['industry']. '</option>'. "\n";
            } else {
                $options_str .= '<option value="'. $industry['id']. '" '. $css_class. '>'. $spacing. $industry['industry']. '</option>'. "\n";
            }
        }
        
        echo '<option value="0" '. (($has_selected) ? '' : 'selected'). '>Select a Specialization</option>'. "\n";
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        echo $options_str;
        echo '</select>'. "\n";
    }
    
    private function generate_employer_description($_id, $_selected) {
        $descs = $GLOBALS['emp_descs'];
        
        echo '<select class="field" id="'. $_id. '" name="'. $_id. '">'. "\n";
        if (empty($_selected) || is_null($_selected) || $_selected < 0) {
            echo '<option value="0" selected>Please select one</option>'. "\n";    
        } else {
            echo '<option value="0">Please select One</option>'. "\n";
        }
        
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        foreach ($descs as $i=>$desc) {
            if ($i != $_selected) {
                echo '<option value="'. $i. '">'. $desc. '</option>'. "\n";
            } else {
                echo '<option value="'. $i. '" selected>'. $desc. '</option>'. "\n";
            }
        }
        
        echo '</select>'. "\n";
    }
    
    public function show($_session) {
        $this->begin();
        $this->top("Member Sign Up");
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>

        <div id="div_sign_up">
            <form id="profile">
                <table class="profile_form">
                    <tr>
                        <td class="instruction" colspan="2">Please fill up <span style="font-weight: bold; text-decoration: underline;">ALL</span> the fields.</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="firstname">First Name:</label></td>
                        <td class="field"><input class="field" type="text" id="firstname" name="firstname" alt="Jane" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="lastname">Last Name:</label></td>
                        <td class="field">
                            <input class="field" type="text" id="lastname" name="lastname" alt="Doe" />
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="phone_num">Telephone Number:</label></td>
                        <td class="field"><input class="field" type="text" id="phone_num" name="phone_num" alt="123-1234 5678" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="email_addr">E-mail Address:</label></td>
                        <td class="field">
                            <input class="field" type="text" id="email_addr" name="email_addr" alt="jane.doe@acme.com" />
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="password">Password:</label></td>
                        <td class="field"><input class="field" type="password" id="password" name="password" alt="create a password" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="password_confirm">Confirm Password:</label></td>
                        <td class="field"><input class="field" type="password" id="password_confirm" name="password_confirm" alt="type the password again" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="forget_password_question">Password hint:</label></td>
                        <td class="field">
                            <?php $this->generate_password_reset_questions(0); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="forget_password_answer">Password hint answer:</label></td>
                        <td class="field"><input class="field" type="text" id="forget_password_answer" name="forget_password_answer" /></td>
                    </tr>
                    <tr>
                        <td class="label">&nbsp;</td>
                        <td class="field">
                            <script type="text/javascript"
                                 src="http://www.google.com/recaptcha/api/challenge?k=6LdwqsASAAAAAJuZpFkYJo6a0-QAJET_OafO3n6D">
                              </script>
                              <noscript>
                                 <iframe src="http://www.google.com/recaptcha/api/noscript?k=6LdwqsASAAAAAJuZpFkYJo6a0-QAJET_OafO3n6D"
                                     height="300" width="500" frameborder="0" id="recaptcha"></iframe><br>
                                 <textarea name="recaptcha_challenge_field" rows="3" cols="40">
                                 </textarea>
                                 <input type="hidden" name="recaptcha_response_field"
                                     value="manual_challenge">
                              </noscript>
                        </td>
                    </tr>
                    <tr>
                        <td class="buttons_bar" colspan="2">
                            <input type="checkbox" id="agreed_terms" name="agreed_terms" />&nbsp;<span style="font-size: 10pt;vertical-align: middle;"><label for="agreed_terms">I have read, understood and accepted the <a target="_new" href="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/terms.php">Terms of Use of Yellow Elevator</a>.</label></span>&nbsp;<input type="button" id="save" onClick="sign_up();" value="Sign Me Up!" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        
        <div id="div_job_profile">
            <form id="job_profile">
                <input type="hidden" id="member_email_addr" value="" />
                <table class="job_profile_form">
                    <tr>
                        <td class="instruction" colspan="2">
                            Congratulations! You are now signed up with YellowElevator.com. <br/>
                            An activation email had been send to your email inbox. Please follow the instructions in the email to fully activate your account.<br/>
                            In the mean time, please help our Recruitment Consultants identify suitable opportunities for you. <br/>
                            <br/>
                            Please fill up <span style="font-weight: bold; text-decoration: underline;">ALL</span> the fields to your best of knowledge.
                        </td>
                    </tr>
                    <tr>
                        <td class="section_title" colspan="2">
                            Current Job Profile
                        </td>
                    </tr>
                    <!-- tr>
                        <td class="label"><label for="specialization">Specialization:</label></td>
                        <td class="field">
                            <?php $this->generate_industries('specialization', array()); ?>
                        </td>
                    </tr -->
                    <tr>
                        <td class="label"><label for="position_title">Job Title:</label></td>
                        <td class="field">
                            <input class="field" type="text" id="position_title" name="position_title" alt="eg: Finance Manager, Operations Director, etc." />
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="position_superior_title">Reporting to:</label></td>
                        <td class="field">
                            <input class="field" type="text" id="position_superior_title" name="position_superior_title" alt="eg: Financial Controller,  VP of Operations, etc." />
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="organization_size">Positions reporting to you:</label></td>
                        <td class="field"><input class="field" type="text" id="organization_size" name="organization_size" alt="eg: 2 Managers, 3 Specialists, and 5 Executives" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="company">Current Company:</label></td>
                        <td class="field"><input class="field" type="text" id="company" name="company" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="work_from_month">Duration at Current Company:</label></td>
                        <td class="field">
                            <?php echo generate_month_dropdown('work_from_month', ''); ?>
                            <input type="text" class="year" maxlength="4" id="work_from_year" alt="yyyy" />
                            to
                            <span id="work_to_dropdown">
                                <?php echo generate_month_dropdown('work_to_month', ''); ?>
                                <input type="text" class="year" maxlength="4" id="work_to_year" alt="yyyy" />
                            </span>
                            <input type="checkbox" id="work_to_present" onClick="toggle_work_to();" /> 
                            <label for="work_to_present">Present</label>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="emp_desc">Company Description:</label></td>
                        <td class="field">
                            <?php $this->generate_employer_description('emp_desc', -1); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="emp_specialization">Company Industry:</label></td>
                        <td class="field">
                            <?php $this->generate_industries('emp_specialization', array()); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="total_work_years">Total Years of Work Experience:</label></td>
                        <td class="field"><input class="field" type="text" id="total_work_years" name="total_work_years" /></td>
                    </tr>
                    <!-- tr>
                        <td class="label"><label for="pref_job_loc_1">Job Location Preferences:</label></td>
                        <td class="field">
                            Preference 1: 
                            <?php $this->generate_countries('', 'pref_job_loc_1'); ?>
                            Preference 2:
                            <?php $this->generate_countries('', 'pref_job_loc_2'); ?>
                        </td>
                    </tr -->
                    <tr>
                        <td class="label"><label for="seeking">Job Responsibilities &amp; Experience:</label></td>
                        <td class="field">
                            <textarea id="seeking" class="field">Brief our Recruitment Consulstants about your career goals and experiences, enough to help them identify suitable opportunities for your recommendation.</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td class="buttons_bar" colspan="2">
                            <input type="button" id="save" onClick="save_job_profile();" value="Finish" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }
}

?>