<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). "/../../../config/job_profile.inc";
require_once dirname(__FILE__). "/../htmltable.php";

class MemberHomePage extends Page {
    private $member = NULL;
    private $mysqli = NULL;
    private $error_message = '';
    
    function __construct($_session) {
        parent::__construct();
        
        $this->member = new Member($_session['id'], $_session['sid']);
        $this->mysqli = Database::connect();
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
    
    public function insert_member_home_css() {
        $this->insert_css('member_home_page.css');
    }
    
    public function insert_member_home_scripts() {
        $this->insert_scripts('member_home_page.js');
    }
    
    public function insert_inline_scripts() {
        $script = 'var id = "'. $this->member->getId(). '";'. "\n";
        
        if (!empty($this->error_message)) {
            $script .= "alert(\"". $this->error_message. "\");\n";
        }
        
        $this->header = str_replace('<!-- %inline_javascript% -->', $script, $this->header);
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
                          members.preferred_job_location_1 AS pref_job_loc_1, 
                          members.preferred_job_location_2 AS pref_job_loc_2, 
                          countries.country AS pref_job_location_1, 
                          countries2.country AS pref_job_location_2", 
            'joins' => "countries ON countries.country_code = members.preferred_job_location_1, 
                        countries AS countries2 ON countries2.country_code = members.preferred_job_location_2", 
            'match' => "members.email_addr = '". $this->member->getId(). "'", 
            'limit' => "1"
        );
        
        $result = $this->member->find($criteria);
        
        $result[0]['seeking'] = htmlspecialchars_decode(stripslashes($result[0]['seeking']));
        $result[0]['reason_for_leaving'] = htmlspecialchars_decode(stripslashes($result[0]['reason_for_leaving']));
        $result[0]['current_position'] = htmlspecialchars_decode(stripslashes($result[0]['current_position']));
        
        // $result[0]['seeking'] = str_replace("\n", '<br/>', $result[0]['seeking']);
        // $result[0]['reason_for_leaving'] = str_replace("\n", '<br/>', $result[0]['reason_for_leaving']);
        // $result[0]['current_position'] = str_replace("\n", '<br/>', $result[0]['current_position']);
        // 
        // $result[0]['seeking'] = addslashes($result[0]['seeking']);
        // $result[0]['reason_for_leaving'] = addslashes($result[0]['reason_for_leaving']);
        // $result[0]['current_position'] = addslashes($result[0]['current_position']);
        
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
        $country_options_str = '<select class="field" id="'. $_name. '" name="'. $_name. '">'. "\n";
        
        $criteria = array(
            'columns' => "country_code, country", 
            'order' => "country"
        );
        $countries = Country::find($criteria);
        
        if (empty($_selected) || is_null($_selected) || $_selected == '0') {
            $country_options_str .= '<option value="0" selected>Please select a county.</option>'. "\n";    
        } else {
            $country_options_str .= '<option value="0">Please select a country.</option>'. "\n";
        }
        
        $country_options_str .= '<option value="0">&nbsp;</option>';
        foreach ($countries as $country) {
            if ($country['country_code'] != $_selected) {
                $country_options_str .= '<option value="'. $country['country_code']. '">'. $country['country']. '</option>'. "\n";
            } else {
                $country_options_str .= '<option value="'. $country['country_code']. '" selected>'. $country['country']. '</option>'. "\n";
            }
        }
        
        $country_options_str .= '</select>'. "\n";
        
        return $country_options_str;
    }
    
    private function generate_industries($_id, $_selecteds, $_is_multi=false) {
        $industries_options_str = '';
        
        $criteria = array('columns' => "id, industry, parent_id");
        $industries = Industry::find($criteria);
        
        if ($_is_multi) {
            $industries_options_str = '<select class="multiselect" id="'. $_id. '" name="'. $_id. '[]" multiple>'. "\n";
        } else {
            $industries_options_str = '<select class="field" id="'. $_id. '" name="'. $_id. '">'. "\n";
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
        
        $industries_options_str .= '<option value="0" '. (($has_selected) ? '' : 'selected'). '>Select a Specialization</option>'. "\n";
        $industries_options_str .= '<option value="0" disabled>&nbsp;</option>'. "\n";
        $industries_options_str .= $options_str;
        $industries_options_str .= '</select>'. "\n";
        
        return $industries_options_str;
    }
    
    private function generate_employer_description($_id, $_selected) {
        $descs = $GLOBALS['emp_descs'];
        
        $emp_descs_options_str = '<select class="field" id="'. $_id. '" name="'. $_id. '">'. "\n";
        if (empty($_selected) || is_null($_selected) || $_selected < 0) {
            $emp_descs_options_str .= '<option value="0" selected>Please select one</option>'. "\n";    
        } else {
            $emp_descs_options_str .= '<option value="0">Please select One</option>'. "\n";
        }
        
        $emp_descs_options_str .= '<option value="0" disabled>&nbsp;</option>'. "\n";
        foreach ($descs as $i=>$desc) {
            if ($i != $_selected) {
                $emp_descs_options_str .= '<option value="'. $i. '">'. $desc. '</option>'. "\n";
            } else {
                $emp_descs_options_str .= '<option value="'. $i. '" selected>'. $desc. '</option>'. "\n";
            }
        }
        
        $emp_descs_options_str .= '</select>'. "\n";
        
        return $emp_descs_options_str;
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
        
        $page = file_get_contents(dirname(__FILE__). '/../../../html/member_home_page.html');
        $page = str_replace('%root%', $this->url_root, $page);
        $page = str_replace('%country_code%', $country, $page);
        $page = str_replace('%country%', Country::getCountryFrom($country), $page);
        $page = str_replace('%currency%', $currency, $page);
        
        // if (!empty($this->error_message)) {
        //     $page = str_replace('%error_message%', $this->error_message, $page);
        // } else {
        //     $page = str_replace('%error_message%', '', $page);
        // }
        
        // HRM
        $page = str_replace('%display_hrm_questions%', $display_hrm_questions, $page);
        $page = str_replace('%birthdate_day%', generate_dropdown('birthdate_day', '', 1, 31, '', 2, 'Day'), $page);
        $page = str_replace('%birthdate_month%', generate_month_dropdown('birthdate_month', '', 'Month'), $page);
        
        // completeness
        $page = str_replace('%completeness_percent%', $completeness_percent, $page);
        $page = str_replace('%next_step%', $next_step, $page);
        
        // photo
        $photo_html = '<div style="text-align: center; margin: auto;">Upload your photo here by clicking the "Upload Photo" button.</div>';
        if ($this->member->hasPhoto()) {
            $photo_html = '<img id="photo_image" class="photo_image" src="candidate_photo.php?id='. $this->member->getId(). '" />';
        }
        $page = str_replace('%photo_html%', $photo_html, $page);
        
        // career profile
        $is_active_str = 'No';
        if ($is_active) {
            $is_active_str = 'Yes';
        }
        $page = str_replace('%is_active%', $is_active_str, $page);
        
        $seeking_txt = str_replace(array("\r\n", "\r", "\n"), '<br/>', $answers['seeking']);
        $page = str_replace('%seeking%', $seeking_txt, $page);
        
        $page = str_replace('%expected_salary_currency%', $answers['expected_salary_currency'], $page);
        $page = str_replace('%expected_salary%', number_format($answers['expected_salary'], 2, '.', ','), $page);
        $page = str_replace('%expected_salary_end%', number_format($answers['expected_salary_end'], 2, '.', ','), $page);
        
        $page = str_replace('%current_salary_currency%', $answers['current_salary_currency'], $page);
        $page = str_replace('%current_salary%', number_format($answers['current_salary'], 2, '.', ','), $page);
        $page = str_replace('%current_salary_end%', number_format($answers['current_salary_end'], 2, '.', ','), $page);
        
        $page = str_replace('%pref_job_loc_1%', $answers['pref_job_location_1'], $page);
        $page = str_replace('%pref_job_loc_2%', $answers['pref_job_location_2'], $page);
        
        if ($answers['can_travel_relocate'] == 'Y') {
            $page = str_replace('%can_travel%', 'Yes', $page);
        } else {
            $page = str_replace('%can_travel%', 'No', $page);
        }
        
        $reason_for_leaving_txt = str_replace(array("\r\n", "\r", "\n"), '<br/>', $answers['reason_for_leaving']);
        $page = str_replace('%reason_for_leaving%', $reason_for_leaving_txt, $page);
        
        $page = str_replace('%notice_period%', $answers['notice_period'], $page);
        
        // job profiles
        if (empty($job_profiles)) {
            $page = str_replace('%no_positions%', 'block', $page);
            $page = str_replace('%job_profiles_table%', '', $page);
        } else {
            $page = str_replace('%no_positions%', 'none', $page);
            
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
                $pos .= '<br/><span class="mini_spec">reporting to</span><br/>';
                $pos .= '<span class="mini_superior">'. $job_profile['position_superior_title']. '</span>';
                $job_profiles_table->set($i+1, 4, $pos, '', 'cell');
                
                $job_profiles_table->set($i+1, 5, '<a class="no_link" onClick="show_job_profile_popup('. $job_profile['id']. ')">edit</a>', '', 'cell action');
            }
            
            $page = str_replace('%job_profiles_table%', $job_profiles_table->get_html(), $page);
        }
        
        // popup windows
        // photo upload
        $page = str_replace('%upload_photo_member_id%', $this->member->getId(), $page);
        
        // career summary editor
        if ($is_active) {
            $page = str_replace('%is_active_yes_option', 'selected', $page);
            $page = str_replace('%is_active_no_option', '', $page);
        } else {
            $page = str_replace('%is_active_yes_option', '', $page);
            $page = str_replace('%is_active_no_option', 'selected', $page);
        }
        
        $page = str_replace('%seeking_txt%', str_replace('<br/>', "\r\n", $answers['seeking']), $page);
        
        $exp_currency_options_str = '';
        foreach ($GLOBALS['currencies'] as $i=>$currency) {
            if ($currency == $answers['expected_salary_currency']) {
                $exp_currency_options_str .= '<option value="'. $currency. '" selected>'. $currency. '</option>'. "\n";
            } else {
                $exp_currency_options_str .= '<option value="'. $currency. '">'. $currency. '</option>'. "\n";
            }
        }
        $page = str_replace('%expected_salary_currency_options%', $exp_currency_options_str, $page);
        $page = str_replace('%expected_salary_txt%', number_format($answers['expected_salary'], 2), $page);
        $page = str_replace('%expected_salary_end_txt%', number_format($answers['expected_salary_end'], 2), $page);
        
        $cur_currency_options_str = '';
        foreach ($GLOBALS['currencies'] as $i=>$currency) {
            if ($currency == $answers['currenty_salary_currency']) {
                $cur_currency_options_str .= '<option value="'. $currency. '" selected>'. $currency. '</option>'. "\n";
            } else {
                $cur_currency_options_str .= '<option value="'. $currency. '">'. $currency. '</option>'. "\n";
            }
        }
        $page = str_replace('%current_salary_currency_options%', $cur_currency_options_str, $page);
        $page = str_replace('%current_salary_txt%', number_format($answers['current_salary'], 2), $page);
        $page = str_replace('%current_salary_end_txt%', number_format($answers['current_salary_end'], 2), $page);
        
        $page = str_replace('%pref_job_loc_1_select%', $this->generate_countries($answers['pref_job_loc_1'], 'pref_job_loc_1'), $page);
        $page = str_replace('%pref_job_loc_2_select%', $this->generate_countries($answers['pref_job_loc_2'], 'pref_job_loc_2'), $page);
        
        if ($answers['can_travel_relocate'] == 'Y') {
            $page = str_replace('%can_travel_yes', 'selected', $page);
            $page = str_replace('%can_travel_no', '', $page);
        } else {
            $page = str_replace('%can_travel_yes', '', $page);
            $page = str_replace('%can_travel_no', 'selected', $page);
        }
        
        $page = str_replace('%reason_for_leaving_txt%', str_replace('<br/>', "\r\n", $answers['reason_for_leaving']), $page);
        
        $page = str_replace('%notice_period_txt%', $answers['notice_period'], $page);
        
        // job profile
        $page = str_replace('%work_from_month_select%', generate_month_dropdown('work_from_month', ''), $page);
        $page = str_replace('%work_to_month_select%', generate_month_dropdown('work_to_month', ''), $page);
        $page = str_replace('%emp_desc_select%', $this->generate_employer_description('emp_desc', -1), $page);
        $page = str_replace('%industry_select%', $this->generate_industries('emp_specialization', array()), $page);
        
        // present page
        echo $page;
    }
}
?>