<?php
session_start();
require_once '../database/db_connection.php';

$username = $_POST['username'];
$password = $_POST['password'];

// Avoid sqli injection

$username = stripslashes(mysqli_real_escape_string($conn, $username));
$password = stripslashes(mysqli_real_escape_string($conn, $password));

$access = "SELECT * FROM users WHERE email = '$username' AND password = '$password'";
$result = mysqli_query($conn, $access);
$row = mysqli_fetch_assoc($result);
$count = mysqli_num_rows($result);

if ($count == 1) {

   $verificationCode = rand(100000, 999999);
   $_SESSION['verification_code'] = $verificationCode;


   $to = $_POST['username'];
   echo $to;
   $subject = 'Verification Code';
   $message = 'Your verification code is: ' . $verificationCode;

   $headers = 'From: Mims';

   // Send email
   if (mail($to, $subject, $message, $headers)) {
      echo "Successfully send";
      header('Location: ../verification.php');
   } else {
      echo "Failed to send verification code. Please try again.";
   }

   //header('location: ../dashboard.php');
} else {
   header('location: ../login.php');
}
