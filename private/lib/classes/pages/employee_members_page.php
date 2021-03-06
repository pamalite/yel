<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). "/../htmltable.php";
require_once dirname(__FILE__). "/../../../config/job_profile.inc";

class EmployeeMembersPage extends Page {
    private $employee = NULL;
    private $current_page = 'applications';
    private $total_applications = 0;
    private $total_members = 0;
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
    
    public function insert_employee_members_css() {
        $this->insert_css(array('list_box.css', 'employee_members.css'));
    }
    
    public function insert_employee_members_scripts() {
        $this->insert_scripts(array('flextable.js', 'list_box.js', 'employee_members.js'));
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
    
    private function get_employers() {
        $branch = $this->employee->getBranch();
        
        $criteria = array(
            'columns' => "DISTINCT employers.name AS employer, employers.id", 
            'joins' => "employers ON employers.id = jobs.employer, 
                        employees ON employees.id = employers.registered_by",
            'match' => "employees.branch = ". $branch[0]['id'],  
            'order' => "employers.name"
        );
        
        $job = new Job();
        return $job->find($criteria);
    }
    
    private function generate_currencies($_id, $_selected='') {
        $currencies = $GLOBALS['currencies'];
        
        echo '<select id="'. $_id. '" name="'. $_id. '">'. "\n";
        echo '<option value="0" selected>Any Currency</option>'. "\n";
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        
        foreach ($currencies as $i=>$currency) {
            echo '<option value="'. $currency. '">'. $currency. '</option>'. "\n";
        }
        
        echo '</select>';
    }
    
    private function generate_industries($_selected, $_name = 'industry') {
        $industries = array();
        $main_industries = Industry::getMain();
        $i = 0;
        foreach ($main_industries as $main) {
            $industries[$i]['id'] = $main['id'];
            $industries[$i]['name'] = $main['industry'];
            $industries[$i]['is_main'] = true;
            $subs = Industry::getSubIndustriesOf($main['id']);
            foreach ($subs as $sub) {
                $i++;

                $industries[$i]['id'] = $sub['id'];
                $industries[$i]['name'] = $sub['industry'];
                $industries[$i]['is_main'] = false;
            }
            $i++;
        }
        
        echo '<select class="field" id="'. $_name. '" name="'. $_name. '">'. "\n";
        
        if (empty($_selected) || is_null($_selected)) {
            echo '<option value="0" selected>Any Specialization</option>'. "\n";
            echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        }
        
        foreach ($industries as $industry) {
            $selected = '';
            if ($industry['id'] == $_selected) {
                $selected = 'selected';
            }
            
            if ($industry['is_main']) {
                echo '<option value="'. $industry['id']. '" class="main_industry" '. $selected. '>';
                echo $industry['name'];
            } else {
                echo '<option value="'. $industry['id']. '"'. $selected. '>';
                echo '&nbsp;&nbsp;&nbsp;&nbsp;'. $industry['name'];
            }

            echo '</option>'. "\n";
        }
        
        echo '</select>'. "\n";
    }
    
    private function generate_employer_description($_id, $_selected) {
        $descs = $GLOBALS['emp_descs'];
        
        echo '<select class="field" id="'. $_id. '" name="'. $_id. '">'. "\n";
        if (empty($_selected) || is_null($_selected) || $_selected < 0) {
            echo '<option value="0" selected>Any description</option>'. "\n";    
        } else {
            echo '<option value="0">Any description</option>'. "\n";
        }
        
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        foreach ($descs as $i=>$desc) {
            if ($i != $_selected) {
                echo '<option value="'. $i. '">'. $desc. '</option>'. "\n";
            } else {
                echo '<option value="'. $i. '" selected>'. $desc. '</option>'. "\n";
            }
        }
        
        echo '</select>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $branch = $this->employee->getBranch();
        $this->top('Candidates - '. $branch[0]['country']);
        $this->menu_employee('members');
        
        $sales_email_addr = 'team.'. strtolower($branch[0]['country']). '@yellowelevator.com';
        $employers = $this->get_employers();
        
        ?>
        <!-- submenu -->
        <div class="menu">
            <?php $style = 'background-color: #CCCCCC;'; ?>
            <ul class="menu">
                <li id="item_new_applicants" style="<?php echo ($this->current_page == 'applications') ? $style : ''; ?>"><a class="menu" onClick="show_new_applicants();">Potential Applicants</a></li>
                <li id="item_applicants" style="<?php echo ($this->current_page == 'members') ? $style : ''; ?>"><a class="menu" onClick="show_applicants();">Applicants</a></li>
                <li id="item_members" style="<?php echo ($this->current_page == 'search') ? $style : ''; ?>"><a class="menu" onClick="show_members();">Candidates</a></li>
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
                        <input type="text" class="filter_search" id="employers_search" /><br/>
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
                            <input type="text" class="filter_search" id="jobs_search" /><br/>
                            <select id="jobs" class="jobs" onClick="toggle_add_button();" multiple>
                            </select>
                        </div>
                    </td>
                    <td class="filter_buttons">
                        <input type="button" class="main_filter_button" value="Show" onClick="do_filter();" />
                        <hr />
                        <input type="button" class="main_filter_button" value="Show All" onClick="show_non_attached();" />
                        <br/>
                        <input type="button" class="main_filter_button" id="search_resume_btn" value="Trace Resume" onClick="trace_resume();"/>
                        <input type="button" class="main_filter_button" id="add_new_btn" value="Add New Applicant" onClick="show_new_application_popup();" disabled />
                        <input type="button" class="main_filter_button" id="bulk_add_new_btn" value="Bulk Add New Applicants" onClick="show_bulk_new_applications_popup();" disabled />
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
        </div>
        
        <div id="members">
            <!-- search form -->
            <div id="div_search_toggle" class="search_toggle">
                <a class="no_link" onClick="toggle_search();">
                    <span id="hide_show_lbl">Toggle Search</span>
                </a>
            </div>
            <div id="div_search" class="search">
                <form id="candidates_search_form">
                    <table id="search_table">
                        <tr>
                            <td class="search_form">
                                <table id="search_form_table">
                                    <tr>
                                        <td class="label"><label for="search_name">Name:</label></td>
                                        <td class="field"><input type="text" class="field" id="search_name" /></td>
                                    </tr>
                                    <tr>
                                        <td class="label"><label for="search_email">E-mail: </label></td>
                                        <td class="field"><input type="text" class="field" id="search_email" /></td>
                                    </tr>
                                    <tr>
                                        <td class="label"><label for="search_position">Position:</label></td>
                                        <td class="field"><input type="text" class="field" id="search_position" /></td>
                                    </tr>
                                    <tr>
                                        <td class="label"><label for="search_employer">Company:</label></td>
                                        <td class="field"><input type="text" class="field" id="search_employer" /></td>
                                    </tr>
                                    <tr>
                                        <td class="label"><label for="search_emp_desc">Company Description:</label></td>
                                        <td class="field"><?php $this->generate_employer_description('search_emp_desc', -1); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="label"><label for="search_specialization">Specialization:</label></td>
                                        <td class="field"><?php $this->generate_industries(array(), 'search_specialization'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="label"><label for="search_total_years">Total Work Years:</label></td>
                                        <td class="field"><input type="text" class="field years" id="search_total_years" maxlength="2" /> years</td>
                                    </tr>
                                    <tr>
                                        <td class="label"><label for="search_seeking">Job Responsibilities &amp; Experiences:</label></td>
                                        <td class="field">
                                            <textarea class="field" id="search_seeking"></textarea>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td class="search_buttons">
                                <input type="button" class="search_button" value="Search" onClick="update_members();" />
                                <hr />
                                <input type="button" class="search_button" value="Show All" onClick="update_members('all');" />
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <!-- end search form -->
            
            <div id="members_list">
                <div class="buttons_bar">
                    <div class="pagination">
                        Page
                        <select id="members_pages" onChange="update_members();">
                            <option value="1" selected>1</option>
                        </select>
                        of <span id="total_members_pages">0</span>
                    </div>
                    <input class="button" type="button" id="add_new_member" name="add_new_member" value="Bulk Add New Candidates" onClick="show_bulk_new_members_popup();">
                    <input class="button" type="button" id="add_new_member" name="add_new_member" value="Add New Candidate" onClick="add_new_member();">
                </div>
                <div id="div_members">
                    <div class="empty_results">No members to show.</div>
                </div>
            </div>
        </div>
        
        <form id="member_page_form" method="post" target="_new" action="member.php">
            <input type="hidden" id="member_email_addr" name="member_email_addr" value="" />
        </form>
        
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
                            <td class="title">Progress Notes</td>
                        </tr>
                        <tr>
                            <td class="auto_fill" colspan="2">
                                <input type="checkbox" id="auto_fill_checkbox" onClick="auto_fill_referrer();" />
                                <label for="auto_fill_checkbox">Referrer is YellowElevator.com</label>
                            </td>
                            <td rowspan="10">
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
                        <tr>
                            <td class="label">Current Position:</td>
                            <td><input type="text" class="field" id="candidate_current_pos" /></td>
                        </tr>
                        <tr>
                            <td class="label">Current Company:</td>
                            <td><input type="text" class="field" id="candidate_current_emp" /></td>
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
        
        <div id="apply_job_window" class="popup_window">
            <div class="popup_window_title">Apply Job</div>
            <form id="apply_job_form" onSubmit="return false;">
                <input type="hidden" id="apply_member_email" value="" />
                <div id="div_apply_job_form" class="apply_job_form">
                    <table class="jobs_selection">
                        <tr>
                            <td class="jobs_list">
                            <?php
                                $employers = $this->get_employers();
                                if (!empty($employers) && $employers !== false) {
                            ?>
                                <select id="apply_employers" class="field" onChange="filter_jobs();">
                            <?php
                                    foreach($employers as $employer) {
                            ?>
                                    <option value="<?php echo $employer['id']; ?>"><?php echo $employer['employer']; ?></option>
                            <?php
                                    }
                            ?>
                                </select>
                            <?php
                                } else {
                            ?>
                                <span class="no_employers">[No employers with opened jobs found.]</span>
                            <?php
                                }
                            ?>
                                <div id="jobs_selector">
                                    Select an employer in the dropdown list above.
                                </div>
                                <div id="selected_job_counter">
                                    <span id="counter_lbl">0</span> jobs selected.
                                </div>
                            </td>
                            <td class="separator"></td>
                            <td>
                                <div id="job_description">
                                    Select a job in the jobs list.
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="popup_window_buttons_bar">
                    <input type="button" id="apply_btn" value="Apply" onClick="close_apply_jobs_popup(true);" />
                    <input type="button" value="Cancel" onClick="close_apply_jobs_popup(false);" />
                </div>
            </form>
        </div>
        
        <div id="employment_window" class="popup_window">
            <div id="window_employment_title" class="popup_window_title"></div>
            <div class="employment_form">
                <table class="employment_form_table">
                    <tr>
                        <td class="label">Employed On:</td>
                        <td class="field">
                        <?php
                            $today = date('Y-m-d');
                            $date_components = explode('-', $today);
                            $year = $date_components[0];
                            $month = $date_components[1];
                            $day = $date_components[2];
                            
                            echo generate_dropdown('employment_day', '', 1, 31, $day, 2, 'Day');
                            echo generate_month_dropdown('employment_month', '', $month);
                            echo '<span id="employment_year_label">'. $year. '</span>'. "\n";
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Work Commencement:</td>
                        <td class="field">
                        <?php
                            $today = date('Y-m-d');
                            $date_components = explode('-', $today);
                            $year = $date_components[0];
                            $month = $date_components[1];
                            $day = $date_components[2];
                            
                            echo generate_dropdown('work_day', '', 1, 31, $day, 2, 'Day');
                            echo generate_month_dropdown('work_month', '', $month);
                            echo '<span id="work_year_label">'. $year. '</span>'. "\n";
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Annual Salary:</td>
                        <td class="field"><span id="employment_currency">???</span>$&nbsp;<input type="text" class="salary_field" id="salary" name="salary" value="1.00" /></td>
                    </tr>
                </table>
            </div>
            <div class="popup_window_buttons_bar">
                <input type="hidden" id="employment_referral_id" value="0" />
                <input type="button" value="Confirm &amp; Close" onClick="close_employment_popup(true);" />
                <input type="button" value="Close" onClick="close_employment_popup(false);" />
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
        
        <div id="upload_new_applicants_window" class="popup_window">
            <div class="popup_window_title">Upload New Applicants (CSV)</div>
            <form id="upload_csv_form" action="members_action.php" method="post" enctype="multipart/form-data" onSubmit="return close_bulk_new_applications_popup(true);">
                <div class="upload_csv_form">
                    <br/>
                    <input type="hidden" id="id" name="id" value="<?php echo $this->employee->getUserId(); ?>" />
                    <input type="hidden" id="bulk_new_applicant_jobs" name="bulk_new_applicant_jobs" value="0" />
                    <input type="hidden" name="action" value="bulk_add_new_applicants" />
                    <div id="upload_progress" style="text-align: center; width: 99%; margin: auto;">
                        Please wait while new applicants are being uploaded... <br/><br/>
                        <img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/progress/circle_big.gif" /><br/><br/>
                    </div>
                    <div id="upload_field" class="upload_field">
                        <input id="csv_file" name="csv_file" type="file" />
                        <div style="font-size: 9pt; margin-top: 15px;">
                            <ol>
                                <li>Only Comma Separated Verbose (CSV) file with less than 2MB are allowed.</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="popup_window_buttons_bar">
                    <input type="submit" value="Bulk Add" />
                    <input type="button" value="Close" onClick="close_bulk_new_applications_popup(false);" />
                </div>
            </form>
        </div>
        
        <div id="upload_new_members_window" class="popup_window">
            <div class="popup_window_title">Upload New Candidates (CSV)</div>
            <form id="upload_csv_form" action="members_action.php" method="post" enctype="multipart/form-data" onSubmit="return close_bulk_new_members_popup(true);">
                <div class="upload_csv_form">
                    <br/>
                    <input type="hidden" id="id" name="id" value="<?php echo $this->employee->getUserId(); ?>" />
                    <input type="hidden" name="action" value="bulk_add_new_candidates" />
                    <div id="upload_progress" style="text-align: center; width: 99%; margin: auto;">
                        Please wait while new candidates are being uploaded... <br/><br/>
                        <img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/progress/circle_big.gif" /><br/><br/>
                    </div>
                    <div id="upload_field" class="upload_field">
                        <input id="members_csv_file" name="members_csv_file" type="file" />
                        <div style="font-size: 9pt; margin-top: 15px;">
                            <ol>
                                <li>Only Comma Separated Verbose (CSV) file with less than 2MB are allowed.</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="popup_window_buttons_bar">
                    <input type="submit" value="Bulk Add" />
                    <input type="button" value="Close" onClick="close_bulk_new_members_popup(false);" />
                </div>
            </form>
        </div>
        <?php
    }
}
?>