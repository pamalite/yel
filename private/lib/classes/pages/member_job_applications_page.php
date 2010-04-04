<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). '/../htmltable.php';

class MemberJobApplicationsPage extends Page {
    private $member = NULL;
    
    function __construct($_session) {
        $this->member = new member($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_job_applications_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/member_job_applications.css">'. "\n";
    }
    
    public function insert_member_job_applications_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/flextable.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/member_job_applications.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->member->getId(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function get_applications() {
        $referral = new Referral();
        
        $criteria = array(
            'columns' => "referrals.id, referrals.job AS job_id, jobs.alternate_employer, 
                          employers.name AS employer, jobs.title AS job, 
                          resumes.file_name AS `resume`, referrals.`resume` AS resume_id, 
                          referrals.employer_agreed_terms_on, referrals.employed_on, 
                          DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
                          DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on,
                          DATE_FORMAT(referrals.referee_confirmed_hired_on, '%e %b, %Y') AS formatted_confirmed_on", 
            'joins' => "resumes ON resumes.id = referrals.`resume`, 
                        jobs ON jobs.id = referrals.job, 
                        employers ON employers.id = jobs.employer", 
            'match' => "referrals.referee = '". $this->member->getId(). "'",
            'order' => "referrals.referred_on DESC"
        );
        
        return $referral->find($criteria);
    }
    
    public function show() {
        $this->begin();
        $this->top_search("Job Applications");
        $this->menu('member', 'job_applications');
        
        $applications = $this->get_applications();
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_applications">
        <?php
            if (empty($applications)) {
        ?>
            <div class="empty_results">No jobs applied.</div>
        <?php
            } else {
                $applications_table = new HTMLTable('applications_table', 'applications');
                
                $applications_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('referrals', 'referrals.referred_on');\">Applied On</a>", '', 'header');
                $applications_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('referrals', 'jobs.title');\">Job</a>", '', 'header');
                $applications_table->set(0, 2, "<a class=\"sortable\" onClick=\"sort_by('referrals', 'employers.name');\">Employer</a>", '', 'header');
                $applications_table->set(0, 3, "<a class=\"sortable\" onClick=\"sort_by('referrals', 'resumes.file_name');\">Resume Submitted</a>", '', 'header');
                $applications_table->set(0, 4, "Status", '', 'header');
                $applications_table->set(0, 5, "&nbsp;", '', 'header actions');

                foreach ($applications as $i=>$application) {
                    $applications_table->set($i+1, 0, $application['formatted_referred_on'], '', 'cell');
                    $applications_table->set($i+1, 1, '<a href="../job/'. $application['job_id']. '">'. $application['job']. '</a>', '', 'cell');
                    
                    $employer = $application['employer'];
                    if (!is_null($application['alternate_employer']) &&
                        !empty($application['alternate_employer'])) {
                        $employer = $application['alternate_employer'];
                    }
                    $applications_table->set($i+1, 2, $employer, '', 'cell');
                    
                    $applications_table->set($i+1, 3, '<a href="resume.php?id='. $application['resume_id']. '">'. $application['resume']. '</a>', '', 'cell');
                    
                    $status = '<span style="font-weight: bold; color: #000000;">New</span>';
                    if (!is_null($application['employer_agreed_terms_on']) && 
                        $application['employer_agreed_terms_on'] != '0000-00-00 00:00:00') {
                        $status = '<span style="font-weight: bold; color: #0000FF;">Viewed</span>';
                    }
                    
                    if (!is_null($application['employed_on']) && 
                        $application['employed_on'] != '0000-00-00 00:00:00') {
                        $status = '<span style="font-weight: bold; color: #00FF00;">Employed</span>';
                    }
                    
                    $applications_table->set($i+1, 4, $status, '', 'cell');
                    
                    $button = '<input type="button" value="Confirm" onClick="confirm_employment('. $application['id']. ', \''. addslashes($application['employer']). '\', \''. addslashes($application['job']). '\')" />';
                    if (!is_null($application['formatted_confirmed_on']) && 
                        !empty($application['formatted_confirmed_on'])) {
                        $button = '<span style="color: #666666; font-size: 9pt;">Employed on '. $application['formatted_employed_on']. '<br/>Confirmed on '. $application['formatted_confirmed_on']. ' </span>';
                    }
                    $applications_table->set($i+1, 5, $button, '', 'cell actions');
                }

                echo $applications_table->get_html();
            }
        ?>
        </div>
        
        <?php
    }
}
?>