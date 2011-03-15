<?php
require_once dirname(__FILE__). "/../../utilities.php";

session_start();

class ContactPage extends Page {
    private $has_captcha_error = false;
    
    function __construct($_has_captcha_error = false) {
        parent::__construct();
        $this->has_captcha_error = $_has_captcha_error;
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_contact_css() {
        $this->insert_css('contact.css');
    }
    
    public function insert_contact_scripts() {
        $this->insert_scripts('contact_page.js');
    }
    
    public function insert_inline_scripts() {
        $script = 'var has_captcha_error = false;'. "\n";
        if ($this->has_captcha_error) {
            $script = 'var has_captcha_error = true;'. "\n";
        }
        
        $this->header = str_replace('<!-- %inline_javascript% -->', $script, $this->header);
    }
    
    public function show() {
        $this->begin();
        $this->top("Contact Us");
        
        $page = file_get_contents(dirname(__FILE__). '/../../../html/contact_page.html');
        $page = str_replace('%root%', $this->url_root, $page);
        
        $page = str_replace('%contact_name%' , $_SESSION['yel']['contact_us']['contact_name'], $page);
        $page = str_replace('%company_name%' , $_SESSION['yel']['contact_us']['company_name'], $page);
        $page = str_replace('%email_addr%' , $_SESSION['yel']['contact_us']['email_addr'], $page);
        $page = str_replace('%phone_num%' , $_SESSION['yel']['contact_us']['phone_num'], $page);
        $page = str_replace('%subject%' , $_SESSION['yel']['contact_us']['subject'], $page);
        $page = str_replace('%message%' , $_SESSION['yel']['contact_us']['message'], $page);
        
        switch ($_SESSION['yel']['contact_us']['kind']) {
            case 'general':
                $page = str_replace('%general_selected%' , 'selected', $page);
                break;
            case 'tech':
                $page = str_replace('%tech_selected%' , 'selected', $page);
                break;
            case 'billing':
                $page = str_replace('%billing_selected%' , 'selected', $page);
                break;
            case 'others':
                $page = str_replace('%others_selected%' , 'selected', $page);
                break;
            default:
                $page = str_replace('%general_selected%' , 'selected', $page);
                break;
        }
        $page = str_replace('%general_selected%' , '', $page);
        $page = str_replace('%tech_selected%' , '', $page);
        $page = str_replace('%billing_selected%' , '', $page);
        $page = str_replace('%others_selected%' , '', $page);
        
        echo $page;
    }
}
?>