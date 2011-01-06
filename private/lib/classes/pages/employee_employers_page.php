<?php
require_once dirname(__FILE__). "/../../utilities.php";
// require_once dirname(__FILE__). "/../../../config/subscriptions_rate.inc";
require_once dirname(__FILE__). "/../htmltable.php";

class EmployeeEmployersPage extends Page {
    private $employee = NULL;
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_employers_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_employers.css">'. "\n";
    }
    
    public function insert_employee_employers_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/flextable.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_employers.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->getId(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->getUserId(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function get_employers($_employee_branch_id) {
        $criteria = array(
            'columns' => "employers.id, employers.name AS employer, employers.phone_num, 
                          employers.email_addr, employers.fax_num, employers.contact_person, 
                          employers.active, 
                          DATE_FORMAT(employers.joined_on, '%e %b, %Y') AS formatted_joined_on, 
                          DATE_FORMAT(employer_sessions.first_login, '%e %b, %Y') AS formatted_first_login, 
                          CONCAT(employees.firstname, ', ', employees.lastname) AS employee", 
            'joins' => "employer_sessions ON employer_sessions.employer = employers.id, 
                        employees ON employees.id = employers.registered_by", 
            'match' => "employees.branch = ". $_employee_branch_id, 
            'order' => "employers.joined_on DESC"
        );
        
        $employer = new Employer();
        return $employer->find($criteria);
    }
    
    public function show() {
        $this->begin();
        $this->top('Employers');
        $this->menu_employee('employers');
        
        $subscriptions_rates = $GLOBALS['subscriptions_rates'];
        $branch = $this->employee->getBranch();
        $employers = $this->get_employers($branch[0]['id']);
        
        // $available_subscriptions = $subscriptions_rates[Currency::getSymbolFromCountryCode($branch[0]['country_code'])];
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div class="buttons_bar">
            <input class="button" type="button" id="add_new_employer" name="add_new_employer" value="Add New Employer" onClick="add_new_employer();" />
        </div>
        <div id="div_employers">
        <?php
            if (is_null($employers) || count($employers) <= 0 || $employers === false) {
            ?>
            <div class="empty_results">No employers at this moment.</div>
            <?php
            } else {
            $employers_table = new HTMLTable('employers_table', 'employers');
            
            $employers_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('employers', 'employers.joined_on');\">Joined On</a>", '', 'header');
            $employers_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('employers', 'employers.name');\">Employer</a>", '', 'header');
            $employers_table->set(0, 2, "<a class=\"sortable\" onClick=\"sort_by('employers', 'employees.lastname');\">Registered By</a>", '', 'header');
            $employers_table->set(0, 3, "<a class=\"sortable\" onClick=\"sort_by('employers', 'employer_sessions.first_login');\">First Login</a>", '', 'header');
            $employers_table->set(0, 4, 'Quick Actions', '', 'header action');
            
            foreach ($employers as $i=>$employer) {
                $employers_table->set($i+1, 0, $employer['formatted_joined_on'], '', 'cell');
                
                $employer_short_details = '<a class="no_link employer_link" onClick="show_employer(\''. $employer['id']. '\');">'. desanitize($employer['employer']). '</a>'. "\n";
                $employer_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Tel.:</span> '. $employer['phone_num']. '</div>'. "\n";
                $employer_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Fax: </span>'. $employer['fax_num']. '</div>'. "\n";
                $employer_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Email: </span><a href="mailto:'. $employer['email_addr']. '">'. $employer['email_addr']. '</a></div>'. "\n";
                $employer_short_details .= '<div class="small_contact"><span style="font-weight: bold;">Contact:</span> '. $employer['contact_person']. '</div>'. "\n";
                $employers_table->set($i+1, 1, $employer_short_details, '', 'cell');
                
                $employers_table->set($i+1, 2, $employer['employee'], '', 'cell');
                
                $employers_table->set($i+1, 3, $employer['formatted_first_login'], '', 'cell');
                
                $actions = '';
                if ($employer['active'] == 'Y') {
                    $actions = '<input type="button" id="activate_button_'. $i. '" value="De-activate" onClick="activate_employer(\''. $employer['id']. '\', \''. $i. '\');" />';
                    $actions .= '<input type="button" id="password_reset_'. $i. '" value="Reset Password" onClick="reset_password(\''. $employer['id']. '\');" />';
                } else {
                    $actions = '<input type="button" id="activate_button_'. $i. '" value="Activate" onClick="activate_employer(\''. $employer['id']. '\', \''. $i. '\');" />';
                    $actions .= '<input type="button" id="password_reset_'. $i. '" value="Reset Password" onClick="reset_password(\''. $employer['id']. '\');" disabled />';
                }
                $actions .= '<input type="button" value="New From" onClick="add_new_employer(\''. $employer['id']. '\');" />';
                
                $employers_table->set($i+1, 4, $actions, '', 'cell action');
            }
            
            echo $employers_table->get_html();
        ?>
        </div>
        <div class="buttons_bar">
            <input class="button" type="button" id="add_new_employer" name="add_new_employer" value="Add New Employer" onClick="add_new_employer();" />
        </div>
        
        <form id="employer_page_form" method="post" action="employer.php">
            <input type="hidden" id="id" name="id" value="" />
            <input type="hidden" id="from_employer" name="from_employer" value="" />
        </form>
        <?php
        }
        ?>
        
        <?php
    }
}
?>