<?php
session_start();
include("db_connect.php");

if(!isset($_SESSION['student_id']))
{
    header("Location: login.html");
    exit();
}

$student_id = $_SESSION['student_id'];

$sql = "SELECT * FROM student
        WHERE student_id='$student_id'";

$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0)
{
    $row = mysqli_fetch_assoc($result);
}
else
{
    echo "Student record not found.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>

    <style>

        body{
            margin:0;
            font-family:Arial, sans-serif;
            background-color:#f3f3f3;
        }

        /* Header */
        header{
            background-color:#00154d;
            height:100px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:0 40px;
        }

        /* Logo */
        .logo{
            display:flex;
            align-items:center;
        }

        .logo img{
            width:70px;
            height:70px;
            margin-right:15px;
        }

        .logo h1{
            color:white;
            margin:0;
            font-size:28px;
        }

        /* Navigation */
        .menu{
            display:flex;
            gap:30px;
        }

        .menu a{
            color:white;
            text-decoration:none;
            font-weight:bold;
            font-size:18px;
        }

        .menu a:hover{
            text-decoration:underline;
        }

        /* Profile Card */
        .container{
            width:700px;
            margin:60px auto;
            background:white;
            padding:40px;
            border-radius:15px;
            box-shadow:0px 4px 15px rgba(0,0,0,0.15);
        }

        .container h1{
            text-align:center;
            color:#00154d;
            margin-bottom:40px;
        }

        table{
            width:100%;
            border-spacing:15px;
            font-size:28px;
        }

        .label{
            width:260px;
            font-weight:bold;
            color:#00154d;
        }

        /* Buttons */
        .buttons{
            margin-top:50px;
            display:flex;
            justify-content:center;
            gap:25px;
        }

        .btn{
            background-color:#00154d;
            color:white;
            text-decoration:none;
            padding:15px 30px;
            border-radius:30px;
            font-weight:bold;
        }

        .btn:hover{
            background-color:#003399;
        }

    </style>
</head>

<body>

<header>

    <div class="logo">
        <img src="GraduateIcon.png" alt="Graduation Icon">
        <h1>UniHELP</h1>
    </div>

    <nav class="menu" id="menu">
        <a href="studentPage.html">Home</a>
        <a href="">My Request</a>
        <a href="">Session History</a>
        <a href="">Feedback</a>
        <a href="studentProfile.php">Profile</a>
        <a href="login.php">Logout</a>
    </nav>

</header>

<div class="container">

    <h1>Student Profile</h1>

    <table>

        <tr>
            <td class="label">Student ID</td>
            <td>: <?php echo $row['student_id']; ?></td>
        </tr>

        <tr>
            <td class="label">Name</td>
            <td>: <?php echo $row['full_name']; ?></td>
        </tr>

        <tr>
            <td class="label">Contact Number</td>
            <td>: <?php echo $row['contactNo']; ?></td>
        </tr>

        <tr>
            <td class="label">Email</td>
            <td>: <?php echo $row['email']; ?></td>
        </tr>

        <tr>
            <td class="label">Volunteer Status</td>
            <td>: Not Registered</td>
        </tr>

    </table>

    <div class="buttons">

        <a href="becomeVolunteer.php" class="btn">
            Become a Volunteer
        </a>

        <a href="editProfile.php" class="btn">
            Edit Profile
        </a>

    </div>

</div>

</body>
</html>