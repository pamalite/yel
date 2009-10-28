<?php
require_once dirname(__FILE__). "/../../utilities.php";

class PrsResumeSearchPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session, $_criterias = '') {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
        
        $this->criterias = $_criterias;
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_resume_search_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/list_box.css">'. "\n";
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/search.css">'. "\n";
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/prs_search_resumes.css">'. "\n";
    }
    
    public function insert_resume_search_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/list_box.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/prs_resume_search.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo 'var country_code = "'. $this->criterias['country_code']. '";'. "\n";
        echo 'var industry = "'. $this->criterias['industry']. '";'. "\n";
        echo 'var keywords = "'. $this->criterias['keywords']. '";'. "\n";
        
        $limit = (isset($this->criterias['limit'])) ? $this->criterias['limit'] : $GLOBALS['default_results_per_page'];
        echo 'var limit = "'. $limit. '";'. "\n";
        
        $offset = (isset($this->criterias['offset'])) ? $this->criterias['offset'] : 0;
        echo 'var offset = "'. $offset. '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_prs($this->employee->get_name(). " - Resumes Search Result");
        $this->menu_prs($this->clearances, '');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_search_results">
            <div class="filters">
                Show all available resumes in <span id="filter_industry_dropdown"></span> from <span id="filter_country_dropdown"></span> with <span id="filter_limit_dropdown"></span> jobs in each page.
            </div>
            <div class="page_navigation">
                <span id="previous_page"></span>&nbsp;&nbsp;&nbsp;Page <span id="current_page"></span> of <span id="total_page"></span>&nbsp;&nbsp;&nbsp;<span id="next_page"></span>
            </div>
            <table class="header">
                <tr>
                    <td class="match_percentage"><span class="sort" id="sort_match_percentage">Match</span></td>
                    <td class="date"><span class="sort" id="sort_joined_on">Joined On</span></td>
                    <td class="member"><span class="sort" id="sort_member">Candidate</span></td>
                    <td class="industry"><span class="sort" id="sort_primary_industry">Specialization 1</span></td>
                    <td class="industry"><span class="sort" id="sort_secondary_industry">Specialization 2</span></td>
                    <td class="label"><span class="sort" id="sort_title">Resume</span></td>
                    <td class="country"><span class="sort" id="sort_country">Country</span></td>
                    <td class="zip"><span class="sort" id="sort_zip">Zip</span></td>
                    <td class="action">&nbsp;</td>
                </tr>
            </table>
            <div id="div_list">
            </div>
            <div class="page_navigation">
                <span id="previous_page_1"></span>&nbsp;&nbsp;&nbsp;Page <span id="current_page_1"></span> of <span id="total_page_1"></span>&nbsp;&nbsp;&nbsp;<span id="next_page_1"></span>
            </div>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_email_add_form">
            <form onSubmit="retun false;">
                <table class="email_add_form">
                    <tr>
                    <td colspan="3"><p style="text-align: center;">Add&nbsp;<span id="candidate_email" style="font-weight: bold;"></span>&nbsp;to...</p></td>
                    </tr>
                    <tr>
                        <td class="left">
                            <table class="lists_form">
                                <tr>
                                    <td class="radio"><input type="radio" id="to_existing" name="mailing_list" value="list" checked /></td>
                                    <td>
                                        <label for="mailing_lists">an existing mailing list</label><br/>
                                        <div class="mailing_lists" id="mailing_lists" name="mailing_lists"></div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="separator"></td>
                        <td class="right">
                            <table class="lists_form">
                                <tr>
                                    <td class="radio"><input type="radio" id="to_new" name="mailing_list" value="new" /></td>
                                    <td>
                                        <label for="new_list_label">a new mailing list</label><br/>
                                        <p><input type="text" class="mini_field" id="new_list_label" name="new_list_label" /></p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <p class="button"><input type="button" value="Cancel" onClick="close_email_add_form();" />&nbsp;<input type="button" value="Add" onClick="add_email_to_list();" /></p>
            </form>
        </div>
        
        <?php
    }
}
?>