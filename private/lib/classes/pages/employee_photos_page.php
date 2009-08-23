<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployeePhotosPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_photos_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_photos.css">'. "\n";
    }
    
    public function insert_employee_photos_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_photos.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_employee($this->employee->get_name(). " - Photos");
        $this->menu_employee($this->clearances, 'photos');
        
        ?>
        <div class="photos_policies">
            <span style="font-weight: bold;">Acceptable Photo Policies</span><br/>
            <ul>
                <li>MUST show partial or full human face.</li>
                <li>MUST be in proper attire and sensitive parts are covered, if full body is shown. (NO swim-wear)</li>
                <li>DO NOT portray violence or act of violence.</li>
                <li>MUST portray neutral or positive feelings. (eg: light smile, but not laughing)</li>
                <li>MUST be clear where objects in the photo are easily identified.</li>
            </ul>
        </div>
        
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_photos">
            <table class="header">
                <tr>
                    <td class="photo_id"><span class="sort" id="sort_photo_id">Photo ID</span></td>
                    <td class="member"><span class="sort" id="sort_member">Member</span></td>
                </tr>
            </table>
            <div id="div_photos_list">
            </div>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_photo_preview">
            <div id="member_name" style="text-align: center; padding-top: 10px; padding-bottom: 10px; font-weight: bold;"></div>
            <img id="photo" class="photo" />
            <p id="buttons" class="button"></p>
        </div>
        <?php
    }
}
?>