<?php
session_start();
require_once 'include/head.php';
require_once 'database/db_connection.php';
?>

<body>

  <?php require_once 'include/navbar.php'; ?>
  <?php require_once 'include/sidebar.php'; ?>

  <!-- Main content -->

  <section class="content my-4 w-100">
    <div class="container-fluid">
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <!-- ./col -->
        <!-- ./col -->
        <div class="col-lg-3 col-6">
          <!-- small box -->
          <?php
          $sql = "SELECT count(*) AS total_customer FROM customers";
          $result = mysqli_query($conn, $sql);
          $data = mysqli_fetch_assoc($result);
          ?>
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?php echo $data['total_customer']; ?></h3>

              <p>Total Customers</p>
            </div>
            <div class="icon">
              <i class="fas fa-users"></i>
            </div>
            <a href="managecustomer.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
          <!-- small box -->
          <?php
          $sql = "SELECT count(*) AS total_accounts FROM accounts";
          $result = mysqli_query($conn, $sql);
          $data = mysqli_fetch_assoc($result);
          ?>

          <div class="small-box bg-danger">
            <div class="inner">
              <h3><?php echo $data['total_accounts']; ?></h3>

              <p>Opened Accounts</p>
            </div>
            <div class="icon">
              <i class="fas fa-user    "></i>
            </div>
            <a href="manageaccountcustomers.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
          <!-- small box -->
          <?php
          $sql = "SELECT count(*) AS total_account FROM transactions";
          $result = mysqli_query($conn, $sql);
          $data = mysqli_fetch_assoc($result);
          ?>
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?php echo $data['total_account']; ?></h3>

              <p>Transactions</p>
            </div>
            <div class="icon">
              <i class="fas fa-money-bill"></i>
            </div>
            <a href="Transactions.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->



        <div class="col-lg-3 col-6">
          <!-- small box -->
          <?php
          $sql = "SELECT COUNT(*) AS total
          FROM (
              SELECT 
                  a.transaction_number, 
                  b.first_name AS customer, 
                  c.account_type AS account_type, 
                  d.account_type_name AS account_type_name, 
                  a.amount, 
                  a.date_of_transaction
              FROM transactions a
              JOIN customers b ON a.customers = b.customer_number
              JOIN accounts c ON a.customers = c.customer
              JOIN account_type d ON c.account_type = d.account_type_number
              WHERE d.account_type_name = 'Susu Account'
          ) AS subquery";
          $result = mysqli_query($conn, $sql);
          $data = mysqli_fetch_assoc($result);
          ?>

          <div class="small-box bg-info">
            <div class="inner">
              <h3><?php echo $data['total']; ?></h3>

              <p>Susu Account</p>
            </div>
            <div class="icon">
              <i class="fas fa-money-bill"></i>
            </div>
            <a href="susu.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
          <!-- small box -->
          <?php
          $sql = "SELECT COUNT(*) AS total
          FROM (
              SELECT 
                  a.transaction_number, 
                  b.first_name AS customer, 
                  c.account_type AS account_type, 
                  d.account_type_name AS account_type_name, 
                  a.amount, 
                  a.date_of_transaction
              FROM transactions a
              JOIN customers b ON a.customers = b.customer_number
              JOIN accounts c ON a.customers = c.customer
              JOIN account_type d ON c.account_type = d.account_type_number
              WHERE d.account_type_name = 'Savings Account'
          ) AS subquery";
          $result = mysqli_query($conn, $sql);
          $data = mysqli_fetch_assoc($result);
          ?>

          <div class="small-box bg-danger">
            <div class="inner">
              <h3><?php echo $data['total']; ?></h3>

              <p>Savings Account</p>
            </div>
            <div class="icon">
              <i class="fas fa-money-bill"></i>
            </div>
            <a href="savings.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->

        <div class="col-lg-3 col-6">
          <!-- small box -->
          <?php
          $sql = "SELECT COUNT(*) AS total
          FROM (
              SELECT 
                  a.transaction_number, 
                  b.first_name AS customer, 
                  c.account_type AS account_type, 
                  d.account_type_name AS account_type_name, 
                  a.amount, 
                  a.date_of_transaction
              FROM transactions a
              JOIN customers b ON a.customers = b.customer_number
              JOIN accounts c ON a.customers = c.customer
              JOIN account_type d ON c.account_type = d.account_type_number
              WHERE d.account_type_name = 'Current Account'
          ) AS subquery";
          $result = mysqli_query($conn, $sql);
          $data = mysqli_fetch_assoc($result);
          ?>

          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?php echo $data['total']; ?></h3>

              <p>Current Account</p>
            </div>
            <div class="icon">
              <i class="fas fa-money-bill"></i>
            </div>
            <a href="current.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
          <!-- small box -->
          <?php
          $sql = "SELECT count(*) AS total_customer FROM loans";
          $result = mysqli_query($conn, $sql);
          $data = mysqli_fetch_assoc($result);
          ?>
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?php echo $data['total_customer']; ?></h3>

              <p>Loan Request</p>
            </div>
            <div class="icon">
              <i class="fas fa-users"></i>
            </div>
            <a href="loanView.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
      </div>
    </div>
  </section>
</body>
<?php require_once 'include/footer.php'; ?>