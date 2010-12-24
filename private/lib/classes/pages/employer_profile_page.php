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
        echo 'var id = "'. $this->employer->getId(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->support($this->employer->getId());
        $this->top('Profile');
        $this->menu('employer', 'profile');
        
        $branch = $this->employer->getAssociatedBranch();
        $profile = desanitize($this->employer->get());
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div class="change_instructions">
            Please <a href="mailto: sales.<?php echo strtolower($branch[0]['country']); ?>@yellowelevator.com">let us know</a> if the following details need to be updated.
        </div>
        
        <div class="profile">
            <form onSubmit="return">
            <table class="profile_form">
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
                    <td class="title" colspan="2">Contact Details</td>
                </tr>
                <tr>
                    <td class="label"><label for="email">E-mail Address:</label></td>
                    <td class="field"><?php echo $profile[0]['email_addr']; ?></td>
                </tr>
                <tr>
                    <td class="label"><label for="name">Company/Business Name:</label></td>
                    <td class="field"><?php echo $profile[0]['name']; ?></td>
                </tr>
                <tr>
                    <td class="label"><label for="contact_person">Contact Person:</label></td>
                    <td class="field"><?php echo $profile[0]['contact_person']; ?></td>
                </tr>
                <tr>
                    <td class="label"><label for="phone_num">Contact Number:</label></td>
                    <td class="field"><?php echo $profile[0]['phone_num']; ?></td>
                </tr>
                <tr>
                    <td class="label"><label for="fax_num">Fax Number:</label></td>
                    <td class="field"><?php echo $profile[0]['fax_num']; ?></td>
                </tr>
                <tr>
                    <td class="label"><label for="address">Mailing Address:</label></td>
                    <td class="field"><?php echo $profile[0]['address']; ?></td>
                </tr>
                <tr>
                    <td class="label"><label for="state">State/Province:</label></td>
                    <td class="field"><?php echo $profile[0]['state']; ?></td>
                </tr>
                <tr>
                    <td class="label"><label for="zip">Zip/Postal Code:</label></td>
                    <td class="field"><?php echo $profile[0]['zip']; ?></td>
                </tr>
                <tr>
                    <td class="label"><label for="country">Country:</label></td>
                    <td class="field"><?php echo Country::getCountryFrom($this->employer->getCountryCode()); ?></td>
                </tr>
                <tr>
                    <td class="label"><label for="website_url">Web-site:</label></td>
                    <td class="field"><?php echo $profile[0]['website_url']; ?></td>
                </tr>
                <tr>
                    <td class="label"><label for="about">Business Summary:</label></td>
                    <td class="field"><?php echo $profile[0]['about']; ?></td>
                </tr>
            </table>
            </form>
        </div>
        <?php
    }
}
?>