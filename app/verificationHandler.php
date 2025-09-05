<?php
session_start();

// Check if the verification code is set in session
if (!isset($_SESSION['verification_code'])) {
    // Redirect back to login page if verification code is not set
    header('Location: ../login.php');
    exit();
}

if (isset($_POST['verify_code'])) {
    $enteredCode = $_POST['verify_code'];
    $storedCode = $_SESSION['verification_code'];

    if ($enteredCode == $storedCode) {
        // Verification successful, redirect to dashboard
        header('Location: ../dashboard.php');
        exit();
    } else {
        // Verification failed, redirect back to login page
        header('Location: ../login.php');
        exit();
    }
}
