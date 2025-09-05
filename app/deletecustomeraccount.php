<?php
session_start();
require_once '../database/db_connection.php';

$deletecustomer = "DELETE FROM accounts WHERE account_number = '" . $_GET['account_number'] . "'";
if (mysqli_query($conn, $deletecustomer)) {
    header('Location: ../manageaccountcustomers.php');
} else {
    echo 'Something went wrong when deleting please try again';
    mysqli_close($conn);
}
