<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/members/resume_viewer.php?id='. $_GET['id']);
        exit();
    }
}

if (!isset($_SESSION['yel']['member']) || 
    empty($_SESSION['yel']['member']['id']) || 
    empty($_SESSION['yel']['member']['sid']) || 
    empty($_SESSION['yel']['member']['hash'])) {
    echo "An illegal attempt to view resume has been detected.";
    exit();
}


$resume = new Resume(0, $_GET['id']);
$cover = $resume->get();

if ($cover[0]['private'] == 'Y') {
    echo 'Sorry, the candidate had decided to lock the resume from public viewing.';
    exit();
}

if (!is_null($cover[0]['file_name'])) {
    $file = $resume->get_file();

    header('Content-type: '. $file['type']);
    header('Content-Disposition: attachment; filename="'. $file['name'].'"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-length: '. $file['size']);
    ob_clean();
    flush();
    readfile($GLOBALS['resume_dir']. "/". $_GET['id']. ".". $file['hash']);
    exit();
} 

$member = new Member($cover[0]['member']);
$contacts = $member->get();
$experiences = $resume->get_work_experiences();
$educations = $resume->get_educations();
$skills = $resume->get_skills();
$technical_skills = $resume->get_technical_skills();

$query = "SELECT COUNT(*) AS has_photo 
          FROM member_photos 
          WHERE member = '". $member->id(). "'";
$mysqli = Database::connect();
$result = $mysqli->query($query);

$has_photo = false;
if ($result[0]['has_photo'] > 0) {
    $has_photo = true;
}

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
    <?php
        if ($has_photo) {
    ?>
            <a href="candidate_photo.php?id=<?php echo $member->id(); ?>">View Photo</a>
            &nbsp;
    <?php
        }
    ?>
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
            <td class="field"><?php echo Country::country_from_code($contacts[0]['country']) ?></td>
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
                    <td class="field"><?php echo Country::country_from_code($education['country']) ?></td>
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
    <?php
        if ($has_photo) {
    ?>
            <a href="candidate_photo.php?id=<?php echo $member->id(); ?>">View Photo</a>
            &nbsp;
    <?php
        }
    ?>
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