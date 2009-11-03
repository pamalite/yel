<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployeeRecommenderTokensPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_recommender_tokens_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_recommender_tokens.css">'. "\n";
    }
    
    public function insert_employee_recommender_tokens_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_recommender_tokens.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_employee($this->employee->get_name(). " - Recommender Tokens");
        $this->menu_employee($this->clearances, 'recommender_tokens');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_tokens">
            <table class="header">
                <tr>
                    <td class="date"><span class="sort" id="sort_presented_on">Presented On</span></td>
                    <td class="recommender"><span class="sort" id="sort_recommender">Recommender</span></td>
                    <td class="token"><span class="sort" id="sort_token">Token</span></td>
                </tr>
            </table>
            <div id="div_tokens_list">
            </div>
        </div>
        <?php
    }
}
?>