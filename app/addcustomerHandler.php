<?php
session_start();
include_once '../database/db_connection.php';
$success_msg = 'New customer created successfully';

if (isset($_POST['addcustomer'])) {

    $customer_number = stripcslashes(mysqli_real_escape_string($conn, $_POST['customer_number']));
    $customer_type = stripcslashes(mysqli_real_escape_string($conn, $_POST['customer_type']));
    $first_name = stripcslashes(mysqli_real_escape_string($conn, $_POST['first_name']));
    $middle_name = stripcslashes(mysqli_real_escape_string($conn, $_POST['middle_name']));
    $surname = stripcslashes(mysqli_real_escape_string($conn, $_POST['surname']));
    $gender = stripcslashes(mysqli_real_escape_string($conn, $_POST['gender']));
    $date_of_birth = stripcslashes(mysqli_real_escape_string($conn, $_POST['date_of_birth']));
    $nationality = stripcslashes(mysqli_real_escape_string($conn, $_POST['nationality']));
    $hometown = stripcslashes(mysqli_real_escape_string($conn, $_POST['hometown']));
    $email = stripcslashes(mysqli_real_escape_string($conn, $_POST['email']));
    $contact = stripcslashes(mysqli_real_escape_string($conn, $_POST['contact']));

    $insert = "INSERT INTO customers
    (customer_number, customer_type, first_name, middle_name, surname, gender, date_of_birth, nationality, hometown, email, contact)
	 VALUES
    ('$customer_number', '$customer_type', '$first_name', '$middle_name', '$surname', '$gender', '$date_of_birth', '$nationality', '$hometown', '$email', '$contact')";

    if (mysqli_query($conn, $insert)) {
        header('Location: ../addcustomer.php');
        $_SESSION['success_msg'] = $success_msg;
    } else {
        echo "Error: " . $insert . " " . mysqli_error($conn);
    }
    mysqli_close($conn);
}
