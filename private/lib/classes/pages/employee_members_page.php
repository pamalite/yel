<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). "/../htmltable.php";
require_once dirname(__FILE__). "/../../../config/job_profile.inc";

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
        $this->top('Candidates');
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
        </div>
        
        <div id="members">
            <!-- search form -->
            <div id="div_search_toggle" class="search_toggle">
                <a class="no_link" onClick="toggle_search();">
                    <span id="hide_show_lbl">Toggle Search</span>
                </a>
            </div>
            <div id="div_search" class="search">
                <table id="search_table">
                    <tr>
                        <td class="search_form">
                            <table id="search_form_table">
                                <tr>
                                    <td class="label"><label for="search_email">E-mail: </label></td>
                                    <td class="field"><input type="text" class="field" id="search_email" /></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="search_name">Name:</label></td>
                                    <td class="field"><input type="text" class="field" id="search_name" /></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="search_position">Position:</label></td>
                                    <td class="field"><input type="text" class="field" id="search_position" /></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="search_specialization">Specialization:</label></td>
                                    <td class="field"><?php $this->generate_industries(array(), 'search_specialization'); ?></td>
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
                                    <td class="label"><label for="search_emp_specialization">Company Specialization:</label></td>
                                    <td class="field"><?php $this->generate_industries(array(), 'search_emp_specialization'); ?></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="search_total_years">Total Work Years:</label></td>
                                    <td class="field"><input type="text" class="field years" id="search_total_years" maxlength="2" /> years</td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="search_notice_period">Notice Period:</label></td>
                                    <td class="field"><input type="text" class="field years" id="search_notice_period" maxlength="2" /> months</td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="search_expected_salary_start">Expected Salary:</label></td>
                                    <td class="field">
                                        <?php $this->generate_currencies('search_expected_salary_currency'); ?>
                                        <input type="text" class="field salary" id="search_expected_salary_start" />
                                        to 
                                        <input type="text" class="field salary" id="search_expected_salary_end" />
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label"><label for="search_seeking">Goals &amp; Experiences:</label></td>
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