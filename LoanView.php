<?php
session_start();
require_once 'database/db_connection.php';
require_once 'include/head.php';

// retrieve data from database
$retrieve = mysqli_query(
    $conn,
    "SELECT 
   a.loans_number, b.first_name as customer, a.amount, a.date_of_transaction
   FROM loans a
    JOIN customers b on a.customer = b.customer_number
    ORDER BY date_of_transaction DESC"
);

?>
<?php require_once 'include/head.php'; ?>

<body>
    <?php
    require_once 'include/navbar.php';

    if (mysqli_num_rows($retrieve) > 0) {

    ?>
        <div class="ms-4 mr-4 ">
            <div class="topnav mt-5" id="myTopnav">
                <a href="loans.php" class="active"><i class="fas fa-user-plus"></i> Record A Loan</a>
                <a href="pdfLoan.php" target="_blank"><i class="fas fa-print"></i> Print all Loan Request</a>
            </div>

            <table id="customers" class="mt-3">
                <thead class=" bg-success table-bordered">
                    <tr>
                        <th>SN</th>
                        <th>Loan Number</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Transaction date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <?php
                $num = 1;
                $i = 0;
                while ($result = mysqli_fetch_assoc($retrieve)) {
                ?>

                    <tbody class="table-bordered">
                        <tr>
                            <td><?php echo $num++; ?></td>
                            <td><?php echo $result["loans_number"]; ?></td>
                            <td><?php echo $result["customer"]; ?></td>
                            <td>Ghc <?php echo $result["amount"]; ?></td>
                            <td><?php echo $result["date_of_transaction"]; ?></td>
                            <td colspan="">
                                <a href="updateLoan.php?loans_number=<?php echo $result["loans_number"]; ?>" type="submit" class="btn btn-primary"><i class="fas fa-pencil-alt"></i> Edit</a>
                                <a href="app/deletecustomerLoans.php?loans_number=<?php echo $result["loans_number"]; ?>" type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</a>

                            </td>
                        </tr>
                    </tbody>
                <?php
                    $i++;
                }
                ?>
            </table>
        </div>
        </div>
    <?php
    } else {
        echo 'No result found';
    } ?>

</body>
<?php require_once 'include/footer.php'; ?>