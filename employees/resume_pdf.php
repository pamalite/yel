<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";
require_once dirname(__FILE__). "/../private/lib/classes/fpdf.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/employees/resume_pdf.php?id='. $_GET['id']);
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

$resume = new Resume(0, $_GET['id']);
$cover = $resume->get();

if (!is_null($cover[0]['file_name'])) {
    $file = $resume->get_file();

    header('Content-length: '. $file['size']);
    header('Content-type: '. $file['type']);
    header('Content-Disposition: attachment; filename="'. $file['name'].'"');

    readfile($GLOBALS['resume_dir']. "/". $_GET['id']. ".". $file['hash']);
    exit();
} 

$member = new Member($cover[0]['member']);
$contacts = $member->get();
$experiences = $resume->get_work_experiences();
$educations = $resume->get_educations();
$skills = $resume->get_skills();
$technical_skills = $resume->get_technical_skills();

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
        $this->Cell(0,5,$_contacts[0]['phone_num'],0,1,'L');
        
        $this->SetFont('Times','B',10);
        $this->Cell(75,5,'E-mail Address:',0,0,'R');
        $this->SetFont('Times','',10);
        $this->Cell(0,5,$_contacts[0]['email_addr'],0,1,'L');
        
        $this->SetFont('Times','B',10);
        $this->Cell(75,5,'Address:',0,0,'R');
        $this->SetFont('Times','',10);
        $this->Cell(0,5,$_contacts[0]['address'],0,1,'L');
        
        $this->SetFont('Times','B',10);
        $this->Cell(75,5,'State:',0,0,'R');
        $this->SetFont('Times','',10);
        $this->Cell(0,5,$_contacts[0]['state'],0,1,'L');
        
        $this->SetFont('Times','B',10);
        $this->Cell(75,5,'Country:',0,0,'R');
        $this->SetFont('Times','',10);
        $this->Cell(0,5,Country::country_from_code($_contacts[0]['country']),0,1,'L');
        
        $this->SetFont('Times','B',10);
        $this->Cell(75,5,'Postal Code:',0,0,'R');
        $this->SetFont('Times','',10);
        $this->Cell(0,5,$_contacts[0]['zip'],0,1,'L');
        
        $this->Ln();
    }
    
    function show_experiences($_experiences) {
        $this->SetTextColor(0);
        
        if (count($_experiences) <= 0) {
            $this->SetFont('Times','',10);
            $this->Cell(0,5,'No experience recorded',0,1,'C');
            $this->Ln();
        } else {
            foreach($_experiences as $experience) {
                $industry = Industry::get($experience['industry']);

                $this->SetFont('Times','B',10);
                $this->Cell(75,5,'Industry:',0,0,'R');
                $this->SetFont('Times','',10);
                $this->Cell(0,5,$industry[0]['industry'],0,1,'L');

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
                $this->Cell(0,5,$experience['work_summary'],0,1,'L');
                
                $this->SetFont('Times','B',10);
                $this->Cell(75,5,'Reason for Leaving:',0,0,'R');
                $this->SetFont('Times','',10);
                $this->Cell(0,5,$experience['reason_for_leaving'],0,1,'L');

                $this->Ln();
            }
        }
    }
    
    function show_educations($_educations) {
        $this->SetTextColor(0);
        
        if (count($_educations) <= 0) {
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
                $this->Cell(0,5,Country::country_from_code($education['country']),0,1,'L');
                
                $this->Ln();
            }
        }
    }
    
    function show_skills($_skills) {
        $this->SetTextColor(0);
        
        if (is_null($_skills[0]['skill']) || empty($_skills[0]['skill'])) {
            $this->SetFont('Times','',10);
            $this->Cell(0,5,'No skill recorded',0,1,'C');
            $this->Ln();
        } else {
            $this->SetFont('Times','',10);
            $this->Cell(0,5,stripslashes(htmlspecialchars_decode($_skills[0]['skill'])),0,1,'C');
            $this->Ln();
        }
    }
    
    function show_technical_skills($_technical_skills) {
        $this->SetTextColor(0);
        
        if (count($_technical_skills) <= 0) {
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
        
        if (is_null($_cover[0]['cover_note']) || empty($_cover[0]['cover_note'])) {
            $this->SetFont('Times','',10);
            $this->Cell(0,5,'No cover note provided',0,1,'C');
            $this->Ln();
        } else {
            $this->SetFont('Times','',10);
            $this->Cell(0,5,stripslashes(htmlspecialchars_decode($_cover[0]['cover_note'])),0,1,'L');
            $this->Ln();
        }
    }
}

$pdf = new ResumePdf();
$pdf->AliasNbPages();
$pdf->SetAuthor('YellowElevator.com Resume Generator. Terms of Use subjected.');
$pdf->SetTitle(htmlspecialchars_decode($member->get_name()). '\'s Resume');
$pdf->SetFontSize(10);
$pdf->AddPage('P'); 
$pdf->SetDisplayMode('real','default');

$pdf->make_title(htmlspecialchars_decode($member->get_name()). '\'s Resume');
$pdf->Ln();
$pdf->make_title('Contacts');
$pdf->show_contacts($contacts);
$pdf->make_title('Work Experiences');
$pdf->show_experiences($experiences);
$pdf->make_title('Educations');
$pdf->show_educations($educations);
$pdf->make_title('General Skills');
$pdf->show_skills($skills);
$pdf->make_title('Technical/Computer/I.T. Skills');
$pdf->show_technical_skills($technical_skills);
$pdf->make_title('Cover Note');
$pdf->show_cover_note($cover);

$pdf->Close();
$pdf->Output('resume.pdf', 'D');
?>
