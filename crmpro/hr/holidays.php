<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'hr') {
    header('Location: ../login.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_holiday'])) {
        $holiday_name = $_POST['holiday_name'];
        $holiday_date = $_POST['holiday_date'];
        $day_of_week = date('l', strtotime($holiday_date));
        $message_text = $_POST['message'];

        $stmt = $conn->prepare("INSERT INTO holidays (holiday_name, holiday_date, day_of_week, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $holiday_name, $holiday_date, $day_of_week, $message_text);

        if ($stmt->execute()) {
            $message = "Holiday added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    if (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];

        $stmt = $conn->prepare("DELETE FROM holidays WHERE id = ?");
        $stmt->bind_param("i", $delete_id);

        if ($stmt->execute()) {
            $message = "Holiday deleted successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$query = "SELECT id, holiday_name, holiday_date, day_of_week, message FROM holidays ORDER BY holiday_date ";
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Holidays</title>
    <style>
        .main-content {
            margin-left: 120px;
            margin-top: 60px;
            padding: 20px;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #ecf0f1;
        }

        .container {
            background-color: white;
            padding: 20px;
            margin: 20px auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 80%;
        }

        h2 {
            text-align: center;
        }

        form {
            margin-bottom: 20px;
        }

        input[type="text"],
        input[type="date"],
        textarea {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
        }

        input[type="submit"],
        button {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button[type="button"] {
            background-color: #e74c3c;
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

        .delete-button {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        .delete-button:hover {
            background-color: #c0392b;
        }
    </style>
    <script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this holiday?');
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Add Holiday</h2>
        <form action="holidays.php" method="post">
            <label for="holiday_name">Holiday Name:</label>
            <input type="text" name="holiday_name" required>
            <label for="holiday_date">Date:</label>
            <input type="date" name="holiday_date" required>
            <label for="message">Message:</label>
            <textarea name="message" rows="4"></textarea>
            <input type="submit" name="add_holiday" value="Add Holiday">
        </form>

        <?php if (!empty($message)) : ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>

        <h2>Holiday List</h2>
        <table>
            <thead>
                <tr>
                    <th>Holiday Name</th>
                    <th>Date</th>
                    <th>Day of Week</th>
                    <th>Message</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($holidays as $holiday) : ?>
                    <tr>
                        <td><?php echo $holiday['holiday_name']; ?></td>
                        <td><?php echo $holiday['holiday_date']; ?></td>
                        <td><?php echo $holiday['day_of_week']; ?></td>
                        <td><?php echo $holiday['message']; ?></td>
                        <td>
                            <form method="post" action="holidays.php" onsubmit="return confirmDelete();">
                                <input type="hidden" name="delete_id" value="<?php echo $holiday['id']; ?>">
                                <button type="submit" class="delete-button">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>
