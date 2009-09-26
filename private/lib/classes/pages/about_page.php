<?php
require_once dirname(__FILE__). "/../../utilities.php";

class AboutPage extends Page {
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_about_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/about.css">'. "\n";
    }
    
    public function insert_about_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/about.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline scripts for welcome page goes here.
    }
    
    public function show() {
        $this->begin();
        $this->top("Yellow Elevator&nbsp;&nbsp;<span style=\"color: #FC8503;\">About Us</span>");
        ?>
        <div class="content">
            <table class="content">
                <tr>
                    <td class="content">
                        <p>Yellow Elevator revolutionizes the job recruitment industry by transforming the conventional recruitment method into a web-based job referral system.  This is a more selective, efficient, and cost effective way of recruiting superior quality candidates who are the best possible fit for the jobs through referrals and recommendations.</p>
                        <p>Attracting the right talents is a serious challenge faced by many employers today.  However, we believe that this can be resolved if hiring was done through strong referrals and personal recommendations as opposed to conventional open recruitment. Yellow Elevator has a strong network of referrers across various industries to help us find the most suitable candidates for you.</p>
                        <p>Founded by Wong Sui Cheng, Yellow Elevator's mission is to be a world renowned online job recruitment company that provides superior quality candidates and great customer service at the most competitive rates.</p>
                        <p>Yellow Elevator creates a win-win situation for all parties involved in the recruitment process:</p>
                        <p>We help Employers: <br/>
                            <ul>
                                <li>Reach out to superior quality candidates</li>
                                <li>Reduce hiring costs with our competitive rates</li>
                                <li>Increase hiring efficiency</li>
                                <li>Tap into a network of global talents effectively</li>
                            </ul>
                        </p>
                        <p>We help Candidates: <br/>
                            <ul>
                                <li>Get referred to better and more suitable jobs</li>
                                <li>Gain access to greater job opportunities locally and globally</li>
                            </ul>
                        </p>
                        <p>We offer Referrers the opportunity to: <br/>
                            <ul>
                                <li>Get rewarded for every successful referral</li>
                                <li>Contribute to industry growth by referring superior quality candidates to employers</li>
                            </ul>
                        </p>
                    </td>
                    <td class="company_video">
                        <p class="caption">Corporate Video</p>
                        <object width="320" height="265"><param name="movie" value="http://www.youtube.com/v/mSo8Sd8cWQ8&hl=en&fs=1&rel=0"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/mSo8Sd8cWQ8&hl=en&fs=1&rel=0" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="320" height="265"></embed></object>
                        <p class="note">Click the 'play' button to watch our corporate video.</p>
                        <p>Click here to view our <a href="advisory.php">Members of the Advisory Board</a>.</p>
                    </td>
                </tr>
            </table>
            <!--a href="video/Yellow_Elevator.wmv">Click here to download the Corporate Video</a><br /-->
            
        </div>
        <?php
    }
}
?>