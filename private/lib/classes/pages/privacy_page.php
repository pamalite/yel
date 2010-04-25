<?php
require_once dirname(__FILE__). "/../../utilities.php";

class PrivacyPage extends Page {
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_privacy_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/privacy.css">'. "\n";
    }
    
    public function insert_privacy_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/privacy.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline scripts for welcome page goes here.
    }
    
    public function show() {
        $this->begin();
        $this->top("Privacy Policy");
        ?>
        <div class="content">
            <span class="title">Introduction</span></br>
           
            <p>Yellow Elevator's Privacy Policy is designed to assist you in understanding how we collect and use the personal information that you provide to us and to assist you in making informed decisions when using www.yellowelevator.com (the "Website").</p>
           
            <span class="title">The information we collect</span></br>
           
            <p>When you visit the Website, you may provide us with personal information. Personal information is information you knowingly choose to disclose to us. Personal information includes, but not limited to your name, email address, gender, birth date, telephone number, address, educations and qualifications, employment history, bank account numbers, and any other personal information. All personal information which we collect is kept confidential to the best of our ability. </p>
           
            <span class="title">Use of information obtained by the Website</span></br>
           
            <p>Unless you object, the information we collect may be used: 
                <ul>
                    <li>to send news, information about our activities and general promotional material which we believe may be useful to you; </li>
                    <li>to monitor who is accessing the Website or using services offered on the Website; and </li>
                    <li>to profile the type of people accessing the Website.</li>
                </ul>
            </p>
           
            <p>We utilize "cookies" which enable us to monitor traffic patterns and to serve you more efficiently if you revisit the site. A cookie does not identify you personally but it does identify your computer. You can set your browser to notify you when you receive a cookie and this will provide you with an opportunity to either accept or reject it in each instance.</p>
           
            <p>We will not sell or otherwise provide your personal information to a third party, or make any other use of your personal information, for any purpose which is not incidental to your use of this Website, unless required by law or for the protection of your Membership. For the removal of doubt, personal information will not be used for any purpose which a reasonable person in your position would not accept.</p>
           
            <p>We may however share any personal information with our business partners, and/or any affiliates of the Website in order to facilitate various services to you, to extend your benefits and special offers, as well as, for marketing purposes which include, but not limited to planning, product development, telemarketing promotions, research, and any other carefully screened marketing programmes or activities, of which we believe are likely to interest you.</p>
           
            <p>If you request us not to use personal information in a particular manner or at all, we will adopt all reasonable measure to observe your request but we may still use or disclosed that information if:
                <ul>
                    <li>we subsequently notify you of the intended use or disclosure and you do not object to that use or disclosure; </li>
                    <li>we believe that the use or disclosure is reasonably necessary to assist a law enforcement agency or an agency responsible for government or public security in the performance of their functions; or </li>
                    <li>we required by law to disclose the information.</li>
                </ul>
            </p>
            
            <p>We will preserve the content of any e-mail you send us if we believe we have the legal requirement to do so.</p>
            
            <p>Your e-mail message content may be monitored by us for trouble-shooting or maintenance purpose or if any form of e-mail abuse suspected.</p>
            
            <p>Personal information which we collect may be aggregated for analysis but in such circumstances we would ensure that individual would remain anonymous.</p>
            
            <p>You may entitle to have access to any personal information relating to you which you have previously supplied to us over this Website. You are entitled to edit or delete such information unless we are required by law to retain it.</p>
            
            <span class="title">Links</span></br>
            
            <p>The Website may contain links to other websites. We are not responsible for the privacy practices of other websites. We encourage our Members or visitors to be aware when they leave our Website and to read the privacy statements of each and every website that collects personally identifiable information. This privacy statement applies solely to information collected by our Website.</p>
            
            <span class="title">Security</span></br>
            
            <p>Your account with us is password-protected. We take every precaution to protect your information. Your account information is located in a secured server behind a firewall. We limit the access to your information to those employees who need access to perform their job function. We take all practicable steps to ensure the security of your account.</p>
            
            <span class="title">Changes in our privacy policy</span></br>
            
            <p>We reserve the right to change our privacy policy at any time. If we do this, we will post at the top of this page indicating that we have made changes to our privacy policy, as well as, indicating the our revised privacy policy's effective date. We therefore encourage you to refer to this policy on an ongoing basis so that you understand our most current privacy policy.</p>
        </div>
        <?php
    }
}
?>