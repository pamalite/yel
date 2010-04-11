<?php
require_once dirname(__FILE__). "/../../utilities.php";

class SearchPage extends Page {
    private $member = NULL;
    private $criterias = '';
    private $job_search = '';
    
    function __construct($_session = NULL, $_criterias = '') {
        if (!is_null($_session)) {
            if (!empty($_session['id']) && !empty($_session['sid'])) {
                $this->member = new Member($_session['id'], $_session['sid']);
            }
        }
        
        $this->criterias = $_criterias;
        $this->job_search = new JobSearch();
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_search_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/search.css">'. "\n";
    }
    
    public function insert_search_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/search.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        if (!is_null($this->member)) {
            echo 'var id = "'. $this->member->getId(). '";'. "\n";
            
            $country_code = (isset($this->criterias['country_code'])) ? $this->criterias['country_code'] : $this->member->getCountry();
            echo 'var country_code = "'. $country_code. '";'. "\n";
        } else {
            echo 'var id = 0;'. "\n";
            
            $country_code = (isset($this->criterias['country_code'])) ? $this->criterias['country_code'] : $_SESSION['yel']['country_code'];
            echo 'var country_code = "'. $country_code. '";'. "\n";
        }
        echo 'var industry = "'. $this->criterias['industry']. '";'. "\n";
        echo 'var employer = "'. $this->criterias['employer']. '";'. "\n";
        echo 'var keywords = "'. $this->criterias['keywords']. '";'. "\n";
        
        $limit = (isset($this->criterias['limit'])) ? $this->criterias['limit'] : $GLOBALS['default_results_per_page'];
        echo 'var limit = "'. $limit. '";'. "\n";
        
        $offset = (isset($this->criterias['offset'])) ? $this->criterias['offset'] : 0;
        echo 'var offset = "'. $offset. '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_search("Searched Jobs");
        $this->menu('member');
        
        $results = $this->job_search->search_using($this->criterias);
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div class="content">
            <table class="results_table">
                <tr>
                    <td class="results">
                    <?php
                    if (is_null($results) || empty($results) || $results === false) {
                    ?>
                        <div class="empty_results">No jobs with the criteria found.</div>
                    <?php
                    } else {
                        foreach ($results as $i=>$row) {
                    ?>
                        <div class="job_short_details">
                            <div class="job_title">
                                <span class="industry"><?php echo $row['industry'] ?></span>
                                &nbsp;
                                <a href="./job/<?php echo $row['id']; ?>"><?php echo $row['title'] ?></a>
                            </div>
                            <div class="employer">
                                <?php 
                                    echo (!is_null($row['alternate_employer']) && !empty($row['alternate_employer'])) ? $row['alternate_employer'] : $row['employer'];
                                ?>
                                &nbsp;
                                <span class="country"><?php echo $row['country'] ?></span>
                            </div>
                            <div class="description">
                                <?php
                                $description = desanitize($row['description']);
                                $description = str_replace('&lt;', '<', $description);
                                $description = str_replace('&gt;', '>', $description);
                                $description = str_replace('<br>', ' ', $description);
                                $description = str_replace('<br/>', ' ', $description);
                                $description = str_replace('<br />', ' ', $description);
                                $words = explode(' ', $description);
                                $short_description = '';
                                foreach ($words as $w=>$word) {
                                    if ($w < 50) {
                                        $short_description .= $word. ' ';
                                    }
                                }
                                echo $short_description. '...';
                                ?>
                            </div>
                            <div class="date_and_salary">
                                <span class="salary">
                                <?php 
                                    echo $row['currency']. '$ '. number_format($row['salary'], 2, '.', ',');
                                    if (!empty($row['salary_end']) && !is_null($row['salary_end'])) {
                                        echo ' - '. number_format($row['salary_end'], 2, '.', ',');
                                    }
                                ?>
                                </span>
                                &nbsp;
                                <span class="reward">Potential Reward: 
                                <?php 
                                    echo $row['currency']. '$ '. number_format($row['potential_reward'], 2, '.', ',');
                                ?>
                                </span>
                                &nbsp;
                                <span class="controls">
                                    <a class="no_link" onClick="show_refer_window();">Refer Now</a>
                                    |
                                    <a href="./job/<?php echo $row['id']; ?>">View Details</a>
                                </span>
                            </div>
                        </div>
                    <?php
                        }
                    }
                    ?>
                    </td>
                    <td class="sort_and_filter">
                        <div class="sorter">
                            <div class="sorter_title">Sort</div>
                            <ul>
                                <li>Job Title</li>
                                <li>Employer</li>
                                <li>Monthly Salary Range</li>
                                <li>Oldest first</li>
                                <li>Newest first</li>
                                <li>Specialization</li>
                            </ul>
                        </div>
                        
                        <div class="filter">
                            <div class="filter_title">Filter</div>
                            <ul>
                                <li>Job Title</li>
                                <li>Employer</li>
                                <li>Monthly Salary Range</li>
                                <li>Oldest first</li>
                                <li>Newest first</li>
                                <li>Specialization</li>
                            </ul>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="pagination">
        <?php
        if ($this->job_search->total_results() <= $GLOBALS['default_results_per_page']) {
        ?>
            Page 1 of 1
        <?php
        } else {
        ?>
            Page 
            <select id="page" onChange="show_jobs();">
        <?php
            $total_pages = ceil($this->job_search->total_results() / $GLOBALS['default_results_per_page']);
            $current_page = '1';
            if ($this->criterias['offset'] > 0) {
                $current_page = ceil($this->criterias['offset'] / $GLOBALS['default_results_per_page']) + 1;
            }
            
            for ($page=0; $page <= $total_pages; $page++) {
                if (($page+1) == $current_page) {
            ?>
                <option value="<?php echo $page; ?>" selected><?php echo ($page+1) ?></option>
            <?php
                } else {
            ?>
                <option value="<?php echo $page; ?>"><?php echo ($page+1) ?></option>
            <?php
                }
            }
        ?>
            </select>
            of <?php echo $this->job_search->total_results() ?>
        <?php
        }
        ?>
        </div>
        
        <!-- div id="div_search_results">
            <div class="filters">
                Show all available jobs in <span id="filter_industry_dropdown"></span> from <span id="filter_country_dropdown"></span> with <span id="filter_limit_dropdown"></span> jobs in each page.
            </div>
            <div class="page_navigation">
                <span id="previous_page"></span>&nbsp;&nbsp;&nbsp;Page <span id="current_page"></span> of <span id="total_page"></span>&nbsp;&nbsp;&nbsp;<span id="next_page"></span>
            </div>
            <table class="header">
                <tr>
                    <td class="match_percentage"><span class="sort" id="sort_match_percentage">Match</span></td>
                    <td class="employer"><span class="sort" id="sort_employer">Employer</span></td>
                    <td class="industry"><span class="sort" id="sort_industry">Specialization</span></td>
                    <td class="title"><span class="sort" id="sort_title">Job</span></td>
                    <td class="date"><span class="sort" id="sort_created_on">Created On</span></td>
                    <td class="country"><span class="sort" id="sort_country">Country</span></td>
                    <td class="state"><span class="sort" id="sort_state">State/Area</span></td>
                    <td class="salary"><span class="sort" id="sort_salary">Monthly Salary</span></td>
                    <td class="potential_reward"><span class="sort" id="sort_potential_reward">Potential Rewards</span></td>
                </tr>
            </table>
            <div id="div_list">
            </div>
            <div class="page_navigation">
                <span id="previous_page_1"></span>&nbsp;&nbsp;&nbsp;Page <span id="current_page_1"></span> of <span id="total_page_1"></span>&nbsp;&nbsp;&nbsp;<span id="next_page_1"></span>
            </div>
        </div -->
        
        
        <!-- popup windows goes here -->
        
        
        
        <?php
    }
}
?>