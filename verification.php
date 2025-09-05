<?php require_once 'include/head.php' ?>

<body>
    <div class="first-form" style="text-align: center; margin-top: 50px;">
        <h2 style="color: #333;">Enter Verification Code</h2>
        <form action="app/verificationHandler.php" method="post" style="display: inline-block; text-align: left;">
            <input type="text" name="verify_code" placeholder="Enter code" required style="padding: 10px; margin: 5px; border: 1px solid #ccc; border-radius: 5px; width: 200px;">
            <button type="submit" style="padding: 10px 20px; background-color: #0d76ba; color: white; border: none; border-radius: 5px; cursor: pointer;">Verify</button>


        </form>

    </div>
</body>

<?php require_once 'include/footer.php' ?>