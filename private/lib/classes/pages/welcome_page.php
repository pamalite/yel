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
        $this->insert_scripts(array('welcome.js', 'jquery.min.js', 'jquery.skinned-select.js'));
    }
    
    public function insert_inline_scripts() {
        $script = 'jquery(document).ready(function() {'. "\n";
        $script .= 'jquery(\'.overTxtLabel\').attr(\'style\',\'\');});'. "\n";
        $this->header = str_replace('<!-- %inline_javascript% -->', $script, $this->header);
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
            'match' => "jobs.closed = 'N' AND jobs.expire_on >= NOW()", 
            'order' => "jobs.salary DESC", 
            'limit' => "10"
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
                    $salary .= ' - '. number_format($job['salary_start'], 0, '.', ',');
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
            'columns' => 'DISTINCT employers.id, employers.name', 
            'joins' => 'jobs ON employers.id = jobs.employer',
            // 'match' => "jobs.expire_on >= CURDATE() AND jobs.closed = 'N'", 
            'order' => 'employers.name ASC'
        );
        $employer = new Employer();
        $employers = $employer->find($criteria);
        if ($employers === false) {
            $employers = array();
        }
        
        return $employers;
    }
    
    private function get_industries() {
        $industries = array();
        $main_industries = Industry::getMain(true);
        $i = 0;
        foreach ($main_industries as $main) {
            $industries[$i]['id'] = $main['id'];
            $industries[$i]['name'] = $main['industry'];
            $industries[$i]['job_count'] = $main['job_count'];
            $industries[$i]['is_main'] = true;
            $subs = Industry::getSubIndustriesOf($main['id'], true);
            foreach ($subs as $sub) {
                $i++;

                $industries[$i]['id'] = $sub['id'];
                $industries[$i]['name'] = $sub['industry'];
                $industries[$i]['job_count'] = $sub['job_count'];
                $industries[$i]['is_main'] = false;
            }
            $i++;
        }
        
        return $industries;
    }
    
    private function get_countries() {
        $criteria = array(
            'columns' => "DISTINCT countries.country_code, countries.country", 
            'joins' => "countries ON countries.country_code = jobs.country",
            // 'match' => "jobs.expire_on >= CURDATE() AND jobs.closed = 'N'", 
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
            $employers_options .= '<option value="'. $emp['id'].'">'. desanitize($emp['name']). '</option>'. "\n";
        }
        $page = str_replace('<!-- %employers_options% -->', $employers_options, $page);
        
        $industries_options = '';
        foreach ($industries as $industry) {
            if ($industry['is_main']) {
                $industries_options .= '<option value="'. $industry['id']. '" class="main_industry">'. $industry['name'];
            } else {
                $industries_options .= '<option value="'. $industry['id']. '">&nbsp;&nbsp;&nbsp;&nbsp;'. $industry['name'];
            }

            if ($industry['job_count'] > 0) {
                $industries_options .= '&nbsp;('. $industry['job_count']. ')';
            }
            $industries_options .= '</option>'. "\n";
        }
        $page = str_replace('<!-- %industries_options% -->', $industries_options, $page);
        
        $countries_options = '';
        foreach ($countries as $a_country) {
            $countries_options .= '<option value="'. $a_country['country_code']. '">'. $a_country['country']. '</option>'. "\n";
        }
        $page = str_replace('<!-- %countries_options% -->', $countries_options, $page);
        
        $page = str_replace('<!-- %top_jobs% -->', $this->generate_top_jobs(), $page);
        
        echo $page;
    }
}
?>
