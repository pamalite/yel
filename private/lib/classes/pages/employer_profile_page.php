<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployerProfilePage extends Page {
    private $employer = NULL;
    
    function __construct($_session) {
        $this->employer = new Employer($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employer_profile_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employer_profile.css">'. "\n";
    }
    
    public function insert_employer_profile_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employer_profile.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employer->id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function generateCountries($selected) {
        $countries = Country::getAll();
        
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
        $this->support();
        $this->top($this->employer->get_name(). "&nbsp;&nbsp;<span style=\"color: #FC8503;\">Profile</span>");
        $this->menu('employer', 'profile');
        
        $profile = desanitize($this->employer->get());
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div class="profile">
            <form id="profile" method="post" onSubmit="return false;">
                <table class="profile_form">
                    <tr>
                        <td class="buttons_bar" colspan="2"><input type="button" id="save" value="Save &amp; Update Profile" /></td>
                    </tr>
                    <tr>
                        <td class="label">Company/Business Registration No.:</td>
                        <td class="field"><?php echo $profile[0]['license_num']; ?></td>
                    </tr>
                    <tr>
                        <td class="title" colspan="2">Sign In Details</td>
                    </tr>
                    <tr>
                        <td class="label">User ID:</td>
                        <td class="field">
                            <?php echo $profile[0]['id']; ?>
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
                        <td class="label"><label for="email">E-mail Address:</label></td>
                        <td class="field"><input class="field" type="text" id="email" name="email" value="<?php echo $profile[0]['email_addr']; ?>" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="name">Company/Business Name:</label></td>
                        <td class="field"><input class="field" type="text" id="name" name="name" value="<?php echo $profile[0]['name']; ?>" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="contact_person">Contact Person:</label></td>
                        <td class="field"><input class="field" type="text" id="contact_person" name="contact_person" value="<?php echo $profile[0]['contact_person']; ?>" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="phone_num">Contact Number:</label></td>
                        <td class="field"><input class="field" type="text" id="phone_num" name="phone_num" value="<?php echo $profile[0]['phone_num']; ?>" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="fax_num">Fax Number:</label></td>
                        <td class="field"><input class="field" type="text" id="fax_num" name="fax_num" value="<?php echo $profile[0]['fax_num']; ?>" /></td>
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
                            <?php 
                                //$this->generateCountries($profile[0]['country']) 
                                echo $this->employer->get_country();
                                echo '<input type="hidden" id="country" name="country" value="' . $this->employer->get_country_code(). '"/>';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="website_url">Web-site:</label></td>
                        <td class="field"><input class="field" type="text" id="website_url" name="website_url" value="<?php echo $profile[0]['website_url']; ?>" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="about">Business Summary:</label></td>
                        <td class="field"><textarea id="about" name="about"><?php echo $profile[0]['about']; ?></textarea></td>
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