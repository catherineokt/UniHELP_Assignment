<?php

session_start();
include("db_connect.php");

$student_id = $_SESSION['student_id'];

$full_name = $_POST['full_name'];
$contactNo = $_POST['contactNo'];

$sql =
"UPDATE student
SET full_name='$full_name',
    contactNo='$contactNo'
WHERE student_id='$student_id'";

mysqli_query($conn,$sql);

header("Location: studentProfile.php");
exit();

?>
