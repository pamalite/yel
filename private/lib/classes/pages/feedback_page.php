<?php
require_once dirname(__FILE__). "/../../utilities.php";

class FeedbackPage extends Page {
    private $error_message = '';
    private $success = false;
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_feedback_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/feedback.css">'. "\n";
    }
    
    public function insert_feedback_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/feedback.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var error_message = "'. $this->error_message. '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function generateCountries($selected) {
        //$countries = Country::get_all_with_display();
        $countries = Country::get_all();
        
        echo '<select class="field" id="country" name="country">'. "\n";
        if (empty($selected) || is_null($selected) || $selected == '0') {
            echo '<option value="0" selected>Please select a country.</option>'. "\n";
        } else {
            echo '<option value="0">Please select a country.</option>'. "\n";
        }
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        
        foreach ($countries as $country) {
            if ($country['country_code'] != $selected) {
                echo '<option value="'. $country['country_code']. '">'. $country['country']. '</option>'. "\n";
            } else {
                echo '<option value="'. $country['country_code']. '" selected>'. $country['country']. '</option>'. "\n";
            }
        }
        
        echo '</select>'. "\n";
    }
    
    public function set_error($_error) {
        switch ($_error) {
            case '1':
                $this->error_message = 'The security code provided is invalid. Please try again.';
                break;
            default:
                $this->error_message = 'An unknown error occured.';
                break;
        }
    }
    
    public function set_success() {
        $this->success = true;
    }
    public function show($_session) {
        $this->begin();
        $this->top("Yellow Elevator&nbsp;&nbsp;<span style=\"color: #FC8503;\">Feedback</span>");
        
        if ($this->success) {
            ?>
            <div style="text-align: center; font-size: 12pt; padding-top: 100px;">
                Your feedback had been received. Thank you!
            </div>
            <?php
        } else {
            ?>
            <div id="div_status" class="status">
                <span id="span_status" class="status"></span>
            </div>
            <div class="profile">
                <form id="profile" method="post" action="feedback_action.php" onSubmit="return validate();">
                    <table class="profile_form">
                        <tr>
                            <td class="title" colspan="2">Feedback Form</td>
                        </tr>
                        <tr>
                            <td class="instruction" colspan="2">Please fill up all fields, and fields marked with * are mandatory.</td>
                        </tr>
                        <tr>
                            <td class="label"><label for="firstname">* First Name / Given Names:</label></td>
                            <td class="field"><input class="field" type="text" id="firstname" name="firstname" value="<?php echo $_session['firstname'] ?>" /></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="lastname">* Last Name / Surname:</label></td>
                            <td class="field">
                                <input class="field" type="text" id="lastname" name="lastname" value="<?php echo $_session['lastname'] ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="email_addr">* E-mail Address:</label></td>
                            <td class="field">
                                <input class="field" type="text" id="email_addr" name="email_addr" value="<?php echo $_session['email_addr'] ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="country">* Country:</label></td>
                            <td class="field">
                                <?php $this->generateCountries($_session['country']) ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="feedback">* Feedback:</label></td>
                            <td class="field">
                                <textarea class="field" id="feedback" name="feedback"><?php echo $_session['feedback'] ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td class="title" colspan="2">* Security</td>
                        </tr>
                        <tr>
                            <td class="instruction" colspan="2">Please type the characters as shown on the left.</td>
                        </tr>
                        <tr>
                            <td class="label"><img src="members/CaptchaSecurityImages.php?characters=6" /></td>
                            <td class="field"><input class="field" type="text" id="security_code" name="security_code" /></td>
                        </tr>
                        <tr>
                            <td class="buttons_bar" colspan="2">
                                <input type="submit" id="submit" value="Send Feedback" />
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <?php
        }
    }
}
?>