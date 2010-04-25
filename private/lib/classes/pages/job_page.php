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
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/job.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts($_show_popup = '') {
        echo '<script type="text/javascript">'. "\n";
        echo 'var job_id = "'. $this->job_id. '";'. "\n";
        echo 'var show_popup = "'. $_show_popup. '";'. "\n";
        
        if (!is_null($this->member)) {
            echo 'var id = "'. $this->member->getId(). '";'. "\n";
            echo 'var country_code = "'. $this->member->getCountry(). '";'. "\n";
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
    }
    
    public function is_employee_viewing() {
        $this->is_employee_viewing = true;
    }
    
    private function get_job_info() {
        $criteria = array(
            'columns' => 'jobs.*, industries.industry AS full_industry, 
                          countries.country AS country_name, employers.name AS employer_name, 
                          employers.website_url AS employer_website_url, branches.currency, 
                          DATE_FORMAT(jobs.expire_on, \'%e %b, %Y\') AS formatted_expire_on, 
                          DATEDIFF(NOW(), jobs.expire_on) AS expired',
            'joins' => 'industries ON industries.id = jobs.industry, 
                        countries ON countries.country_code = jobs.country, 
                        employers ON employers.id = jobs.employer, 
                        employees ON employees.id = employers.registered_by, 
                        branches ON branches.id = employees.branch', 
            'match' => 'jobs.id = \''. $this->job_id. '\''
        );
        
        $job = new Job();
        $result = $job->find($criteria);
        $job = array();
        
        if (count($result) <= 0 || is_null($result)) {
            return NULL;
        }
        
        $job = $result[0];
        $total_potential_reward = $job['potential_reward'];
        $potential_token_reward = $total_potential_reward * 0.05;
        $potential_reward = $total_potential_reward - $potential_token_reward;
        
        $job['description'] = htmlspecialchars_decode($job['description']);
        if (!is_null($job['alternate_employer']) && !empty($job['alternate_employer'])) {
            $job['employer_name'] = $job['alternate_employer'];
        }
        
        $job['potential_reward'] = number_format($potential_reward, 0, '.', ', ');
        $job['potential_token_reward'] = number_format($potential_token_reward, 0, '. ', ', ');;
        $job['salary'] = number_format($job['salary'], 0, '. ', ', ');
        $job['salary_end'] = number_format($job['salary_end'], 0, '. ', ', ');
        $job['state'] = ucwords($job['state']);
        
        return $job;
    }
    
    private function add_view_count() {
        $job = new Job($this->job_id);
        $job->incrementViewCount();
    }
    
    private function generateCountriesDropdown($_for_quick_upload = false, $_for_referrer = false) {
        $countries = Country::getAll();
        
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
        $this->top_search("Job Details");
        if (!is_null($this->member)) {
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
                // $error_message = 'The job that you are looking for is no longer available.';
            }
        }
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div class="job_details">
        <?php
        if (!empty($error_message)) {
            ?>
            <div class="empty_results">
                <?php echo $error_message ?>
            </div>
            <?php
            return false;
        } else {
            $this->add_view_count();
            
            ?>
            <div class="job_title"><?php echo $job['title'] ?></div>
            <div class="employer_country">
                <?php echo $job['employer_name'] ?>
                &nbsp;
                <span class="country">
                <?php 
                    echo (!is_null($job['state']) && !empty($job['state'])) ? $job['state']. ', ' : '';
                    echo $job['country_name'] ?>
                </span>
            </div>
            <table class="details_table">
                <tr>
                    <td class="description">
                        <?php echo $job['description']; ?>
                    </td>
                    <td class="rest">
                        <div class="industry">
                            <span class="label">Specialization:</span><br/><?php echo $job['full_industry'] ?>
                        </div>
                        <div class="salary">
                            <span class="label">Monthly Salary:</span><br/>
                            <?php 
                                echo $job['currency']. '$ '. $job['salary'];
                                if ($job['salary_end'] > 0) {
                                    echo ' to '. $job['salary_end'];
                                }
                                
                                if ($job['salary_negotiable'] == 'Y') {
                                ?><br/><span class="negotiable">Negotiable</span><?php
                                }
                            ?>
                        </div>
                        <div class="expires_on">
                            <span class="label">Expires On:</span><br/><?php echo $job['formatted_expire_on'] ?>
                        </div>
                        <div class="rewards_section">
                            <div class="reward_label">Referrer's Potential Reward:</div>
                            <div class="reward_amount">
                                <?php echo $job['currency']. '$ '. $job['potential_reward'] ?>
                            </div>
                            <div class="reward_label">Candidate's Bonus:</div>
                            <div class="reward_amount">
                                <?php echo $job['currency']. '$ '. $job['potential_token_reward'] ?>
                            </div>
                        </div>
                        <div class="actions">
                            <div class="action_item">
                                <a class="no_link" onClick="show_refer_popup();">Refer Now</a>
                            </div>
                            <div class="action_item">
                                <a class="no_link" onClick="show_apply_popup();">Apply Now</a>
                            </div>
                            <?php
                            if ($_from_search) {
                            ?>
                            <div class="action_item">
                                <a href="../search.php">Back to Searched Jobs</a>
                            </div>
                            <?php
                            }
                            ?>
                        </div>
                    </td>
                </tr>
            </table>
            <?php
        }
        ?>
        </div>
        
        <!-- popup window goes here -->
        <div id="refer_window" class="popup_window">
            <div class="popup_window_title">Refer a Job</div>
            <div id="refer_progress">
                Please wait while your request is being processed... <br/><br/>
                <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/progress/circle_big.gif" />
            </div>
            <form id="refer_form" action="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/refer_action.php" method="post" enctype="multipart/form-data" target="upload_target">
                <input type="hidden" name="job_id" value="<?php echo $this->job_id ?>" />
                <table class="refer_form">
                    <tr>
                        <td class="referrer">
                            <table class="referrer">
                                <tr>
                                    <td colspan="2" class="title">Your Contact Details</td>
                                </tr>
                            <?php
                            if (!is_null($this->member)) {
                            ?>
                                <tr>
                                    <td class="label"><label for="referrer_email">E-mail Address:</label></td>
                                    <td>
                                        <input type="hidden" name="referrer_email" id="referrer_email" value="<?php echo $this->member->getId(); ?>" />
                                        <?php echo $this->member->getId(); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="referrer_phone">Telephone:</label></td>
                                    <td><input type="text" class="field" name="referrer_phone" id="referrer_phone" value="<?php echo  $this->member->getPhone(); ?>" /></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="referrer_name">Name:</label></td>
                                    <td>
                                        <input type="hidden" name="referrer_name" id="referrer_name" value="<?php echo  $this->member->getFullName(); ?>" />
                                        <?php echo  $this->member->getFullName(); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="note">Tip: To change your contact details, please update your Profile in the Profile page.</div>
                                    </td>
                                </tr>
                            <?php
                            } else {
                            ?>
                                <tr>
                                    <td class="label"><label for="referrer_email">E-mail Address:</label></td>
                                    <td><input type="text" class="field" name="referrer_email" id="referrer_email" value="" /></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="referrer_phone">Telephone:</label></td>
                                    <td><input type="text" class="field" name="referrer_phone" id="referrer_phone" value="" /></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="referrer_name">Name:</label></td>
                                    <td><input type="text" class="field" name="referrer_name" id="referrer_name" value="" /></td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="note">Tip: Sign up to have these fields pre-filled.</div>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                            </table>
                        </td>
                        <td class="candidate">
                            <table class="candidate">
                                <tr>
                                    <td colspan="2" class="title">Candidate's Contact Details &amp; Resume</td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="candidate_email">E-mail Address:</label></td>
                                    <td><input type="text" class="field" name="candidate_email" id="candidate_email" value="" /></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="candidate_phone">Telephone:</label></td>
                                    <td><input type="text" class="field" name="candidate_phone" id="candidate_phone" value="" /></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="candidate_name">Name:</label></td>
                                    <td><input type="text" class="field" name="candidate_name" id="candidate_name" value="" /></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="candidate_resume">Resume:</label></td>
                                    <td><input type="file" class="field" name="candidate_resume" id="candidate_resume" value="" /></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </form>
            <div class="popup_window_buttons_bar">
                 <input type="button" value="Refer Now" onClick="close_refer_popup(true);" />
                 <input type="button" value="Cancel" onClick="close_refer_popup(false);" />
            </div>
        </div>
        
        <div id="apply_window" class="popup_window">
            <div class="popup_window_title">Apply for a Job</div>
            <div id="apply_progress">
                Please wait while your request is being processed... <br/><br/>
                <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/progress/circle_big.gif" />
            </div>
            <form id="apply_form" action="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/apply_action.php" method="post" enctype="multipart/form-data" target="upload_target">
                <input type="hidden" name="job_id" value="<?php echo $this->job_id ?>" />
                <table class="apply_form">
                <?php
                if (!is_null($this->member)) {
                ?>
                    <tr>
                        <td class="label"><label for="apply_email">E-mail Address:</label></td>
                        <td>
                            <input type="hidden" name="apply_email" id="apply_email" value="<?php echo $this->member->getId(); ?>" />
                            <?php echo $this->member->getId(); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="apply_phone">Telephone:</label></td>
                        <td><input type="text" class="field" name="apply_phone" id="apply_phone" value="<?php echo  $this->member->getPhone(); ?>" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="apply_name">Name:</label></td>
                        <td>
                            <input type="hidden" name="apply_name" id="apply_name" value="<?php echo  $this->member->getFullName(); ?>" />
                            <?php echo  $this->member->getFullName(); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="apply_resume">Resume:</label></td>
                        <td>
                            Choose: 
                        <?php
                        if ($this->member->hasResume()) {
                        ?>
                            <select id="existing_resume" name="existing_resume" onChange="toggle_resume_upload();">
                                <option value="0" selected>from one of your pre-uploads</option>
                                <option value="0" disabled>&nbsp;</option>
                        <?php
                                $criteria = array(
                                    'columns' => 'id, file_name', 
                                    'match' => "member = '". $this->member->getId(). "'"
                                );

                                $resume = new Resume();
                                $result = $resume->find($criteria);
                                foreach ($result as $row) {
                        ?>
                                <option value="<?php echo $row['id'] ?>"><?php echo $row['file_name'] ?></option>
                        <?php
                                }
                        ?>
                            </select>
                        <?php
                        }
                        ?>
                            or<br/>
                            Upload New:
                            <input type="file" name="apply_resume" id="apply_resume" value="" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="note">Tip: To change your contact details and update your list of resumes, please update your Profile in the Profile page and upload your resume in Resumes page.</div>
                        </td>
                    </tr>
                <?php
                } else {
                ?>
                    <tr>
                        <td class="label"><label for="apply_email">E-mail Address:</label></td>
                        <td><input type="text" class="field" name="apply_email" id="apply_email" value="" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="apply_phone">Telephone:</label></td>
                        <td><input type="text" class="field" name="apply_phone" id="apply_phone" value="" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="apply_name">Name:</label></td>
                        <td><input type="text" class="field" name="apply_name" id="apply_name" value="" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="apply_resume">Resume:</label></td>
                        <td><input type="file" name="apply_resume" id="apply_resume" value="" /></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="note">Tip: Sign up to have these fields pre-filled.</div>
                        </td>
                    </tr>
                <?php
                }
                ?>
                </table>                
            </form>
            <div class="popup_window_buttons_bar">
                 <input type="button" value="Apply Now" onClick="close_apply_popup(true);" />
                 <input type="button" value="Cancel" onClick="close_apply_popup(false);" />
            </div>
        </div>
        
        <!-- upload target -->
        <iframe id="upload_target" name="upload_target" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/blank.php" style="width:0%;height:0px;border:none;"></iframe>
        <?php
    }
}
?>