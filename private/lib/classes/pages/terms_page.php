<?php
require_once dirname(__FILE__). "/../../utilities.php";

class TermsPage extends Page {
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_terms_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/terms.css">'. "\n";
    }
    
    public function insert_terms_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/terms.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline scripts for welcome page goes here.
    }
    
    public function show() {
        $this->begin();
        $this->top("Yelow Elevator - Terms of Use");
        ?>
        <div class="content">
            <span class="title">Definitions</span><br/> 

            <p>The following clauses define identified words and expressions that are used in the Terms of Use. Throughout the Terms of Use, unless and otherwise stated contrarily in a given context, the word or expression in question carries the definition as given below:-</p>
            <ol type="i">
                <li>YEL: means Yellow Elevator Sdn Bhd.</li>
                <li>Membership: means registering and becoming a Member of the Website.</li>
                <li>Candidate: means  a Member who is referred by another Member (the Referrer) to an Employer through the Website and is identified to be suitable for a job vacancy advertised by the Employer.</li>
                <li>Referrer: means a Member who has referred a Job Advertisement to another Member (the Candidate) through the Website.</li>
                <li>Employer: means an organization, whether government, for profit or non-profit, which has published Job Advertisement(s) on the Website and is interested in employing Candidates referred by Referrers through the Website.</li>
                <li>Job Advertisement: means information of a job vacancy that an Employer advertises on the Website.</li>
                <li>Job Application: means the process of which a Candidate has submitted information and his or her resume to an Employer through the Website.</li>
                <li>Potential Reward: means the potential fee, that may be payable to the Referrer upon the successful employment of a Candidate, which is stated in the Website dependent upon the salary structure of any successful job Candidate.</li>
                <li>Reward: means the fee, that will be paid to the Referrer upon the successful employment of a Candidate, which is stated in the Website subject to the confirmation of the successful Candidate fulfilling the requirements of the Employer.</li>
                <li>Website: means the designated website owned by YEL and for the usage of an Employer which is located on the World Wide Web vide universal resource locater: www.yellowelevator.com.</li>
                <li>Contact List: means the feature of the Website that allows a Member to add and keep information of the Member's contacts.</li>
                <li>Referrer System: means the feature of the Website that allows the Referrer to keep track of the Referrer's referred Candidates including, but not limited to, tracking whether the Candidates has been employed by the Employer or not.</li>
                <li>Confirm Employment: means the feature of the Website that serves to notify YEL of each successful employment of a Candidate.</li>
                <li>Guarantee Period: means a specific period of guarantee agreed between YEL and an Employer.</li>
            </ol>
            
            <span class="title">Membership Terms</span><br/>
            
            <p>Membership to the Website is free. By accessing the Website, you represent and warrant that you have the right, authority, and capacity to enter into this agreement and to abide by all of the Terms of Use of this Website. If you do not agree with the Terms of Use, do not sign up as a Member.</p>

            <p>You represent that your access to this Website is not illegal or prohibited by laws which apply to you.</p>

            <p>You must take your own precautions to ensure that the process which you employ for accessing this Website does not expose you to the risk of viruses, malicious computer code or other forms of interference which may damage your own computer system.</p>

            <p>For the removal of doubt, YEL does not accept responsibility for any interference or damage to your own computer system which arises in connection with your use of this Website or any linked websites.</p>

            <p>Whilst we have no reason to believe that any information contained on this Website is inaccurate, we do not warrant the accuracy, adequacy or completeness of such information, nor do we undertake to keep this Website updated. We do not accept responsibility for any loss suffered as a result of reliance by you upon the accuracy or currency of information contained on the Website.</p>

            <p>Responsibility for content of Job Advertisements appearing on this Website (including hyperlinks to Employers' websites) rests solely with the Employers. The placement of such Job Advertisements do not constitute a recommendation or endorsement by YEL of the Employers' Job Advertisements and each Employer is solely responsible for any representations made in connection with the Employer's Job Advertisements.</p>

            <p>Another Member may request that you be added to his/her Contact List. Your approval is required before you are added into that Member's Contact List. If you agree to be added to another Member's Contact List, your personal details may be disclosed to that Member. YEL will not be held responsible for any damage or loss that may arise as a result of the disclosure.</p>

            <p>You are fully responsible to ensure that all information you submit to YEL are true and accurate. Failure to do so may result in you not receiving your Reward or termination of your Membership.</p>
            
            <p>YEL reserves the right to terminate your Membership at any time for any reason, including but not limited to, complaints from Employers regarding your job applications or referrals made by you.</p>
            
            <p>All information provided by us pursuant to these Terms of Use is provided in good faith. You accept that any information provided by us is general information and is not in the nature of advice and as such we cannot be held accountable for any error or mistake based upon the information supplied. We derive our information from sources which we believe to be accurate and up to date as at the date of publication. We nevertheless reserve the right to update this information at any time. In addition, we do not make any representations or warranties that the information we provide is reliable, accurate or complete or that your access to that information will be uninterrupted, timely or secure. We are not liable for any loss resulting from any action taken or reliance made by you on any information or material posted by us. You should make your own inquiries and seek independent advice from relevant industry professionals before acting or relying on any information or material which is made available to you pursuant to our information service.</p>
            
            <span class="title">Terms applicable to Members acting as a Referrer</span></br/>
            
            <p>As a Referrer, you are required to provide all the information specified on this Website which must be true and accurate for the duration of your Membership.</p>

            <p>Upon a successful Candidate's employment referred by you to an Employer, you will be entitled to your Reward within thirty (30) days of YEL receiving payment from the relevant Employer, as well as, upon the expiry of the Guarantee Period.</p>

            <p>You are not entitled to any Reward:<br/>
                <ol type="1">
                    <li>should the Employer fail to make the required payment to YEL upon the successful  employment  of a Candidate.</li>
                    <li>should the Candidate be terminated from employment within the Guarantee Period.</li>
                </ol>
            </p>

            <p>You will take full responsibility to:<br/>
                <ol type="1">
                    <li>follow up closely with your Candidate after you have referred the Candidate to the Employer.</li>
                    <li>procure your Candidate to notify YEL by clicking on the 'Confirm Employed' button in the Confirm Employment section should the Candidate be successfully employed by the Employer. You will not receive your Reward if your Candidate does not click on the 'Confirm Employed' button in the Confirm Employment section upon the successful employment of your Candidate.</li>
                    <li>notify YEL immediately should your referred Candidate be successfully employed by the Employer but the Referrer System does not indicate so.</li>
                    <li>identify and report to YEL any untowards activities that are taking or have taken place with sufficient proof including, but not limited to, an Employer avoiding making payment to YEL after the successful employment of a Candidate.</li>
                    <li>inform YEL if you have reasons to believe or that you have come to knowledge of any information that a successful Candidate may harm the interest of the Employer at any given time.</li>
                </ol>
            </p>
            
            <p>You agree that you will not be in any contact with a relevant Employer other than through the use of this Website. Any attempt to circumvent this will result in the termination of your Membership with YEL.</p>

            <p>YEL is not required at any time to give or provide you reasons as to why a Candidate's job application may be rejected.</p>

            <p>You acknowledge that despite our reasonable precautions, some job applications may be incorrectly listed due to a typographical error, an inadvertent mistake or like oversight. In these circumstances, we reserve the right to cancel the transaction, notwithstanding that your referral may have been confirmed.</p>
        
            <span class="title">Terms applicable to Members acting as a Candidate</span><br/>
            
            <p>As a Candidate, you are required to provide all the information specified on this Website which must be true and accurate for the duration of your Membership. </p>

            <p>You hereby agree and warrant that all information in your resume is true, complete and accurate.  Your resume shall be submitted through the Website for the consideration of employment by Employers. A Referrer may make recommendations to you in respect of future employment and in such event you are required to submit your resume to the Employer for their consideration through the Website.</p>

            <p>We do not warrant the conduct of the Referrer neither do we warrant  the success of your job application via the Website. The information that you provide in your resume is to help ascertain your suitability for a possible vacancy for you. </p>

            <p>You will take full responsibility to:<br/>
                <ol type="1">
                    <li>constantly update your Referrer with the status of your potential employment after you have submitted your resume to the Employer through the Website.</li>
                    <li>attend all interviews conducted by the Employer if you have submitted your resume and have been requested to attend the interviews. If you are unable or decided not to attend the interviews, you take responsibility to inform the Employer and your Referrer. Failure to do so may result in the termination of your Membership and your Referrer's Membership.</li>
                    <li>identify and report to YEL and your Referrer any untowards activities that are taking or have taken place with sufficient proof including, but not limited to, an Employer avoiding making payment to YEL after your successful employment.</li>
                </ol>
            </p>
            
            <p>In the event that you are successfully employed by an Employer, you must immediately:<br/>
                <ol type="1">
                    <li>inform YEL by clicking on the 'Confirm Employed' button in the Confirm Employment section.</li>
                    <li>inform your Referrer regarding your successful employment.</li>
                </ol>
            </p>
            
            <blockquote>NOTICE:<br/>
            In the event you fail to click on the 'Confirm Employed' button in the Confirm Employment section, we have the right to terminate your membership and the Referrer which may also result in the termination of your employment with the Employer.</blockquote>
            
            <p>You must not submit your resume to the Employer through the Website if you are not suitable for the vacancy or if you know that your employment will be detrimental to the Employer. You consent to the use of the information provided by you to be used by a third party in respect of obtaining prospective employment through out Website.</p>

            <p>You agree that you will not be in any contact with the relevant Employer other than through the use of this Website. Any attempt to circumvent this will result in the termination of your Membership and your Referrer's Membership with YEL.</p>

            <p>YEL is not required at any time to give or provide you reasons as to why your job application may be rejected.</p>

            <p>You acknowledge that despite our reasonable precautions, some job applications may be incorrectly listed due to a typographical error, an inadvertent mistake or like oversight. In these circumstances, we reserve the right to cancel the transaction, notwithstanding that your referral may have been confirmed.</p>
            
            <span class="title">Copyrights</span><br/>
            
            <p>Copyright in this Website (including text, graphics, logos, icons, sound recordings and software) is owned or licensed by us. Information procured from a third party may be the subject of copyright owned by that third party. Other than that for the purposes of, and subject to the conditions prescribed under, the Copyright Act 1987 MALAYSIA and similar legislation which applies in your location, and except as expressly authorized by these Terms of Use, you may not in any form or by any means:</p>
            
            <ul type="dot">
                <li>adapt, reproduce, store, distribute, print, display, perform, publish or create derivative works from any part of this Website; or</li>
                <li>commercialize any information, products or services obtained from any part of this Website;</li>
            </ul>    
            
            <p>without the owners written permission or, in the case of third party material, from the owner of the copyright in that material.</p>
            
            <span class="title">Trade Marks</span><br/>
            
            <p>Except where otherwise specified, any word or device to which is attached the &trade; or &reg; symbol is registered trade mark.</p>
            
            <p>If you use any of our trade marks in reference to our activities, products or services, you must include a statement attributing that trade mark to us. You must not use any of our trade marks:</p>
            
            <ul type="dot">
                <li>in or as the whole or part of your own trade marks;</li>
                <li>in connection with activities, products or services which are not ours;</li>
                <li>in a manner which may be confusing, misleading or deceptive;</li>
                <li>in a manner that disparages us or our information, products or services (including this Website). </li>
            </ul>
            
            <span class="title">Restricted Use</span><br/>
            
            <p>Unless we agree otherwise in writing, you are provided with access to this Website only as per the Terms of Use of this Website. You are authorized to print a copy of any information contained on this Website for your personal use, unless such printing is expressly prohibited. Without limiting the foregoing, you may not without our written permission on – sell information contained from this Website.</p>
            
            <span class="title">Linked Websites</span><br/>
            
            <p>This Website may contain links to other websites (“linked website”). Those links are provided for convenience only and may not remain current or be maintained.</p>
            
            <p>We are not responsible for the content or privacy practices associated with linked websites.</p>
            
            <p>Our links with linked websites should not be construed as an endorsement, approval or recommendation by us of the owners or operators of those linked websites, or of any information, graphics, materials, products or services referred to or contained on those linked websites, unless and to the extent stipulated to the contrary. </p>
            
            <span class="title">Privacy Policy</span><br/>
            
            <p>We undertake to comply with the terms of our privacy policy which is annexed to these Terms of Use.</p>
            
            <span class="title">Security of Information</span><br/>
            
            <p>Unfortunately, no data transmission avers the Internet can be guaranteed as totally secure. Whilst we strive to protect such information, we do not warrant and cannot ensure the security of any information which you transmitted to us. Accordingly, any information which you transmit to us is transmitted at your own risk. Nevertheless, once we receive your transmission, we will take reasonable steps to preserve the security of such information.</p>
            
            <span class="title">Termination of Access</span><br/>
            
            <p>Access to this Website may be terminated at any time by us without notice. Our disclaimer will nevertheless survive any such termination.</p>
            
            <span class="title">Governing Law</span><br/>
            
            <p>These Terms of Use are governed by the laws in force in Malaysia. You agree to submit to the exclusive jurisdiction of the courts of this jurisdiction.</p>
            
            <span class="title">General</span><br/>
            
            <p>We accept no liability for any failure to comply with these Terms of Use where such failure is due to circumstances beyond our reasonable control.</p>
                        
            <p>If we waive any rights available to us under these Terms of Use on one occasion, this does not mean that those rights will automatically be waived on any other occasion.</p>
            
            <p>If any of these Terms of Use are held to be invalid, unenforceable or illegal for any reason, the remaining Terms of Use shall nevertheless continue in full force.</p>
            
            <span class="title">To Return to the Website</span><br/>
            
            <p>To return to the Website, click where indicated. By doing so, you acknowledge that you have read, understood and accepted the above Terms of Use.</p>
            
            <span class="title">Changes in Terms of Use</span><br/>
            
            <p>We reserve the right to change our Terms of Use at any time. If we do this, we will post at the top of this page indicating that we have made changes to our Terms of Use, as well as, indicating the our revised Terms of Use's effective date. We therefore encourage you to refer to our Terms of Use on an ongoing basis so that you understand our most current terms.</p>
        </div>
        <?php
    }
}
?>