<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). "/../../../". $GLOBALS['openinviter_path']. "/openinviter.php";

class MemberInvitesPage extends Page {
    private $member = NULL;
    private $inviter = NULL; 
    private $oi_services = array();
    
    function __construct($_session) {
        $this->member = new member($_session['id'], $_session['sid']);
        $this->inviter = new OpenInviter();
        $this->oi_services = $this->inviter->getPlugins();
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_invites_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/list_box.css">'. "\n";
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/member_invites.css">'. "\n";
    }
    
    public function insert_member_invites_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/list_box.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/member_invites.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->member->id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function generate_open_inviter_services() {
        echo '<select class="field" id="oi_service" name="oi_service">'. "\n";
        echo '<option value="0">Select a service</option>'. "\n";
        echo '<option disabled>&nbsp;</option>'. "\n";
        foreach ($this->oi_services as $type => $services) {
            echo '<option style="font-weight:bold;" disabled>'. $this->inviter->pluginTypes[$type]. '</option>'. "\n";
            foreach ($services as $service => $details) {
                echo '<option value="'. $service. '">&nbsp;&nbsp;&nbsp;'. $details['name']. '</option>'. "\n";
            }
        }
        echo '</select>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_search($this->member->get_name(). " - Invite Contacts");
        $this->menu('member', 'invites');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_tabs">
            <ul>
                <li id="li_smart_invite">Smart</li>
                <li id="li_manual_invite">Manual</li>
            </ul>
        </div>
        <div id="div_smart_invite">
            <form id="invite" method="post" onSubmit="return false;">
                <div id="get_contacts_form">
                    <table class="invite_form">
                        <tr>
                            <td class="instruction" colspan="2">You can login to your Web-based email or social networking accounts to send invites directly to them. Just select the service (email/social networking) from the drop-down box, enter the username and password to that account to get the list of contacts from that account, select which contacts you want to the invite to be send to, type in a personal message, and finally press the 'Send' button. </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td class="label"><label for="oi_services">Online Service:</label></td>
                            <td class="field"><?php $this->generate_open_inviter_services(); ?></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="username">Username:</label></td>
                            <td class="field"><input type="text" class="field" id="username" name="username" /></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="password">Password:</label></td>
                            <td class="field"><input type="password" class="field" id="password" name="password" /></td>
                        </tr>
                        <tr>
                            <td class="buttons_bar" colspan="2"><input type="button" id="get_contacts" value="Get My Contacts" /></td>
                        </tr>
                    </table>
                </div>
                
                <div id="send_invite_form">
                    <input type="hidden" id="oi_session_id" name="oi_session_id" value="0" />
                    <table class="invite_form">
                        <tr>
                            <td class="label">My Contacts from <span id="oi_service_name"></span></td>
                            <td class="field"><div class="contacts" id="contacts"></div></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="smart_message">Message:</label></td>
                            <td class="field">
                                <textarea id="smart_message" name="smart_message"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td class="buttons_bar" colspan="2"><input type="button" id="send_smart" value="Send" /></td>
                        </tr>
                    </table>
                </div>
            </form>
        </div>
        
        <div id="div_manual_invite">
            <form id="invite" method="post" onSubmit="return false;">
                <table class="invite_form">
                    <tr>
                        <td class="instruction" colspan="2">To invite multiple contacts, separate your contacts' email addresses by spaces <span style="font-weight: bold;">i.e. contact1@email1.com contact2@email2.com contact3@email3.com</span>. Then, write a message to tell your contacts about Yellow Elevator before clicking 'Send'. </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="email_addresses">E-mail Addresses:</label></td>
                        <td class="field">
                            <textarea id="email_addresses" name="email_address"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="message">Message:</label></td>
                        <td class="field"><textarea id="message" name="message"></textarea></td>
                    </tr>
                    <tr>
                        <td class="buttons_bar" colspan="2"><input type="button" id="send" value="Send" /></td>
                    </tr>
                </table>
            </form>
        </div>
        
        <?php
    }
}
?>