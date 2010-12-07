<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). "/../htmltable.php";
require_once dirname(__FILE__). "/../../../config/job_profile.inc";

class EmployeeMemberPage extends Page {
    private $employee = NULL;
    private $member = NULL;
    private $is_new = false;
    private $current_page = 'profile';
    private $error_message = '';
    private $selected_jobs = array();
    
    function __construct($_session, $_member_id = '') {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->member = new Member($_member_id);
    }
    
    public function new_member($_is_new) {
        $this->is_new = $_is_new;
    }
    
    public function set_selected_jobs($_jobs_str) {
        $this->selected_jobs = explode(',', $_jobs_str);
    }
    
    public function set_page($_page) {
        $this->current_page = $_page;
    }
    
    public function set_error($_error) {
        switch ($_error) {
            case '1':
                $this->error_message = 'An error occured when trying to create a new resume placeholder.';
                break;
            case '2':
                $this->error_message = 'An error occured when trying to update your resume.';
                break;
            case '3':
                $this->error_message = 'An error occured when uploading your resume. \\n\\nPlease make sure that the file you are uploading is listed in the resume upload window.';
                break;
            default:
                $this->error_message = '';
        }
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_member_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/list_box.css">'. "\n";
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_member.css">'. "\n";
    }
    
    public function insert_employee_member_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/flextable.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/list_box.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_member.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->getId(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->getUserId(). '";'. "\n";
        echo 'var current_page = "'. $this->current_page. '";'. "\n";
        
        if (!empty($this->error_message)) {
            echo "alert(\"". $this->error_message. "\");\n";
        }
        
        if ($this->is_new) {
            echo 'var member_id = "0";'. "\n";
        } else {
            echo 'var member_id = "'. $this->member->getId(). '";'. "\n";
        }
        
        echo '</script>'. "\n";
    }
    
    private function remove_br($_str) {
        return str_replace('<br/>', "\n", $_str);
    }
    
    private function generate_currencies($_id, $_selected='') {
        $currencies = $GLOBALS['currencies'];
        
        echo '<select id="'. $_id. '" name="'. $_id. '">'. "\n";
        
        foreach ($currencies as $i=>$currency) {
            if (empty($_selected) && $i == 0) {
                echo '<option value="'. $currency. '" selected>'. $currency. '</option>'. "\n";
                continue;
            }
             
            if ($currency == $_selected) {
                echo '<option value="'. $currency. '" selected>'. $currency. '</option>'. "\n";
            } else {
                echo '<option value="'. $currency. '">'. $currency. '</option>'. "\n";
            }
        }
        
        echo '</select>';
    }
    
    private function generate_countries($_selected, $_name = 'country') {
        $criteria = array(
            'columns' => "country_code, country", 
            'order' => "country"
        );
        $countries = Country::find($criteria);
        
        echo '<select class="field" id="'. $_name. '" name="'. $_name. '">'. "\n";
        
        if (empty($_selected) || is_null($_selected)) {
            echo '<option value="" selected>Select a Country</option>'. "\n";
            echo '<option value="" disabled>&nbsp;</option>'. "\n";
        }
        
        foreach ($countries as $country) {
            if ($country['country_code'] != $_selected) {
                echo '<option value="'. $country['country_code']. '">'. $country['country']. '</option>'. "\n";
            } else {
                echo '<option value="'. $country['country_code']. '" selected>'. $country['country']. '</option>'. "\n";
            }
        }
        
        echo '</select>'. "\n";
    }
    
    private function generate_industries($_selected, $_name = 'industry') {
        $industries = array();
        $main_industries = Industry::getMain();
        $i = 0;
        foreach ($main_industries as $main) {
            $industries[$i]['id'] = $main['id'];
            $industries[$i]['name'] = $main['industry'];
            $industries[$i]['is_main'] = true;
            $subs = Industry::getSubIndustriesOf($main['id']);
            foreach ($subs as $sub) {
                $i++;

                $industries[$i]['id'] = $sub['id'];
                $industries[$i]['name'] = $sub['industry'];
                $industries[$i]['is_main'] = false;
            }
            $i++;
        }
        
        echo '<select class="field" id="'. $_name. '" name="'. $_name. '">'. "\n";
        
        if (empty($_selected) || is_null($_selected)) {
            echo '<option value="0" selected>Select a Specialization</option>'. "\n";
            echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        }
        
        foreach ($industries as $industry) {
            $selected = '';
            if ($industry['id'] == $_selected) {
                $selected = 'selected';
            }
            
            if ($industry['is_main']) {
                echo '<option value="'. $industry['id']. '" class="main_industry" '. $selected. '>';
                echo $industry['name'];
            } else {
                echo '<option value="'. $industry['id']. '"'. $selected. '>';
                echo '&nbsp;&nbsp;&nbsp;&nbsp;'. $industry['name'];
            }

            echo '</option>'. "\n";
        }
        
        echo '</select>'. "\n";
    }
    
    private function generate_employer_description($_id, $_selected) {
        $descs = $GLOBALS['emp_descs'];
        
        echo '<select class="field" id="'. $_id. '" name="'. $_id. '">'. "\n";
        if (empty($_selected) || is_null($_selected) || $_selected < 0) {
            echo '<option value="0" selected>Please select one</option>'. "\n";    
        } else {
            echo '<option value="0">Please select One</option>'. "\n";
        }
        
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        foreach ($descs as $i=>$desc) {
            if ($i != $_selected) {
                echo '<option value="'. $i. '">'. $desc. '</option>'. "\n";
            } else {
                echo '<option value="'. $i. '" selected>'. $desc. '</option>'. "\n";
            }
        }
        
        echo '</select>'. "\n";
    }
    
    private function get_resumes() {
        $resume = new Resume();
        
        $criteria = array(
            'columns' => "id, file_name, is_yel_uploaded, 
                          DATE_FORMAT(modified_on, '%e %b, %Y') AS formatted_modified_on", 
            'match' => "member = '". $this->member->getId(). "' AND deleted = 'N'", 
            'order' => "modified_on DESC"
        );
        
        return $resume->find($criteria);
    }
    
    private function get_potential_referrers() {
        $criteria = array(
            'columns' => "email_addr, CONCAT(lastname, ', ', firstname) AS member_name", 
            'match' => "email_addr <> '". $this->member->getId(). "' AND 
                        email_addr NOT IN (
                            SELECT DISTINCT member FROM member_referees 
                            WHERE referee = '". $this->member->getId(). "' 
                        ) AND 
                        email_addr NOT LIKE 'team.%@yellowelevator.com' AND 
                        email_addr <> 'initial@yellowelevator.com'", 
            'order' => "member_name"
        );
        
        return $this->member->find($criteria);
    }
    
    private function get_potential_candidates() {
        $criteria = array(
            'columns' => "email_addr, CONCAT(lastname, ', ', firstname) AS member_name", 
            'match' => "email_addr <> '". $this->member->getId(). "' AND 
                        email_addr NOT IN (
                            SELECT DISTINCT referee FROM member_referees 
                            WHERE member = '". $this->member->getId(). "' 
                        ) AND 
                        email_addr NOT LIKE 'team.%@yellowelevator.com' AND 
                        email_addr <> 'initial@yellowelevator.com'", 
            'order' => "member_name"
        );
        
        return $this->member->find($criteria);
    }
    
    private function get_referrers() {
        $criteria = array(
            'columns' => "member_referees.member AS id, 
                          CONCAT(members.lastname, ', ', members.firstname) AS member_name", 
            'joins' => "member_referees ON members.email_addr = member_referees.member", 
            'match' => "member_referees.referee = '". $this->member->getId(). "'",
            'order' => "member_name"
        );
        
        return $this->member->find($criteria);
    }
    
    private function get_applications() {
        $criteria = array(
            'columns' => "referrals.id, referrals.member AS referrer, 
                          jobs.title AS job, jobs.id AS job_id, 
                          employers.name AS employer, employers.id AS employer_id, 
                          referrals.resume AS resume_id, resumes.file_name, 
                          CONCAT(members.lastname, ', ', members.firstname) AS referrer_name, 
                          DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
                          DATE_FORMAT(referrals.employer_agreed_terms_on, '%e %b, %Y') AS formatted_employer_agreed_terms_on, 
                          DATE_FORMAT(referrals.employer_rejected_on, '%e %b, %Y') AS formatted_employer_rejected_on, 
                          DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
                          DATE_FORMAT(referrals.employer_removed_on, '%e %b, %Y') AS formatted_employer_removed_on, 
                          IF(referrals.testimony IS NULL OR referrals.testimony = '', '0', '1') AS has_testimony, 
                          IF(referrals.employer_remarks IS NULL OR referrals.employer_remarks = '', '0', '1') AS has_employer_remarks", 
            'joins' => "members ON members.email_addr = referrals.member, 
                        jobs ON jobs.id = referrals.job, 
                        employers ON employers.id = jobs.employer, 
                        resumes ON resumes.id = referrals.resume", 
            'match' => "referrals.referee = '". $this->member->getId(). "'",
            'order' => "referrals.referred_on DESC"
        );
        
        $referral = new Referral();
        return $referral->find($criteria);
    }
    
    private function get_employers() {
        $criteria = array(
            'columns' => "DISTINCT employers.id, employers.name AS employer",
            'joins' => "employers ON employers.id = jobs.employer" 
            //'match' => "jobs.expire_on >= now()"
        );
        
        $job = new Job();
        return $job->find($criteria);
    }
    
    private function get_pre_selected_jobs() {
        $criteria = array(
            'columns' => "jobs.employer, jobs.title", 
            'match' => "id IN (". implode(',', $this->selected_jobs). ")"
        );
        
        $job = new Job();
        $result = $job->find($criteria);
        
        if ($result === false || is_null($result) || empty($result)) {
            return '(None Selected)';
        }
        
        $jobs_str = '';
        foreach ($result as $i=>$row) {
            $jobs_str .= '['. $row['employer']. '] '. $row['title'];
            
            if ($i < count($result)-1) {
                $jobs_str .= '<br/>';
            }
        }
        
        return $jobs_str;
    }
    
    private function get_job_profiles() {
        $criteria = array(
            'columns' => "member_job_profiles.id, member_job_profiles.position_title, 
                          member_job_profiles.position_superior_title, 
                          member_job_profiles.employer, member_job_profiles.employer_description, 
                          industries.industry AS specialization, 
                          employer_industries.industry AS employer_specialization, 
                          DATE_FORMAT(member_job_profiles.work_from, '%b, %Y') AS formatted_work_from, 
                          DATE_FORMAT(member_job_profiles.work_to, '%b, %Y') AS formatted_work_to", 
            'joins' => "member_job_profiles ON member_job_profiles.member = members.email_addr, 
                        industries ON industries.id = member_job_profiles.specialization, 
                        industries AS employer_industries ON employer_industries.id = member_job_profiles.specialization",
            'match' => "members.email_addr = '". $this->member->getId(). "'",
            'having' => "member_job_profiles.id IS NOT NULL",
            'order' => "work_from DESC"
        );
        
        $result = $this->member->find($criteria);
        if (is_null($result) || count($result) <= 0) {
            return array();
        }
        
        return $result;
    }
    
    public function show() {
        $this->begin();
        if ($this->is_new) {
            $this->top('Member - New Member');
        } else {
            $this->top('Member - '. htmlspecialchars_decode(stripslashes($this->member->getFullName())));
        }
        $this->menu_employee('members');
        
        $branch = $this->employee->getBranch();
        
        $raw_data = array();
        $profile = array();
        if (!$this->is_new) {
            // get profile
            $raw_data = $this->member->get();
            $profile = $raw_data[0];
            
            // get job profiles
            $raw_data = $this->get_job_profiles();
            $profile['job_profiles'] = $raw_data;
            
            // get resumes
            $profile['resumes'] = $this->get_resumes();
            
            // get notes
            $profile['notes'] = $this->member->getNotes();
            
            // get the referrers and referees
            //$profile['referrers'] = $this->get_referrers();
            $profile['referrers'] = $this->member->getReferrers();
            $profile['referees'] = $this->member->getReferees();
        } else {
            $profile = array(
                'email_addr' => '',
                'firstname' => '',
                'lastname' => '',
                'phone_num' => '',
                'address' => '',
                'state' => '',
                'zip' => '0',
                'country' => $branch[0]['country'],  
                'hrm_gender' => '',
                'hrm_ethicnity' => '',
                'hrm_birthdate' => '1957-08-31',
                'citizenship' => $branch[0]['country'],
                'resumes' => array()
            );
        }
        
        $potential_referrers = $this->get_potential_referrers();
        $potential_candidates = $this->get_potential_candidates();
        $applications = $this->get_applications();
        
        ?>
        <!-- submenu -->
        <div class="menu">
            <?php $style = 'background-color: #CCCCCC;'; ?>
            <ul class="menu">
                <li id="item_back"><a class="menu" onClick="go_back();">&lt;&lt;</a></li>
                <li id="item_profile" style="<?php echo ($this->current_page == 'profile') ? $style : ''; ?>"><a class="menu" onClick="show_profile();">Profile</a></li>
            <?php
            if (!$this->is_new) {
            ?>
                <li id="item_resumes" style="<?php echo ($this->current_page == 'resumes') ? $style : ''; ?>"><a class="menu" onClick="show_resumes(false);">Resumes</a></li>
                <li id="item_career" style="<?php echo ($this->current_page == 'career') ? $style : ''; ?>"><a class="menu" onClick="show_career(false);">Career Profile</a></li>
                <li id="item_notes" style="<?php echo ($this->current_page == 'notes') ? $style : ''; ?>"><a class="menu" onClick="show_notes(false);">Notes</a></li>
                <li id="item_connections" style="<?php echo ($this->current_page == 'connections') ? $style : ''; ?>"><a class="menu" onClick="show_connections(false);">Connections</a></li>
                <li id="item_applications" style="<?php echo ($this->current_page == 'applications') ? $style : ''; ?>"><a class="menu" onClick="show_applications(false);">Applications</a></li>
            <?php
            }
            ?>
            </ul>
        </div>
        <!-- end submenu -->
        
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="member_profile">
            <table class="profile">
                <tr>
                    <td class="photo">
                        <div id="photo_area" class="photo">
                    <?php
                        if (!$this->is_new && $this->member->hasPhoto()) {
                     ?>
                            <img class="photo_image" src="member_photo.php?id=<?php echo $profile['email_addr']; ?>" />
                     <?php
                        } else {
                    ?>
                            <div style="text-align: center; margin: auto;">
                    <?php
                            if ($this->is_new) {
                                echo 'Not allowed to upload photo without an account.';
                            } else {
                                echo 'No photo uploaded.';
                            }
                    ?>
                            </div>
                    <?php
                        }
                    ?>
                        </div>
                    <?php
                        if (!$this->is_new) {
                    ?>
                        <div id="photo_buttons" class="photo_buttons">
                    <?php
                            if (!$this->member->isPhotoApproved()) {
                    ?>
                            <input type="button" id="accept_btn" value="Accept" onClick="approve_photo();" />
                    <?php
                            } else {
                    ?>
                            <input type="button" value="Accept" disabled />
                    <?php
                            }
                    ?>
                            <input type="button" value="Reject" onClick="reject_photo();" />
                        </div>
                    <?php
                        }
                    ?>
                    </td>
                    <td>
                        <form id="profile" method="post" onSubmit="return false;">
                            <table class="profile_form">
                                <tr>
                                    <td class="buttons_bar" colspan="2"><input type="button" onClick="save_profile();" value="Save &amp; Update Profile" /></td>
                                </tr>
                                <tr>
                                    <td class="title" colspan="2">Sign In Details</td>
                                </tr>
                                <tr>
                                    <td class="label">Email Address:</td>
                                    <td class="field">
                                        <?php
                                        if ($this->is_new) {
                                        ?>
                                        <input class="field" type="text" id="email_addr" value=""  maxlength="50" />
                                        <?php
                                        } else {
                                            echo $profile['email_addr'];
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="password">Password:</label></td>
                                    <td class="field">
                                        <?php
                                        if ($this->is_new) {
                                        ?>
                                        <input type="button" value="Reset Password" onClick="reset_password();" disabled />
                                        <?php
                                        } else {
                                        ?>
                                        <input type="button" value="Reset Password" onClick="reset_password();" />
                                        <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="title" colspan="2">Citizenship</td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="citizenship">Citizen of:</label></td>
                                    <td class="field">
                                        <?php echo $this->generate_countries($profile['citizenship'], 'citizenship'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="title" colspan="2">Contact Details</td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="firstname">First Name:</label></td>
                                    <td class="field"><input class="field" type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars_decode(stripslashes($profile['firstname'])) ?>"  /></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="lastname">Last Name:</label></td>
                                    <td class="field"><input class="field" type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars_decode(stripslashes($profile['lastname'])) ?>"  /></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="phone_num">Telephone Number:</label></td>
                                    <td class="field"><input class="field" type="text" id="phone_num" name="phone_num" value="<?php echo $profile['phone_num'] ?>"  /></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="address">Mailing Address:</label></td>
                                    <td class="field"><textarea id="address" name="address" ><?php echo htmlspecialchars_decode(stripslashes($profile['address'])) ?></textarea></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="state">State/Province/Area:</label></td>
                                    <td class="field"><input class="field" type="text" id="state" name="state" value="<?php echo $profile['state'] ?>"   /></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="zip">Zip/Postal Code:</label></td>
                                    <td class="field"><input class="field" type="text" id="zip" name="zip" value="<?php echo $profile['zip'] ?>"  /></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="country">Country:</label></td>
                                    <td class="field">
                                        <?php echo $this->generate_countries($profile['country']); ?>
                                    </</td>
                                </tr>
                                <tr>
                                    <td class="title" colspan="2">HRM Census Form</td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="gender">Gender:</label></td>
                                    <td class="field">
                                        <select id="gender" name="hrm_gender">
                                        <?php
                                            if ($this->is_new) {
                                        ?>
                                            <option value="" selected>Select One</option>
                                            <option value="" disabled>&nbsp;</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                        <?php
                                            } else {
                                        ?>
                                            <option value="">Select One</option>
                                            <option value="" disabled>&nbsp;</option>
                                        <?php
                                                if ($profile['hrm_gender'] == 'male') {
                                        ?>
                                            <option value="male" selected>Male</option>
                                            <option value="female">Female</option>
                                        <?php
                                                } else {
                                        ?>
                                            <option value="male">Male</option>
                                            <option value="female" selected>Female</option>
                                        <?php
                                                }
                                            }
                                        ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="birthdate_month">Birthdate:</label></td>
                                    <td class="field">
                                    <?php
                                        $birthdate = explode('-', $profile['hrm_birthdate']);
                                        echo generate_month_dropdown('birthdate_month', '', $birthdate[1]);
                                        echo generate_dropdown('birthdate_day', '', 1, 31, $birthdate[2], 2, 'Day');
                                    ?>
                                        <input maxlength="4" style="width: 50px;" type="text" id="birthdate_year" name="birthdate_year" value="<?php echo $birthdate[0] ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="ethnicity">Ethnicity:</label></td>
                                    <td class="field"><input class="field" type="text" id="ethnicity" name="ethnicity" value="<?php echo $profile['hrm_ethnicity'] ?>"  /></td>
                                </tr>
                                <tr>
                                    <td class="buttons_bar" colspan="2"><input type="button" onClick="save_profile();" value="Save &amp; Update Profile" /></td>
                                </tr>
                            </table>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="member_resumes">
            <table class="buttons">
                <tr>
                    <td class="right">
                        <input type="button" id="upload_new_resume" name="upload_new_resume" value="Upload Resume" onClick="show_upload_resume_popup(0);" />
                    </td>
                </tr>
            </table>
            <div id="div_resumes">
            <?php
                if (empty($profile['resumes'])) {
            ?>
                <div class="empty_results">No resumes uploaded.</div>
            <?php
                } else {
                    $resumes_table = new HTMLTable('resumes_table', 'resumes');

                    $resumes_table->set(0, 0, "Modified On", '', 'header');
                    $resumes_table->set(0, 1, "Resume", '', 'header');
                    $resumes_table->set(0, 2, "&nbsp;", '', 'header actions');

                    foreach ($profile['resumes'] as $i=>$resume) {
                        $resumes_table->set($i+1, 0, $resume['formatted_modified_on'], '', 'cell');
                        $resumes_table->set($i+1, 1, '<a href="resume_download.php?id='. $resume['id']. '">'. $resume['file_name']. '</a>', '', 'cell');
                        
                        $resume_action = '<a class="no_link" onClick="update_resume('. $resume['id']. ');">Update</a>';
                        if ($resume['is_yel_uploaded'] == '1') {
                            $resume_action .= '&nbsp;|&nbsp;<a class="no_link" onClick="show_apply_job_popup('. $resume['id']. ', \''. addslashes($resume['file_name']). '\');">Apply Job</a>';
                        }
                        $resumes_table->set($i+1, 2, $resume_action, '', 'cell actions');
                    }

                    echo $resumes_table->get_html();
                }
            ?>
            </div>
            <table class="buttons">
                <tr>
                    <td class="right">
                        <input type="button" id="upload_new_resume" name="upload_new_resume" value="Upload Resume" onClick="show_upload_resume_popup(0);" />
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="member_career">
            <form id="career" method="post" onSubmit="return false;">
                <table class="career_profile">
                    <tr>
                        <td>
                            <table class="career_form">
                                <tr>
                                    <td class="buttons_bar" colspan="2"><input type="button" onClick="save_career();" value="Save" /></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="is_active_seeking_job">Seeking for a Job:</label></td>
                                    <td class="field">
                                        <select id="is_active_seeking_job">
                                        <?php
                                            if ($profile['is_active_seeking_job'] == '1') {
                                        ?>
                                            <option value="1" selected>Yes</option>
                                            <option value="0">No</option>
                                        <?php
                                            } else {
                                        ?>
                                            <option value="1">Yes</option>
                                            <option value="0" selected>No</option>
                                        <?php
                                            }
                                        ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="seeking">Goals &amp; Experiences:</label></td>
                                    <td class="field">
                                        <textarea id="seeking"><?php echo $this->remove_br( htmlspecialchars_decode(stripslashes($profile['seeking']))); ?></textarea></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="expected_salary">Expected Salary:</label></td>
                                    <td class="field">
                                        <?php 
                                            $this->generate_currencies('expected_salary_currency', $profile['expected_salary_currency']); 
                                        ?>$ &nbsp;
                                        <input class="salary" type="text" id="expected_salary" value="<?php echo $profile['expected_salary'] ?>" /> 
                                        - 
                                        <input class="salary" type="text" id="expected_salary_end" value="<?php echo $profile['expected_salary_end'] ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="can_travel_relocate">Willing to Travel or Relocate:</label></td>
                                    <td class="field">
                                        <select id="can_travel_relocate">
                                        <?php
                                            if ($profile['can_travel_relocate'] == 'Y') {
                                        ?>
                                            <option value="Y" selected>Yes</option>
                                            <option value="N">No</option>
                                        <?php
                                            } else {
                                        ?>
                                            <option value="Y">Yes</option>
                                            <option value="N" selected>No</option>
                                        <?php
                                            }
                                        ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="reason_for_leaving">Reason for leaving:</label></td>
                                    <td class="field"><textarea id="reason_for_leaving"><?php echo $this->remove_br(htmlspecialchars_decode(stripslashes($profile['reason_for_leaving']))); ?></textarea></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="current_position">Current position:</label></td>
                                    <td class="field"><textarea id="current_position"><?php echo $this->remove_br(htmlspecialchars_decode(stripslashes($profile['current_position']))); ?></textarea></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="current_salary">Current Salary:</label></td>
                                    <td class="field">
                                        <?php 
                                            $this->generate_currencies('current_salary_currency', $profile['current_salary_currency']); 
                                        ?>$ &nbsp;<input class="salary" type="text" id="current_salary" value="<?php echo $profile['current_salary'] ?>" /> - <input class="salary" type="text" id="current_salary_end" value="<?php echo $profile['current_salary_end'] ?>" /></td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="notice_period">Notice Period:</label></td>
                                    <td class="field"><input class="salary" type="text" id="notice_period" value="<?php echo $profile['notice_period'] ?>" /> months</td>
                                </tr>
                                <tr>
                                    <td class="buttons_bar" colspan="2"><input type="button" onClick="save_career();" value="Save" /></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <div class="buttons_bar"><input type="button" onClick="show_job_profile_popup('0');" value="Add" /></div>
                            <div id="job_profiles">
                            <?php
                            $job_profiles = $profile['job_profiles'];
                            if (empty($job_profiles) || count($job_profiles) <= 0 ||
                                $job_profiles === false) {
                            ?>
                                <div class="empty_results">No job profiles found.</div>
                            <?php
                            } else {
                                $job_profiles_table = new HTMLTable('job_profiles_table', 'job_profiles');

                                $job_profiles_table->set(0, 0, '&nbsp;', '', 'header action');
                                $job_profiles_table->set(0, 1, 'From', '', 'header');
                                $job_profiles_table->set(0, 2, 'To', '', 'header');
                                $job_profiles_table->set(0, 3, 'Employer', '', 'header');
                                $job_profiles_table->set(0, 4, 'Position', '', 'header');
                                $job_profiles_table->set(0, 5, '&nbsp;', '', 'header action');
                            
                                foreach ($job_profiles as $i => $job_profile) {
                                    $job_profiles_table->set($i+1, 0, '<a class="no_link" onClick="delete_job_profile('. $job_profile['id']. ')">delete</a>', '', 'cell action');
                                    $job_profiles_table->set($i+1, 1, $job_profile['formatted_work_from'], '', 'cell');
                                    $work_to = $job_profile['formatted_work_to'];
                                    if (is_null($work_to) || empty($work_to) || $work_to == '0000-00-00') {
                                        $work_to = 'Present';
                                    }
                                    $job_profiles_table->set($i+1, 2, $work_to, '', 'cell');
                                
                                    $emp = htmlspecialchars_decode(stripslashes($job_profile['employer']));
                                    $emp .= '<span class="mini_spec">'. $job_profile['employer_specialization']. '</span><br/>';
                                    $emp .= '<span class="mini_emp_desc">'. $job_profile['employer_description']. '</span><br/>';
                                    $job_profiles_table->set($i+1, 3, $emp, '', 'cell');
                                
                                    $pos = htmlspecialchars_decode(stripslashes($job_profile['position_title']));
                                    $pos .= '<span class="mini_spec">'. $job_profile['specialization']. '</span><br/>';
                                    $pos .= '<span class="mini_superior">'. $job_profile['position_superior_title']. '</span>';
                                    $job_profiles_table->set($i+1, 4, $pos, '', 'cell');
                                    $job_profiles_table->set($i+1, 5, '<a class="no_link" onClick="show_job_profile_popup('. $job_profile['id']. ')">edit</a>', '', 'cell action');
                                }
                            
                                echo $job_profiles_table->get_html();
                            }
                            ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        
        <div id="member_notes">
            <form id="notes" method="post" onSubmit="return false;">
                <table class="notes_form">
                    <tr>
                        <td class="title">Extra Notes/Remarks:</td>
                    </tr>
                    <tr>
                        <td class="field"><textarea id="extra_notes"><?php echo htmlspecialchars_decode(stripslashes($profile['notes'])) ?></textarea></td>
                    </tr>
                    <tr>
                        <td class="buttons_bar" colspan="2"><input type="button" onClick="save_notes();" value="Save" /></td>
                    </tr>
                </table>
            </form>
        </div>
        
        <div id="member_connections">
            <div id="connections">
                <table id="connections_table">
                    <tr>
                        <td class="connections">
                            <table class="buttons">
                                <tr>
                                    <td class="right">
                                        <input type="button" value="Add Referrer" onClick="show_add_referrers_popup();" />
                                    </td>
                                </tr>
                            </table>
                            <div id="div_referrers">
                            <?php
                                if (empty($profile['referrers'])) {
                            ?>
                                <div class="empty_results">No referrers found.</div>
                            <?php
                                } else {
                                    $referrers_table = new HTMLTable('referrers_table', 'referrers');

                                    $referrers_table->set(0, 0, "Referrer (Who referred me?)", '', 'header');
                                    $referrers_table->set(0, 1, "&nbsp;", '', 'header actions');

                                    foreach ($profile['referrers'] as $i=>$referrer) {
                                        $referrers_table->set($i+1, 0, '<a href="member.php?member_email_addr='. $referrer['email_addr']. '">'. $referrer['referrer']. '</a>', '', 'cell');
                                        $referrers_table->set($i+1, 1, '<a class="no_link" onClick="remove_referrer(\''. addslashes($referrer['email_addr']). '\');">Remove</a>&nbsp|&nbsp;<a class="no_link" onClick="reward(\''. addslashes($referrer['email_addr']). '\');">Reward</a>', '', 'cell actions');
                                    }

                                    echo $referrers_table->get_html();
                                }
                            ?>
                            </div>
                            <table class="buttons">
                                <tr>
                                    <td class="right">
                                        <input type="button" value="Add Referrer" onClick="show_add_referrers_popup();" />
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="border">&nbsp;</td>
                        <td class="connections">
                            <table class="buttons">
                                <tr>
                                    <td class="right">
                                        <input type="button" value="Add Candidate" onClick="show_add_candidates_popup();" />
                                    </td>
                                </tr>
                            </table>
                            <div id="div_referees">
                            <?php
                                if (empty($profile['referees'])) {
                            ?>
                                <div class="empty_results">No candidates found.</div>
                            <?php
                                } else {
                                    $referees_table = new HTMLTable('referees_table', 'referees');

                                    $referees_table->set(0, 0, "Candidate (I referred who?)", '', 'header');
                                    $referees_table->set(0, 1, "&nbsp;", '', 'header actions');
                                    
                                    foreach ($profile['referees'] as $i=>$referee) {
                                        $referees_table->set($i+1, 0, '<a href="member.php?member_email_addr='. $referee['email_addr']. '">'. $referee['referee']. '</a>', '', 'cell');
                                        $referees_table->set($i+1, 1, '<a class="no_link" onClick="remove_referee(\''. addslashes($referee['email_addr']). '\');">Remove</a>', '', 'cell actions');
                                    }
                                    
                                    echo $referees_table->get_html();
                                }
                            ?>
                            </div>
                            <table class="buttons">
                                <tr>
                                    <td class="right">
                                        <input type="button" value="Add Candidate" onClick="show_add_candidates_popup();" />
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div id="member_applications">
            <table class="buttons">
                <tr>
                    <td class="right">
                        Filter: 
                        <select id="filter" onChange="filter_applications();">
                            <option value="" selected>Show All</option>
                            <option value="" disabled>&nbsp;</option>
                            <option value="not_viewed">Not Viewed Yet</option>
                            <option value="viewed">Viewed</option>
                            <option value="employed">Employed</option>
                            <option value="rejected">Rejected</option>
                            <option value="removed">Deleted</option>
                        </select>
                    </td>
                </tr>
            </table>
            <div id="applications">
        <?php
            if (empty($applications)) {
        ?>
                <div class="empty_results">No applications found.</div>
        <?php
            } else {
                $applications_table = new HTMLTable('applications_table', 'applications');

                $applications_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('referrals', 'employers.name');\">Employers</a>", '', 'header');
                $applications_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('referrals', 'jobs.title');\">Job</a>", '', 'header');
                $applications_table->set(0, 2, "<a class=\"sortable\" onClick=\"sort_by('referrals', 'members.lastname');\">Referrer</a>", '', 'header');
                $applications_table->set(0, 3, "<a class=\"sortable\" onClick=\"sort_by('referrals', 'referrals.referred_on');\">Applied On</a>", '', 'header');
                $applications_table->set(0, 4, 'Status', '', 'header');
                $applications_table->set(0, 5, "Testimony", '', 'header');
                $applications_table->set(0, 6, "Resume Submitted", '', 'header');

                foreach ($applications as $i=>$application) {
                    $applications_table->set($i+1, 0, '<a href="employer.php?id='. $application['employer_id']. '">'. $application['employer']. '</a>', '', 'cell');
                    $applications_table->set($i+1, 1, '<a class="no_link" onClick="show_job_desc('. $application['job_id']. ');">'. $application['job']. '</a>', '', 'cell');
                    $applications_table->set($i+1, 2, '<a href="member.php?member_email_addr='. $application['referrer']. '">'. $application['referrer_name']. '</a>', '', 'cell');
                    $applications_table->set($i+1, 3, $application['formatted_referred_on'], '', 'cell');
                    
                    $status = '<span class="not_viewed_yet">Not Viewed Yet</a>';
                    if (!is_null($application['formatted_employer_agreed_terms_on'])) {
                        $status = '<span class="viewed">Viewed on:</span> '. $application['formatted_employer_agreed_terms_on'];
                    }
                    
                    if (!is_null($application['formatted_employed_on'])) {
                        $status = '<span class="employed">Employed on:</span> '. $application['formatted_employed_on'];
                    }
                    
                    if (!is_null($application['formatted_employer_rejected_on'])) {
                        $status = '<span class="rejected">Rejected on:</span> '. $application['formatted_employer_rejected_on'];
                    }
                    
                    if (!is_null($application['formatted_employer_removed_on'])) {
                        $status = '<span class="removed">Deleted on:</span> '. $application['formatted_employer_deleted_on'];
                    }
                    
                    if ($application['has_employer_remarks'] == '1') {
                        $status .= '<br/><a class="no_link" onClick="show_employer_remarks('. $application['id']. ');">Employer Remarks</a>';
                    }
                    $applications_table->set($i+1, 4, $status, '', 'cell testimony');
                    
                    $testimony = 'None Provided';
                    if ($application['has_testimony'] == '1') {
                        $testimony = '<a class="no_link" onClick="show_testimony('. $application['id']. ');">Show</a>';
                    }
                    $applications_table->set($i+1, 5, $testimony, '', 'cell testimony');
                    
                    $applications_table->set($i+1, 6, '<a href="resume_download.php?id='. $application['resume_id']. '">'. $application['file_name']. '</a>', '', 'cell testimony');
                }

                echo $applications_table->get_html();
            }
        ?>
            </div>
        </div>
        
        <!-- popup windows goes here -->
        <div id="upload_resume_window" class="popup_window">
            <div class="popup_window_title">Upload Resume</div>
            <form id="upload_resume_form" action="member_action.php" method="post" enctype="multipart/form-data" onSubmit="return close_upload_resume_popup(true);">
                <div class="upload_resume_form">
                    <input type="hidden" id="resume_id" name="id" value="0" />
                    <input type="hidden" name="member" value="<?php echo $this->member->getId(); ?>" />
                    <input type="hidden" name="action" value="upload_resume" />
                    <div id="upload_field" class="upload_field">
                        <input id="my_file" name="my_file" type="file" />
                        <div style="font-size: 9pt; margin-top: 15px;">
                            <ol>
                                <li>Only HTML (*.html, *.htm), Text (*.txt), Portable Document Format (*.pdf) or MS Word document (*.doc) with the file size of less than 1MB are allowed.</li>
                                <li>You can upload as many resumes as you want and designate them for different job applications.</li>
                                <li>You can update your resume by clicking &quot;Update&quot; then upload an updated version.</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="popup_window_buttons_bar">
                     <input type="submit" value="Upload" />
                     <input type="button" value="Cancel" onClick="close_upload_resume_popup(false);" />
                </div>
            </form>
        </div>
                
        <div id="apply_job_window" class="popup_window">
            <div class="popup_window_title">Apply Job</div>
            <div id="div_resume_info" class="resume_info">
                <span style="font-weight: bold;">Resume selected:</span>
                <span id="resume_file_name"></span><br/>
                <table class="pre_selected_jobs_table">
                    <tr>
                        <td class="label">
                            <span style="font-weight: bold;">Pre-selected Jobs:</span><br/>
                            <span style="font-size: 9pt;">
                                <a class="no_link" onClick="clear_pre_selected_jobs();">
                                    (clear)
                                </a>
                            </span>
                        </td>
                        <td>
                            <div id="pre_selected_jobs_list">
                                <?php echo $this->get_pre_selected_jobs(); ?>
                            </div>
                        </td>
                    </tr>
                </table>
                <span style="font-weight: bold;">Referrer: </span>
                <?php
                    $referrers = $this->get_referrers();
                    if (empty($referrers) || $referrers === false) {
                ?>
                    [No referrers.]
                    <input type="hidden" id="apply_job_referrer" value="<?php echo 'team.'. strtolower($branch[0]['country']); ?>@yellowelevator.com" />
                <?php
                    } else {
                ?>
                    <select id="apply_job_referrer">
                        <option value="">Select a Referrer</option>
                        <option value="" disabled>&nbsp;</option>
                <?php
                        foreach ($referrers as $referrer) {
                ?>
                        <option value="<?php echo $referrer['id']; ?>"><?php echo htmlspecialchars_decode(stripslashes($referrer['member_name'])). ' ('. $referrer['id']. ')'; ?></option>
                <?php
                        }
                ?>
                    </select>
                <?php
                    }
                ?>
            </div>
            <form id="apply_job_form" onSubmit="return false;">
                <input type="hidden" id="resume_id" name="resume_id" value="0" />
                <input type="hidden" id="selected_jobs" value="<?php echo implode(',', $this->selected_jobs); ?>" />
                <div id="div_apply_job_form" class="apply_job_form">
                    <table class="jobs_selection">
                        <tr>
                            <td class="jobs_list">
                            <?php
                                $employers = $this->get_employers();
                                if (!empty($employers) && $employers !== false) {
                            ?>
                                <select id="employers" onChange="filter_jobs();">
                            <?php
                                    foreach($employers as $employer) {
                            ?>
                                    <option value="<?php echo $employer['id']; ?>"><?php echo $employer['employer']; ?></option>
                            <?php
                                    }
                            ?>
                                </select>
                            <?php
                                } else {
                            ?>
                                <span class="no_employers">[No employers with opened jobs found.]</span>
                            <?php
                                }
                            ?>
                                <div id="jobs_selector">
                                    Select an employer in the dropdown list above.
                                </div>
                                <div id="selected_job_counter">
                                    <span id="counter_lbl">0</span> jobs selected.
                                </div>
                            </td>
                            <td class="separator"></td>
                            <td>
                                <div id="job_description">
                                    Select a job in the jobs list.
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="popup_window_buttons_bar">
                    <input type="button" id="apply_btn" value="Apply" onClick="close_apply_job_popup(true);" />
                    <input type="button" value="Cancel" onClick="close_apply_job_popup(false);" />
                </div>
            </form>
        </div>
        
        <div id="add_referrers_window" class="popup_window">
            <?php 
                $has_no_potential_referrers = false;
            ?>
            <div class="popup_window_title">Add Referrers</div>
            <form id="add_referrers_form" action="member_action.php" method="post">
                <div class="add_referrers_form">
                    <br/>
                <?php
                    if (empty($potential_referrers)) {
                        $has_no_potential_referrers = true;
                ?>
                    No potential referrers can be found.
                    <br/><br/>
                <?php
                    } else {
                ?>
                    Select multiple potential referrers from the following list.
                    <br/><br/>
                    <select id="referrers" class="potentials_list" multiple>
                <?php
                        foreach($potential_referrers as $referrer) {
                ?>
                        <option value="<?php echo $referrer['email_addr']; ?>">
                            <?php echo $referrer['member_name']. ' ('. $referrer['email_addr']. ')'; ?>
                        </option>
                <?php
                        }
                ?>
                    </select>
                <?php
                    }
                ?>
                </div>
                <div class="popup_window_buttons_bar">
                <?php 
                    if ($has_no_potential_referrers) {
                ?>
                    <input type="button" value="Add Referrers" disabled />
                <?php
                    } else {
                ?>
                    <input type="button" value="Add Referrers" onClick="close_add_referrers_popup(true);" />
                <?php
                    }
                ?>
                    <input type="button" value="Cancel" onClick="close_add_referrers_popup(false);" />
                </div>
            </form>
        </div>
        
        <div id="add_candidates_window" class="popup_window">
            <?php 
                $has_no_potential_candidates = false;
            ?>
            <div class="popup_window_title">Add Candidate</div>
            <form id="add_candidates_form" action="member_action.php" method="post">
                <div class="add_candidates_form">
                    <br/>
                <?php
                    if (empty($potential_candidates)) {
                        $has_no_potential_candidate = true;
                ?>
                    No potential candidates can be found.
                    <br/><br/>
                <?php
                    } else {
                ?>
                    Select multiple potential candidates from the following list.
                    <br/><br/>
                    <select id="candidates" class="potentials_list" multiple>
                <?php
                        foreach($potential_candidates as $candidate) {
                ?>
                        <option value="<?php echo $candidate['email_addr']; ?>">
                            <?php echo $candidate['member_name']. ' ('. $candidate['email_addr']. ')'; ?>
                        </option>
                <?php
                        }
                ?>
                    </select>
                <?php
                    }
                ?>
                </div>
                <div class="popup_window_buttons_bar">
                <?php 
                    if ($has_no_potential_candidates) {
                ?>
                    <input type="button" value="Add Candidates" disabled />
                <?php
                    } else {
                ?>
                    <input type="button" value="Add Candidates" onClick="close_add_candidates_popup(true);" />
                <?php
                    }
                ?>
                    <input type="button" value="Cancel" onClick="close_add_candidates_popup(false);" />
                </div>
            </form>
        </div>
        
        <div id="testimony_window" class="popup_window">
            <div class="popup_window_title">Testimony</div>
            <div class="testimony_form">
                <br/>
                <span id="testimony"></span>
                <br/><br/>
            </div>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Cancel" onClick="close_testimony();" />
            </div>
        </div>
        
        <div id="job_desc_window" class="popup_window">
            <div class="popup_window_title">Job Description</div>
            <div class="job_desc_form">
                <br/>
                <span id="job_desc"></span>
                <br/><br/>
            </div>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Cancel" onClick="close_job_desc();" />
            </div>
        </div>
        
        <div id="employer_remarks_window" class="popup_window">
            <div class="popup_window_title">Employer Remarks</div>
            <div class="employer_remarks_form">
                <br/>
                <span id="employer_remarks"></span>
                <br/><br/>
            </div>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Cancel" onClick="close_employer_remarks();" />
            </div>
        </div>
        
        <div id="job_profile_window" class="popup_window">
            <div class="popup_window_title">Job Profile</div>
            <form onSubmit="return false;">
                <input type="hidden" id="job_profile_id" value="0" />
                <div class="job_profile_form">
                    <table class="job_profile_form">
                        <tr>
                            <td class="label"><label for="specialization">Specialization:</label></td>
                            <td class="field">
                                <?php $this->generate_industries('', 'specialization'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="position_title">Job Title:</label></td>
                            <td class="field">
                                <input class="field" type="text" id="position_title" name="position_title" />
                                <br/>
                                <span class="tips">eg: Director, Manager, GM, VP, etc.</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="position_superior_title">Superior Title:</label></td>
                            <td class="field">
                                <input class="field" type="text" id="position_superior_title" name="position_superior_title" />
                                <br/>
                                <span class="tips">eg: Director, Manager, GM, VP, etc.</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="organization_size">Number of Direct Reports:</label></td>
                            <td class="field"><input class="field" type="text" id="organization_size" name="organization_size" /></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="work_from_month">Duration:</label></td>
                            <td class="field">
                                <?php echo generate_month_dropdown('work_from_month', ''); ?>
                                <input type="text" class="year" maxlength="4" id="work_from_year" value="yyyy" /> 
                                to 
                                <span id="work_to_dropdown">
                                    <?php echo generate_month_dropdown('work_to_month', ''); ?>
                                    <input type="text" class="year" maxlength="4" id="work_to_year" value="yyyy" />
                                </span>
                                <input type="checkbox" id="work_to_present" onClick="toggle_work_to();" /> 
                                <label for="work_to_present">Present</label>
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="company">Employer:</label></td>
                            <td class="field"><input class="field" type="text" id="company" name="company" /></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="emp_desc">Employer Description:</label></td>
                            <td class="field">
                                <?php $this->generate_employer_description('emp_desc', -1); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="emp_specialization">Employer Specialization:</label></td>
                            <td class="field">
                                <?php $this->generate_industries('', 'emp_specialization'); ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Save" onClick="close_job_profile_popup(true);" />
                <input type="button" value="Cancel" onClick="close_job_profile_popup(false);" />
            </div>
        </div>
        <?php
    }
}
?>