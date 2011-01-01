<?php
require_once dirname(__FILE__). "/../utilities.php";

class JobSearch {
    private $employer = '';
    private $industry = 0;
    private $keywords = '';
    private $country_code = '';
    private $salary = 0;
    private $salary_end = 0;
    private $order_by = 'jobs.created_on DESC';
    private $limit = '';
    private $offset = 0;
    private $punctuations = array(",", ".",";","\"","\'","(",")","[","]","{","}","<",">");
    private $total = 0;
    private $pages = 1;
    private $changed_country_code = false;
    private $time_elapsed = 0;
    private $special = '';
    
    public $result_countries = array();
    public $result_employers = array();
    public $result_industries = array();
    public $result_salaries = array();
    
    function __construct() {
        $this->country_code = $GLOBALS['default_country_code'];
        $this->limit = $GLOBALS['default_results_per_page'];
    }
    
    private function remove_punctuations_from($_keywords) {
        return str_replace($this->punctuations, ' ', $_keywords);
    }
    
    private function insert_wildcard_to_keywords() {
        $keywords = explode(" ", trim($keywords));
        $out = '%';

        foreach ($keywords as $str) {
            $out .= trim($str). '%';
        }

        return $out;
    }
    
    private function log_search_criteria() {
        $gi = geoip_open($GLOBALS['maxmind_geoip_data_file'], GEOIP_STANDARD);
        $country = geoip_country_code_by_addr($gi, $_SERVER['REMOTE_ADDR']);
        geoip_close($gi);

        if (empty($country) || is_null($country)) {
            $country = '??';
        }
        
        $query = "INSERT INTO search_log SET 
                  from_ip_address = '". $_SERVER['REMOTE_ADDR']. "', 
                  from_country = '". $country. "', 
                  keywords = '". sanitize($this->keywords). "', 
                  filter_industry = ". $this->industry. ", 
                  filter_country_code = '". $this->country_code. "', 
                  searched_on = NOW()";
        $mysqli = Database::connect();
        return $mysqli->execute($query);
    }
    
    private function make_query($with_limit = false) {
        $this->log_search_criteria();
        $boolean_mode = '';
        
        $match_against = "MATCH (job_index.title, 
                                 job_index.description, 
                                 job_index.state) 
                          AGAINST ('". $this->keywords. "' IN BOOLEAN MODE)";
        
        $filter_job_status = "(jobs.closed = 'N' OR jobs.closed = 'Y')";
        //$filter_job_status = "jobs.closed = 'N'";
        
        $filter_employer = "jobs.employer IS NOT NULL";
        if (!empty($this->employer)) {
            $filter_employer = "jobs.employer = '". $this->employer. "'";
        }
        
        $filter_industry = "jobs.industry <> 0";
        if ($this->industry > 0) {
            $children = Industry::getSubIndustriesOf($this->industry);
            $industries = '('. $this->industry;
            if (count($children) > 0) {
                $industries .= ', ';
            }
            
            $i = 0;
            foreach ($children as $child) {
                $industries .= $child['id'];
                if ($i < (count($children)-1)) {
                    $industries .= ', ';
                }
                $i++;
            }
            $industries .= ')';
            $filter_industry = "jobs.industry IN ". $industries;
        }
        
        $filter_country = "jobs.country LIKE '%'";
        if (!empty($this->country_code) && !is_null($this->country_code)) {
            $filter_country = "jobs.country = '". $this->country_code. "'";
        }
        
        $filter_salary = "";
        if ($this->salary > 0) {
            $filter_salary = "jobs.salary >= ". $this->salary;
            if ($this->salary_end > 0) {
                $filter_salary = "(jobs.salary BETWEEN ". $this->salary. " AND ". $this->salary_end. ")";
            }
        }
        
        $filter_latest = "";
        if ($this->special == 'latest') {
            $filter_latest = "jobs.created_on BETWEEN date_add(CURDATE(), INTERVAL -5 DAY) AND CURDATE() ";
            $this->offset = 0;
            $this->limit = 10;
            $with_limit = true;
        } else if ($this->special == 'top') {
            $this->order_by = "jobs.potential_reward DESC";
            $this->offset = 0;
            $this->limit = 10;
            $with_limit = true;
        }
        
        $columns = "jobs.id, jobs.title, jobs.state, jobs.salary, jobs.salary_end, jobs.description, 
                    jobs.potential_reward, branches.currency, jobs.alternate_employer, 
                    jobs.employer AS employer_id, employers.name AS employer, 
                    industries.industry, industries.id AS industry_id, 
                    countries.country, countries.country_code, 
                    DATE_FORMAT(jobs.expire_on, '%e %b %Y') AS formatted_expire_on";
        
        $joins = "job_index ON job_index.job = jobs.id, 
                  employers ON employers.id = jobs.employer, 
                  employees ON employees.id = employers.registered_by, 
                  branches ON branches.id = employees.branch, 
                  industries ON industries.id = jobs.industry, 
                  countries ON countries.country_code = jobs.country";
        
        $match = "";
        if (!is_null($this->keywords) && !empty($this->keywords)) {
            $match .= $match_against. " AND ";
        } 
       
        $match .= "jobs.deleted = FALSE 
                   AND ". $filter_job_status. " 
                   AND ". $filter_industry. " 
                   AND ". $filter_country. " 
                   AND ". $filter_employer. " "; 
        
        if (!empty($filter_salary)) {
            $match .= "AND ". $filter_salary. " ";
        }
        
        if (!empty($filter_latest)) {
            $match .= "AND ". $filter_latest. " ";
        }
        
        $order = $this->order_by;
        
        $limit = "";
        if ($with_limit) {
            $limit = $this->offset. ", ". $this->limit; 
            
            return array(
                'columns' => $columns, 
                'joins' => $joins, 
                'match' => $match, 
                'order' => $order, 
                'limit' => $limit
            );
        }
        
        return array(
            'columns' => $columns, 
            'joins' => $joins, 
            'match' => $match, 
            'order' => $order
        );
    }
    
    public function total_results() {
        return $this->total;
    } 
    
    public function next_offset() {
        return ($this->offset + $this->limit);
    }
    
    public function get_offset_of($n = 1) {
        return ($this->offset + ($this->limit * $n));
    }
    
    public function search_using($_criterias) {
        if (!empty($_criterias['employer']) && $_criterias['employer'] != '0') {
            $this->employer = $_criterias['employer'];
        }
        
        if ($_criterias['industry'] > 0) {
            $this->industry = $_criterias['industry'];
        }
        
        $keywords = sanitize(trim($_criterias['keywords']));
        if (!empty($keywords)) {
            $this->keywords = $this->remove_punctuations_from($keywords);
        }
        
        // if (array_key_exists('country_code', $_criterias)) {
        //     $this->country_code = $_criterias['country_code'];
        // } else {
        //     if ($_criterias['is_local'] <= 0) {
        //         $this->country_code = NULL;
        //     }
        // }
        
        if (array_key_exists('countrye', $_criterias)) {
            $this->country_code = $_criterias['country'];
        } else {
            $this->country_code = NULL;
        }
        
        if ($_criterias['salary'] > 0) {
            $this->salary = $_criterias['salary'];
        }
        
        if (array_key_exists('salary_end', $_criterias)) {
            $this->salary_end = $_criterias['salary_end'];
        }
        
        if (array_key_exists('order_by', $_criterias)) {
            $this->order_by = $_criterias['order_by'];
        }
        
        if (array_key_exists('limit', $_criterias)) {
            $this->limit = $_criterias['limit'];
        }
        
        if (array_key_exists('offset', $_criterias)) {
            $this->offset = $_criterias['offset'];
        }
        
        if (array_key_exists('special', $_criterias)) {
            $this->special = $_criterias['special'];
        }
        
        $criteria = $this->make_query();
        $job = new Job();
        $result = $job->find($criteria);
        if (!is_null($result) && !empty($result)) {
            $this->total = count($result);
        }
        
        $total_pages = ceil($this->total / $this->limit);
        if ($total_pages <= 0) {
            return 0;
        }
        
        $start = microtime();
        $result = $job->find($this->make_query(true));
        $end = microtime();
        
        list($ustart, $istart) = explode(" ", $start);
        list($uend, $iend) = explode(" ", $end);
        
        $this->time_elapsed = ((float)$uend + (float)$iend) - ((float)$ustart + (float)$istart);
        
        if ($result === false) {
            return false;
        }
        
        // find the unique employers
        $i = 0;
        foreach ($result as $row) {
            $is_in_array = false;
            if (count($this->result_employers) > 0) {
                foreach ($this->result_employers as $employer) {
                    if ($row['employer_id'] == $employer['id']) {
                        $is_in_array = true;
                        break;
                    }
                }
            }
            
            if (!$is_in_array) {
                $name = $row['employer'];
                if (!is_null($row['alternate_employer']) && !empty($row['alternate_employer'])) {
                    $name = $row['employer'];
                }
                
                $this->result_employers[$i]['id'] = $row['employer_id'];
                $this->result_employers[$i]['name'] = $name;
                $i++;
            }
        }
        
        // find the unique industries
        $i = 0;
        foreach ($result as $row) {
            $is_in_array = false;
            if (count($this->result_industries) > 0) {
                foreach ($this->result_industries as $industry) {
                    if ($row['industry_id'] == $industry['id']) {
                        $is_in_array = true;
                        break;
                    }
                }
            }
            
            if (!$is_in_array) {
                $this->result_industries[$i]['id'] = $row['industry_id'];
                $this->result_industries[$i]['name'] = $row['industry'];
                $i++;
            }
        }
        
        // find the unique countries
        $i = 0;
        foreach ($result as $row) {
            $is_in_array = false;
            if (count($this->result_countries) > 0) {
                foreach ($this->result_countries as $country) {
                    if ($row['country_code'] == $country['id']) {
                        $is_in_array = true;
                        break;
                    }
                }
            }
            
            if (!$is_in_array) {
                $this->result_countries[$i]['id'] = $row['country_code'];
                $this->result_countries[$i]['name'] = $row['country'];
                $i++;
            }
        }
        
        // find the unique salary beginning
        foreach ($result as $row) {
            $is_in_array = in_array($row['salary'], $this->result_salaries);
            
            if (!$is_in_array) {
                $this->result_salaries[] = $row['salary'];
            }
        }
        sort($this->result_salaries);
        
        return $result;
    }
    
    public function country_code_changed() {
        return $this->changed_country_code;
    }
    
    public function time_elapsed() {
        return $this->time_elapsed;
    }
}
?>