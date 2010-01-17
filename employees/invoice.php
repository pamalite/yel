<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/employees/invoice.php?id='. $_GET['id']);
        exit();
    }
}

if (!isset($_SESSION['yel']['employee']) || 
    empty($_SESSION['yel']['employee']['uid']) || 
    empty($_SESSION['yel']['employee']['id']) || 
    empty($_SESSION['yel']['employee']['sid']) || 
    empty($_SESSION['yel']['employee']['hash'])) {
    echo "An illegal attempt to view invoice has been detected.";
    exit();
}

$clearances = $_SESSION['yel']['employee']['security_clearances'];
if (!Employee::has_clearance_for('invoices_view', $clearances)) {
    echo 'No permisison to view invoice.';
    exit();
}

$invoice = Invoice::get($_GET['id']);
$items = Invoice::get_items_of($_GET['id']);

if (!$invoice) {
    echo "Invoice not found.";
    exit();
}

$employer = new Employer($invoice[0]['employer']);
$query = "SELECT currencies.symbol 
          FROM currencies 
          LEFT JOIN employers ON currencies.country_code = employers.country 
          WHERE employers.id = '". $employer->id(). "' LIMIT 1";
$mysqli = Database::connect();
$result = $mysqli->query($query);
$currency = '???';
if (count($result) > 0 && !is_null($result)) {
    $currency = $result[0]['symbol'];
}

$amount_payable = 0.00;
foreach($items as $i=>$item) {
    $amount_payable += $item['amount'];
    $items[$i]['amount'] = number_format($item['amount'], 2, '.', ', ');
}
$amount_payable = number_format($amount_payable, 2, '.', ', ');
$invoice_or_receipt = (is_null($invoice[0]['paid_on']) || empty($invoice[0]['paid_on'])) ? 'Invoice' : 'Receipt';
$branch = $employer->get_branch();
?>

<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<?php 
echo '<title>'. $GLOBALS['COMPANYNAME']. ' - Invoice '. pad($_GET['id'], 11, '0'). '</title>'. "\n";
echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/common.css">'. "\n";
echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/invoice.css">'. "\n";
?>
</head>
<body>
<div id="div_buttons">
    <input class="button" type="button" value="Save as PDF" onClick="location.replace('invoice_pdf.php?id=<?php echo $_GET['id'] ?>');"/>
    &nbsp;
    <input class="button" type="button" value="Print Invoice" onClick="window.print();"/>
    &nbsp;
    <input class="button" type="button" value="Close" onClick="window.close();"/>
</div>
<br/>

<div class="section">
    <table class="header_invoice">
        <tr>
            <td class="logo"><img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/logos/top_letterhead.png"></td>
            <td class="type">
                <?php
                    switch ($invoice[0]['type']) {
                        case 'J':
                            echo "Subscription ". $invoice_or_receipt;
                            break;
                        case 'R':
                            echo "Service Fee ". $invoice_or_receipt;
                            break;
                        default:
                            echo "Miscellanous ". $invoice_or_receipt;
                            break;
                    }
                ?>
            </td>
            <td class="contacts">
                <div class="contacts">
                    <table class="contacts">
                        <tr>
                            <td class="field">Telephone:</td>
                            <td class="value"><?php echo $branch[0]['phone'] ?></td>
                        </tr>
                        <tr>
                            <td class="field">Fax:</td>
                            <td class="value"><?php echo $branch[0]['fax'] ?></td>
                        </tr>
                        <tr>
                            <td class="field">E-mail:</td>
                            <td class="value">sales@yellowelevator.com</td>
                        </tr>
                        <tr>
                            <td class="field">Mailing Address:</td>
                            <td class="value">
                                <?php echo str_replace(array("\r\n", "\r", "\n"), '<br/>', $branch[0]['address']). ', <br/>'. 
                                           $branch[0]['zip']. ' '. 
                                           $branch[0]['state'], ', '. 
                                           $branch[0]['country_name']. '.'; ?><br/>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="invoiceinfo">
    <table class="invoiceinfo">
        <tr>
            <td class="field">Invoice Number</td>
            <td class="field">Issuance Date</td>
            <?php
                if (is_null($invoice[0]['paid_on']) || empty($invoice[0]['paid_on'])) {
            ?>
            <td class="field">Payable By</td>
            <?php
                } else {
            ?>
            <td class="field">Paid On</td>
            <?php
                }
            ?>
            <td class="field">Amount Payable (<?php echo $currency; ?>)</td>
        </tr>
        <tr>
            <td class="value"><?php echo pad($_GET['id'], 11, '0') ?></td>
            <td class="value"><?php echo $invoice[0]['issued_on'] ?></td>
            <?php
                if (is_null($invoice[0]['paid_on']) || empty($invoice[0]['paid_on'])) {
            ?>
            <td class="value"><?php echo $invoice[0]['payable_by'] ?></td>
            <?php
                } else {
            ?>
            <td class="value"><?php echo $invoice[0]['paid_on'] ?></td>
            <?php
                }
            ?>
            <td class="value"><?php echo $amount_payable ?></td>
        </tr>
        <tr>
            <td class="field">User ID</td>
            <td class="field" colspan="3">Employer Name</td>
        </tr>
        <tr>
            <td class="value"><?php echo $invoice[0]['employer'] ?></td>
            <td class="value" colspan="3"><?php echo $employer->get_name(); ?></td>
        </tr>
    </table>
</div>

<div class="items">
    <table class="items">
        <tr class="header">
            <td>No.</td>
            <td class="item">Item</td>
            <td class="amount">Amount (<?php echo $currency; ?>)</td>
        </tr>
        <?php
            $count = 1;
            foreach($items as $item) {
                if (($count % 2) == 0) {
        ?><tr class="odd"><?php
                } else {
        ?><tr class="even"><?php
                }
        ?>
            <td><?php echo $count ?></td>
        <?php
        ?>
            <td class="item"><?php echo $item['itemdesc'] ?></td>
        <?php
        ?>  
            <td class="amount"><?php echo $item['amount'] ?></td>
        </tr>
        <?php
                $count++;
            }
        ?>
        <tr>
            <td class="total" colspan="2">Total Amount Payable (<?php echo $currency; ?>) &nbsp;</td>
            <td class="total_amount"><?php echo $amount_payable ?></td>
        </tr>
    </table>
</div>

<div class="footer">
    This invoice was automatically generated. Signature is not required.<br/><br/>
    <div class="payment_note">
        <span style="{font-weight: bold; text-decoration: underline}">Payment Notice</span>
        <ul>
            <li>Payment shall be made payable to Yellow Elevator Sdn. Bhd.</li>
            <li>To facilitate the processing of the payment, please write down the invoice number(s) on your cheque(s)/payment slip(s)</li>
            </ul>
    </div>
    <br/><br/>E. &amp; O. E.
</div>

<div id="div_buttons">
    <input class="button" type="button" value="Save as PDF" onClick="location.replace('invoice_pdf.php?id=<?php echo $_GET['id'] ?>');"/>
    &nbsp;
    <input class="button" type="button" value="Print Invoice" onClick="window.print();"/>
    &nbsp;
    <input class="button" type="button" value="Close" onClick="window.close();"/>
</div>

</body>
</html>