<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployeeHomePage extends Page {
    private $employee = NULL;
    private $current_page = 'applications';
    private $total_applications = 0;
    private $error_code = '';
    
    function __construct($_session) {
        parent::__construct();
        
        $this->employee = new Employee($_session['id'], $_session['sid']);
    }
    
    public function set_page($_page) {
        $this->current_page = $_page;
    }
    
    public function set_error($_error_code) {
        $this->error_code = $_error_code;
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_home_css() {
        $this->insert_css(array('employee_home_page.css', 'employee_members.css'));
    }
    
    public function insert_employee_home_scripts() {
        $this->insert_scripts(array('flextable.js', 'employee_home_page.js'));
    }
    
    public function insert_inline_scripts() {
        $script = 'var id = "'. $this->employee->getId(). '";'. "\n";
        $script .= 'var user_id = "'. $this->employee->getUserId(). '";'. "\n";
        $script .= 'var current_page = "'. $this->current_page. '";'. "\n";
        
        if (!empty($this->error_code)) {
            $script .= 'var error = "'. $this->error_code. '";'. "\n";
        } else {
            $script .= 'var error = "";'. "\n";
        }
        
        $this->header = str_replace('<!-- %inline_javascript% -->', $script, $this->header);
    }
    
    private function generate_employees_list($_is_applicants = false) {
        $criteria = array(
            'columns' => "id, CONCAT(lastname, ', ', firstname) AS employee, country", 
            'match' => "id > 1",
            'order' => "employee ASC"
        );
        
        $employee = new Employee();
        $result = $employee->find($criteria);
        
        $id = 'job_owners_filter';
        if ($_is_applicants) {
            $id = 'ref_job_owners_filter';
        }
        
        echo '<select id="'. $id. '">'. "\n";
        echo '<option value="0">Everyone</option>'. "\n";
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        echo '<option value="'. $this->employee->getId(). '" selected>Myself</option>'. "\n";
        
        foreach ($result as $row) {
            if ($row['id'] != $this->employee->getId()) {
                echo '<option value="'. $row['id']. '">'. $row['employee']. ' ('. $row['country']. ')'. '</option>'. "\n";
            }
        }
        
        echo '</select>';
    }
    
    public function show() {
        $this->begin();
        $branch = $this->employee->getBranch();
        $this->top('Home - '. $branch[0]['country']);
        $this->menu_employee('home');
        
        $sales_email_addr = 'team.'. strtolower($branch[0]['country']). '@yellowelevator.com';
        
        ?>
        <!-- submenu -->
        <div class="menu">
            <?php $style = 'background-color: #CCCCCC;'; ?>
            <ul class="menu">
                <li id="item_new_applicants" style="<?php echo ($this->current_page == 'applications') ? $style : ''; ?>"><a class="menu" onClick="show_new_applicants();">Potential Applicants</a> <span class="counter_label" id="lbl_new_applicants"></span></li>
                <li id="item_applicants" style="<?php echo ($this->current_page == 'members') ? $style : ''; ?>"><a class="menu" onClick="show_applicants();">Applicants</a> <span class="counter_label" id="lbl_applicants"></span></li>
            </ul>
        </div>
        <!-- end submenu -->
        
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <!-- main filter -->
        <!-- placeholder for future expansion -->
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
                    <select id="applications_filter" disabled>
                        <option value="" selected>All</option>
                        <option value="" disabled>&nbsp;</option>
                        <option value="self_applied">Self Applied</option>
                        <option value="referred">Referred</option>
                    </select>
                    reminders handled by 
                    <?php echo $this->generate_employees_list(); ?>
                    to expire on
                    <input type="text" id="expire_on_field" class="expire_on_field" value="<?php echo date('Y-m-d'); ?>" />
                    
                    <input type="button" onClick="filter_new_applicants();"  value="Filter" />
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
                <div class="sub_filter">
                    Show reminders handled by 
                    <?php echo $this->generate_employees_list(true); ?>
                    to expire on
                    <input type="text" id="ref_expire_on_field" class="ref_expire_on_field" value="<?php echo date('Y-m-d'); ?>" />
                    <input type="button" onClick="update_applicants();"  value="Filter" />
                </div>
            </div>
            <div id="div_applicants">
                <div class="empty_results">No applicants to show.</div>
            </div>
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
        
        <div id="referrer_remarks_window" class="popup_window">
            <div class="popup_window_title">Referrer Remarks</div>
            <div id="remarks" class="remarks">
                <input type="hidden" id="app_id" value="0" />
                <textarea id="remarks_field" class="remarks"></textarea>
            </div>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Save" onClick="close_referrer_remarks_popup(true);" />
                <input type="button" value="Close" onClick="close_referrer_remarks_popup();" />
            </div>
        </div>
        
        <div id="reminder_window" class="popup_window">
            <div class="popup_window_title">Follow Up</div>
            <form onSubmit="return false;">
                <input type="hidden" id="reminder_id" value="" />
                <input type="hidden" id="reminder_is_buffer" value="1" />
                <div class="reminder_form">
                    Follow up on <input type="text" id="reminder_day" class="reminder_field" value="" />
                </div>
            </form>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Set" onClick="close_reminder_popup(true);" />
                <input type="button" value="Cancel" onClick="close_reminder_popup(false);" />
            </div>
        </div>
        <?php
    }
}
?>