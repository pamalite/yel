<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). "/../../../config/job_profile.inc";
require_once dirname(__FILE__). "/../htmltable.php";

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
            'columns' => "members.is_active_seeking_job, members.seeking, 
                          members.expected_salary_currency, members.expected_salary, 
                          members.expected_salary_end, members.can_travel_relocate, 
                          members.reason_for_leaving, members.current_position, 
                          members.current_salary_currency, members.current_salary, 
                          members.current_salary_end, members.notice_period, 
                          countries.country AS pref_job_loc_1, countries_1.country AS pref_job_loc_2", 
            'joins' => "countries ON countries.country_code = members.preferred_job_location_1, 
                        countries AS countries_1 ON countries_1.country_code = members.preferred_job_location_2",
            'match' => "members.email_addr = '". $this->member->getId(). "'", 
            'limit' => "1"
        );
        
        $result = $this->member->find($criteria);
        
        $result[0]['seeking'] = htmlspecialchars_decode(stripslashes($result[0]['seeking']));
        $result[0]['reason_for_leaving'] = htmlspecialchars_decode(stripslashes($result[0]['reason_for_leaving']));
        $result[0]['current_position'] = htmlspecialchars_decode(stripslashes($result[0]['current_position']));
        
        $result[0]['seeking'] = str_replace("\n", '<br/>', $result[0]['seeking']);
        $result[0]['reason_for_leaving'] = str_replace("\n", '<br/>', $result[0]['reason_for_leaving']);
        $result[0]['current_position'] = str_replace("\n", '<br/>', $result[0]['current_position']);
        
        $result[0]['seeking'] = addslashes($result[0]['seeking']);
        $result[0]['reason_for_leaving'] = addslashes($result[0]['reason_for_leaving']);
        $result[0]['current_position'] = addslashes($result[0]['current_position']);
        
        return $result[0];
    }
    
    private function get_job_profiles() {
        $criteria = array(
            'columns' => "member_job_profiles.id, member_job_profiles.position_title, 
                          member_job_profiles.position_superior_title, 
                          member_job_profiles.employer, member_job_profiles.employer_description, 
                          industries.industry AS specialization, 
                          employer_industries.industry AS employer_specialization, 
                          DATE_FORMAT(member_job_profiles.work_from, '%b, %Y') AS formatted_work_from, 
                          DATE_FORMAT(member_job_profiles.work_to, '%b, %Y') AS formatted_work_to", 
            'joins' => "member_job_profiles ON member_job_profiles.member = members.email_addr, 
                        industries ON industries.id = member_job_profiles.specialization, 
                        industries AS employer_industries ON employer_industries.id = member_job_profiles.employer_specialization",
            'match' => "members.email_addr = '". $this->member->getId(). "'",
            'having' => "member_job_profiles.id IS NOT NULL",
            'order' => "work_from DESC"
        );
        
        $result = $this->member->find($criteria);
        if (is_null($result) || count($result) <= 0) {
            return array();
        }
        
        foreach ($result as $i=>$row) {
            $result[$i]['employer_description'] = $GLOBALS['emp_descs'][$row['employer_description']];
        }
        
        return $result;
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
    
    public function show() {
        $this->begin();
        $this->top_search('Home');
        $this->menu('member', 'home');
        
        $country = $this->member->getCountry();
        $currency = Currency::getSymbolFromCountryCode($country);
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
        
        $job_profiles = $this->get_job_profiles();
        
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
                        <div class="profile_title">Summary</div>
                        <div class="profile_form">
                            Please help us answer the following questions as part of our on-going effort to understand you better. You may also update youe answers if necessary.<br/>
                            <table class="profile_form_table">
                                <tr>
                                    <td class="field">Are you actively seeking for a new job or experience?</td>
                                    <td>
                                        <?php
                                        if ($is_active) {
                                        ?>
                                        Yes
                                        <?php
                                        } else {
                                        ?>
                                        No
                                        <?php    
                                        }
                                        ?>
                                    </td>
                                    <td class="action"><a class="no_link edit" onClick="show_choices_popup('Active Job Seeker?', 'Yes|No', '<?php echo ($is_active) ? 'Yes' : 'No'; ?>', 'save_is_active_job_seeker');">edit</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field odd">Briefly, tell us what are your goals and experiences.</td>
                                    <td class="odd">
                                        <span id="seeking_field">
                                        <?php
                                        if ($is_active) {
                                            echo $answers['seeking'];
                                        }
                                        ?>
                                        </span>
                                    </td>
                                    <td class="action">
                                        <span id="seeking_edit">
                                        <?php
                                        if ($is_active) {
                                        ?>
                                            <a class="no_link edit" onClick="show_notes_popup('Goals and Experiences', '<?php echo $answers['seeking']; ?>', 'save_seeking');">edit</a>
                                        <?php
                                        }
                                        ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field">What will be your expected salary range?</td>
                                    <td>
                                        <span id="expected_salary_field">
                                        <?php 
                                        if ($is_active) {
                                            echo $answers['expected_salary_currency']. '$&nbsp;';
                                            echo number_format($answers['expected_salary'], 2, '.', ' '). ' to '. number_format($answers['expected_salary_end'], 2, '.', ' ');
                                        }
                                        ?>
                                        </span>
                                    </td>
                                    <td class="action">
                                        <span id="expected_salary_edit">
                                        <?php
                                        if ($is_active) {
                                        ?>
                                            <a class="no_link edit" onClick="show_ranges_popup('Expected Salary Range', '<?php echo $answers['expected_salary']; ?>', '<?php echo $answers['expected_salary_end']; ?>', '<?php echo $answers['expected_salary_currency']; ?>', 'save_expected_salary');">edit</a>
                                        <?php
                                        }
                                        ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field odd" rowspan="2">Job Location Preferences?</td>
                                    <td class="odd">
                                        1. 
                                        <span id="pref_job_loc_1_field">
                                        <?php 
                                        if ($is_active) {
                                            echo $answers['pref_job_loc_1'];
                                        }
                                        ?>
                                        </span>
                                    </td>
                                    <td class="action">
                                        <span id="pref_job_loc_1_edit">
                                        <?php
                                        if ($is_active) {
                                        ?>
                                            <a class="no_link edit" onClick="show_countries_popup('Job Preference 1', '<?php echo $answers['pref_job_loc_1']; ?>', 'save_pref_job_loc_1');">edit</a>
                                        <?php
                                        }
                                        ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="odd">
                                        2. 
                                        <span id="pref_job_loc_2_field">
                                        <?php 
                                        if ($is_active) {
                                            echo $answers['pref_job_loc_2'];
                                        }
                                        ?>
                                        </span>
                                    </td>
                                    <td class="action">
                                        <span id="pref_job_loc_2_edit">
                                        <?php
                                        if ($is_active) {
                                        ?>
                                            <a class="no_link edit" onClick="show_countries_popup('Job Preference 2', '<?php echo $answers['pref_job_loc_2']; ?>', 'save_pref_job_loc_2');">edit</a>
                                        <?php
                                        }
                                        ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field">Perhaps you can travel, or relocate, if the new job requires it?</td>
                                    <td>
                                        <span id="travel_field">
                                        <?php
                                        if ($is_active) {
                                            echo ($answers['can_travel_relocate'] == 'Y') ? 'Yes' : 'No';
                                        }
                                        ?>
                                    </td>
                                    <td class="action">
                                        <span id="travel_edit">
                                        <?php
                                        if ($is_active) {
                                        ?>
                                            <a class="no_link edit" onClick="show_choices_popup('Can Travel/Relocate?', 'Yes|No', '<?php echo ($answers['can_travel_relocate'] == 'Y') ? 'Yes' : 'No'; ?>', 'save_travel_relocate');">edit</a>
                                        <?php
                                        }
                                        ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field odd">Briefly, why do you want to leave your current job?</td>
                                    <td class="odd">
                                        <span id="leaving_field">
                                        <?php
                                        if ($is_active) {
                                            echo $answers['reason_for_leaving'];
                                        }
                                        ?>
                                        </span>
                                    </td>
                                    <td class="action">
                                        <span id="leaving_edit">
                                        <?php
                                        if ($is_active) {
                                        ?>
                                            <a class="no_link edit" onClick="show_notes_popup('Reason for Leaving', '<?php echo $answers['reason_for_leaving']; ?>', 'save_reason_for_leaving');">edit</a>
                                        <?php
                                        }
                                        ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field">Briefly, what is your current position, and what do you do?</td>
                                    <td>
                                        <span id="current_job_field">
                                        <?php
                                        if ($is_active) {
                                            echo stripslashes($answers['current_position']);
                                        }
                                        ?>
                                        </span>
                                    </td>
                                    <td class="action">
                                        <span id="current_job_edit">
                                        <?php
                                        if ($is_active) {
                                        ?>
                                            <a class="no_link edit" onClick="show_notes_popup('Current Job Description', '<?php echo $answers['current_position']; ?>', 'save_current_job_desc');">edit</a>
                                        <?php
                                        }
                                        ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field odd">What is your current salary range?</td>
                                    <td class="odd">
                                        <span id="current_salary_field">
                                        <?php 
                                        if ($is_active) {
                                            echo $answers['current_salary_currency']. '$&nbsp;';
                                            echo number_format($answers['current_salary'], 2, '.', ' ') .' to '.  number_format($answers['current_salary_end'], 2, '.' , ' ');
                                        }
                                        ?>
                                        </span>
                                    </td>
                                    <td class="action">
                                        <span id="current_salary_edit">
                                        <?php
                                        if ($is_active) {
                                        ?>
                                            <a class="no_link edit" onClick="show_ranges_popup('Current Salary Range', '<?php echo $answers['current_salary']; ?>', '<?php echo $answers['current_salary_end']; ?>', '<?php echo $answers['current_salary_currency']; ?>', 'save_current_salary');">edit</a>
                                        <?php
                                        }
                                        ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field">What is your notice period?</td>
                                    <td>
                                        <span id="notice_period_field">
                                        <?php
                                        if ($is_active) {
                                            echo $answers['notice_period']. ' months';
                                        }
                                        ?>
                                        </span>
                                    </td>
                                    <td class="action">
                                        <span id="notice_period_edit">
                                        <?php
                                        if ($is_active) {
                                        ?>
                                            <a class="no_link edit" onClick="show_texts_popup('Notice Period (in Months)', '<?php echo $answers['notice_period']; ?>', 'save_notice_period');">edit</a>
                                        <?php
                                        }
                                        ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div id="job_profiles" class="profile">
                        <div class="profile_title">Positions Held &amp; Currently Holding</div>
                        <div class="buttons">
                            <input type="button" value="Add Position" onClick="show_job_profile_popup(0);" />
                        </div>
                        <div class="job_profiles">
                        <?php
                        if (empty($job_profiles)) {
                        ?>
                        <div class="empty_results">No positions found.</div>
                        <?php
                        } else {
                            $job_profiles_table = new HTMLTable('job_profiles_table', 'job_profiles');

                            $job_profiles_table->set(0, 0, '&nbsp;', '', 'header action');
                            $job_profiles_table->set(0, 1, 'From', '', 'header');
                            $job_profiles_table->set(0, 2, 'To', '', 'header');
                            $job_profiles_table->set(0, 3, 'Employer', '', 'header');
                            $job_profiles_table->set(0, 4, 'Position', '', 'header');
                            $job_profiles_table->set(0, 5, '&nbsp;', '', 'header action');

                            foreach ($job_profiles as $i => $job_profile) {
                                $job_profiles_table->set($i+1, 0, '<a class="no_link" onClick="delete_job_profile('. $job_profile['id']. ')">delete</a>', '', 'cell action');
                                $job_profiles_table->set($i+1, 1, $job_profile['formatted_work_from'], '', 'cell');
                                $work_to = $job_profile['formatted_work_to'];
                                if (is_null($work_to) || empty($work_to) || $work_to == '0000-00-00') {
                                    $work_to = 'Present';
                                }
                                $job_profiles_table->set($i+1, 2, $work_to, '', 'cell');
                                
                                $emp = htmlspecialchars_decode(stripslashes($job_profile['employer']));
                                $emp .= '<br/><span class="mini_spec">'. $job_profile['employer_specialization']. '</span><br/>';
                                $emp .= '<span class="mini_emp_desc">'. $job_profile['employer_description']. '</span><br/>';
                                $job_profiles_table->set($i+1, 3, $emp, '', 'cell');
                                
                                $pos = htmlspecialchars_decode(stripslashes($job_profile['position_title']));
                                $pos .= '<br/><span class="mini_spec">'. $job_profile['specialization']. '</span><br/>';
                                $pos .= '<span class="mini_superior">'. $job_profile['position_superior_title']. '</span>';
                                $job_profiles_table->set($i+1, 4, $pos, '', 'cell');
                                
                                $job_profiles_table->set($i+1, 5, '<a class="no_link" onClick="show_job_profile_popup('. $job_profile['id']. ')">edit</a>', '', 'cell action');
                            }
                            
                            echo $job_profiles_table->get_html();
                        }
                        ?>
                        </div>
                    </div>
                </td>
                <td class="right_content">
                    <div class="quick_search">
                        <div class="quick_search_title">Quick Search</div>
                        <ul class="quick_search_list">
                            <li><a href="../search.php?special=latest&country=<?php echo $country; ?>">Latest jobs</a></li>
                            <li><a href="../search.php?special=top&country=<?php echo $country; ?>">Top jobs</a></li>
                            <li>
                                <a href="../search.php?special=country&country=<?php echo $country; ?>">Jobs in <?php echo Country::getCountryFrom($country); ?></a>
                            </li>
                            <li>
                                Jobs in salary range:
                                <ul class="quick_search_list_inner">
                                    <li><a href="../search.php?special=salary&range=0&country=<?php echo $country; ?>">above <?php echo $currency; ?>$ 8,000</a></li>
                                    <li><a href="../search.php?special=salary&range=1&country=<?php echo $country; ?>">$ 7,000 - 8,000</a></li>
                                    <li><a href="../search.php?special=salary&range=2&country=<?php echo $country; ?>">$ 6,000 - 7,000</a></li>
                                    <li><a href="../search.php?special=salary&range=3&country=<?php echo $country; ?>">$ 5,000 - 6,000</a></li>
                                    <li><a href="../search.php?special=salary&range=4&country=<?php echo $country; ?>">$ 4,000 - 5,000</a></li>
                                    <li><a href="../search.php?special=salary&range=5&country=<?php echo $country; ?>">$ 3,000 - 4,000</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        </table>
        
        <!-- popup windows goes here -->
        <div id="notes_window" class="popup_window">
            <div id="notes_title" class="popup_window_title">Career Profile Update</div>
            <form onSubmit="return false;">
                <input type="hidden" id="id" value="" />
                <input type="hidden" id="notes_action" value="" />
                <div class="notes_form">
                    <textarea id="notes" class="notes"></textarea>
                </div>
            </form>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Save" onClick="close_notes_popup(true);" />
                <input type="button" value="Cancel" onClick="close_notes_popup(false);" />
            </div>
        </div>
        
        <div id="texts_window" class="popup_window">
            <div id="texts_title" class="popup_window_title">Career Profile Update</div>
            <form onSubmit="return false;">
                <input type="hidden" id="id" value="" />
                <input type="hidden" id="texts_action" value="" />
                <div class="texts_form">
                    <input type="text" class="texts" id="texts" />
                </div>
            </form>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Save" onClick="close_texts_popup(true);" />
                <input type="button" value="Cancel" onClick="close_texts_popup(false);" />
            </div>
        </div>
        
        <div id="ranges_window" class="popup_window">
            <div id="ranges_title" class="popup_window_title">Career Profile Update</div>
            <form onSubmit="return false;">
                <input type="hidden" id="id" value="" />
                <input type="hidden" id="ranges_action" value="" />
                <div class="ranges_form">
                    <select id="range_currency" class="range_currency">
                    <?php
                    foreach ($GLOBALS['currencies'] as $i=>$currency) {
                    ?>
                        <option value="<?php echo $currency; ?>"><?php echo $currency; ?></option>
                    <?php
                    }
                    ?>
                    </select>
                    <input type="text" class="range" id="range_start" /> to <input type="text" class="range" id="range_end" />
                </div>
            </form>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Save" onClick="close_ranges_popup(true);" />
                <input type="button" value="Cancel" onClick="close_ranges_popup(false);" />
            </div>
        </div>
        
        <div id="choices_window" class="popup_window">
            <div id="choices_title" class="popup_window_title">Career Profile Update</div>
            <form onSubmit="return false;">
                <input type="hidden" id="id" value="" />
                <input type="hidden" id="choices_action" value="" />
                <div class="choices_form">
                    <span id="choices_dropdown"></span>
                </div>
            </form>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Save" onClick="close_choices_popup(true);" />
                <input type="button" value="Cancel" onClick="close_choices_popup(false);" />
            </div>
        </div>
        
        <div id="countries_window" class="popup_window">
            <div id="countries_title" class="popup_window_title">Job Location Preference</div>
            <form onSubmit="return false;">
                <input type="hidden" id="id" value="" />
                <input type="hidden" id="countries_action" value="" />
                <div class="countries_form">
                    <?php echo $this->generate_countries('', 'pref_job_loc'); ?>
                </div>
            </form>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Save" onClick="close_countries_popup(true);" />
                <input type="button" value="Cancel" onClick="close_countries_popup(false);" />
            </div>
        </div>
        
        <div id="job_profile_window" class="popup_window">
            <div class="popup_window_title">Job Profile</div>
            <form onSubmit="return false;">
                <input type="hidden" id="job_profile_id" value="0" />
                <div class="job_profile_form">
                    <table class="job_profile_form">
                        <tr>
                            <td class="label"><label for="specialization">Specialization:</label></td>
                            <td class="field">
                                <?php $this->generate_industries('specialization', array()); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="position_title">Job Title:</label></td>
                            <td class="field">
                                <input class="field" type="text" id="position_title" name="position_title" />
                                <br/>
                                <span class="tips">eg: Director, Manager, GM, VP, etc.</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="position_superior_title">Superior Title:</label></td>
                            <td class="field">
                                <input class="field" type="text" id="position_superior_title" name="position_superior_title" />
                                <br/>
                                <span class="tips">eg: Director, Manager, GM, VP, etc.</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="organization_size">Number of Direct Reports:</label></td>
                            <td class="field"><input class="field" type="text" id="organization_size" name="organization_size" /></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="work_from_month">Duration:</label></td>
                            <td class="field">
                                <?php echo generate_month_dropdown('work_from_month', ''); ?>
                                <input type="text" class="year" maxlength="4" id="work_from_year" value="yyyy" /> 
                                to 
                                <span id="work_to_dropdown">
                                    <?php echo generate_month_dropdown('work_to_month', ''); ?>
                                    <input type="text" class="year" maxlength="4" id="work_to_year" value="yyyy" />
                                </span>
                                <input type="checkbox" id="work_to_present" onClick="toggle_work_to();" /> 
                                <label for="work_to_present">Present</label>
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="company">Employer:</label></td>
                            <td class="field"><input class="field" type="text" id="company" name="company" /></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="emp_desc">Employer Description:</label></td>
                            <td class="field">
                                <?php $this->generate_employer_description('emp_desc', -1); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="emp_specialization">Employer Specialization:</label></td>
                            <td class="field">
                                <?php $this->generate_industries('emp_specialization', array()); ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Save" onClick="close_job_profile_popup(true);" />
                <input type="button" value="Cancel" onClick="close_job_profile_popup(false);" />
            </div>
        </div>
        <?php
    }
}
?>