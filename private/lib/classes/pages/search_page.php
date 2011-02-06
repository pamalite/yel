<?php
require_once dirname(__FILE__). "/../../utilities.php";

class SearchPage extends Page {
    private $member = NULL;
    private $criterias = '';
    private $job_search = '';
    private $country_code = '';
    
    function __construct($_session = NULL, $_criterias = '') {
        parent::__construct();
        
        if (!is_null($_session)) {
            if (!empty($_session['id']) && !empty($_session['sid'])) {
                $this->member = new Member($_session['id'], $_session['sid']);
            }
        }
        
        $this->criterias = $_criterias;
        if (!isset($this->criterias['salary'])) {
            $this->criterias['salary'] = 0;
        }
        
        $this->job_search = new JobSearch();
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_search_css() {
        $this->insert_css('search.css');
    }
    
    public function insert_search_scripts() {
        $this->insert_scripts('search.js');
    }
    
    public function insert_inline_scripts() {
        $script = '';
        
        if (!is_null($this->member)) {
            $script .= 'var id = "'. $this->member->getId(). '";'. "\n";
            
            $this->country_code = (isset($this->criterias['country'])) ? $this->criterias['country'] : $this->member->getCountry();
            $script .= 'var country_code = "'. $this->country_code. '";'. "\n";
        } else {
            $script .= 'var id = 0;'. "\n";
            
            $this->country_code = (isset($this->criterias['country'])) ? $this->criterias['country'] : $_SESSION['yel']['country_code'];
            $script .= 'var country_code = "'. $this->country_code. '";'. "\n";
        }
        $script .= 'var industry = "'. $this->criterias['industry']. '";'. "\n";
        $script .= 'var employer = "'. $this->criterias['employer']. '";'. "\n";
        $script .= 'var keywords = "'. $this->criterias['keywords']. '";'. "\n";
        // echo 'var is_local = '. $this->criterias['is_local']. ';'. "\n";
        $script .= 'var filter_salary = '. $this->criterias['salary']. ';'. "\n";
        $script .= 'var filter_salary_end = '. ((isset($this->criterias['salary_end'])) ? $this->criterias['salary_end'] : 0). ';'. "\n";
        
        $limit = (isset($this->criterias['limit'])) ? $this->criterias['limit'] : $GLOBALS['default_results_per_page'];
        $script .= 'var limit = "'. $limit. '";'. "\n";
        
        $offset = (isset($this->criterias['offset'])) ? $this->criterias['offset'] : 0;
        $script .= 'var offset = "'. $offset. '";'. "\n";
        
        $this->header = str_replace('<!-- %inline_javascript% -->', $script, $this->header);
    }
    
    public function show() {
        $this->begin();
        $this->top_search("Searched Jobs");
        
        if ($this->member != NULL) {
            $this->menu('member');
        }
        
        $results = $this->job_search->search_using($this->criterias);
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div class="content">
            <div class="statistics" id="statistics">
                Found <?php echo $this->job_search->total_results(); ?> jobs in <?php echo number_format($this->job_search->time_elapsed(), 6) ?> seconds.
            </div>
            <table class="results_table">
                <tr>
                    <td class="results" id="results">
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
                                <a href="./job/<?php echo $row['id']; ?>"><?php echo $row['title'] ?></a>
                            </div>
                            <div class="employer">
                                <?php 
                                    echo (!is_null($row['alternate_employer']) && !empty($row['alternate_employer'])) ? $row['alternate_employer'] : $row['employer'];
                                ?>
                                <span class="country"><?php echo $row['country'] ?></span>                    
                            </div>
                            <div class="description">
                                <?php
                                $new_lines = array("\r", "\n", "\r\n");
                                $description = desanitize($row['description']);
                                $description = str_replace('&amp;', '&', $description);
                                $description = str_replace('&lt;', '<', $description);
                                $description = str_replace('&gt;', '>', $description);
                                $description = str_replace('<br>', ' ', $description);
                                $description = str_replace('<br/>', ' ', $description);
                                $description = str_replace('<br />', ' ', $description);
                                $description = str_replace('&nbsp;', ' ', $description);
                                $description = str_replace($new_lines, ' ', $description);
                                $description = preg_replace('/<(.|\n)*?>/', ' ', $description);
                                
                                $words = explode(' ', $description);
                                $short_description = '';
                                foreach ($words as $w=>$word) {
                                    if ($w < 50) {
                                        $short_description .= trim($word). ' ';
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
                                    <a href="./job/<?php echo $row['id'] ?>?refer=1">Refer Now</a>
                                    |
                                    <a href="./job/<?php echo $row['id'] ?>?apply=1">Explore Now</a>
                                    |
                                    <a href="./job/<?php echo $row['id']; ?>">View Details</a>
                                </span>
                            </div>
                            <div class="expire_on">
                                Expires on <?php echo $row['formatted_expire_on'] ?>
                            </div>
                        </div>
                    <?php
                        }
                    }
                    ?>
                    </td>
                    <td class="sort_and_filter">
                        <div class="sorter">
                            <div class="sorter_title">Sort By</div>
                            <div>
                                <input type="radio" name="sort_by" id="sort_by_job_title" onClick="sort_by('jobs.title');" /><label for="sort_by_job_title">Job Title</label><br/>
                                <input type="radio" name="sort_by" id="sort_by_employer" onClick="sort_by('employers.name');" /><label for="sort_by_employer">Employer</label><br/>
                                <input type="radio" name="sort_by" id="sort_by_salary" onClick="sort_by('jobs.salary');" /><label for="sort_by_salary">Monthly Salary Range</label><br/>
                                <input type="radio" name="sort_by" id="sort_by_expiry" onClick="sort_by('jobs.expire_on');" checked /><label for="sort_by_expiry">Expiry Date</label><br/>
                                <input type="radio" name="sort_by" id="sort_by_specialization" onClick="sort_by('industries.industry');" /><label for="sort_by_specialization">Specialization</label>
                            </div>
                        </div>
                        
                        <div class="filter">
                            <div class="filter_title">Filter By</div>
                            <div class="filter_options">
                                <label for="filter_employer">Employer:</label>
                                <select id="filter_employer">
                                <?php
                                if (empty($this->criterias['employer'])) {
                                ?>
                                    <option value="" selected>Any</option>
                                <?php
                                } else {
                                ?>
                                    <option value="">Any</option>
                                <?php
                                }
                                ?>
                                    <option value="" disabled>&nbsp;</option>
                                <?php
                                foreach ($this->job_search->result_employers as $employer) {
                                    if ($this->criterias['employer'] == $employer['id']) {
                                ?>
                                    <option value="<?php echo $employer['id'] ?>" selected><?php echo $employer['name'] ?></option>
                                <?php
                                    } else {
                                ?>
                                    <option value="<?php echo $employer['id'] ?>"><?php echo $employer['name'] ?></option>
                                <?php
                                    }
                                }
                                ?>
                                </select>
                                <br/>
                                <label for="filter_industry">Specialization:</label>
                                <select id="filter_industry">
                                <?php
                                if (empty($this->criterias['industry'])) {
                                ?>
                                    <option value="" selected>Any</option>
                                <?php
                                } else {
                                ?>
                                    <option value="">Any</option>
                                <?php
                                }
                                ?>
                                    <option value="" disabled>&nbsp;</option>
                                <?php
                                foreach ($this->job_search->result_industries as $industry) {
                                    if ($this->criterias['industry'] == $industry['id']) {
                                ?>
                                    <option value="<?php echo $industry['id'] ?>" selected><?php echo $industry['name'] ?></option>
                                <?php
                                    } else {
                                ?>
                                    <option value="<?php echo $industry['id'] ?>"><?php echo $industry['name'] ?></option>
                                <?php
                                    }
                                }
                                ?>
                                </select>
                                <br/>
                                <label for="filter_country">Country:</label>
                                <select id="filter_country" onChange="changed_country();">
                                <?php
                                if (empty($this->country_code) || $this->country_code <= 0) {
                                ?>
                                    <option value="" selected>Any</option>
                                <?php
                                } else {
                                ?>
                                    <option value="">Any</option>
                                <?php
                                }
                                ?>
                                    <option value="" disabled>&nbsp;</option>
                                <?php
                                foreach ($this->job_search->result_countries as $country) {
                                    if ($this->country_code == $country['id']) {
                                ?>
                                    <option value="<?php echo $country['id'] ?>" selected><?php echo $country['name'] ?></option>
                                <?php
                                    } else {
                                ?>
                                    <option value="<?php echo $country['id'] ?>"><?php echo $country['name'] ?></option>
                                <?php
                                    }
                                }
                                ?>
                                </select>
                                <br/>
                                <label for="filter_salary">Salary from:</label>
                                <select id="filter_salary">
                                <?php
                                if ($this->criterias['salary'] <= 0) {
                                ?>
                                    <option value="" selected>Any</option>
                                <?php
                                } else {
                                ?>
                                    <option value="">Any</option>
                                <?php
                                }
                                ?>
                                    <option value="" disabled>&nbsp;</option>
                                <?php
                                foreach ($this->job_search->result_salaries as $salary) {
                                    if ($this->criterias['salary'] == $salary) {
                                ?>
                                    <option value="<?php echo $salary ?>" selected><?php echo $salary ?></option>
                                <?php
                                    } else {
                                ?>
                                    <option value="<?php echo $salary ?>"><?php echo $salary ?></option>
                                <?php
                                    }
                                }
                                ?>
                                </select>
                            </div>
                            <div class="filter_button">
                                <input type="button" value="Filter" onClick="filter_jobs();" />
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="pagination" id="pagination">
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
            
            for ($page=0; $page < $total_pages; $page++) {
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
            of <?php echo $total_pages ?>
        <?php
        }
        ?>
        </div>
        
        <!-- popup windows goes here -->
        
        
        
        <?php
    }
}
?>