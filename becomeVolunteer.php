<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become a Volunteer</title>

    <style>

        body{
            margin:0;
            font-family:Arial, sans-serif;
            background-color:#f3f3f3;
        }

        header{
            background-color:#00154d;
            color:white;
            padding:15px 30px;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }

        .logo{
            display:flex;
            align-items:center;
        }

        .logo img{
            width:40px;
            margin-right:10px;
        }

        .logo h1{
            font-size:28px;
            color:white;
            margin:0;
        }

        .menu a{
            color:white;
            text-decoration:none;
            margin-left:20px;
            font-weight:bold;
        }

        .container{
            width:80%;
            max-width:1000px;
            margin:40px auto;
            background:white;
            padding:40px;
            border-radius:10px;
        }

        h1{
            text-align:center;
            margin-bottom:40px;
        }

        .form-section{
            display:flex;
            justify-content:space-between;
            gap:80px;
        }

        .left-column,
        .right-column{
            width:45%;
        }

        .left-column h3,
        .right-column h3{
            margin-bottom:20px;
        }

        .left-column label,
        .right-column label{
            display:block;
            margin-bottom:15px;
            font-size:18px;
        }

        input[type="checkbox"]{
            width:18px;
            height:18px;
            margin-right:10px;
        }

        .btn{
            display:block;
            margin:40px auto 0;
            background:#00154d;
            color:white;
            border:none;
            padding:15px 30px;
            border-radius:30px;
            font-size:16px;
            cursor:pointer;
        }

        .btn:hover{
            background:#002d8f;
        }

    </style>
</head>

<body>
    <header>
        <!-- Logo -->
        <div class="logo">
            <img src="GraduateIcon.png" alt="Graduation Icon">
            <h1>UniHELP</h1>
        </div>
        <!-- Navigation -->
        <nav class="menu" id="menu">
            <a href="studentpage.html">Home</a>
            <a href="">My Request</a>
            <a href="">Session History</a>
            <a href="">Feedback</a>
            <a href="studentProfile.php">Profile</a>
            <a href="login.html">Logout</a>
        </nav>
    </header>

<div class="container">

    <h1>Become a Volunteer</h1>

    <form action="saveVolunteer.php" method="POST">

        <div class="form-section">

            <div class="left-column">

                <h3>Volunteer Categories & Skills</h3>

                <label>
                    <input type="checkbox" name="skills[]" value="Academic">
                    Academic
                </label>

                <label>
                    <input type="checkbox" name="skills[]" value="Mathematics">
                    Mathematics
                </label>

                <label>
                    <input type="checkbox" name="skills[]" value="Programming">
                    Programming
                </label>

                <label>
                    <input type="checkbox" name="skills[]" value="Statistics">
                    Statistics
                </label>

                <label>
                    <input type="checkbox" name="skills[]" value="Technology">
                    Technology
                </label>

                <label>
                    <input type="checkbox" name="skills[]" value="New Student">
                    New Student
                </label>

            </div>

            <div class="right-column">

                <h3>Availability</h3>

                <p><strong>Day:</strong></p>

                <label>
                    <input type="checkbox" name="days[]" value="Mon">
                    Mon
                </label>

                <label>
                    <input type="checkbox" name="days[]" value="Tue">
                    Tue
                </label>

                <label>
                    <input type="checkbox" name="days[]" value="Wed">
                    Wed
                </label>

                <label>
                    <input type="checkbox" name="days[]" value="Thu">
                    Thu
                </label>

                <label>
                    <input type="checkbox" name="days[]" value="Fri">
                    Fri
                </label>

            </div>

        </div>

        <button type="submit" class="btn">
            Register Volunteer
        </button>

    </form>

</div>

</body>
</html>