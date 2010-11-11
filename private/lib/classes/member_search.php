<?php
require_once dirname(__FILE__). "/../utilities.php";

class MemberSearch {
    private $resume_keywords = array();
    private $notes_keywords = array();
    private $seeking_keywords = array();
    private $hrm_gender = '';
    private $hrm_ethnicity = '';
    private $is_active_seeking_job = '';
    private $expected_salary = array();
    private $current_salary = array();
    private $can_travel_relocate = '';
    private $notice_period = '';
    
    private $order_by = 'member_name DESC';
    private $limit = '';
    private $offset = 0;
    private $punctuations = array(",", ".",";","\"","\'","(",")","[","]","{","}","<",">");
    private $total = 0;
    private $pages = 1;
    private $time_elapsed = 0;
    private $mysqli = NULL;
    
    function __construct() {
        if (!is_a($this->mysqli, "MySQLi")) {
            $this->mysqli = Database::connect();
        }
        
        $this->country_code = $GLOBALS['default_country_code'];
        $this->limit = $GLOBALS['default_results_per_page'];
        
        $this->resume_keywords = array(
            'keywords' => '',
            'is_boolean' => false,
            'is_use_all_words' => false
        );
        
        $this->notes_keywords = array(
            'keywords' => '',
            'is_boolean' => false,
            'is_use_all_words' => false
        );
        
        $this->seeking_keywords = array(
            'keywords' => '',
            'is_boolean' => false,
            'is_use_all_words' => false
        );
        
        $this->current_salary = array(
            'start' => 0,
            'end' => 0
        );
        
        $this->expected_salary = array(
            'start' => 0,
            'end' => 0
        );
    }
    
    private function remove_punctuations_from($_keywords) {
        return str_replace($this->punctuations, ' ', $_keywords);
    }
    
    private function mandate_all_words($_keywords) {
        $keywords = explode(' ', trim($_keywords));
        $out = '';
        
        foreach ($keywords as $str) {
            $word = trim($str);
            
            if (!empty($word)) {
                $out .= '+'. $word. ' ';
            }
        }
        
        return rtrim($out);
    }
    
    private function make_query($with_limit = false) {
        $is_union_buffer = false;
        
        // 1. work out how many match_against needed
        $match_against = array();
        
        // resume keywords
        if (!empty($this->resume_keywords['keywords'])) {
            $match_against['resume'] = array();
            
            $keywords_str = $this->resume_keywords['keywords'];
            $mode = " WITH QUERY EXPANSION";
            if ($this->resume_keywords['is_boolean']) {
                $mode = " IN BOOLEAN MODE";
                
                if ($this->resume_keywords['is_use_all_words']) {
                    $keywords_str = '+'. str_replace(' ', ' +', $this->keywords['keywords']);
                }
            }
            
            $match_against['resume']['member'] = "MATCH (resume_index.file_text) 
                                                  AGAINST ('". $keywords_str. "'". $mode. ")";
            $match_against['resume']['buffer'] = "MATCH (referral_buffers.resume_file_text) 
                                                  AGAINST ('". $keywords_str. "'". $mode. ")";
            $is_union_buffer = true;
        }
        
        // notes keywords
        if (!empty($this->notes_keywords['keywords'])) {
            $match_against['notes'] = array();
            
            $keywords_str = $this->notes_keywords['keywords'];
            $mode = " WITH QUERY EXPANSION";
            if ($this->notes_keywords['is_boolean']) {
                $mode = " IN BOOLEAN MODE";
                
                if ($this->notes_keywords['is_use_all_words']) {
                    $keywords_str = '+'. str_replace(' ', ' +', $this->keywords['keywords']);
                }
            }
            
            $match_against['notes']['member'] = "MATCH (member_index.notes) 
                                                 AGAINST ('". $keywords_str. "'". $mode. ")";
            $match_against['notes']['buffer'] = "MATCH (referral_buffers.notes) 
                                                 AGAINST ('". $keywords_str. "'". $mode. ")";
            $is_union_buffer = true;
        }
        
        // seeking keywords (members only)
        if (!empty($this->seeking_keywords['keywords'])) {
            $match_against['seeking'] = array();
            
            $keywords_str = $this->seeking_keywords['keywords'];
            $mode = " WITH QUERY EXPANSION";
            if ($this->seeking_keywords['is_boolean']) {
                $mode = " IN BOOLEAN MODE";
                
                if ($this->seeking_keywords['is_use_all_words']) {
                    $keywords_str = '+'. str_replace(' ', ' +', $this->keywords['keywords']);
                }
            }
            
            $match_against['seeking']['member'] = "MATCH (member_index.seeking) 
                                                   AGAINST ('". $keywords_str. "'". $mode. ")";
        }
        
        // 2. salaries
        // expected
        $salaries = array();
        if ($this->expected_salary['start'] > 0) {
            $salaries['expected'] = "members.expected_salary <= ". $this->expected_salary['start'];
            
            if ($this->expected_salary['end'] > 0) {
                $salaries['expected'] .= " AND members.expected_salary_end >= ". $this->expected_salary['end'];
            }
        }
        
        // current
        if ($this->current_salary['start'] > 0) {
            $salaries['current'] = "members.current_salary <= ". $this->current_salary['start'];
            
            if ($this->current_salary['end'] > 0) {
                $salaries['current'] .= " AND members.current_salary_end >= ". $this->current_salary['end'];
            }
        }
        
        // 3. others
        $query_others = "";
        if (!empty($this->hrm_gender)) {
            $query_others = "members.hrm_gender = '". $this->hrm_gender. "'";
        }
        
        if (!empty($this->hrm_ethnicity)) {
            if (!empty($query_others)) {
                $query_others .= " AND members.hrm_ethnicity LIKE '". $this->hrm_ethnicity. "'";
            } else {
                $query_others = "members.hrm_ethnicity LIKE '". $this->hrm_ethnicity. "'";
            }
        }
        
        if (!empty($this->is_active_seeking_job)) {
            if (!empty($query_others)) {
                $query_others .= " AND members.is_active_seeking_job = '". $this->is_active_seeking_job. "'";
            } else {
                $query_others = "members.is_active_seeking_job = '". $this->is_active_seeking_job. "'";
            }
        }
        
        if (!empty($this->can_travel_relocate)) {
            if (!empty($query_others)) {
                $query_others .= " AND members.can_travel_relocate = '". $this->can_travel_relocate. "'";
            } else {
                $query_others = "members.can_travel_relocate = '". $this->can_travel_relocate. "'";
            }
        }
        
        if (!empty($this->notice_period)) {
            if (!empty($query_others)) {
                $query_others .= " AND members.notice_period >= '". $this->notice_period. "'";
            } else {
                $query_others = "members.notice_period >= '". $this->notice_period. "'";
            }
        }
        
        // 4. setup columns and joins
        $columns = array();
        $columns['member'] = "'0' AS buffer_id, members.email_addr, members.phone_num, 
                               CONCAT(members.lastname, ', ', members.firstname) AS member_name, 
                               resumes.name AS resume_name, resumes.file_hash, resumes.id AS resume_id";
        if ($is_union_buffer) {
            $columns['buffer'] = "referral_buffers.id, referral_buffers.candidate_email,     
                                  referral_buffers.candidate_phone, referral_buffers.candidate_name,
                                  referral_buffers.resume_file_name, referral_buffers.resume_file_hash, '0'";
                                  
            if (array_key_exists('resume', $match_against)) {
                $columns['member'] .= ", ". $match_against['resume']['member']. " AS resume_score";
                $columns['buffer'] .= ", ". $match_against['resume']['buffer'];
            }
            
            if (array_key_exists('notes', $match_against)) {
                $columns['member'] .= ", ". $match_against['notes']['member']. " AS notes_score";
                $columns['buffer'] .= ", ". $match_against['notes']['buffer'];
            }
        }
        
        if (array_key_exists('seeking', $match_against)) {
            $columns['member'] .= ", ". $match_against['seeking']['member']. " AS seeking_score";
            
            if ($is_union_buffer) {
                $columns['buffer'] .= ", '0'";
            }
        }
        
        $joins['member'] = "LEFT JOIN resumes ON resumes.member = members.email_addr 
                            LEFT JOIN resume_index ON resume_index.resume = resumes.id";
        if (array_key_exists('seeking', $match_against) || 
            array_key_exists('notes', $match_against)) {
            $joins['member'] .= " LEFT JOIN member_index ON members.email_addr = member_index.member";
        }
        
        // 5. setup query
        $query = "SELECT ". $columns['member']. " 
                  FROM members 
                  ". $joins['member']. " 
                  WHERE ";
        if (!empty($match_against)) {
            foreach ($match_against as $i=>$table) {
                $query .= $table['member']. " ";

                if ($i <= count($match_against)-1) {
                    $query .= "AND ";
                }
            }
        }
        
        if (!empty($salaries)) {
            if (empty($match_against)) {
                $query .= "AND ";
            }
            
            foreach ($salaries as $i=>$criteria) {
                $query .= $criteria. " ";
                
                if ($i <= count($salaries)) {
                    $query .= "AND ";
                }
            }
        }
        
        if (!empty($query_others)) {
            if (!empty($match_against) || !empty($salaries)) {
                $query .= "AND ";
            }
            
            $query .= $query_others;
        }
        
        // 6. setup union, if any
        if ($is_union_buffer) {
            $query .= "UNION 
                       SELECT ". $columns['buffer']. "
                       FROM referral_buffers 
                       WHERE ";
            if (!empty($match_against)) {
                foreach ($match_against as $i=>$table) {
                   $query .= $table['buffer']. " ";

                   if ($i <= count($match_against)-1) {
                       $query .= "AND ";
                   }
                }
            }
        }
        
        // 7. setup query order, limit and offset
        $query .= $this->order_by;
        
        $limit = "";
        if ($with_limit) {
            $limit = $this->offset. ", ". $this->limit; 
            
            return $query. " LIMIT ". $limit;
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
        if (!empty($_criterias['resume_keywords'])) {
            $this->resume_keywords['keywords'] = $this->remove_punctuations_from(sanitize(trim($_criterias['resume_keywords'])));
            
            if ($_criterias['resume_is_boolean']) {
                $this->resume_keywords['is_boolean'] = true;
            }
            
            if ($_criterias['resume_is_use_all_words']) {
                $this->resume_keywords['is_use_all_words'] = true;
            }
        }
        
        if (!empty($_criterias['notes_keywords'])) {
            $this->notes_keywords['keywords'] = $this->remove_punctuations_from(sanitize(trim($_criterias['notes_keywords'])));
            
            if ($_criterias['notes_is_boolean']) {
                $this->notes_keywords['is_boolean'] = true;
            }
            
            if ($_criterias['notes_is_use_all_words']) {
                $this->notes_keywords['is_use_all_words'] = true;
            }
        }
        
        if (!empty($_criterias['seeking_keywords'])) {
            $this->seeking_keywords['keywords'] = $this->remove_punctuations_from(sanitize(trim($_criterias['seeking_keywords'])));
            
            if ($_criterias['seeking_is_boolean']) {
                $this->seeking_keywords['is_boolean'] = true;
            }
            
            if ($_criterias['seeking_is_use_all_words']) {
                $this->seeking_keywords['is_use_all_words'] = true;
            }
        }
        
        if ($_criterias['current_salary'] > 0) {
            $this->current_salary['start'] = $_criterias['current_salary'];
            $this->current_salary['end'] = 0;
            if (array_key_exists('current_salary_end', $_criterias)) {
                $this->current_salary['end'] = $_criterias['current_salary_end'];
            }
        }
        
        if ($_criterias['expected_salary'] > 0) {
            $this->expected_salary['start'] = $_criterias['expected_salary'];
            $this->expected_salary['end'] = 0;
            if (array_key_exists('expected_salary_end', $_criterias)) {
                $this->expected_salary['end'] = $_criterias['expected_salary_end'];
            }
        }
        
        if (array_key_exists('is_active_seeking_job', $_criteria)) {
            $this->is_active_seeking_job = ($_criteria['is_active_seeking_job']) ? 'TRUE' : 'FALSE';
        }
        
        if (array_key_exists('can_travel_relocate', $_criteria)) {
            $this->can_travel_relocate = ($_criteria['can_travel_relocate']) ? 'Y' : 'N';
        }
        
        if (array_key_exists('hrm_ethnicity', $_criteria)) {
            $ethnicities = explode(',', $_criteria['hrm_ethnicity']);
            $ethnicities_str = '';
            foreach ($ethnicities as $i=>$ethnicity) {
                $ethnicities_str .= trim($ethnicity);
                if ($i <= count($ethnicities)-1) {
                    $ethnicities_str .= '%';
                }
            }
            $this->hrm_ethnicity = $ethnicities_str;
        }
        
        if (array_key_exists('hrm_gender', $_criteria)) {
            $this->hrm_gender = $_criteria['hrm_gender'];
        }
        
        if (array_key_exists('notice_period', $_criteria)) {
            $this->notice_period = $criteria['notice_period'];
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
        
        $result = $this->mysqli->query($this->make_query());
        if (!is_null($result) && !empty($result)) {
            $this->total = count($result);
        }
        
        $total_pages = ceil($this->total / $this->limit);
        if ($total_pages <= 0) {
            return 0;
        }
        
        $start = microtime();
        $result = $this->mysqli->find($this->make_query(true));
        $end = microtime();
        
        list($ustart, $istart) = explode(" ", $start);
        list($uend, $iend) = explode(" ", $end);
        
        $this->time_elapsed = ((float)$uend + (float)$iend) - ((float)$ustart + (float)$istart);
        
        if ($result === false) {
            return false;
        }
        
        return $result;
    }
    
    public function time_elapsed() {
        return $this->time_elapsed;
    }
}
?>