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
        
        $criteria = array(
            'columns' => "CONCAT(employees.firstname, ', ', employees.lastname) AS employee_name,
                          employees.email_addr", 
            'joins' => "employees ON employees.id = employers.registered_by",
            'match' => "employers.id = '". $this->employer->getId(). "'"
        );
        $result = $this->employer->find($criteria);
        
        echo 'var employee_name = "'. htmlspecialchars_decode($result[0]['employee_name']). '";'. "\n";
        echo 'var employee_email = "'. $result[0]['email_addr']. '";'. "\n";
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
                
                $jobs_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'jobs.expire_on');\">Expire On</a>", '', 'header');
                $jobs_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'jobs.title');\">Job</a>", '', 'header');
                $jobs_table->set(0, 2, "<a class=\"sortable\" onClick=\"sort_by('referred_jobs', 'num_referrals');\">Resumes</a>", '', 'header');
                
                foreach ($referred_jobs as $i=>$referred_job) {
                    $jobs_table->set($i+1, 0, $referred_job['formatted_expire_on'], '', 'cell');
                    
                    $job_title = "<a class=\"no_link\" onClick=\"toggle_job_description('". $i. "');\">". $referred_job['title']. "</a>";
                    $job_title .= "<div id=\"inline_job_desc_". $i. "\" class=\"inline_job_desc\">". $referred_job['description']. "</div>";
                    $jobs_table->set($i+1, 1, $job_title, '', 'cell');
                    
                    $resumes = "<a class=\"no_link\" onClick=\"show_resumes_of('". $referred_job['id']. "', '". addslashes($referred_job['title']). "');\">". $referred_job['num_referrals'];
                    if ($referred_job['new_referrals_count'] > 0) {
                        $resumes .= "&nbsp;<span style=\"vertical-align: top; font-size: 7pt;\">[ ". $referred_job['new_referrals_count']. " new ]</span>";
                    }
                    $resumes .= "</a>";
                    $jobs_table->set($i+1, 2, $resumes, '', 'cell resumes_column');
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
            <div id="window_job_title" class="popup_window_title"></div>
            <div id="window_description"></div>
            <div class="popup_window_buttons_bar"><input type="button" value="Close" onClick="close_job_description_popup();" /></div>
        </div>
        
        <div id="testimony_window" class="popup_window">
            <input type="hidden" id="referral_id" value="0" />
            <div id="window_testimony_candidate" class="popup_window_title"></div>
            <div id="window_testimony"></div>
            <div class="popup_window_buttons_bar">
                <div class="instructions_label">
                    (Tip: Drag and select all to copy by pressing Ctrl + C or Command + C.)
                </div>
                <input type="button" value="Download PDF" onClick="download_testimony_pdf();" />
                <input type="button" value="Close" onClick="close_testimony_popup();" />
            </div>
        </div>
        
        <div id="remarks_window" class="popup_window">
            <div id="window_remarks_candidate" class="popup_window_title"></div>
            <textarea id="txt_remarks" class="txt_remarks"></textarea>
            <div class="popup_window_buttons_bar">
                <input type="hidden" id="remarks_referral_id" value="0" />
                <input type="hidden" id="remarks_candidate_idx" value="-1" />
                <input type="button" value="Save &amp; Close" onClick="close_remarks_popup(true);" />
                <input type="button" value="Close" onClick="close_remarks_popup(false);" />
            </div>
        </div>
        
        <div id="notify_window" class="popup_window">
            <div id="window_notify_consultant" class="popup_window_title"></div>
            <div class="message_options">
                Choose a request to send, and enter any further communications below.<br/><br/>
                <input type="radio" name="message" id="full_resume" checked /><label for="full_resume">Need full resume.&nbsp;
                <input type="radio" name="message" id="others" /><label for="others">Others.
                <br/><br/>
            </div>
            <textarea id="txt_message" class="txt_message"></textarea>
            <table class="reply_to_area">
                <tr>
                    <td style="width: 25%;">Send To Consultant:</td>
                    <td><span id="employee_name"></span>&nbsp;(<span id="employee_email"></span>)</td>
                </tr>
                <tr>
                    <td style="width: 25%;">Reply To Email:</td>
                    <td>
                        <input type="text" class="field" id="reply_to" value="" /><br/>
                        <span style="font-size: 7pt; color: #666666;">Tip: If this is left empty, the default email will be used for your consultant to reply to.</span>
                    </td>
                </tr>
            </table>
            <div class="popup_window_buttons_bar">
                <input type="hidden" id="notify_referral_id" value="0" />
                <input type="hidden" id="notify_candidate_idx" value="-1" />
                <input type="button" value="Send E-mail &amp; Close" onClick="close_notify_popup(true);" />
                <input type="button" value="Close" onClick="close_notify_popup(false);" />
            </div>
        </div>
        
        <div id="employment_window" class="popup_window">
            <div id="window_employment_title" class="popup_window_title"></div>
            <div class="employment_form">
                <table class="employment_form_table">
                    <tr>
                        <td class="label">Work Commencement:</td>
                        <td class="field">
                        <?php
                            $today = date('Y-m-d');
                            $date_components = explode('-', $today);
                            $year = $date_components[0];
                            $month = $date_components[1];
                            $day = $date_components[2];
                            
                            echo generate_dropdown('day', '', 1, 31, $day, 2, 'Day');
                            echo generate_month_dropdown('month', '', $month);
                            echo '<span id="year_label">'. $year. '</span>'. "\n";
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Annual Salary:</td>
                        <td class="field"><span id="currency"><?php echo $currency; ?></span>$&nbsp;<input type="text" class="salary_field" id="salary" name="salary" value="1.00" /></td>
                    </tr>
                </table>
            </div>
            <div class="popup_window_buttons_bar">
                <input type="hidden" id="employment_referral_id" value="0" />
                <input type="hidden" id="employment_candidate_idx" value="-1" />
                <input type="button" value="Confirm &amp; Close" onClick="close_employment_popup(true);" />
                <input type="button" value="Close" onClick="close_employment_popup(false);" />
            </div>
        </div>
        <?php
    }
}
?>