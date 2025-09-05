<?php
session_start();
require_once 'include/head.php';
require_once 'database/db_connection.php';
$success_msg = '';
?>

<body>
    <?php require_once 'include/navbar.php'; ?>
    <?php require_once 'include/sidebar.php'; ?>

    <body>
        <div class="alert alert-success alert-dismissible fade show " role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                <span class="sr-only">Close</span>
            </button>
            <strong><?php echo isset($_SESSION['success_msg']) ? $_SESSION['success_msg'] : ''; ?></strong>
        </div>
        <div class="topnav" id="myTopnav">
            <a href="addcustomer.php" class="active"><i class="fas fa-table"></i> Manage customer</a>

            <a href="pdfLoan.php" target="_blank"><i class="fas fa-print"></i> Print all loan Request</a>
        </div>

        <form action="app/addLoans.php" method="post" autocomplete="off">
            <div class="row">
                <?php
                $costant = rand(0, 100);
                $customer_number = rand(8802, 10708809);
                ?>
                <div class="col-md-12 col-sm-4">
                    <div class="form-group">
                        <label for="">Loan number</label>
                        <div class="input-group">
                            <input type="number" name="loan_number" class="form-control" placeholder="Loan number" value="<?php echo $customer_number . $costant; ?>" readonly>
                            <small class="input-group-text"><i class="fas fa-user-cog"></i></small>
                        </div>
                    </div>
                </div>


                <div class="col-md-12 col-sm-6">
                    <div class="form-group">
                        <label for="customer info">Customer Information</label>
                        <select class="custom-select" name="customer_number" id="">
                            <option selected>Select one</option>
                            <?php
                            $result = mysqli_query($conn, "SELECT customer_number, first_name FROM customers");
                            ?>
                            <?php
                            while ($row = mysqli_fetch_assoc($result)) {
                            ?>

                                <option value="<?php echo $row['customer_number']; ?>">
                                    <?php echo $row['first_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-12 col-sm-4">
                    <div class="form-group">
                        <label for="">Amount</label>
                        <input type="number" name="amount" id="" class="form-control" placeholder="Amount">
                    </div>
                </div>

            </div>
            <div class="col-md-12 col-sm-4">
                <div class="form-group">
                    <label for="">Date of Transaction</label>
                    <input type="date" name="date_of_transaction" id="" class="form-control" placeholder="Date of birth">
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" name="addLoans">Record Loan</button>
            </div>

            </div>
        </form>
        </main>
    </body>