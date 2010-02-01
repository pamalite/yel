<?php
require_once dirname(__FILE__). "/../utilities.php";

class ResumeSearch {
    private $industry = 0;
    private $keywords = '';
    private $country_code = '';
    private $order_by = 'members.joined_on desc';
    private $limit = '';
    private $offset = 0;
    private $punctuations = array(",", ".",";","\"","\'","(",")","[","]","{","}","<",">");
    private $total = 0;
    private $pages = 1;
    private $changed_country_code = false;
    // private $use_exact = false;
    private $use_mode = 'or'; // 'or' or 'and'
    
    function __construct() {
        $this->country_code = $GLOBALS['default_country_code'];
        $this->limit = $GLOBALS['default_results_per_page'];
    }
    
    private function remove_punctuations_from($keywords) {
        return str_replace($this->punctuations, ' ', $keywords);
    }
    
    private function insert_wildcard_to_keywords($_keywords) {
        $keywords = explode(' ', trim($_keywords));
        $out = '%';

        foreach ($keywords as $word) {
            $out .= trim($word). '%';
        }

        return $out;
    }
    
    private function remove_stop_words($_keywords) {
        $out = array();
        $stop_words = $GLOBALS['stopWords'];
        $words = explode(' ', $_keywords);
        
        foreach ($words as $word) {
            if (!in_array($word, $stop_words)) {
                $out[] = $word;
            }
        }
        
        return implode(' ', $out);
    }
    
    private function make_query($with_limit = false) {
        $boolean_mode = '';
        $keywords = $this->keywords;
        
        // check what mode should we use
        $match_against = '';
        if (!empty($keywords) && !is_null($keywords)) {
            if ($this->use_mode == 'or') {
                // 'or' mode
                // $keywords = $this->insert_wildcard_to_keywords($keywords);
                
                $match_against = "MATCH (resume_index.cover_note, 
                                         resume_index.skill, 
                                         resume_index.technical_skill, 
                                         resume_index.qualification, 
                                         resume_index.work_summary, 
                                         resume_index.file_text) 
                                  AGAINST ('". $keywords. "' IN BOOLEAN MODE)";
            } else {
                // 'and' mode
                $keywords = '%'. $keywords. '%';
                
                $match_against = "resume_index.cover_note LIKE '". $keywords. "' OR 
                                  resume_index.qualification LIKE '". $keywords. "' OR 
                                  resume_index.work_summary LIKE '". $keywords. "' OR 
                                  resume_index.skill LIKE '". $keywords. "' OR 
                                  resume_index.technical_skill LIKE '". $keywords. "' OR 
                                  resume_index.file_text LIKE '". $keywords. "'";
            }
        }
        
        // check should we use BOOLEAN MODE
        // if (str_word_count($this->keywords) <= 3 || $this->use_exact) {
        //     $boolean_mode = ' IN BOOLEAN MODE';
        // }
        // 
        // if ($this->use_exact) {
        //     $keywords = '"'. $this->keywords. '"';
        // }
        // 
        // $match_against = "MATCH (resume_index.cover_note, 
        //                          resume_index.skill, 
        //                          resume_index.technical_skill, 
        //                          resume_index.qualification, 
        //                          resume_index.work_summary, 
        //                          resume_index.file_text) 
        //                   AGAINST ('". $keywords. "'". $boolean_mode. ")";
        
        $filter_industry = "members.primary_industry IS NOT NULL OR members.primary_industry IS NULL";
        if ($this->industry > 0) {
            $filter_industry = "(members.primary_industry = ". $this->industry. " OR members.secondary_industry = ". $this->industry. ")";
            $filter_industry .= " OR (recommender_industries.industry = ". $this->industry. ")";
        }
        
        $filter_country = "members.country LIKE '%'";
        if (!empty($this->country_code) && !is_null($this->country_code)) {
            $filter_country = "members.country = '". $this->country_code. "'";
        }
        
        $query = "SELECT DISTINCT members.email_addr, members.zip, members.phone_num, members.added_by, 
                  primary_industries.industry AS prime_industry, secondary_industries.industry AS second_industry, 
                  countries.country, resumes.id AS resume_id, resumes.name AS resume_label, 
                  resumes.file_hash, resumes.file_name, members.active, 
                  CONCAT(members.firstname, ', ', members.lastname) AS member, 
                  DATE_FORMAT(members.joined_on, '%e %b %Y') AS formatted_joined_on ";
        
        // if (!is_null($this->keywords) && !empty($this->keywords)) {
        //     $query .= ", ". $match_against. " AS relevance, relevances.max_relevance ";
        // } else {
        //     $query .= ", '1' AS relevance, '1' AS max_relevance ";
        // }
        
        $query .= "FROM resumes 
                   LEFT JOIN resume_index ON resume_index.resume = resumes.id 
                   LEFT JOIN members ON members.email_addr = resumes.member 
                   LEFT JOIN industries AS primary_industries ON primary_industries.id = members.primary_industry 
                   LEFT JOIN industries AS secondary_industries ON secondary_industries.id = members.secondary_industry 
                   LEFT JOIN countries ON countries.country_code = members.country 
                   LEFT JOIN recommender_industries ON recommender_industries.recommender = members.recommender ";
        
        if (!is_null($this->keywords) && !empty($this->keywords)) {
            // $query .= ", (SELECT MAX(". $match_against. ") AS max_relevance FROM resume_index LIMIT 1) relevances ";
            // $query .= "WHERE ". $match_against. " AND ";
            $query .= "WHERE (". $match_against. ") AND ";
        } else {
            $query .= "WHERE ";
        }
       
        $query .= "(". $filter_industry. ") 
                   AND (". $filter_country. ") 
                   AND members.active <> 'S' ";
       
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
        if ($_criterias['industry'] > 0) {
            $this->industry = $_criterias['industry'];
        }
        
        $keywords = sanitize(trim($_criterias['keywords']));
        if (!empty($keywords)) {
            $this->keywords = $this->remove_punctuations_from($keywords);
            $this->keywords = $this->remove_stop_words($this->keywords);
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
        
        // $this->use_exact = $_criterias['use_exact'];
        $this->use_mode = $_criterias['use_mode'];
        
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
        
        $query = $this->make_query(true);
        $result = $mysqli->query($query);
        
        if ($result === false) {
            return false;
        }
        
        // foreach($result as $i=>$row) {
        //     $result[$i]['match_percentage'] = number_format((($row['relevance'] / $row['max_relevance']) * 100.00), 0, '.', ', ');
        // }
        
        return $result;
    }
    
    public function country_code_changed() {
        return $this->changed_country_code;
    }
}
?>