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
    
    private function get_members($_employee_branch_id) {
        $results = array();
        $criteria = array(
            'columns' => "members.email_addr, members.phone_num, members.progress_notes, members.active,
                          IF(member_index.notes IS NULL OR member_index.notes = '', 0, 1) AS has_notes,  
                          CONCAT(members.lastname, ', ', members.firstname) AS member_name, 
                          DATE_FORMAT(members.joined_on, '%e %b, %Y') AS formatted_joined_on, 
                          COUNT(DISTINCT resumes.id) AS num_yel_resumes, 
                          COUNT(DISTINCT resumes_1.id) AS num_self_resumes,
                          COUNT(DISTINCT member_jobs.id) AS num_attached_jobs",
            'joins' => "resumes AS resumes ON resumes.member = members.email_addr AND 
                            resumes.is_yel_uploaded = TRUE,
                        resumes AS resumes_1 ON resumes_1.member = members.email_addr AND
                            resumes_1.is_yel_uploaded = FALSE, 
                        member_index ON member_index.member = members.email_addr, 
                        member_jobs ON member_jobs.member = members.email_addr",
            'match' => "members.email_addr <> 'initial@yellowelevator.com' AND 
                        members.email_addr NOT LIKE 'team%@yellowelevator.com'",
            'group' => "members.email_addr", 
            'order' => "members.joined_on DESC"
        );
        
        $member = new Member();
        $this->total_members = count($member->find($criteria));
        $criteria['limit']= $GLOBALS['default_results_per_page'];
        $results = $member->find($criteria);
        return $results;
    }
    
    private function get_applications() {
        $criteria = array(
            'columns' => "referral_buffers.id, referral_buffers.candidate_email, 
                          referral_buffers.candidate_phone, referral_buffers.candidate_name, 
                          referral_buffers.referrer_email, referral_buffers.referrer_name, 
                          referral_buffers.referrer_phone, 
                          referral_buffers.existing_resume_id, referral_buffers.resume_file_hash, 
                          IF(referral_buffers.notes IS NULL OR referral_buffers.notes = '', 0, 1) AS has_notes,
                          IF(members.email_addr IS NULL, 0, 1) AS is_member, 
                          jobs.title AS job, employers.id AS employer, 
                          DATE_FORMAT(referral_buffers.requested_on, '%e %b, %Y') AS formatted_requested_on", 
            'joins' => "members ON members.email_addr = referral_buffers.candidate_email, 
                        jobs ON jobs.id = referral_buffers.job, 
                        employers ON employers.id = jobs.employer",
            'order' => "referral_buffers.requested_on DESC"
        );
        
        $referral_buffer = new ReferralBuffer();
        $this->total_applications = count($referral_buffer->find($criteria));
        $criteria['limit']= $GLOBALS['default_results_per_page'];
        return $referral_buffer->find($criteria);
    }
    
    public function show() {
        $this->begin();
        $this->top('Members');
        $this->menu_employee('members');
        
        $branch = $this->employee->getBranch();
        $sales_email_addr = 'team.'. strtolower($branch[0]['country']). '@yellowelevator.com';
        $members = $this->get_members($branch[0]['id']);
        $applications = $this->get_applications();
        $employers = $this->get_employers();
        
        ?>
        <!-- submenu -->
        <div class="menu">
            <?php $style = 'background-color: #CCCCCC;'; ?>
            <ul class="menu">
                <li id="item_applications" style="<?php echo ($this->current_page == 'applications') ? $style : ''; ?>"><a class="menu" onClick="show_applications();">Applicants</a></li>
                <li id="item_members" style="<?php echo ($this->current_page == 'members') ? $style : ''; ?>"><a class="menu" onClick="show_members();">Members</a></li>
                <li id="item_search" style="<?php echo ($this->current_page == 'search') ? $style : ''; ?>"><a class="menu" onClick="show_search_members();">Search</a></li>
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
                        <input type="button" class="main_filter_button" value="Refresh" onClick="do_filter();" />
                        <br/>
                        <input type="button" class="main_filter_button" value="Reset" onClick="show_all();" />
                        <input type="button" class="main_filter_button" value="Show Non-attached" onClick="show_non_attached();" />
                        <hr />
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
        
        <div id="applications">
        <?php
        if (is_null($applications) || count($applications) <= 0 || $applications === false) {
        ?>
            <div class="empty_results">No applications at this moment.</div>
            <input type="hidden" id="total_applications" value="0" />
        <?php
        } else {
        ?>
            <input type="hidden" id="total_applications" value="<?php echo $this->total_applications; ?>" />
            <div class="buttons_bar">
                <div class="pagination">
                    Page
                    <select id="pages" onChange="update_applications();">
                        <option value="1" selected>1</option>
                    <?php
                        $total_pages = ceil($this->total_applications / $GLOBALS['default_results_per_page']);
                        for ($i=1; $i < $total_pages; $i++) {
                    ?>
                        <option value="<?php echo ($i+1); ?>"><?php echo ($i+1); ?></option>
                    <?php
                        }
                    ?>
                    </select>
                    of <span id="total_pages"><?php echo $total_pages; ?></span>
                </div>
                <div class="sub_filter">
                    Show 
                    <select id="applications_filter" onChange="filter_applications();">
                        <option value="" selected>All</option>
                        <option value="" disabled>&nbsp;</option>
                        <option value="self_applied">Self Applied</option>
                        <option value="referred">Referred</option>
                    </select>
                </div>
            </div>
            <div id="div_applications">
            <?php
                $applications_table = new HTMLTable('applications_table', 'applications');
                
                $applications_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('applications', 'referral_buffers.requested_on');\">Created On</a>", '', 'header');
                $applications_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('applications', 'referral_buffers.candidate_name');\">Candidate</a>", '', 'header');
                $applications_table->set(0, 2, "Notes", '', 'header');
                $applications_table->set(0, 3, "Job", '', 'header');
                $applications_table->set(0, 4, "Resume", '', 'header');
                $applications_table->set(0, 5, 'Quick Actions', '', 'header action');
                
                foreach ($applications as $i=>$application) {
                    $is_cannot_signup = false;
                                    
                    $applications_table->set($i+1, 0, $application['formatted_requested_on'], '', 'cell');
                    
                    $candidate_short_details = '<span style="font-weight: bold;">'. htmlspecialchars_decode(stripslashes($application['candidate_name'])). '</span>'. "\n";
                    if (empty($application['candidate_phone']) || 
                        is_null($application['candidate_phone'])) {
                        $is_cannot_signup = true;
                        $candidate_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Tel.:</span> <a class="no_link small_contact_edit" onClick="edit_candidate_phone('. $application['id']. ');">Add Phone Number</a></div>'. "\n";
                    } else {
                        $candidate_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Tel.:</span> '. $application['candidate_phone']. ' <a class="no_link small_contact_edit" onClick="edit_candidate_phone('. $application['id']. ');">edit</a></div>'. "\n";
                    }

                    if (empty($application['candidate_email']) || is_null($application['candidate_email'])) {
                        $is_cannot_signup = true;
                        $candidate_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Email: </span> <a class="no_link small_contact_edit" onClick="edit_candidate_email('. $application['id']. ');">Add Email</a></div>'. "\n";
                    } else {
                        $candidate_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Email: </span><a href="mailto:'. $application['candidate_email']. '">'. $application['candidate_email']. '</a> <a class="no_link small_contact_edit" onClick="edit_candidate_email('. $application['id']. ');">edit</a></div>'. "\n";
                    }

                    $referrer_short_details = '<div class="tiny_contact">Ref: ';
                    if (substr($application['referrer_email'], 0, 5) == 'team.' && 
                        substr($application['referrer_email'], 7) == '@yellowelevator.com') {
                        $referrer_short_details .= 'Self Applied';
                    } else {
                        $referrer_short_details .= '<a class="no_link" onClick="show_referrer_popup('. $application['id']. ');">';
                        $referrer_short_details .= htmlspecialchars_decode(stripslashes($application['referrer_name']));
                        if (empty($application['referrer_phone']) || 
                            is_null($application['referrer_phone']) || 
                            empty($application['referrer_email']) || 
                            is_null($application['referrer_email'])) {
                            $is_cannot_signup = true;
                            $referrer_short_details .= '&nbsp;(Incomplete!)'. "\n";
                        }

                        $referrer_short_details .= '</a>';
                    }
                    $referrer_short_details .= '</div>';
                    $candidate_short_details .= '<br/>'. $referrer_short_details;
                    $applications_table->set($i+1, 1, $candidate_short_details, '', 'cell');
                    
                    if ($application['has_notes'] == '1') {
                        $applications_table->set($i+1, 2, '<a class="no_link" onClick="show_notes_popup(\''. $application['id']. '\');">Update</a>', '', 'cell');
                    } else {
                        $applications_table->set($i+1, 2, '<a  class="no_link" onClick="show_notes_popup(\''. $application['id']. '\');">Add</a>', '', 'cell');
                    }
                    
                    $job = '['. $application['employer']. '] '. $application['job'];
                    if ($job == '[] ') {
                        $job = 'N/A';
                    }
                    $applications_table->set($i+1, 3, $job, '', 'cell');
                    
                    if (!is_null($application['existing_resume_id']) && 
                        !empty($application['existing_resume_id'])) {
                        $applications_table->set($i+1, 4, '<a href="resume.php?id='. $application['existing_resume_id']. '">View</a>', '', 'cell');
                    } elseif (!is_null($application['resume_file_hash']) && 
                              !empty($application['resume_file_hash'])) {
                        $applications_table->set($i+1, 4, '<a href="resume.php?id='. $application['id']. '&hash='. $application['resume_file_hash']. '">View</a>', '', 'cell');
                    } else {
                        $applications_table->set($i+1, 4, 'N/A', '', 'cell');
                    }
                    
                    $actions = '<input type="button" value="Delete" onClick="delete_application(\''. $application['id']. '\');" />';
                    
                    if (is_null($application['candidate_email']) || 
                        empty($application['candidate_email'])) {
                        $actions .= '<input type="button" value="Jobs" onClick="show_jobs_popup(false, \''. $application['candidate_name']. '\');" />';
                    } else {
                        $actions .= '<input type="button" value="Jobs" onClick="show_jobs_popup(true, \''. $application['candidate_email']. '\');" />';
                    }
                    
                    if ($is_cannot_signup) {
                        $actions .= '<input type="button" value="Sign Up" disabled />';
                    } else {
                        if ($application['is_member'] == '1') {
                            $actions .= '<input type="button" value="Transfer" onClick="transfer_to_member(\''. $application['id']. '\');" />';
                        } else {
                            $actions .= '<input type="button" value="Sign Up" onClick="make_member_from(\''. $application['id']. '\');" />';
                        }
                    }
                    $applications_table->set($i+1, 5, $actions, '', 'cell action');
                }
                
                echo $applications_table->get_html();
            ?>
            </div>
        <?php
        }
        ?>
        </div>
        
        <div id="members">
        <?php
        if (is_null($members) || count($members) <= 0 || $members === false) {
        ?>
            <div class="empty_results">No members found.</div>
        <?php
        } else {
        ?>
            <div class="buttons_bar">
                <div class="pagination">
                    Page
                    <select id="member_pages" onChange="update_members();">
                        <option value="1" selected>1</option>
                    <?php
                        $total_member_pages = ceil($this->total_members / $GLOBALS['default_results_per_page']);
                        for ($i=1; $i < $total_member_pages; $i++) {
                    ?>
                        <option value="<?php echo ($i+1); ?>"><?php echo ($i+1); ?></option>
                    <?php
                        }
                    ?>
                    </select>
                    of <span id="total_member_pages"><?php echo $total_member_pages; ?></span>
                </div>
                <input class="button" type="button" id="add_new_member" name="add_new_member" value="Add New Member" onClick="add_new_member();" />
            </div>
            <div id="div_members">
            <?php
                $members_table = new HTMLTable('members_table', 'members');

                $members_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('members', 'members.joined_on');\">Joined On</a>", '', 'header');
                $members_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('members', 'members.lastname');\">Member</a>", '', 'header');
                $members_table->set(0, 2, "Notes", '', 'header');
                $members_table->set(0, 3, "Resumes", '', 'header');
                $members_table->set(0, 4, "Jobs", '', 'header');
                $members_table->set(0, 5, "Progress", '', 'header');
                $members_table->set(0, 6, 'Quick Actions', '', 'header action');

                foreach ($members as $i=>$member) {
                    $members_table->set($i+1, 0, $member['formatted_joined_on'], '', 'cell');

                    $member_short_details = '<a class="member_link" href="member.php?member_email_addr='. $member['email_addr']. '">'. desanitize($member['member_name']). '</a>'. "\n";
                    $member_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Tel.:</span> '. $member['phone_num']. '</div>'. "\n";
                    $member_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Email: </span><a href="mailto:'. $member['email_addr']. '">'. $member['email_addr']. '</a></div>'. "\n";
                    $member_short_details .= '<br/><a href="member.php?member_email_addr='. $member['email_addr']. '&page=referrers">View Referrers</a>'. "\n";
                    $members_table->set($i+1, 1, $member_short_details, '', 'cell');
                    
                    if ($member['has_notes'] == '1') {
                        $members_table->set($i+1, 2, '<a class="no_link" onClick="show_notes_popup(\''. $member['email_addr']. '\');">Update</a>', '', 'cell');
                    } else {
                        $members_table->set($i+1, 2, '<a class="no_link" onClick="show_notes_popup(\''. $member['email_addr']. '\');">Add</a>', '', 'cell');
                    }
                    
                    $resume_details = '<a class="no_link" onClick="show_resumes_page(\''. addslashes($member['email_addr']). '\');">View/Refer</a><br/><br/>'. "\n";
                    $resume_details .= '<span style="color: #666666;">YEL: '. $member['num_yel_resumes']. "</span><br/>\n";
                    $resume_details .= '<span style="color: #666666;">Self: '. $member['num_self_resumes']. "</span><br/>\n";
                    $members_table->set($i+1, 3, $resume_details, '', 'cell');
                    
                    $jobs_attached = '<span style="font-style: italic; color: #666666;">No jobs attached.</span>';
                    if ($member['num_attached_jobs'] > 0) {
                        $jobs_attached = '<a class="no_link" onClick="show_jobs_popup(false, \''. $member['email_addr']. '\', true);">'. $member['num_attached_jobs']. '</a>';
                    }
                    $members_table->set($i+1, 4, $jobs_attached, '', 'cell');
                    
                    $progress_note = '<a class="no_link" onClick="show_progress_popup(\''. $member['email_addr']. '\');">Add</a>';
                    if (!is_null($member['progress_notes']) && !empty($member['progress_notes'])) {
                        $progress_note = '<div class="progress_cell">'. str_replace("\n", '<br/>', $member['progress_notes']). '</div>';
                        $progress_note .= '<br/><a class="no_link" onClick="show_progress_popup(\''. $member['email_addr']. '\');">Update</a>';
                    }
                    $members_table->set($i+1, 5, $progress_note, '', 'cell progress_cell');
                    
                    $actions = '';
                    if ($member['active'] == 'Y') {
                        $actions = '<input type="button" id="activate_button_'. $i. '" value="De-activate" onClick="activate_member(\''. $member['email_addr']. '\', \''. $i. '\');" />';
                        $actions .= '<input type="button" id="password_reset_'. $i. '" value="Reset Password" onClick="reset_password(\''. $member['email_addr']. '\');" />';
                    } else {
                        $actions = '<input type="button" id="activate_button_'. $i. '" value="Activate" onClick="activate_member(\''. $member['email_addr']. '\', \''. $i. '\');" />';
                        // $actions .= '<input type="button" id="password_reset_'. $i. '" value="Reset Password" onClick="reset_password(\''. $member['email_addr']. '\');" disabled />';
                    }
                    
                    $members_table->set($i+1, 6, $actions, '', 'cell action');
                }

                echo $members_table->get_html();
            ?>
            </div>
            
            <div class="buttons_bar">
                <input class="button" type="button" id="add_new_member" name="add_new_member" value="Add New Member" onClick="add_new_member();" />
            </div>
            
            <form id="member_page_form" method="post" action="member.php">
                <input type="hidden" id="member_email_addr" name="member_email_addr" value="" />
            </form>
        <?php
        }
        ?>
        </div>
        
        <div id="member_search">
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
                <input type="hidden" id="progress_email" value="" />
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