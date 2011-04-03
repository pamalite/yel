<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). '/../htmltable.php';

class MemberRecommendationsPage extends Page {
    private $member = NULL;
    
    function __construct($_session) {
        parent::__construct();
        
        $this->member = new member($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_recommendations_css() {
        $this->insert_css('member_recommendations.css');
    }
    
    public function insert_member_recommendations_scripts() {
        $this->insert_scripts(array('flextable.js', 'member_recommendations.js'));
    }
    
    public function insert_inline_scripts() {
        $script = 'var id = "'. $this->member->getId(). '";'. "\n";
        
        $this->header = str_replace('<!-- %inline_javascript% -->', $script, $this->header);
    }
    
    public function show() {
        $this->begin();
        $this->top_search("Recommendations");
        $this->menu('member', 'recommendations');
        $this->howitworks();
        
        $recommendations = $this->member->getReferrals();
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_recommendations">
        <?php
            if (empty($recommendations)) {
        ?>
            <div class="empty_results">No recommedations made.</div>
        <?php
            } else {
                $recommendations_table = new HTMLTable('recommendations_table', 'recommendations');
                
                $recommendations_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('referrals', 'referred_on');\">Recommended On</a>", '', 'header');
                $recommendations_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('referrals', 'job');\">Position</a>", '', 'header');
                $recommendations_table->set(0, 2, "<a class=\"sortable\" onClick=\"sort_by('referrals', 'candidate_name');\">Candidate</a>", '', 'header');
                $recommendations_table->set(0, 3, "&nbsp;", '', 'header actions');

                foreach ($recommendations as $i=>$recommendation) {
                    // referred on
                    $recommendations_table->set($i+1, 0, $recommendation['formatted_referred_on'], '', 'cell');
                    
                    // position
                    $job_details = '<div class="candidate_name"><a href="../job/'. $recommendation['job_id']. '">'. htmlspecialchars_decode(stripslashes($recommendation['job'])). '</a></div><br/>';
                    $employer = $recommendation['employer'];
                    if (!is_null($recommendation['alternate_employer']) &&
                        !empty($recommendation['alternate_employer'])) {
                        $employer = $recommendation['alternate_employer'];
                    }
                    $job_details .= '<div class="small_contact"><span style="font-weight: bold;">Employer: </span>'. htmlspecialchars_decode(stripslashes($employer)). '</div>';
                    $recommendations_table->set($i+1, 1, $job_details, '', 'cell');
                    
                    // candidate
                    $candidate = '<div class="candidate_name">'. htmlspecialchars_decode(stripslashes($recommendation['candidate_name'])). '</div><br/>';
                    $candidate .= '<div class="small_contact"><span style="font-weight: bold;">E-mail:</span><a href="mailto:'. $recommendation['candidate_email']. '"> '. $recommendation['candidate_email']. '</a></div>';
                    $recommendations_table->set($i+1, 2, $candidate, '', 'cell');
                    
                    // action
                    $action = 'Processed';
                    if ($recommendation['tab'] == 'buf') {
                        $action = '<a class="no_link" onClick="delete_buffered(\''. $recommendation['id']. '\');">delete</a>';
                    } 
                    $recommendations_table->set($i+1, 3, $action, '', 'cell actions');
                }

                echo $recommendations_table->get_html();
            }
        ?>
        </div>
        
        <?php
    }
}
?>