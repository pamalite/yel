<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). "/../htmltable.php";

class EmployeeMembersPage extends Page {
    private $employee = NULL;
    private $current_page = 'applications';
    private $total_applicantions = 0;
    private $total_members = 0;
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
    }
    
    public function set_page($_page) {
        $this->current_page = $_page;
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_members_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_members.css">'. "\n";
    }
    
    public function insert_employee_members_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/flextable.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_members.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->getId(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->getUserId(). '";'. "\n";
        echo 'var current_page = "'. $this->current_page. '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function get_employers() {
        $criteria = array(
            'columns' => "DISTINCT employers.name AS employer, employers.id", 
            'joins' => "employers ON employers.id = jobs.employer", 
            'order' => "employers.name"
        );
        
        $job = new Job();
        return $job->find($criteria);
    }
    
    public function show() {
        $this->begin();
        $this->top('Members');
        $this->menu_employee('members');
        
        $branch = $this->employee->getBranch();
        $sales_email_addr = 'team.'. strtolower($branch[0]['country']). '@yellowelevator.com';
        $employers = $this->get_employers();
        
        ?>
        <!-- submenu -->
        <div class="menu">
            <?php $style = 'background-color: #CCCCCC;'; ?>
            <ul class="menu">
                <li id="item_new_applicants" style="<?php echo ($this->current_page == 'applications') ? $style : ''; ?>"><a class="menu" onClick="show_new_applicants();">New Applicants</a></li>
                <li id="item_applicants" style="<?php echo ($this->current_page == 'members') ? $style : ''; ?>"><a class="menu" onClick="show_applicants();">Applicants</a></li>
                <li id="item_members" style="<?php echo ($this->current_page == 'search') ? $style : ''; ?>"><a class="menu" onClick="show_members();">Members</a></li>
            </ul>
        </div>
        <!-- end submenu -->
        
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <!-- main filter -->
        <div id="div_main_filter_toggle" class="main_filter_toggle">
            <a class="no_link" onClick="toggle_main_filter();">
                <span id="hide_show_lbl">Toggle Filter</span>
            </a>
        </div>
        <div id="div_main_filter" class="main_filter">
            <table id="main_filter_table">
                <tr>
                    <td class="employers_list">
                    <?php
                        if ($employers === false || empty($employers) || is_null($employers)) {
                    ?>
                        <div class="main_filter_warning">There are no employers to list.</div>
                    <?php
                        } else {
                    ?>
                        <select id="employers" class="employers" multiple>
                    <?php
                            foreach ($employers as $an_employer) {
                    ?>
                            <option value="<?php echo $an_employer['id']; ?>">
                                <?php echo '['.  $an_employer['id']. '] '. htmlspecialchars_decode(stripslashes($an_employer['employer'])); ?>
                            </option>
                    <?php
                            }
                    ?>
                        </select>
                    <?php
                        }
                    ?>
                    </td>
                    <td class="filter_jobs_button">
                        <input type="button" value="&gt;&gt;" onClick="populate_jobs_list();" />
                    </td>
                    <td id="jobs_list" class="jobs_list">
                        <div id="jobs_list_message_box">&lt;--- Select the employers to list jobs.</div>
                        <div id="jobs_list_placeholder">
                            <select id="jobs" class="jobs" onClick="toggle_add_button();" multiple>
                            </select>
                        </div>
                    </td>
                    <td class="filter_buttons">
                        <input type="button" class="main_filter_button" value="Show" onClick="do_filter();" />
                        <hr />
                        <input type="button" class="main_filter_button" value="Show All" onClick="show_non_attached();" />
                        <br/>
                        <input type="button" class="main_filter_button" id="add_new_btn" value="Add New Applicant" onClick="show_new_application_popup();" disabled />
                    </td>
                </tr>
                <tr>
                    <td colspan="4" class="hide_show">
                        <div class="main_filter_tip">Hold down the CTRL (Windows), or Command (Mac), key to select/un-select multiple items.</div>
                    </td>
                </tr>
            </table>
        </div>
        <!-- end main filter -->
        
        <div id="new_applicants">
            <input type="hidden" id="total_applications" value="0" />
            <div class="buttons_bar">
                <div class="pagination">
                    Page
                    <select id="pages" onChange="update_new_applicants();">
                        <option value="1" selected>1</option>
                    </select>
                    of <span id="total_pages">0</span>
                </div>
                <div class="sub_filter">
                    Show 
                    <select id="applications_filter" onChange="filter_new_applicants();" disabled>
                        <option value="" selected>All</option>
                        <option value="" disabled>&nbsp;</option>
                        <option value="self_applied">Self Applied</option>
                        <option value="referred">Referred</option>
                    </select>
                </div>
            </div>
            <div id="div_new_applicants">
                <div class="empty_results">No new applicants to show.</div>
            </div>
        </div>
        
        <div id="applicants">
            <div class="buttons_bar">
                <div class="pagination">
                    Page
                    <select id="applicants_pages" onChange="update_applicants();">
                        <option value="1" selected>1</option>
                    </select>
                    of <span id="total_applicants_pages">0</span>
                </div>
                <!-- input class="button" type="button" id="add_new_member" name="add_new_member" value="Add New Member" onClick="add_new_member();" /-->
            </div>
            <div id="div_applicants">
                <div class="empty_results">No applicants to show.</div>
            </div>
            
            <form id="member_page_form" method="post" action="member.php">
                <input type="hidden" id="member_email_addr" name="member_email_addr" value="" />
            </form>
        </div>
        
        <div id="members">
        </div>
        
        <!-- popup windows goes here -->
        <div id="notes_window" class="popup_window">
            <div class="popup_window_title">Notes</div>
            <form onSubmit="return false;">
                <input type="hidden" id="app_id" value="" />
                <input type="hidden" id="notes_email" value="" />
                <div class="notes_form">
                    <textarea id="notes" class="notes"></textarea>
                </div>
            </form>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Save" onClick="close_notes_popup(true);" />
                <input type="button" value="Cancel" onClick="close_notes_popup(false);" />
            </div>
        </div>
        
        <div id="referrer_window" class="popup_window">
            <div class="popup_window_title">Referrer to <span id="ref_candidate_name">[Unknown]</span></div>
            <form onSubmit="return false;">
                <input type="hidden" id="referral_buffer_id" value="" />
                <div class="referrer_form">
                    <table class="referrer_form">
                        <tr>
                            <td class="label">Name:</td>
                            <td><input type="text" class="field" id="ref_referrer_name" /></td>
                        </tr>
                        <tr>
                            <td class="label">Telephone:</td>
                            <td><input type="text" class="field" id="ref_referrer_phone" /></td>
                        </tr>
                        <tr>
                            <td class="label">E-mail:</td>
                            <td><input type="text" class="field" id="ref_referrer_email" /></td>
                        </tr>
                    </table>
                </div>
            </form>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Save" onClick="close_referrer_popup(true);" />
                <input type="button" value="Cancel" onClick="close_referrer_popup(false);" />
            </div>
        </div>
        
        <div id="new_application_window" class="popup_window">
            <div class="popup_window_title">New Application</div>
            <form onSubmit="return false;">
                <input type="hidden" id="sales_email_addr" value="<?php echo $sales_email_addr; ?>" />
                <input type="hidden" id="new_applicant_jobs" value="" />
                <div class="new_application_form">
                    <table class="new_application">
                        <tr>
                            <td class="title" colspan="2">Referrer</td>
                            <td class="title">Notes</td>
                        </tr>
                        <tr>
                            <td class="auto_fill" colspan="2">
                                <input type="checkbox" id="auto_fill_checkbox" onClick="auto_fill_referrer();" />
                                <label for="auto_fill_checkbox">Referrer is YellowElevator.com</label>
                            </td>
                            <td rowspan="8">
                                <textarea class="quick_notes" id="quick_notes"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Name:</td>
                            <td><input type="text" class="field" id="referrer_name" /></td>
                        </tr>
                        <tr>
                            <td class="label">Telephone:</td>
                            <td><input type="text" class="field" id="referrer_phone" /></td>
                        </tr>
                        <tr>
                            <td class="label">E-mail:</td>
                            <td><input type="text" class="field" id="referrer_email_addr" /></td>
                        </tr>
                        <tr>
                            <td class="title" colspan="2">Candidate</td>
                        </tr>
                        <tr>
                            <td class="label">Name:</td>
                            <td><input type="text" class="field" id="candidate_name" /></td>
                        </tr>
                        <tr>
                            <td class="label">Telephone:</td>
                            <td><input type="text" class="field" id="candidate_phone" /></td>
                        </tr>
                        <tr>
                            <td class="label">E-mail:</td>
                            <td><input type="text" class="field" id="candidate_email_addr" /></td>
                        </tr>
                    </table>
                </div>
            </form>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Add New Applicant" onClick="close_new_application_popup(1);" />
                <input type="button" value="Cancel" onClick="close_new_application_popup(0);" />
            </div>
        </div>
        
        <div id="other_jobs_window" class="popup_window">
            <div class="popup_window_title">Other Applied Jobs</div>
            <div id="div_other_jobs" class="other_jobs"></div>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Close" onClick="close_jobs_popup();" />
            </div>
        </div>
        
        <div id="progress_notes_window" class="popup_window">
            <div class="popup_window_title">Progress Notes</div>
            <form onSubmit="return false;">
                <input type="hidden" id="progress_id" value="" />
                <input type="hidden" id="progress_is_buffer" value="1" />
                <div class="notes_form">
                    <textarea id="progress_notes" class="notes"></textarea>
                </div>
            </form>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Save" onClick="close_progress_popup(true);" />
                <input type="button" value="Cancel" onClick="close_progress_popup(false);" />
            </div>
        </div>
        <?php
    }
}
?>