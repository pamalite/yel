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
                        members.email_addr <> 'initial@yellowelevator.com'", 
            'order' => "members.joined_on DESC"
        );

        $member = new Member();
        $results = $member->find($criteria);
        return $results;
    }
    
    private function get_applications($_employee_branch_id) {
        $results = array();
        $criteria = array(
            'columns' => "id, candidate_email, candidate_phone, candidate_name, 
                          referrer_email, referrer_phone, referrer_name, 
                          existing_resume_id, resume_file_hash, 
                          IF(testimony IS NULL OR testimony = '', 0, 1) AS has_testimony,
                          DATE_FORMAT(requested_on, '%e %b, %Y') AS formatted_requested_on", 
            'order' => "requested_on DESC"
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
        $members = $this->get_members($branch[0]['id']);
        $applications = $this->get_applications($branch[0]['id']);
        
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
        if (is_null($requests) || count($requests) <= 0 || $requests === false) {
        ?>
            <div class="empty_results">No applications at this moment.</div>
        <?php
        } else {
        ?>
            <div id="div_applications">
            <?php
                $applications_table = new HTMLTable('applications_table', 'applications');

                $applications_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('applications', 'applications.joined_on');\">Joined On</a>", '', 'header');
                $applications_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('applications', 'applications.lastname');\">Member</a>", '', 'header');
                $applications_table->set(0, 2, "<a class=\"sortable\" onClick=\"sort_by('applications', 'employees.lastname');\">Added By</a>", '', 'header');
                $applications_table->set(0, 3, "<a class=\"sortable\" onClick=\"sort_by('applications', 'application_sessions.first_login');\">Last Login</a>", '', 'header');
                $applications_table->set(0, 4, 'Quick Actions', '', 'header action');

                foreach ($applications as $i=>$application) {
                    $applications_table->set($i+1, 0, $application['formatted_joined_on'], '', 'cell');

                    $application_short_details = '<a class="no_link application_link" onClick="show_application(\''. $application['email_addr']. '\');">'. desanitize($application['application']). '</a>'. "\n";
                    $application_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Tel.:</span> '. $application['phone_num']. '</div>'. "\n";
                    $application_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Email: </span><a href="mailto:'. $application['email_addr']. '">'. $application['email_addr']. '</a></div>'. "\n";
                    $applications_table->set($i+1, 1, $application_short_details, '', 'cell');
                    $applications_table->set($i+1, 2, $application['employee'], '', 'cell');
                    $applications_table->set($i+1, 3, $application['formatted_last_login'], '', 'cell');

                    $actions = '';
                    if ($application['active'] == 'Y') {
                        $actions = '<input type="button" id="activate_button_'. $i. '" value="De-activate" onClick="activate_application(\''. $application['email_addr']. '\', \''. $i. '\');" />';
                        $actions .= '<input type="button" id="password_reset_'. $i. '" value="Reset Password" onClick="reset_password(\''. $application['email_addr']. '\');" />';
                    } else {
                        $actions = '<input type="button" id="activate_button_'. $i. '" value="Activate" onClick="activate_application(\''. $application['email_addr']. '\', \''. $i. '\');" />';
                        $actions .= '<input type="button" id="password_reset_'. $i. '" value="Reset Password" onClick="reset_password(\''. $application['email_addr']. '\');" disabled />';
                    }
                    
                    $applications_table->set($i+1, 4, $actions, '', 'cell action');
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

                    $member_short_details = '<a class="no_link member_link" onClick="show_member(\''. $member['email_addr']. '\');">'. desanitize($member['member']). '</a>'. "\n";
                    $member_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Tel.:</span> '. $member['phone_num']. '</div>'. "\n";
                    $member_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Email: </span><a href="mailto:'. $member['email_addr']. '">'. $member['email_addr']. '</a></div>'. "\n";
                    $members_table->set($i+1, 1, $member_short_details, '', 'cell');
                    $members_table->set($i+1, 2, $member['employee'], '', 'cell');
                    $members_table->set($i+1, 3, $member['formatted_last_login'], '', 'cell');

                    $actions = '';
                    if ($member['active'] == 'Y') {
                        $actions = '<input type="button" id="activate_button_'. $i. '" value="De-activate" onClick="activate_member(\''. $member['email_addr']. '\', \''. $i. '\');" />';
                        $actions .= '<input type="button" id="password_reset_'. $i. '" value="Reset Password" onClick="reset_password(\''. $member['email_addr']. '\');" />';
                    } else {
                        $actions = '<input type="button" id="activate_button_'. $i. '" value="Activate" onClick="activate_member(\''. $member['email_addr']. '\', \''. $i. '\');" />';
                        $actions .= '<input type="button" id="password_reset_'. $i. '" value="Reset Password" onClick="reset_password(\''. $member['email_addr']. '\');" disabled />';
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
                <input type="hidden" id="referral_buffer_id" name="referral_buffer_id" value="" />
            </form>
        <?php
        }
        ?>
        </div>
        
        <div id="member_search">
        </div>
        <?php
    }
}
?>