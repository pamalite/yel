<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployeeProfilePage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_profile_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_profile.css">'. "\n";
    }
    
    public function insert_employee_profile_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_profile.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function generateCountries($selected) {
        $countries = Country::get_all_with_display();
        
        echo '<select class="field" id="country" name="country">'. "\n";
        
        foreach ($countries as $country) {
            if ($country['country_code'] != $selected) {
                echo '<option value="'. $country['country_code']. '">'. $country['country']. '</option>'. "\n";
            } else {
                echo '<option value="'. $country['country_code']. '" selected>'. $country['country']. '</option>'. "\n";
            }
        }
        
        echo '</select>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_employee($this->employee->get_name(). " - Profile");
        $this->menu_employee($this->clearances, 'profile');
        
        $branch = $this->employee->get_branch();
        $business_groups = $this->employee->get_business_groups();
        $user_id = $this->employee->get_user_id();
        $profile = desanitize($this->employee->get());
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div class="profile">
            <form id="profile" method="post" onSubmit="return false;">
                <table class="profile_form">
                    <tr>
                        <td  class="buttons_bar" colspan="2"><input type="button" id="save" value="Save &amp; Update Profile" /></td>
                    </tr>
                    <tr>
                        <td class="label">Yellow Elevator Branch:</td>
                        <td class="field"><?php echo $branch[0]['branch']. ' ('. $branch[0]['country']. ')'; ?></td>
                    </tr>
                    <tr>
                        <td class="label">Business Group:</td>
                        <td class="field">
                        <?php 
                            $i = 0;
                            foreach ($business_groups as $business_group) {
                                echo $business_group['group'];
                                
                                if ($i < count($business_groups)-1) {
                                    echo "<br/>";
                                }
                                
                                $i++;
                            } 
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Designation:</td>
                        <td class="field"><?php echo $profile[0]['designation']; ?></td>
                    </tr>
                    <tr>
                        <td class="title" colspan="2">Sign In Details</td>
                    </tr>
                    <tr>
                        <td class="label">User ID:</td>
                        <td class="field">
                            <?php echo $user_id; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="password">New Password:</label></td>
                        <td class="field"><input class="field" type="password" id="password" name="password" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="password_confirm">Confirm New Password:</label></td>
                        <td class="field"><input class="field" type="password" id="password_confirm" name="password_confirm" /></td>
                    </tr>
                    <tr>
                        <td class="title" colspan="2">Contact Details</td>
                    </tr>
                    <tr>
                        <td class="label">E-mail Address:</td>
                        <td class="field"><?php echo $profile[0]['email_addr']; ?></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="alternate_email">Alternate E-mail Address:</label></td>
                        <td class="field"><input class="field" type="text" id="alternate_email" name="alternate_email" value="<?php echo $profile[0]['alternate_email']; ?>" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="firstname">First Names:</label></td>
                        <td class="field"><input class="field" type="text" id="firstname" name="firstname" value="<?php echo $profile[0]['firstname']; ?>" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="lastname">Last Name:</label></td>
                        <td class="field"><input class="field" type="text" id="lastname" name="lastname" value="<?php echo $profile[0]['lastname']; ?>" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="phone_num">Telephone Number:</label></td>
                        <td class="field"><input class="field" type="text" id="phone_num" name="phone_num" value="<?php echo $profile[0]['phone_num']; ?>" /></td>
                    </tr>
                        <tr>
                            <td class="label"><label for="mobile">Mobile Number:</label></td>
                            <td class="field"><input class="field" type="text" id="mobile" name="mobile" value="<?php echo $profile[0]['mobile']; ?>" /></td>
                        </tr>
                    <tr>
                        <td class="label"><label for="address">Mailing Address:</label></td>
                        <td class="field"><textarea id="address" name="address"><?php echo $profile[0]['address']; ?></textarea></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="state">State/Province:</label></td>
                        <td class="field"><input class="field" type="text" id="state" name="state" value="<?php echo $profile[0]['state']; ?>" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="zip">Zip/Postal Code:</label></td>
                        <td class="field"><input class="field" type="text" id="zip" name="zip" value="<?php echo $profile[0]['zip']; ?>" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="country">Country:</label></td>
                        <td class="field">
                            <?php $this->generateCountries($profile[0]['country']) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="buttons_bar" colspan="2"><input type="button" id="save_1" value="Save &amp; Update Profile" /></td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }
}
?>