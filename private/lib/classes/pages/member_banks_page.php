<?php
require_once dirname(__FILE__). "/../../utilities.php";

class MemberBanksPage extends Page {
    private $member = NULL;
    
    function __construct($_session) {
        $this->member = new Member($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_banks_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/member_banks.css">'. "\n";
    }
    
    public function insert_member_banks_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/member_banks.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->member->id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_search($this->member->get_name(). " - Bank Accounts");
        $this->menu('member', 'banks');
        
        ?>
        <div class="banner">
            We will bank your rewards directly into your account for every successful job referral you make.<br/><br/>
            Please ensure that the bank account information you provide is genuine and accurate. Failure to do so may result in you not receiving your reward.
        </div>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_banks">
            <table class="buttons">
                <tr>
                    <td class="left"><input class="button" type="button" id="delete_accounts" name="delete_accounts" value="Delete Selected Accounts" /></td>
                    <td class="right">
                        <input class="button" type="button" id="add_new_account" name="add_new_account" value="Add Bank Account" />
                    </td>
                </tr>
            </table>
            <table class="header">
                <tr>
                    <td class="checkbox"><input type="checkbox" id="select_all" /></td>
                    <td class="bank"><span class="sort" id="sort_bank">Bank/BSB</span></td>
                    <td class="account"><span class="sort" id="sort_account">Account Number</span></td>
                    <td class="edit">&nbsp;</td>
                </tr>
            </table>
            <div id="div_list">
            </div>
            <table class="buttons">
                <tr>
                    <td class="left"><input class="button" type="button" id="delete_accounts_1" name="delete_accounts_1" value="Delete Selected Accounts" /></td>
                    <td class="right">
                        <input class="button" type="button" id="add_new_account_1" name="add_new_account_1" value="Add Bank Account" />
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_bank_form">
            <form method="post" onSubmit="return false;">
                <input type="hidden" id="bank_id" name="bank_id" value="0" />
                <p class="instructions">Please enter the name of the bank/BSB and your account number.</p>
                <table id="bank_form" class="bank_form">
                    <tr>
                        <td class="label"><label for="bank:">Bank/BSB:</label></td>
                        <td class="field"><input class="field" type="text" id="bank" name="bank" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="account:">Account Number:</label></td>
                        <td class="field"><input class="field" type="text" id="account" name="account" /></td>
                    </tr>
                </table>
                <p class="button"><input class="button" type="button" value="Cancel" onClick="close_bank_form();" />&nbsp;<input class="button" type="button" id="save_bank" name="save_bank" value="Save" onClick="save_bank_account();" /></p>
            </form>
        </div>
        
        <?php
    }
}
?>