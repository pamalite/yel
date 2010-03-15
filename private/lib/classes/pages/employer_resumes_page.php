<?php
require_once dirname(__FILE__). "/../../utilities.php";

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
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employer_resumes_page.js"></script>'. "\n";
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
        
        $criteria = '';
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
        ?>
            <table class="referred_jobs">
                <tr>
                    <td class="header">
                        <a class="sortable" onClick="sort_by('industries.industry');">
                            Specialization
                        </a>
                    </td>
                    <td class="header">
                        <a class="sortable" onClick="sort_by('jobs.title');">Job</a>
                    </td>
                    <td class="header">
                        <a class="sortable" onClick="sort_by('jobs.expire_on');">Expire On</span>
                    </td>
                    <td class="header">
                        <a class="sortable" onClick="sort_by('num_referrals');">Resumes</a>
                    </td>
                </tr>
        <?php
                foreach ($referred_jobs as $i=>$referred_job) {
        ?>
                <tr>
                    <td class="cell"><?php echo $referred_job['industry']; ?></td>
                    <td class="cell">
                        <a class="no_link" onClick="toggle_job_description('<?php echo $i; ?>');"><?php echo $referred_job['title']; ?></a>
                        <div id="inline_job_desc_<?php echo $i; ?>" class="inline_job_desc">
                            <?php echo $referred_job['description']; ?>
                        </div>
                    </td>
                    <td class="cell"><?php echo $referred_job['formatted_expire_on']; ?></td>
                    <td class="cell resumes_column">
                        <a class="no_link" onClick="show_resumes_of('<?php echo $referred_job['id']; ?>');">
                            <?php 
                                echo $referred_job['num_referrals'];
                                if ($referred_job['new_referrals_count'] > 0) {
                                    echo '&nbsp;<span style="vertical-align: top; font-size: 7pt;">[ '. $referred_job['new_referrals_count']. ' new ]</span>';
                                }
                            ?>
                        </a>
                    </td>
                </tr>
        <?php
                }
        ?>
            </table>
        <?php
            }
        ?>
        </div>
        <?php
    }
}
?>