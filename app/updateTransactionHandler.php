<?php
session_start();
include_once '../database/db_connection.php';
$success_msg = 'transaction updated  successfully';

if (isset($_POST['addtransaction'])) {

    $transaction_number = stripcslashes(mysqli_real_escape_string($conn, $_POST['transaction_number']));
    $customer_number = stripcslashes(mysqli_real_escape_string($conn, $_POST['customer_number']));
    $amount = stripcslashes(mysqli_real_escape_string($conn, $_POST['amount']));
    $date_of_transaction = stripcslashes(mysqli_real_escape_string($conn, $_POST['date_of_transaction']));


    $update = "UPDATE transactions set customers =' $customer_number', amount='$amount', date_of_transaction='$date_of_transaction' where transaction_number='$transaction_number' ";
    if (mysqli_query($conn, $update)) {
        $_SESSION['success_msg'] = "Transaction has been updated successfully.";
        header('Location: ../updateTransaction.php?transaction_number=' . $transaction_number);
    } else {
        echo "Error: " . $insert . " " . mysqli_error($conn);
    }
    mysqli_close($conn);
}
