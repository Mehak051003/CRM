<!-- header.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #ecf0f1;
        }

        .header {
            width: 100%;
            background-color: #2c3e50;
            color: white;
            padding: 10px 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 40px;
        }

        .header .logo {
            font-size: 1.5em;
        }

        .header .profile {
            position: relative;
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .header .profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 130px;
        }

        .header .profile .dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background-color: #2c3e50;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            overflow: hidden;
            white-space: nowrap;
            margin-right:50px;
        }

        .header .profile .dropdown a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
        }

        .header .profile .dropdown a:hover {
            background-color: #34495e;
        }

        .header .profile:hover .dropdown {
            display: block;
        }

        .sidebar {
            width: 200px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            position: fixed;
            top: 60px;
            left: 0;
            height: calc(100% - 60px);
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
            display: block;
        }

        .sidebar a:hover {
            background-color: #34495e;
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
    </script>
</head>
<body>
<div class="header">
        <div class="logo">TL Panel</div>
        <div class="profile">
            <img src="../path/to/profile/image.jpg" alt="Profile Picture">
            <div class="dropdown">
                <a href="myaccount.php">My Account</a>
                <a href="editprofile.php">Edit Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a class="reports-toggle" href="#">Reports</a>
        <div class="sub-menu">
            <a href="morning.php">Morning Status Report</a>
            <a href="evening.php">Evening Status Report</a>
            <a href="permission.php">Reports Permission Requests</a>
        </div>
        <a href="editproject.php">Projects</a>
        <a href="leave.php">Leave Requests</a>
        <a href="policy.php">Policies</a>
        <a href="holidays.php">Holidays</a>
    </div>
    <div class="main-content">
