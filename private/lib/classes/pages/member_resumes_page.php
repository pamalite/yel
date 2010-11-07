<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). '/../htmltable.php';

class MemberResumesPage extends Page {
    private $member = NULL;
    private $error_message = '';
    
    function __construct($_session) {
        $this->member = new Member($_session['id'], $_session['sid']);
    }
    
    public function set_error($_error) {
        switch ($_error) {
            case '1':
                $this->error_message = 'An error occured when trying to create a new resume placeholder.\\n\\nPlease try again later. If problem persist, please contact our technical support for further assistance.';
                break;
            case '2':
                $this->error_message = 'An error occured when trying to update your resume.\\n\\nPlease try again later. If problem persist, please contact our technical support for further assistance.';
                break;
            case '3':
                $this->error_message = 'An error occured when uploading your resume. \\n\\nPlease try again later. Please make sure that the file you are uploading is listed in the resume upload window.\\n\\nIf problem persist, please contact our technical support for further assistance.';
                break;
            default:
                $this->error_message = '';
        }
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
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/flextable.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/member_resumes.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->member->getId(). '";'. "\n";
        
        if (!empty($this->error_message)) {
            echo "alert(\"". $this->error_message. "\");\n";
        }
        
        echo '</script>'. "\n";
    }
    
    private function get_resumes() {
        $resume = new Resume();
        
        $criteria = array(
            'columns' => "id, file_name, DATE_FORMAT(modified_on, '%e %b, %Y') AS formatted_modified_on", 
            'match' => "member = '". $this->member->getId(). "' AND 
                        deleted = 'N' AND 
                        is_yel_uploaded = FALSE", 
            'order' => "modified_on DESC"
        );
        
        return $resume->find($criteria);
    }
    
    public function show() {
        $this->begin();
        $this->top_search("Resumes");
        $this->menu('member', 'resumes');
        
        $resumes = $this->get_resumes();
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <table class="buttons">
            <tr>
                <td class="right">
                    <input type="button" id="upload_new_resume" name="upload_new_resume" value="Upload Resume" onClick="show_upload_resume_popup(0);" />
                </td>
            </tr>
        </table>
        <div id="div_resumes">
        <?php
            if (empty($resumes)) {
        ?>
            <div class="empty_results">No resumes uploaded. Click &quot;Upload Resume&quot; button to upload one now.</div>
        <?php
            } else {
                $resumes_table = new HTMLTable('resumes_table', 'resumes');
                
                $resumes_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('resumes', 'modified_on');\">Modified On</a>", '', 'header');
                $resumes_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('resumes', 'file_name');\">Resume</a>", '', 'header');
                $resumes_table->set(0, 2, "&nbsp;", '', 'header actions');

                foreach ($resumes as $i=>$resume) {
                    $resumes_table->set($i+1, 0, $resume['formatted_modified_on'], '', 'cell');
                    $resumes_table->set($i+1, 1, '<a href="resume.php?id='. $resume['id']. '">'. $resume['file_name']. '</a>', '', 'cell');
                    $resumes_table->set($i+1, 2, '<a class="no_link" onClick="update_resume('. $resume['id']. ');">Update</a>', '', 'cell actions');
                    //$resumes_table->set($i+1, 2, '<a class="no_link" onClick="delete_resume('. $resume['id']. ');">Delete</a>&nbsp;|&nbsp;<a class="no_link" onClick="update_resume('. $resume['id']. ');">Update</a>', '', 'cell actions');
                }

                echo $resumes_table->get_html();
            }
        ?>
        </div>
        <table class="buttons">
            <tr>
                <td class="right">
                    <input type="button" id="upload_new_resume" name="upload_new_resume" value="Upload Resume" onClick="show_upload_resume_popup(0);" />
                </td>
            </tr>
        </table>
        
        <!-- popups goes here -->
        <div id="upload_resume_window" class="popup_window">
            <div class="popup_window_title">Upload Resume</div>
            <form id="upload_resume_form" action="resumes_action.php" method="post" enctype="multipart/form-data" onSubmit="return close_upload_resume_popup(true);">
                <div class="upload_resume_form">
                    <input type="hidden" id="resume_id" name="id" value="0" />
                    <input type="hidden" name="member" value="<?php echo $this->member->getId(); ?>" />
                    <input type="hidden" name="action" value="upload" />
                    <div id="upload_progress" style="text-align: center; width: 99%; margin: auto;">
                        Please wait while your resume is being uploaded... <br/><br/>
                        <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/progress/circle_big.gif" />
                    </div>
                    <div id="upload_field" class="upload_field">
                        <input id="my_file" name="my_file" type="file" />
                        <div style="font-size: 9pt; margin-top: 15px;">
                            <ol>
                                <li>Only HTML (*.html, *.htm), Text (*.txt), Portable Document Format (*.pdf) or MS Word document (*.doc) with the file size of less than 1MB are allowed.</li>
                                <li>You can upload as many resumes as you want and designate them for different job applications.</li>
                                <li>You can update your resume by clicking &quot;Update&quot; then upload an updated version.</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="popup_window_buttons_bar">
                     <input type="submit" value="Upload" />
                     <input type="button" value="Cancel" onClick="close_upload_resume_popup(false);" />
                </div>
            </form>
        </div>
        <?php
    }
}
?>