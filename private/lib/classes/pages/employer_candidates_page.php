<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). "/../htmltable.php";
require_once dirname(__FILE__). "/../../../config/job_profile.inc";

class EmployerCandidatesPage extends Page {
    private $employer = NULL;
    private $current_page = 'search';
    private $total_applications = 0;
    private $total_members = 0;
    
    function __construct($_session) {
        parent::__construct();
        
        $this->employer = new Employer($_session['id'], $_session['sid']);
    }
    
    public function set_page($_page) {
        $this->current_page = $_page;
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employer_candidates_css() {
        $this->insert_css(array('list_box.css', 'employer_candidates.css'));
    }
    
    public function insert_employer_candidates_scripts() {
        $this->insert_scripts(array('flextable.js', 'list_box.js', 'employer_candidates.js'));
    }
    
    public function insert_inline_scripts() {
        $script = 'var id = "'. $this->employer->getId(). '";'. "\n";
        $script .= 'var current_page = "'. $this->current_page. '";'. "\n";
        
        $criteria = array(
            'columns' => "credits_left", 
            'match' => "id = '". $this->employer->getId(). "'", 
            'limit' => "1"
        );
        
        $result = $this->employer->find($criteria);
        
        $credits_left = '0';
        if (!is_null($result[0]['credits_left']) && !empty($result[0]['credits_left'])) {
            $credits_left = $result[0]['credits_left'];
        }
        $script .= 'var credits_left = '. $credits_left. ';'. "\n";
        
        $this->header = str_replace('<!-- %inline_javascript% -->', $script, $this->header);
    }
    
    private function get_employers() {
        
        $criteria = array(
            'columns' => "DISTINCT employers.name AS employer, employers.id", 
            'joins' => "employers ON employers.id = jobs.employer",
            'order' => "employers.name"
        );
        
        $job = new Job();
        return $job->find($criteria);
    }
    
    private function generate_currencies($_id, $_selected='') {
        $currencies = $GLOBALS['currencies'];
        
        echo '<select id="'. $_id. '" name="'. $_id. '">'. "\n";
        echo '<option value="0" selected>Any Currency</option>'. "\n";
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        
        foreach ($currencies as $i=>$currency) {
            echo '<option value="'. $currency. '">'. $currency. '</option>'. "\n";
        }
        
        echo '</select>';
    }
    
    private function generate_industries($_selected, $_name = 'industry') {
        $industries = array();
        $main_industries = Industry::getMain();
        $i = 0;
        foreach ($main_industries as $main) {
            $industries[$i]['id'] = $main['id'];
            $industries[$i]['name'] = $main['industry'];
            $industries[$i]['is_main'] = true;
            $subs = Industry::getSubIndustriesOf($main['id']);
            foreach ($subs as $sub) {
                $i++;

                $industries[$i]['id'] = $sub['id'];
                $industries[$i]['name'] = $sub['industry'];
                $industries[$i]['is_main'] = false;
            }
            $i++;
        }
        
        echo '<select class="field" id="'. $_name. '" name="'. $_name. '">'. "\n";
        
        if (empty($_selected) || is_null($_selected)) {
            echo '<option value="0" selected>Any Specialization</option>'. "\n";
            echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        }
        
        foreach ($industries as $industry) {
            $selected = '';
            if ($industry['id'] == $_selected) {
                $selected = 'selected';
            }
            
            if ($industry['is_main']) {
                echo '<option value="'. $industry['id']. '" class="main_industry" '. $selected. '>';
                echo $industry['name'];
            } else {
                echo '<option value="'. $industry['id']. '"'. $selected. '>';
                echo '&nbsp;&nbsp;&nbsp;&nbsp;'. $industry['name'];
            }

            echo '</option>'. "\n";
        }
        
        echo '</select>'. "\n";
    }
    
    private function generate_employer_description($_id, $_selected) {
        $descs = $GLOBALS['emp_descs'];
        
        echo '<select class="field" id="'. $_id. '" name="'. $_id. '">'. "\n";
        if (empty($_selected) || is_null($_selected) || $_selected < 0) {
            echo '<option value="0" selected>Any description</option>'. "\n";    
        } else {
            echo '<option value="0">Any description</option>'. "\n";
        }
        
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        foreach ($descs as $i=>$desc) {
            if ($i != $_selected) {
                echo '<option value="'. $i. '">'. $desc. '</option>'. "\n";
            } else {
                echo '<option value="'. $i. '" selected>'. $desc. '</option>'. "\n";
            }
        }
        
        echo '</select>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->support($this->employer->getId());
        $this->top('Candidates');
        $this->menu('employer', 'candidates', $this->employer->isYEConnectOnly());
        
        $employers = $this->get_employers();
        
        $credits_left = $this->employer->getCreditsAmount();
        ?>
        <!-- submenu -->
        <div class="menu">
            <?php $style = 'background-color: #CCCCCC;'; ?>
            <ul class="menu">
                <li id="item_search_candidates" style="<?php echo ($this->current_page == 'search') ? $style : ''; ?>"><a class="menu" onClick="show_search_candidates();">Search Candidates</a></li>
                <li id="item_shortlist" style="<?php echo ($this->current_page == 'shortlist') ? $style : ''; ?>"><a class="menu" onClick="show_shortlist();">Shortlist</a></li>
                <li id="item_connections" style="<?php echo ($this->current_page == 'connections') ? $style : ''; ?>"><a class="menu" onClick="show_connections();">Candidates</a></li>
                <li id="item_credits" style="<?php echo ($this->current_page == 'credits') ? $style : ''; ?>"><a class="menu" onClick="show_credits();">Credits</a></li>
            </ul>
        </div>
        <!-- end submenu -->
        
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="members">
            <!-- search form -->
            <div id="div_search_toggle" class="search_toggle">
                <a class="no_link" onClick="toggle_search();">
                    <span id="hide_show_lbl">Toggle Search</span>
                </a>
            </div>
            <div id="div_search" class="search">
                <form id="candidates_search_form">
                    <table id="search_table">
                        <tr>
                            <td class="search_form">
                                <table id="search_form_table">
                                    <tr>
                                        <td class="label"><label for="search_position">Position:</label></td>
                                        <td class="field"><input type="text" class="field" id="search_position" /></td>
                                    </tr>
                                    <tr>
                                        <td class="label"><label for="search_employer">Company:</label></td>
                                        <td class="field"><input type="text" class="field" id="search_employer" /></td>
                                    </tr>
                                    <tr>
                                        <td class="label"><label for="search_emp_desc">Company Description:</label></td>
                                        <td class="field"><?php $this->generate_employer_description('search_emp_desc', -1); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="label"><label for="search_specialization">Specialization:</label></td>
                                        <td class="field"><?php $this->generate_industries(array(), 'search_specialization'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="label"><label for="search_total_years">Total Work Years:</label></td>
                                        <td class="field"><input type="text" class="field years" id="search_total_years" maxlength="2" /> years</td>
                                    </tr>
                                    <tr>
                                        <td class="label"><label for="search_seeking">Job Responsibilities &amp; Experiences:</label></td>
                                        <td class="field">
                                            <textarea class="field" id="search_seeking"></textarea>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td class="search_buttons">
                                <input type="button" class="search_button" value="Search" onClick="update_members();" />
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <!-- end search form -->
            
            <div id="members_list">
                <div class="buttons_bar">
                    <div class="pagination">
                        Page
                        <select id="members_pages" onChange="update_members();">
                            <option value="1" selected>1</option>
                        </select>
                        of <span id="total_members_pages">0</span>
                    </div>                    
                </div>
                <div id="div_members">
                    <div class="empty_results">
                        No candidates to show.
                    </div>
                </div>
            </div>
        </div>
        
        <div id="shortlist">
        </div>
        
        <div id="connections">
        </div>
        
        <div id="credits">
            <div class="credits_left">
                <span class="label">Credits Left: </span>
                <span class="credits <?php echo ($credits_left > 0) ? 'green' : 'red'; ?>">
                    <?php echo number_format($credits_left, 2, '.', ','); ?>
                </span>
            </div>
            <div class="top_up">
            <?php
                if ($credits_left > 0) {
            ?>
                Please <a href="../contact.php">Contact Us</a> to top-up your connections credits.
            <?php
                } else {
                    $credits = $this->employer->getCredits();
                    if (is_null($credits) || empty($credits)) {
            ?>
                In order for you to connect with our candidates, you need to <span style="font-weight: bold;">purchase</a> sufficient connection credits.<br/><br/>
                Please <a href="../contact.php">Contact Us</a> for further information.
            <?php
                    } else {
            ?>
                Please <a href="../contact.php">Contact Us</a> to reload your connections credits.
            <?php
                    }
            ?>
            <?php
                }
            ?>
            </div>
        </div>
        
        <!-- popup windows goes here -->
        
        <?php
    }
}
?>