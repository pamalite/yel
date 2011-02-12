<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). "/../../../config/subscriptions_rate.inc";
require_once dirname(__FILE__). '/../htmltable.php';

class EmployerJobsPage extends Page {
    private $employer = NULL;
    
    function __construct($_session) {
        parent::__construct();
        
        $this->employer = new Employer($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employer_jobs_css() {
        $this->insert_css('employer_jobs.css');
    }
    
    public function insert_employer_jobs_scripts() {
        $this->insert_scripts(array('flextable.js', 'employer_jobs.js'));
    }
    
    public function insert_inline_scripts() {
        $script = 'var id = "'. $this->employer->getId(). '";'. "\n";
        
        $this->header = str_replace('<!-- %inline_javascript% -->', $script, $this->header);
    }
    
    public function show() {
        $this->begin();
        $this->support($this->employer->getId());
        $this->top('Job Descriptions');
        $this->menu('employer', 'jobs');
        
        $branch = $this->employer->getAssociatedBranch();
        $currency = $branch[0]['currency'];
        $subscriptions_rates = $GLOBALS['subscriptions_rates'];
        $subscriptions = $subscriptions_rates[$payment_currency];
        if (!array_key_exists($payment_currency, $subscriptions_rates)) {
            $payment_currency = 'MYR';
            $subscriptions = $subscriptions_rates['MYR'];
        }
        
        $subscription = $this->employer->getSubscriptionsDetails();
        $subscription_is_expired = false;
        if ($subscription[0]['expired'] <= 0) {
            $subscription_is_expired = true;
        }
        
        $job_postings_left = $this->employer->hasPaidJobPostings();
        if ($job_postings_left === false) {
            $job_postings_left = 0;
        }
        
        $free_postings_left = $this->employer->hasFreeJobPostings();
        if ($free_postings_left === false) {
            $free_postings_left = 0;
        }
        
        $jobs = $this->employer->getJobs();
        
        ?>
        <div style="padding-bottom: 25px;">
            <table class="top_banner">
                <tr>
                    <td class="item">
                        <div class="jobs_publishing_instructions">
                            Follow the easy steps below to get your jobs published:
                            <ol>
                                <li>Prepare your job description in a Word (*.doc), PDF or Text file.</li>
                                <li>Attach the file, or files if you have many, to an email using your email software of choice.</li>
                                <li>Set the subject of the email to <span style="font-weight: bold;">"Publish Job Description"</span></li>
                                <li>Enter any special instructions you need us to follow as an email.</li>
                                <li>Send it to <a href="mailto: sales.<?php echo strtolower($branch[0]['country']); ?>@yellowelevator.com">sales.<?php echo strtolower($branch[0]['country']); ?>@yellowelevator.com</a></li>
                            </ol>
                        </div>
                    </td>
                    <td class="item">
                        <div class="subscriptions_details">
                            <table class="subscriptions_info">
                                <tr>
                                    <td class="label">Subscription Expires On: </td>

                                    <td>
                                        <?php $expired_html = ($subscription_is_expired) ? 'color: #FF0000;' : 'color: #000000;'; ?>
                                        <span id="subscriptions_expiry" style="font-weight: bold; <?php echo $expired_html; ?>">
                                            <?php echo $subscription[0]['formatted_expire_on'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label">Job Posts Left: </td>
                                    <td>
                                        <span id="subscriptions_job_postings" style="font-weight: bold;">
                                            <?php echo $job_postings_left; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label">Free Posts Left: </td>
                                    <td>
                                        <span id="subscriptions_free_postings" style="font-weight: bold;">
                                            <?php echo $free_postings_left; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="buy">
                                            Contact us to renew your subscriptions or buy job posts with the <span style="font-weight: bold;">Billing</span> contact details listed above.
                                            <br/><br/>
                                            You can contact us to extend your expired published jobs as well.
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div>
            <div id="div_jobs" class="jobs">
            <?php
                if (empty($jobs)) {
            ?>
                <div class="empty_results">No job published at this moment.</div>
            <?php
                } else {
                    $jobs_table = new HTMLTable('jobs_table', 'jobs');
                
                    $jobs_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('jobs', 'created_on');\">Created On</a>", '', 'header');
                    $jobs_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('jobs', 'title');\">Job</a>", '', 'header');
                    $jobs_table->set(0, 2, "<a class=\"sortable\" onClick=\"sort_by('jobs', 'expire_on');\">Expire On</a>", '', 'header');
                
                    foreach ($jobs as $i=>$job) {
                        $jobs_table->set($i+1, 0, $job['formatted_created_on'], '', 'cell');
                    
                        $job_title = "<a class=\"no_link\" onClick=\"show_job_description(". $job['id']. ");\">". $job['title']. "</a>";
                        $jobs_table->set($i+1, 1, $job_title, '', 'cell');
                    
                        $jobs_table->set($i+1, 2, $job['formatted_expire_on'], '', 'cell');
                    }
                
                    echo $jobs_table->get_html();
                }
            ?>
            </div>
            
            <div id="div_job_desc" class="job_desc">
                <div class="back">
                    <a class="no_link" onClick="show_jobs();">
                        &lt;&lt; Back to Job Postings
                    </a>
                </div>
                
                <table class="job">
                    <tr>
                        <td colspan="2" class="title">
                            <span id="job.title"></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Alternate Employer:</td>
                        <td><span id="job.alternate_employer"></span></td>
                    </tr>
                    <tr>
                        <td class="label">Carbon Copy Email:</td>
                        <td><span id="job.contact_carbon_copy"></span></td>
                    </tr>
                    <tr>
                        <td class="label">Specialization:</td>
                        <td><span id="job.specialization"></span></td>
                    </tr>
                    <tr>
                        <td class="label">Salary Range:</td>
                        <td>
                            <?php echo $currency; ?>&nbsp;$<span id="job.salary_range"></span>
                            &nbsp;
                            [ <span id="job.salary_negotiable"></span> ]
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Location:</td>
                        <td><span id="job.state"></span></td>
                    </tr>
                    <tr>
                        <td class="label">Description:</td>
                        <td><div id="job.description" class="job_description"></div></td>
                    </tr>
                    <tr>
                        <td class="label">Created On:</td>
                        <td><span id="job.created_on"></span></td>
                    </tr>
                    <tr>
                        <td class="label">Expired On:</td>
                        <td><span id="job.expired_on"></span></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }
}
?>