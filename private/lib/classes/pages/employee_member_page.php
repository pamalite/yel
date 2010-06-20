<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). "/../htmltable.php";

class EmployeeMemberPage extends Page {
    private $employee = NULL;
    private $member = NULL;
    private $is_new = false;
    private $current_page = 'profile';
    
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
    
    public function show() {
        $this->begin();
        $this->top('Member');
        $this->menu_employee('members');
        
        $branch = $this->employee->getBranch();
        
        $raw_data = array();
        $profile = array();
        if (!$this->is_new) {
            // get profile
            $raw_data = $this->member->get();
            $profile = $raw_data[0];
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
                'citizenship' => $branch[0]['country']
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
                <li id="item_resumes" style="<?php echo ($this->current_page == 'resumes') ? $style : ''; ?>"><a class="menu" onClick="show_resumes();">Resumes</a></li>
                <li id="item_notes" style="<?php echo ($this->current_page == 'notes') ? $style : ''; ?>"><a class="menu" onClick="show_notes();">Notes</a></li>
                <li id="item_applications" style="<?php echo ($this->current_page == 'applications') ? $style : ''; ?>"><a class="menu" onClick="show_applications();">Applications</a></li>
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
        </div>
        
        <div id="member_resumes">
        </div>
        
        <div id="member_notes">
        </div>
        
        <div id="member_applications">
        </div>
        
        <div id="job">
            <form method="post"onSubmit="return false;">
                <input type="hidden" id="job_id" value="0" />
                <table id="job_form" class="job_form">
                    <tr>
                        <td class="label"><label for="job.title">Title:</label></td>
                        <td class="field"><input class="field" type="text" id="job.title" name="title" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="job.alternate_member">Alternate Employer:</label></td>
                        <td class="field"><input class="field" type="text" id="job.alternate_member" name="alternate_member" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="job.contact_carbon_copy">Extra Contacts:</label></td>
                        <td class="field"><input class="field" type="text" id="job.contact_carbon_copy" name="contact_carbon_copy" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="job.industry">Specialization:</label></td>
                        <td class="field"><?php $this->generate_industries('', 'job.industry'); ?></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="job.country">Country:</label></td>
                        <td class="field"><?php $this->generate_countries('', 'job.country'); ?></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="job.state">State/Province/Area:</label></td>
                        <td class="field"><input type="text" class="field" id="job.state" name="state" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="job.salary">Monthly Salary:</label></td>
                        <td class="field">
                            <?php echo $branch[0]['currency']; ?>$ <input class="salary" type="text" id="job.salary" name="salary" />&nbsp;-&nbsp;<input class="salary" type="text" id="job.salary_end" name="salary_end" /><br>
                            <input type="checkbox" id="job.salary_negotiable" name="salary_negotiable" /> <label for="job.salary_negotiable">Negotiable</label><br/>
                            <p class="small_notes">This account allows you to create job ads with salary in <span id="job.member.currency_2"></span> only. If you wish to create job ads with salary in other currencies, please log into the relevant accounts.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="label center" colspan="2"><label for="job.description">Description:</label></td>
                    </tr>
                    <tr>
                        <td class="field center" colspan="2"><textarea id="job.description" name="description" class="job_description"></textarea></td>
                    </tr>
                </table>
                <div class="buttons_bar">
                    <input class="button" type="button" value="Cancel" onClick="show_jobs();" />
                    <input class="button" type="button" value="Publish" onClick="save_job();" />
                </div>
            </form>
        </div>
        
        <!-- popup windows goes here -->
        
        <?php
    }
}
?>