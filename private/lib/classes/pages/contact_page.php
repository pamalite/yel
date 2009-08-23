<?php
require_once dirname(__FILE__). "/../../utilities.php";

class ContactPage extends Page {
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_contact_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/contact.css">'. "\n";
    }
    
    public function insert_contact_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/contact.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline scripts for welcome page goes here.
    }
    
    public function show() {
        $this->begin();
        $this->top("Yellow Elevator - Contact Us");
        ?>
        <div class="content">
            <table class="contact">
                <tr>
                    <td colspan="2" class="title">Sales &amp; Enquiries</td>
                </tr>
                <tr>
                    <td class="label">Telephone:</td>
                    <td class="text">+6 04 640 6363</td>
                </tr>
                <tr>
                    <td class="label">E-mail:</td>
                    <td class="text">sales@yellowelevator.com</td>
                </tr>
                <tr>
                    <td colspan="2" class="title">Billing Information</td>
                </tr>
                <tr>
                    <td class="label">Telephone:</td>
                    <td class="text">+6 04 640 6363</td>
                </tr>
                <tr>
                    <td class="label">Fax:</td>
                    <td class="text">+6 04 640 6366</td>
                </tr>
                <tr>
                    <td class="label">E-mail:</td>
                    <td class="text">billing@yellowelevator.com</td>
                </tr>
                <tr>
                    <td colspan="2" class="title">Support</td>
                </tr>
                <tr>
                    <td class="label">Telephone:</td>
                    <td class="text">+6 04 640 6363</td>
                </tr>
                <tr>
                    <td class="label">E-mail:</td>
                    <td class="text">support@yellowelevator.com</td>
                </tr>
                <tr>
                    <td colspan="2" class="title">Mailing Address</td>
                </tr>
                <tr>
                    <td class="label">Main Office:</td>
                    <td class="text">
                        Yellow Elevator Sdn. Bhd.<br>
                        1-12B-9, Suntech @ Penang Cybercity,<br>
                        Lintang Mayang Pasir 3, <br>
                        11950 Penang, Malaysia.<br>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
}
?>