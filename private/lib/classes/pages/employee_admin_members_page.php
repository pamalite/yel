<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployeeAdminMembersPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_admin_members_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_admin_members.css">'. "\n";
    }
    
    public function insert_employee_admin_members_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_admin_members.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function count_members() {
        $query = "SELECT COUNT(*) AS num_of_members FROM members";
        $mysql = Database::connect();
        $result = $mysql->query($query);
        return $result[0]['num_of_members'];
    }
    
    public function show() {
        $this->begin();
        $this->top_employee($this->employee->get_name(). " - Members");
        $this->menu_employee($this->clearances, 'members');
        
        $count = $this->count_members();
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_members">
            <div style="padding-bottom: 10px; color: #333333;">
                <?php
                    if ($count == 1) {
                        echo 'There is only 1 member.';
                    } else {
                        echo 'There are '. $count. ' members.';
                    }
                ?>
            </div>
            <table class="header">
                <tr>
                    <td class="email_addr"><span class="sort" id="sort_email_addr">E-mail Address</span></td>
                    <td class="member"><span class="sort" id="sort_member">Member</span></td>
                    <td class="date"><span class="sort" id="sort_joined_on">Joined On</span></td>
                    <td class="actions">&nbsp;</td>
                </tr>
            </table>
            <div id="div_members_list">
            </div>
            <div style="padding-top: 10px; color: #333333;">
                <?php
                    if ($count == 1) {
                        echo 'There is only 1 member.';
                    } else {
                        echo 'There are '. $count. ' members';
                    }
                ?>
            </div>
        </div>
        <?php
    }
}
?>