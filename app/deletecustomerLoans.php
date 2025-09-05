<?php
session_start();
require_once '../database/db_connection.php';

$deletecustomer = "DELETE FROM loans WHERE loans_number = '" . $_GET['loans_number'] . "'";
if (mysqli_query($conn, $deletecustomer)) {
    header('Location: ../LoanView.php');
} else {
    echo 'Something went wrong when deleting please try again';
    mysqli_close($conn);
}
