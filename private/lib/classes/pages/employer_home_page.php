<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployerHomePage extends Page {
    private $employer = NULL;
    
    function __construct($_session) {
        $this->employer = new Employer($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employer_home_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employer_home_page.css">'. "\n";
    }
    
    public function insert_employer_home_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employer_home_page.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employer->id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->support($this->employer->id());
        $this->top($this->employer->get_name(). "&nbsp;&nbsp;<span style=\"color: #FC8503;\">Home</span>");
        $this->menu('employer', 'home');
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div class="banner">
            <div style="text-align: center;">
                <a class="no_link guides" onClick="show_guide_page('manage_job_post.php');">
                    How to <span style="font-weight: bold;">Create &amp; Publish a Job Ad</span>?
                </a>
            </div>
            <br/>
            <div style="text-align: center;">
                <a class="no_link guides" onClick="show_guide_page('view_resume_hire.php');">
                    How to <span style="font-weight: bold;">View Resumes &amp; Hire Candidates</span>?
                </a>
            </div>
            
            <!--img style="border: none;" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root'] ?>/common/images/misc/employer_banners/home.jpg" /-->
        </div>
        <!--div class="referred_jobs">
            <div style="text-align: center; margin-top: 5px; margin-bottom: 5px; padding-top: 5px; padding-bottom: 5px; font-weight: bold; border: 1px solid #CCCCCC;">Recent referrals</div>
            <table class="header">
                <tr>
                    <td class="industry">Industry</td>
                    <td class="title">Job</td>
                    <td class="date">Created On</td>
                    <td class="date">Expire On</td>
                    <td class="referrals">Referrals</td>
                </tr>
            </table>
            <div id="div_referred_jobs_list">
            </div>
        </div>
        <div class="invoices">
            <div style="text-align: center; margin-top: 5px; margin-bottom: 5px; padding-top: 5px; padding-bottom: 5px; font-weight: bold; border: 1px solid #CCCCCC;">Recent Unpaid Invoices</div>
            <table class="header">
                <tr>
                    <td class="expired">&nbsp;</td>
                    <td class="date">Issued On</td>
                    <td class="date">Payable By</td>
                    <td class="type">Type</td>
                    <td class="invoice">Invoice</td>
                </tr>
            </table>
            <div id="div_new_invoices_list">
            </div>
        </div-->
        <?php
    }
}
?>