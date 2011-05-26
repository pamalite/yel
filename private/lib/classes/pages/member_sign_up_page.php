<?php
require_once dirname(__FILE__). '/../../../config/job_profile.inc';
require_once dirname(__FILE__). "/../../utilities.php";

class MemberSignUpPage extends Page {
    private $member = '';
    private $error_message = '';
    
    function __construct($_member = '') {
        parent::__construct();
        
        if (!empty($_member)) {
            $this->member = desanitize($_member);
        }
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_sign_up_css() {
        $this->insert_css('member_sign_up.css');
    }
    
    public function insert_member_sign_up_scripts() {
        $this->insert_scripts('member_sign_up.js');
    }
    
    public function insert_inline_scripts() {
        // TOOD: Any inline JS goes here
    }
    
    private function generate_countries($_selected, $_name = 'country') {
        $countries_options_html = '';
        
        $criteria = array(
            'columns' => "country_code, country", 
            'order' => "country"
        );
        $countries = Country::find($criteria);
        
        $countries_options_html = '<select class="field" id="'. $_name. '" name="'. $_name. '">'. "\n";
        if (empty($_selected) || is_null($_selected) || $_selected == '0') {
            $countries_options_html .= '<option value="0" selected>Please select a county.</option>'. "\n";    
        } else {
            $countries_options_html .= '<option value="0">Please select a country.</option>'. "\n";
        }
        
        $countries_options_html .= '<option value="0">&nbsp;</option>';
        foreach ($countries as $country) {
            if ($country['country_code'] != $_selected) {
                $countries_options_html .= '<option value="'. $country['country_code']. '">'. $country['country']. '</option>'. "\n";
            } else {
                $countries_options_html .= '<option value="'. $country['country_code']. '" selected>'. $country['country']. '</option>'. "\n";
            }
        }
        
        $countries_options_html .= '</select>'. "\n";
        
        return $countries_options_html;
    }
    
    private function generate_industries($_id, $_selecteds, $_is_multi=false) {
        $industries_options_html = '';
        
        $criteria = array('columns' => "id, industry, parent_id");
        $industries = Industry::find($criteria);
        
        if ($_is_multi) {
            $industries_options_html = '<select class="multiselect" id="'. $_id. '" name="'. $_id. '[]" multiple>'. "\n";
        } else {
            $industries_options_html = '<select class="field" id="'. $_id. '" name="'. $_id. '">'. "\n";
        }
        
        $options_str = '';
        $has_selected = false;
        foreach ($industries as $industry) {
            $css_class = '';
            $spacing = '';
            if (is_null($industry['parent_id'])) {
                $css_class = 'class = "main_industry"';
            } else {
                $spacing = '&nbsp;&nbsp;&nbsp;';
            }
            
            $selected = false;
            if (in_array($industry['id'], $_selecteds)) {
                $selected = true;
                $has_selected = true;
            }
            
            if ($selected) {
                $options_str .= '<option value="'. $industry['id']. '" '. $css_class. ' selected>'. $spacing. $industry['industry']. '</option>'. "\n";
            } else {
                $options_str .= '<option value="'. $industry['id']. '" '. $css_class. '>'. $spacing. $industry['industry']. '</option>'. "\n";
            }
        }
        
        $industries_options_html .= '<option value="0" '. (($has_selected) ? '' : 'selected'). '>Select a Specialization</option>'. "\n";
        $industries_options_html .= '<option value="0" disabled>&nbsp;</option>'. "\n";
        $industries_options_html .= $options_str;
        $industries_options_html .= '</select>'. "\n";
        
        return $industries_options_html;
    }
    
    private function generate_employer_description($_id, $_selected) {
        $emp_desc_options_html = '';
        
        $descs = $GLOBALS['emp_descs'];
        
        $emp_desc_options_html = '<select class="field" id="'. $_id. '" name="'. $_id. '">'. "\n";
        if (empty($_selected) || is_null($_selected) || $_selected < 0) {
            $emp_desc_options_html .= '<option value="0" selected>Please select one</option>'. "\n";    
        } else {
            $emp_desc_options_html .= '<option value="0">Please select One</option>'. "\n";
        }
        
        $emp_desc_options_html .= '<option value="0" disabled>&nbsp;</option>'. "\n";
        foreach ($descs as $i=>$desc) {
            if ($i != $_selected) {
                $emp_desc_options_html .= '<option value="'. $i. '">'. $desc. '</option>'. "\n";
            } else {
                $emp_desc_options_html .= '<option value="'. $i. '" selected>'. $desc. '</option>'. "\n";
            }
        }
        
        $emp_desc_options_html .= '</select>'. "\n";
        
        return $emp_desc_options_html;
    }
    
    public function show($_session) {
        $this->begin();
        $this->top("Sign Up");
        
        $page = file_get_contents(dirname(__FILE__). '/../../../html/member_sign_up_page.html');
        $page = str_replace('%root%', $this->url_root, $page);
        $page = str_replace('%work_from_month%', generate_month_dropdown('work_from_month', ''), $page);
        $page = str_replace('%work_to_month%', generate_month_dropdown('work_to_month', ''), $page);
        $page = str_replace('%emp_desc%', $this->generate_employer_description('emp_desc', -1), $page);
        $page = str_replace('%industries%', $this->generate_industries('emp_specialization', array()), $page);
        $page = str_replace('%birthdate_day%', generate_dropdown('birthdate_day', '', 1, 31, '', 2, 'Day'), $page);
        $page = str_replace('%birthdate_month%', generate_month_dropdown('birthdate_month', '', 'Month'), $page);
        
        echo $page;
    }
}

?>