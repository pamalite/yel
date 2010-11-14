<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). "/../htmltable.php";

class EmployeeStatusPage extends Page {
    private $employee = NULL;
    private $referrals = NULL;
    private $period = NULL;
    private $initial_total_pages = NULL;
    private $found_employers = array();
    
    function __construct($_session, $_member_id = '') {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->referrals = new Referral();
        
        $now = now();
        $this->period = array(
            0 => sql_date_add($now, -4, 'year'),
            //0 => sql_date_add($now, -1, 'year')
            1 => $now 
        );
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_status_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_status.css">'. "\n";
    }
    
    public function insert_employee_status_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/flextable.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_status.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->getId(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->getUserId(). '";'. "\n";
        echo 'var period = "'. $this->period[0]. ';'. $this->period[1]. '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function get_applications() {
        $criteria = array(
            'columns' => "referrals.id, referrals.member AS referrer, referrals.referee AS candidate, 
                          jobs.title AS job, jobs.id AS job_id, 
                          employers.name AS employer, employers.id AS employer_id, 
                          referrals.resume AS resume_id, resumes.file_name, 
                          CONCAT(referrers.lastname, ', ', referrers.firstname) AS referrer_name, 
                          CONCAT(candidates.lastname, ', ', candidates.firstname) AS candidate_name, 
                          DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
                          DATE_FORMAT(referrals.employer_agreed_terms_on, '%e %b, %Y') AS formatted_employer_agreed_terms_on, 
                          DATE_FORMAT(referrals.employer_rejected_on, '%e %b, %Y') AS formatted_employer_rejected_on, 
                          DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
                          DATE_FORMAT(referrals.employer_removed_on, '%e %b, %Y') AS formatted_employer_removed_on, 
                          DATE_FORMAT(referrals.referee_confirmed_hired_on, '%e %b, %Y') AS formatted_referee_confirmed_hired_on, 
                          IF(referrals.testimony IS NULL OR referrals.testimony = '', '0', '1') AS has_testimony, 
                          IF(referrals.employer_remarks IS NULL OR referrals.employer_remarks = '', '0', '1') AS has_employer_remarks", 
            'joins' => "members AS referrers ON referrers.email_addr = referrals.member, 
                        members AS candidates ON candidates.email_addr = referrals.referee, 
                        jobs ON jobs.id = referrals.job, 
                        employers ON employers.id = jobs.employer, 
                        resumes ON resumes.id = referrals.resume", 
            'match' => "referrals.referred_on BETWEEN '". $this->period[0]. "' AND '". $this->period[1]. "'",
            'order' => "referrals.referred_on DESC"
        );
        
        $result = $this->referrals->find($criteria);
        
        $this->found_employers = array();
        if (count($result) > 0 && !is_null($result)) {
            foreach ($result as $app) {
                $this->found_employers[$app['employer_id']] = $app['employer'];
            }
        }
        
        $page_limit = $GLOBALS['default_results_per_page'] + 10;
        $this->initial_total_pages = ceil(count($result) / $page_limit);
        $criteria['limit'] = "0, ". $page_limit;
        return $this->referrals->find($criteria);
    }
    
    public function show() {
        $this->begin();
        $this->top('Application Status');
        $this->menu_employee('status');
        
        $branch = $this->employee->getBranch();
        $applications = $this->get_applications();
        
        $employers_filter = '<select id="employers_filter" onChange="update_applications();">';
        $employers_filter .= '<option value="" selected>All Employers</option>';
        $employers_filter .= '<option value="" disabled>&nbsp;</option>';
        foreach ($this->found_employers as $key=>$value) {
            $employers_filter .= '<option value="'. $key. '">'. $value. '</option>';
        }
        $employers_filter .= '</select>';
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div class="pagination">
            Page
            <select id="current_page" onChange="goto_page();">
        <?php
            for($i=0; $i < $this->initial_total_pages; $i++) {
        ?>
                <option value="<?php echo $i; ?>"><?php echo ($i+1); ?></option>
        <?php
            }
        ?>
            </select>
            of <span id="total_pages"><?php echo $this->initial_total_pages; ?></span>
        </div>
        
        <div id="div_filter">
        <?php
            $from_timestamp = explode(' ', $this->period[0]);
            $from_date = explode('-', $from_timestamp[0]);
            $month_from_dropdown = generate_month_dropdown('from_month', '', $from_date[1]);
            $day_from_dropdown = generate_dropdown('from_day', '', 1, 31, $from_date[2]);
            
            $to_timestamp = explode(' ', $this->period[1]);
            $to_date = explode('-', $to_timestamp[0]);
            $month_to_dropdown = generate_month_dropdown('to_month', '', $to_date[1]);
            $day_to_dropdown = generate_dropdown('to_day', '', 1, 31, $to_date[2]);
        ?>
            Show
            <select id="filter">
                <option value="" selected>All</option>
                <option value="" disabled>&nbsp;</option>
                <option value="not_viewed">Not Viewed &amp; applied</option>
                <option value="viewed">Viewed</option>
                <option value="employed">Employed</option>
                <option value="rejected">Rejected</option>
                <option value="removed">Deleted</option>
                <option value="confirmed">Confirmed &amp; applied</option>
            </select>
            between
            <input type="text" class="mini_field" id="from_year" maxlength="4" value="<?php echo $from_date[0]; ?>" />
            <?php echo $month_from_dropdown. ' '. $day_from_dropdown; ?>
            and
            <input type="text" class="mini_field" id="to_year" maxlength="4" value="<?php echo $to_date[0];?>" />
            <?php echo $month_to_dropdown. ' '. $day_to_dropdown; ?>
            <input type="button" onClick="filter_applications();" value="Filter" />
        </div>
        
        <div id="member_applications">
            <div id="applications">
        <?php
            if (empty($applications)) {
        ?>
                <div class="empty_results">No applications found.</div>
        <?php
            } else {
                $applications_table = new HTMLTable('applications_table', 'applications');
                
                $applications_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('referrals', 'referrals.referred_on');\">Applied On</a>", '', 'header');
                $applications_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('referrals', 'candidates.lastname');\">Candidate</a>", '', 'header');
                $applications_table->set(0, 2, "<a class=\"sortable\" onClick=\"sort_by('referrals', 'jobs.title');\">Job</a> from ". $employers_filter, '', 'header');
                $applications_table->set(0, 3, 'Status', '', 'header');

                foreach ($applications as $i=>$application) {
                    $applications_table->set($i+1, 0, $application['formatted_referred_on'], '', 'cell');
                    
                    $candidate_details = '<a href="member.php?member_email_addr='. $application['candidate']. '">'. $application['candidate_name']. '</a>';
                    $candidate_details .= '<div class="resume"><span style="font-weight: bold;">Resume:</span> <a href="resume.php?id='. $application['resume_id']. '">'. $application['file_name']. '</a></div>';
                    
                    $ref_email = explode('@', $application['referrer']);
                    $ref_email_front = explode('.', $ref_email[0]);
                    if ($ref_email_front[0] == 'team' && $ref_email[1] == 'yellowelevator.com') {
                        $candidate_details .= '<br/><div class="referrer">Self Applied</div>';
                    } else {
                        $candidate_details .= '<br/><div class="referrer"><a href="member.php?member_email_addr='. $application['referrer']. '">'. $application['referrer_name']. '</a></div>';
                    }
                    $applications_table->set($i+1, 1, $candidate_details, '', 'cell');
                    
                    $job_details = '<a class="no_link" onClick="show_job_desc('. $application['job_id']. ');">'. $application['job']. '</a>';
                    $job_details .= '<br/><br/><div class="employer"><a href="employer.php?id='. $application['employer_id']. '">'. $application['employer']. '</a></div>';
                    $applications_table->set($i+1, 2, $job_details, '', 'cell');
                    
                    $status = '<span class="not_viewed_yet">Not Viewed Yet</a>';
                    if (!is_null($application['formatted_employer_agreed_terms_on'])) {
                        $status = '<span class="viewed">Viewed on:</span> '. $application['formatted_employer_agreed_terms_on'];
                    }
                    
                    if (!is_null($application['formatted_employed_on'])) {
                        $status = '<span class="employed">Employed on:</span> '. $application['formatted_employed_on'];
                    }
                    
                    if (!is_null($application['formatted_employer_rejected_on'])) {
                        $status = '<span class="rejected">Rejected on:</span> '. $application['formatted_employer_rejected_on'];
                    }
                    
                    if (!is_null($application['formatted_employer_removed_on'])) {
                        $status = '<span class="removed">Deleted on:</span> '. $application['formatted_employer_removed_on'];
                    }
                    
                    if ($application['has_employer_remarks'] == '1') {
                        $status .= '<br/><a class="no_link" onClick="show_employer_remarks('. $application['id']. ');">Employer Remarks</a>';
                    }
                    
                    if (!is_null($application['formatted_referee_confirmed_hired_on'])) {
                        $status .= '<br/><span class="confirmed">Confirmed on: </span>'. $application['formatted_referee_confirmed_hired_on'];
                    }
                    $applications_table->set($i+1, 3, $status, '', 'cell testimony');
                    
                    // $testimony = 'None Provided';
                    // if ($application['has_testimony'] == '1') {
                    //     $testimony = '<a class="no_link" onClick="show_testimony('. $application['id']. ');">Show</a>';
                    // }
                    // $applications_table->set($i+1, 6, $testimony, '', 'cell testimony');
                }

                echo $applications_table->get_html();
            }
        ?>
            </div>
        </div>
        
        <div class="pagination">
            Page
            <select id="current_page_bottom" onChange="goto_page(true);">
        <?php
            for($i=0; $i < $this->initial_total_pages; $i++) {
        ?>
                <option value="<?php echo $i; ?>"><?php echo ($i+1); ?></option>
        <?php
            }
        ?>
            </select>
            of <span id="total_pages_bottom"><?php echo $this->initial_total_pages; ?></span>
        </div>
        
        <!-- popup windows goes here -->
        <div id="testimony_window" class="popup_window">
            <div class="popup_window_title">Testimony</div>
            <div class="testimony_form">
                <br/>
                <span id="testimony"></span>
                <br/><br/>
            </div>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Cancel" onClick="close_testimony();" />
            </div>
        </div>
        
        <div id="job_desc_window" class="popup_window">
            <div class="popup_window_title">Job Description</div>
            <div class="job_desc_form">
                <br/>
                <span id="job_desc"></span>
                <br/><br/>
            </div>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Cancel" onClick="close_job_desc();" />
            </div>
        </div>
        
        <div id="employer_remarks_window" class="popup_window">
            <div class="popup_window_title">Employer Remarks</div>
            <div class="employer_remarks_form">
                <br/>
                <span id="employer_remarks"></span>
                <br/><br/>
            </div>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Cancel" onClick="close_employer_remarks();" />
            </div>
        </div>
        <?php
    }
}
?>