<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). "/../htmltable.php";

class EmployeeMembersPage extends Page {
    private $employee = NULL;
    private $current_page = 'applications';

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
    
    private function get_members($_employee_branch_id) {
        $results = array();
        $criteria = array(
            'columns' => "members.email_addr, members.phone_num, members.active, members.phone_num,
                          members.address, members.state, members.zip, countries.country, 
                          DATE_FORMAT(members.joined_on, '%e %b, %Y') AS formatted_joined_on, 
                          DATE_FORMAT(member_sessions.last_login, '%e %b, %Y') AS formatted_last_login, 
                          CONCAT(members.lastname, ', ', members.firstname) AS member, 
                          CONCAT(employees.lastname, ', ', employees.firstname) AS employee",
            'joins' => "member_sessions ON member_sessions.member = members.email_addr, 
                        employees ON employees.id = members.added_by, 
                        countries ON countries.country_code = members.country", 
            'match' => "employees.branch = ". $_employee_branch_id ." AND 
                        members.email_addr <> 'initial@yellowelevator.com' AND 
                        members.email_addr NOT LIKE 'team%@yellowelevator.com'", 
            'order' => "members.joined_on DESC"
        );

        $member = new Member();
        $results = $member->find($criteria);
        return $results;
    }
    
    private function get_applications() {
        $results = array();
        $criteria = array(
            'columns' => "referral_buffers.id, referral_buffers.candidate_email, 
                          referral_buffers.candidate_phone, referral_buffers.candidate_name, 
                          referral_buffers.referrer_email, referral_buffers.referrer_phone, 
                          referral_buffers.referrer_name, 
                          referral_buffers.existing_resume_id, referral_buffers.resume_file_hash, 
                          IF(referral_buffers.notes IS NULL OR referral_buffers.notes = '', 0, 1) AS has_notes,
                          IF(members.email_addr IS NULL, 0, 1) AS is_member,
                          DATE_FORMAT(referral_buffers.requested_on, '%e %b, %Y') AS formatted_requested_on", 
            'joins' => "members ON members.email_addr = referral_buffers.candidate_email",
            'order' => "referral_buffers.requested_on DESC"
        );

        $referral_buffer = new ReferralBuffer();
        $results = $referral_buffer->find($criteria);
        return $results;
    }
    
    public function show() {
        $this->begin();
        $this->top('Members');
        $this->menu_employee('members');
        
        $branch = $this->employee->getBranch();
        $sales_email_addr = 'team.'. strtolower($branch[0]['country']). '@yellowelevator.com';
        $members = $this->get_members($branch[0]['id']);
        $applications = $this->get_applications();
        
        ?>
        <!-- submenu -->
        <div class="menu">
            <?php $style = 'background-color: #CCCCCC;'; ?>
            <ul class="menu">
                <li id="item_applications" style="<?php echo ($this->current_page == 'applications') ? $style : ''; ?>"><a class="menu" onClick="show_applications();">Applications</a></li>
                <li id="item_members" style="<?php echo ($this->current_page == 'members') ? $style : ''; ?>"><a class="menu" onClick="show_members(false);">Members</a></li>
                <li id="item_search" style="<?php echo ($this->current_page == 'search') ? $style : ''; ?>"><a class="menu" onClick="show_search_members();">Search</a></li>
            </ul>
        </div>
        <!-- end submenu -->
        
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="applications">
        <?php
        if (is_null($applications) || count($applications) <= 0 || $applications === false) {
        ?>
            <div class="empty_results">No applications at this moment.</div>
        <?php
        } else {
        ?>
            <div class="buttons_bar">
                Filter: 
                <select id="applications_filter" onChange="filter_applications();">
                    <option value="" selected>All</option>
                    <option value="" disabled>&nbsp;</option>
                    <option value="self_applied">Self Applied</option>
                    <option value="referred">Referred</option>
                </select>
                &nbsp;
                <input class="button" type="button" value="Add New Application" onClick="show_new_application_popup();" />
            </div>
            <div id="div_applications">
            <?php
                $applications_table = new HTMLTable('applications_table', 'applications');

                $applications_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('applications', 'referral_buffers.requested_on');\">Requested/Added On</a>", '', 'header');
                $applications_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('applications', 'referral_buffers.referrer_name');\">Referrer</a>", '', 'header');
                $applications_table->set(0, 2, "<a class=\"sortable\" onClick=\"sort_by('applications', 'referral_buffers.candidate_name');\">Candidate</a>", '', 'header');
                $applications_table->set(0, 3, "Notes", '', 'header');
                $applications_table->set(0, 4, "Resume", '', 'header');
                $applications_table->set(0, 5, 'Quick Actions', '', 'header action');

                foreach ($applications as $i=>$application) {
                    $is_cannot_signup = false;
                    
                    $applications_table->set($i+1, 0, $application['formatted_requested_on'], '', 'cell');
                    
                    $referrer_short_details = '';
                    if (substr($application['referrer_email'], 0, 5) == 'team.' && 
                        substr($application['referrer_email'], 7) == '@yellowelevator.com') {
                        $referrer_short_details = 'Self Applied';
                    } else {
                        $referrer_short_details = '<span style="font-weight: bold;">'. htmlspecialchars_decode(desanitize($application['referrer_name'])). '</span>'. "\n";
                        
                        if (empty($application['referrer_phone']) || 
                            is_null($application['referrer_phone'])) {
                            $is_cannot_signup = true;
                            $referrer_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Tel.:</span> <a class="no_link small_contact_edit" onClick="edit_referrer_phone('. $application['id']. ');">Add Phone Number</a></div>'. "\n";
                        } else {
                            $referrer_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Tel.:</span> '. $application['referrer_phone']. '</div>'. "\n";
                        }
                        
                        if (empty($application['referrer_email']) || 
                            is_null($application['referrer_email'])) {
                            $is_cannot_signup = true;
                            $referrer_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Email: </span> <a class="no_link small_contact_edit" onClick="edit_referrer_email('. $application['id']. ');">Add Email</a></div>'. "\n";
                        } else {
                            $referrer_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Email: </span><a href="mailto:'. $application['referrer_email']. '">'. $application['referrer_email']. '</a></div>'. "\n";
                        }
                    }
                    $applications_table->set($i+1, 1, $referrer_short_details, '', 'cell');
                    
                    $candidate_short_details = '<span style="font-weight: bold;">'. htmlspecialchars_decode(desanitize($application['candidate_name'])). '</span>'. "\n";
                    
                    if (empty($application['candidate_phone']) || is_null($application['candidate_phone'])) {
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
                    $applications_table->set($i+1, 2, $candidate_short_details, '', 'cell');
                    
                    if ($application['has_notes'] == '1') {
                        $applications_table->set($i+1, 3, '<a class="no_link" onClick="show_notes_popup(\''. $application['id']. '\');">Update</a>', '', 'cell');
                    } else {
                        $applications_table->set($i+1, 3, '<a  class="no_link" onClick="show_notes_popup(\''. $application['id']. '\');">Add</a>', '', 'cell');
                    }
                    
                    if (!is_null($application['existing_resume_id']) && 
                        !empty($application['existing_resume_id'])) {
                        $applications_table->set($i+1, 4, '<a href="resume.php?id='. $application['existing_resume_id']. '">View Resume</a>', '', 'cell');
                    } elseif (!is_null($application['resume_file_hash']) && 
                              !empty($application['resume_file_hash'])) {
                        $applications_table->set($i+1, 4, '<a href="resume.php?id='. $application['id']. '&hash='. $application['resume_file_hash']. '">View Resume</a>', '', 'cell');
                    } else {
                        $applications_table->set($i+1, 4, 'Sign Up to Upload', '', 'cell');
                    }
                    
                    $actions = '<input type="button" value="Delete" onClick="delete_application(\''. $application['id']. '\');" />';
                    
                    if ($is_cannot_signup) {
                        $actions .= '<input type="button" value="Sign Up" disabled />';
                    } else {
                        $actions .= '<input type="button" value="Sign Up" onClick="make_member_from(\''. $application['id']. '\');" />';
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
            <div class="empty_results">No requests at this moment.</div>
        <?php
        } else {
        ?>
            <div class="buttons_bar">
                <input class="button" type="button" id="add_new_member" name="add_new_member" value="Add New Member" onClick="add_new_member();" />
            </div>
            <div id="div_members">
            <?php
                $members_table = new HTMLTable('members_table', 'members');

                $members_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('members', 'members.joined_on');\">Joined On</a>", '', 'header');
                $members_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('members', 'members.lastname');\">Member</a>", '', 'header');
                $members_table->set(0, 2, "<a class=\"sortable\" onClick=\"sort_by('members', 'employees.lastname');\">Added By</a>", '', 'header');
                $members_table->set(0, 3, "<a class=\"sortable\" onClick=\"sort_by('members', 'member_sessions.first_login');\">Last Login</a>", '', 'header');
                $members_table->set(0, 4, 'Quick Actions', '', 'header action');

                foreach ($members as $i=>$member) {
                    $members_table->set($i+1, 0, $member['formatted_joined_on'], '', 'cell');

                    $member_short_details = '<a class="member_link" href="member.php?member_email_addr='. $member['email_addr']. '">'. desanitize($member['member']). '</a>'. "\n";
                    $member_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Tel.:</span> '. $member['phone_num']. '</div>'. "\n";
                    $member_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Email: </span><a href="mailto:'. $member['email_addr']. '">'. $member['email_addr']. '</a></div>'. "\n";
                    $members_table->set($i+1, 1, $member_short_details, '', 'cell');
                    $members_table->set($i+1, 2, $member['employee'], '', 'cell');
                    $members_table->set($i+1, 3, $member['formatted_last_login'], '', 'cell');

                    $actions = '';
                    if ($member['active'] == 'Y') {
                        $actions = '<input type="button" id="activate_button_'. $i. '" value="De-activate" onClick="activate_member(\''. $member['email_addr']. '\', \''. $i. '\');" />';
                        $actions .= '<input type="button" id="password_reset_'. $i. '" value="Reset Password" onClick="reset_password(\''. $member['email_addr']. '\');" />';
                        $actions .= '<input type="button" value="Pick a Resume to Refer" onClick="show_resumes_page(\''. $member['email_addr']. '\');" />';
                    } else {
                        $actions = '<input type="button" id="activate_button_'. $i. '" value="Activate" onClick="activate_member(\''. $member['email_addr']. '\', \''. $i. '\');" />';
                        // $actions .= '<input type="button" id="password_reset_'. $i. '" value="Reset Password" onClick="reset_password(\''. $member['email_addr']. '\');" disabled />';
                    }
                    
                    $members_table->set($i+1, 4, $actions, '', 'cell action');
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
                <div class="notes_form">
                    <textarea id="notes" class="notes"></textarea>
                </div>
            </form>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Cancel" onClick="close_notes_popup(false);" />
                <input type="button" value="Save" onClick="close_notes_popup(true);" />
            </div>
        </div>
        
        <div id="new_application_window" class="popup_window">
            <div class="popup_window_title">New Application</div>
            <form onSubmit="return false;">
                <input type="hidden" id="sales_email_addr" value="<?php echo $sales_email_addr; ?>" />
                <div class="new_application_form">
                    <table class="new_application">
                        <tr>
                            <td class="referrer" colspan="2">Referrer</td>
                            <td class="candidate" colspan="2">Candidate</td>
                        </tr>
                        <tr>
                            <td class="auto_fill" colspan="2">
                                <input type="checkbox" id="auto_fill_checkbox" onClick="auto_fill_referrer();" />
                                <label for="auto_fill_checkbox">Referrer is YellowElevator.com</label>
                            </td>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <td class="label">Name:</td>
                            <td><input type="text" class="field" id="referrer_name" /></td>
                            <td class="label">Name:</td>
                            <td><input type="text" class="field" id="candidate_name" /></td>
                        </tr>
                        <tr>
                            <td class="label">Telephone:</td>
                            <td><input type="text" class="field" id="referrer_phone" /></td>
                            <td class="label">Telephone:</td>
                            <td><input type="text" class="field" id="candidate_phone" /></td>
                        </tr>
                        <tr>
                            <td class="label">E-mail:</td>
                            <td><input type="text" class="field" id="referrer_email_addr" /></td>
                            <td class="label">E-mail:</td>
                            <td><input type="text" class="field" id="candidate_email_addr" /></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td class="label">Notes:</td>
                            <td>
                                <textarea class="quick_notes" id="quick_notes"></textarea>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Add New Application" onClick="close_new_application_popup(1);" />
                <input type="button" value="Cancel" onClick="close_new_application_popup(0);" />
            </div>
        </div>
        
        <div id="conflicts_window" class="popup_window">
            <div class="popup_window_title">Member Contact Conflicts Resolution</div>
            <div class="resolution_instructions">
                Choose which of the following details are the latest contacts.
            </div>
            <form onSubmit="return false;">
                <input type="hidden" id="conflict_app_id" value="" />
                <div class="conflicts_form">
                    <table class="conflicts">
                        <tr>
                            <td class="buffered" colspan="2">Buffered</td>
                            <td class="existing" colspan="2">Existing</td>
                        </tr>
                        <tr>
                            <td class="label">Name:</td>
                            <td><span id="buffered_name"></span></td>
                            <td class="label">Name:</td>
                            <td><span id="existing_name"></span></td>
                        </tr>
                        <tr>
                            <td class="label">Telephone:</td>
                            <td><span id="buffered_phone"></span></td>
                            <td class="label">Telephone:</td>
                            <td><span id="existing_phone"></span></td>
                        </tr>
                        <tr>
                            <td class="label">Requested On:</td>
                            <td><span id="buffered_created_on"></span></td>
                            <td class="label">Joined On:</td>
                            <td><span id="existing_created_on"></span></td>
                        </tr>
                        <tr>
                            <td class="resolution_button" colspan="2">
                                <input type="button" value="Use Buffered" onClick="close_conflict_popup(0);" />
                            </td>
                            <td class="resolution_button" colspan="2">
                                <input type="button" value="Use Existing" onClick="close_conflict_popup(1);" />
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Cancel" onClick="close_conflict_popup();" />
            </div>
        </div>
        
        <?php
    }
}
?>