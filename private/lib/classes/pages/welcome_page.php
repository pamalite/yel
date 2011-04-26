<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). '/../htmltable.php';

class WelcomePage extends Page {
    
    function __construct() {
        parent::__construct();
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_welcome_css() {
        $this->insert_css(array('welcome.css', 'job_search_result.css'));
    }
    
    public function insert_welcome_scripts() {
        $this->insert_scripts(array('welcome.js'));
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline JavaScripts goes here
    }
    
    private function generate_top_jobs() {
        $top_jobs_html = '';
        
        $criteria = array(
            'columns' => "jobs.id AS job_id, jobs.title AS position_title, jobs.salary AS salary_start, 
                          jobs.salary_end AS salary_end, jobs.potential_reward AS potential_reward, 
                          branches.currency, employers.name AS employer, industries.industry, countries.country, 
                          jobs.alternate_employer, 
                          DATE_FORMAT(jobs.expire_on, '%e %b, %Y') AS formatted_expire_on", 
            'joins' => "job_index ON job_index.job = jobs.id, 
                        employers ON employers.id = jobs.employer, 
                        industries ON industries.id = jobs.industry, 
                        countries ON countries.country_code = jobs.country, 
                        branches ON branches.id = employers.branch", 
            'match' => "jobs.closed = 'N' AND jobs.expire_on >= NOW() AND jobs.deleted = FALSE", 
            'order' => "jobs.salary DESC", 
            'limit' => "5"
        );
        
        $job = new Job();
        $result = $job->find($criteria);
        
        if (count($result) > 0) {
            $result_template = file_get_contents(dirname(__FILE__). '/../../../html/job_search_result.html');
            foreach ($result as $i=>$job) {
                $a_result = $result_template;
                $total_potential_reward = $job['potential_reward'];
                $potential_token_reward = $total_potential_reward * 0.05;
                $potential_reward = $total_potential_reward - $potential_token_reward;
                
                $a_result = str_replace('%job_id%', $job['job_id'], $a_result);
                $a_result = str_replace('%job_title%', $job['position_title'], $a_result);
                
                if (!is_null($job['alternate_employer']) && !empty($job['alternate_employer'])) {
                    $a_result = str_replace('%employer%', $job['alternate_employer'], $a_result);
                } else {
                    $a_result = str_replace('%employer%', $job['employer'], $a_result);
                }
                
                $a_result = str_replace('%country%', $job['country'], $a_result);
                $a_result = str_replace('%industry%', $job['industry'], $a_result);
                $a_result = str_replace('%currency%', $job['currency'], $a_result);
                
                $salary_range = number_format($job['salary_start'], 2, '.', ',');
                if (!empty($job['salary_end']) && !is_null($job['salary_end'])) {
                    $salary_range .= ' - '. number_format($job['salary_end'], 2, '.', ',');
                }
                $a_result = str_replace('%salary_range%', $salary_range, $a_result);
                
                $a_result = str_replace('%potential_reward%', number_format($potential_reward, 2, '.', ','), $a_result);
                $a_result = str_replace('%potential_token_reward%', number_format($potential_token_reward, 2, '.', ','), $a_result);
                $a_result = str_replace('%expire_on%', $job['formatted_expire_on'], $a_result);
                
                $top_jobs_html .= $a_result;
            }
        } else {
            $top_jobs_html = 'No jobs at the moment.';
        }
        
        return $top_jobs_html;
    }
    
    private function get_employers() {
        $criteria = array(
            'columns' => 'employers.id, employers.name, COUNT(jobs.id) AS job_count', 
            'joins' => 'employers ON employers.id = jobs.employer',
            'match' => "jobs.deleted = FALSE AND jobs.expire_on >= CURDATE() AND jobs.closed = 'N'", 
            'group' => 'employers.id', 
            'order' => 'employers.name ASC'
        );
        $job = new Job();
        $employers = $job->find($criteria);
        if ($employers === false) {
            $employers = array();
        }
        
        return $employers;
    }
    
    private function get_industries() {
        $industries = Industry::getIndustriesFromJobs(true);
        return $industries;
    }
    
    private function get_countries() {
        $criteria = array(
            'columns' => "countries.country_code, countries.country, COUNT(jobs.id) AS job_count", 
            'joins' => "countries ON countries.country_code = jobs.country",
            'match' => "jobs.deleted = FALSE AND jobs.expire_on >= CURDATE() AND jobs.closed = 'N'", 
            'group' => "countries.country_code", 
            'order' => "countries.country ASC"
        );
        
        $job = new Job();
        $countries = $job->find($criteria);
        return ($countries === false) ? array() : $countries;
    }
    
    public function show() {
        $this->begin();
        $this->top_welcome();
        $this->howitworks();
        
        $employers = $this->get_employers();
        $industries = $this->get_industries();
        $countries = $this->get_countries();
        
        $page = file_get_contents(dirname(__FILE__). '/../../../html/welcome_page.html');
        $page = str_replace('%root%', $this->url_root, $page);
        
        $employers_options = '';
        foreach ($employers as $emp) {
            $employers_options .= '<option value="'. $emp['id'].'">'. desanitize($emp['name']);
            
            if ($emp['job_count'] > 0) {
                $employers_options .= '&nbsp;('. $emp['job_count']. ')';
            }
            $employers_options .= '</option>'. "\n";
        }
        $page = str_replace('<!-- %employers_options% -->', $employers_options, $page);
        
        $industries_options = '';
        foreach ($industries as $industry) {
            $industries_options .= '<option value="'. $industry['id']. '">'. $industry['industry'];
            
            if ($industry['job_count'] > 0) {
                $industries_options .= '&nbsp;('. $industry['job_count']. ')';
            }
            $industries_options .= '</option>'. "\n";
        }
        $page = str_replace('<!-- %industries_options% -->', $industries_options, $page);
        
        $countries_options = '';
        foreach ($countries as $a_country) {
            $countries_options .= '<option value="'. $a_country['country_code']. '">'. $a_country['country'];
            
            if ($a_country['job_count'] > 0) {
                $countries_options .= '&nbsp;('. $a_country['job_count']. ')';
            }
            $countries_options .= '</option>'. "\n";
        }
        $page = str_replace('<!-- %countries_options% -->', $countries_options, $page);
        
        $page = str_replace('<!-- %top_jobs% -->', $this->generate_top_jobs(), $page);
        
        echo $page;
    }
}
?>
