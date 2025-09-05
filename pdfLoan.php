<?php
function generateRow()
{
    $contents = '';
    include_once 'database/db_connection.php';
    $sql = "SELECT 
    a.loans_number, b.first_name as customer, a.amount, a.date_of_transaction
    FROM loans a
     JOIN customers b on a.customer = b.customer_number
     ORDER BY date_of_transaction DESC";

    $num = 1;
    $query = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
        $contents .= "
         <tr>
             <td>" . $num++ . "</td>
             <td>" . $row['loans_number'] . "</td>
             <td>" . $row['customer'] . "</td>
             <td>Ghc " . $row['amount'] . "</td>
             <td>" . $row['date_of_transaction'] . "</td>
         </tr>
         ";
    }

    return $contents;
}

require_once 'tcpdf/tcpdf.php';
$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle("All Loan Transactions");
$pdf->SetHeaderData('Netasatta Technologies', '', PDF_HEADER_TITLE, PDF_HEADER_STRING);
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN, 12));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, 'Times new roman', PDF_FONT_SIZE_DATA, 12));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetMargins(PDF_MARGIN_LEFT, 0, PDF_MARGIN_RIGHT, 0);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(true, 10);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();
$content = '';
$content .= '
      	<h2 align="center">All Loan Request</h2>
      	<h4>customer detail sheet</h4>
      	<table border="1" cellspacing="0" cellpadding="3">
           <tr>
                <th width="4%">SN</th>
				<th width="16%">Loan Number</th>
				<th width="20%">Customer</th>
            
            <th width="15%">Amount</th>
            <th width="20%">Transaction Date</th>
           </tr>
      ';
$content .= generateRow();
$content .= '</table>';
$pdf->writeHTML($content);
$pdf->Output('customer detail.pdf', 'I');
