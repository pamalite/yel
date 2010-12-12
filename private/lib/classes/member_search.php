<?php
require_once dirname(__FILE__). "/../utilities.php";

class MemberSearch {
    private $email_addr = '';
    private $name = '';
    private $position = '';
    private $specialization = 0;
    private $employer = '';
    private $emp_specialization = 0;
    private $emp_desc = '0';
    private $total_work_years = 0;
    private $seeking_keywords = array();
    private $expected_salary = array();
    private $notice_period = 0;
    private $filter = '';
    
    private $order_by = 'members.lastname DESC';
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
        
        $this->seeking_keywords = array(
            'keywords' => '',
            'is_boolean' => false,
            'is_use_all_words' => false
        );
        
        $this->expected_salary = array(
            'currency' => '',
            'start' => 0,
            'end' => 0
        );
        
        $this->notice_period = 0;
        $this->filter = '';
        $this->order_by = 'members.lastname DESC';
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
    
    private function insert_wildcards($_keywords) {
        $out = "";
        $terms = explode(' ', $_keywords);
        
        foreach ($terms as $i=>$term) {
            $a_term = trim($term);
            if (!empty($a_term)) {
                $out .= $a_term;
                
                if ($i < count($terms)-1) {
                    $out .= '%';
                }
            }
        }
        
        if (substr($out, strlen($out)-1, -1) == '%') {
            $out = substr($out, 0, strlen($out)-1);
        }
        
        return $out;
    }
    
    private function make_query($with_limit = false) {
        $is_union_buffer = false;
        
        // 1. work out how many match_against needed
        $match_against = array();
        
        // // resume keywords
        // if (!empty($this->resume_keywords['keywords'])) {
        //     $match_against['resume'] = array();
        //     
        //     $keywords_str = $this->resume_keywords['keywords'];
        //     $mode = " WITH QUERY EXPANSION";
        //     if ($this->resume_keywords['is_boolean']) {
        //         $mode = " IN BOOLEAN MODE";
        //         
        //         if ($this->resume_keywords['is_use_all_words']) {
        //             $keywords_str = '+'. str_replace(' ', ' +', $this->keywords['keywords']);
        //         }
        //     }
        //     
        //     $match_against['resume']['member'] = "MATCH (resume_index.file_text) 
        //                                           AGAINST ('". $keywords_str. "'". $mode. ")";
        //     $match_against['resume']['buffer'] = "MATCH (referral_buffers.resume_file_text) 
        //                                           AGAINST ('". $keywords_str. "'". $mode. ")";
        //     $is_union_buffer = true;
        // }
        
        // // notes keywords
        // if (!empty($this->notes_keywords['keywords'])) {
        //     $match_against['notes'] = array();
        //     
        //     $keywords_str = $this->notes_keywords['keywords'];
        //     $mode = " WITH QUERY EXPANSION";
        //     if ($this->notes_keywords['is_boolean']) {
        //         $mode = " IN BOOLEAN MODE";
        //         
        //         if ($this->notes_keywords['is_use_all_words']) {
        //             $keywords_str = '+'. str_replace(' ', ' +', $this->keywords['keywords']);
        //         }
        //     }
        //     
        //     $match_against['notes']['member'] = "MATCH (member_index.notes) 
        //                                          AGAINST ('". $keywords_str. "'". $mode. ")";
        //     $match_against['notes']['buffer'] = "MATCH (referral_buffers.notes) 
        //                                          AGAINST ('". $keywords_str. "'". $mode. ")";
        //     $is_union_buffer = true;
        // }
        
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
        // if ($this->filter == trim('members_only')) {
        //     $is_union_buffer = false;
        // } elseif ($this->filter == trim('buffer_only')) {
        //     $is_union_buffer = false;
        //     $is_bypassed = true;
        // }
        
        $salaries = array();
        $query_others = "(members.email_addr NOT LIKE 'team%@yellowelevator.com' AND 
                          members.email_addr <> 'initial@yellowelevator.com')";
        if (!$is_bypassed) {
            // 2. salaries
            // expected
            if ($this->expected_salary['start'] > 0) {
                $salaries['expected'] = "members.expected_salary <= ". $this->expected_salary['start'];
                
                if ($this->expected_salary['end'] > 0) {
                    $salaries['expected'] .= " AND members.expected_salary_end >= ". $this->expected_salary['end'];
                }
                
                if (!empty($this->expected_salary['currency'])) {
                    $salaries['expected'] .= "members.expected_salary_currency = ". $this->expected_salary['currency'];
                }
                
                $salaries['expected'] = "(". $salaries['expected']. ")";
            }
            
            // 3. others
            if (!empty($this->email_addr)) {
                $query_others .= " AND members.email_addr = '". $this->email_addr. "'";
            }

            if (!empty($this->name)) {
                $query_others .= " AND (members.firstname LIKE '%". $this->name. "%' OR ";
                $query_others .= "members.lastname LIKE '%". $this->name. "%')";
            }

            if (!empty($this->position)) {
                $query_others .= " AND member_job_profiles.position_title LIKE '%". $this->position. "%'";
            }

            if (!empty($this->employer)) {
                $query_others .= " AND member_job_profiles.employer LIKE '%". $this->employer. "%'";
            }
            
            if ($this->specialization > 0) {
                $sub_industries = Industry::getSubIndustriesOf($this->specialization);
                if (empty($sub_industries) || is_null($sub_industries)) {
                    $query_others .= " AND member_job_profiles.specialization = ". $this->specialization;
                } else {
                    $this->specialization .= ', ';
                    foreach ($sub_industries as $i=>$sub_industry) {
                        $this->specialization .= $sub_industry['id'];
                        
                        if ($i < count($sub_industries)-1) {
                            $this->specialization .= ', ';
                        }
                    }
                    
                    $query_others .= " AND member_job_profiles.specialization IN (". $this->specialization. ")";
                }
            }
            
            if ($this->emp_specialization > 0) {
                $sub_industries = Industry::getSubIndustriesOf($this->specialization);
                if (empty($sub_industries) || is_null($sub_industries)) {
                    $query_others .= " AND member_job_profiles.employer_specialization = ". $this->emp_specialization;
                } else {
                    $this->emp_specialization .= ', ';
                    foreach ($sub_industries as $i=>$sub_industry) {
                        $this->emp_specialization .= $sub_industry['id'];
                        
                        if ($i < count($sub_industries)-1) {
                            $this->emp_specialization .= ', ';
                        }
                    }
                    
                    $query_others .= " AND member_job_profiles.employer_specialization IN (". $this->emp_specialization. ")";
                }
            }
            
            if ($this->emp_desc > 0) {
                $query_others .= " AND member_job_profiles.employer_description = ". $this->emp_desc;
            }
            
            if ($this->notice_period > 0) {
                $query_others .= " AND members.notice_period >= ". $this->notice_period;
            }
            
            if ($this->total_work_years > 0) {
                $query_others .= " AND members.total_work_years >= ". $this->total_work_years;
            }
        }
        
        // 4. setup columns and joins
        // $columns = array();
        // if (!$is_bypassed) {
        //     $columns['member'] = "'0' AS buffer_id, members.email_addr, members.phone_num, 
        //                            CONCAT(members.lastname, ', ', members.firstname) AS member_name, 
        //                            resumes.name AS resume_name, resumes.file_hash, resumes.id AS resume_id";
        // }
        // 
        // if ($is_union_buffer || ($is_union_buffer === false && $is_bypassed)) {
        //     $columns['buffer'] = "referral_buffers.id, referral_buffers.candidate_email,     
        //                           referral_buffers.candidate_phone, referral_buffers.candidate_name,
        //                           referral_buffers.resume_file_name, referral_buffers.resume_file_hash, '0'";
        //     if ($is_union_buffer === false && $is_bypassed) {
        //         $columns['buffer'] = "referral_buffers.id AS buffer_id, 
        //                               referral_buffers.candidate_email AS email_addr,
        //                               referral_buffers.candidate_phone, referral_buffers.candidate_name AS member_name,
        //                               referral_buffers.resume_file_name AS resume_name,
        //                               referral_buffers.resume_file_hash AS file_hash, 
        //                               '0' AS resume_id";
        //         
        //     }
        // }
        //  
        // if (array_key_exists('resume', $match_against)) {
        //     $columns['member'] .= ", ". $match_against['resume']['member']. " AS resume_score";
        //     
        //     if ($is_union_buffer) {
        //         $columns['buffer'] .= ", ". $match_against['resume']['buffer'];
        //     } 
        //     
        //     if ($is_union_buffer === false && $is_bypassed) {
        //         $columns['buffer'] .= ", ". $match_against['resume']['buffer']. " AS resume_score";
        //     }
        // }
        // 
        // if (array_key_exists('notes', $match_against)) {
        //     $columns['member'] .= ", ". $match_against['notes']['member']. " AS notes_score";
        //     
        //     if ($is_union_buffer) {
        //         $columns['buffer'] .= ", ". $match_against['notes']['buffer'];
        //     } 
        //     
        //     if ($is_union_buffer === false && $is_bypassed) {
        //         $columns['buffer'] .= ", ". $match_against['notes']['buffer']. " AS notes_score";
        //     }
        // }
        // 
        // $joins = array();
        // if (!$is_bypassed) {
        //     if (array_key_exists('seeking', $match_against)) {
        //         $columns['member'] .= ", ". $match_against['seeking']['member']. " AS seeking_score";
        //         
        //         if ($is_union_buffer) {
        //             $columns['buffer'] .= ", '0'";
        //         }
        //     }
        //     
        //     $joins['member'] = "LEFT JOIN resumes ON resumes.member = members.email_addr 
        //                         LEFT JOIN resume_index ON resume_index.resume = resumes.id";
        //     if (array_key_exists('seeking', $match_against) || 
        //         array_key_exists('notes', $match_against)) {
        //         $joins['member'] .= " LEFT JOIN member_index ON members.email_addr = member_index.member";
        //     }
        // }
        
        $columns = "members.email_addr, members.phone_num, members.active, 
                    CONCAT(members.lastname, ', ', members.firstname) AS member_name, 
                    DATE_FORMAT(members.joined_on, '%e %b, %Y') AS formatted_joined_on, 
                    members.total_work_years, members.is_active_seeking_job, 
                    members.can_travel_relocate, members.notice_period, 
                    members.expected_salary_currency, members.expected_salary, 
                    members.expected_salary_end";
        
        if (array_key_exists('seeking', $match_against)) {
            $columns .= ", ". $match_against['seeking']['member']. " AS seeking_score";
        }
        
        $joins = "";
        if (!empty($this->position) || !empty($this->employer) || 
            $this->specialization > 0 || $this->emp_specialization > 0 || 
            $this->emp_desc > 0) {
            $joins = "LEFT JOIN member_job_profiles ON member_job_profiles.member = members.email_addr";
        }
        
        if (array_key_exists('seeking', $match_against)) {
            $joins .= " LEFT JOIN member_index ON members.email_addr = member_index.member";
        }
        
        // 5. setup query
        $query = "";
        $query = "SELECT ". $columns. " 
                  FROM members 
                  ". $joins. " 
                  WHERE ";
        if (!empty($match_against)) {
            $query .= $match_against['seeking']['member']. " ";
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
        
        // if (!$is_bypassed) {
        //     $query = "SELECT ". $columns['member']. " 
        //               FROM members 
        //               ". $joins['member']. " 
        //               WHERE ";
        //     if (!empty($match_against)) {
        //         $sub_query_array = array();
        //         foreach ($match_against as $table) {
        //             if (isset($table['member'])) {
        //                 $sub_query_array[] = $table['member'];
        //             }
        //         }
        //         $query .= implode(" AND ", $sub_query_array);
        //         $query .= " ";
        //     }
        // 
        //     if (!empty($salaries)) {
        //         if (!empty($match_against)) {
        //             $query .= "AND ";
        //         }
        //         
        //         $i = 0;
        //         foreach ($salaries as $criteria) {
        //             $query .= $criteria. " ";
        // 
        //             if ($i < count($salaries)-1) {
        //                 $query .= "AND ";
        //             }
        //             
        //             $i++;
        //         }
        //     }
        // 
        //     if (!empty($query_others)) {
        //         if (!empty($match_against) || !empty($salaries)) {
        //             $query .= "AND ";
        //         }
        // 
        //         $query .= $query_others;
        //     }
        // }
        
        // 6. setup union, if any
        // if ($is_union_buffer || ($is_union_buffer === false && $is_bypassed)) {
        //     if (!$is_bypassed) {
        //         $query .= " UNION ";
        //     }
        //     $query .= "SELECT ". $columns['buffer']. "
        //                FROM referral_buffers 
        //                WHERE ";
        //     if (!empty($match_against)) {
        //         $sub_query_array = array();
        //         foreach ($match_against as $table) {
        //             if (isset($table['buffer'])) {
        //                 $sub_query_array[] = $table['buffer'];
        //             }
        //         }
        //         $query .= implode(" AND ", $sub_query_array);
        //         $query .= " ";
        //     }
        // }
        
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
        if (array_key_exists('email', $_criterias)) {
            $this->email_addr = $_criterias['email'];
        }
        
        if (array_key_exists('name', $_criterias)) {
            $this->name = $this->insert_wildcards($_criterias['name']);
        }
        
        if (array_key_exists('position', $_criterias)) {
            $this->position = $this->insert_wildcards($_criterias['position']);
        }
        
        if (array_key_exists('employer', $_criterias)) {
            $this->employer = $this->insert_wildcards($_criterias['employer']);
        }
        
        if (array_key_exists('specialization', $_criterias)) {
            $this->specialization = $_criterias['specialization'];
        }
        
        if (array_key_exists('emp_desc', $_criterias)) {
            $this->emp_desc = $_criterias['emp_desc'];
        }
        
        if (array_key_exists('emp_spec', $_criterias)) {
            $this->emp_specialization = $_criterias['emp_spec'];
        }
        
        if (array_key_exists('notice_period', $_criterias)) {
            $this->notice_period = $_criterias['notice_period'];
        }
        
        if (array_key_exists('total_work_years', $_criterias)) {
            $this->total_work_years = $_criterias['total_years'];
        }
        
        if (array_key_exists('expected_salary', $_criterias)) {
            if ($_criterias['expected_salary'] > 0) {
                $this->expected_salary['currency'] = $_criterias['expected_currency'];
                $this->expected_salary['start'] = $_criterias['expected_salary'];
                $this->expected_salary['end'] = 0;
                if (array_key_exists('expected_salary_end', $_criterias)) {
                    $this->expected_salary['end'] = $_criterias['expected_salary_end'];
                }
            }
        } 
        
        if (array_key_exists('seeking_keywords', $_criterias)) {
            if (!empty($_criterias['seeking_keywords'])) {
                $this->seeking_keywords['keywords'] = $this->remove_punctuations_from(sanitize(trim($_criterias['seeking_keywords'])));

                // if ($_criterias['seeking_is_boolean']) {
                //     $this->seeking_keywords['is_boolean'] = true;
                // }
                // 
                // if ($_criterias['seeking_is_use_all_words']) {
                //     $this->seeking_keywords['is_use_all_words'] = true;
                // }
            }
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
                $seeking_score = (isset($row['seeking_score'])) ? $row['seeking_score'] : 0;
                $result[$i]['total_score'] = $seeking_score;

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