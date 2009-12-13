<?php
require_once dirname(__FILE__). "/../../utilities.php";

class MemberConfirmHiresPage extends Page {
    private $member = NULL;
    
    function __construct($_session) {
        $this->member = new member($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_confirm_hires_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/member_confirm_hires.css">'. "\n";
    }
    
    public function insert_member_confirm_hires_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/member_confirm_hires.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->member->id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_search($this->member->get_name(). "&nbsp;&nbsp;<span style=\"color: #FC8503;\">Jobs Applied</span>");
        $this->menu('member', 'confirm_hires');
        
        ?>
        <div class="banner" id="div_banner">
            <a class="no_link" onClick="toggle_banner();"><span id="hide_show_label">Hide</span> Guide</a>
            <br/>
            <img style="border: none;" src="..\common\images\banner_jobs_applied.jpg" />
        </div>        
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_employed_jobs">
            <table class="header">
                <tr>
                    <td class="employer"><span class="sort" id="sort_employer">Employer</span></td>
                    <td class="title"><span class="sort" id="sort_title">Job</span></td>
                    <td class="title"><span class="sort" id="sort_member">Referrer</span></td>
                    <td class="title"><span class="sort" id="sort_resume">Resume</span></td>
                    <td class="date"><span class="sort" id="sort_acknowledged_on">Applied On</span></td>
                    <td class="date"><span class="sort" id="sort_agreed_terms_on">Employer Viewed On</a></td>
                    <td class="confirm">&nbsp;</td>
                </tr>
            </table>
            <div id="div_list">
            </div>
        </div>
        
        <?php
    }
}
?>