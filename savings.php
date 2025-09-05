<?php
session_start();
require_once 'database/db_connection.php';
require_once 'include/head.php';

// retrieve data from database
$retrieve = mysqli_query(
    $conn,
    "SELECT 
   a.transaction_number, b.first_name as customer, c.account_type as accout_type, d.account_type_name as account_type_name, a.amount, a.date_of_transaction
   FROM transactions a
    JOIN customers b on a.customers = b.customer_number
    JOIN accounts c on a.customers = c.customer
    JOIN account_type d on c.account_type = d.account_type_number
    WHERE d.account_type_name = 'Savings Account'
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
                <a href="AddTransaction.php" class="active"><i class="fas fa-user-plus"></i> Record A Transaction</a>
                <a href="pdfTransaction.php" target="_blank"><i class="fas fa-print"></i> Print all Transaction</a>
            </div>

            <table id="customers" class="mt-3">
                <thead class=" bg-success table-bordered">
                    <tr>
                        <th>SN</th>
                        <th>Transaction Number</th>
                        <th>Customer</th>
                        <th>Account Type</th>
                        <th>Amount</th>
                        <th>Transaction date</th>
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
                            <td><?php echo $result["transaction_number"]; ?></td>
                            <td><?php echo $result["customer"]; ?></td>
                            <td><?php echo $result["account_type_name"]; ?></td>
                            <td>Ghc <?php echo $result["amount"]; ?></td>
                            <td><?php echo $result["date_of_transaction"]; ?></td>

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