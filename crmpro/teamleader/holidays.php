<?php
session_start();
include '../includes/db_connect.php';  
include '../timeout_check.php';

if (!isset($_SESSION['user']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'User' && $_SESSION['role'] != 'Team Leader')) {
    header('Location: ../login.php');
    exit();
}

$query = "SELECT holiday_name, holiday_date, day_of_week, message FROM holidays ORDER BY holiday_date DESC";
$result = $conn->query($query);
$holidays = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $holidays[] = $row;
    }
}

$conn->close();
?>


<?php include 'header.php'; ?>

<style>
    .container {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        width: 80%;
        margin: auto;
    }

    h2 {
        text-align: center;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }
    .main-content{
        margin-left: 120px;
            margin-top: 60px;
            padding: 20px;
    }
</style>

<div class="container">
    <h2>Holiday List</h2>
    <table>
        <thead>
            <tr>
                <th>Serial No.</th>
                <th>Holiday Name</th>
                <th>Date</th>
                <th>Day of Week</th>
                <th>Message</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $serialNo = 1;
            foreach ($holidays as $holiday) : ?>
                <tr>
                    <td><?php echo $serialNo++; ?></td>
                    <td><?php echo htmlspecialchars($holiday['holiday_name']); ?></td>
                    <td><?php echo htmlspecialchars($holiday['holiday_date']); ?></td>
                    <td><?php echo htmlspecialchars($holiday['day_of_week']); ?></td>
                    <td><?php echo htmlspecialchars($holiday['message']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


<?php include 'footer.php'; ?>

</body>
</html>
