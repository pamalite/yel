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
        $this->top("Yellow Elevator&nbsp;&nbsp;<span style=\"color: #FC8503;\">Contact Us</span>");
        ?>
        <div class="content">
            <table class="contact">
                <tr>
                    <td colspan="4" style="text-align: center; padding-bottom: 50px;">
                        <span style="font-weight: bold;">Technical Support, Password Reset &amp; Help:</span><br/><br/>
                        support@yellowelevator.com
                    </td>
                </tr>
                <tr>
                    <td rowspan="1">
                        <img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/flags/MY.gif" />
                    </td>
                    <td style="padding-right: 20px;">
                        <div>
                            <span style="font-weight: bold;">Malaysia</span><br/>
                            Yellow Elevator Sdn. Bhd.<br/>
                            1-12B-9, Suntech @ Penang Cybercity,<br/>
                            Lintang Mayang Pasir 3, <br/>
                            11950 Penang, Malaysia.
                        </div>
                        <div style="padding-top: 10px;">
                            <span style="font-weight: bold;">Tel:</span> +6 04 640 6363<br/>
                            <span style="font-weight: bold;">Fax:</span> +6 04 640 6366<br/>
                        </div>
                        <div style="padding-top: 10px;">
                            <span style="font-weight: bold;">E-mail addresses:</span>
                            <ul style="margin-top: 3px; margin-left: -20px;">
                                <li>
                                    <span style="font-weight: bold; font-size: 8pt;">Sales &amp; Enquiries</span><br/>
                                    sales.my@yellowelevator.com
                                </li>
                                <li>
                                    <span style="font-weight: bold; font-size: 8pt;">Billing Information</span><br/>
                                    billing.my@yellowelevator.com
                                </li>
                            </ul>
                        </div>
                    </td>
                    <!-- td rowspan="1" style="padding-left: 20px; border-left: 1px dashed #CCCCCC;">
                        <img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/flags/AU.gif" />
                    </td>
                    <td>
                        <div>
                            <span style="font-weight: bold;">Australia</span><br/>
                            Yellow Elevator Pty Ltd<br/>
                            Suite 3, 22 Council St, <br/>
                            Hawthorn East, <br/>
                            VIC  3123, Australia.
                        </div>
                        <div style="padding-top: 10px;">
                            <span style="font-weight: bold;">Tel:</span> +61 03 9882 7164<br/>
                            <span style="font-weight: bold;">Fax:</span> +61 03 9882 9792<br/>
                        </div>
                        <div style="padding-top: 10px;">
                            <span style="font-weight: bold;">E-mail addresses:</span>
                            <ul style="margin-top: 3px; margin-left: -20px;">
                                <li>
                                    <span style="font-weight: bold; font-size: 8pt;">Sales &amp; Enquiries</span><br/>
                                    sales.au@yellowelevator.com
                                </li>
                                <li>
                                    <span style="font-weight: bold; font-size: 8pt;">Billing Information</span><br/>
                                    billing.au@yellowelevator.com
                                </li>
                            </ul>
                        </div>
                    </td -->
                    <td rowspan="1">
                        <img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/flags/SG.gif" />
                    </td>
                    <td style="padding-right: 20px;">
                        <div>
                            <span style="font-weight: bold;">Singapore</span><br/>
                            Yellow Elevator Sdn. Bhd.<br/>
                            1-12B-9, Suntech @ Penang Cybercity,<br/>
                            Lintang Mayang Pasir 3, <br/>
                            11950 Penang, Malaysia.
                        </div>
                        <div style="padding-top: 10px;">
                            <span style="font-weight: bold;">Tel:</span> +6 04 640 6363<br/>
                            <span style="font-weight: bold;">Fax:</span> +6 04 640 6366<br/>
                        </div>
                        <div style="padding-top: 10px;">
                            <span style="font-weight: bold;">E-mail addresses:</span>
                            <ul style="margin-top: 3px; margin-left: -20px;">
                                <li>
                                    <span style="font-weight: bold; font-size: 8pt;">Sales &amp; Enquiries</span><br/>
                                    sales.sg@yellowelevator.com
                                </li>
                                <li>
                                    <span style="font-weight: bold; font-size: 8pt;">Billing Information</span><br/>
                                    billing.sg@yellowelevator.com
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
}
?>