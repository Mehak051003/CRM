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
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.projects-toggle').click(function(e) {
                e.preventDefault();
                $('.sub-menu').slideToggle();
            });
        });
    </script>
</head>
<body>
<div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="adduser.php">Add User</a>
        <a href="addclient.php">Clients</a>
        <a href="reports.php">Reports</a>
        <a class="projects-toggle" href="#">Projects</a>
        <div class="sub-menu">
            <a href="project.php">Add Project</a>
            <a href="projectview.php">View Projects</a>
        </div>
        <a href="policy.php">Policies</a>
        <a href="holidays.php">Holidays</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="main-content">