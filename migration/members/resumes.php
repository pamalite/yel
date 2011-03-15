<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";
require_once dirname(__FILE__). "/../../private/lib/classes/fpdf.php";

// flatten online resumes to PDF files and re-index them

$path_to_copy = $GLOBALS['data_path']. '/migrated_resumes/';
// $path_to_copy = $GLOBALS['resume_dir'];

class ResumePdf extends FPDF {
    function make_title($title) {
        $this->SetFont('Times', 'B');
        $this->SetDrawColor(204, 204, 204);
        $this->Cell(0, 5, $title, 1, 1, 'C');
        $this->SetFont('Times', '');
        $this->SetDrawColor(0, 0, 0);
    }
    
    function Header() {
        //Page header
        $this->SetFont('Times','I',7);
        $this->SetTextColor(128);
        $this->Cell(0,10,'Generated from YellowElevator.com.',0,1,'C');
        $this->SetFontSize(10);
        //$this->Ln();
    }
    
    function Footer() {
        //Page footer
        $this->SetY(-15);
        $this->SetFont('Times','I',8);
        $this->SetTextColor(128);
        $this->Cell(0,10,'Page '.$this->PageNo(). ' of {nb}',0,0,'C');
    }
    
    function show_contacts($_contacts) {
        $this->SetTextColor(0);
        
        $this->SetFont('Times','B',10);
        $this->Cell(75,5,'Telephone:',0,0,'R');
        $this->SetFont('Times','',10);
        $this->Cell(0,5,$_contacts['phone_num'],0,1,'L');
        
        $this->SetFont('Times','B',10);
        $this->Cell(75,5,'E-mail Address:',0,0,'R');
        $this->SetFont('Times','',10);
        $this->Cell(0,5,$_contacts['email_addr'],0,1,'L');
        
        $this->SetFont('Times','B',10);
        $this->Cell(75,5,'Address:',0,0,'R');
        $this->SetFont('Times','',10);
        $this->Cell(0,5,$_contacts['address'],0,1,'L');
        
        $this->SetFont('Times','B',10);
        $this->Cell(75,5,'State:',0,0,'R');
        $this->SetFont('Times','',10);
        $this->Cell(0,5,$_contacts['state'],0,1,'L');
        
        $this->SetFont('Times','B',10);
        $this->Cell(75,5,'Country:',0,0,'R');
        $this->SetFont('Times','',10);
        $this->Cell(0,5,$_contacts['country'],0,1,'L');
        
        $this->SetFont('Times','B',10);
        $this->Cell(75,5,'Postal Code:',0,0,'R');
        $this->SetFont('Times','',10);
        $this->Cell(0,5,$_contacts['zip'],0,1,'L');
        
        $this->Ln();
    }
    
    function show_experiences($_experiences) {
        $this->SetTextColor(0);
        
        if (is_null($_experiences) || empty($_experiences) || 
            count($_experiences) <= 0) {
            $this->SetFont('Times','',10);
            $this->Cell(0,5,'No experience recorded',0,1,'C');
            $this->Ln();
        } else {
            foreach($_experiences as $experience) {
                $this->SetFont('Times','B',10);
                $this->Cell(75,5,'Industry:',0,0,'R');
                $this->SetFont('Times','',10);
                $this->Cell(0,5,$experience['industry'],0,1,'L');

                $this->SetFont('Times','B',10);
                $this->Cell(75,5,'From:',0,0,'R');
                $this->SetFont('Times','',10);
                $this->Cell(0,5,$experience['from'],0,1,'L');

                $this->SetFont('Times','B',10);
                $this->Cell(75,5,'To:',0,0,'R');
                $this->SetFont('Times','',10);
                $this->Cell(0,5,$experience['to'],0,1,'L');

                $this->SetFont('Times','B',10);
                $this->Cell(75,5,'Place:',0,0,'R');
                $this->SetFont('Times','',10);
                $this->Cell(0,5,$experience['place'],0,1,'L');

                $this->SetFont('Times','B',10);
                $this->Cell(75,5,'Role:',0,0,'R');
                $this->SetFont('Times','',10);
                $this->Cell(0,5,$experience['role'],0,1,'L');

                $this->SetFont('Times','B',10);
                $this->Cell(75,5,'Work Summary:',0,0,'R');
                $this->SetFont('Times','',10);
                $this->Cell(0,5,stripslashes(htmlspecialchars_decode($experience['work_summary'])),0,1,'L');
                
                $this->SetFont('Times','B',10);
                $this->Cell(75,5,'Reason for Leaving:',0,0,'R');
                $this->SetFont('Times','',10);
                $this->Cell(0, 5, stripslashes(htmlspecialchars_decode($experience['reason_for_leaving'])),0,1,'L');

                $this->Ln();
            }
        }
    }
    
    function show_educations($_educations) {
        $this->SetTextColor(0);
        
        if (is_null($_educations) || empty($_educations) || 
            count($_educations) <= 0) {
            $this->SetFont('Times','',10);
            $this->Cell(0,5,'No education recorded',0,1,'C');
            $this->Ln();
        } else {
            foreach($_educations as $education) {
                $this->SetFont('Times','B',10);
                $this->Cell(75,5,'Qualification:',0,0,'R');
                $this->SetFont('Times','',10);
                $this->Cell(0,5,$education['qualification'],0,1,'L');

                $this->SetFont('Times','B',10);
                $this->Cell(75,5,'Completion Year:',0,0,'R');
                $this->SetFont('Times','',10);
                $this->Cell(0,5,$education['completed_on'],0,1,'L');

                $this->SetFont('Times','B',10);
                $this->Cell(75,5,'Institution:',0,0,'R');
                $this->SetFont('Times','',10);
                $this->Cell(0,5,$education['institution'],0,1,'L');

                $this->SetFont('Times','B',10);
                $this->Cell(75,5,'Country:',0,0,'R');
                $this->SetFont('Times','',10);
                $this->Cell(0,5,$education['country'],0,1,'L');
                
                $this->Ln();
            }
        }
    }
    
    function show_skills($_skill) {
        $this->SetTextColor(0);
        
        if (is_null($_skill) || empty($_skill)) {
            $this->SetFont('Times','',10);
            $this->Cell(0,5,'No skill recorded',0,1,'C');
            $this->Ln();
        } else {
            $this->SetFont('Times','',10);
            $this->Cell(0,5,stripslashes(htmlspecialchars_decode($_skill)),0,1,'C');
            $this->Ln();
        }
    }
    
    function show_technical_skills($_technical_skills) {
        $this->SetTextColor(0);
        
        if (is_null($_technical_skills) || empty($_technical_skills) || 
            count($_technical_skills) <= 0) {
            $this->SetFont('Times','',10);
            $this->Cell(0,5,'No technicall skill recorded',0,1,'C');
            $this->Ln();
        } else {
            $levels = array('A' => 'Beginner', 'B' => 'Intermediate', 'C' => 'Advanced');
            
            foreach($_technical_skills as $technical_skill) {
                $this->SetFont('Times','B',10);
                $this->Cell(75,5,'Technical SKill:',0,0,'R');
                $this->SetFont('Times','',10);
                $this->Cell(0,5,$technical_skill['technical_skill'],0,1,'L');

                $this->SetFont('Times','B',10);
                $this->Cell(75,5,'Level:',0,0,'R');
                $this->SetFont('Times','',10);
                $this->Cell(0,5,$levels[$technical_skill['level']],0,1,'L');

                $this->Ln();
            }
        }
    }
    
    function show_cover_note($_cover) {
        $this->SetTextColor(0);
        
        if (is_null($_cover) || empty($_cover)) {
            $this->SetFont('Times','',10);
            $this->Cell(0,5,'No cover note provided',0,1,'C');
            $this->Ln();
        } else {
            $this->SetFont('Times','',10);
            $this->Cell(0,5,stripslashes(htmlspecialchars_decode($_cover)),0,1,'L');
            $this->Ln();
        }
    }
}

function generate_pdf_file($_contacts, $_cover_note, $_educations, 
                           $_experiences, $_skill, $_technical_skills, 
                           $_resume_id) {
    $hash = generate_random_string_of(6);
    $file = $_resume_id. '.'. $hash;
    
    $pdf = new ResumePdf();
    $pdf->AliasNbPages();
    $pdf->SetAuthor('YellowElevator.com Resume Generator. Terms of Use subjected.');
    $pdf->SetTitle(htmlspecialchars_decode($_contacts['name']). '\'s Resume');
    $pdf->SetFontSize(10);
    $pdf->AddPage('P'); 
    $pdf->SetDisplayMode('real','default');
    
    $pdf->make_title(htmlspecialchars_decode($_contacts['name']). '\'s Resume');
    $pdf->Ln();
    $pdf->make_title('Contacts');
    $pdf->show_contacts($_contacts);
    $pdf->make_title('Work Experiences');
    $pdf->show_experiences($_experiences);
    $pdf->make_title('Educations');
    $pdf->show_educations($_educations);
    $pdf->make_title('General Skills');
    $pdf->show_skills($_skills);
    $pdf->make_title('Technical/Computer/I.T. Skills');
    $pdf->show_technical_skills($_technical_skills);
    $pdf->make_title('Cover Note');
    $pdf->show_cover_note($_cover_note);
    
    $pdf->Close();
    $pdf->Output($GLOBALS['path_to_copy']. $file, 'F');
    
    return $hash;
}

// migration starts here 

$mysqli = Database::connect();

// 1. get all online resumes
$query = "SELECT id FROM resumes
          WHERE (file_hash IS NULL OR file_hash = '')";

$result = $mysqli->query($query);
if (is_null($result) || empty($result)) {
    echo 'No online resumes found.<br/>';
    exit();
}

// 2. get the individual sections
$resumes = $result;
foreach ($resumes as $resume) {
    $id = $resume['id'];
    
    $contacts = array();
    $cover_note = '';
    $educations = array();
    $experiences = array();
    $skill = '';
    $technical_skills = array();
    
    // 2.0 get contact details 
    $query = "SELECT email_addr, phone_num, address, state, zip, 
              countries.country, 
              CONCAT(firstname, ', ', lastname) AS `name`  
              FROM members 
              LEFT JOIN countries ON countries.country_code = members.country 
              LEFT JOIN resumes ON members.email_addr = resumes.member 
              WHERE resumes.id = ". $id. " LIMIT 1";
    $result = $mysqli->query($query);
    if (!is_null($result) && count($result) > 0) {
        $contacts = $result[0];
    }
    
    // 2.1 get cover note
    $query = "SELECT cover_note FROM resumes WHERE id = ". $id. " LIMIT 1";
    $result = $mysqli->query($query);
    if (!is_null($result[0]['cover_note']) && !empty($result[0]['cover_note'])) {
        $cover_note = $result[0]['cover_note'];
    }
    
    // 2.2 get educations
    $query = "SELECT qualification, completed_on, institution, countries.country 
              FROM resume_educations 
              LEFT JOIN countries ON countries.country_code = resume_educations.country
              WHERE `resume` = ". $id. " 
              ORDER BY completed_on DESC";
    $result = $mysqli->query($query);
    if (!is_null($result) && count($result) > 0) {
        $educations = $result;
    }
    
    // 2.3 get experiences
    $query = "SELECT `from`, `to`, place, role, industries.industry, 
              work_summary, reason_for_leaving 
              FROM resume_work_experiences 
              LEFT JOIN industries ON industries.id = resume_work_experiences.industry 
              WHERE `resume` = ". $id. " 
              ORDER BY `from` DESC";
    $result = $mysqli->query($query);
    if (!is_null($result) && count($result) > 0) {
        $experiences = $result;
    }
    
    // 2.4 get skills 
    $query = "SELECT skill FROM resume_skills 
              WHERE `resume` = ". $id;
    $result = $mysqli->query($query);
    if (!is_null($result[0]['skill']) && !empty($result[0]['skill'])) {
        $skill = $result[0]['skill'];
    }
    
    // 2.5 get technical skills
    $query = "SELECT technical_skill, `level` 
              FROM resume_technical_skills 
              WHERE `resume` = ". $id. " 
              ORDER BY `level` DESC";
    $result = $mysqli->query($query);
    if (!is_null($result) && count($result) > 0) {
        $technical_skills = $result;
    }
    
    // 2.6 generate PDF file
    $hash = generate_pdf_file($contacts, $cover_note, $educations, 
                              $experiences, $skill, $technical_skills, $id);
    $file = $id. '.'. $hash;
    echo 'Written resume: '. $file. '.pdf <br/>';
    
    // 2.7 update the resume with file hash
    $url = $path_to_copy. $file;
    $query = "UPDATE resumes SET 
              file_name = 'my_resume.pdf', 
              file_hash = '". $hash. "', 
              file_size = ". filesize($url). ",
              file_type = 'application/pdf' 
              WHERE id = ". $id;
    $mysqli->execute($query);
    echo $query. '<br/>';
    
    // 2.8 merge the indices into file_text and remove the rest of the indices
    $query = "SELECT qualification, cover_note, skill, technical_skill, work_summary 
              FROM resume_index 
              WHERE resume = ". $id. " LIMIT 1"; 
    $result = $mysqli->query($query);
    $file_text = stripslashes(htmlspecialchars_decode($result[0]['cover_note']. ' '. $result[0]['qualification']. ' '. $result[0]['work_summary']. ' '. $result[0]['skill']. ' '. $result[0]['technical_skill']));
    $file_text = preg_replace("/[\s,]+/", ' ', $file_text);
    
    $query = "UPDATE resume_index SET 
              file_text = '". addslashes(htmlspecialchars($file_text)). "', 
              qualification = NULL, 
              cover_note = NULL, 
              skill = NULL, 
              technical_skill = NULL, 
              work_summary = NULL 
              WHERE resume = ". $id;
    $mysqli->execute($query);
    echo $query. '<br/>';
    
    echo '<br/>';
}

// 3. Remove 'deleted' resumes
// 3.1 Remove referrals with resumes marked deleted

$query = "DELETE FROM referrals 
          WHERE `resume` IN (SELECT id FROM resumes WHERE deleted = 'Y')";
if ($mysqli->execute($query) === false) {
    echo 'Failed to remove related referrals. <br/><br/>';
    exit();
}

// 3.2 Remove the resumes marked deleted

$query = "SELECT id, file_hash FROM resumes WHERE deleted = 'Y'";
$result = $mysqli->query($query);
if (is_null($result) || empty($result)) {
    echo 'No resumes marked for deletion. <br/><br/>';
    exit();
}

$resumes = $result;
foreach ($resumes as $resume) {
    $failed = false;
    if (is_null($resume['file_hash']) || empty($resume['file_hash'])) {
        // delete online
        $query = "DELETE FROM resume_technical_skills WHERE resume = ". $resume['id']. "; 
                  DELETE FROM resume_skills WHERE resume = ". $resume['id']. "; 
                  DELETE FROM resume_work_experiences WHERE resume = ". $resume['id']. "; 
                  DELETE FROM resume_educations WHERE resume = ". $resume['id'];
        echo $query. '<br/>';
        if ($mysqli->transact($query) === false) {
            echo '<br/>Cannot delete sections of resume ID: '. $resume['id']. '<br/><br/>';
            $failed = true;
        } else {
            echo 'Deleted sections resume ID: '. $resume['id']. '<br/>';
        }
    } else {
        // delete file
        $file_path = $GLOBALS['resume_dir']. '/'. $resume['id']. '.'. $resume['file_hash'];
        echo 'unlink '. $file_path. '<br/>';
        if (unlink($file_path) === false) {
            echo '<br/>Cannot delete resume file: '. $file_path. '<br/><br/>';
            $failed = true;
        } else {
            echo 'Deleted resume file: '. $file_path. '<br/>';
        }        
    }
    
    if (!$failed) {
        $query = "DELETE FROM resumes WHERE id = ". $resume['id'];
        echo $query. '<br/>';
        if ($mysqli->execute($query) === false) {
            echo '<br/>Cannot delete resume ID: '. $resume['id']. '<br/><br/>';
        } else {
            echo 'Deleted resume ID: '. $resume['id']. '<br/>';
        }
    }
}

echo 'Finish';
exit();
?>
