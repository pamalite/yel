<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployerProfilePage extends Page {
    private $employer = NULL;
    
    function __construct($_session) {
        parent::__construct();
        
        $this->employer = new Employer($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employer_profile_css() {
        $this->insert_css('employer_profile.css');
    }
    
    public function insert_employer_profile_scripts() {
        $this->insert_scripts('employer_profile.js');
    }
    
    public function insert_inline_scripts() {
        $script = 'var id = "'. $this->employer->getId(). '";'. "\n";
        
        $this->header = str_replace('<!-- %inline_javascript% -->', $script, $this->header);
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
    
    public function show() {
        $this->begin();
        $this->support($this->employer->getId());
        $this->top('Company Profile');
        $this->menu('employer', 'profile', $this->employer->isYEConnectOnly());
        
        $branch = $this->employer->getAssociatedBranch();
        $profile = desanitize($this->employer->get());
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div class="change_instructions">
            Please <a href="mailto: sales.<?php echo strtolower($branch[0]['country']); ?>@yellowelevator.com">let us know</a> if either the Business Registration No., the Business Name, or both needs to be updated.
        </div>
        
        <div class="profile">
            <form onSubmit="return false;">
            <table class="profile_form">
                <tr>
                    <td class="label">Company/Business Registration No.:</td>
                    <td class="field"><?php echo $profile[0]['license_num']; ?></td>
                </tr>
                <tr>
                    <td class="label"><label for="name">Company/Business Name:</label></td>
                    <td class="field"><?php echo $profile[0]['name']; ?></td>
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
                    <td class="label"><label for="password">Password:</label></td>
                    <td class="field"><input type="password" id="password" value="" /></td>
                </tr>
                <tr>
                    <td class="label"><label for="password2">Password Again:</label></td>
                    <td class="field">
                        <input type="password" id="password2" value="" />
                        <input type="button" value="Save Password" onClick="save_password();" />
                    </td>
                </tr>
                <tr>
                    <td class="title" colspan="2">Contact Details<br/><span class="note">Fields marked with * indicates cannot be left empty.</span></td>
                </tr>
                <tr>
                    <td class="label"><label for="email">* HR Contact Emails:</label></td>
                    <td class="field">
                        <input type="text" id="email_addr" class="field" value="<?php echo $profile[0]['email_addr']; ?>" />
                    </td>
                </tr>
                <tr>
                    <td class="label"><label for="contact_person">* HR Contacts:</label></td>
                    <td class="field">
                        <input type="text" id="contact_person" class="field" value="<?php echo $profile[0]['contact_person']; ?>" />
                    </td>
                </tr>
                <tr>
                    <td class="label"><label for="phone_num">* HR Contact Numbers:</label></td>
                    <td class="field">
                        <input type="text" id="phone_num" class="field" value="<?php echo $profile[0]['phone_num']; ?>" />
                    </td>
                </tr>
                <tr>
                    <td class="label"><label for="fax_num">Fax Number:</label></td>
                    <td class="field">
                        <input type="text" id="fax_num" class="field" value="<?php echo $profile[0]['fax_num']; ?>" />
                    </td>
                </tr>
                <tr>
                    <td class="label"><label for="address">Mailing Address:</label></td>
                    <td class="field">
                        <textarea id="address"><?php echo stripslashes($profile[0]['address']); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td class="label"><label for="state">State/Province:</label></td>
                    <td class="field">
                        <input type="text" id="state" class="field" value="<?php echo $profile[0]['state']; ?>" />
                    </td>
                </tr>
                <tr>
                    <td class="label"><label for="zip">* Zip/Postal Code:</label></td>
                    <td class="field">
                        <input type="text" id="zip" class="field" value="<?php echo $profile[0]['zip']; ?>" />
                    </td>
                </tr>
                <tr>
                    <td class="label"><label for="country">* Country:</label></td>
                    <td class="field">
                        <?php echo $this->generate_countries($this->employer->getCountryCode()); ?>
                    </td>
                </tr>
                <tr>
                    <td class="label"><label for="website_url">Web-site:</label></td>
                    <td class="field">
                        <input type="text" id="website_url" class="field" value="<?php echo $profile[0]['website_url']; ?>" />
                    </td>
                </tr>
                <tr>
                    <td class="label"><label for="about">Business Summary:</label></td>
                    <td class="field">
                        <textarea id="summary"><?php echo stripslashes($profile[0]['about']); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td class="buttons_bar" colspan="2"><input type="button" onClick="save_profile();" value="Save &amp; Update Contact Details" /></td>
                </tr>
            </table>
            </form>
        </div>
        <?php
    }
}
?>