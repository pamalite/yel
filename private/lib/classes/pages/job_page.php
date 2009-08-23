<?php
require_once dirname(__FILE__). "/../../utilities.php";

class JobPage extends Page {
    private $member = NULL;
    private $job_id = 0;
    private $criterias = NULL;
    
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
        echo '<script type="text/javascript" src="http://w.sharethis.com/button/sharethis.js#publisher=abd4d798-c853-4cba-ad91-8cad043044b8&amp;type=website&amp;embeds=true&amp;style=rotate"></script>';
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

        $job['description'] = htmlspecialchars_decode($job['description']);
        $job['potential_reward'] = number_format($job['potential_reward'], 2, '. ', ', ');
        $job['salary'] = number_format($job['salary'], 2, '. ', ', ');
        $job['salary_end'] = number_format($job['salary_end'], 2, '. ', ', ');
        $job['state'] = ucwords($job['state']);
        
        return $job;
    }
    
    private function add_view_count() {
        $job = new Job($this->job_id);
        $job->add_view_count();
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
            $this->top_search("Yellow Elevator - Job Details");
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
        
        if ($job['expired'] >= 0 || $job['closed'] == 'Y') {
            $error_message = 'The job that you are looking for is no longer available.';
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
                    <td colspan="2" class="title"><span id="job.title"><?php echo $job['title']; ?></span></td>
                </tr>
                <tr>
                    <td colspan="2" class="title_reward">Potential Reward of <span id="job.currency_1"><?php echo $job['currency_symbol']; ?></span>$&nbsp;<span id="job.potential_reward"><?php echo $job['potential_reward']; ?></span></td>
                </tr>
                <tr>
                    <td class="label">Industry:</td>
                    <td class="field"><span id="job.industry"><?php echo $job['full_industry'] ?></span></td>
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
                    <td class="label">&nbsp;</td>
                    <td class="field">
                        <script type="text/javascript">
                            url = '<?php echo 'http://'. $_SERVER['SERVER_NAME']. '/job/'. $this->job_id ?>';
                        </script>
                        <script type="text/javascript" src="http://www.retweet.com/static/retweets.js"></script>
                    </td>
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
                    <td id="job_buttons" class="buttons" colspan="2">
                    <?php
                    if (!is_null($this->member)) {
                        ?>
                        <input class="button" type="button" id="save_job" name="save_job" value="Save Job" onClick="save_job();" />&nbsp;<input class="button" type="button" id="refer_job" name="refer_job" value="Refer Now" onClick="show_refer_job();" />&nbsp;<input class="button" type="button" id="refer_me" name="refer_me" value="Request for a Referral" onClick="show_refer_me();" />
                        <?php
                    } else {
                        ?>
                        Sign In or Sign Up to <input class="button" type="button" id="save_job" name="save_job" value="Save Job" onClick="save_job();" /> or <input class="button" type="button" id="refer_job" name="refer_job" value="Refer Now" onClick="show_refer_job();" /> or <input class="button" type="button" id="refer_me" name="refer_me" value="Request for a Referral" onClick="show_refer_me();" />
                        <?php
                    }
                    ?>
                    </td>
                </tr>
                <tr>
                    <td id="job_buttons" class="buttons" colspan="2">
                        You can&nbsp;
                        <script language="javascript" type="text/javascript">
                            SHARETHIS.addEntry({
                                title: '<?php echo $job['title']; ?> on YellowElevator.com',
                                summary: 'Check this job, by <?php echo $job['employer_name'] ?>, out at yellowelevator.com!'
                            }, {button:true} );
                        </script>
                        &nbsp;with your friends.
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_refer_form">
            <form onSubmit="retun false;">
                <table class="refer_form">
                    <tr>
                        <td class="left">
                            <p>You are about to refer the job position,&nbsp;<span id="job_title" style="font-weight: bold;"></span>&nbsp;to one of your contacts. Please select a contact...</p>
                            <table class="candidate_form">
                                <tr>
                                    <td class="radio"><input type="radio" id="from_list" name="candidate_from" value="list" checked /></td>
                                    <td>
                                        <label for="from_list">from your Contacts</label><br/>
                                        <span class="filter">[ Show candidates from <?php (!is_null($this->member)) ? $this->generate_networks_list() : ''; ?> ]</span><br/>
                                        <div class="candidates" id="candidates" name="candidates"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="radio"><input type="radio" id="from_email" name="candidate_from" value="email" /></td>
                                    <td>
                                        <label for="from_email">or enter e-mail address of a new contact</label><br/>
                                        <input type="text" class="mini_field" id="email_addr" name="email_addr" />
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="separator"></td>
                        <td class="right">
                            <p>1. How long have you known and how do you know <span id="candidate_name" style="font-weight: bold;">this contact</span>? (<span id="word_count_q1">0</span>/50 words)</p>
                            <p><textarea class="mini_field" id="testimony_answer_1"></textarea></p>
                            <p>2. What makes <span id="candidate_name" style="font-weight: bold;">this contact</span> suitable for <span id="job_title" style="font-weight: bold;">the job</span>?  (<span id="word_count_q2">0</span>/50 words)</p>
                            <p><textarea class="mini_field" id="testimony_answer_2"></textarea></p>
                            <p>3. Briefly, what are the areas of improvements for <span id="candidate_name" style="font-weight: bold;">this contact</span>?  (<span id="word_count_q3">0</span>/50 words)</p>
                            <p><textarea class="mini_field" id="testimony_answer_3"></textarea></p>
                        </td>
                    </tr>
                </table>
                <div style="padding: 2px 2px 2px 2px; text-align: center; font-style: italic;">
                    Your testimonial for this contact can only be viewed by the employer.
                </div>
                <p class="button"><input type="button" value="Cancel" onClick="close_refer_form();" />&nbsp;<input type="button" value="Refer Now" onClick="refer();" /></p>
            </form>
        </div>
        
        <div id="div_acknowledge_form">
            <form onSubmit="retun false;">
                <p>
                    You are about to make a request for a referral to the <span id="acknowledge_form_job_title" style="font-weight: bold;"><?php echo $job['title']; ?></span> position.
                </p>
                <p>
                    Please select the resume you wish to submit, as well as, make a request to your desired referrer.
                </p>
                <p><?php $this->generate_resumes_list(); ?></p>
                <p>
                    You may make this request to your desired referrer.
                    <table class="request_form">
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
                <p class="button"><input type="button" value="Cancel" onClick="close_refer_me();" />&nbsp;<input type="button" value="Submit Request" onClick="refer_me();" /></p>
            </form>
        </div>
        <?php
    }
}
?>