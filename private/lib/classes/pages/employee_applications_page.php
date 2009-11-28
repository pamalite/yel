<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployeeApplicationsPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_applications_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_applications.css">'. "\n";
    }
    
    public function insert_employee_applications_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_applications.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_employee($this->employee->get_name(). " - Applications");
        $this->menu_employee($this->clearances, 'applications');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_employers">
            <table class="header">
                <tr>
                    <td class="employer"><span class="sort" id="sort_employer">Employer</span></td>
                    <td class="count"><span class="sort" id="sort_open">Open Jobs</span></td>
                    <td class="count"><span class="sort" id="sort_referred">Referred</span></td>
                    <td class="count"><span class="sort" id="sort_viewed">Viewed</span></td>
                </tr>
            </table>
            <div id="div_employers_list">
            </div>
        </div>
        
        <div id="div_jobs">
            <div id="div_tabs">
                <ul>
                    <li id="li_back">&lt;&lt; Back to Employers</li>
                </ul>
            </div>
            <div style="text-align: center; padding-top: 15px; padding-bottom: 15px;">
                Jobs listing of <span id="employer_name" style="font-weight: bold;"></span>.
            </div>
            <table class="header">
                <tr>
                    <td class="industry"><span class="sort" id="sort_industry">Specialization</span></td>
                    <td class="title"><span class="sort" id="sort_title">Title</span></td>
                    <td class="date"><span class="sort" id="sort_created_on">Created On</span></td>
                    <td class="date"><span class="sort" id="sort_expire_on">Expire On</span></td>
                    <td class="count"><span class="sort" id="sort_jobs_referred">Referred</span></td>
                    <td class="count"><span class="sort" id="sort_jobs_viewed">Viewed</span></td>
                </tr>
            </table>
            <div id="div_jobs_list">
            </div>
        </div>
        
        <div id="div_referrals">
            <div id="div_tabs">
                <ul>
                    <li id="li_back_1">&lt;&lt; Back to Job Ads</li>
                </ul>
            </div>
            <div style="text-align: center; padding-top: 15px; padding-bottom: 15px;">
                Referrals listing of <span id="job_title" style="font-weight: bold;"></span>.
            </div>
            <table class="header">
                <tr>
                    <td class="date"><span class="sort" id="sort_referred_on">Referred On</span></td>
                    <td class="referrer"><span class="sort" id="sort_referrer">Referrer</span></td>
                    <td class="candidate"><span class="sort" id="sort_candidate">Candidate</span></td>
                    <td class="date"><span class="sort" id="sort_employer_viewed_on">Viewed On</span></td>
                    <td class="links">&nbsp;</td>
                </tr>
            </table>
            <div id="div_referrals_list">
            </div>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_testimony">
            <div id="testimony" class="testimony"></div>
            <div class="buttons">
                <input type="button" onClick="close_testimony();" value="Close" />
            </div>
        </div>
        <?php
    }
}
?>