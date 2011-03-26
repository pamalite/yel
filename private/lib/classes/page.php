<?php
require_once dirname(__FILE__). "/../../config/common.inc";

class Page {
    protected $url_root;
    protected $header;
    
    function __construct() {
        $this->url_root = $GLOBALS['protocol']. '://'. $GLOBALS['root'];
    }
    
    public function insert_css($_css_file_path) {
        if (!empty($_css_file_path)) {
            if (is_array($_css_file_path)) {
                $css = '';
                foreach ($_css_file_path as $path) {
                    $css .= '<link rel="stylesheet" type="text/css" href="'. $this->url_root. '/common/css/'. $path. '">'. "\n";
                }
                $this->header = str_replace('%page_css%', $css. "\n", $this->header);
            } else {
                $this->header = str_replace('%page_css%', '<link rel="stylesheet" type="text/css" href="'. $this->url_root. '/common/css/'. $_css_file_path. '">'. "\n", $this->header);
            }
        } else {
            $this->header = str_replace('%page_css%', ''. "\n", $this->header);
        }
    }

    public function insert_scripts($_js_file_path) {
        if (!empty($_js_file_path)) {
            if (is_array($_js_file_path)) {
                $js = '';
                foreach ($_js_file_path as $path) {
                    $js .= '<script type="text/javascript" src="'. $this->url_root. '/common/scripts/'. $path. '"></script>'. "\n";
                }
                $this->header = str_replace('%page_javascript%', $js. "\n", $this->header);
            } else {
                $this->header = str_replace('%page_javascript%', '<script type="text/javascript" src="'. $this->url_root. '/common/scripts/'. $_js_file_path. '"></script>'. "\n", $this->header);
            }
        } else {
            $this->header = str_replace('%page_javascript%', ''. "\n", $this->header);
        }
    }

    public function header($hashes = "") {
        $override_title = (!isset($hashes['override_title'])) ? false : true;
        $title = (!isset($hashes['title'])) ? '' : $hashes['title'];
        
        $this->header = file_get_contents(dirname(__FILE__). '/../../html/header_common.html');
        $this->header = str_replace('%root%', $this->url_root, $this->header);
        
        if ($override_title) {
            $this->header = str_replace('%page_title%', $title, $this->header);
        } else {
            $this->header = str_replace('%page_title%', $GLOBALS['COMPANYNAME']. ' - '.$title, $this->header);
        }
    }
    
    public function begin() {
        echo $this->header;
    }
    
    public function footer() {
        $footer = file_get_contents(dirname(__FILE__). '/../../html/footer_common.html');
        $footer = str_replace('%copyright_year%', date('Y'), $footer);
        $footer = str_replace('%root%', $this->url_root, $footer);
        
        echo $footer;
    }
    
    protected function top_welcome() {
        $top = file_get_contents(dirname(__FILE__). '/../../html/top_welcome.html');
        $top = str_replace('%root%', $this->url_root, $top);
        
        if (isset($_SESSION['yel']['employer']) && 
            !empty($_SESSION['yel']['employer']['id']) && 
            !is_null($_SESSION['yel']['employer']['id'])) {
            $top = str_replace('%not_logged_in%', 'none', $top);
            $top = str_replace('%employer_logged_in%', 'inline', $top);
            $top = str_replace('%member_logged_in%', 'none', $top);
            $top = str_replace('%employee_logged_in%', 'none', $top);
        } elseif (isset($_SESSION['yel']['member']) && 
                  !empty($_SESSION['yel']['member']['id']) && 
                  !is_null($_SESSION['yel']['member']['id'])) {
            $top = str_replace('%not_logged_in%', 'none', $top);
            $top = str_replace('%employer_logged_in%', 'none', $top);
            $top = str_replace('%member_logged_in%', 'inline', $top);
            $top = str_replace('%employee_logged_in%', 'none', $top);
        } elseif (isset($_SESSION['yel']['employee']) && 
                  !empty($_SESSION['yel']['employee']['id']) && 
                  !is_null($_SESSION['yel']['employee']['id'])) {
            $top = str_replace('%not_logged_in%', 'none', $top);
            $top = str_replace('%employer_logged_in%', 'none', $top);
            $top = str_replace('%member_logged_in%', 'none', $top);
            $top = str_replace('%employee_logged_in%', 'inline', $top);
        } else {
            $top = str_replace('%not_logged_in%', 'inline', $top);
            $top = str_replace('%employer_logged_in%', 'none', $top);
            $top = str_replace('%member_logged_in%', 'none', $top);
            $top = str_replace('%employee_logged_in%', 'none', $top);
        }
        
        echo $top;
    }
    
    protected function top($_page_title) {
        $top = file_get_contents(dirname(__FILE__). '/../../html/top_common.html');
        
        $top = str_replace('%root%', $this->url_root, $top);
        $top = str_replace('%page_title%', $_page_title, $top);
        
        if (isset($_SESSION['yel']['employee']) &&
            !empty($_SESSION['yel']['employee']['id']) && 
            !empty($_SESSION['yel']['employee']['sid']) && 
            !empty($_SESSION['yel']['employee']['hash'])) {
            
            $top = str_replace('%logo_visible%', 'none', $top);
            $top = str_replace('%employee_help_visible%', 'default', $top);
        } else {
            $top = str_replace('%logo_visible%', 'default', $top);
            $top = str_replace('%employee_help_visible%', 'none', $top);
        }
        
        echo $top;
    }
    
    protected function top_search($_page_title) {
        // get the employers
        $criteria = array(
            'columns' => 'employers.id, employers.name, COUNT(jobs.id) AS job_count', 
            'joins' => 'employers ON employers.id = jobs.employer',
            // 'match' => "jobs.expire_on >= CURDATE() AND jobs.closed = 'N'", 
            'group' => 'employers.id', 
            'order' => 'employers.name ASC'
        );
        $job = new Job();
        $employers = $job->find($criteria);
        if ($employers === false) {
            $employers = array();
        }
        
        // get the industries
        $industries = Industry::getIndustriesFromJobs(true);
        
        // get the countries
        $criteria = array(
            'columns' => "countries.country_code, countries.country, COUNT(jobs.id) AS job_count", 
            'joins' => "countries ON countries.country_code = jobs.country",
            // 'match' => "jobs.expire_on >= CURDATE() AND jobs.closed = 'N'", 
            'group' => "countries.country_code", 
            'order' => "countries.country ASC"
        );
        
        $job = new Job();
        $countries = $job->find($criteria);
        
        $top = file_get_contents(dirname(__FILE__). '/../../html/top_search.html');
        $top = str_replace('%root%', $this->url_root, $top);
        
        $employers_options = '';
        foreach ($employers as $emp) {
            $employers_options .= '<option value="'. $emp['id'].'">'. desanitize($emp['name']);
            
            if ($emp['job_count'] > 0) {
                $employers_options .= '&nbsp;('. $emp['job_count']. ')';
            }
            $employers_options .= '</option>'. "\n";
        }
        $top = str_replace('<!-- %employers_options% -->', $employers_options, $top);
        
        $industries_options = '';
        foreach ($industries as $industry) {
            $industries_options .= '<option value="'. $industry['id']. '">'. $industry['industry'];
            
            if ($industry['job_count'] > 0) {
                $industries_options .= '&nbsp;('. $industry['job_count']. ')';
            }
            $industries_options .= '</option>'. "\n";
        }
        $top = str_replace('<!-- %industries_options% -->', $industries_options, $top);
        
        $countries_options = '';
        foreach ($countries as $a_country) {
            $countries_options .= '<option value="'. $a_country['country_code']. '">'. $a_country['country'];
            
            if ($a_country['job_count'] > 0) {
                $countries_options .= '&nbsp;('. $a_country['job_count']. ')';
            }
            $countries_options .= '</option>'. "\n";
        }
        $top = str_replace('<!-- %countries_options% -->', $countries_options, $top);
        
        echo $top;
    }
    
    protected function top_prs($page_title) {
        ?>
        <div class="top">
            <table class="top">
                <tr>
                    <td rowspan="3" class="logo">
                        <a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/index.php">
                            <img name="logo" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/logos/top.jpg" />
                        </a>
                    </td>
                    <td><div class="page_title"><?php echo desanitize($page_title) ?></div></td>
                </tr>
                <tr>
                    <td>
                        <form method="post" action="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/prs/search_resume.php" onSubmit="return prs_verify_mini();">
                            <div class="mini_search">
                                <span id="mini_industry_drop_down"></span>
                                &nbsp;
                                <input type="text" name="keywords" id="mini_keywords">
                                &nbsp;
                                <input id="mini_search_button" type="submit" value="Search Resumes">
                                &nbsp;
                                <!--input type="checkbox" name="use_exact" id="use_exact" value="1" /><label for="use_exact">Exact</label-->
                                <input type="radio" name="use_mode" id="or_mode" value="or" checked /><label for="or_mode">OR</label>
                                <input type="radio" name="use_mode" id="and_mode" value="and" /><label for="and_mode">AND</label>
                            </div>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    protected function menu($type, $page = '') {
        $menu = '';
        $selected = '#CCCCCC';
        
        if ($type == 'employer') {
            $menu = file_get_contents(dirname(__FILE__). '/../../html/menu_employers.html');
            $menu = str_replace('%root%', $this->url_root, $menu);
            
            if ($page == 'resumes') {
                $menu = str_replace('%employer_resumes%', $selected, $menu);
            } else {
                $menu = str_replace('%employee_resumes%', 'none', $menu);
            }
            
            if ($page == 'resumes') {
                $menu = str_replace('%employer_resumes%', $selected, $menu);
            } else {
                $menu = str_replace('%employee_resumes%', 'none', $menu);
            }
            
            if ($page == 'jobs') {
                $menu = str_replace('%employer_jobs%', $selected, $menu);
            } else {
                $menu = str_replace('%employee_jobs%', 'none', $menu);
            }
            
            if ($page == 'invoices') {
                $menu = str_replace('%employer_invoices%', $selected, $menu);
            } else {
                $menu = str_replace('%employee_invoices%', 'none', $menu);
            }
            
            if ($page == 'profile') {
                $menu = str_replace('%employer_profile%', $selected, $menu);
            } else {
                $menu = str_replace('%employee_profile%', 'none', $menu);
            }
        } else if ($type == 'member') {
            $menu = file_get_contents(dirname(__FILE__). '/../../html/menu_members.html');
            $menu = str_replace('%root%', $this->url_root, $menu);
            
            if ($page == 'home') {
                $menu = str_replace('%member_home%', $selected, $menu);
            } else {
                $menu = str_replace('%member_home%', 'none', $menu);
            }
            
            if ($page == 'resumes') {
                $menu = str_replace('%member_resumes%', $selected, $menu);
            } else {
                $menu = str_replace('%member_resumes%', 'none', $menu);
            }
            
            if ($page == 'job_applications') {
                $menu = str_replace('%member_job_apps%', $selected, $menu);
            } else {
                $menu = str_replace('%member_job_apps%', 'none', $menu);
            }
            
            if ($page == 'recommendations') {
                $menu = str_replace('%member_recommendations%', $selected, $menu);
            } else {
                $menu = str_replace('%member_recommendations%', 'none', $menu);
            }
            
            if ($page == 'profile') {
                $menu = str_replace('%member_profile%', $selected, $menu);
            } else {
                $menu = str_replace('%member_profile%', 'none', $menu);
            }
        }
        
        echo $menu;
    }
    
    protected function menu_employee($_page = '', $_clearances = array()) {
        $menu = file_get_contents(dirname(__FILE__). '/../../html/menu_employees.html');
        $menu = str_replace('%root%', $this->url_root, $menu);
        
        $selected = '#CCCCCC';
        
        if ($_page == 'home') {
            $menu = str_replace('%employee_home%', $selected, $menu);
        } else {
            $menu = str_replace('%employee_home%', 'none', $menu);
        }
        
        if ($_page == 'employers') {
            $menu = str_replace('%employee_employers%', $selected, $menu);
        } else {
            $menu = str_replace('%employee_employers%', 'none', $menu);
        }
        
        if ($_page == 'members') {
            $menu = str_replace('%employee_members%', $selected, $menu);
        } else {
            $menu = str_replace('%employee_members%', 'none', $menu);
        }
        
        if ($_page == 'payments') {
            $menu = str_replace('%employee_payments%', $selected, $menu);
        } else {
            $menu = str_replace('%employee_payments%', 'none', $menu);
        }
        
        if ($_page == 'rewards') {
            $menu = str_replace('%employee_rewards%', $selected, $menu);
        } else {
            $menu = str_replace('%employee_rewards%', 'none', $menu);
        }
        
        if ($_page == 'status') {
            $menu = str_replace('%employee_status%', $selected, $menu);
        } else {
            $menu = str_replace('%employee_status%', 'none', $menu);
        }
        
        echo $menu;
    }
    
    protected function menu_prs($_clearances, $page = '') {
        $style = 'style="border: 1px solid #0000FF;"';
        
        ?>
        <div class="menu">
            <ul class="menu">
                <li <?php echo ($page == 'home') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/prs/home.php">Home</a></li>
        
        <?php
        if (Employee::has_clearances_for('prs_resumes_privileged', $_clearances)) {
        ?>
                <li <?php echo ($page == 'resumes_privileged') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/prs/resumes_privileged.php">Privileged Candidates</a></li>
        <?php
        }
        ?>
        
        <?php
        if (Employee::has_clearances_for('prs_recommenders', $_clearances)) {
        ?>
                <li <?php echo ($page == 'recommenders') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/prs/recommenders.php">Recommenders</a></li>
        <?php
        }
        ?>
        
        <?php
        if (Employee::has_clearances_for('prs_resumes', $_clearances)) {
        ?>
                <li <?php echo ($page == 'resumes') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/prs/resumes.php">Other Resumes</a></li>
        <?php
        }
        ?>
        
        <?php
        if (Employee::has_clearances_for('prs_resumes', $_clearances)) {
        ?>
                <li <?php echo ($page == 'resumes_uploaded') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/prs/resumes_uploaded.php">Uploaded Resumes</a></li>
        <?php
        }
        ?>
        
        <?php
        if (Employee::has_clearances_for('prs_referrals', $_clearances)) {
        ?>
                <li <?php echo ($page == 'referrals') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/prs/referrals.php">Referrals</a></li>
        <?php
        }
        ?>
    
        <?php
        if (Employee::has_clearances_for('prs_mailing_lists', $_clearances)) {
        ?>
                <li <?php echo ($page == 'mailing_lists') ? $style : '';?>><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/prs/mailing_lists.php">Mailing Lists</a></li>
        <?php
        }
        ?>
        
                <li><a href="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/prs/logout.php">Logout</a></li>
            </ul>
        </div>
        <?php
    }
    
    protected function support($_employer_id = '') {
        $support = file_get_contents(dirname(__FILE__). '/../../html/employer_support.html');
        $support = str_replace('%root%', $this->url_root, $support);
        
        $phone_number = '';
        $fax_number = '';
        $country_code = '';
        
        if (!empty($_employer_id)) {
            $employer = new Employer($_employer_id);
            $branch = $employer->getAssociatedBranch();
            
            $phone_number = $branch[0]['phone'];
            $fax_number = $branch[0]['fax'];
            $country_code = $branch[0]['country'];
        }
        
        if (empty($phone_number) || is_null($phone_number)) {
            $phone_number = '+60 4 640 6363';
        }
        
        if (empty($fax_number) || is_null($fax_number)) {
            $fax_number = '+60 4 640 6366';
        }
        
        if (empty($country_code) || is_null($country_code)) {
            $country_code = 'my';
        }
        
        $support = str_replace('%phone_number%', $phone_number, $support);
        $support = str_replace('%fax_number%', $fax_number, $support);
        $support = str_replace('%country%', strtolower($country_code), $support);
        
        echo $support;
    } 
}
?>