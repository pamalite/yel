<?php
require_once dirname(__FILE__). "/../utilities.php";

class YEFacebook {
    private $api_id;
    private $available_ye_branches;
    private $error;
    
    function __construct() {
        $this->api_id = 'facebook';
        $this->error = '';
        
        $this->available_ye_branches = array(
            'MY' => 'Malaysia',
            'SG' => 'Singapore'
        );
    }
    
    private function log_api_usage($_usage='') {
        if (empty($_usage)) {
            $_usage = 'unknown';
        }
        
        $query = "INSERT INTO api_usage_log SET 
                  api_id = '". $this->api_id. "', 
                  `usage` = '". addslashes($_usage). "', 
                  used_on = NOW()";
        $mysqli = Database::connect();
        return $mysqli->execute($query);
    }
    
    private function array_to_text($_items) {
        if (!is_array($_items)) {
            return $_items;
        }
        
        $output = '';
        $i = 0;
        foreach ($_items as $key=>$value) {
            $output .= '['. $key. '] => '. $this->array_to_text($value);
            
            if ($i < count($_items)-1) {
                $output .= "\n";
            }
            $i++;
        }
        
        return $output;
    }
    
    private function is_member($_email_addr) {
        $member = new Member($_email_addr);
        return $member->isActive();
    }
    
    public function last_error() {
        return $this->error;
    }
    
    public function get_industries() {
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
    
    public function get_countries() {
        $criteria = array(
            'columns' => "DISTINCT countries.country_code, countries.country", 
            'joins' => "countries ON countries.country_code = jobs.country",
            // 'match' => "jobs.expire_on >= CURDATE() AND jobs.closed = 'N'", 
            'order' => "countries.country ASC"
        );

        $job = new Job();
        return $job->find($criteria);
    }
    
    public function get_employers() {
        $criteria = array(
            'columns' => 'DISTINCT employers.id, employers.name', 
            'joins' => 'jobs ON employers.id = jobs.employer',
            // 'match' => "jobs.expire_on >= CURDATE() AND jobs.closed = 'N'", 
            'order' => 'employers.name ASC'
        );
        $employer = new Employer();
        return $employer->find($criteria);
    }
    
    public function get_jobs($_keyword_str='', $_industry=0, $_country='', $_employer='', 
                             $_limit='', $_offset=0, 
                             $_order_by='jobs.created_on', $_order='DESC', 
                             $_salary_start=0, $_salary_end=0) {
        $jobs = array();
        
        if (empty($_limit) || $_limit <= 0) {
            $_limit = $GLOBALS['default_results_per_page'];
        }
        
        if (empty($_keyword_str) && empty($_country) && empty($_employer) &&
            ($_industry <= 0 || !is_numeric($_industry))) {
            $this->error = 'get_jobs : keywords, country, employer and industry cannot be empty.';
            return false;
        }
        
        $criteria = array();
        $criteria['order_by'] = $_order_by. ' '. $_order;
        $criteria['industry'] = $_industry;
        $criteria['employer'] = $_employer;
        $criteria['country_code'] = $_country;
        $criteria['limit'] = $_limit;
        $criteria['offset'] = $_offset;
        $criteria['keywords'] = $_keyword_str;
        $criteria['is_local'] = 1;
        $criteria['salary'] = $_salary_start;
        $criteria['salary_end'] = $_salary_end;
        
        $this->log_api_usage('get_jobs : '. $this->array_to_text($criteria));
        
        $job_search = new JobSearch();
        $result = $job_search->search_using($criteria);
        if ($result == 0) {
            return array();
        }
        
        if ($result === false) {
            $this->error = 'get_jobs : JobSearch encountered an error.';
            return false;
        }
        
        $jobs['total'] = $job_search->total_results(); 
        $jobs['jobs'] = $result;
        return $jobs;
    }
    
    public function get_recommendations($_referrer_email, $_order_by='referred_on', $_order='DESC') {
        if ($this->is_member($_referrer_email) === false) {
            $this->error = 'get_recommendations : referrer is not an active member of YE.';
            return false;
        }
        
        $order = $_order_by. ' '. $_order;
        
        $this->log_api_usage('get_recommendations : of '. $_referrer_email. '; order='. $order);
        
        $member = new Member($_referrer_email);
        return $member->getReferrals($order);
    }
    
    public function recommend_candidate($_candidate_email, $_candidate_name, $_candidate_phone='', 
                                        $_candidate_pos='not provided', 
                                        $_candidate_emp='not provided', 
                                        $_remarks='not provided', 
                                        $_job_id=0, 
                                        $_referrer_email='', $_referrer_name='', $_referrer_phone='') {
        if (empty($_candidate_email) || empty($_candidate_name) || $_job_id <= 0) {
            $this->error = 'recommend_candidate : candidate_email, name, phone or job_id is empty or invalid.';
            return false;
        }
        
        $referrer = array();
        $referrer['email_addr'] = $_referrer_email;
        $referrer['phone_num'] = $_referrer_phone;
        $referrer['name'] = $_referrer_name;

        $candidate = array();
        $candidate['email_addr'] = $_candidate_email;
        $candidate['phone_num'] = $_candidate_phone;
        $candidate['name'] = $_candidate_name;
        
        $today = now();
        
        $data = array();
        $data['requested_on'] = $today; 
        $data['referrer_email'] = $referrer['email_addr'];
        $data['referrer_phone'] = $referrer['phone_num'];
        $data['referrer_name'] = $referrer['name'];
        $data['candidate_email'] = $candidate['email_addr'];
        $data['candidate_phone'] = $candidate['phone_num'];
        $data['candidate_name'] = $candidate['name'];
        $data['job'] = $_job_id;
        $data['referrer_remarks'] = '<b>Current Position:</b><br/>'. $_candidate_pos. '<br/><br/><b>Current Employer:</b><br/>'. $_candidate_emp. '<br/><br/><b>Other Remarks:</b><br/>'. str_replace(array("\r\n", "\r", "\n"), '<br/>', $_remarks);
        
        $this->log_api_usage('recommend_candidate : '. $this->array_to_text($data));
        
        $referral_buffer = new ReferralBuffer();
        $buffer_id = $referral_buffer->create($data);
        if ($buffer_id === false) {
            $this->error = 'recommend_candidate : ReferralBuffer encountered an error while creating a new record.';
            return false;
        }
        
        return $buffer_id;
    }
    
    public function upload_resume_file($_buffer_id, $_file=array()) {
        if (empty($_buffer_id) || $_buffer_id <= 0 || $_buffer_id === false) {
            $this->error = 'upload_resume_file : the buffer_id is invalid.';
            return false;
        }
        
        $file_path = '';
        $resume_text = '';
        
        if (!empty($_file['name'])) {
            $type = $_file['type'];
            $size = $_file['size'];
            $name = $_file['name'];
            $temp = $_file['tmp_name'];

            if ($size <= $GLOBALS['resume_size_limit'] && $size > 0) {
                $is_upload_ok = false;
                foreach ($GLOBALS['allowable_resume_types'] as $mime_type) {
                    if ($type == $mime_type) {
                        $hash = generate_random_string_of(6);
                        $new_name = $buffer_id. ".". $hash;
                        $file_path = $GLOBALS['buffered_resume_dir']. "/". $new_name;

                        if (move_uploaded_file($temp, $file_path)) {
                            $data = array();
                            $data['resume_file_name'] = $name;
                            $data['resume_file_type'] = $type;
                            $data['resume_file_hash'] = $hash;
                            $data['resume_file_size'] = $size;

                            if ($referral_buffer->update($data)) {
                                if ($type == 'application/msword') {
                                    $data['needs_indexing'] = '1';
                                    if ($referral_buffer->update($data) === true) {
                                        $is_upload_ok = true;
                                    } else {
                                        @unlink($file_path);
                                    }
                                    break;
                                }

                                switch ($type) {
                                    case 'text/plain':
                                        $tmp = file_get_contents($file_path);
                                        $resume_text = sanitize($tmp);
                                        break;
                                    case 'text/html':
                                        $tmp = file_get_contents($file_path);
                                        $resume_text = sanitize(strip_tags($tmp));
                                        break;
                                    case 'application/pdf':
                                        $cmd = "/usr/local/bin/pdftotext ". $file_path. " /tmp/". $new_name;
                                        shell_exec($cmd);
                                        $tmp = file_get_contents('/tmp/'. $new_name);
                                        $resume_text = sanitize($tmp);

                                        if (!empty($tmp)) {
                                            unlink('/tmp/'. $new_name);
                                        }
                                        break;
                                    case 'application/msword':
                                        // $tmp = Resume::getTextFromMsword($file_path);
                                        // if (empty($tmp)) {
                                        //     $tmp = Resume::getTextFromRTF($file_path);
                                        // }
                                        // $resume_text = sanitize($tmp);
                                        break;
                                }

                                if (!empty($resume_text)) {
                                    $keywords = preg_split("/[\s,]+/", $resume_text);
                                    $resume_text = '';
                                    foreach ($keywords as $i=>$keyword) {
                                        $resume_text .= $keyword;

                                        if ($i < count($keywords)-1) {
                                            $resume_text .= ' ';
                                        }
                                    }

                                    $data['resume_file_text'] = sanitize(stripslashes($resume_text));
                                    if ($referral_buffer->update($data) === true) {
                                        $is_upload_ok = true;
                                    } else {
                                        @unlink($file_path);
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
                
                if (!$is_upload_ok) {
                    $this->error = 'upload_resume_file : file type is not allowed.';
                    return false;
                }
            } else {
                $this->error = 'upload_resume_file : resume is over the allowed size of '. $GLOBALS['resume_size_limit']. ' bytes.';
                return false;
            }
        }
        
        return true;
    }
    
    public function notify_ye_consultants($_buffer_id, $_country='my') {
        if (empty($_buffer_id) || $_buffer_id <= 0 || $_buffer_id === false) {
            $this->error = 'notify_ye_consultants : the buffer_id is invalid.';
            return false;
        }
        
        if (!array_key_exists(strtoupper($_country), $this->available_ye_branches)) {
            $_country = 'MY';
        }
        
        $buffer = new ReferralBuffer($_buffer_id);
        $result = $buffer->get();
        $job = new Job($result[0]['job']);
        
        $has_resume = 'YES';
        if (is_null($result[0]['existing_resume_id']) && 
            is_null($result[0]['resume_file_hash'])) {
            $has_resume = 'NO';
        }
        
        $branch_email = 'team.'. strtolower($_country). '@yellowelevator.com';
        
        $this->log_api_usage('notify_ye_consultants : '. $_buffer_id. ' send to '. $branch_email);
        
        $mail_lines = file(dirname(__FILE__). '/../../mail/new_referral.txt');
        $message = '';
        foreach ($mail_lines as $line) {
            $message .= $line;
        }

        $message = str_replace('%referrer%', htmlspecialchars_decode(stripslashes($result[0]['referrer_name'])), $message);
        $message = str_replace('%candidate%', htmlspecialchars_decode(stripslashes($result[0]['candidate_name'])), $message);
        $message = str_replace('%referrer_email%', $result[0]['referrer_email'], $message);
        $message = str_replace('%candidate_email%', $result[0]['candidate_email'], $message);
        $message = str_replace('%request_on%', $result[0]['requested_on'], $message);
        $message = str_replace('%job_title%', $job->getTitle(), $message);
        $message = str_replace('%has_resume%', $has_resume, $message);

        $subject = "[Facebook] New Referral for ". $job->getTitle(). " position";
        $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
        mail($branch_email, $subject, $message, $headers);

        // $handle = fopen('/tmp/email_to_'. $branch_email. '.txt', 'w');
        // fwrite($handle, 'Subject: '. $subject. "\n\n");
        // fwrite($handle, $message);
        // fclose($handle);
        
        return true;
    }
}
?>