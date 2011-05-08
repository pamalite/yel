<?php
require_once dirname(__FILE__). "/../../utilities.php";

class SearchPage extends Page {
    private $member = NULL;
    private $criterias = '';
    private $job_search = '';
    private $country_code = '';
    private $a_result_template = '';
    
    function __construct($_session = NULL, $_criterias = '') {
        parent::__construct();
        
        if (!is_null($_session)) {
            if (!empty($_session['id']) && !empty($_session['sid'])) {
                $this->member = new Member($_session['id'], $_session['sid']);
            }
        }
        
        $this->criterias = $_criterias;
        if (!isset($this->criterias['salary'])) {
            $this->criterias['salary'] = 0;
        } 
        
        $this->job_search = new JobSearch();
        
        $this->a_result_template = file_get_contents(dirname(__FILE__). '/../../../html/job_search_result.html');
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_search_css() {
        $this->insert_css(array('search.css', 'job_search_result.css'));
    }
    
    public function insert_search_scripts() {
        $this->insert_scripts(array('search.js', 'job_search_result.js'));
    }
    
    public function insert_inline_scripts() {
        $script = '';
        
        if (!is_null($this->member)) {
            $script .= 'var id = "'. $this->member->getId(). '";'. "\n";
            
            $this->country_code = (isset($this->criterias['country'])) ? $this->criterias['country'] : $this->member->getCountry();
            $script .= 'var country_code = "'. $this->country_code. '";'. "\n";
        } else {
            $script .= 'var id = 0;'. "\n";
            
            $this->country_code = (isset($this->criterias['country'])) ? $this->criterias['country'] : $_SESSION['yel']['country_code'];
            $script .= 'var country_code = "'. $this->country_code. '";'. "\n";
        }
        $script .= 'var industry = "'. $this->criterias['industry']. '";'. "\n";
        $script .= 'var employer = "'. $this->criterias['employer']. '";'. "\n";
        $script .= 'var keywords = "'. $this->criterias['keywords']. '";'. "\n";
        // echo 'var is_local = '. $this->criterias['is_local']. ';'. "\n";
        $script .= 'var filter_salary = '. $this->criterias['salary']. ';'. "\n";
        $script .= 'var filter_salary_end = '. ((isset($this->criterias['salary_end'])) ? $this->criterias['salary_end'] : 0). ';'. "\n";
        
        $limit = (isset($this->criterias['limit'])) ? $this->criterias['limit'] : $GLOBALS['default_results_per_page'];
        $script .= 'var limit = "'. $limit. '";'. "\n";
        
        $offset = (isset($this->criterias['offset'])) ? $this->criterias['offset'] : 0;
        $script .= 'var offset = "'. $offset. '";'. "\n";
        
        $script .= 'var a_result_template = "'. addslashes(str_replace("\n", '', $this->a_result_template)). '";'. "\n";
        
        $this->header = str_replace('<!-- %inline_javascript% -->', $script, $this->header);
    }
    
    public function show() {
        $this->begin();
        $this->top_search("Searched Jobs");
        
        if ($this->member != NULL) {
            $this->menu('member');
        }
        
        $results = $this->job_search->search_using($this->criterias);
        
        $result_template = $this->a_result_template;
        $page = file_get_contents(dirname(__FILE__). '/../../../html/job_search_page.html');
        $page = str_replace('%root%', $this->url_root, $page);
        
        // populate statistics
        $page = str_replace('%total_results%', $this->job_search->total_results(), $page);
        $page = str_replace('%time_elapsed%', number_format($this->job_search->time_elapsed(), 6), $page);
        
        // render filters
        // employer filter
        $employer_filter = '<option value="" selected>Any</option>';
        if (!empty($this->criterias['employer'])) {
            $employer_filter = '<option value="">Any</option>';
        }
        $employer_filter .= '<option value="" disabled>&nbsp;</option>';
        
        foreach ($this->job_search->result_employers as $employer) {
            if ($this->criterias['employer'] == $employer['id']) {
                $employer_filter .= '<option value="'. $employer['id']. '" selected>'. $employer['name']. '</option>';
            } else {
                $employer_filter .= '<option value="'. $employer['id']. '">'. $employer['name']. '</option>';
            }
        }
        $page = str_replace('%filter_employer%', $employer_filter, $page);
        
        // industry filter
        $industry_filter = '<option value="" selected>Any</option>';
        if (!empty($this->criterias['industry'])) {
            $industry_filter = '<option value="">Any</option>';
        }
        $industry_filter .= '<option value="" disabled>&nbsp;</option>';
        
        foreach ($this->job_search->result_industries as $industry) {
            if ($this->criterias['industry'] == $industry['id']) {
                $industry_filter .= '<option value="'. $industry['id'] .'" selected>'. $industry['name']. '></option>';
            } else {
                $industry_filter .= '<option value="'. $industry['id']. '">'. $industry['name']. '</option>';
            }
        }
        $page = str_replace('%filter_industry%', $industry_filter, $page);
        
        // country filter
        $country_filter = '<option value="" selected>Any</option>';
        if (!empty($this->country_code) && $this->country_code > 0) {
            $country_filter = '<option value="">Any</option>';
        }
        $country_filter .= '<option value="" disabled>&nbsp;</option>';
        
        foreach ($this->job_search->result_countries as $country) {
            if ($this->country_code == $country['id']) {
                $country_filter .= '<option value="'. $country['id']. '" selected>'. $country['name']. '</option>';
            } else {
                $country_filter .= '<option value="'. $country['id']. '">'. $country['name']. '</option>';
            }
        }
        $page = str_replace('%filter_country%', $country_filter, $page);
        
        // salary filter
        $salary_filter = '<option value="0" selected>Any</option>';
        if ($this->criterias['salary'] > 0) {
            $salary_filter = '<option value="0">Any</option>';
        }
        $salary_filter .= '<option value="" disabled>&nbsp;</option>';
        
        foreach ($this->job_search->result_salaries as $salary) {
            if ($this->criterias['salary'] == $salary) {
                $salary_filter .= '<option value="'. $salary. '" selected>'. $salary. '</option>';
            } else {
                $salary_filter .= '<option value="'. $salary. '">'. $salary. '</option>';
            }
        }
        $page = str_replace('%filter_salary%', $salary_filter, $page);
        
        // render pagination
        $pagination = '1 of 1';
        if ($this->job_search->total_results() > $GLOBALS['default_results_per_page']) {
            $pagination = '<select id="page" onChange="show_jobs();">';
            $total_pages = ceil($this->job_search->total_results() / $GLOBALS['default_results_per_page']);
            $current_page = '1';
            if ($this->criterias['offset'] > 0) {
                $current_page = ceil($this->criterias['offset'] / $GLOBALS['default_results_per_page']) + 1;
            }
            
            for ($page_num=0; $page_num < $total_pages; $page_num++) {
                if (($page_num+1) == $current_page) {
                    $pagination .= '<option value="'. $page_num. '" selected>'. ($page_num+1). '</option>';
                } else {
                    $pagination .= '<option value="'. $page_num. '">'. ($page_num+1). '</option>';
                }
            }
            $pagination .= '</select> of '. $total_pages;
        }
        $page = str_replace('%pagination%', $pagination, $page);
        
        // render results
        if (is_null($results) || empty($results) || $results === false) {
            $page = str_replace('%searched_results%', '<div class="empty_results">No jobs with the criteria found.</div>', $page);
        } else {
            $html = '';
            foreach ($results as $i=>$row) {
                $total_potential_reward = $row['potential_reward'];
                $potential_token_reward = $total_potential_reward * 0.05;
                $potential_reward = $total_potential_reward - $potential_token_reward;
                $a_result = $result_template;
                
                $a_result = str_replace('%job_id%', $row['id'], $a_result);
                $a_result = str_replace('%job_title%', $row['title'], $a_result);
                
                if (!is_null($row['alternate_employer']) && !empty($row['alternate_employer'])) {
                    $a_result = str_replace('%employer%', $row['alternate_employer'], $a_result);
                } else {
                    $a_result = str_replace('%employer%', $row['employer'], $a_result);
                }
                
                $a_result = str_replace('%country%', $row['country'], $a_result);
                $a_result = str_replace('%industry%', $row['industry'], $a_result);
                $a_result = str_replace('%currency%', $row['currency'], $a_result);
                
                $salary_range = number_format($row['salary'], 2, '.', ',');
                if (!empty($row['salary_end']) && !is_null($row['salary_end'])) {
                    $salary_range .= ' - '. number_format($row['salary_end'], 2, '.', ',');
                }
                $a_result = str_replace('%salary_range%', $salary_range, $a_result);
                
                $a_result = str_replace('%potential_reward%', number_format($potential_reward, 2, '.', ','), $a_result);
                $a_result = str_replace('%potential_token_reward%', number_format($potential_token_reward, 2, '.', ','), $a_result);
                $a_result = str_replace('%expire_on%', $row['formatted_expire_on'], $a_result);
                
                $html .= $a_result;
            }
            $page = str_replace('%searched_results%', $html, $page);
        }
        
        echo $page;
    }
}
?>