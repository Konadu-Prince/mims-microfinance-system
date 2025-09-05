<?php
session_start();
require_once 'database/db_connection.php';
require_once 'include/head.php';

// retrieve data from database
$retrieve = mysqli_query(
    $conn,
    "SELECT 
    a.account_number, b.first_name as customer, c.account_type_name as account_type, a.open_date, d.account_status_name as account_status FROM accounts a
     JOIN customers b on a.customer = b.customer_number
     JOIN account_type c on a.account_type = c.account_type_number
     JOIN account_status d on a.account_status = d.account_status_number
      WHERE d.account_status_name = 'Active' ORDER BY open_date DESC"
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
                <a href="openaccount.php" class="active"><i class="fas fa-user-plus"></i> Open customer Accont</a>
                <a href="pdfcustomeraccount.php" target="_blank"><i class="fas fa-print"></i> Print all customer</a>
            </div>

            <table id="customers" class="mt-3">
                <thead class=" bg-success table-bordered">
                    <tr>
                        <th>SN</th>
                        <th>Account Number</th>
                        <th>Customer</th>
                        <th>Account type</th>
                        <th>Open Date</th>
                        <th>Account Status</th>
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
                            <td><?php echo $result["account_number"]; ?></td>
                            <td><?php echo $result["customer"]; ?></td>
                            <td><?php echo $result["account_type"]; ?></td>
                            <td><?php echo $result["open_date"]; ?></td>
                            <td><?php echo $result["account_status"]; ?></td>

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