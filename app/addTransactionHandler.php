<?php
session_start();
include_once '../database/db_connection.php';
$success_msg = 'New transaction added successfully';

if (isset($_POST['addtransaction'])) {

    $transaction_number = stripcslashes(mysqli_real_escape_string($conn, $_POST['transaction_number']));
    $customer_number = stripcslashes(mysqli_real_escape_string($conn, $_POST['customer_number']));
    $amount = stripcslashes(mysqli_real_escape_string($conn, $_POST['amount']));
    $date_of_transaction = stripcslashes(mysqli_real_escape_string($conn, $_POST['date_of_transaction']));


    $insert = "INSERT INTO `transactions`
    (`transaction_number`, `customers`, `amount`, `date_of_transaction`)
	 VALUES
    ('$transaction_number', '$customer_number', '$amount', '$date_of_transaction')";


    $update = "SELECT * FROM customers WHERE customer_number='$customer_number' ";


    if (mysqli_query($conn, $insert)) {

        $retrieve = mysqli_query($conn, $update);
        if (mysqli_num_rows($retrieve) > 0) {
            $result = mysqli_fetch_assoc($retrieve);
            $name = $result['first_name'];
            $email = $result['email'];
            $subject = 'Transaction';
            $message = 'There has been a transaction from: ' . $name . 'payment into RITEAID MICROCREDIT account. Received amount GHC' . $amount;
            $headers = 'From: Mims';
            if (mail($email, $subject, $message, $headers)) {
                header('Location: ../addTransaction.php');
                $_SESSION['success_msg'] = $success_msg;
            } else {
                echo "Error happened when sending transaction email";
            }
        }
    } else {
        echo "Error: " . $insert . " " . mysqli_error($conn);
    }
    mysqli_close($conn);
}
