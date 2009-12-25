<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployerSlotsPage extends Page {
    private $employer = NULL;
    
    function __construct($_session) {
        $this->employer = new Employer($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employer_slots_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employer_slots.css">'. "\n";
    }
    
    public function insert_employer_slots_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employer_slots.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts($_job = 0) {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employer->id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->support();
        $this->top($this->employer->get_name(). "&nbsp;&nbsp;<span style=\"color: #FC8503;\">Job Slots</span>");
        $this->menu('employer', 'slots');
        
        $query = "SELECT currencies.symbol FROM currencies 
                  LEFT JOIN employers ON currencies.country_code = employers.country 
                  WHERE employers.id = '". $this->employer->id(). "' LIMIT 1";
        $mysqli = Database::connect();
        $result = $mysqli->query($query);
        $currency = '???';
        if (count($result) > 0 && !is_null($result)) {
            $currency = $result[0]['symbol'];
        }
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_slots_info">
            <table class="slots_info">
                <tr>
                    <td class="info">
                        <div class="slots_details">
                            Number of Job Slots left: <span id="num_slots">0</span>
                            <br/>
                            Expiring on: <span id="slots_expiry" style="font-style: italic;"></span>
                        </div>
                    </td>
                    <td class="buy"><input type="button" value="Buy Slots" onClick="show_buy_slots_form();" /></td>
                </tr>
            </table>
        </div>
        <div id="div_buying_history">
            <table class="header">
                <tr>
                    <td class="date">Date of Purchase</td>
                    <td class="number_of_slots_title">Number of Slots</td>
                    <td class="price_per_slot_title">Slot Price</td>
                    <td class="amount_title">Amount</td>
                </tr>
            </table>
            <div id="div_list">
            </div>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_buy_slots_form">
            <form onSubmit="return false;">
                <input type="hidden" id="currency" name="currency" value="<?php echo $currency; ?>" />
                <table class="buy_slots_form">
                    <tr>
                        <td class="label">Price per slot:</td>
                        <td><?php echo $currency; ?><span id="price_per_slot">200</span></td>
                    </tr>
                    <tr>
                        <td class="label">Number of slots:</td>
                        <td><input type="text" class="field" id="num_slots" name="num_slots" value="5" /></td>
                    </tr>
                    <tr>
                        <td style="border-top: 1px solid #666666; font-weight: bold; class="label">Total:</td>
                        <td style="border-bottom: 1px solid #666666; font-weight: bold;">
                            <?php echo $currency; ?><span id="total_amount">1000</span>
                        </td>
                    </tr>
                </table>
                <p class="button"><input type="button" value="Cancel" onClick="close_buy_slots_form();" />&nbsp;<input type="button" value="Buy Now" onClick="buy_slots();" /></p>
            </form>
        </div>
        
        <?php
    }
}
?>