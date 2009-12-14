<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). "/../../../". $GLOBALS['openinviter_path']. "/openinviter.php";

class MemberCandidatesPage extends Page {
    private $member = NULL;
    private $inviter = NULL; 
    private $oi_services = array();
    
    function __construct($_session) {
        $this->member = new Member($_session['id'], $_session['sid']);
        $this->inviter = new OpenInviter();
        $this->oi_services = $this->inviter->getPlugins();
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_candidates_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/list_box.css">'. "\n";
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/member_candidates.css">'. "\n";
    }
    
    public function insert_member_candidates_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/list_box.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/member_candidates.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts($referee_id = 0, $candidate = '') {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->member->id(). '";'. "\n";
        echo 'var attr_get_referee_id = "'. $referee_id. '";'. "\n";
        echo 'var attr_get_candidate = "'. $candidate. '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function generateCountries($selected = '') {
        $countries = Country::get_all();
        
        echo '<select class="field" id="country" name="country">'. "\n";
        
        if ($selected == '') {
            echo '<option value="0" selected>Please select a country</option>'. "\n";
        }
        
        foreach ($countries as $country) {
            if ($country['country_code'] != $selected) {
                echo '<option value="'. $country['country_code']. '">'. $country['country']. '</option>'. "\n";
            } else {
                echo '<option value="'. $country['country_code']. '" selected>'. $country['country']. '</option>'. "\n";
            }
        }
        
        echo '</select>'. "\n";
    }
    
    private function generate_all_industry_list() {
        $industries = Industry::get_main();
        
        echo '<select class="field" id="industry" name="industry">'. "\n";
        
        if ($selected == '') {
            echo '<option value="0" selected>Please select an industry</option>'. "\n";
        }
        
        foreach ($industries as $industry) {
            echo '<option class="main_industry" value="'. $industry['id']. '">'. $industry['industry']. '</option>'. "\n";
            
            $sub_industries = Industry::get_sub_industries_of($industry['id']);
            foreach ($sub_industries as $sub_industry) {
                echo '<option value="'. $sub_industry['id']. '">&nbsp;&nbsp;&nbsp;'. $sub_industry['industry']. '</option>'. "\n";
            }
            
        }
        
        echo '</select>'. "\n";
    }
    
    private function generate_networks_list() {
        $networks = $this->member->get_networks();
        
        echo '<select id="network_dropdown" name="network_dropdown" onChange="add_to_network();">'. "\n";
        echo '<option value="0" selected>Add Selected To Network</option>'. "\n";
        echo '<option value="-1">Create a network</option>'. "\n";
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        
        foreach ($networks as $network) {
            echo '<option value="'. $network['id']. '">'. $network['industry']. '</option>'. "\n";
        }
        
        echo '</select>'. "\n";
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
        $this->top_search($this->member->get_name(). "&nbsp;&nbsp;<span style=\"color: #FC8503;\">Contacts</span>");
        $this->menu('member', 'candidates');
        
        ?>
        <div class="banner" id="div_banner">
            <a class="no_link" onClick="toggle_banner();"><span id="hide_show_label">Hide</span> Guide</a>
            <br/>
            <img style="border: none;" src="../common/images/banner_contacts.jpg" />
        </div>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_tabs">
            <ul>
                <li id="li_candidates">Contacts</li>
                <li id="li_networks">Networks</li>
                <li id="li_invite">Invite Contacts</li>
            </ul>
        </div>
        <div id="div_total_rewards">
            You have earned <br/>
            <span id="rewards"></span><br/>
            as referral rewards. Keep it up!
        </div>
        <div id="div_candidates">
            <table class="buttons">
                <tr>
                    <td class="left"><input class="button" type="button" id="delete_candidates" name="delete_candidates" value="Remove Selected Contacts" />&nbsp;<span id="networks_drop_down"><?php echo $this->generate_networks_list(); ?></span></td>
                    <td class="right">
                        <input class="button" type="button" id="add_candidate" name="add_candidate" value="Add Contact" />
                    </td>
                </tr>
            </table>
            <table class="header">
                <tr>
                    <td class="checkbox"><input type="checkbox" id="select_all" /></td>
                    <!--td class="id">&nbsp;</td-->
                    <td class="name">
                        <span class="sort" id="sort_name">Contacts</span>&nbsp;
                        <span style="font-size: 8pt;">[ Show <span id="network_filter_drop_down"></span> ]</span>
                    </td>
                    <td class="date"><span class="sort" id="sort_referred_on">Added On</span></td>
                </tr>
            </table>
            <div id="div_list">
            </div>
            <table class="buttons">
                <tr>
                    <td class="left"><input class="button" type="button" id="delete_candidates_1" name="delete_candidates_1" value="Remove Selected Contacts" /></td>
                    <td class="right">
                        <input class="button" type="button" id="add_candidate_1" name="add_candidate_1" value="Add Contact" />
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="div_networks">
            <table class="buttons">
                <tr>
                    <td class="left"><input class="button" type="button" id="delete_networks" name="delete_networks" value="Remove Selected Networks" /></td>
                    <td class="right">
                        <input class="button" type="button" id="add_network" name="add_network" value="Add Network" />
                    </td>
                </tr>
            </table>
            <table class="header">
                <tr>
                    <td class="checkbox"><input type="checkbox" id="select_all_networks" /></td>
                    <!--td class="id">&nbsp;</td-->
                    <td class="name">Network</td>
                </tr>
            </table>
            <div id="div_network_list">
            </div>
            <table class="buttons">
                <tr>
                    <td class="left"><input class="button" type="button" id="delete_networks_1" name="delete_networks_1" value="Remove Selected Networks" /></td>
                    <td class="right">
                        <input class="button" type="button" id="add_network_1" name="add_network_1" value="Add Network" />
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="div_search_candidates">
            <table class="buttons">
                <tr>
                    <td class="left">
                        <span style="font-size: 10pt;">
                        Search for your contact by
                        &nbsp;
                        <select id="search_using" name="search_using">
                            <option value="firstname" selected>first name / given names</option>
                            <option value="lastname">last name / surname</option>
                            <option value="email_addr">e-mail address</option>
                        </select>
                        &nbsp;
                        matching
                        &nbsp;
                        <input type="text" id="match" name="match" />
                        . Or, <a class="no_link" onClick="show_invites();">invite contacts here</a>.
                        </span>
                    </td>
                    <td class="right">
                        <input class="button" type="button" id="clear_candidates" name="clear_candidates" value="Clear" />&nbsp;
                        <input class="button" type="button" id="find_candidates" name="find_candidates" value="Search" />
                    </td>
                </tr>
            </table>
            <table class="header">
                <tr>
                    <td class="email">E-mail Address</td>
                    <td class="name"><span class="sort" id="sort_search_name">Contact</span></td>
                    <td class="date"><span class="sort" id="sort_joined_on">Joined On</span></td>
                    <td class="add">&nbsp;</td>
                </tr>
            </table>
            <div id="div_search_result">
                <div style="text-align: center;padding-top: 10px;">Please enter either the first or last name (surname), or the e-mail address to search for the contact you wish to add into <a class="no_link" onClick="show_candidates();">Contacts</a>.</div>
            </div>
        </div>
        
        <div id="div_candidate">
            <input type="hidden" id="candidate_id" name="candidate_id" />
            <div id="div_candidate_details">
                <div id="div_candidate_name"></div>
                <div id="div_candidate_networks"></div>
                <div id="div_candidate_contacts"><span class="email" id="email"></span>&nbsp;&nbsp;&nbsp;<span class="phone" id="phone"></span></div>
            </div>
            
            <div id="div_candidate_history">
                <table class="history">
                    <!--tr>
                        <td class="left">Rewards Earned:</td>
                        <td class="right"><span id="currency">MYR</span>$&nbsp;<span id="total_rewards"></span></td>
                    </tr-->
                </table>
                <div style="padding-top: 10px; padding-bottom: 10px;"><span style="color: #666666; font-style: italic;">[&bull;] means the contact that you have referred to this job has accepted the same job refarral from another member. As such, you will not be eligible for the reward.</span></div>
                <table class="header">
                    <tr>
                        <td class="testimony_title">Testimonial</td>
                        <td class="job"><span class="sort" id="sort_history_by_job">Job</span></td>
                        <td class="employer"><span class="sort" id="sort_history_by_employer">Employer</span></td>
                        <td class="date"><span class="sort" id="sort_history_by_referred_on">Referred On</span></td>
                        <td class="date"><span class="sort" id="sort_history_by_acknowledged_on">Resume Submitted On</span></td>
                        <td class="date"><span class="sort" id="sort_history_by_employed_on">Employed On</span></td>
                        <td class="date"><span class="sort" id="sort_history_by_commence_on">Commence On</span></td>
                        <td class="reward"><span class="sort" id="sort_history_by_reward">Reward</span></td>
                        <td class="reward"><span class="sort" id="sort_history_by_paid">Paid</span></td>
                    </tr>
                </table>
                <div id="div_history">
                </div>
            </div>
        </div>
        
        <div id="div_invite_contacts">
            <div id="div_tabs" style="padding-top: 10px;">
                <ul>
                    <li id="li_smart_invite">Smart</li>
                    <li id="li_vcard_invite">vCards</li>
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
            
            <div id="div_vcard_invite">
                <div id="get_vcard_contacts_form">
                    <form id="get_vcard_contacts" action="invites_action.php" method="post" enctype="multipart/form-data" target="upload_target">
                        <input type="hidden" name="id" value="0" />
                        <input type="hidden" name="action" value="get_contacts_from_vcard" />
                        <p id="upload_progress" style="text-align: center;">
                            <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/progress/circle_big.gif" />
                        </p>
                        <table class="get_contacts_form">
                            <tr>
                                <td class="instruction" colspan="2">You can used the contacts from your own <a href="http://www.apple.com/pro/tips/sharing_vcards.html" target="_new">Address Book</a>, <a href="http://support.microsoft.com/kb/290840" target="_new">Outlook</a>, <a href="http://tbirdhowto.wordpress.com/category/lists-groups/" target="_new">Thunderbird</a> and other contacts application to send invites to. All you need to do is to export your contacts from them to a vCard file (*.vcf), upload it here to see their e-mails listed by clicking "Get My Contacts".</td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="label"><label for="my_file">Your vCard file:</label></td>
                                <td class="field"><input class="field" id="my_file" name="my_file" type="file" /></td>
                            </tr>
                            <tr>
                                <td class="buttons_bar" colspan="2">
                                    <span style="font-size: 9pt;">NOTE: The details from the vCard file are NOT stored on the server.</span>&nbsp;<input class="button" type="submit" id="upload_vcard" name="upload_vcard" value="Get My Contacts" onClick="start_upload();" />
                                </td>
                            </tr>
                        </table>
                    </form>
                    <iframe id="upload_target" name="upload_target" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/blank.php" style="width:0px;height:0px;border:none;"></iframe>
                </div>
                
                <div id="send_vcard_invites_form">
                    <form id="send_vcard_invites" method="post">
                        <table class="send_vcard_invites_form">
                            <tr>
                                <td class="label">Contacts with E-mail Addresses:</td>
                                <td class="field"><div class="vcard_contacts" id="vcard_contacts"></div></td>
                            </tr>
                            <tr>
                                <td class="label"><label for="vcard_message">Message:</label></td>
                                <td class="field">
                                    <textarea id="vcard_message" name="vcard_message"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="buttons_bar" colspan="2"><input type="button" id="send_vcard" value="Send" /></td>
                            </tr>
                        </table>
                    </form>
                </div>
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
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_network_form">
            <p>Please select an industry to be used as the network: <span id="network_industry_drop_down"></span></p>
            <div class="buttons">
                <input class="button" type="button" value="Cancel" onClick="close_network_form();" />
                <input class="button" type="button" value="Create Network" onClick="add_network();" />
            </div>
        </div>
        <div id="div_testimony">
            <div id="testimony" class="testimony"></div>
            <div class="buttons">
                <input type="button" onClick="close_testimony();" value="Close" />
            </div>
        </div>
        
        <?php
    }
}
?>