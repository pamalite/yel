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
                            Job Slots left: <span id="num_slots" style="font-weight: bold;">0</span>
                            <br/>
                            Expiring on: <span id="slots_expiry" style="font-weight: bold;"></span>
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
                    <td class="price_per_slot_title">Price (<?php echo $currency; ?>)</td>
                    <td class="amount_title">Amount (<?php echo $currency; ?>)</td>
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
                        <td class="label">Price:</td>
                        <td><?php echo $currency; ?>$&nbsp;<span id="price_per_slot">200</span></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="qty">Number of slots:</label></td>
                        <td><input type="text" class="field" id="qty" name="qty" value="5" onKeyUp="calculate_fee();" />&nbsp;<span style="font-size: 9pt; color: #888888;">discount: <span id="discount">0%</span></span></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;" class="label">Amount:</td>
                        <td style="border-top: 1px solid #666666; border-bottom: 1px double #666666; font-weight: bold;">
                            <?php echo $currency; ?>$&nbsp;<span id="total_amount">1000</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Payment Method:</td>
                        <td>
                            <table style="border: none; margin: auto; width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 10px;">
                                        <input type="radio" name="payment_method" id="payment_method_credit_card" value="credit_card" checked onClick="remove_admin_fee();" />
                                    </td>
                                    <td>
                                        <label for="payment_method_credit_card">Credit Card/PayPal <span style="font-size: 7pt; color: #666666;">(via PayPal portal)</span></label>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="vertical-align: top;">
                                        <input type="radio" name="payment_method" id="payment_method_cheque" value="cheque" onClick="add_admin_fee();" />
                                    </td>
                                    <td>
                                        <label for="payment_method_cheque">Cheque/Money Order/Bank Transfer <span style="font-size: 7pt; color: #666666;">(+5% admin fee)</span></label>
                                    </td>
                                </tr>
                            </table>
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