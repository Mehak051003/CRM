<?php
session_start();
include '../includes/db_connect.php';
include '../timeout_check.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = '';

// Handle add holiday
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

    if (isset($_POST['update_holiday'])) {
        $update_id = $_POST['update_id'];
        $holiday_name = $_POST['holiday_name'];
        $holiday_date = $_POST['holiday_date'];
        $day_of_week = date('l', strtotime($holiday_date));
        $message_text = $_POST['message'];

        $stmt = $conn->prepare("UPDATE holidays SET holiday_name = ?, holiday_date = ?, day_of_week = ?, message = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $holiday_name, $holiday_date, $day_of_week, $message_text, $update_id);

        if ($stmt->execute()) {
            $message = "Holiday updated successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$query = "SELECT id, holiday_name, holiday_date, day_of_week, message FROM holidays ORDER BY holiday_date";
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

        .edit-button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        .edit-button:hover {
            background-color: #2980b9;
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
                        <td><?php echo htmlspecialchars($holiday['holiday_name']); ?></td>
                        <td><?php echo htmlspecialchars($holiday['holiday_date']); ?></td>
                        <td><?php echo htmlspecialchars($holiday['day_of_week']); ?></td>
                        <td><?php echo htmlspecialchars($holiday['message']); ?></td>
                        <td>
                            <button class="edit-button" onclick="openEditForm(<?php echo $holiday['id']; ?>, '<?php echo htmlspecialchars($holiday['holiday_name']); ?>', '<?php echo htmlspecialchars($holiday['holiday_date']); ?>', '<?php echo htmlspecialchars($holiday['message']); ?>')">Edit</button>
                            <form method="post" action="holidays.php" style="display:inline;" onsubmit="return confirmDelete();">
                                <input type="hidden" name="delete_id" value="<?php echo $holiday['id']; ?>">
                                <button type="submit" class="delete-button">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Holiday Modal -->
    <div id="editModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:lightblue; padding:40px; border:1px solid #ddd; box-shadow:0 0 15px rgba(0,0,0,0.2); z-index:1000; width: 80%; max-width: 800px; height: auto; min-height: 300px;">
        <h2>Edit Holiday</h2>
        <form action="holidays.php" method="post">
            <input type="hidden" name="update_id" id="update_id">
            <label for="holiday_name">Holiday Name:</label>
            <input type="text" name="holiday_name" id="edit_holiday_name" required>
            <label for="holiday_date">Date:</label>
            <input type="date" name="holiday_date" id="edit_holiday_date" required>
            <label for="message">Message:</label>
            <textarea name="message" id="edit_message" rows="4"></textarea>
            <input type="submit" name="update_holiday" value="Update Holiday">
            <button type="button" onclick="closeEditForm()">Cancel</button>
        </form>
    </div>

    <script>
        function openEditForm(id, name, date, message) {
            document.getElementById('update_id').value = id;
            document.getElementById('edit_holiday_name').value = name;
            document.getElementById('edit_holiday_date').value = date;
            document.getElementById('edit_message').value = message;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditForm() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</body>
</html>

<?php include 'footer.php'; ?>
