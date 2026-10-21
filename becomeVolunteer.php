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
            background:#e9e9e9;
        }

        header{
            background-color:#00154d;
            color:white;
            padding:15px 40px;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }

        .logo{
            display:flex;
            align-items:center;
        }

        .logo img{
            width:45px;
            margin-right:10px;
        }

        .logo h1{
            font-size:18px;
            color:white;
            margin:0;
        }

        .menu{
            display: felx;
            align-items: center;
            gap: 25px;
        }

        .menu a{
            color:white;
            text-decoration:none;
            font-size: 14px;
            font-weight:bold;
        }

        .menu-icon{
            color: white;
            font-size: 24px;
            margin-left: 20px;
            cursor: pointer;
        }

        .container{
            max-width:1100px;
            margin:20px auto;
            padding:20px 60px;
        }

        .page-title{
            text-align:center;
            font-family: "Times New Roman", serif;
            font-size: 50px;
            margin-bottom: 50px;
        }

        .form-section{
            display:flex;
            justify-content:space-between;
            align-items: flex-start;
        }

        .left-column,
        .right-column{
            width:45%;
        }

        .left-column h3,
        .right-column h3{
            font-size: 22px;
            margin-bottom:25px;
        }

        .checkboz-list label{
            display: flex;
            align-items: center;
            margin-bottom: 18px;
            font-size:18px;
        }

        input[type="checkbox"]{
            width: 22px;
            height: 22px;
            margin-right: 15px;
        }

        .days-gird{
            display: grid;
            grid-template-columns: repeat(3,auto);
            gap: 15px 30px;
            margin-bottom: 30px;
        }

        .days-grid label{
            display:flex;
            align-items:center;
            font-size:18px;
        }

        .time-section{
            margin-top:20px;
            margin-bottom:30px;
        }

        .time-section label{
            font-size:18px;
            font-weight:bold;
            margin-right:15px;
        }

        .time-section input{
            width:180px;
            padding:8px;
            font-size:16px;
        }

        .support-section{
            margin-top:20px;
        }

        .support-section p{
            font-size:18px;
            font-weight:bold;
        }    

        .support-section label{
            margin-right:30px;
            font-size:18px;
        }

        .btn{
            display:block;
            margin:50px auto;
            background:#00154d;
            color:white;
            border:none;
            padding:15px 35px;
            border-radius:30px;
            font-size:16px;
            cursor:pointer;
            font-size:16px
        }

        .btn:hover{
            background:#002f99;
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

<h1 class="page-title">Become a Volunteer</h1>

<form action="saveVolunteer.php" method="POST">

<div class="form-section">

    <div class="left-column">

        <h3>Volunteer Categories & Skills:</h3>

        <div class="checkbox-list">
            <label><input type="checkbox" name="skills[]" value="Academic"> Academic</label>

            <label><input type="checkbox" name="skills[]" value="Mathematics"> Mathematics</label>

            <label><input type="checkbox" name="skills[]" value="Programming"> Programming</label>

            <label><input type="checkbox" name="skills[]" value="Statistics"> Statistics</label>

            <label><input type="checkbox" name="skills[]" value="Technology"> Technology</label>

            <label><input type="checkbox" name="skills[]" value="New Student"> New Student</label>
        </div>

    </div>

    <div class="right-column">

        <h3>Availability:</h3>

        <p><strong>Day:</strong></p>

        <div class="days-grid">
            <label><input type="checkbox" name="days[]" value="Mon"> Mon</label>
            <label><input type="checkbox" name="days[]" value="Tue"> Tue</label>
            <label><input type="checkbox" name="days[]" value="Wed"> Wed</label>
            <label><input type="checkbox" name="days[]" value="Thu"> Thu</label>
            <label><input type="checkbox" name="days[]" value="Fri"> Fri</label>
        </div>

        <div class="time-section">
            <label>Time:</label>
            <input type="time" name="available_time">
        </div>

        <div class="support-section">
            <p>Support Mode:</p>

            <label>
                <input type="radio" name="support_mode" value="Physical">
                Physical
            </label>

            <label>
                <input type="radio" name="support_mode" value="Online" checked>
                Online
            </label>
        </div>

    </div>

</div>

<button type="submit" class="btn">
    Register Volunteer
</button>

</form>

</div>

</body>
</html>
