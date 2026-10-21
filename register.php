<?php

include("db_connect.php");

$student_id = $_POST['student_id'];
$full_name  = $_POST['full_name'];
$email      = $_POST['email'];
$contactNo = $_POST['contactNo'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirmPassword'];

if ($password != $confirmPassword){
    echo"
    <script>
        alert('Password do not match!');
        window.location.href='register.html';
    </script>
    ";
    exit();
}

$check = mysqli_query(
    $conn,
    "SELECT * FROM student WHERE email='$email'"
);

if(mysqli_num_rows($check) > 0)
{
    echo "
    <script>
        alert('Email already registered!');
        window.location.href='register.html';
    </script>
    ";
    exit();
}

$sql = "INSERT INTO student
(student_id, full_name, email,contactNo,password)
VALUES
('$student_id','$full_name','$email','$contactNo','$password')";

if(mysqli_query($conn,$sql))
{
    echo "
    <script>
        alert('Registration Successful!');
        window.location.href='login.html';
    </script>
    ";
}
else
{
    echo "
    <script>
        alert('Registration Failed!');
        window.location.href='register.html';
    </script>
    ";
}

?>