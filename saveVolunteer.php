<?php
session_start();
include("db_connect.php");

$student_id = $_SESSION['student_id'];
$skill = implode(", ", $_POST['skills']);
$availability = implode(", ", $_POST['days']);
$time = $_POST['available_time'];
$support_mode = $_POST['support_mode'];

/** make an random id for volunteer **/
$volunteer_id = "VOL" . rand(1000,9999);

/** insert data into database **/
$sql = "INSERT INTO volunteer
(volunteer_id , student_id, skill, availability, available_time, support_mode)
VALUES (?,?,?,?,?,?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssss",
    $volunteer_id,
    $student_id,
    $skill,
    $availability,
    $available_time,
    $support_mode
);

if ($stmt->execute()) {

    // Update volunteer status in student table
    $updateSql = "UPDATE student
                  SET volunteer_status = 'Successfully'
                  WHERE student_id = ?";

    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("s", $student_id);
    $updateStmt->execute();

    // Redirect back to becomeVolunteer.php
    header("Location: becomeVolunteer.php");
    exit();

} else {

    echo "Error: " . $stmt->error;

}
?>
