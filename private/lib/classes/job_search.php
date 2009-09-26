<?php
require_once dirname(__FILE__). "/../utilities.php";

class JobSearch {
    private $employer = '';
    private $industry = 0;
    private $keywords = '';
    private $country_code = '';
    private $order_by = 'relevance desc';
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
    
    private function remove_punctuations_from($keywords) {
        return str_replace($this->punctuations, ' ', $keywords);
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
        
        // check should we use BOOLEAN MODE
        if (str_word_count($this->keywords) <= 3) {
            $boolean_mode = ' IN BOOLEAN MODE';
        }
        
        $match_against = "MATCH (job_index.title, 
                                 job_index.description, 
                                 job_index.state) 
                          AGAINST ('". $this->keywords. "'". $boolean_mode. ")";
        
        //$filter_job_status = "jobs.closed = 'N' AND jobs.expire_on >= NOW()";
        $filter_job_status = "jobs.closed = 'N'";
        
        $filter_employer = "jobs.employer IS NOT NULL";
        if (!empty($this->employer)) {
            $filter_employer = "jobs.employer = '". $this->employer. "'";
        }
        
        $filter_industry = "jobs.industry <> 0";
        if ($this->industry > 0) {
            $children = Industry::get_sub_industries_of($this->industry);
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
        
        $query = "SELECT jobs.id, jobs.title, jobs.state, jobs.salary, jobs.salary_end, 
                         jobs.potential_reward, currencies.symbol AS currency, 
                         employers.name, industries.industry, countries.country, 
                         DATE_FORMAT(jobs.created_on, '%e %b %Y') AS formatted_created_on ";
        
        if (!is_null($this->keywords) && !empty($this->keywords)) {
            $query .= ", ". $match_against. " AS relevance, relevances.max_relevance ";
        } else {
            $query .= ", '1' AS relevance, '1' AS max_relevance ";
        }
        
        $query .= "FROM jobs 
                   LEFT JOIN job_index ON job_index.job = jobs.id 
                   LEFT JOIN employers ON employers.id = jobs.employer 
                   LEFT JOIN industries ON industries.id = jobs.industry 
                   LEFT JOIN countries ON countries.country_code = jobs.country 
                   LEFT JOIN currencies ON currencies.country_code = employers.country ";
        
       if (!is_null($this->keywords) && !empty($this->keywords)) {
           $query .= ", (SELECT MAX(". $match_against. ") AS max_relevance FROM job_index LIMIT 1) relevances ";
           $query .= "WHERE ". $match_against. " AND ";
       } else {
           $query .= "WHERE ";
       }
       
       $query .= $filter_job_status. " 
                  AND ". $filter_industry. " 
                  AND ". $filter_country. " 
                  AND ". $filter_employer. " ";
        
        if ($with_limit) {
            $query .= "ORDER BY ". $this->order_by. " 
                       LIMIT ". $this->offset. ", ". $this->limit; 
        } 
        
        return $query;
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
        
        if (array_key_exists('order_by', $_criterias)) {
            $this->order_by = $_criterias['order_by'];
        }
        
        if (array_key_exists('limit', $_criterias)) {
            $this->limit = $_criterias['limit'];
        }
        
        if (array_key_exists('offset', $_criterias)) {
            $this->offset = $_criterias['offset'];
        }
        
        $query = $this->make_query();
        $mysqli = Database::connect();
        $result = $mysqli->query($query);
        if (!is_null($result) && !empty($result)) {
            $this->total = count($result);
        }
        
        // generalized for any country
        if ($this->total <= 0) {
            $this->country_code = '';
            $this->changed_country_code = true;
            $query = $this->make_query();
            $result = $mysqli->query($query);
            if (!is_null($result) && !empty($result)) {
                $this->total = count($result);
            }
        }
        
        $total_pages = ceil($this->total / $this->limit);
        if ($total_pages <= 0) {
            return 0;
        }
        
        $result = $mysqli->query($this->make_query(true));
        if ($result === false) {
            return false;
        }
        
        foreach($result as $i=>$row) {
            $result[$i]['match_percentage'] = number_format((($row['relevance'] / $row['max_relevance']) * 100.00), 0, '.', ', ');
        }
        
        return $result;
    }
    
    public function country_code_changed() {
        return $this->changed_country_code;
    }
}
?>