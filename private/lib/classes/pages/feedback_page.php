<?php
require_once dirname(__FILE__). "/../../utilities.php";

class FeedbackPage extends Page {
    private $error_message = '';
    private $success = false;
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_feedback_css() {
        $this->insert_css('member_sign_up.css');
    }
    
    public function insert_feedback_scripts() {
        $this->insert_scripts('feedback.js');
    }
    
    public function insert_inline_scripts() {
        $this->header = str_replace('<!-- %inline_javascript% -->', 'var error_message = "'. $this->error_message. '";'. "\n", $this->header);
    }
    
    private function generateCountries($selected) {
        $countries_options_html = '<select class="field" id="country" name="country">'. "\n";
        
        $criteria = array(
            'columns' => "country_code, country"
        );
        $countries = Country::find($criteria);
        
        if (empty($selected) || is_null($selected) || $selected == '0') {
            $countries_options_html .= '<option value="0" selected>Please select a country.</option>'. "\n";
        } else {
            $countries_options_html .= '<option value="0">Please select a country.</option>'. "\n";
        }
        $countries_options_html .= '<option value="0" disabled>&nbsp;</option>'. "\n";
        
        foreach ($countries as $country) {
            if ($country['country_code'] != $selected) {
                $countries_options_html .= '<option value="'. $country['country_code']. '">'. $country['country']. '</option>'. "\n";
            } else {
                $countries_options_html .= '<option value="'. $country['country_code']. '" selected>'. $country['country']. '</option>'. "\n";
            }
        }
        
        $countries_options_html .= '</select>'. "\n";
        
        return $countries_options_html;
    }
    
    public function set_error($_error) {
        switch ($_error) {
            case '1':
                $this->error_message = 'The security code provided is invalid. Please try again.';
                break;
            default:
                $this->error_message = 'ALL fields are required to be filled.';
                break;
        }
    }
    
    public function set_success() {
        $this->success = true;
    }
    public function show($_session) {
        $this->begin();
        $this->top("Feedback");
        
        $page = file_get_contents(dirname(__FILE__). '/../../../html/feedback_page.html');
        
        if (!empty($this->error_message)) {
            $page = str_replace('%error_message%', $this->error_message, $page);
        }
        
        if ($this->success) {
            $page = str_replace('%is_success%', 'default', $page);
            $page = str_replace('%show_form%', 'none', $page);
        } else {
            $page = str_replace('%is_success%', 'none', $page);
            $page = str_replace('%show_form%', 'default', $page);
            
            $page = str_replace('%firstname%', $_session['firstname'], $page);
            $page = str_replace('%lastname%', $_session['lastname'], $page);
            $page = str_replace('%email_addr%', $_session['email_addr'], $page);
            $page = str_replace('%feedback%', $_session['feedback'], $page);
            $page = str_replace('%countries%', $this->generateCountries($_session['country']), $page);
        }
        
        echo $page;
    }
}
?>