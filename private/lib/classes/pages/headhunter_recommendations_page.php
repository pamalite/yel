<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). '/../htmltable.php';

class HeadhunterRecommendationsPage extends Page {
    private $member = NULL;
    
    function __construct($_session) {
        parent::__construct();
        
        $this->member = new member($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_recommendations_css() {
        $this->insert_css('member_recommendations.css');
    }
    
    public function insert_member_recommendations_scripts() {
        $this->insert_scripts(array('flextable.js', 'member_recommendations.js'));
    }
    
    public function insert_inline_scripts() {
        $script = 'var id = "'. $this->member->getId(). '";'. "\n";
        
        $this->header = str_replace('<!-- %inline_javascript% -->', $script, $this->header);
    }
    
    public function show() {
        $this->begin();
        $this->top_search("Recommendations");
        $this->menu('headhunter', 'recommendations');
        $this->howitworks();
        
        $criteria = array(
            'columns' => "headhunter_referrals.*, jobs.alternate_employer, 
                          employers.name AS employer, jobs.title AS job_title,
                          DATE_FORMAT(headhunter_referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
                          DATE_FORMAT(headhunter_referrals.employer_agreed_on, '%e %b, %Y') AS formatted_agreed_on,
                          DATE_FORMAT(headhunter_referrals.employer_rejected_on, '%e %b, %Y') AS formatted_rejected_on, 
                          DATE_FORMAT(headhunter_referrals.interview_scheduled_on, '%e %b, %Y') AS formatted_scheduled_on",
            'joins' => "jobs ON jobs.id = headhunter_referrals.job, 
                        employers ON employers.id = jobs.employer", 
            'match' => "headhunter_referrals.member = '". $this->member->getId(). "'",
            'order' => "referred_on DESC"
        );
        
        $referrals = new HeadhunterReferral();
        $recommendations = $referrals->find($criteria);
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_recommendations">
        <?php
            if (empty($recommendations) || is_null($recommendations)) {
        ?>
            <div class="empty_results">No recommedations made.</div>
        <?php
            } else {
                $recommendations_table = new HTMLTable('recommendations_table', 'recommendations');
                
                $recommendations_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('headhunter_referrals', 'referred_on');\">Recommended On</a>", '', 'header');
                $recommendations_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('headhunter_referrals', 'jobs.title');\">Position</a>", '', 'header');
                $recommendations_table->set(0, 2, "Resume File Submitted", '', 'header');
                $recommendations_table->set(0, 3, "&nbsp;", '', 'header actions');

                foreach ($recommendations as $i=>$recommendation) {
                    // referred on
                    $recommendations_table->set($i+1, 0, $recommendation['formatted_referred_on'], '', 'cell');
                    
                    // position
                    $job_details = '<div class="candidate_name"><a href="../job/'. $recommendation['job']. '">'. htmlspecialchars_decode(stripslashes($recommendation['job_title'])). '</a></div><br/>';
                    $employer = $recommendation['employer'];
                    if (!is_null($recommendation['alternate_employer']) &&
                        !empty($recommendation['alternate_employer'])) {
                        $employer = $recommendation['alternate_employer'];
                    }
                    $job_details .= '<div class="small_contact"><span style="font-weight: bold;">Employer: </span>'. htmlspecialchars_decode(stripslashes($employer)). '</div>';
                    $recommendations_table->set($i+1, 1, $job_details, '', 'cell');
                    
                    // resume file
                    $resume_file = '<a href="recommendations_action.php?id='. $recommendation['id']. '&hash='. $recommendation['resume_file_hash']. '"> '. $recommendation['resume_file_name']. '</a>';
                    $recommendations_table->set($i+1, 2, $resume_file, '', 'cell');
                    
                    // status
                    $status = 'New';
                    if (!is_null($recommendation['employer_agreed_on']) && 
                        !empty($recommendation['employer_agreed_on'])) {
                        $status = '<span style="font-weight: bold;">Employer Accepted On: </span>'. $recommendation['formatted_agreed_on'];
                        
                        if (!is_null($recommendation['interview_scheduled_on']) && 
                            !empty($recommendation['interview_scheduled_on'])) {
                            $status .= '<br/><span style="font-weight: bold;">Interview Scheduled On: </span>'. $recommendation['formatted_scheduled_on'];
                        }
                    }
                    
                    if (!is_null($recommendation['employer_rejected_on']) && 
                        !empty($recommendation['employer_rejected_on'])) {
                        $status = '<span style="font-weight: bold;">Employer Rejected On: </span>'. $recommendation['formatted_rejected_on'];
                    }
                    $recommendations_table->set($i+1, 3, $status, '', 'cell actions');
                }

                echo $recommendations_table->get_html();
            }
        ?>
        </div>
        
        <?php
    }
}
?>