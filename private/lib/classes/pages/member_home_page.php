<?php
require_once dirname(__FILE__). "/../../utilities.php";

class MemberHomePage extends Page {
    private $member = NULL;
    private $mysqli = NULL;
    
    function __construct($_session) {
        $this->member = new Member($_session['id'], $_session['sid']);
        $this->mysqli = Database::connect();
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_home_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/member_home_page.css">'. "\n";
    }
    
    public function insert_member_home_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/member_home_page.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->member->getId(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function get_completeness() {
        $query = "SELECT members.checked_profile, bank.has_bank, cv.has_resume, photo.has_photo 
                  FROM members, 
                  (SELECT COUNT(*) AS has_bank FROM member_banks WHERE member = '". $_POST['id']. "') bank, 
                  (SELECT COUNT(*) AS has_resume FROM resumes WHERE member = '". $_POST['id']. "' AND deleted = 'N') cv, 
                  (SELECT COUNT(*) AS has_photo FROM member_photos WHERE member = '". $_POST['id']. "') photo 
                  WHERE members.email_addr = '". $this->member->getId(). "'";
        $result = $this->mysqli->query($query);
        
        $response = array();
        $response['checked_profile'] = ($result[0]['checked_profile'] == 'Y') ? '1' : '0';
        $response['has_bank'] = ($result[0]['has_bank'] > 0) ? '1' : '0';
        $response['has_resume'] = ($result[0]['has_resume'] > 0) ? '1' : '0';
        $response['has_photo'] = ($result[0]['has_photo'] > 0) ? '1' : '0';
        
        return $response;
    }
    
    private function is_hrm_questions_filled() {
        $criteria = array(
            'columns' => "hrm_gender, hrm_ethnicity, hrm_birthdate", 
            'match' => "email_addr = '". $this->member->getId(). "'", 
            'limit' => "1"
        );
        
        $result = $this->member->find($criteria);
        if ((is_null($result[0]['hrm_gender']) || empty($result[0]['hrm_gender'])) ||
            (is_null($result[0]['hrm_ethnicity']) || empty($result[0]['hrm_ethnicity'])) || 
            (is_null($result[0]['hrm_birthdate']) || empty($result[0]['hrm_birthdate']))) {
            return false;
        }
        
        return true;
    }
    
    private function get_answers() {
        $criteria = array(
            'columns' => "is_active_seeking_job, seeking, expected_salary, 
                          expected_salary_end, can_travel_relocate, reason_for_leaving, 
                          current_position, current_salary, current_salary_end, 
                          notice_period", 
            'match' => "email_addr = '". $this->member->getId(). "'", 
            'limit' => "1"
        );
        
        $result = $this->member->find($criteria);
        return $result[0];
    }
    
    public function show() {
        $this->begin();
        $this->top_search('Home');
        $this->menu('member', 'home');
        
        $currency = Currency::getSymbolFromCountryCode($this->member->getCountry());
        $completeness_raw = $this->get_completeness();
        $completeness_percent = 0;
        $next_step = '';
        $total = 0;
        foreach ($completeness_raw as $key=>$value) {
            $total += $value;
            $completeness_percent = ($total / count($completeness_raw)) * 100;
            
            if ($value == 0 && empty($next_step)) {
                switch ($key) {
                    case 'checked_profile':
                        $next_step = '<a href="profile.php">Check Your Profile</a>';
                        break;
                    case 'has_bank':
                        $next_step = '<a href="profile.php">Enter a bank account in Profile</a>';
                        break;
                    case 'has_resume':
                        $next_step = '<a href="resumes.php">Upload a Resume</a>';
                        break;
                    case 'has_photo':
                        $next_step = '<a href="profile.php">Upload a photo in Profile</a>';
                        break;
                }
            }
        }
        
        $display_hrm_questions = 'display: none;';
        if (!$this->is_hrm_questions_filled()) {
            $display_hrm_questions = 'display: block;';
        }
        
        $answers = $this->get_answers();
        $is_active = ($answers['is_active_seeking_job'] == '1') ? true : false;
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <table class="content">
            <tr>
                <td class="left_content">        
                    <div id="div_hrm_census" style="<?php echo $display_hrm_questions; ?>">
                        <div class="census_title">One-time Survey</div>
                        <div class="census_form">
                            Please help us answer the following <span style="text-decoration: underline; font-weight: bold;">one-time</span> questions as part of our on-going effort to serve you better.<br/>
                            <ol>
                                <li>
                                    Gender: 
                                    <select id="gender">
                                        <option value="">Please select one</option>
                                        <option value="" disabled>&nbsp;</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </li>
                                <li>
                                    Ethnicity:
                                    <select id="ethnicity">
                                        <option value="">Please select one</option>
                                        <option value="" disabled>&nbsp;</option>
                                        <option value="malay">Malay</option>
                                        <option value="chinese">Chinese</option>
                                        <option value="indian">Indian</option>
                                        <option value="caucasian">Caucasian</option>
                                        <option value="other">Other (please specify)</option>
                                    </select>
                                    <input type="text" id="ethnicity_txt" alt="specify only when Other is selected" value="" />
                                </li>
                                <li>
                                    Birth Date:
                                    <?php echo generate_dropdown('birthdate_day', '', 1, 31, '', 2, 'Day'); ?>
                                    <?php echo generate_month_dropdown('birthdate_month', '', 'Month'); ?>
                                    <input type="text" class="year" id="birthdate_year" alt="year" maxlength="4" value="" />
                                </li>
                            </ol>
                        </div>
                        <div class="buttons">
                            <input type="button" value="Save &amp; Close Forever" onClick="save_census_answers();" />
                        </div>
                    </div>

                    <div class="profile_completeness">
                        <div class="completeness_title">Profile Completeness:</div>
                        <div class="progress">
                            <div id="progress_bar" style="width: <?php echo $completeness_percent; ?>%;"></div>
                        </div>
                        <div id="percent"><?php echo $completeness_percent; ?>%</div>
                        <div class="progress_details">
                            Tip: <span id="details"><?php echo $next_step; ?></span>
                        </div>
                    </div>
                    
                    <div class="profile">
                        <div class="profile_title">Quick Profile</div>
                        <div class="profile_form">
                            Please help us answer the following questions as part of our on-going effort to understand you better. You may also update youe answers if necessary.<br/>
                            <table class="profile_form_table">
                                <tr>
                                    <td class="field"><label for="is_seeking_job">Are you actively seeking for a new job or experience?</label></td>
                                    <td>
                                        <select id="is_seeking_job" onChange="toggle_the_rest_of_form();">
                                        <?php
                                        if ($is_active) {
                                        ?>
                                            <option value="1" selected>Yes</option>
                                            <option value="0">No</option>
                                        <?php
                                        } else {
                                        ?>
                                            <option value="1">Yes</option>
                                            <option value="0" selected>No</option>
                                        <?php
                                        }
                                        ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field odd"><label for="seeking">Briefly, what sort of job or experience are you seeking?</label></td>
                                    <td class="odd">
                                        <?php
                                        if ($is_active) {
                                        ?>
                                            <textarea id="seeking"><?php echo $answers['seeking']; ?></textarea>
                                        <?php
                                        } else {
                                        ?>
                                            <textarea id="seeking" disabled></textarea>
                                        <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field"><label for="expected_salary">What will be your expected salary range?</label></td>
                                    <td>
                                        <?php echo $currency. '$&nbsp;'; ?>
                                        <?php 
                                        if ($is_active) {
                                        ?>
                                            <input type="input" id="expected_salary" value="<?php echo $answers['expected_salary']; ?>" /> 
                                            to
                                            <input type="input" id="expected_salary_end" value="<?php echo $answers['expected_salary_end']; ?>"/>
                                        <?php
                                        } else {
                                        ?>
                                            <input type="input" id="expected_salary" value="" disabled /> 
                                            to
                                            <input type="input" id="expected_salary_end" value="" disabled />
                                        <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field odd"><label for="can_travel_relocate">Perhaps you can travel, or relocate, if the new job requires it?</label></td>
                                    <td class="odd">
                                        <?php
                                        if ($is_active) {
                                        ?>
                                            <select id="can_travel_relocate">
                                            <?php
                                            if ($answers['can_travel_relocate'] == 'Y') {
                                            ?>
                                                <option value="1" selected>Yes</option>
                                                <option value="0">No</option>
                                            <?php
                                            } else {
                                            ?>
                                                <option value="1">Yes</option>
                                                <option value="0" selected>No</option>
                                            <?php
                                            }
                                            ?>
                                            </select>
                                        <?php
                                        } else {
                                        ?>
                                            <select id="can_travel_relocate" disabled>
                                                <option value="1" selected>Yes</option>
                                                <option value="0">No</option>
                                            </select>
                                        <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field"><label for="reason_for_leaving">Briefly, why do you want to leave your current job?</label></td>
                                    <td>
                                        <?php
                                        if ($is_active) {
                                        ?>
                                            <textarea id="reason_for_leaving"><?php echo $answers['reason_for_leaving']; ?></textarea>
                                        <?php
                                        } else {
                                        ?>
                                            <textarea id="reason_for_leaving" disabled></textarea>
                                        <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field odd"><label for="current_position">Briefly, what is your current position, and what do you do?</label></td>
                                    <td class="odd">
                                        <?php
                                        if ($is_active) {
                                        ?>
                                            <textarea id="current_position"><?php echo $answers['current_position']; ?></textarea>
                                        <?php
                                        } else {
                                        ?>
                                            <textarea id="current_position" disabled></textarea>
                                        <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field"><label for="current_salary">What is your current salary range?</label></td>
                                    <td>
                                        <?php echo $currency. '$&nbsp;'; ?>
                                        <?php 
                                        if ($is_active) {
                                        ?>
                                            <input type="input" id="current_salary" value="<?php echo $answers['current_salary']; ?>" /> 
                                            to
                                            <input type="input" id="current_salary_end" value="<?php echo $answers['current_salary_end']; ?>"/>
                                        <?php
                                        } else {
                                        ?>
                                            <input type="input" id="current_salary" value="" disabled /> 
                                            to
                                            <input type="input" id="current_salary_end" value="" disabled />
                                        <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field odd"><label for="notice_period">What is your notice period?</label></td>
                                    <td class="odd">
                                        <?php
                                        if ($is_active) {
                                        ?>
                                            <input type="text" class="notice_period" id="notice_period" value="<?php echo $answers['notice_period']; ?>" />
                                        <?php
                                        } else {
                                        ?>
                                            <input type="text" class="notice_period" id="notice_period" value="" disabled />
                                        <?php
                                        }
                                        ?>
                                        months
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="buttons">
                            <input type="button" value="Save" onClick="save_answers();" />
                        </div>
                    </div>
                </td>
                <td class="right_content">
                    <div class="quick_search">
                        <div class="quick_search_title">Quick Search</div>
                        <ul class="quick_search_list">
                            <li><a class="no_link" onClick="quick_search_jobs('latest');">Latest jobs</a></li>
                            <li><a class="no_link" onClick="quick_search_jobs('top');">Top jobs</a></li>
                            <li>
                                <a class="no_link" onClick="quick_search_jobs('country', '<?php echo $this->member->getCountry(); ?>');">Jobs in <?php echo Country::getCountryFrom($this->member->getCountry()); ?></a>
                            </li>
                            <li>
                                Jobs in salary range:
                                <ul class="quick_search_list_inner">
                                    <li><a class="no_link" onClick="quick_search_jobs('salary', 8001, 0);">above <?php echo $currency; ?>$ 8,000</a></li>
                                    <li><a class="no_link" onClick="quick_search_jobs('salary', 7001, 8000);"><?php echo $currency; ?>$ 7,000 - 8,000</a></li>
                                    <li><a class="no_link" onClick="quick_search_jobs('salary', 6001, 7000);"><?php echo $currency; ?>$ 6,000 - 7,000</a></li>
                                    <li><a class="no_link" onClick="quick_search_jobs('salary', 5001, 6000);"><?php echo $currency; ?>$ 5,000 - 6,000</a></li>
                                    <li><a class="no_link" onClick="quick_search_jobs('salary', 4001, 5000);"><?php echo $currency; ?>$ 4,000 - 5,000</a></li>
                                    <li><a class="no_link" onClick="quick_search_jobs('salary', 3000, 4000);"><?php echo $currency; ?>$ 3,000 - 4,000</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        </table>
        
        <?php
    }
}
?>