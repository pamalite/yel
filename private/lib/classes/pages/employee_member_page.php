<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). "/../htmltable.php";

class EmployeeMemberPage extends Page {
    private $employee = NULL;
    private $member = NULL;
    private $is_new = false;
    private $current_page = 'profile';
    private $error_message = '';
    
    function __construct($_session, $_member_id = '') {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->member = new Member($_member_id);
    }
    
    public function new_member($_is_new) {
        $this->is_new = $_is_new;
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
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_member.css">'. "\n";
    }
    
    public function insert_employee_member_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/flextable.js"></script>'. "\n";
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
    
    private function get_resumes() {
        $resume = new Resume();
        
        $criteria = array(
            'columns' => "id, file_name, DATE_FORMAT(modified_on, '%e %b, %Y') AS formatted_modified_on", 
            'match' => "member = '". $this->member->getId(). "' AND deleted = 'N'", 
            'order' => "modified_on DESC"
        );
        
        return $resume->find($criteria);
    }
    
    private function get_referrers() {
        $criteria = array(
            'columns' => "members.email_addr, CONCAT(members.firstname, ', ', members.lastname) AS referrer",
            'joins' => "member_referees ON members.email_addr = member_referees.member", 
            'match' => "member_referees.referee = '". $this->member->getId(). "'", 
            'order' => "members.lastname ASC"
        );
        
        return $this->member->find($criteria);
    }
    
    private function get_referees() {
        $criteria = array(
            'columns' => "members.email_addr, CONCAT(members.firstname, ', ', members.lastname) AS referee",
            'joins' => "member_referees ON members.email_addr = member_referees.referee", 
            'match' => "member_referees.member = '". $this->member->getId(). "'", 
            'order' => "members.lastname ASC"
        );
        
        return $this->member->find($criteria);
    }
    
    public function show() {
        $this->begin();
        $this->top('Member - '. htmlspecialchars_decode(stripslashes($this->member->getFullName())));
        $this->menu_employee('members');
        
        $branch = $this->employee->getBranch();
        
        $raw_data = array();
        $profile = array();
        if (!$this->is_new) {
            // get profile
            $raw_data = $this->member->get();
            $profile = $raw_data[0];
            
            // get resumes
            $profile['resumes'] = $this->get_resumes();
            
            // get notes
            $profile['notes'] = $this->member->getNotes();
            
            // get the referrers and referees
            //$profile['referrers'] = $this->get_referrers();
            $profile['referrers'] = $this->get_referees();
            $profile['referees'] = $this->get_referees();
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
        
        ?>
        <!-- submenu -->
        <div class="menu">
            <?php $style = 'background-color: #CCCCCC;'; ?>
            <ul class="menu">
                
                <li id="item_profile" style="<?php echo ($this->current_page == 'profile') ? $style : ''; ?>"><a class="menu" onClick="show_profile();">Profile</a></li>
            <?php
            if (!$this->is_new) {
            ?>
                <li id="item_resumes" style="<?php echo ($this->current_page == 'resumes') ? $style : ''; ?>"><a class="menu" onClick="show_resumes(false);">Resumes</a></li>
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
                        $resumes_table->set($i+1, 1, '<a href="resume.php?id='. $resume['id']. '">'. $resume['file_name']. '</a>', '', 'cell');
                        $resumes_table->set($i+1, 2, '<a class="no_link" onClick="update_resume('. $resume['id']. ');">Update</a>&nbsp;<a class="no_link" onClick="apply_job_with('. $resume['id']. ');">Applu Job</a>', '', 'cell actions');
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
        
        <div id="member_notes">
            <form id="notes" method="post" onSubmit="return false;">
                <table class="notes_form">
                    <tr>
                        <td class="buttons_bar" colspan="2"><input type="button" onClick="save_notes();" value="Save &amp; Update Notes" /></td>
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
                        <td class="label"><label for="seeking">Job Seeking:</label></td>
                        <td class="field"><textarea id="seeking"><?php echo htmlspecialchars_decode(stripslashes($profile['seeking'])) ?></textarea></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="expected_salary">Expected Salary:</label></td>
                        <td class="field"><?php echo $branch[0]['currency']; ?>$ &nbsp;<input class="salary" type="text" id="expected_salary" value="<?php echo $profile['expected_salary'] ?>" /> - <input class="salary" type="text" id="expected_salary_end" value="<?php echo $profile['expected_salary_end'] ?>" /></td>
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
                        <td class="field"><textarea id="reason_for_leaving"><?php echo htmlspecialchars_decode(stripslashes($profile['reason_for_leaving'])) ?></textarea></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="current_position">Current position:</label></td>
                        <td class="field"><textarea id="current_position"><?php echo htmlspecialchars_decode(stripslashes($profile['current_position'])) ?></textarea></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="current_salary">Current Salary:</label></td>
                        <td class="field"><?php echo $branch[0]['currency']; ?>$ &nbsp;<input class="salary" type="text" id="current_salary" value="<?php echo $profile['current_salary'] ?>" /> - <input class="salary" type="text" id="current_salary_end" value="<?php echo $profile['current_salary_end'] ?>" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="notice_period">Notice Period:</label></td>
                        <td class="field"><input class="salary" type="text" id="notice_period" value="<?php echo $profile['notice_period'] ?>" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="extra_notes">Extra Notes/Remarks:</label></td>
                        <td class="field"><textarea id="extra_notes"><?php echo htmlspecialchars_decode(stripslashes($profile['notes'])) ?></textarea></td>
                    </tr>
                    <tr>
                        <td class="buttons_bar" colspan="2"><input type="button" onClick="save_notes();" value="Save &amp; Update Notes" /></td>
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
                                        $referrers_table->set($i+1, 1, '<a class="no_link" onClick="remove_referrer('. $referrer['email_addr']. ');">Remove</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a class="no_link" onClick="reward('. $referrer['email_addr']. ');">Reward</a>', '', 'cell actions');
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
                                        $referees_table->set($i+1, 1, '<a class="no_link" onClick="remove_referee('. $referee['email_addr']. ');">Remove</a>', '', 'cell actions');
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
                                <li>Only HTML (*.html, *.htm), Text (*.txt), Portable Document Format (*.pdf), Rich Text Format (*.rtf) or MS Word document (*.doc) with the file size of less than 1MB are allowed.</li>
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
        
        <?php
    }
}
?>