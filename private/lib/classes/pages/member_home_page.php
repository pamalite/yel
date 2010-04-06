<?php
require_once dirname(__FILE__). "/../../utilities.php";

class MemberHomePage extends Page {
    private $member = NULL;
    private $mysqli = NULL;
    
    function __construct($_session) {
        $this->member = new Member($_session['id'], $_session['sid']);
        $this->mysqli = Database::connect();
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_home_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/member_home_page.css">'. "\n";
    }
    
    public function insert_member_home_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/member_home_page.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->member->getId(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function get_completeness() {
        $query = "SELECT members.checked_profile, bank.has_bank, cv.has_resume, photo.has_photo 
                  FROM members, 
                  (SELECT COUNT(*) AS has_bank FROM member_banks WHERE member = '". $_POST['id']. "') bank, 
                  (SELECT COUNT(*) AS has_resume FROM resumes WHERE member = '". $_POST['id']. "' AND deleted = 'N') cv, 
                  (SELECT COUNT(*) AS has_photo FROM member_photos WHERE member = '". $_POST['id']. "') photo 
                  WHERE members.email_addr = '". $this->member->getId(). "'";
        $result = $this->mysqli->query($query);
        
        $response = array();
        $response['checked_profile'] = ($result[0]['checked_profile'] == 'Y') ? '1' : '0';
        $response['has_bank'] = ($result[0]['has_bank'] > 0) ? '1' : '0';
        $response['has_resume'] = ($result[0]['has_resume'] > 0) ? '1' : '0';
        $response['has_photo'] = ($result[0]['has_photo'] > 0) ? '1' : '0';
        
        return $response;
    }
    
    private function is_hrm_questions_filled() {
        $criteria = array(
            'columns' => "hrm_gender, hrm_ethnicity, hrm_birthdate", 
            'match' => "email_addr = '". $this->member->getId(). "'", 
            'limit' => "1"
        );
        
        $result = $this->member->find($criteria);
        if ((is_null($result[0]['hrm_gender']) || empty($result[0]['hrm_gender'])) ||
            (is_null($result[0]['hrm_ethnicity']) || empty($result[0]['hrm_ethnicity'])) || 
            (is_null($result[0]['hrm_birthdate']) || empty($result[0]['hrm_birthdate']))) {
            return false;
        }
        
        return true;
    }
    
    public function show() {
        $this->begin();
        $this->top_search('Home');
        $this->menu('member', 'home');
        
        $currency = Currency::getSymbolFromCountryCode($this->member->getCountry());
        $completeness_raw = $this->get_completeness();
        $completeness_percent = 0;
        $next_step = '';
        $total = 0;
        foreach ($completeness_raw as $key=>$value) {
            $total += $value;
            $completeness_percent = ($total / count($completeness_raw)) * 100;
            
            if ($value == 0 && empty($next_step)) {
                switch ($key) {
                    case 'checked_profile':
                        $next_step = '<a href="profile.php">Check Your Profile</a>';
                        break;
                    case 'has_bank':
                        $next_step = '<a href="profile.php">Enter a bank account in Profile</a>';
                        break;
                    case 'has_resume':
                        $next_step = '<a href="resumes.php">Upload a Resume</a>';
                        break;
                    case 'has_photo':
                        $next_step = '<a href="profile.php">Upload a photo in Profile</a>';
                        break;
                }
            }
        }
        
        $display_hrm_questions = 'display: none;';
        if (!$this->is_hrm_questions_filled()) {
            $display_hrm_questions = 'display: block;';
        }
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <table class="content">
            <tr>
                <td class="left_content">        
                    <div id="div_hrm_census" style="<?php echo $display_hrm_questions; ?>">
                        <div class="census_title">One-time Survey</div>
                        <div class="census_form">
                            Please help us answer the following <span style="text-decoration: underline; font-weight: bold;">one-time</span> questions as part of our on-going effort to serve you better.<br/>
                            <ol>
                                <li>
                                    Gender: 
                                    <select id="gender">
                                        <option value="">Please select one</option>
                                        <option value="" disabled>&nbsp;</option>
                                        <option value="m">Male</option>
                                        <option value="f">Female</option>
                                    </select>
                                </li>
                                <li>
                                    Ethnicity:
                                    <select id="ethnicity">
                                        <option value="">Please select one</option>
                                        <option value="" disabled>&nbsp;</option>
                                        <option value="malay">Malay</option>
                                        <option value="chinese">Chinese</option>
                                        <option value="indian">Indian</option>
                                        <option value="caucasian">Caucasian</option>
                                        <option value="others">Others (please specify)</option>
                                    </select>
                                    <input type="text" id="ethnicity_txt" value="" />
                                </li>
                                <li>
                                    Birth Date:
                                    <?php echo generate_dropdown('birthdate_day', '', 1, 31, '', 2, 'Day'); ?>
                                    <?php echo generate_month_dropdown('birthdate_month', '', 'Month'); ?>
                                    <input type="text" class="year" id="birthdate_year" value="year" maxlength="4" />
                                </li>
                            </ol>
                        </div>
                        <div class="buttons">
                            <input type="button" value="Save &amp; Close Forever" onClick="save_census_answers();" />
                        </div>
                    </div>

                    <div class="profile_completeness">
                        <div class="completeness_title">Profile Completeness:</div>
                        <div class="progress">
                            <div id="progress_bar" style="width: <?php echo $completeness_percent; ?>%;"></div>
                        </div>
                        <div id="percent"><?php echo $completeness_percent; ?>%</div>
                        <div class="progress_details">
                            Tip: <span id="details"><?php echo $next_step; ?></span>
                        </div>
                    </div>
                </td>
                <td class="right_content">
                    <div class="quick_search">
                        <div class="quick_search_title">Quick Search</div>
                        <ul class="quick_search_list">
                            <li><a class="no_link" onClick="quick_search_jobs('latest');">Latest jobs</a></li>
                            <li><a class="no_link" onClick="quick_search_jobs('top');">Top jobs</a></li>
                            <li>
                                <a class="no_link" onClick="quick_search_jobs('country', '<?php echo $this->member->getCountry(); ?>');">Jobs in <?php echo Country::getCountryFrom($this->member->getCountry()); ?></a>
                            </li>
                            <li>
                                Jobs in salary range:
                                <ul class="quick_search_list_inner">
                                    <li><a class="no_link" onClick="quick_search_jobs('salary', 8001, 0);">above <?php echo $currency; ?>$ 8,000</a></li>
                                    <li><a class="no_link" onClick="quick_search_jobs('salary', 7001, 8000);"><?php echo $currency; ?>$ 7,000 - 8,000</a></li>
                                    <li><a class="no_link" onClick="quick_search_jobs('salary', 6001, 7000);"><?php echo $currency; ?>$ 6,000 - 7,000</a></li>
                                    <li><a class="no_link" onClick="quick_search_jobs('salary', 5001, 6000);"><?php echo $currency; ?>$ 5,000 - 6,000</a></li>
                                    <li><a class="no_link" onClick="quick_search_jobs('salary', 4001, 5000);"><?php echo $currency; ?>$ 4,000 - 5,000</a></li>
                                    <li><a class="no_link" onClick="quick_search_jobs('salary', 3000, 4000);"><?php echo $currency; ?>$ 3,000 - 4,000</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        </table>
        
        <?php
    }
}
?>