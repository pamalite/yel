<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        if (isset($GET['id'])) {
            redirect_to('https://'. $GLOBALS['root']. '/employees/resume.php?id='. $_GET['id']);
        } else {
            redirect_to('https://'. $GLOBALS['root']. '/employees/resume.php?job_id='. $_GET['job_id']. '&candidate_email='. $_GET['candidate_email']. '&referrer_email='. $_GET['referrer_email']);
        }
        exit();
    }
}

if (!isset($_SESSION['yel']['employee']) || 
    empty($_SESSION['yel']['employee']['id']) || 
    empty($_SESSION['yel']['employee']['sid']) || 
    empty($_SESSION['yel']['employee']['hash'])) {
    echo "An illegal attempt to view resume has been detected.";
    exit();
}

if (isset($_SESSION['yel']['employee']['dev'])) {
    if ($_SESSION['yel']['employee']['dev'] === true) {
        $is_dev = false;
        $root_items = explode('/', $GLOBALS['root']);
        foreach ($root_items as $value) {
            if ($value == 'yel') {
                $is_dev = true;
                break;
            }
        }

        if (!$is_dev) {
            ?>
            <script type="text/javascript">alert('Please logout from your existing connection before proceeding.');</script>
            <?php
            exit();
        }
    }
}

if (!isset($_GET['id'])) {
    $query = "SELECT file_name, file_hash, file_size, file_type 
              FROM users_contributed_resumes 
              WHERE job_id = ". $_GET['job_id']. " AND 
              candidate_email_addr = '". $_GET['candidate_email']. "' AND 
              referrer_email_addr = '". $_GET['referrer_email']. "' LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if (is_null($result[0]['file_name'])) {
        echo 'The resume is corrupted.';
        exit();
    }
    
    header('Content-type: '. $result[0]['file_type']);
    header('Content-Disposition: attachment; filename="'. $result[0]['file_name'].'"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-length: '. $result[0]['file_size']);
    ob_clean();
    flush();
    readfile($GLOBALS['buffered_resume_dir']. "/". $result[0]['file_hash']);
    exit();
}

$resume = new Resume(0, $_GET['id']);
$cover = $resume->get();

$member = new Member($cover[0]['member']);
$query = "SELECT COUNT(*) AS has_photo 
          FROM member_photos 
          WHERE member = '". $member->id(). "'";
$mysqli = Database::connect();
$result = $mysqli->query($query);

$has_photo = false;
if ($result[0]['has_photo'] > 0) {
    $has_photo = true;
}

if (!is_null($cover[0]['file_name'])) {
    if ($has_photo) {
        ?>
            <div style="text-align: center;">
                <a href="https://<?php echo $GLOBALS['root']. '/employees/resume_download.php?id='. $_GET['id'] ?>">
                    Click here to download the resume.
                </a>
            </div>
            <br/>
            <div style="text-align: center;">
                <img src="candidate_photo.php?id=<?php echo $member->id() ?>" style="border: none;" />
            </div>
        <?php
    } else {
        ?>
            <div style="text-align: center;">
                <a href="https://<?php echo $GLOBALS['root']. '/employees/resume_download.php?id='. $_GET['id'] ?>">
                    Click here to download the resume.
                </a>
            </div>
        <?php
    }
    exit();
} 

$contacts = $member->get();
$experiences = $resume->get_work_experiences();
$educations = $resume->get_educations();
$skills = $resume->get_skills();
$technical_skills = $resume->get_technical_skills();

?>

<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<?php 
echo '<title>'. $GLOBALS['COMPANYNAME']. ' - '. htmlspecialchars_decode($member->get_name()). '\'s Resume</title>'. "\n";
echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/common.css">'. "\n";
echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employer_referrals.css">'. "\n";
?>

</head>
<body>
<p class="disclaimer">Generated from YellowElevator.com.</p>
<div id="div_buttons">
    <input class="button" type="button" value="Save as XML" onClick="location.replace('resume_xml.php?id=<?php echo $_GET['id'] ?>');"/>
    &nbsp;
    <input class="button" type="button" value="Save as PDF" onClick="location.replace('resume_pdf.php?id=<?php echo $_GET['id'] ?>');"/>
    &nbsp;
    <input class="button" type="button" value="Print Resume" onClick="window.print();"/>
    &nbsp;
    <input class="button" type="button" value="Close" onClick="window.close();"/>
</div>
<br/>

<div class="section">
    <table class="section">
        <tr>
            <td colspan="2" class="title"><?php echo htmlspecialchars_decode($member->get_name()). "'s Resume";?></td>
        </tr>
        <tr>
            <td colspan="2" class="separator"></td>
        </tr>
        <?php 
            if ($has_photo) {
                ?>
        <tr>
            <td colspan="2" class="title" style="border: none; ">
                <img style="border: none; vertical-align: top;" src="candidate_photo.php?id=<?php echo $member->id(); ?>" />
            </td>
        </tr>
        <tr>
            <td colspan="2" class="separator"></td>
        </tr>
                <?php
            }
        ?>
        <tr>
            <td colspan="2" class="title">Contacts</td>
        </tr>
        <tr>
            <td class="label">Telephone:</td>
            <td class="field"><?php echo $contacts[0]['phone_num'] ?></td>
        </tr>
        <tr>
            <td class="label">E-mail Address:</td>
            <td class="field"><?php echo $contacts[0]['email_addr'] ?></td>
        </tr>
        <tr>
            <td class="label">Address:</td>
            <td class="field"><?php echo htmlspecialchars_decode($contacts[0]['address']) ?></td>
        </tr>
        <tr>
            <td class="label">State/Province:</td>
            <td class="field"><?php echo $contacts[0]['state'] ?></td>
        </tr>
        <tr>
            <td class="label">Country:</td>
            <td class="field"><?php echo Country::getCountryFrom($contacts[0]['country']) ?></td>
        </tr>
        <tr>
            <td class="label">Postal Code:</td>
            <td class="field"><?php echo $contacts[0]['zip'] ?></td>
        </tr>
    </table>
</div>

<div class="section">
    <table class="section">
        <tr>
            <td colspan="2" class="title">Work Experiences</td>
        </tr>
    </table>
    <div class="section">
        <?php
            if (count($experiences) <= 0) {
                echo 'No work experience recorded.';
            } else {
                
        ?>
        <table class="section">
        <?php
            $i = 0;
            foreach($experiences as $experience) {
                $industry = Industry::get($experience['industry']);
        ?>
            <tr>
                <td class="label">Industry:</td>
                <td class="field"><?php echo $industry[0]['industry'] ?></td>
            </tr>
            <tr>
                <td class="label">From:</td>
                <td class="field"><?php echo $experience['from'] ?></td>
            </tr>
            <tr>
                <td class="label">To:</td>
                <td class="field"><?php echo $experience['to'] ?></td>
            </tr>
            <tr>
                <td class="label">Place:</td>
                <td class="field"><?php echo $experience['place'] ?></td>
            </tr>
            <tr>
                <td class="label">Role:</td>
                <td class="field"><?php echo $experience['role'] ?></td>
            </tr>
            <tr>
                <td class="label">Work Summary:</td>
                <td class="field"><?php echo htmlspecialchars_decode($experience['work_summary']) ?></td>
            </tr>
            <tr>
                <td class="label">Reason for Leaving:</td>
                <td class="field"><?php echo htmlspecialchars_decode($experience['reason_for_leaving']) ?></td>
            </tr>
        <?php
                if ($i < count($experiences) - 1) {
        ?>
            <tr>
                <td colspan="2" class="separator"></td>
            </tr>
        <?php
                }
                
                $i++;
            }
        ?>
        </table>
        <?php
            }
        ?>
    </div>
</div>

<div class="section">
    <table class="section">
        <tr>
            <td colspan="2" class="title">Educations/Qualifications</td>
        </tr>
    </table>
    <div class="section">
        <?php
            if (count($educations) <= 0) {
                echo 'No education recorded.';
            } else {
                
        ?>
        <table class="section">
            <?php
                $i = 0;
                foreach($educations as $education) {
            ?>
                <tr>
                    <td class="label">Qualification:</td>
                    <td class="field"><?php echo $education['qualification'] ?></td>
                </tr>
                <tr>
                    <td class="label">Completion Year:</td>
                    <td class="field"><?php echo $education['completed_on'] ?></td>
                </tr>
                <tr>
                    <td class="label">Institution:</td>
                    <td class="field"><?php echo $education['institution'] ?></td>
                </tr>
                <tr>
                    <td class="label">Country:</td>
                    <td class="field"><?php echo Country::getCountryFrom($education['country']) ?></td>
                </tr>
            <?php
                    if ($i < count($educations) - 1) {
            ?>
                <tr>
                    <td colspan="2" class="separator"></td>
                </tr>
            <?php
                    }

                    $i++;
                }
            ?>
        </table>
        <?php
        }
        ?>
    </div>
</div>

<div class="section">
    <table class="section">
        <tr>
            <td class="title">General Skills</td>
        </tr>
        <tr>
            <td class="field">
                <div style="margin: 10px 10px 10px 10px; font-size: 10pt; text-align: center;">
                <?php
                    if (is_null($skills[0]['skill']) || empty($skills[0]['skill'])) {
                        echo 'No general skill recorded.';
                    } else {
                        echo htmlspecialchars_decode($skills[0]['skill']);
                    }
                ?>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <table class="section">
        <tr>
            <td colspan="2" class="title">Technical/Computer/I.T. Skills</td>
        </tr>
    </table>
    <div class="section">
        <?php
            if (count($technical_skills) <= 0) {
                echo 'No technical skill recorded.';
            } else {
                
        ?>
        <table class="section">
            <?php
                $i = 0;
                $levels = array('A' => 'Beginner', 'B' => 'Intermediate', 'C' => 'Advanced');
                foreach($technical_skills as $technical_skill) {
            ?>
                <tr>
                    <td class="label">Technical Skill:</td>
                    <td class="field"><?php echo $technical_skill['technical_skill'] ?></td>
                </tr>
                <tr>
                    <td class="label">Level:</td>
                    <td class="field"><?php echo $levels[$technical_skill['level']] ?></td>
                </tr>
            <?php
                    if ($i < count($technical_skills) - 1) {
            ?>
                <tr>
                    <td colspan="2" class="separator"></td>
                </tr>
            <?php
                    }

                    $i++;
                }
            ?>
        </table>
        <?php
        }
        ?>
    </div>
</div>

<div class="section">
    <table class="section">
        <tr>
            <td class="title">Cover Note</td>
        </tr>
        <tr>
            <td class="field">
                <div style="margin: 10px 10px 10px 10px; font-size: 10pt;">
                <?php
                    if (is_null($cover[0]['cover_note']) || empty($cover[0]['cover_note'])) {
                        echo '<p style="text-align: center;">No cover note provided.</p>';
                    } else {
                        echo stripslashes(htmlspecialchars_decode($cover[0]['cover_note']));
                    }
                ?>
                </div>
            </td>
        </tr>
    </table>
</div>

<div id="div_buttons">
    <input class="button" type="button" value="Save as XML" onClick="location.replace('resume_xml.php?id=<?php echo $_GET['id'] ?>');"/>
    &nbsp;
    <input class="button" type="button" value="Save as PDF" onClick="location.replace('resume_pdf.php?id=<?php echo $_GET['id'] ?>');"/>
    &nbsp;
    <input class="button" type="button" value="Print Resume" onClick="window.print();"/>
    &nbsp;
    <input class="button" type="button" value="Close" onClick="window.close();"/>
</div>

<p class="disclaimer">Generated from YellowElevator.com.</p>
</body>
</html>