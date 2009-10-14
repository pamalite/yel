<?php
require_once dirname(__FILE__). "/../../utilities.php";

class PrsResumeSearchPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session, $_criterias = '') {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
        
        $this->criterias = $_criterias;
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_resume_search_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/list_box.css">'. "\n";
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/search.css">'. "\n";
    }
    
    public function insert_resume_search_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/list_box.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/resume_search.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo 'var country_code = "'. $this->criterias['country_code']. '";'. "\n";
        echo 'var industry = "'. $this->criterias['industry']. '";'. "\n";
        echo 'var keywords = "'. $this->criterias['keywords']. '";'. "\n";
        
        $limit = (isset($this->criterias['limit'])) ? $this->criterias['limit'] : $GLOBALS['default_results_per_page'];
        echo 'var limit = "'. $limit. '";'. "\n";
        
        $offset = (isset($this->criterias['offset'])) ? $this->criterias['offset'] : 0;
        echo 'var offset = "'. $offset. '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function generateCountryFilters() {
        $mysqli = Database::connect();
        $query = "SELECT DISTINCT countries.country_code AS id, countries.country 
                  FROM members 
                  LEFT JOIN countries ON countries.country_code = members.country 
                  ORDER BY countries.country";
        $result = $mysqli->query($query);
        
        echo '<select id="country_filter" name="country_filter" onChange="refresh_candidates();">'. "\n";
        echo '<option value="">all countries</option>'. "\n";
        echo '<option value="" disabled>&nbsp;</option>'. "\n";
        
        foreach ($result as $row) {
            echo '<option value="'. $row['id']. '">'. $row['country']. '</option>'. "\n";
        }
        
        echo '</select>'. "\n";
    }
    
    private function generateZipFilters() {
        $mysqli = Database::connect();
        $query = "SELECT DISTINCT zip 
                  FROM members 
                  WHERE zip IS NOT NULL AND zip <> '' 
                  ORDER BY zip";
        $result = $mysqli->query($query);
        
        echo '<select id="zip_filter" name="zip_filter" onChange="refresh_candidates();">'. "\n";
        echo '<option value="">all areas</option>'. "\n";
        echo '<option value="" disabled>&nbsp;</option>'. "\n";
        
        foreach ($result as $row) {
            echo '<option value="'. $row['zip']. '">'. $row['zip']. '</option>'. "\n";
        }
        
        echo '</select>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_prs($this->employee->get_name(). " - Resumes Search Result");
        $this->menu_prs($this->clearances, '');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_search_results">
            <div class="filters">
                Show all available resumes in <span id="filter_industry_dropdown"></span> from <span id="filter_country_dropdown"></span> with <span id="filter_limit_dropdown"></span> jobs in each page.
            </div>
            <div class="page_navigation">
                <span id="previous_page"></span>&nbsp;&nbsp;&nbsp;Page <span id="current_page"></span> of <span id="total_page"></span>&nbsp;&nbsp;&nbsp;<span id="next_page"></span>
            </div>
            <table class="header">
                <tr>
                    <td class="match_percentage"><span class="sort" id="sort_match_percentage">Match</span></td>
                    <td class="date"><span class="sort" id="sort_joined_on">Joined On</span></td>
                    <td class="member"><span class="sort" id="sort_member">Candidate</span></td>
                    <td class="industry"><span class="sort" id="sort_primary_industry">Specialization 1</span></td>
                    <td class="industry"><span class="sort" id="sort_secondary_industry">Specialization 2</span></td>
                    <td class="title"><span class="sort" id="sort_title">Resume</span></td>
                    <td class="country"><span class="sort" id="sort_country">Country</span></td>
                    <td class="zip"><span class="sort" id="sort_zip">Zip</span></td>
                </tr>
            </table>
            <div id="div_list">
            </div>
            <div class="page_navigation">
                <span id="previous_page_1"></span>&nbsp;&nbsp;&nbsp;Page <span id="current_page_1"></span> of <span id="total_page_1"></span>&nbsp;&nbsp;&nbsp;<span id="next_page_1"></span>
            </div>
        </div>
        
        <?php
    }
}
?>