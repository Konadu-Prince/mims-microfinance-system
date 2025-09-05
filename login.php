<?php require_once 'include/head.php' ?>

<body>
  <div class="first-form" style="text-align: center; margin-top: 50px;">
    <form action="app/loginHandler.php" method="post" autocomplete="off" style="display: inline-block; text-align: left;">
      <div style="text-align: center;">
        <img src="assets/img/rite.jpg" alt="Avatar" class="avatar" style="width: 40%; border-radius: 10%;">
      </div>

      <div style="padding: 16px;">
        <label for="username" style="display: block;"><b>Username</b></label>
        <input type="text" placeholder="Enter Username" name="username" required style="width: 100%; padding: 12px 20px; margin: 8px 0; display: inline-block; border: 1px solid #ccc; box-sizing: border-box;">

        <label for="password" style="display: block;"><b>Password</b></label>
        <input type="password" placeholder="Enter Password" name="password" required style="width: 100%; padding: 12px 20px; margin: 8px 0; display: inline-block; border: 1px solid #ccc; box-sizing: border-box;">

        <button type="submit" name="login" style="background-color: #0d76ba; color: white; padding: 14px 20px; margin: 8px 0; border: none; cursor: pointer; width: 100%;">Sign in</button>
        <label style="margin-bottom: 0;">
          <input type="checkbox" checked="checked" name="remember" style="display: inline-block; margin-right: 5px;"> Remember me
        </label>
      </div>
    </form>
  </div>
</body>
<?php require_once 'include/footer.php' ?>