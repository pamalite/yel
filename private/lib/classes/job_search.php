<?php
require_once dirname(__FILE__). "/../utilities.php";

class JobSearch {
    private $employer = '';
    private $industry = 0;
    private $keywords = '';
    private $country_code = '';
    private $order_by = 'jobs.created_on DESC';
    private $limit = '';
    private $offset = 0;
    private $punctuations = array(",", ".",";","\"","\'","(",")","[","]","{","}","<",">");
    private $total = 0;
    private $pages = 1;
    private $changed_country_code = false;
    
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
        
        $filter_job_status = "jobs.closed = 'N' OR jobs.closed = 'Y'";
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
        
        $columns = "jobs.id, jobs.title, jobs.state, jobs.salary, jobs.salary_end, jobs.description, 
                    jobs.potential_reward, branches.currency, jobs.alternate_employer, 
                    employers.name AS employer, industries.industry, countries.country, 
                    DATE_FORMAT(jobs.created_on, '%e %b %Y') AS formatted_created_on";
        
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
       
        $match .= $filter_job_status. " 
                  AND ". $filter_industry. " 
                  AND ". $filter_country. " 
                  AND ". $filter_employer. " "; 
        
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
        if (!empty($_criterias['employer'])) {
            $this->employer = $_criterias['employer'];
        }
        
        if ($_criterias['industry'] > 0) {
            $this->industry = $_criterias['industry'];
        }
        
        $keywords = sanitize(trim($_criterias['keywords']));
        if (!empty($keywords)) {
            $this->keywords = $this->remove_punctuations_from($keywords);
        }
        
        if (array_key_exists('country_code', $_criterias)) {
            $this->country_code = $_criterias['country_code'];
        }
        
        if ($_criterias['is_local'] <= 0) {
            $this->country_code = NULL;
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
        
        $result = $job->find($this->make_query(true));
        if ($result === false) {
            return false;
        }
        
        return $result;
    }
    
    public function country_code_changed() {
        return $this->changed_country_code;
    }
}
?>