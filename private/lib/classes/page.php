<?php
require_once dirname(__FILE__). "/../../config/common.inc";

class Page {
    
    public function insert_css() {
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/common.css">'. "\n";
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/Autocompleter.css" type="text/css" media="screen" />';
    }

    public function insert_scripts() {
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/webtoolkit.md5.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/webtoolkit.sha1.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/mootools-1.2-core.js"></script>'. "\n"; // 1.2.3-core
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/mootools-1.2-more.js"></script>'. "\n"; // 1.2.3.1-more
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/common.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/Observer.js"></script>';
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/Autocompleter.js"></script>';
        
    }

    public function header($hashes = "") {
        $override_title = (!isset($hashes['override_title'])) ? false : true;
        $title = (!isset($hashes['title'])) ? '' : $hashes['title'];
        $root_dir = (!isset($hashes['root_dir'])) ? "" : $hashes['root_dir'];
        $insert_styles = (!isset($hashes['insert_styles'])) ? false : $hashes['insert_styles'];
        $insert_scripts = (!isset($hashes['insert_scripts'])) ? false : $hashes['insert_scripts'];
        
        echo '<!DOCTYPE HTML>'. "\n"; // HTML 5
        echo '<meta charset="utf-8">' ."\n";
        echo '<html xmlns="http://www.w3.org/1999/xhtml">'. "\n";
        echo '<head>'. "\n";
        echo '<LINK REL="SHORTCUT ICON" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/images/favicon.ico">'. "\n";
        
        if ($override_title) {
            echo '<title>'. $title. '</title>'. "\n";
        } else {
            echo '<title>'. $GLOBALS['COMPANYNAME']. ' - '.$title. '</title>'. "\n";
        }
        
        if ($insert_styles) {
            $this->insert_css();
        }

        if ($insert_scripts) {
            $this->insert_scripts();
        }
    }
    
    public function begin() {
        echo '</head>'. "\n";
        echo '<body>'. "\n";
    }
    
    public function footer() {
        echo "\n";
        ?>
        <div class="footer">
            <p>
                &copy;<?php echo date('Y'); ?> Yellow Elevator &nbsp; 
                <a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/tour.php">Take a Tour</a> &nbsp; 
                <a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/faq.php">FAQ</a> &nbsp; 
                <a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/about.php">About Us</a> &nbsp; 
    	        <a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/terms.php">Terms of Use</a> &nbsp; 
                <a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/privacy.php">Privacy Policy</a> &nbsp;  
                <a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/contact.php">Contact Us</a> &nbsp; 
                <a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/feedback.php">Feedback</a> &nbsp; 
                <a href="http://twitter.com/yellowelevator" target="_new">Follow Us on Twitter</a> &nbsp; 
            </p>
        </div>
        <?php
        echo '</body>'. "\n";
        echo '</html>'. "\n";
    }
    
    protected function top_welcome() {
        ?>
        <div class="top">
            <div id="welcome_top">
                <img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/logos/yellowelevator-logo.gif" alt="Elevator" width="183" height="107" id="logo" />
                <div class="loginpanel">
                    <?php
                    if (isset($_SESSION['yel']['employer']) && 
                        !empty($_SESSION['yel']['employer']['id']) && 
                        !is_null($_SESSION['yel']['employer']['id'])) {
                        ?>
                    Go back to <span style="font-weight: bold;"><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employers">Employer Home Page</a></span> or <span style="font-weight: bold;"><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employers/logout.php">Logout</a></span>
                        <?php
                    } elseif (isset($_SESSION['yel']['member']) && 
                              !empty($_SESSION['yel']['member']['id']) && 
                              !is_null($_SESSION['yel']['member']['id'])) {
                        ?>
                    Go back to <span style="font-weight: bold;"><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/members">Member Home Page</a></span> or <span style="font-weight: bold;"><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/members/logout.php">Logout</a></span>
                        <?php
                    } else {
                        ?>
                    Login as an <a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employers/" class="employer"><strong>Employer</strong></a> <span class="spacer">or a</span> <a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/members/" class="member"><strong>Member</strong></a>
                    <?php
                    }
                    ?>
                </div>
                <div class="topmenu">
                    <table border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td class="tour" valign="bottom"><a href="#" onClick="show_tour();"><strong>TAKE A TOUR |</strong></a></td>
                            <td class="topmenudivider"><strong> | </strong></td>
                            <td class="aboutus" valign="bottom"><a href="about.php"><strong>ABOUT US</strong></a></td>
                            <td class="topmenudivider"><strong> | </strong></td>
                            <td class="contactus" valign="bottom"><a href="contact.php"><strong>CONTACT US</strong></a></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
    
    protected function top_employee($page_title) {
        ?>
        <div class="top">
            <table class="top">
                <tr>
                    <td rowspan="2" class="logo">
                        <img name="logo" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/logos/top.jpg" />
                    </td>
                    <td><div class="page_title"><?php echo desanitize($page_title) ?></div></td>
                </tr>
                <tr>
                    <td style="text-align: right;">
                        <span style="font-size: 10pt;"><a target="_new" href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/help/employees/">Help</a></span>&nbsp;&nbsp;&nbsp;&nbsp;
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    protected function top($_page_title) {
        ?>
        <div class="top">
            <table class="top">
                <?php
                if (isset($_SESSION['yel']['employer']) &&
                    !empty($_SESSION['yel']['employer']['id']) && 
                    !empty($_SESSION['yel']['employer']['sid']) && 
                    !empty($_SESSION['yel']['employer']['hash'])) {
                ?>
                <tr>
                    <td class="logo">
                        <a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/index.php">
                            <img name="logo" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/logos/top.jpg" />
                        </a>
                    </td>
                    <td><div class="page_title"><?php echo $_page_title ?></div></td>
                </tr>
                <?php
                } else {
                ?>
                <tr>
                    <td class="logo">
                        <a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/index.php">
                            <img name="logo" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/logos/top.jpg" />
                        </a>
                    </td>
                    <td><div class="page_title"><?php echo $_page_title ?></div></td>
                </tr>
                <?php
                } 
                ?>
            </table>
        </div>
        <?php
    }
    
    protected function top_search($_page_title) {
        // get the employers
        $criteria = array(
            'columns' => 'DISTINCT employers.id, employers.name', 
            'joins' => 'jobs ON employers.id = jobs.employer',
            'match' => "jobs.expire_on >= CURDATE() AND jobs.closed = 'N'", 
            'order' => 'employers.name ASC'
        );
        $employer = new Employer();
        $employers = $employer->find($criteria);
        if ($employers === false) {
            $employers = array();
        }
        
        // get the industries
        $industries = array();
        $main_industries = Industry::getMain(true);
        $i = 0;
        foreach ($main_industries as $main) {
            $industries[$i]['id'] = $main['id'];
            $industries[$i]['name'] = $main['industry'];
            $industries[$i]['job_count'] = $main['job_count'];
            $industries[$i]['is_main'] = true;
            $subs = Industry::getSubIndustriesOf($main['id'], true);
            foreach ($subs as $sub) {
                $i++;

                $industries[$i]['id'] = $sub['id'];
                $industries[$i]['name'] = $sub['industry'];
                $industries[$i]['job_count'] = $sub['job_count'];
                $industries[$i]['is_main'] = false;
            }
            $i++;
        }
        ?>
        <div class="top">
            <div class="top_logo">
                <a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/index.php">
                    <img name="logo" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/logos/top.jpg" />
                </a>
            </div>
            
            <div class="top_search">
                <form method="post" action="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/search.php" onSubmit="return verify_mini();">
                    <select id="mini_employer" name="employer">
                        <option value="0">Any Employer</option>
                        <option value="0" disabled>&nbsp;</option>
                        <?php
                        foreach ($employers as $emp) {
                        ?>
                        <option value="<?php echo $emp['id'] ?>">
                            <?php echo desanitize($emp['name']); ?>
                        </option>
                        <?php
                        }
                        ?>
                    </select>
                    &nbsp;
                    <select id="mini_industry" name="industry">
                        <option value="0">Any Specialization</option>
                        <option value="0" disabled>&nbsp;</option>
                        <?php
                        foreach ($industries as $industry) {
                            if ($industry['is_main']) {
                                echo '<option value="'. $industry['id']. '" class="main_industry">';
                                echo $industry['name'];
                            } else {
                                echo '<option value="'. $industry['id']. '">';
                                echo '&nbsp;&nbsp;&nbsp;&nbsp;'. $industry['name'];
                            }
                            
                            if ($industry['job_count'] > 0) {
                                echo '&nbsp;('. $industry['job_count']. ')';
                            }
                            echo '</option>'. "\n";
                        }
                        ?>
                    </select>
                    &nbsp;
                    <input type="radio" id="local" name="is_local" value="1" checked />
                    <label for="local">local jobs</label>
                    &nbsp;
                    <input type="radio" id="international" name="is_local" value="0" />
                    <label for="international">international jobs</label>
                    <br/>
                    <input type="text" name="keywords" id="mini_keywords" alt="Job title or keywords" value="" />
                    &nbsp;
                    <input id="mini_search_button" type="submit" value="Search Jobs">
                </form>
                
                <?php
                if (isset($_SESSION['yel']['member']) &&
                    !empty($_SESSION['yel']['member']['id']) && 
                    !empty($_SESSION['yel']['member']['sid']) && 
                    !empty($_SESSION['yel']['member']['hash'])) {
                ?>
                <div class="reminder">
                    <img class="reminder_warn" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/warning.jpg"/>
                    Always remember to click on the <strong>'I'm Employed'</strong> button in the <strong><a href="applications.php">Applications</a></strong> section whenever you are employed by an employer.
                </div>
                <?php
                }
                ?>
                
            </div>
        </div>
        <?php
    }
    
    protected function top_prs($page_title) {
        ?>
        <div class="top">
            <table class="top">
                <tr>
                    <td rowspan="3" class="logo">
                        <a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/index.php">
                            <img name="logo" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/logos/top.jpg" />
                        </a>
                    </td>
                    <td><div class="page_title"><?php echo desanitize($page_title) ?></div></td>
                </tr>
                <tr>
                    <td>
                        <form method="post" action="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/prs/search_resume.php" onSubmit="return prs_verify_mini();">
                            <div class="mini_search">
                                <span id="mini_industry_drop_down"></span>
                                &nbsp;
                                <input type="text" name="keywords" id="mini_keywords">
                                &nbsp;
                                <input id="mini_search_button" type="submit" value="Search Resumes">
                                &nbsp;
                                <!--input type="checkbox" name="use_exact" id="use_exact" value="1" /><label for="use_exact">Exact</label-->
                                <input type="radio" name="use_mode" id="or_mode" value="or" checked /><label for="or_mode">OR</label>
                                <input type="radio" name="use_mode" id="and_mode" value="and" /><label for="and_mode">AND</label>
                            </div>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    protected function menu($type, $page = '') {
        $style = 'style="background-color: #CCCCCC;"';
        
        if ($type == 'employer') {
            ?>
            <div class="menu">
                <ul class="menu">
                    <li <?php echo ($page == 'resumes') ? $style : '';?>><a class="menu" href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employers/resumes.php">Resumes</a></li>
                    <li <?php echo ($page == 'jobs') ? $style : '';?>><a class="menu" href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employers/jobs.php">Job Postings</a></li>
                    <li <?php echo ($page == 'invoices') ? $style : '';?>><a class="menu" href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employers/invoices.php">Invoices &amp; Receipts</a></li>
                    <li <?php echo ($page == 'profile') ? $style : '';?>><a class="menu" href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employers/profile.php">Profile</a></li>
                    <li><a class="menu" href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employers/logout.php">Logout</a></li>
                </ul>
            </div>
            <?php
        } else if ($type == 'member') {
            ?>
            <div class="menu">
                <ul class="menu">
                    <li <?php echo ($page == 'home') ? $style : '';?>><a class="menu" href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/members/home.php">Home</a></li>
                    <li <?php echo ($page == 'profile') ? $style : '';?>><a class="menu" href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/members/profile.php">Profile</a></li>
                    <li <?php echo ($page == 'resumes') ? $style : '';?>><a class="menu" href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/members/resumes.php">Resumes</a></li>
                    <li <?php echo ($page == 'job_applications') ? $style : '';?>><a class="menu" href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/members/job_applications.php">Applications</a></li>
                    <li><a class="menu" href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/members/logout.php">Logout</a></li>
                </ul>
            </div>
            <?php
        }
    }
    
    protected function menu_employee($_clearances, $page = '') {
        $style = 'style="border: 1px solid #0000FF;"';
        
        ?>
        <div class="menu">
            <ul class="menu">
                <li <?php echo ($page == 'home') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employees/home.php">Home</a></li>
                <li <?php echo ($page == 'profile') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employees/profile.php">My Profile</a></li>
        
        <?php
        if (Employee::has_clearances_for('employers', $_clearances)) {
        ?>
                <li <?php echo ($page == 'employers') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employees/employers.php">Employers</a></li>
        <?php
        }
        ?>
        
        <?php
        if (Employee::has_clearances_for('photos', $_clearances)) {
        ?>
                <li <?php echo ($page == 'photos') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employees/photos.php">Photos</a><span style="color: #FF0000; font-size: 7pt; font-weight: bold;" id="unapproved_photos_count"></span></li>
        <?php
        }
        ?>
        
        <?php
        if (Employee::has_clearances_for('invoices', $_clearances)) {
        ?>
                <li <?php echo ($page == 'invoices') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employees/invoices.php">Invoices &amp; Receipts</a></li>
        <?php
        }
        ?>

        <?php
        if (Employee::has_clearances_for('rewards', $_clearances)) {
        ?>
                <li <?php echo ($page == 'rewards') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employees/rewards.php">Rewards</a><span style="color: #FF0000; font-size: 7pt; font-weight: bold;" id="rewards_count"></span></li>
                <li <?php echo ($page == 'tokens') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employees/token_rewards.php">Bonuses</a><span style="color: #FF0000; font-size: 7pt; font-weight: bold;" id="tokens_count"></span></li>
                <li <?php echo ($page == 'recommender_tokens') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employees/recommender_tokens.php">Tokens</a><span style="color: #FF0000; font-size: 7pt; font-weight: bold;" id="tokens_count"></span></li>
        <?php
        }
        ?>
        
        <?php
        if (Employee::has_clearances_for('referrals', $_clearances)) {
        ?>
                <li <?php echo ($page == 'referrals') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employees/referrals.php">Verification</a></li>
        <?php
        }
        ?>
        
        <?php
        if (Employee::has_clearances_for('replacements', $_clearances)) {
        ?>
                <li <?php echo ($page == 'replacements') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employees/replacements.php">Replacements</a></li>
        <?php
        }
        ?>
        
        <?php
        if (Employee::has_clearances_for('admin_employers', $_clearances) && 
            !Employee::has_clearances_for('employers', $_clearances)) {
        ?>
                <li <?php echo ($page == 'admin_employers') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employees/admin_employers.php">Employers</a></li>
        <?php
        }
        ?>
        
        <?php
        if (Employee::has_clearances_for('members', $_clearances)) {
        ?>
                <li <?php echo ($page == 'members') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employees/admin_members.php">Members</a></li>
        <?php
        }
        ?>
        
        <?php
        if (Employee::has_clearances_for('employers', $_clearances)) {
        ?>
                <li <?php echo ($page == 'headhunters') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employees/headhunters.php">IRCs</a></li>
                <li <?php echo ($page == 'headhunter_testimonies') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employees/headhunter_testimonies.php">IRC Testmonies</a></li>
        <?php
        }
        ?>
        
        <?php
        if (Employee::has_clearances_for('admin_employers', $_clearances)) {
        ?>
                <li <?php echo ($page == 'jobs') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employees/jobs.php">Jobs</a></li>
                <li <?php echo ($page == 'applications') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employees/applications.php">Applications</a></li>
        <?php
        }
        ?>
        
        <?php
        //if (Employee::has_clearances_for('refer_requests', $_clearances)) {
        ?>
                <!--li <?php echo ($page == 'refer_requests') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employees/refer_requests.php">Refer Requests</a></li-->
        <?php
        //}
        ?>
        
                <li><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/employees/logout.php">Logout</a></li>
            </ul>
        </div>
        <?php
    }
    
    protected function menu_prs($_clearances, $page = '') {
        $style = 'style="border: 1px solid #0000FF;"';
        
        ?>
        <div class="menu">
            <ul class="menu">
                <li <?php echo ($page == 'home') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/prs/home.php">Home</a></li>
        
        <?php
        if (Employee::has_clearances_for('prs_resumes_privileged', $_clearances)) {
        ?>
                <li <?php echo ($page == 'resumes_privileged') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/prs/resumes_privileged.php">Privileged Candidates</a></li>
        <?php
        }
        ?>
        
        <?php
        if (Employee::has_clearances_for('prs_recommenders', $_clearances)) {
        ?>
                <li <?php echo ($page == 'recommenders') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/prs/recommenders.php">Recommenders</a></li>
        <?php
        }
        ?>
        
        <?php
        if (Employee::has_clearances_for('prs_resumes', $_clearances)) {
        ?>
                <li <?php echo ($page == 'resumes') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/prs/resumes.php">Other Resumes</a></li>
        <?php
        }
        ?>
        
        <?php
        if (Employee::has_clearances_for('prs_resumes', $_clearances)) {
        ?>
                <li <?php echo ($page == 'resumes_uploaded') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/prs/resumes_uploaded.php">Uploaded Resumes</a></li>
        <?php
        }
        ?>
        
        <?php
        if (Employee::has_clearances_for('prs_referrals', $_clearances)) {
        ?>
                <li <?php echo ($page == 'referrals') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/prs/referrals.php">Referrals</a></li>
        <?php
        }
        ?>
    
        <?php
        if (Employee::has_clearances_for('prs_mailing_lists', $_clearances)) {
        ?>
                <li <?php echo ($page == 'mailing_lists') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/prs/mailing_lists.php">Mailing Lists</a></li>
        <?php
        }
        ?>
        
                <li><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/prs/logout.php">Logout</a></li>
            </ul>
        </div>
        <?php
    }
    
    protected function support($_employer_id = '') {
        ?>
        <div class="support">
            Support: <span class="phone">+60 4 640 6363</span> or <span class="email">support@yellowelevator.com</span>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <?php
            $phone_number = '';
            $fax_number = '';
            $country_code = '';
            
            if (!empty($_employer_id)) {
                $employer = new Employer($_employer_id);
                $branch = $employer->getAssociatedBranch();
                
                $phone_number = $branch[0]['phone'];
                $fax_number = $branch[0]['fax'];
                $country_code = $branch[0]['country'];
            }
            
            if (empty($phone_number) || is_null($phone_number)) {
                $phone_number = '+60 4 640 6363';
            }
            
            if (empty($fax_number) || is_null($fax_number)) {
                $fax_number = '+60 4 640 6366';
            }
            
            if (empty($country_code) || is_null($country_code)) {
                $country_code = 'my';
            }
        ?>
            Billing: <span class="phone"><?php echo $phone_number ?></span> (fax: <span class="phone"><?php echo $fax_number ?></span>) or <span class="phone">billing.<?php echo strtolower($country_code); ?>@yellowelevator.com</span>
        </div>
        <?php
    } 
}
?>