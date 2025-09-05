<?php
session_start();
require_once 'database/db_connection.php';
require_once 'include/head.php';
$update_success_msg = '';
$result = mysqli_query($conn, "SELECT * FROM transactions
WHERE transaction_number = '" . $_GET['transaction_number'] . "'");
$row = mysqli_fetch_assoc($result);
$rdata = [];
foreach ($row as $k => $v) {
    $rdata[$k] = $v;
}
?>

<body>
    <?php
    require_once 'include/navbar.php';
    require_once 'include/sidebar.php';
    ?>



    <div class="card ">
        <div class="card-header bg-primary text-bold">
            CUSTOMER TRANSACTION FORM
        </div>
        <div class="card-body">
            <div class="card-text">
                <div class="alert alert-success alert-dismissible fade show " role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">Close</span>
                    </button>
                    <strong><?php echo isset($_SESSION['success_msg']) ? $_SESSION['success_msg'] : ''; ?></strong>
                </div>
                <form action="app/updateTransactionHandler.php" method="post">
                    <div class="row">
                        <?php

                        $account_number =  $row['transaction_number'];
                        ?>
                        <div class="col-md-12 col-sm-6">
                            <div class="form-group">
                                <label for="transaction number">Transaction number</label>
                                <input type="text" name="transaction_number" id="" class="form-control" placeholder="" aria-describedby="helpId" value="<?php echo $account_number; ?>" readonly>
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

                                        <option disabled><?php echo $row['first_name']; ?></option>
                                        <option value="<?php echo $row['customer_number']; ?>" <?php echo $row['customer_number'] == $rdata['customers'] ? "selected" : ''; ?>>
                                            <?php echo $row['customer_number']; ?></option>
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
                        <button type="submit" class="btn btn-primary" name="addtransaction">Update Transaction</button>
                    </div>

            </div>
            </form>
        </div>
    </div>
    </div>
</body>
<?php require_once 'include/footer.php'; ?>