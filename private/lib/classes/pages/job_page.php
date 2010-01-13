<?php
require_once dirname(__FILE__). "/../../utilities.php";

class JobPage extends Page {
    private $member = NULL;
    private $job_id = 0;
    private $criterias = NULL;
    private $is_employee_viewing = false;
    
    function __construct($_session = NULL, $_job_id, $_criterias = NULL) {
        if (!is_null($_session)) {
            if (!empty($_session['id']) && !empty($_session['sid'])) {
                $this->member = new Member($_session['id'], $_session['sid']);
            }
        }
        
        if ($_job_id > 0) {
            $this->job_id = $_job_id;
        }
        
        $this->criterias = $_criterias;
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_job_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/list_box.css">'. "\n";
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/job.css">'. "\n";
    }
    
    public function insert_job_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/list_box.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/job.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        if (!is_null($this->member)) {
            echo 'var id = "'. $this->member->id(). '";'. "\n";
            echo 'var country_code = "'. $this->member->get_country_code(). '";'. "\n";
        } else {
            echo 'var id = 0;'. "\n";
            echo 'var country_code = "'. $_SESSION['yel']['country_code']. '";'. "\n";
        }
        
        if (count($this->criterias) > 0 && !is_null($this->criterias)) {
            echo 'var industry = "'. $this->criterias['industry']. '";'. "\n";
            echo 'var keywords = "'. $this->criterias['keywords']. '";'. "\n";
        } else {
            echo 'var industry = "";'. "\n";
            echo 'var keywords = "";'. "\n";
        }
        
        echo '</script>'. "\n";
        echo '<script type="text/javascript" src="http://w.sharethis.com/button/sharethis.js#publisher=abd4d798-c853-4cba-ad91-8cad043044b8&amp;type=website&amp;style=rotate&amp;post_services=email%2Creddit%2Cfacebook%2Ctwitter%2Cmyspace%2Cdigg%2Csms%2Cwindows_live%2Cdelicious%2Cgoogle_bmarks%2Clinkedin%2Cblogger%2Cwordpress"></script>';
    }
    
    public function is_employee_viewing() {
        $this->is_employee_viewing = true;
    }
    
    private function generate_networks_list($_for_request_form = false) {
        $networks = $this->member->get_networks();
        
        if (!$_for_request_form) {
            echo '<select id="network_filter" name="network_filter" onChange="set_filter(false);">'. "\n";
        } else {
            echo '<select id="network_filter_request" name="network_filter_request" onChange="set_filter(true);">'. "\n";
        }
        
        echo '<option value="0" selected>all my networks</option>'. "\n";
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        
        foreach ($networks as $network) {
            echo '<option value="'. $network['id']. '">'. $network['industry']. '</option>'. "\n";
        }
        
        echo '</select>'. "\n";
    }
    
    private function get_job_info() {
        $criteria = array(
            'columns' => 'jobs.*, currencies.symbol AS currency_symbol, industries.industry AS full_industry, 
                          countries.country AS country_name, employers.name AS employer_name, 
                          employers.website_url AS employer_website_url, 
                          DATE_FORMAT(jobs.created_on, \'%e %b, %Y %k:%i:%s\') AS formatted_created_on, 
                          DATE_FORMAT(jobs.expire_on, \'%e %b, %Y %k:%i:%s\') AS formatted_expire_on, 
                          DATEDIFF(NOW(), jobs.expire_on) AS expired',
            'joins' => 'industries ON industries.id = jobs.industry, 
                        countries ON countries.country_code = jobs.country, 
                        employers ON employers.id = jobs.employer, 
                        currencies ON currencies.country_code = employers.country', 
            'match' => 'jobs.id = \''. $this->job_id. '\''
        );
        
        $jobs = Job::find($criteria);
        $job = array();
        
        if (count($jobs) <= 0 || is_null($jobs)) {
            return NULL;
        }
        
        foreach ($jobs[0] as $key => $value) {
            $job[$key] = $value;
        }
        
        $total_potential_reward = $job['potential_reward'];
        $potential_token_reward = $total_potential_reward * 0.05;
        $potential_reward = $total_potential_reward - $potential_token_reward;
        
        $job['description'] = htmlspecialchars_decode($job['description']);
        $job['potential_reward'] = number_format($potential_reward, 0, '.', ', ');
        $job['potential_token_reward'] = number_format($potential_token_reward, 0, '. ', ', ');;
        $job['salary'] = number_format($job['salary'], 0, '. ', ', ');
        $job['salary_end'] = number_format($job['salary_end'], 0, '. ', ', ');
        $job['state'] = ucwords($job['state']);
        
        return $job;
    }
    
    private function add_view_count() {
        $job = new Job($this->job_id);
        $job->add_view_count();
    }
    
    private function generateContactsDropdown() {
        if (!is_null($this->member)) {
            $contacts = $this->member->get_referees("referee_name ASC");
            echo '<select class="mini_field" id="qr_candidate_email_from_list" name="qr_candidate_email_from_list" onChange="toggle_new_contact_form();">'. "\n";
            echo '<option value="0" selected>Contacts</option>'. "\n";
            echo '<option value="0" disabled>&nbsp;</option>'. "\n";
            
            foreach ($contacts as $contact) {
                echo '<option value="'. $contact['referee']. '">'. $contact['referee_name']. '</option>'. "\n";
            }
            
            echo '</select>'. "\n";
        }
    }
    
    private function generateCountriesDropdown($_for_quick_upload = false, $_for_referrer = false) {
        $countries = Country::get_all();
        
        $prefix = ($_for_quick_upload) ? 'qu' : 'qr';
        $prefix .= ($_for_referrer) ? '_referrer' : '_candidate';
        echo '<select class="mini_field" id="'. $prefix. '_country" name="'. $prefix. '_country">'. "\n";
        echo '<option value="0" selected>Country of residence</option>'. "\n";
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        
        foreach ($countries as $country) {
            echo '<option value="'. $country['country_code']. '">'. $country['country']. '</option>'. "\n";
        }
        
        echo '</select>'. "\n";
    }
    
    private function generate_resumes_list() {
        if (!is_null($this->member)) {
            $query = "SELECT id, name FROM resumes 
                      WHERE member = '". $this->member->id(). "' AND 
                      private = 'N' AND 
                      deleted = 'N'";
            $mysqli = Database::connect();
            $result = $mysqli->query($query);
            if (!$result) {
                echo 'Sorry, you need to create at least a public viewable resume to proceed.';
                echo '<input type="hidden" name="resume" value="0" />';
                return;
            }
        
            echo '<select class="field" id="resume" name="resume">'. "\n";
            echo '<option value="0" selected>Please select a resume</option>'. "\n";
            echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        
            foreach ($result as $resume) {
                echo '<option value="'. $resume['id']. '">'. $resume['name']. '</option>'. "\n";
            }
        
            echo '</select>'. "\n";
        }
    }
    
    public function show($_from_search = false) {
        $this->begin();
        if (is_null($this->member)) {
            $this->top_search("Yellow Elevator&nbsp;&nbsp;<span style=\"color: #FC8503;\">Job Details</span>");
        } else {
            $this->top_search($this->member->get_name(). " - Job Details");
            $this->menu('member');
        }
        
        $job = $this->get_job_info();
        $error_message = '';
        if (count($job) <= 0 || is_null($job)) {
            $error_message = 'The job that you are looking for cannot be found.';
        } else if ($job === false) {
            $error_message = 'An error occured while loading the job details.';
        } 
        
        if (!$this->is_employee_viewing) {
            if ($job['expired'] >= 0 || $job['closed'] == 'Y') {
                $error_message = 'The job that you are looking for is no longer available.';
            }
        }
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <?php
        if (!empty($error_message)) {
            ?>
            <div style="text-align: center; font-size: 12pt; font-style: italic; padding-top: 100px; padding-bottom: 100px;">
                <?php echo $error_message ?>
            </div>
            <?php
            return false;
        }
        
        $this->add_view_count();
        ?>
        
        <div id="div_job_info">
            <input type="hidden" id="job_id" name="job_id" value="<?php echo $this->job_id; ?>" />
            <?php
            if ($_from_search) {
                ?><div class="back"><span class="back"><a class="no_link" onClick="window.location = root + '/search.php';">Back to Search Results</a></span></div><?php
            }
            ?>
            <table id="job_info" class="job_info">
                <tr>
                    <td colspan="3" class="title"><span id="job.title"><?php echo $job['title']; ?></span></td>
                </tr>
                <tr>
                    <td class="label">Specialization:</td>
                    <td class="field"><span id="job.industry"><?php echo $job['full_industry'] ?></span></td>
                    <td rowspan="9" class="title_reward">
                        <div style="width: 100%; background-color: #EEE; border: 5px solid #EEE; padding-top: 3px; padding-bottom: 3px;">
                            <span style="color: #28688A;">Referrer's <span style="font-weight: normal;">Potential Reward:</span></span><br/>
                            <div style="border: 2px solid #777; padding: 3px 3px 3px 3px; background-color: #FFF;">
                                <span id="job.currency_1"><?php echo $job['currency_symbol']; ?></span>$&nbsp;<span id="job.potential_reward"><?php echo $job['potential_reward']; ?></span>
                            </div>
                            <br/>
                            <span style="color: #28688A;">Candidate's <span style="font-weight: 500;">Bonus:</span></span><br/> 
                            <div style="border: 2px solid #777; padding: 3px 3px 3px 3px; background-color: #FFF;">
                                <span id="job.currency_2"><?php echo $job['currency_symbol']; ?></span>$&nbsp;<span id="job.potential_reward"><?php echo $job['potential_token_reward']; ?></span>
                            </div>
                        </div>
                        <br/>
                        <div style="font-weight: normal;">
                        <script language="javascript" type="text/javascript">
                            SHARETHIS.addEntry({
                                title: '<?php echo $job['title']; ?> on YellowElevator.com',
                                summary: 'Check this job, by <?php echo $job['employer_name'] ?>, out at yellowelevator.com!'
                            }, {button:true} );
                        </script>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="label">Employer:</td>
                    <td class="field">
                        <span id="job.employer">
                        <?php 
                            if (!is_null($job['employer_website_url'])) {
                                echo '<a href="'. $job['employer_website_url']. '" target="_new">'. $job['employer_name']. '</a>';
                            } else {
                                echo $job['employer_name'];
                            }
                        ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="label">Country:</td>
                    <td class="field"><span id="job.country"><?php echo $job['country_name'] ?></span></td>
                </tr>
                <tr>
                    <td class="label">State/Province/Area:</td>
                    <td class="field"><span id="job.state"><?php echo $job['state'] ?></span></td>
                </tr>
                <tr>
                    <td class="label">Monthly Salary:</td>
                    <td class="field">
                        <span id="job.currency"><?php echo $job['currency_symbol'] ?></span>
                        $&nbsp;<span id="job.salary"><?php echo $job['salary'] ?></span>
                        <?php
                            if ($job['salary_end'] > 0 && !is_null($job['salary_end'])) {
                        ?>
                        -&nbsp;<span id="job.salary_end"><?php echo $job['salary_end'] ?></span>
                        <?php
                            }
                        ?>
                        &nbsp;[<span id="job.salary_negotiable"><?php echo ($job['salary_negotiable'] == 'Y') ? 'Negotiable' : 'Not Negotiable'; ?></span>]</td>
                </tr>
                <tr>
                    <td class="label">Description:</td>
                    <td class="field"><div class="job_description"><span id="job.description"><?php echo $job['description'] ?></span></div></td>
                </tr>
                <tr>
                    <td class="label">&nbsp;</td>
                    <td class="field">&nbsp;</td>
                </tr>
                <tr>
                    <td class="label">Created On:</td>
                    <td class="field"><span id="job.created_on"><?php echo $job['formatted_created_on'] ?></span></td>
                </tr>
                <tr>
                    <td class="label">Expires On:</td>
                    <td class="field"><span id="job.expire_on"><?php echo $job['formatted_expire_on'] ?></span></td>
                </tr>
                <tr>
                    <td id="job_buttons" class="buttons" colspan="3">
                    <?php
                    if (!is_null($this->member)) {
                        ?>
                        <input type="image" src="../common/images/button_refer_now.gif" onClick="show_refer_options();" />&nbsp;<input type="image" src="../common/images/button_apply_now.gif" onClick="show_refer_me();" />&nbsp;<input type="image" src="../common/images/button_save_job.gif" onClick="save_job();" />
                        <?php
                    } else {
                        ?>
                        <a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/members?job=<?php echo $job['id']; ?>">Sign In</a> or <a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/members/sign_up.php">Sign Up</a> to 
                        <input type="image" src="../common/images/button_refer_now.gif" style="vertical-align: middle;" onClick="show_refer_options();" /> or <input type="image" src="../common/images/button_apply_now.gif" style="vertical-align: middle;" onClick="show_refer_me_ad();" /> or <input type="image" src="../common/images/button_save_job.gif" style="vertical-align: middle;" onClick="save_job();" />
                        <?php
                    }
                    ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_refer_options">
            <table class="refer_options">
                <tr>
                    <td>
                        <div style="text-align: center; height: 50px; margin-top: 15px;">
                            <span style="font-weight: bold;">
                                <a class="no_link" style="color: #1D3E6E;" onClick="close_refer_options(); show_refer_job();">Standard Referral</a>
                            </span>
                            &nbsp;
                            <img style="border: none; vertical-align: middle;" src="../common/images/icons/triangle.jpg" />
                        </div>
                        <div style="color: #666666;">
                            <p>
                                Send the details of this job to your contact(s) so that they can apply for it with you as the referrer.
                            </p><br/>
                            <p>
                                As the referrer, you will need to ensure that your contact has properly completed the application so that you can retrieve his/her resume from the <span style="font-weight: bold;">Referral Requests</span> section in your member account.
                            </p><br/>
                            <p>
                                You are then required to <span style="font-weight: bold">screen</span> his/her resume and write a short <span style="font-weight: bold">testimony</span> for him/her before submitting it to the employer.
                            </p>
                        </div>
                    </td>
                    <td>
                        <div style="text-align: center; height: 50px; margin-top: 15px;">
                             <span style="font-weight: bold;">
                                 <a class="no_link" style="color: #1D3E6E;" onClick="close_refer_options(); show_quick_refer_form();">Express Referral</a>
                             </span>
                             &nbsp;
                             <img style="border: none; vertical-align: middle;" src="../common/images/icons/triangle.jpg" />
                        </div>
                        <div style="color: #666666;">
                            <p>
                                If you have your contact's permission, as well as his/her resume, you can directly refer your contact to the job.
                            </p><br/>
                            <p>
                                Please ensure that your contact approves your referral by clicking <span style="font-weight: bold;">Accept</span> under the <span style="font-weight: bold;">Jobs Referred To Me</span> section in his/her account so that this referral can go through.
                            </p>
                        </div>
                    </td>
                    <td rowspan="2">
                        <div style="text-align: center; height: 50px; margin-top: 15px;">
                            <span style="font-weight: bold;">
                                <a class="no_link" style="color: #1D3E6E;" onClick="close_refer_options(); show_quick_upload_form();">Ask Yellow Elevator To Refer On Your Behalf</a>
                            </span>
                            &nbsp;
                            <img style="border: none; vertical-align: middle;" src="../common/images/icons/triangle.jpg" />
                        </div>
                        <div style="color: #666666;">
                            <p>
                                Drop off your contact's details and resume (if any) so that Yellow Elevator can refer him/her on your behalf to make it more convenient for you.
                            </p><br/>
                            <p>
                                Please note that if you use this method of referral, which requires us to do the screening on your behalf, you will only receive a portion of the referrer's reward if your contact is hired.
                            </p>
                        </div>
                    </td>
                </tr>
            <?php
            if (is_null($this->member)) {
            ?>
                <tr>
                    <td colspan="2">
                        <div style="color: #666666; text-align: center; vertical-align: middle;">
                            To make a <span style="font-weight: bold;">Standard</span> or an <span style="font-weight: bold;">Express</span>, please <a href="../members?job=<?php echo $job['id']; ?>">Sign In</a>.
                            <br/>
                            Not signed up yet? Click here to <a href="../members/sign_up.php">Sign Up</a>!
                        </div>
                    </td>
                </tr>
            <?php
            }
            ?>
            </table>
            <p class="button">
                <a class="no_link" onClick="close_refer_options();">Continue Browsing</a>
            </p>
        </div>
        
        <div id="div_refer_me_ad">
            <div style="color: #666666; padding: 15px 15px 15px 15px;">
                In order to apply for this job position, please <a href="../members?job=<?php echo $job['id']; ?>">Sign In</a> to submit your resume.
                <br/><br/>
                Not signed up yet? Click here to <a href="../members/sign_up.php">Sign Up</a> now!
            </div>
            <p class="button">
                <a class="no_link" onClick="close_refer_me_ad();">Continue Browsing</a>
            </p>
        </div>
        
        <div id="div_refer_form">
            <div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">
                <span style="color: #FC8503; font-weight: bold;">Step 1:</span> <span style="color: #666666;">Choose the contacts, whom you want to refer &nbsp;<span id="job_title" style="font-weight: bold;"></span>&nbsp; to, through either...</span>
            </div>
            <form onSubmit="return false;">
                <table class="refer_form">
                    <tr>
                        <td class="left">
                            <table class="candidate_form">
                                <tr>
                                    <td class="radio"><input type="radio" id="from_list" name="candidate_from" value="list" checked /></td>
                                    <td>
                                        <label for="from_list">your Contacts list</label><br/>
                                        <span class="filter">[ Show contacts from <?php (!is_null($this->member)) ? $this->generate_networks_list() : ''; ?> ]</span><br/>
                                        <div class="candidates" id="candidates" name="candidates"></div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="separator"></td>
                        <td class="right">
                            <table class="candidate_form">
                                <tr>
                                    <td class="radio"><input type="radio" id="from_email" name="candidate_from" value="email" /></td>
                                    <td>
                                        <label for="from_email">or enter their e-mail addresses, separated by spaces</label><br/>
                                        <p><textarea class="mini_field" id="email_addr" name="email_addr"></textarea></p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">
                    <span style="color: #FC8503; font-weight: bold;">Step 2:</span> <span style="color: #666666;">Inform your contact(s) and have them submit their resumes for your screening.</span>
                </div>
                <div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">
                    <span style="color: #FC8503; font-weight: bold;">Step 3:</span> <span style="color: #666666;">Complete the referral by going to <span style="font-style:bold;">Referral Requests</span>, screen their resumes, and refer them to their dream jobs.</span>
                </div>
                <p class="button"><input type="button" value="Cancel" onClick="close_refer_form();" />&nbsp;<input type="button" value="Refer Now" onClick="refer();" /></p>
            </form>
        </div>
        
        <div id="div_acknowledge_form">
            <form onSubmit="return false;">
                <p>
                    <span style="font-weight: bold;">To apply for the <span id="acknowledge_form_job_title" style="font-weight: bold; text-decoration: underline;"><?php echo $job['title']; ?></span> position...</span>
                </p>
                <p>
                    <span style="color: #FC8503; font-weight: bold;">Step 1:</span> <span style="color: #666666;">Please select the most current resume you wish to submit for your referrer's screening.</span>
                </p>
                <p><?php $this->generate_resumes_list(); ?></p>
                <p>
                    <span style="color: #FC8503; font-weight: bold;">Step 2:</span> <span style="color: #666666;">Apply through one of the following options.</span>
                    <table class="request_form">
                        <tr>
                            <td colspan="5">
                                <div style="color: #666666; margin: 5px 5px 5px 5px; padding: 10px 10px 10px 10px; border: 1px solid #CCCCCC; font-size: 9pt; font-style: italic;">
                                    Make a good first impression! Strengthen your application by nominating someone who has worked with you before to write a testimony for you. If you are hired, both of you will be rewarded!
                                </div>
                            </td>
                            <td class="separator">&nbsp;</td>
                            <td colspan="2">
                                <div style="color: #666666; margin: 5px 5px 5px 5px; padding: 10px 10px 10px 10px; border: 1px solid #CCCCCC; font-size: 9pt; font-style: italic;">
                                    Or, if you have no suitable referrers, let Yellow Elevator screen your resume and write a testimony for you.
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="radio"><input type="radio" id="referrer_contacts" name="referrer_option" value="contacts" checked /></td>
                            <td class="option">
                                <label for="referrer_contacts">by selecting a referrer from your Contacts</label><br/>
                                <span class="filter">[ Show contacts from <?php (!is_null($this->member)) ? $this->generate_networks_list(true) : ''; ?> ]</span><br/>
                                <div class="referrers" id="referrers" name="referrers"></div>
                            </td>
                            <td class="separator">&nbsp;</td>
                            <td class="radio"><input type="radio" id="referrer_others" name="referrer_option" value="others" /></td>
                            <td class="option">
                                <label for="referrer_others">by e-mail (enter their e-mail addresses, separated by spaces)</label><br/><br/>
                                <textarea id="referrer_emails" name="referrer_emails"></textarea>
                            </td>
                            <td class="separator">&nbsp;</td>
                            <td class="radio"><input type="radio" id="referrer_yel" name="referrer_option" value="yel" /></td>
                            <td class="option">
                                <label for="referrer_yel">by submitting your resume to Yellow Elevator so that we can review your resume and refer you if we find that you are suitable for the job position.</label>
                            </td>
                        </tr>
                    </table>
                </p>
                <p class="button"><input type="button" value="Cancel" onClick="close_refer_me();" />&nbsp;<input type="button" value="Apply Now" onClick="refer_me();" /></p>
            </form>
        </div>
        
        <div id="div_quick_refer_form">
            <div style="text-align: center; padding-top: 10px; padding-bottom: 10px; font-weight: bold;">
                To make an Express Referral of &nbsp;<span id="qr_job_title" style="text-decoration: underline;"></span>...
            </div>
            <form action="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/search_action.php" method="post" enctype="multipart/form-data" target="upload_target" onSubmit="return validate_quick_refer_form();">
                <input type="hidden" name="id" id="id" value="<?php echo (is_null($this->member)) ? '' : $this->member->id(); ?>" />
                <input type="hidden" name="qr_job_id" id="qr_job_id" value="<?php echo $this->job_id; ?>" />
                <input type="hidden" name="action" value="quick_refer" />
                <p id="qr_upload_progress" style="text-align: center;">
                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/progress/circle_big.gif" />
                </p>
                <table id="table_quick_refer_form" class="quick_refer_form">
                    <tr>
                        <td class="left" style="height: 50%;">
                            <div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">
                                <span style="color: #FC8503; font-weight: bold;">Step 1:</span> <span style="color: #666666;">Select the Candidate that you wish to refer for this job position.</span>
                            </div>
                            <div style="padding-bottom: 5px;">
                                <label for="qr_candidate_email_from_list">Your Contacts list:</label><br/>
                                <?php $this->generateContactsDropdown(); ?>
                            </div>
                            <div style="padding-top: 15px;">
                                Or, if your Candidate is not in your Contacts, enter one's details below:
                            </div>
                            <table class="qr_candidate_form" style="border-bottom: 1px dashed #666666; padding-bottom: 15px;">
                                <tr>
                                    <td class="label"><label for="qr_candidate_firstname">Firstname:</label></td>
                                    <td class="field">
                                        <input type="text" class="mini_field" id="qr_candidate_firstname" name="qr_candidate_firstname" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="qr_candidate_lastname">Lastname:</label></td>
                                    <td class="field">
                                        <input type="text" class="mini_field" id="qr_candidate_lastname" name="qr_candidate_lastname" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="qr_candidate_email">E-mail:</label></td>
                                    <td class="field">
                                        <input type="text" class="mini_field" id="qr_candidate_email" name="qr_candidate_email" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="qr_candidate_phone">Telephone:</label></td>
                                    <td class="field">
                                        <input type="text" class="mini_field" id="qr_candidate_phone" name="qr_candidate_phone" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="qr_candidate_country">Country:</label></td>
                                    <td class="field">
                                        <?php $this->generateCountriesDropdown(); ?>
                                    </td>
                                </tr>
                            </table>
                            <div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">
                                <span style="color: #FC8503; font-weight: bold;">Step 2:</span> <span style="color: #666666;">Attach the Candidate's resume.</span>
                            </div>
                            <p style="text-align: center;">
                                <input class="field" id="qr_my_file" name="qr_my_file" type="file" />
                                <br/><br/>
                                <div class="upload_note">Only HTML (*.html, *.htm), Text (*.txt), Portable Document Format (*.pdf), Rich Text Format (*.rtf) or MS Word document (*.doc) with the file size of less than 2MB are allowed.</div>
                            </p>
                        </td>
                        <td class="separator"></td>
                        <td class="right">
                            <div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">
                                <span style="color: #FC8503; font-weight: bold;">Step 3:</span> <span style="color: #666666;">Give a brief testimony about the Candidate by answering the following questions.</span>
                            </div>
                            <p>1. What experience and skill-sets do <span style="font-weight: bold;">the candidate</span> have that makes him/her suitable for the <span id="qr_job_title" style="font-weight: bold;"></span> position? (<span id="word_count_q1">0</span>/200 words)</p>
                            <p><textarea class="mini_field" id="testimony_answer_1" name="testimony_answer_1"></textarea></p>
                            <p>2. Does <span style="font-weight: bold;">the candidate</span> meet all the requirements of the <span id="qr_job_title" style="font-weight: bold;"></span> position?</p><div style="text-align: center;"><input type="radio" id="meet_req_yes" name="meet_req" value="yes" checked /><label for="meet_req_yes">Yes</label>&nbsp;&nbsp;&nbsp;<input type="radio" id="meet_req_no" name="meet_req" value="no" /><label for="meet_req_no">No</label></div><p>Briefly describe how they are met if you choose 'Yes'. (<span id="word_count_q2">0</span>/200 words)</p>
                            <p><textarea class="mini_field" id="testimony_answer_2" name="testimony_answer_2"></textarea></p>
                            <p>3. Briefly, describe <span style="font-weight: bold;">the candidate</span>'s personality and work attitude. (<span id="word_count_q3">0</span>/200 words)</p>
                            <p><textarea class="mini_field" id="testimony_answer_3" name="testimony_answer_3"></textarea></p>
                            <p>4. Additional recommendations for <span style="font-weight: bold;">the candidate</span> (if any) ? (<span id="word_count_q4">0</span>/200 words)</p>
                            <p><textarea class="mini_field" id="testimony_answer_4" name="testimony_answer_4"></textarea></p>
                        </td>
                    </tr>
                </table>
                <div style="text-align: center; font-size: 9pt; padding-top: 5px; font-style: italic;">
                    Your testimonial for this candidate can only be viewed by the employer.<br/>
                    You should already have an agreement with the candidate about referring him/her to this job.<br/>
                    You need to provide your testimony as truthful and honest as possible.<br/>
                </div>
                <p class="button"><input type="button" value="Cancel" onClick="close_quick_refer_form();" />&nbsp;<input type="submit" value="Refer Now" /></p>
            </form>
        </div>
        
        <div id="div_quick_upload_form">
            <form action="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/search_action.php" method="post" enctype="multipart/form-data" target="upload_target" onSubmit="return validate_quick_upload_form();">
                <input type="hidden" name="qu_job_id" id="qu_job_id" value="<?php echo $this->job_id; ?>" />
                <input type="hidden" name="action" value="quick_upload" />
                <p id="qu_upload_progress" style="text-align: center;">
                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/progress/circle_big.gif" />
                </p>
                <table id="table_quick_upload_form" class="quick_upload_form">
                    <tr colspan="3">
                        <div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">
                            <span style="color: #FC8503; font-weight: bold;">Step 1:</span> <span style="color: #666666;">Please fill in your details along with the details of your contact whom you are referring for the &nbsp;<span id="qu_job_title" style="font-weight: bold;"></span>&nbsp; position.</span>
                        </div>
                    </tr>
                    <tr>
                        <td class="left">
                            <div style="font-size: 12pt; font-weight: bold; padding-bottom: 15px;">Contact's Details</div>
                            <table class="qu_candidate_form">
                                <tr>
                                    <td class="label"><label for="qu_candidate_firstname">Firstname:</label></td>
                                    <td class="field">
                                        <input type="text" class="mini_field" id="qu_candidate_firstname" name="qu_candidate_firstname" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="qu_candidate_lastname">Lastname:</label></td>
                                    <td class="field">
                                        <input type="text" class="mini_field" id="qu_candidate_lastname" name="qu_candidate_lastname" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="qu_candidate_email">Email:</label></td>
                                    <td class="field">
                                        <input type="text" class="mini_field" id="qu_candidate_email" name="qu_candidate_email" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="qu_candidate_phone">Telephone:</label></td>
                                    <td class="field">
                                        <input type="text" class="mini_field" id="qu_candidate_phone" name="qu_candidate_phone" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="qu_candidate_country">Country:</label></td>
                                    <td class="field">
                                        <?php $this->generateCountriesDropdown(true); ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="separator"></td>
                        <td class="right">
                            <div style="font-size: 12pt; font-weight: bold; padding-bottom: 15px;">Your Details</div>
                            <table class="qu_candidate_form">
                                <tr>
                                    <td class="label"><label for="qu_referrer_firstname">Firstname:</label></td>
                                    <td class="field">
                                        <input type="text" class="mini_field" id="qu_referrer_firstname" name="qu_referrer_firstname" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="qu_referrer_lastname">Lastname:</label></td>
                                    <td class="field">
                                        <input type="text" class="mini_field" id="qu_referrer_lastname" name="qu_referrer_lastname" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="qu_referrer_email">Email:</label></td>
                                    <td class="field">
                                        <input type="text" class="mini_field" id="qu_referrer_email" name="qu_referrer_email" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="qu_referrer_phone">Telephone:</label></td>
                                    <td class="field">
                                        <input type="text" class="mini_field" id="qu_referrer_phone" name="qu_referrer_phone" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="qu_referrer_country">Country:</label></td>
                                    <td class="field">
                                        <?php $this->generateCountriesDropdown(true, true); ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">
                                <span style="color: #FC8503; font-weight: bold;">Step 2 (Optional): </span> <span style="color: #666666;">YPlease upload your Contact's resume if you have it.</span>
                            </div>
                            <p style="text-align: center;">
                                <input class="field" id="qu_my_file" name="qu_my_file" type="file" />
                                <br/><br/>
                                <div class="upload_note">Only HTML (*.html, *.htm), Text (*.txt), Portable Document Format (*.pdf), Rich Text Format (*.rtf) or MS Word document (*.doc) with the file size of less than 2MB are allowed.</div>
                            </p>
                        </td>
                    </tr>
                </table>
                <p class="button"><input type="button" value="Cancel" onClick="close_quick_upload_form();" />&nbsp;<input type="submit" value="Refer" /></p>
            </form>
        </div>
        
        <iframe id="upload_target" name="upload_target" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/blank.php" style="width:0px;height:0px;border:none;"></iframe>
        <?php
    }
}
?>