<?php
require_once dirname(__FILE__). "/../../utilities.php";

class PrsMailingListsPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_prs_mailing_lists_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/prs_mailing_lists.css">'. "\n";
    }
    
    public function insert_prs_mailing_lists_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/prs_mailing_lists.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_prs($this->employee->get_name(). " - Mailing Lists");
        $this->menu_prs($this->clearances, 'mailing_lists');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_mailing_lists">
            <table class="buttons">
                <tr>
                    <td class="right"><input class="button" type="button" id="add_new_list" name="add_new_list" value="Add New Mailing List" /></td>
                </tr>
            </table>
            <table class="header">
                <tr>
                    <td class="date"><span class="sort" id="sort_added_on">Added On</span></td>
                    <td class="employee"><span class="sort" id="sort_added_by">Added By</span></td>
                    <td class="label"><span class="sort" id="sort_label">Label</span></td>
                    <td class="count"><span class="sort" id="sort_count"># of Candidates</span></td>
                    <td class="actions">&nbsp;</td>
                </tr>
            </table>
            <div id="div_mailing_lists_list">
            </div>
            <table class="buttons">
                <tr>
                    <td class="right"><input class="button" type="button" id="add_new_list_1" name="add_new_list_1" value="Add New Mailing List" /></td>
                </tr>
            </table>
        </div>
        
        <div id="div_candidates">
            <div id="div_tabs">
                <ul>
                    <li class="back" id="li_back">&lt;&lt; Back to Mailing Lists</li>
                </ul>
            </div>
            
            <div style="padding-top: 3px; padding-bottom: 10px; text-align: center;">
                <span id="mailing_list_label" style="font-weight: bold;"></span><br/><br/>
                <a class="no_link" onClick="show_email_form();">Click here to send email message to this mailing list.</a>
            </div>
            <table class="header">
                <tr>
                    <td class="date"><span class="sort" id="sort_joined_on">Joined On</span></td>
                    <td class="candidate"><span class="sort" id="sort_candidate">Candidate</span></td>
                    <td class="actions">&nbsp;</td>
                </tr>
            </table>
            <div id="div_candidates_list">
            </div>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_email_form">
            <form onSubmit="retun false;">
                <input type="hidden" id="mailing_list_id" name="mailing_list_id" value="0" />
                <div style="text-align: center;">
                    Enter an email message to <span id="list_label" style="font-weight: bold;"></span> in the following text field.<br/><br/>
                    <textarea class="email_message" id="email_message" name="email_message"></textarea>
                </div>
                <p class="button"><input type="button" value="Cancel" onClick="close_email_form();" />&nbsp;<input type="button" value="Send Email" onClick="send_email_to_list();" /></p>
            </form>
        </div>
        <?php
    }
}
?>