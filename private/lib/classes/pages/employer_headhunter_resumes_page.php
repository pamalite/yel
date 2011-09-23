<?php
require_once dirname(__FILE__). '/../../utilities.php';
require_once dirname(__FILE__). '/../htmltable.php';

class EmployerHeadhunterResumesPage extends Page {
    private $employer = NULL;
    
    function __construct($_session) {
        parent::__construct();
        
        $this->employer = new Employer($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employer_resumes_css() {
        $this->insert_css('employer_resumes.css');
    }
    
    public function insert_employer_resumes_scripts() {
        $this->insert_scripts(array('flextable.js', 'employer_hh_resumes.js'));
    }
    
    public function insert_inline_scripts() {
        $script = 'var id = "'. $this->employer->getId(). '";'. "\n";
        
        $criteria = array(
            'columns' => "CONCAT(employees.firstname, ', ', employees.lastname) AS employee_name,
                          employees.email_addr", 
            'joins' => "employees ON employees.id = employers.registered_by",
            'match' => "employers.id = '". $this->employer->getId(). "'"
        );
        $result = $this->employer->find($criteria);
        
        $script .= 'var employee_name = "'. htmlspecialchars_decode($result[0]['employee_name']). '";'. "\n";
        $script .= 'var employee_email = "'. $result[0]['email_addr']. '";'. "\n";
        
        $this->header = str_replace('<!-- %inline_javascript% -->', $script, $this->header);
    }
    
    private function get_referred_jobs() {
        $criteria = array(
            'columns' => "industries.industry, jobs.id, jobs.title, 
                          COUNT(headhunter_referrals.id) AS num_referrals, 
                          DATE_FORMAT(jobs.expire_on, '%e %b, %Y') AS formatted_expire_on, 
                          jobs.description", 
            'joins' => 'jobs ON jobs.id = headhunter_referrals.job, 
                        industries ON industries.id = jobs.industry', 
            'match' => "jobs.employer = '". $this->employer->getId(). "' AND 
                        headhunter_referrals.employer_rejected_on IS NULL AND 
                        headhunter_referrals.employer_removed_on IS NULL", 
            'group' => 'headhunter_referrals.job',
            'order' => 'num_referrals DESC'
        );
        
        $referral = new HeadhunterReferral();
        $result = $referral->find($criteria);
        if ($result === false || is_null($result) || empty($result)) {
            return false;
        }
        
        foreach ($result as $i=>$row) {
            $result[$i]['description'] = htmlspecialchars_decode(desanitize($row['description']));
        }
        
        return $result;
    }
    
    public function show() {
        $this->begin();
        $this->support($this->employer->getId());
        $this->top('Recommended Resumes');
        $this->menu('employer', 'hh_resumes');
        
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
            <div class="empty_results">No recommendations found for all job posts at this moment.</div>
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
            <div id="window_testimony_candidate" class="popup_window_title">Cover Note</div>
            <div id="window_testimony"></div>
            <div class="popup_window_buttons_bar">
                <div class="instructions_label">
                    (Tip: Drag and select all to copy by pressing Ctrl + C or Command + C.)
                </div>
                <input type="button" value="Close" onClick="close_cover_note_popup();" />
            </div>
        </div>
        
        <div id="employment_window" class="popup_window">
            <div id="window_employment_title" class="popup_window_title"></div>
            <div class="employment_form">
                <table class="employment_form_table">
                    <tr>
                        <td class="label">Annual Salary:</td>
                        <td class="field"><span id="currency"><?php echo $currency; ?></span>$&nbsp;<input type="text" class="salary_field" id="salary" name="salary" value="1.00" /></td>
                    </tr>
                </table>
            </div>
            <div class="popup_window_buttons_bar">
                <input type="hidden" id="employment_referral_id" value="0" />
                <input type="button" value="Confirm &amp; Close" onClick="close_employment_popup(true);" />
                <input type="button" value="Close" onClick="close_employment_popup(false);" />
            </div>
        </div>
        
        <div id="interview_schedule_window" class="popup_window">
            <div id="window_employment_title" class="popup_window_title">Schedule Interview</div>
            <div class="employment_form">
                <table class="schedule_form_table">
                    <tr>
                        <td class="label">Date &amp; Time:</td>
                        <td class="field"><input type="text" id="schedule_datetime" class="schedule_datetime" value="<?php echo date('Y-m-d H:i:s'); ?>" /></td>
                    </tr>
                    <tr>
                        <td class="label">Message:</td>
                        <td class="field"><textarea id="schedule_message"></textarea></td>
                    </tr>
                </table>
            </div>
            <div class="popup_window_buttons_bar">
                <input type="hidden" id="schedule_employment_referral_id" value="0" />
                <input type="button" value="Send" onClick="close_schedule_interview_popup(true);" />
                <input type="button" value="Close" onClick="close_schedule_interview_popup(false);" />
            </div>
        </div>
        <?php
    }
}
?>