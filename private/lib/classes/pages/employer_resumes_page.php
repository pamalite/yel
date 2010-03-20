<?php
require_once dirname(__FILE__). '/../../utilities.php';
require_once dirname(__FILE__). '/../htmltable.php';

class EmployerResumesPage extends Page {
    private $employer = NULL;
    
    function __construct($_session) {
        $this->employer = new Employer($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employer_resumes_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employer_resumes.css">'. "\n";
    }
    
    public function insert_employer_resumes_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/flextable.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employer_resumes.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employer->getId(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function get_referred_jobs() {
        $criteria = array(
            'columns' => "industries.industry, jobs.id, jobs.title, COUNT(referrals.id) AS num_referrals, 
                          DATE_FORMAT(jobs.expire_on, '%e %b, %Y') AS formatted_expire_on, 
                          jobs.description", 
            'joins' => 'jobs ON jobs.id = referrals.job, 
                        industries ON industries.id = jobs.industry', 
            'match' => "jobs.employer = '". $this->employer->getId(). "' AND 
                        need_approval = 'N' AND 
                        (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
                        (referrals.member_confirmed_on IS NOT NULL AND referrals.member_confirmed_on <> '0000-00-00 00:00:00') AND 
                        referrals.employer_removed_on IS NULL AND 
                        (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00')", 
            'group' => 'referrals.job',
            'order' => 'num_referrals DESC'
        );
        
        $referral = new Referral();
        $result = $referral->find($criteria);
        if ($result === false || is_null($result) || empty($result)) {
            return false;
        }
        
        foreach ($result as $i=>$row) {
            $result[$i]['description'] = htmlspecialchars_decode(desanitize($row['description']));
            $result[$i]['new_referrals_count'] = '0';
        }
        
        $criteria = array(
            'columns' => 'jobs.id, COUNT(referrals.id) AS num_new_referrals', 
            'joins' => 'jobs ON jobs.id = referrals.job, 
                        resumes ON resumes.id = referrals.resume', 
            'match' => "jobs.employer = '". $this->employer->getId(). "' AND 
                        (resumes.deleted = 'N' AND resumes.private = 'N') AND 
                        (referrals.employer_agreed_terms_on IS NULL OR referrals.employer_agreed_terms_on = '0000-00-00 00:00:00') AND 
                        (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
                        (referrals.member_confirmed_on IS NOT NULL AND referrals.member_confirmed_on <> '0000-00-00 00:00:00') AND 
                        (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
                        referrals.employer_removed_on IS NULL AND 
                        (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00')", 
            'group' => 'referrals.job'
        );
        
        $new_referrals = $referral->find($criteria);
        if ($new_referrals === false) {
            return false;
        }
        
        foreach ($new_referrals as $new_referral) {
            foreach ($result as $i=>$row) {
                if ($row['id'] == $new_referral['id']) {
                    $result[$i]['new_referrals_count'] = $new_referral['num_new_referrals'];
                    break;
                }
            }
        }
        
        return $result;
    }
    
    public function show() {
        $this->begin();
        $this->support($this->employer->getId());
        $this->top('Resumes');
        $this->menu('employer', 'resumes');
        
        $branch = $this->employer->getAssociatedBranch();
        $currency = $branch[0]['currency'];
        
        $referred_jobs = $this->get_referred_jobs();
        if ($referred_jobs === false || is_null($referred_jobs) || empty($referred_jobs)) {
            $referred_jobs = array();
        }
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_referred_jobs">
        <?php
            if (empty($referred_jobs)) {
        ?>
            <div class="empty_results">No applications found for all job posts at this moment.</div>
        <?php
            } else {
                $jobs_table = new HTMLTable('referred_jobs_table', 'referred_jobs');
                
                $jobs_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'industries.industry');\">Specialization</a>", '', 'header');
                $jobs_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'jobs.title');\">Job</a>", '', 'header');
                $jobs_table->set(0, 2, "<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'jobs.expire_on');\">Expire On</a>", '', 'header');
                $jobs_table->set(0, 3, "<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'num_referrals');\">Resumes</a>", '', 'header');
                
                foreach ($referred_jobs as $i=>$referred_job) {
                    $jobs_table->set($i+1, 0, $referred_job['industry'], '', 'cell');
                    
                    $job_title = "<a class=\"no_link\" onClick=\"toggle_job_description('". $i. "');\">". $referred_job['title']. "</a>";
                    $job_title .= "<div id=\"inline_job_desc_". $i. "\" class=\"inline_job_desc\">". $referred_job['description']. "</div>";
                    $jobs_table->set($i+1, 1, $job_title, '', 'cell');
                    
                    $jobs_table->set($i+1, 2, $referred_job['formatted_expire_on'], '', 'cell');
                    
                    $resumes = "<a class=\"no_link\" onClick=\"show_resumes_of('". $referred_job['id']. "', '". addslashes($referred_job['title']). "');\">". $referred_job['num_referrals'];
                    if ($referred_job['new_referrals_count'] > 0) {
                        $resumes .= "&nbsp;<span style=\"vertical-align: top; font-size: 7pt;\">[ ". $referred_job['new_referrals_count']. " new ]</span>";
                    }
                    $resumes .= "</a>";
                    $jobs_table->set($i+1, 3, $resumes, '', 'cell resumes_column');
                }
                
                echo $jobs_table->get_html();
            }
        ?>
        </div>
        
        <div id="div_resumes">
            <div class="resumes_top">
                <span class="back">
                    <a class="no_link" onClick="show_referred_jobs();">&lt;&lt; Back to Jobs</a>
                </span>
                <br/>
                <div class="job_title">
                    <span id="job_title"></span>
                </div>
            </div>
            
            <div style="width: 99%; margin: auto; text-align: right;">
                Show
                <select id="filter" onChange="show_resumes_of(current_job_id, current_job_title);">
                    <option value="" selected>All</option>
                    <option value="" disabled>&nbsp;</option>
                    <option value="no_star">No Star</option>
                    <option value="1_star">1 Star</option>
                    <option value="2_star">2 Stars</option>
                    <option value="3_star">3 Stars</option>
                    <option value="4_star">4 Stars</option>
                    <option value="5_star">5 Stars</option>
                    <option value="hired">Hired</option>
                </select>
            </div>
            <div id="resumes_list">
            </div>
        </div>
        
        <!-- popups goes here -->
        <div id="job_description_window" class="popup_window">
            <div id="window_job_title"></div>
            <div id="window_description"></div>
            <div class="buttons_bar"><input type="button" value="Close" onClick="close_job_description_popup();" /></div>
        </div>
        
        <?php
    }
}
?>