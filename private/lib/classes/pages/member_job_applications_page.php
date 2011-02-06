<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). '/../htmltable.php';

class MemberJobApplicationsPage extends Page {
    private $member = NULL;
    
    function __construct($_session) {
        parent::__construct();
        
        $this->member = new member($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_job_applications_css() {
        $this->insert_css('member_job_applications.css');
    }
    
    public function insert_member_job_applications_scripts() {
        $this->insert_scripts(array('flextable.js', 'member_job_applications.js'));
    }
    
    public function insert_inline_scripts() {
        $script = 'var id = "'. $this->member->getId(). '";'. "\n";
        
        $this->header = str_replace('<!-- %inline_javascript% -->', $script, $this->header);
    }
    
    public function show() {
        $this->begin();
        $this->top_search("Job Applications");
        $this->menu('member', 'job_applications');
        
        $applications = $this->member->getAllAppliedJobs();
        
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
                
                $applications_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('referrals', 'applied_on');\">Explored On</a>", '', 'header');
                $applications_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('referrals', 'job');\">Job Opportunity</a>", '', 'header');
                $applications_table->set(0, 2, "<a class=\"sortable\" onClick=\"sort_by('referrals', 'employer');\">Employer</a>", '', 'header');
                $applications_table->set(0, 3, "Resume Submitted", '', 'header');
                $applications_table->set(0, 4, "&nbsp;", '', 'header actions');

                foreach ($applications as $i=>$application) {
                    $applications_table->set($i+1, 0, $application['formatted_referred_on'], '', 'cell');
                    $applications_table->set($i+1, 1, '<a href="../job/'. $application['job_id']. '">'. $application['job']. '</a>', '', 'cell');
                    
                    $employer = $application['employer'];
                    if (!is_null($application['alternate_employer']) &&
                        !empty($application['alternate_employer'])) {
                        $employer = $application['alternate_employer'];
                    }
                    $applications_table->set($i+1, 2, $employer, '', 'cell');
                    
                    $resume = '';
                    if ($application['tab'] == 'buf') {
                        if (!is_null($application['resume_id']) &&
                            is_null($application['resume'])) {
                            // use pre uploaded resume
                            $resume = $application['buffered_stored_resume_file'];
                        } elseif (is_null($application['resume_id']) &&
                                  !is_null($application['resume'])) {
                            // use just uploaded resume
                            $resume = $application['resume'];
                        }
                    } else {
                        $resume = $application['resume'];
                    }
                    $applications_table->set($i+1, 3, $resume, '', 'cell');
                    
                    $button = 'Processing...';
                    if ($application['tab'] == 'ref') {
                        $button = '<input type="button" value="Confirm Employed" onClick="confirm_employment('. $application['id']. ', \''. addslashes($application['employer']). '\', \''. addslashes($application['job']). '\')" />';
                        if (!is_null($application['formatted_confirmed_on']) && 
                            !empty($application['formatted_confirmed_on'])) {
                            $button = '<span style="color: #666666; font-size: 9pt;">Employed on '. $application['formatted_employed_on']. '<br/>Confirmed on '. $application['formatted_confirmed_on']. ' </span>';
                        }
                    }
                    $applications_table->set($i+1, 4, $button, '', 'cell actions');
                }

                echo $applications_table->get_html();
            }
        ?>
        </div>
        
        <?php
    }
}
?>