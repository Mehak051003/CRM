<!-- header.php -->
<?php
include '../includes/daily_reset.php'; // Include the daily reset script
?>
<!-- Rest of your header content -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            margin: 0;
            background-color: #ecf0f1;
        }

        .sidebar {
            width: 200px;
            background-color: #2c3e50;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px;
            height: 100vh;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
        }

        .sidebar a:hover {
            background-color: #34495e;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
        }

        .sub-menu {
            display: none;
            padding-left: 20px; 
            background-color: #34495e; 
        }

        .sub-menu a {
            display: block;
            color: white;
            padding: 10px;
            text-decoration: none;
        }

        .sub-menu a:hover {
            background-color: #2c3e50;
        }

        .menu {
            display: none;
            padding-left: 20px; 
            background-color: #34495e; 
        }

        .menu a {
            display: block;
            color: white;
            padding: 10px;
            text-decoration: none;
        }

        .menu a:hover {
            background-color: #2c3e50;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.reports-toggle').click(function(e) {
                e.preventDefault();
                $('.sub-menu').slideToggle();
            });
        });
        $(document).ready(function() {
            $('.leaves-toggle').click(function(e) {
                e.preventDefault();
                $('.menu').slideToggle();
            });
        });
    </script>
</head>
<body>
    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a class="reports-toggle" href="#">Reports</a>
        <div class="sub-menu">
            <a href="morning.php">Morning Status Report</a>
            <a href="evening.php">Evening Status Report</a>
        </div>
        <a class="leaves-toggle" href="#">Leaves</a>
        <div class="menu">
            <a href="sendleave.php">Send Leave Requests</a>
            <a href="myleave.php">My Leave Requests</a>
        </div>
        <a href="policy.php">Policies</a>
        <a href="holidays.php">Holidays</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="main-content">
