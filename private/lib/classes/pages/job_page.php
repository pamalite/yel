<?php
require_once dirname(__FILE__). "/../../utilities.php";

class JobPage extends Page {
    private $member = NULL;
    private $job_id = 0;
    private $criterias = NULL;
    private $is_employee_viewing = false;
    private $action_has_error = false;
    private $action_responded = false;
    
    function __construct($_session = NULL, $_job_id, $_criterias = NULL) {
        parent::__construct();
        
        if (!is_null($_session)) {
            if (!empty($_session['id']) && !empty($_session['sid'])) {
                $this->member = new Member($_session['id'], $_session['sid']);
            }
        }
        
        if ($_job_id > 0) {
            $this->job_id = $_job_id;
        }
        
        $this->criterias = $_criterias;
    }
    
    function set_request_status($_error_number = 0) {
        $this->action_responded = true;
        if ($_error_number > 0) {
            $this->action_has_error = true;
        }
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_job_css() {
        $this->insert_css(array('list_box.css', 'job.css', 'job_desc.css'));
    }
    
    public function insert_job_scripts() {
        $this->insert_scripts('job.js');
    }
    
    public function insert_inline_scripts($_show_popup = '') {
        $script = 'var job_id = "'. $this->job_id. '";'. "\n";
        $script .= 'var show_popup = "'. $_show_popup. '";'. "\n";
        
        if (!is_null($this->member)) {
            $script .= 'var id = "'. $this->member->getId(). '";'. "\n";
            $script .= 'var country_code = "'. $this->member->getCountry(). '";'. "\n";
        } else {
            $script .= 'var id = 0;'. "\n";
            $script .= 'var country_code = "'. $_SESSION['yel']['country_code']. '";'. "\n";
        }
        
        if (count($this->criterias) > 0 && !is_null($this->criterias)) {
            $script .= 'var industry = "'. $this->criterias['industry']. '";'. "\n";
            $script .= 'var keywords = "'. $this->criterias['keywords']. '";'. "\n";
        } else {
            $script .= 'var industry = "";'. "\n";
            $script .= 'var keywords = "";'. "\n";
        }
        
        if ($this->action_responded && $this->action_has_error) {
            $script .= 'var alert_error = true;'; 
            $script .= 'var alert_success = false;'; 
        } else {
            $script .= 'var alert_error = false;'; 
            if ($this->action_responded) {
                $script .= 'var alert_success = true;'; 
            } else {
                $script .= 'var alert_success = false;'; 
            }
        }
        
        $script .= '</script>'. "\n";
        
        $this->header = str_replace('<!-- %inline_javascript% -->', $script, $this->header);
    }
    
    public function is_employee_viewing() {
        $this->is_employee_viewing = true;
    }
    
    private function get_job_info() {
        $criteria = array(
            'columns' => 'jobs.*, industries.industry AS full_industry, 
                          countries.country AS country_name, employers.name AS employer_name, 
                          employers.website_url AS employer_website_url, branches.currency, 
                          DATE_FORMAT(jobs.expire_on, \'%e %b, %Y\') AS formatted_expire_on, 
                          DATEDIFF(NOW(), jobs.expire_on) AS expired',
            'joins' => 'industries ON industries.id = jobs.industry, 
                        countries ON countries.country_code = jobs.country, 
                        employers ON employers.id = jobs.employer, 
                        employees ON employees.id = employers.registered_by, 
                        branches ON branches.id = employees.branch', 
            'match' => 'jobs.id = \''. $this->job_id. '\''
        );
        
        $job = new Job();
        $result = $job->find($criteria);
        $job = array();
        
        if (count($result) <= 0 || is_null($result)) {
            return NULL;
        }
        
        $job = $result[0];
        $total_potential_reward = $job['potential_reward'];
        $potential_token_reward = $total_potential_reward * 0.05;
        $potential_reward = $total_potential_reward - $potential_token_reward;
        
        $job['description'] = htmlspecialchars_decode($job['description']);
        if (!is_null($job['alternate_employer']) && !empty($job['alternate_employer'])) {
            $job['employer_name'] = $job['alternate_employer'];
        }
        
        $job['potential_reward'] = number_format($potential_reward, 0, '.', ', ');
        $job['potential_token_reward'] = number_format($potential_token_reward, 0, '. ', ', ');;
        $job['salary'] = number_format($job['salary'], 0, '. ', ', ');
        $job['salary_end'] = number_format($job['salary_end'], 0, '. ', ', ');
        $job['state'] = ucwords($job['state']);
        
        return $job;
    }
    
    private function add_view_count() {
        $job = new Job($this->job_id);
        $job->incrementViewCount();
    }
    
    private function generateCountriesDropdown($_for_quick_upload = false, $_for_referrer = false) {
        $countries = Country::getAll();
        
        $prefix = ($_for_quick_upload) ? 'qu' : 'qr';
        $prefix .= ($_for_referrer) ? '_referrer' : '_candidate';
        echo '<select class="mini_field" id="'. $prefix. '_country" name="'. $prefix. '_country">'. "\n";
        echo '<option value="0" selected>Country of residence</option>'. "\n";
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        
        foreach ($countries as $country) {
            echo '<option value="'. $country['country_code']. '">'. $country['country']. '</option>'. "\n";
        }
        
        echo '</select>'. "\n";
    }
    
    private function generate_resumes_list() {
        if (!is_null($this->member)) {
            $query = "SELECT id, name FROM resumes 
                      WHERE member = '". $this->member->id(). "' AND 
                      private = 'N' AND 
                      deleted = 'N'";
            $mysqli = Database::connect();
            $result = $mysqli->query($query);
            if (!$result) {
                echo 'Sorry, you need to create at least a public viewable resume to proceed.';
                echo '<input type="hidden" name="resume" value="0" />';
                return;
            }
        
            echo '<select class="field" id="resume" name="resume">'. "\n";
            echo '<option value="0" selected>Please select a resume</option>'. "\n";
            echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        
            foreach ($result as $resume) {
                echo '<option value="'. $resume['id']. '">'. $resume['name']. '</option>'. "\n";
            }
        
            echo '</select>'. "\n";
        }
    }
    
    private function get_member_career() {
        $career = array('current_position' => '', 'current_employer' => '');
        
        if (!is_null($this->member)) {
            $criteria = array(
                'columns' => "member_job_profiles.position_title, member_job_profiles.employer", 
                'joins' => "member_job_profiles ON member_job_profiles.member = members.email_addr", 
                'match' => "members.email_addr = '". $this->member->getId(). "'", 
                'order' => "member_job_profiles.work_from DESC", 
                'limit' => "1"
            );
            
            $result = $this->member->find($criteria);
            
            $career['current_position'] = htmlspecialchars_decode(stripslashes($result[0]['position_title']));
            $career['current_employer'] = htmlspecialchars_decode(stripslashes($result[0]['employer']));
        }
        
        return $career;
    }
    
    public function show($_from_search = false) {
        $this->begin();
        $this->top_search("Job Details");
        if (!is_null($this->member)) {
            $this->menu('member');
        }
        
        $job = $this->get_job_info();
        $career = $this->get_member_career();
        
        $error_message = '';
        if (count($job) <= 0 || is_null($job)) {
            $error_message = 'The job that you are looking for cannot be found.';
        } else if ($job === false) {
            $error_message = 'An error occured while loading the job details.';
        }
        
        // format tags to HTML
        $job['description'] = format_job_description($job['description']);
        
        if (!$this->is_employee_viewing) {
            if ($job['expired'] >= 0 || $job['closed'] == 'Y') {
                // $error_message = 'The job that you are looking for is no longer available.';
            }
        }
        
        $page = file_get_contents(dirname(__FILE__). '/../../../html/job_page.html');
        $page = str_replace('%root%', $this->url_root, $page);
        $page = str_replace('%job_id%', $this->job_id, $page);
        
        if (!empty($error_message)) {
            $page = str_replace('%error_message%', $error_message, $page);
            $page = str_replace('%show_error%', 'block', $page);
            $page = str_replace('%show_job%', 'none', $page);
        } else {
            $page = str_replace('%show_error%', 'none', $page);
            $page = str_replace('%show_job%', 'block', $page);
            
            $this->add_view_count();
            
            $page = str_replace('%job_title%', $job['title'], $page);
            
            $employer_name = $job['employer_name'];
            if (!is_null($job['employer_website_url']) && !empty($job['employer_website_url'])) {
                $employer_name = '<a href="'. $job['employer_website_url']. '" target="_new">'. $job['employer_name']. '</a>';
            }
            $page = str_replace('%employer_name%', $employer_name, $page);
            
            $page = str_replace('%job_description%', $job['description'], $page);
            
            $employer_country = (!is_null($job['state']) && !empty($job['state'])) ? $job['state']. ', ' : '';
            $employer_country .= $job['country_name'];
            $page = str_replace('%employer_country%', $employer_country, $page);
            
            $page = str_replace('%full_industry%', $job['full_industry'], $page);
            
            $page = str_replace('%currency%', $job['currency'], $page);
            $salary_range = $job['salary'];
            if ($job['salary_end'] > 0) {
                $salary_range .= ' to '. $job['salary_end'];
            }
            $page = str_replace('%salary_range%', $salary_range, $page);
            
            if ($job['salary_negotiable'] == 'Y') {
                $page = str_replace('%negotiable%', '<br/><span class="negotiable">Negotiable</span>', $page);
            } else {
                $page = str_replace('%negotiable%', '', $page);
            }
            
            $page = str_replace('%expire_on%', $job['formatted_expire_on'], $page);
            $page = str_replace('%potential_reward%', $job['potential_reward'], $page);
            $page = str_replace('%potential_token_reward%', $job['potential_token_reward'], $page);
            
            if ($_from_search) {
                $page = str_replace('%from_search%', 'default', $page);
            } else  {
                $page = str_replace('%from_search%', 'none', $page);
            }
            
            // popup windows
            // refer
            $refer_form = file_get_contents(dirname(__FILE__). '/../../../html/job_page_refer_common_form.html');
            if (!is_null($this->member)) {
                $refer_form = file_get_contents(dirname(__FILE__). '/../../../html/job_page_refer_logged_in_form.html');
                
                $refer_form = str_replace('%refer_member_id%', $this->member->getId(), $refer_form);
                $refer_form = str_replace('%refer_member_fullname%', $this->member->getFullname(), $refer_form);
                $refer_form = str_replace('%refer_member_phone%', $this->member->getPhone(), $refer_form);
            }
            $page = str_replace('<!-- %refer_form% -->', $refer_form, $page);
            
            // apply
            $apply_form = file_get_contents(dirname(__FILE__). '/../../../html/job_page_apply_common_form.html');
            if (!is_null($this->member)) {
                $apply_form = file_get_contents(dirname(__FILE__). '/../../../html/job_page_apply_logged_in_form.html');
                
                $apply_form = str_replace('%apply_member_id%', $this->member->getId(), $apply_form);
                $apply_form = str_replace('%apply_member_fullname%', $this->member->getFullname(), $apply_form);
                $apply_form = str_replace('%apply_member_phone%', $this->member->getPhone(), $apply_form);
                
                $apply_form = str_replace('%apply_current_pos%', $career['current_position'], $apply_form);
                $apply_form = str_replace('%apply_current_emp%', $career['current_employer'], $apply_form);
                
                $existing_resumes_select = '<select id="existing_resume" name="existing_resume" onChange="toggle_resume_upload();">'. "\n";
                $existing_resumes_select .= '<option value="0" selected>Choose from one of your pre-uploads</option>'. "\n";
                $existing_resumes_select .= '<option value="0" disabled>&nbsp;</option>'. "\n";
                if ($this->member->hasResume()) {
                    $criteria = array(
                        'columns' => 'id, file_name', 
                        'match' => "member = '". $this->member->getId(). "' AND 
                                    is_yel_uploaded = FALSE"
                    );

                    $resume = new Resume();
                    $result = $resume->find($criteria);
                    foreach ($result as $row) {
                        $existing_resumes_select .= '<option value="'. $row['id']. '">'. $row['file_name']. '</option>'. "\n";
                    }
                    
                    $existing_resumes_select .= '</select> or<br/>'. "\n";
                }
                $apply_form = str_replace('<!-- %existing_resumes_select% -->', $existing_resumes_select, $apply_form);
            }
            
            $page = str_replace('<!-- %apply_form% -->', $apply_form, $page);
        }
        
        echo $page;
    }
}
?>