<?php
require_once dirname(__FILE__). "/../../utilities.php";

class FaqPage extends Page {
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_faq_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/faq.css">'. "\n";
    }
    
    public function insert_faq_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/faq.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline scripts for welcome page goes here.
    }
    
    public function show() {
        $this->begin();
        $this->top("Yellow Elevator&nbsp;&nbsp;<span style=\"color: #FC8503;\">Frequently Asked Questions</span>");
        ?>
        <div class="questions">
            <div class="general">
                <span style="font-size: 18pt; font-weight: bolder; padding-bottom: 5px;">General</span>
                <ol class="general">
                    <li><a href="#general.1">Can anyone join Yellow Elevator?</a></li>
                    <li><a href="#general.2">Is there a membership fee?</a></li>
                    <li><a href="#general.3">Can I register several accounts?</a></li>
                    <li><a href="#general.4">Can I unsubscribe?</a></li>
                    <li><a href="#general.5">Is the data transferred between my browser and Yellow Elevator secure?</a></li>
                </ol>
            </div>
            <div class="members">
                <span style="font-size: 18pt; font-weight: bolder; padding-bottom: 5px;">Membership</span>
                <ol class="members">
                    <li><a href="#members.1">Is Yellow Elevator for mid-high level executives only?</a></li>
                    <li><a href="#members.2"> Is Yellow Elevator for referrers only? Can I apply for job positions posted at Yellow Elevator?</a></li>
                    <li><a href="#members.3">Must I be referred in order to apply for a job position?</a></li>
                    <li><a href="#members.4">I am from the U.S.A. and my contact is from Singapore. Can I refer my contact to a job position in Australia?</a></li>
                    <li><a href="#members.5">When will I receive my reward when there is a successful referral (employment)?</a></li>
                    <li><a href="#members.6">How does Yellow Elevator pay me?</a></li>
                </ol>
            </div>
        </div>
        <hr/>
        <div class="answers">
            <div class="general">
                <span style="font-size: 18pt; font-weight: bolder; padding-bottom: 5px;">General</span>
                <ol class="general">
                    <li>
                        <a name="general.1"></a>
                        <div class="question">Can anyone join Yellow Elevator?</div>
                        <div class="answer">Yes, anyone from any part of the world can join Yellow Elevator.</div>
                    </li>
                    <li>
                        <a name="general.2"></a>
                        <div class="question">Is there a membership fee?</div>
                        <div class="answer">Membership is totally free.</div>
                    </li>
                    <li>
                        <a name="general.3"></a>
                        <div class="question">Can I register several accounts?</div>
                        <div class="answer">Yes you can. However, we advise you to stick to just one account for easy tracking/references.</div>
                    </li>
                    <li>
                        <a name="general.4"></a>
                        <div class="question">Can anyone join Yellow Elevator?</div>
                        <div class="answer">Yes, you can. Simply click on "Unsubscribe" in the "Profile" section to unsubscribe.</div>
                    </li>
                    <li>
                        <a name="general.5"></a>
                        <div class="question">Is the data transferred between my browser and Yellow Elevator secure?</div>
                        <div class="answer">Yes. When you sign in, all information are encrypted using Secure Socket Layer (SSL). Your web address should start with "https" and you can see a padlock on your browser. If you find that the 2 features are missing, please inform us immediately.</div>
                    </li>
                </ol>
            </div>
            <div class="members">
                <span style="font-size: 18pt; font-weight: bolder; padding-bottom: 5px;">Membership</span>
                <ol class="members">
                    <li>
                        <a name="members.1"></a>
                        <div class="question">Is Yellow Elevator for mid-high level executives only?</div>
                        <div class="answer">Anybody can join Yellow Elevator. However, Yellow Elevator focuses primarily on mid-high level job positions. As such, mid-high level executives will benefit from it most.</div>
                    </li>
                    <li>
                        <a name="members.2"></a>
                        <div class="question">Is Yellow Elevator for referrers only? Can I apply for job positions posted at Yellow Elevator?</div>
                        <div class="answer">As a member of Yellow Elevator, you can act as both a referrer and a job seeker. Referrer when you refer jobs to your contacts; job seeker when you apply for job positions posted at Yellow Elevator.</div>
                    </li>
                    <li>
                        <a name="members.3"></a>
                        <div class="question">Must I be referred in order to apply for a job position?</div>
                        <div class="answer">Yes, you must be referred before you can apply for a job position. If you identify a job position that you are interested in, you may request for your desired referrer to refer you.</div>
                    </li>
                    <li>
                        <a name="members.4"></a>
                        <div class="question">I am from the U.S.A. and my contact is from Singapore. Can I refer my contact to a job position in Australia?</div>
                        <div class="answer">Yes, you can refer your contacts (and apply for job positions) from any part of the world.</div>
                    </li>
                    <li>
                        <a name="members.5"></a>
                        <div class="question">When will I receive my reward when there is a successful referral (employment)?</div>
                        <div class="answer">You will receive your reward within 30 days right after Yellow Elevator has received payment from the employer.</div>
                    </li>
                    <li>
                        <a name="members.6"></a>
                        <div class="question">How does Yellow Elevator pay me?</div>
                        <div class="answer">Yellow Elevator pays referrers electronically. You are required to submit your bank account details in "Bank Accounts". If you wish to receive payments by cheque, you are required to inform us through email (<a href="mailto:billing@yellowelevator.com">billing@yellowelevator.com</a>) within 30 days starting from the day your contact accepts the employment offer from the employer. Your payment will be forfeited if you fail to either submit your bank account details or send us an email. All fees including but not limited to administration fee, postage, transaction fee, and GST will be charged accordingly.</div>
                    </li>
                </ol>
            </div>
        </div>
        <?php
    }
}
?>