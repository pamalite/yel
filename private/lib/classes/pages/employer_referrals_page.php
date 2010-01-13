<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployerReferralsPage extends Page {
    private $employer = NULL;
    
    function __construct($_session) {
        $this->employer = new Employer($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employer_referrals_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employer_referrals.css">'. "\n";
    }
    
    public function insert_employer_referrals_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employer_referrals.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts($_job = 0) {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employer->id(). '";'. "\n";
        
        if ($_job > 0) {
            $query = "SELECT jobs.title, industries.industry 
                      FROM jobs 
                      LEFT JOIN industries ON industries.id = jobs.industry 
                      WHERE jobs.id = ". $_job. " LIMIT 1";
            $mysqli = Database::connect();
            $result = $mysqli->query($query);
            
            echo 'var job_to_list = "'. $_job. '";'. "\n";
            echo 'var job_to_list_title = "'. $result[0]['title']. '";'. "\n";
            echo 'var job_to_list_industry = "'. $result[0]['industry']. '";'. "\n";
        } else {
            echo 'var job_to_list = "0";'. "\n";
            echo 'var job_to_list_title = "";'. "\n";
            echo 'var job_to_list_industry = "";'. "\n";
        }
        
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->support();
        $this->top($this->employer->get_name(). "&nbsp;&nbsp;<span style=\"color: #FC8503;\">Resumes</span>");
        $this->menu('employer', 'referrals');
        
        $query = "SELECT currencies.symbol FROM currencies 
                  LEFT JOIN employers ON currencies.country_code = employers.country 
                  WHERE employers.id = '". $this->employer->id(). "' LIMIT 1";
        $mysqli = Database::connect();
        $result = $mysqli->query($query);
        $currency = '???';
        if (count($result) > 0 && !is_null($result)) {
            $currency = $result[0]['symbol'];
        }
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_referred_jobs">
            <table class="header">
                <tr>
                    <td class="industry"><span class="sort" id="sort_industry">Industry</span></td>
                    <td class="title"><span class="sort" id="sort_title">Job</span></td>
                    <td class="date"><span class="sort" id="sort_created_on">Created On</span></td>
                    <td class="date"><span class="sort" id="sort_expire_on">Expire On</span></td>
                    <td class="referrals"><span class="sort" id="sort_referrals">Resumes</span></td>
                </tr>
            </table>
            <div id="div_list">
            </div>
        </div>
        
        <div id="div_referrals">
            <input type="hidden" id="job_id" value="" /> 
            <div id="title" class="title"></div>
            <div id="industry_label" class="industry"></div>
            <div class="salary"><?php echo $currency; ?>$&nbsp;<span id="pay">0.00</span></div>
            <div class="description_link"><a href="#" onClick="show_description('0');">Click here for job description</a></div>
            
            <div id="div_tabs">
                <ul>
                    <li id="li_referred_jobs">&lt;&lt;</li>
                    <li id="li_suggested">Recommended<span id="suggested_count"></span></li>
                    <li id="li_referred">Others<span id="referred_count"></span></li>
                    <li id="li_shortlisted">My Shortlist<span id="shortlisted_count"></span></li>
                </ul>
            </div>
            
            <div id="div_referred">
                <table class="buttons">
                    <tr>
                        <td class="left">
                            <input class="button" type="button" id="remove_referred_candidates" name="remove_referred_candidates" value="Remove Selected Candidates" />
                            &nbsp;
                            <span style="font-weight: bold;">
                            [ Show
                            <select id="filter_by">
                                <option value="" selected>all</option>
                                <option value="NULL">only online resumes</option>
                                <option value="NOT NULL">only file uploads</option>
                            </select>
                            &nbsp;]
                            </span>
                        </td>
                        <td class="right"><!--a class="no_link" style="font-size: 9pt;" onClick="show_resume_viewing_terms();">Resume Terms of Use</a-->&nbsp;</td>
                    </tr>
                </table>
                <table class="header">
                    <tr>
                        <td class="checkbox"><input type="checkbox" id="remove_all_referred" /></td>
                        <!--td class="id">&nbsp;</td-->
                        <td class="indicator">&nbsp;</td>
                        <!--td class="view">&nbsp;</td-->
                        <td class="member"><span class="sort" id="sort_referred_member">Referrer</span></td>
                        <td class="referee"><span class="sort" id="sort_referred_referee">Candidate</span></td>
                        <td class="date"><span class="sort" id="sort_referred_referred_on">Referred On</span></td>
                        <td class="date"><span class="sort" id="sort_referred_acknowledged_on">Resume Submitted On</span></td>
                        <td class="employ">&nbsp;</td>
                    </tr>
                </table>
                <div id="div_referred_candidates_list">
                </div>
                <table class="buttons">
                    <tr>
                        <td class="left"><input class="button" type="button" id="remove_referred_candidates_1" name="remove_referred_candidates_1" value="Remove Selected Candidates" /></td>
                        <td class="right"><!--a class="no_link" style="font-size: 9pt;" onClick="show_resume_viewing_terms();">Resume Terms of Use</a-->&nbsp;</td>
                    </tr>
                </table>
            </div>
            
            <div id="div_suggested">
                <table class="buttons">
                    <tr>
                        <td class="left"><input class="button" type="button" id="remove_referred_candidates_2" name="remove_referred_candidates_2" value="Remove Selected Candidates" /></td>
                        <td class="right"><!--a class="no_link" style="font-size: 9pt;" onClick="show_resume_viewing_terms();">Resume Terms of Use</a-->&nbsp;</td>
                    </tr>
                </table>
                <table class="header">
                    <tr>
                        <td class="checkbox"><input type="checkbox" id="remove_all_suggested" /></td>
                        <!--td class="id">&nbsp;</td-->
                        <td class="indicator">&nbsp;</td>
                        <td class="score"><span class="sort" id="sort_suggested_score">Score</span></td>
                        <!--td class="view">&nbsp;</td-->
                        <td class="member"><span class="sort" id="sort_suggested_member">Referrer</span></td>
                        <td class="referee"><span class="sort" id="sort_suggested_referee">Candidate</span></td>
                        <td class="date"><span class="sort" id="sort_suggested_referred_on">Referred On</span></td>
                        <td class="date"><span class="sort" id="sort_suggested_acknowledged_on">Resume Submitted On</span></td>
                        <td class="employ">&nbsp;</td>
                    </tr>
                </table>
                <div id="div_suggested_candidates_list">
                </div>
                <table class="buttons">
                    <tr>
                        <td class="left"><input class="button" type="button" id="remove_referred_candidates_3" name="remove_referred_candidates_3" value="Remove Selected Candidates" /></td>
                        <td class="right"><!--a class="no_link" style="font-size: 9pt;" onClick="show_resume_viewing_terms();">Resume Terms of Use</a-->&nbsp;</td>
                    </tr>
                </table>
            </div>
            
            <div id="div_shortlisted">
                <table class="buttons">
                    <tr>
                        <td class="left"><input class="button" type="button" id="remove_referred_candidates_4" name="remove_referred_candidate_4" value="Remove Selected Candidates" /></td>
                        <td class="right"><!--a class="no_link" style="font-size: 9pt;" onClick="show_resume_viewing_terms();">Resume Terms of Use</a-->&nbsp;</td>
                    </tr>
                </table>
                <table class="header">
                    <tr>
                        <td class="checkbox"><input type="checkbox" id="remove_all_shortlisted" /></td>
                        <!--td class="id">&nbsp;</td-->
                        <td class="indicator">&nbsp;</td>
                        <!--td class="view">&nbsp;</td-->
                        <td class="member"><span class="sort" id="sort_shortlisted_member">Referrer</span></td>
                        <td class="referee"><span class="sort" id="sort_shortlisted_referee">Candidate</span></td>
                        <td class="date"><span class="sort" id="sort_shortlisted_referred_on">Referred On</span></td>
                        <td class="date"><span class="sort" id="sort_shortlisted_acknowledged_on">Resume Submitted On</span></td>
                        <td class="date"><span class="sort" id="sort_shortlisted_shortlisted_on">Shortlisted On</span></td>
                        <td class="employ">&nbsp;</td>
                    </tr>
                </table>
                <div id="div_shortlisted_candidates_list">
                </div>
                <table class="buttons">
                    <tr>
                        <td class="left"><input class="button" type="button" id="remove_referred_candidates_5" name="remove_referred_candidates_5" value="Remove Selected Candidates" /></td>
                        <td class="right"><!--a class="no_link" style="font-size: 9pt;" onClick="show_resume_viewing_terms();">Resume Terms of Use</a-->&nbsp;</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_description">
            <div id="job_title"></div>
            <div id="description"></div>
            <p class="button"><input type="button" value="Close" onClick="close_description();" /></p>
        </div>
        
        <div id="div_employ_form">
            <form onSubmit="return false;">
                <p>
                    Please fill-up <span id="candidate_name" style="font-weight: bold;"></span>'s employment details for the <span id="employ_job_title" style="font-weight: bold;"></span>&nbsp;position:
                </p>
                <table class="employ_form">
                    <tr>
                        <td class="label">Work Commencement:</td>
                        <td class="field"><input type="text" style="width: 25px;" maxlength="2" id="day" name="day" value="dd" />&nbsp;<span id="month_list"></span>&nbsp;<span id="year_label"></span></td>
                    </tr>
                    <tr>
                        <td class="label">Annual Salary:</td>
                        <td class="field"><span id="currency"><?php echo $currency; ?></span>$&nbsp;<input type="text" class="field" id="salary" name="salary" value="1.00" /></td>
                    </tr>
                </table>
                <p class="button"><input type="button" value="Cancel" onClick="close_employ_form();" />&nbsp;<input type="button" value="Confirm Employed" onClick="employ();" /></p>
            </form>
        </div>
        
        <div id="div_employer_remarks">
            <form onSubmit="return false;">
                <input type="hidden" id="remarks_referral_id" value="" />
                <div style="text-align: center; padding-top: 15px; padding-bottom: 15px;">
                    Remarks for <span id="remarks_candidate_name" style="font-weight: bold;"></span> - <span id="remarks_job_title" style="font-weight: bold;"></span>:
                </div>
                <div style="width: 100%; margin:auto; text-align: center;">
                    <textarea id="remarks_field" style="width: 98%; height: 250px;"></textarea>
                </div>
                <p class="button"><input type="button" value="Cancel" onClick="close_remarks_form();" />&nbsp;<input type="button" value="Save Remarks" onClick="save_remarks();" /></p>
            </form>
        </div>
        <?php
    }
}
?>