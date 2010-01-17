<?php
require_once dirname(__FILE__). "/../private/lib/classes/fpdf.php";

class SubscriptionInvoice extends FPDF {
    private $invoiceType;
    private $invoice_or_receipt;
    private $currency;
    private $branch;
    
    function SetCurrency($_currency) {
        $this->currency = $_currency;
    }
    
    function SetBranch($_branch) {
        $this->branch = $_branch;
    }
    
    //Page header
    function Header()   {
        //Logo
        $this->Image($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/images/logos/top_letterhead_pdf.png', 10, 8);
        //Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        //Move to the right
        $this->Cell(80);
        //Title
        $this->Cell(30, 20, "Subscription Invoice", 0, 0, 'C');
        $this->Cell(30);
        $this->SetFont('Arial', '', 5);
        $this->Cell(5, 3, "Phone:", 0, 0);
        $this->Cell(11);
        $this->Cell(5, 3, $this->branch[0]['phone'], 0, 2);
        $this->Cell(-16);
        
        $this->Cell(5, 3, "Fax:", 0, 0);
        $this->Cell(11);
        $this->Cell(5, 3, $this->branch[0]['fax'], 0, 2);
        $this->Cell(-16);
        
        $this->Cell(5, 3, "E-mail:", 0, 0);
        $this->Cell(11);
        $this->Cell(5, 3, "sales.". $this->branch[0]['country_code']. "@yellowelevator.com", 0, 2);
        $this->Cell(-16);
        
        $this->Cell(5, 3, "Mailing Address:", 0, 0);
        $this->Cell(11);
        foreach ($this->branch['address_lines'] as $i=>$line) {
            if ($i == count($this->branch['address_lines']) - 1) {
                $line = $line. ", ";
            }
            $this->Cell(5, 3, $line, 0, 2);
        }
        $this->Cell(5, 3, $this->branch[0]['zip']. " ". $this->branch[0]['state']. ", ". $this->branch[0]['country_name']. ". ", 0, 2);
        
        $this->SetFont('Arial','',12);
        //Line break
        $this->Ln(5);
    }

    function Footer() {
        //Page footer
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(128);
        $this->Cell(0,10,'Page '.$this->PageNo(). ' of {nb}',0,0,'C');
    }

    function FancyTable($header, $data, $_amount_payable) {
        //Colors, line width and bold font
        $this->SetFillColor(54, 54, 54);
        $this->SetTextColor(255);
        $this->SetDrawColor(255, 255, 255);
        $this->SetLineWidth(0.3);
        //$this->SetFont('','B');
        //Header
        $w = array(10, 135, 45);
        for($i=0; $i < count($header); $i++) {
            $this->Cell($w[$i], 5, $header[$i], 1, 0, 'C', 1);
        }
        
        $this->Ln();
        //Color and font restoration
        $this->SetFillColor(204, 204, 204);
        $this->SetTextColor(0);
        $this->SetFont('');
        //Data
        $fill=0;
        $count = 1;
        foreach($data as $row) {
            $this->Cell($w[0], 5, $count, 'L', 0, 'L', $fill);
            $this->Cell($w[1], 5, stripslashes(htmlspecialchars_decode($row['itemdesc'])), 'L', 0, 'L', $fill);
            $this->Cell($w[2], 5, $row['amount'], 'L', 0,'R', $fill);
            $this->Ln();
            $fill = !$fill;
            $count++;
        }
        $this->Cell(array_sum($w),0,'','T');
        $this->Ln();
        $this->SetFillColor(54, 54, 54);
        $this->SetDrawColor(54, 54, 54);
        $this->SetTextColor(255);
        $this->Cell($w[0]+$w[1], 5, "Total Amount Payable (". $this->currency. ") ", 1, 0, 'R', 1);
        $this->SetTextColor(0);
        $this->SetFont('', 'B');
        $this->SetDrawColor(0, 0, 0);
        $this->Cell($w[2], 5, $_amount_payable, 1, 0, 'R', 0);
        $this->SetFont('Arial', '', 10);
    }
}

?>