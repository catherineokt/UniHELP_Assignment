<?php
session_start();
include("db_connect.php");

$student_id = $_SESSION['student_id'];

$skills = implode(", ", $_POST['skills']);
$availability = implode(", ", $_POST['days']);

$sql = "INSERT INTO volunteer
(student_id, skill, availability)
VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sss",
    $student_id,
    $skills,
    $availability
);

$stmt->execute();

header("Location: studentProfile.php");
exit();
?>
