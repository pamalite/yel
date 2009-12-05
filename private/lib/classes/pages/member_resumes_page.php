<?php
require_once dirname(__FILE__). "/../../utilities.php";

class MemberResumesPage extends Page {
    private $member = NULL;
    
    function __construct($_session) {
        $this->member = new Member($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_resumes_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/member_resumes.css">'. "\n";
    }
    
    public function insert_member_resumes_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/work_experience.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/education.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/skill_sets.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/technical_skill.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/member_resumes.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->member->id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function generateCountries($selected = '') {
        $countries = Country::get_all();
        
        echo '<select class="field" id="country" name="country">'. "\n";
        
        if ($selected == '') {
            echo '<option value="0" selected>Please select a country</option>'. "\n";
        }
        
        foreach ($countries as $country) {
            if ($country['country_code'] != $selected) {
                echo '<option value="'. $country['country_code']. '">'. $country['country']. '</option>'. "\n";
            } else {
                echo '<option value="'. $country['country_code']. '" selected>'. $country['country']. '</option>'. "\n";
            }
        }
        
        echo '</select>'. "\n";
    }
    
    private function generate_all_industry_list() {
        $industries = Industry::get_main();
        
        echo '<select class="field" id="industry" name="industry">'. "\n";
        
        if ($selected == '') {
            echo '<option value="0" selected>Please select an industry</option>'. "\n";
        }
        
        foreach ($industries as $industry) {
            echo '<option class="main_industry" value="'. $industry['id']. '">'. $industry['industry']. '</option>'. "\n";
            
            $sub_industries = Industry::get_sub_industries_of($industry['id']);
            foreach ($sub_industries as $sub_industry) {
                echo '<option value="'. $sub_industry['id']. '">&nbsp;&nbsp;&nbsp;'. $sub_industry['industry']. '</option>'. "\n";
            }
            
        }
        
        echo '</select>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_search($this->member->get_name(). "&nbsp;&nbsp;<span style=\"color: #FC8503;\">Resumes</span>");
        $this->menu('member', 'resumes');
        
        ?>
        <div class="banner">
            You are allowed to create multiple resumes for different job applications.<br/><br/>
            In order to apply for a desired job position, you MUST be referred by another member. If you come across a job position that you are interested in, please get a member who knows you well to refer you. Only someone who knows you well will be able to write a good testimonial about you.
        </div>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_resumes">
            <table class="buttons">
                <tr>
                    <td class="right">
                        <input class="button" type="button" id="add_new_resume" name="add_new_resume" value="Create New Resume" />
                        <input class="button" type="button" id="upload_new_resume" name="upload_new_resume" value="Upload Resume" />
                    </td>
                </tr>
            </table>
            <table class="header">
                <tr>
                    <td class="name"><span class="sort" id="sort_name">Resume Label</span></td>
                    <td class="date"><span class="sort" id="sort_modified_on">Modified On</span></td>
                </tr>
            </table>
            <div id="div_list">
            </div>
            <table class="buttons">
                <tr>
                    <td class="right">
                        <input class="button" type="button" id="add_new_resume_1" name="add_new_resume_1" value="Create New Resume" />
                        <input class="button" type="button" id="upload_new_resume_1" name="upload_new_resume_1" value="Upload Resume" />
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="div_upload_resume_form">
            <form action="resume_action.php" method="post" enctype="multipart/form-data" target="upload_target">
                <input type="hidden" id="resume_id" name="id" value="0" />
                <input type="hidden" name="member" value="<?php echo $this->member->id(); ?>" />
                <input type="hidden" name="action" value="upload" />
                <p id="upload_progress" style="text-align: center;">
                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/progress/circle_big.gif" />
                </p>
                <p id="upload_form">
                    <table class="upload_form">
                        <tr>
                            <td class="label"><label for="my_file">Resume File:</label></td>
                            <td class="field"><input class="field" name="my_file" type="file" /><br/><span class="upload_note">1. Only HTML (*.html, *.htm), Text (*.txt), Portable Document Format (*.pdf), Rich Text Format (*.rtf) or MS Word document (*.doc) with the file size of less than 2MB are allowed. <br />2. Only ONE resume can be uploaded to the system for every resume. <br />3. You can update your resume by clicking "<span style="font-weight: bold;">Replace Resume</span>" button in the previous page next to the file name.</span></td>
                        </tr>
                        <tr>
                            <td class="buttons_left"><input class="button" type="button" value="Cancel" onClick="show_resumes();" /></td>
                            <td class="buttons_right"><input class="button" type="submit" id="upload_resume" name="upload_resume" value="Upload" onClick="start_upload();" /></td>
                        </tr>
                    </table>
                </p>
             </form>
             <iframe id="upload_target" name="upload_target" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/blank.php" style="width:0px;height:0px;border:none;"></iframe>
        </div>
        
        <div id="div_resume_form">
            <input type="hidden" id="resume_id" name="resume_id" />
            <div id="div_resume_title"></div>
            <div id="div_tabs">
                <ul>
                    <li id="li_resumes">&lt;&lt;</li>
                    <li id="li_cover_note">Cover Note</li>
                    <li id="li_experiences">Work Experiences</li>
                    <li id="li_educations">Educations &amp; Qualifications</li>
                    <li id="li_skills">General Skills</li>
                    <li id="li_technical_skills">Technical Skills</li>
                </ul>
            </div>
            
            <div id="div_cover_note">
                <form method="post" onSubmit="return false;">
                    <table id="cover_note_form" class="cover_note_form">
                        <tr>
                            <td colspan="2" class="title">Resume Cover Note</td>
                        </tr>
                        <tr>
                            <td class="label"><label for="name">Resume Label:</label></td>
                            <td class="field"><input class="field" type="text" id="name" name="name" value="Untitled Resume" /></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="cover_note">Cover Note:</label></td>
                            <td class="field"><textarea id="cover_note" name="cover_note"></textarea></td>
                        </tr>
                        <tr>
                            <td class="buttons_left">&nbsp;</td>
                            <td class="buttons_right"><input class="button" type="button" value="Save" onClick="save_cover_note(false);" />&nbsp;<input class="button" type="button" value="Save &amp; Next >" onClick="save_cover_note(true);" /></td>
                        </tr>    
                    </table>
                </form>
            </div>
            
            <div id="div_experiences">
                <table id="experiences_form" class="experiences_form">
                    <tr>
                        <td colspan="2" class="title">Work Experiences</td>
                    </tr>
                </table>
                <form name="experiences_form">
                    <div id="experience_forms">
                    </div>
                </form>
                <table class="experiences_form">
                    <tr>
                        <td class="buttons_left"><input class="button" type="button" value="Add Work Experience" onClick="add_work_experience(null);"/></td>
                        <td class="buttons_right"><input class="button" type="button" value="Save" onClick="save_work_experiences(false);" />&nbsp;<input class="button" type="button" value="Save &amp; Next >" onClick="save_work_experiences(true);" /></td>
                    </tr>
                </table>
            </div>
            
            <div id="div_educations">
                <table id="educations_form" class="educations_form">
                    <tr>
                        <td colspan="2" class="title">Educations/Qualifications</td>
                    </tr>
                </table>
                <div id="education_forms">
                </div>
                <table class="educations_form">
                    <tr>
                        <td class="buttons_left"><input class="button" type="button" value="Add Education/Qualification" onClick="add_education(null);"/></td>
                        <td class="buttons_right"><input class="button" type="button" value="Save" onClick="save_educations(false);" />&nbsp;<input class="button" type="button" value="Save &amp; Next >" onClick="save_educations(true);" /></td>
                    </tr>
                </table>
            </div>
            
            <div id="div_skills">
                <table id="skills_form" class="skills_form">
                    <tr>
                        <td colspan="2" class="title">General Skills</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="skills">Skills:</label></td>
                        <td class="field"><textarea id="skills" name="skills"></textarea></td>
                    </tr>
                    <tr>
                        <td class="buttons_left">&nbsp;</td>
                        <td class="buttons_right"><input class="button" type="button" value="Save" onClick="save_skills(false);" /><input class="button" type="button" value="Save &amp; Next >" onClick="save_skills(true);" /></td>
                    </tr>
                </table>
            </div>
            
            <div id="div_technical_skills">
                <table id="technical_skills_form" class="technical_skills_form">
                    <tr>
                        <td colspan="2" class="title">Technical/Computer/I.T. Skills</td>
                    </tr>
                </table>
                <div id="technical_skill_forms">
                </div>
                <table class="technical_skills_form">
                    <tr>
                        <td class="buttons_left"><input class="button" type="button" value="Add Technical Skill" onClick="add_technical_skill(null);"/></td>
                        <td class="buttons_right"><input class="button" type="button" value="Save" onClick="save_technical_skills(false);" /><input class="button" type="button" value="Save &amp; Done" onClick="save_technical_skills(true);" /></td>
                    </tr>
                </table>
            </div>
            
        </div>
        <?php
    }
}
?>