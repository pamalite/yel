<?php
require_once dirname(__FILE__). "/../../utilities.php";

class SearchPage extends Page {
    private $member = NULL;
    private $criterias = '';
    
    function __construct($_session = NULL, $_criterias = '') {
        if (!is_null($_session)) {
            if (!empty($_session['id']) && !empty($_session['sid'])) {
                $this->member = new Member($_session['id'], $_session['sid']);
            }
        }
        
        $this->criterias = $_criterias;
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_search_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/list_box.css">'. "\n";
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/search.css">'. "\n";
    }
    
    public function insert_search_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/list_box.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/search.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        if (!is_null($this->member)) {
            echo 'var id = "'. $this->member->id(). '";'. "\n";
            
            $country_code = (isset($this->criterias['country_code'])) ? $this->criterias['country_code'] : $this->member->get_country_code();
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
    
    private function generate_networks_list() {
        $networks = $this->member->get_networks();
        
        echo '<select id="network_filter" name="network_filter" onChange="set_filter();">'. "\n";
        echo '<option value="0" selected>all my networks</option>'. "\n";
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        
        foreach ($networks as $network) {
            echo '<option value="'. $network['id']. '">'. $network['industry']. '</option>'. "\n";
        }
        
        echo '</select>'. "\n";
    }
    
    public function show() {
        $this->begin();
        if (is_null($this->member)) {
            $this->top_search("Yellow Elevator - Job Search Results");
        } else {
            $this->top_search($this->member->get_name(). " - Job Search Results");
            $this->menu('member');
        }
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_search_results">
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
                    <td class="industry"><span class="sort" id="sort_industry">Industry</span></td>
                    <td class="title"><span class="sort" id="sort_title">Job</span></td>
                    <!--td class="date"><span class="sort" id="sort_created_on">Created On</span></td-->
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
        </div>
        
        <div id="div_job_info">
            <input type="hidden" id="job_id" name="job_id" value="" />
            <div class="back"><span class="back"><a class="no_link" onClick="back_to_results();">Back to Search Results</a></span></div>
            <table id="job_info" class="job_info">
                <tr>
                    <td colspan="2" class="title"><span id="job.title">Loading</span></td>
                </tr>
                <tr>
                    <td colspan="2" class="title_reward">Potential Reward of <span id="job.currency_1"></span>$&nbsp;<span id="job.potential_reward">Loading</span></td>
                </tr>
                <tr>
                    <td class="label">Industry:</td>
                    <td class="field"><span id="job.industry">Loading</span></td>
                </tr>
                <tr>
                    <td class="label">Employer:</td>
                    <td class="field"><span id="job.employer">Loading</span></td>
                </tr>
                <tr>
                    <td class="label">Country:</td>
                    <td class="field"><span id="job.country">Loading</span></td>
                </tr>
                <tr>
                    <td class="label">State/Province/Area:</td>
                    <td class="field"><span id="job.state">Loading</span></td>
                </tr>
                <tr>
                    <td class="label">Monthly Salary:</td>
                    <td class="field"><span id="job.currency"></span>$&nbsp;<span id="job.salary">Loading</span>&nbsp;<span id="job.salary_end">Loading</span>&nbsp;[<span id="job.salary_negotiable">Loading</span>]</td>
                </tr>
                <tr>
                    <td class="label">Description:</td>
                    <td class="field"><div class="job_description"><span id="job.description">Loading</span></div></td>
                </tr>
                <tr>
                    <td class="label">&nbsp;</td>
                    <td class="field">&nbsp;</td>
                </tr>
                <tr>
                    <td class="label">Created On:</td>
                    <td class="field"><span id="job.created_on">Loading</span></td>
                </tr>
                <tr>
                    <td class="label">Expires On:</td>
                    <td class="field"><span id="job.expire_on">Loading</span></td>
                </tr>
                <tr>
                    <td id="job_buttons" class="buttons" colspan="2"></td>
                </tr>
            </table>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_refer_form">
            <form onSubmit="retun false;">
                <table class="refer_form">
                    <tr>
                        <td class="left">
                            <p>You are about refer the job <span id="job_title" style="font-weight: bold;"></span> to a potential candidate. Please the candidate from either...</p>
                            <table class="candidate_form">
                                <tr>
                                    <td class="radio"><input type="radio" id="from_list" name="candidate_from" value="list" checked /></td>
                                    <td>
                                        <label for="from_list">from your candidates list</label><br/>
                                        <span class="filter">[ Show candidates from <?php (!is_null($this->member)) ? $this->generate_networks_list() : ''; ?> ]</span><br/>
                                        <div class="candidates" id="candidates" name="candidates"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="radio"><input type="radio" id="from_email" name="candidate_from" value="email" /></td>
                                    <td>
                                        <label for="from_email">or e-mail address of a new candidate</label><br/>
                                        <input type="text" class="mini_field" id="email_addr" name="email_addr" />
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="separator"></td>
                        <td class="right">
                            <p>1. How long have you known and how do you know <span id="candidate_name" style="font-weight: bold;">the potential candidate</span>? (<span id="word_count_q1">0</span>/50 words)</p>
                            <p><textarea class="mini_field" id="testimony_answer_1"></textarea></p>
                            <p>2. What makes <span id="candidate_name" style="font-weight: bold;">the potential candidate</span> suitable for <span id="job_title" style="font-weight: bold;">the job</span>?  (<span id="word_count_q2">0</span>/50 words)</p>
                            <p><textarea class="mini_field" id="testimony_answer_2"></textarea></p>
                            <p>3. Briefly, what are <span id="candidate_name" style="font-weight: bold;">the potential candidate</span>'s rooms for improvement?  (<span id="word_count_q3">0</span>/50 words)</p>
                            <p><textarea class="mini_field" id="testimony_answer_3"></textarea></p>
                        </td>
                    </tr>
                </table>
                <p class="button"><input type="button" value="Cancel" onClick="close_refer_form();" />&nbsp;<input type="button" value="Refer Now" onClick="refer();" /></p>
            </form>
        </div>
        <?php
    }
}
?>