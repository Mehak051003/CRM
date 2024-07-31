<?php
session_start();
include '../includes/db_connect.php';  

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}


$policiesQuery = "SELECT * FROM policies";
$policiesResult = $conn->query($policiesQuery);

$conn->close();
?>

<?php include 'header.php'; ?>

<style>
    .policy-list {
        width: 80%;
        margin: auto;
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .policy-item {
        margin: 10px 0;
        padding: 10px;
        border-bottom: 1px solid #bdc3c7;
    }

    .policy-item a {
        text-decoration: none;
        color: #3498db;
    }
    .main-content{
        margin-left: 120px;
            margin-top: 60px;
            padding: 20px;
    }
</style>

<div class="policy-list">
    <h2>Policies</h2>
    <?php while ($row = $policiesResult->fetch_assoc()): ?>
        <div class="policy-item">
            <p><strong><?php echo $row['policy_type']; ?></strong></p>
            <a href="<?php echo $row['file_path']; ?>" target="_blank">View Policy</a>
        </div>
    <?php endwhile; ?>
</div>

<?php include 'footer.php'; ?>
