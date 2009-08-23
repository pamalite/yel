<?php
require_once dirname(__FILE__). "/../../utilities.php";

class MemberSavedJobsPage extends Page {
    private $member = NULL;
    
    function __construct($_session) {
        $this->member = new Member($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_saved_jobs_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/member_saved_jobs.css">'. "\n";
    }
    
    public function insert_member_saved_jobs_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/member_saved_jobs.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->member->id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_search($this->member->get_name(). " - Saved Jobs");
        $this->menu('member', 'saved_jobs');
        
        ?>
        <div class="banner">
            Below are the jobs that you have saved to be referred to your contacts later. <br/>To refer the following jobs to your contacts, go to <a href="refer.php">Refer</a>.
        </div>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_saved_jobs">
            <table class="buttons">
                <tr>
                    <td class="left"><input class="button" type="button" id="remove_jobs" name="remove_jobs" value="Remove Selected Jobs" /></td>
                    <td class="right">&nbsp;</td>
                </tr>
            </table>
            <table class="header">
                <tr>
                    <td class="checkbox"><input type="checkbox" id="close_all" /></td>
                    <td class="industry"><span class="sort" id="sort_industry">Industry</span></td>
                    <td class="employer"><span class="sort" id="sort_employer">Employer</span></td>
                    <td class="title"><span class="sort" id="sort_title">Title</span></td>
                    <td class="date"><span class="sort" id="sort_created_on">Created On</span></td>
                    <td class="date"><span class="sort" id="sort_expire_on">Expire On</span></td>
                    <td class="date"><span class="sort" id="sort_saved_on">Saved On</span></td>
                </tr>
            </table>
            <div id="div_list">
            </div>
            <table class="buttons">
                <tr>
                    <td class="left"><input class="button" type="button" id="remove_jobs_1" name="remove_jobs_1" value="Remove Selected Jobs" /></td>
                    <td class="right">&nbsp;</td>
                </tr>
            </table>
        </div>
        
        <?php
    }
}
?>