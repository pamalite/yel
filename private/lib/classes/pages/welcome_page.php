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
        $this->insert_css('welcome.css');
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
                          branches.currency, employers.name AS employer", 
            'joins' => "job_index ON job_index.job = jobs.id, 
                        employers ON employers.id = jobs.employer, 
                        branches ON branches.id = employers.branch", 
            // 'match' => "jobs.closed = 'N' AND jobs.expire_on >= NOW() AND jobs.deleted = FALSE", 
            'order' => "jobs.salary DESC", 
            'limit' => "5"
        );
        
        $job = new Job();
        $result = $job->find($criteria);
        
        if (count($result) > 0) {
            $top_jobs_table = new HTMLTable('top_jobs_table', '');

            $top_jobs_table->set(0, 0, "Job", '', 'header');
            $top_jobs_table->set(0, 1, "Employer", '', 'header');
            $top_jobs_table->set(0, 2, "Salary Range", '', 'header actions');
            $top_jobs_table->set(0, 3, "Potential Reward", '', 'header actions');

            foreach ($result as $i=>$job) {
                $top_jobs_table->set($i+1, 0, '<a href="job/'. $job['job_id']. '">'. $job['position_title']. '</a>', '', '');
                $top_jobs_table->set($i+1, 1, $job['employer'], '', '');

                $salary = $job['currency']. '$ '. number_format($job['salary_start'], 0, '.', ',');
                if (!is_null($job['salary_end'])) {
                    $salary .= ' - '. number_format($job['salary_end'], 0, '.', ',');
                }
                $top_jobs_table->set($i+1, 2, $salary, '', '');

                $top_jobs_table->set($i+1, 3, $job['currency']. '$ '. number_format($job['potential_reward'], 0, '.', ','), '', '');
            }

            $top_jobs_html = $top_jobs_table->get_html();
        } 
        
        return $top_jobs_html;
    }
    
    private function get_employers() {
        $criteria = array(
            'columns' => 'employers.id, employers.name, COUNT(jobs.id) AS job_count', 
            'joins' => 'employers ON employers.id = jobs.employer',
            // 'match' => "jobs.expire_on >= CURDATE() AND jobs.closed = 'N'", 
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
            // 'match' => "jobs.expire_on >= CURDATE() AND jobs.closed = 'N'", 
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
