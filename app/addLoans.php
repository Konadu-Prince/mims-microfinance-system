<?php
session_start();
include_once '../database/db_connection.php';
$success_msg = 'New Loan added successfully';

if (isset($_POST['addLoans'])) {

    $loan_number = stripcslashes(mysqli_real_escape_string($conn, $_POST['loan_number']));
    $customer_number = stripcslashes(mysqli_real_escape_string($conn, $_POST['customer_number']));
    $amount = stripcslashes(mysqli_real_escape_string($conn, $_POST['amount']));
    $date_of_transaction = stripcslashes(mysqli_real_escape_string($conn, $_POST['date_of_transaction']));


    $insert = "INSERT INTO `loans`
    (`loans_number`, `customer`, `amount`, `date_of_transaction`)
	 VALUES
    ('$loan_number', '$customer_number', '$amount', '$date_of_transaction')";


    if (mysqli_query($conn, $insert)) {
        header('Location: ../loans.php');
        $_SESSION['success_msg'] = $success_msg;
    } else {
        echo "Error: " . $insert . " " . mysqli_error($conn);
    }
    mysqli_close($conn);
}
