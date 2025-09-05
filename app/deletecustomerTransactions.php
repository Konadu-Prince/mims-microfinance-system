<?php
session_start();
require_once '../database/db_connection.php';

$deletecustomer = "DELETE FROM transactions WHERE transaction_number = '" . $_GET['transaction_number'] . "'";
if (mysqli_query($conn, $deletecustomer)) {
    header('Location: ../Transactions.php');
} else {
    echo 'Something went wrong when deleting please try again';
    mysqli_close($conn);
}
