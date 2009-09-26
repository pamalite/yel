<?php
require_once dirname(__FILE__). "/../../utilities.php";

class MemberPhotosPage extends Page {
    private $member = NULL;
    
    function __construct($_session) {
        $this->member = new Member($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_photos_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/member_photos.css">'. "\n";
    }
    
    public function insert_member_photos_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/member_photos.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->member->id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_search($this->member->get_name(). "&nbsp;&nbsp;<span style=\"color: #FC8503;\">Photo</span>");
        $this->menu('member', 'photos');
        
        $has_photo = false;
        $photo_approved = false;
        $photos = $this->member->get_photos();
        if (count($photos) > 0 && $photos != false) {
            if ($photos[0]['approved'] == 'Y') {
                $photos = $this->member->get_approved_photos();
                $photo_approved = true;
            } else {
                $photo_approved = false;
            }
            
            $has_photo = true;
        }
        
        ?>
        <div class="banner">
            Please ensure that the photos you uploaded are decent and appropriate i.e. not of any controversial, offensive or sexually explicit in nature.<br/><br/>
            We reserve the right to disapprove your photos and remove them based on our discretion.
        </div>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_upload_photo_form">
            <form action="photos_action.php" method="post" enctype="multipart/form-data" target="upload_target">
                <input type="hidden" name="id" value="0" />
                <input type="hidden" name="member" value="<?php echo $this->member->id(); ?>" />
                <input type="hidden" name="action" value="upload" />
                <p id="upload_progress" style="text-align: center;">
                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/progress/circle_big.gif" />
                </p>
                <p id="upload_form">
                    <table class="upload_form">
                        <tr>
                            <td class="label"><label for="my_file">Photo File:</label></td>
                            <td class="field"><input class="field" name="my_file" type="file" /><br/><span class="upload_note">1. Only GIF (*.gif), JPEG (*.jpg, *.jpeg), Portable Network Graphics (*.png), TIFF (*.tiff) or Bitmap (*.bmp) with the file size of less than 2MB are allowed. <br />2. Maximum photo resolution is 1024x1024 pixels. <br />3. You can update your photo by re-uploading a new one, and delete the current one.</span></td>
                            <td class="button">
                                <?php
                                    if (($has_photo && $photo_approved) || (!$has_photo && !$photo_apprived)) {
                                ?>
                                <input class="button" type="submit" id="upload_photo" name="upload_photo" value="Upload" onClick="start_upload();" />
                                <?php
                                    } else {
                                ?>
                                <input class="button" type="submit" id="upload_photo" name="upload_photo" value="Upload" onClick="start_upload();" disabled />
                                <?php
                                    }
                                ?>
                                
                            </td>
                        </tr>
                    </table>
                </p>
             </form>
             <iframe id="upload_target" name="upload_target" src="#" style="width:0px;height:0px;border:none;"></iframe>
        </div>
        
        <div id="div_photo">
            <?php
                if (($has_photo && !$photo_approved) || (!$has_photo && $photo_approved)) {
                    echo '<span style="text-align: center; font-weight: bold; font-size: 14pt;">Awaiting approval...</span>';
                }
            ?>
            <img class="photo" id="photo" <?php echo ($has_photo && $photo_approved) ? 'src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root'] . '/members/photos_action.php?id='. $this->member->id(). '"' : ''; ?> />
        </div>
        <?php
        if ($has_photo && $photo_approved) {
        ?>
            <div class="button">
                <input class="button" type="button" id="delete_photo" name="delete_photo" value="Remove Photo" onClick="delete_photo('<?php echo $photos[0]['id']; ?>');" />
            </div>
        <?php
        }
    }
}
?>