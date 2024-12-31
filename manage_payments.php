<?php
require 'authentication_check.php';
require_admin_access();
require 'db_connect.php';
require 'common_functions.php';

$successMessage = $errorMessage = "";

// Fetch customers and their associated data
$sqlCustomers = "SELECT customers.id, users.fname AS customer_fname, users.lname AS customer_lname 
                 FROM customers 
                 JOIN users ON customers.user_id = users.id";
$resultCustomers = mysqli_query($conn, $sqlCustomers);

// Fetch bookings
$sqlTattoos = "SELECT tattoos.*
               FROM tattoos";
$resultTattoos = mysqli_query($conn, $sqlTattoos);

// select all bookings that are not pending and don't have a payment yet
$sqlFetchBookings = "SELECT bookings.id, 
                     customers.id AS customer_id,
                     users.fname AS customer_fname,
                     users.lname AS customer_lname,
                     payments.total_price
                     FROM bookings
                     LEFT JOIN customers ON bookings.customer_id = customers.id
                     LEFT JOIN users AS users ON customers.user_id = users.id
                     LEFT JOIN payments ON bookings.id = payments.booking_id
                     WHERE bookings.status <> 'pending' AND payments.total_price IS NULL";

$sqlFetchBookings .= " ORDER BY bookings.created_at DESC;";
$resultBookings = mysqli_query($conn, $sqlFetchBookings);

// Fetch payments
$sqlPayments = "SELECT payments.id, payments.total_price, payments.status 
                FROM payments WHERE status = 'pending'";
$resultPayments = mysqli_query($conn, $sqlPayments);

// Create Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_payment'])) {
        $bookingId = $_POST['booking_id'];
        $totalPrice = $_POST['total_price'];

        $query = "SELECT customer_id FROM bookings WHERE id = '$bookingId'";
        $result = mysqli_query($conn, $query);

        if ($row = mysqli_fetch_assoc($result)) {
            $customerId = $row['customer_id'];
            if ($totalPrice > 10000) {
                $errorMessage = "Tattoo can't cost more than 10000";
            } else if ($totalPrice < 10) {
                $errorMessage = "Tattoo can't cost less than 10";
            } else {
                $sqlCreatePayment = "INSERT INTO payments (total_price, status, booking_id, customer_id) 
                                     VALUES ('$totalPrice', 'pending', '$bookingId', '$customerId')";

                if (mysqli_query($conn, $sqlCreatePayment)) {
                    $successMessage = "Payment added successfully";
                } else {
                    $errorMessage = "Error: " . mysqli_error($conn);
                }
            }
        }
    }


    $totalRemaining = 0;

    // Add Sub-Payment
    if (isset($_POST['add_sub_payment'])) {
        $paymentId = test_input($_POST['payment_id']);
        $amount = test_input($_POST['amount']);
        $method = test_input($_POST['method']);
        $paidAt = test_input(date("Y-m-d H:i:s"));

        if (is_numeric($amount) && $amount > 0) {
            // Update Payment Status
            $sqlSumSubPayments = "SELECT SUM(amount) AS total_paid FROM sub_payments WHERE payment_id = '$paymentId'";
            $result = mysqli_query($conn, $sqlSumSubPayments);
            $row = mysqli_fetch_assoc($result);
            $totalPaid = $row['total_paid'];

            $sqlPaymentTotal = "SELECT total_price FROM payments WHERE id = '$paymentId'";
            $result = mysqli_query($conn, $sqlPaymentTotal);
            $row = mysqli_fetch_assoc($result);
            $totalPrice = $row['total_price'];

            $totalRemaining = ($totalPrice - $totalPaid);
            if ($amount <= $totalRemaining) {
                // Insert sub-payment
                $sqlAddSubPayment = "INSERT INTO sub_payments (amount, method, paid_at, payment_id) 
                                VALUES ('$amount', '$method', '$paidAt', '$paymentId')";
                mysqli_query($conn, $sqlAddSubPayment);
                if ($totalRemaining == $amount) {
                    $updatePaymentStatus = "UPDATE payments SET status = 'paid' WHERE id = '$paymentId'";
                    mysqli_query($conn, $updatePaymentStatus);
                }
            } else {
                $errorMessage = "Can't pay more than required.";
            }
        } else {
            $errorMessage = "Pay positive numbers only";
        }
    }
}

// Fetch Payments
$sqlPaymentsView = "SELECT
    payments.id AS payment_id,
    payments.total_price,
    payments.status,
    customers.id AS customer_id,
    users.fname AS customer_fname,
    users.lname AS customer_lname,
    users.email AS customer_email,
    bookings.id AS booking_id,
    sub_payments.amount AS sub_payment_amount,
    sub_payments.method AS sub_payment_method,
    sub_payments.paid_at AS sub_payment_paid_at,
    (payments.total_price - COALESCE((
        SELECT SUM(sub_payments.amount)
        FROM sub_payments
        WHERE sub_payments.payment_id = payments.id
    ), 0)) AS price_remaining
FROM payments
JOIN customers ON payments.customer_id = customers.id
JOIN users ON customers.user_id = users.id
JOIN bookings ON payments.booking_id = bookings.id
LEFT JOIN sub_payments ON sub_payments.payment_id = payments.id
ORDER BY payments.id, sub_payments.id;";
$resultPaymentsView = mysqli_query($conn, $sqlPaymentsView);

$payments = [];
while ($row = mysqli_fetch_assoc($resultPaymentsView)) {
    $paymentId = $row['payment_id'];
    if (!isset($payments[$paymentId])) {
        $payments[$paymentId] = [
            'booking_id' => $row['booking_id'],
            'customer_name' => $row['customer_fname'] . ' ' . $row['customer_lname'],
            'customer_email' => $row['customer_email'],
            'total_price' => $row['total_price'],
            'status' => ucfirst($row['status']),
            'price_remaining' => $row['price_remaining'],
            'sub_payments' => [],
        ];
    }
    if (!empty($row['sub_payment_amount'])) {
        $payments[$paymentId]['sub_payments'][] = [
            'amount' => $row['sub_payment_amount'],
            'method' => $row['sub_payment_method'],
            'paid_at' => $row['sub_payment_paid_at'],
        ];
    }
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVibe | Manage Payments</title>
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="stylesheet" href="styles/included.css">
    <script defer src="scripts/included.js"></script>
    <script>
        window.onload = function() {
            const errorMessage = "<?php echo addslashes($errorMessage); ?>";
            const successMessage = "<?php echo addslashes($successMessage); ?>";
            if (errorMessage) alert(errorMessage);
            if (successMessage) alert(successMessage);
        };
    </script>
</head>

<body>
    <div class="container">
        <header>
            <?php include 'navbar.php'; ?>
        </header>
        <h2 class="heading">Manage Payments</h2>
        <!-- Create Payment -->
        <section class="create-payment">
            <h3>Create Payment</h3>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <label for="booking_id">Booking:</label>
                <select name="booking_id" id="booking_id" required>
                    <option value="">--Select tattoo--</option>
                    <?php while ($row = mysqli_fetch_assoc($resultBookings)) : ?>
                        <option value="<?php echo $row['id']; ?>">
                            <?php echo htmlspecialchars($row['id'] . ' ' . $row['customer_fname'] . ' ' . $row['customer_lname']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <label for="total_price">Total Price:</label>
                <input type="number" step="0.01" min="1" max="10000" name="total_price" id="total_price" placeholder="Total Price" required>
                <button type="submit" name="add_payment">Create Payment</button>
            </form>
        </section>

        <!-- Add Sub-Payment -->
        <section class="add-sub-payment">
            <h3>Add Sub-Payment</h3>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <label for="payment_id">Payment:</label>
                <select name="payment_id" id="payment_id" required>
                    <?php while ($row = mysqli_fetch_assoc($resultPayments)) : ?>
                        <option value="<?php echo $row['id']; ?>">
                            Payment #<?php echo $row['id']; ?> - $<?php echo number_format($row['total_price'], 2); ?> (<?php echo ucfirst($row['status']); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
                <label for="amount">Amount:</label>
                <input type="number" step="0.01" min="1" name="amount" id="amount" placeholder="Amount" required>
                <label for="method">Method:</label>
                <select name="method" id="method" required>
                    <option value="cash">Cash</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="online">Online</option>
                </select>
                <button type="submit" name="add_sub_payment">Add Sub-Payment</button>
            </form>
        </section>

        <!-- Payment List -->
        <section class="payment-history">
            <h3>Payment History</h3>
            <table border="1" class="table">
                <thead>
                    <tr>
                        <th>Booking Number</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Total Price</th>
                        <th>Remaining</th>
                        <th>Sub-Payments</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td data-label="Booking Number"><?php echo htmlspecialchars($payment['booking_id']); ?></td>
                            <td data-label="Customer"><?php echo htmlspecialchars($payment['customer_name']); ?></td>
                            <td data-label="Email"><?php echo htmlspecialchars($payment['customer_email']); ?></td>
                            <td data-label="Status"><?php echo $payment['status']; ?></td>
                            <td data-label="Total Price">$<?php echo number_format($payment['total_price'], 2); ?></td>
                            <td data-label="Remaining">$<?php echo number_format($payment['price_remaining'], 2); ?></td>
                            <td data-label="Sub-Payments">
                                <ul>
                                    <?php foreach ($payment['sub_payments'] as $subPayment): ?>
                                        <li>
                                            $<?php echo number_format($subPayment['amount'], 2); ?>
                                            (<?php echo htmlspecialchars($subPayment['method']); ?>,
                                            <?php echo htmlspecialchars($subPayment['paid_at']); ?>)
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <a class="back-link" href="dashboard_admin.php">Back to Dashboard</a>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>