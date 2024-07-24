<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $serial_number = $_POST['serial_number'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $country = $_POST['country'];

    
    $stmt = $conn->prepare("INSERT INTO clients (serial_number, firstname, lastname, email, country) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $serial_number, $firstname, $lastname, $email, $country);

    if ($stmt->execute()) {
        $message = "Client added successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

$clientList = [];
$clientQuery = "SELECT * FROM clients";
$clientResult = $conn->query($clientQuery);

if ($clientResult) {
    while ($row = $clientResult->fetch_assoc()) {
        $clientList[] = $row;
    }
} else {
    $message = "Error fetching clients: " . $conn->error;
}

$conn->close(); 

?>

<?php include 'header.php'; ?>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f2f2f2;
    }

    .container {
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .container h2 {
        margin-bottom: 20px;
        text-align: center;
    }

    .form-container {
        width: 100%;
        max-width: 600px;
        margin: auto;
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .form-container label {
        display: block;
        margin-bottom: 8px;
    }

    .form-container input[type="text"],
    .form-container input[type="email"] {
        width: calc(100% - 20px);
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }

    .form-container button[type="submit"] {
        width: 100%;
        padding: 12px;
        background-color: #3498db;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.3s ease;
    }

    .form-container button[type="submit"]:hover {
        background-color: #2980b9;
    }

    .client-list {
        margin-top: 20px;
    }

    .client-list h2 {
        text-align: center;
    }

    .client-list table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .client-list table th,
    .client-list table td {
        padding: 8px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .client-list table th {
        background-color: #f2f2f2;
    }

    .message {
        text-align: center;
        margin: 10px 0;
        padding: 10px;
        background-color: #e74c3c;
        color: #fff;
        border-radius: 4px;
    }
</style>

<div class="container">
    <h2>Add Client</h2>
    <div class="form-container">
        <?php if (isset($message)) echo "<p class='message'>$message</p>"; ?>
        <form method="post" action="addclient.php">
            <label for="serial_number">Reference Number:</label>
            <input type="text" id="serial_number" name="serial_number" required>

            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" required>

            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="country">Country:</label>
            <input type="text" id="country" name="country" required>

            <button type="submit">Add Client</button>
        </form>
    </div>

    <div class="client-list">
        <h2>All Clients</h2>
        <table>
            <thead>
                <tr>
                    <th>Reference Number</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Country</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientList as $client): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client['serial_number']); ?></td>
                        <td><?php echo htmlspecialchars($client['firstname'] . ' ' . $client['lastname']); ?></td>
                        <td><?php echo htmlspecialchars($client['email']); ?></td>
                        <td><?php echo htmlspecialchars($client['country']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
