<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";
require_once dirname(__FILE__). "/../private/lib/classes/fpdf.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/employers/testimony_pdf.php?id='. $_GET['id']);
        exit();
    }
}

if (!isset($_SESSION['yel']['employer']) || 
    empty($_SESSION['yel']['employer']['id']) || 
    empty($_SESSION['yel']['employer']['sid']) || 
    empty($_SESSION['yel']['employer']['hash'])) {
    echo "An illegal attempt to view resume has been detected.";
    exit();
}

$mysqli = Database::connect();
$query = "SELECT referrals.member, referrals.testimony, jobs.title 
          FROM referrals 
          LEFT JOIN jobs ON jobs.id = referrals.job 
          WHERE referrals.id = ". $_GET['id']. " LIMIT 1";
$result = $mysqli->query($query);

if (is_null($result[0]['testimony']) || empty($result[0]['testimony'])) {
    ?><div style="text-align: center;">No testimony found.</div><?php
    exit();
} 

$testimony_raw = $result[0]['testimony'];
$testimonies = explode('&lt;br/&gt;', $testimony_raw);

$tmp = array();
$i = 0;
foreach ($testimonies as $testimony) {
    if (!empty($testimony)) {
        $tmp[$i] = $testimony;
        $i++;
    }
}
$testimonies = $tmp;

$member = new Member($result[0]['member']);
$job = $result[0]['title'];

class TestimonyPdf extends FPDF {
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
    
    function show_testimony($_lines) {
        $this->SetTextColor(0);
        $this->SetFont('Times','',10);
        
        $this->Ln();
        foreach ($_lines as $line) {
            if (!empty($line)) {
                $this->Cell(0,5,stripslashes(htmlspecialchars_decode($line)),0,1,'L');
                $this->Ln();
            }
        }
    }
}

$pdf = new TestimonyPdf();
$pdf->AliasNbPages();
$pdf->SetAuthor('YellowElevator.com Testimony Generator. Terms of Use subjected.');
$pdf->SetTitle(htmlspecialchars_decode($member->get_name()). '\'s Testimony');
$pdf->SetFontSize(10);
$pdf->AddPage('P'); 
$pdf->SetDisplayMode('real','default');

$pdf->make_title(htmlspecialchars_decode($member->get_name()). '\'s Testimony for '. $job. ' position');
$pdf->show_testimony($testimonies);

$pdf->Close();
$pdf->Output($member->id(). '_'. $job. '_testimony.pdf', 'D');
?>
