<?php

session_start();
include("db_connect.php");

if(!isset($_SESSION['student_id'])){
    header("Location: login.html");
    exit();
}

$student_id = $_SESSION['student_id'];

$stmt= mysqli_prepare(
    $conn,
    "UPDATE student
    SET volunteer_status = 'Volunteer'
    WHERE student_id= ?"
)

mysqli_stmt_bind_param($stmt,"s",$student_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: studentProfile.php");
exit();

?>