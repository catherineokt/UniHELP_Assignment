<?php
session_start();
include("db_connect.php");

if(!isset($_SESSION['student_id']))
{
    header("Location: login.html");
    exit();
}

$student_id = $_SESSION['student_id'];

$result = mysqli_query(
$conn,
"SELECT * FROM student
 WHERE student_id='$student_id'"
);

$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>

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
    font-size:26px;
    margin:0;
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

/* Form Card */
.container{
    width:600px;
    margin:60px auto;
    background:white;
    padding:40px;
    border-radius:15px;
    box-shadow:0px 4px 15px rgba(0,0,0,0.15);
}

.container h1{
    text-align:center;
    margin-bottom:35px;
    color:#00154d;
}

/* Form */
.form-group{
    margin-bottom:25px;
}

.form-group label{
    display:block;
    font-weight:bold;
    margin-bottom:10px;
}

.form-group input{
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:8px;
    font-size:16px;
    box-sizing:border-box;
}

/* Buttons */
.buttons{
    display:flex;
    justify-content:center;
    gap:20px;
    margin-top:35px;
}

.btn{
    background-color:#00154d;
    color:white;
    padding:12px 28px;
    border:none;
    border-radius:30px;
    text-decoration:none;
    font-size:16px;
    font-weight:bold;
    cursor:pointer;
}

.btn:hover{
    background-color:#003399;
}

</style>

</head>

<body class="bg-gradient-primary">
    <header>
        <!-- Logo -->
        <div class="logo">
            <img src="GraduateIcon.png" alt="Graduation Icon">
            <h1>UniHELP</h1>
        </div>

        <nav class="menu" id="menu">
            <a href="">Home</a>
            <a href="">My Request</a>
            <a href="">Session History</a>
            <a href="">Feedback</a>
            <a href="studentProfile.php">Profile</a>
            <a href="login.html">Logout</a>
        </nav>
    </header>


<div class="container">

    <h1>Edit Profile</h1>

    <form action="updateProfile.php" method="post">

        <div class="form-group">
            <label>Full Name</label>
            <input
                type="text"
                name="full_name"
                value="<?php echo $row['full_name']; ?>"
                required>
        </div>

        <div class="form-group">
            <label>Contact Number</label>
            <input
                type="text"
                name="contactNo"
                value="<?php echo $row['contactNo']; ?>"
                required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input
                type="email"
                name="email"
                value="<?php echo $row['email']; ?>"
                required>
        </div>

        <div class="buttons">

            <button type="submit" class="btn">
                Save Changes
            </button>

            <a href="studentProfile.php" class="btn">
                Cancel
            </a>

        </div>

    </form>

</div>

</body>
</html>