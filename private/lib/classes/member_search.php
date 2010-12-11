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
    private $filter = '';
    
    private $order_by = 'member_name DESC';
    private $limit = '';
    private $offset = 0;
    private $punctuations = array(",", ".",";","\"","\'","(",")","[","]","{","}","<",">");
    private $total = 0;
    private $pages = 1;
    private $time_elapsed = 0;
    private $mysqli = NULL;
    private $query = "";
    
    function __construct() {
        if (!is_a($this->mysqli, "MySQLi")) {
            $this->mysqli = Database::connect();
        }
        
        $this->initialize();
    }
    
    private function initialize() {
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
        
        $this->hrm_gender = '';
        $this->hrm_ethnicity = '';
        $this->is_active_seeking_job = '';
        $this->can_travel_relocate = '';
        $this->notice_period = '';
        $this->filter = '';
        $this->order_by = 'member_name DESC';
        $this->offset = 0;
        $this->punctuations = array(",", ".",";","\"","\'","(",")","[","]","{","}","<",">");
        $this->total = 0;
        $this->pages = 1;
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
        
        // 1.5 If filter for buffer only is turned on, then bypass the rest.
        $is_bypassed = false;
        if ($this->filter == trim('members_only')) {
            $is_union_buffer = false;
        } elseif ($this->filter == trim('buffer_only')) {
            $is_union_buffer = false;
            $is_bypassed = true;
        }
        
        $salaries = array();
        $query_others = "";
        if (!$is_bypassed) {
            // 2. salaries
            // expected
            if ($this->expected_salary['start'] > 0) {
                $salaries['expected'] = "members.expected_salary <= ". $this->expected_salary['start'];

                if ($this->expected_salary['end'] > 0) {
                    $salaries['expected'] .= " AND members.expected_salary_end >= ". $this->expected_salary['end'];
                }
                
                $salaries['expected'] = "(". $salaries['expected']. ")";
            }

            // current
            if ($this->current_salary['start'] > 0) {
                $salaries['current'] = "members.current_salary <= ". $this->current_salary['start'];

                if ($this->current_salary['end'] > 0) {
                    $salaries['current'] .= " AND members.current_salary_end >= ". $this->current_salary['end'];
                }
                
                $salaries['current'] = "(". $salaries['current']. ")";
            }
            
            // 3. others
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
                    $query_others .= " AND members.is_active_seeking_job = ". $this->is_active_seeking_job;
                } else {
                    $query_others = "members.is_active_seeking_job = ". $this->is_active_seeking_job;
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
                    $query_others .= " AND members.notice_period >= ". $this->notice_period;
                } else {
                    $query_others = "members.notice_period >= ". $this->notice_period;
                }
            }
        }
        
        // 4. setup columns and joins
        $columns = array();
        if (!$is_bypassed) {
            $columns['member'] = "'0' AS buffer_id, members.email_addr, members.phone_num, 
                                   CONCAT(members.lastname, ', ', members.firstname) AS member_name, 
                                   resumes.name AS resume_name, resumes.file_hash, resumes.id AS resume_id";
        }
        
        if ($is_union_buffer || ($is_union_buffer === false && $is_bypassed)) {
            $columns['buffer'] = "referral_buffers.id, referral_buffers.candidate_email,     
                                  referral_buffers.candidate_phone, referral_buffers.candidate_name,
                                  referral_buffers.resume_file_name, referral_buffers.resume_file_hash, '0'";
            if ($is_union_buffer === false && $is_bypassed) {
                $columns['buffer'] = "referral_buffers.id AS buffer_id, 
                                      referral_buffers.candidate_email AS email_addr,
                                      referral_buffers.candidate_phone, referral_buffers.candidate_name AS member_name,
                                      referral_buffers.resume_file_name AS resume_name,
                                      referral_buffers.resume_file_hash AS file_hash, 
                                      '0' AS resume_id";
                
            }
        }
         
        if (array_key_exists('resume', $match_against)) {
            $columns['member'] .= ", ". $match_against['resume']['member']. " AS resume_score";
            
            if ($is_union_buffer) {
                $columns['buffer'] .= ", ". $match_against['resume']['buffer'];
            } 
            
            if ($is_union_buffer === false && $is_bypassed) {
                $columns['buffer'] .= ", ". $match_against['resume']['buffer']. " AS resume_score";
            }
        }
        
        if (array_key_exists('notes', $match_against)) {
            $columns['member'] .= ", ". $match_against['notes']['member']. " AS notes_score";
            
            if ($is_union_buffer) {
                $columns['buffer'] .= ", ". $match_against['notes']['buffer'];
            } 
            
            if ($is_union_buffer === false && $is_bypassed) {
                $columns['buffer'] .= ", ". $match_against['notes']['buffer']. " AS notes_score";
            }
        }
        
        $joins = array();
        if (!$is_bypassed) {
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
        }
        
        // 5. setup query
        $query = "";
        if (!$is_bypassed) {
            $query = "SELECT ". $columns['member']. " 
                      FROM members 
                      ". $joins['member']. " 
                      WHERE ";
            if (!empty($match_against)) {
                $sub_query_array = array();
                foreach ($match_against as $table) {
                    if (isset($table['member'])) {
                        $sub_query_array[] = $table['member'];
                    }
                }
                $query .= implode(" AND ", $sub_query_array);
                $query .= " ";
            }

            if (!empty($salaries)) {
                if (!empty($match_against)) {
                    $query .= "AND ";
                }
                
                $i = 0;
                foreach ($salaries as $criteria) {
                    $query .= $criteria. " ";

                    if ($i < count($salaries)-1) {
                        $query .= "AND ";
                    }
                    
                    $i++;
                }
            }

            if (!empty($query_others)) {
                if (!empty($match_against) || !empty($salaries)) {
                    $query .= "AND ";
                }

                $query .= $query_others;
            }
        }
        
        // 6. setup union, if any
        if ($is_union_buffer || ($is_union_buffer === false && $is_bypassed)) {
            if (!$is_bypassed) {
                $query .= " UNION ";
            }
            $query .= "SELECT ". $columns['buffer']. "
                       FROM referral_buffers 
                       WHERE ";
            if (!empty($match_against)) {
                $sub_query_array = array();
                foreach ($match_against as $table) {
                    if (isset($table['buffer'])) {
                        $sub_query_array[] = $table['buffer'];
                    }
                }
                $query .= implode(" AND ", $sub_query_array);
                $query .= " ";
            }
        }
        
        // 7. setup query order, limit and offset
        if (substr(trim($this->order_by), 0, 5) == 'score') {
            return $query;
        }
        
        $query .= " ORDER BY ". $this->order_by;
        
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
        
        if (array_key_exists('is_active_seeking_job', $_criterias)) {
            $this->is_active_seeking_job = ($_criterias['is_active_seeking_job']) ? 'TRUE' : 'FALSE';
        }
        
        if (array_key_exists('can_travel_relocate', $_criterias)) {
            $this->can_travel_relocate = ($_criterias['can_travel_relocate']) ? 'Y' : 'N';
        }
        
        if (array_key_exists('hrm_ethnicity', $_criterias)) {
            $ethnicities = explode(',', $_criterias['hrm_ethnicity']);
            $ethnicities_str = '';
            foreach ($ethnicities as $i=>$ethnicity) {
                $ethnicities_str .= trim($ethnicity);
                if ($i < count($ethnicities)-1) {
                    $ethnicities_str .= '%';
                }
            }
            $this->hrm_ethnicity = $ethnicities_str;
        }
        
        if (array_key_exists('hrm_gender', $_criterias)) {
            $this->hrm_gender = $_criterias['hrm_gender'];
        }
        
        if (array_key_exists('notice_period', $_criterias)) {
            $this->notice_period = $_criterias['notice_period'];
        }
        
        if (array_key_exists('filter', $_criterias)) {
            $this->filter = $_criterias['filter'];
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
        
        $this->query = $this->make_query();
        $result = $this->mysqli->query($this->query);
        if (!is_null($result) && !empty($result)) {
            $this->total = count($result);
        }
        
        // paginate
        $total_pages = ceil($this->total / $this->limit);
        if ($total_pages <= 0) {
            echo $this->query;
            return 0;
        }
        
        $start = microtime();
        if (substr(trim($this->order_by), 0, 5) == 'score') {
            // get the total score and relevance
            $score_max = 1;
            foreach ($result as $i=>$row) {
                $resume_score = (isset($row['resume_score'])) ? $row['resume_score'] : 0;
                $notes_score = (isset($row['notes_score'])) ? $row['notes_score'] : 0;
                $seeking_score = (isset($row['seeking_score'])) ? $row['seeking_score'] : 0;
                $total_score = $resume_score + $notes_score + $seeking_score;
                $result[$i]['total_score'] = $total_score;

                if ($total_score > $score_max) {
                    $score_max = $total_score;
                }
            }

            foreach ($result as $i=>$row) {
                $result[$i]['relevance'] = floor(($row['total_score'] / $score_max) * 100);
            }
            
            // sort and paginate manually
            usort($result, "sort_by_total_score");
            $order = explode(' ', $this->order_by);
            if ($order[1] == 'ASC') {
                $tmp = array_reverse($result);
                $result = $tmp;
            }
            $rows = array();
            for ($i=$this->offset; $i <= ($this->offset + $this->limit) - 1; $i++) {
                if (isset($result[$i])) {
                    $rows[] = $result[$i];
                }
            }
            
            $result = $rows;
        } else {
            // just sort and paginate normally
            $this->query = $this->make_query(true);
            $result = $this->mysqli->query($this->query);
        }
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
    
    public function reset_query() {
        $this->query = "";
        $this->initialize();
    }
    
    public function get_query() {
        return $this->query;
    }
}

function sort_by_total_score($before, $after) {
    $before_total_score = $before['total_score']; 
    $after_total_score = $after['total_score']; 

    if ($before_total_score == $after_total_score) {
        return 0;
    }

    if ($before_total_score < $after_total_score) {
        return 1;
    } else {
        return -1;
    }
}

?>