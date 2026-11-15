<?php
session_start();
include("db_connect.php");

$student_id = $_SESSION['student_id'];
$skill =isset($_POST['skills'])
        ?implode(",",$_POST['skills'])
        :"";
$availability = isset($_POST['days'])
                ?implode(",",$_POST['days'])
                :"";
$available_time = $_POST['available_time'];
$support_mode = $_POST['support_mode'];

//check whether the student has registered as a volunteer
$checkSql = "SELECT * FROM volunteer WHERE student_id = ?";
$checkSTMT = $conn->prepare ($checkSql);
$checkSTMT ->bind_param("s",$student_id);
$checkSTMT->execute();
$result = $checkSTMT ->get_result();

if ($result->num_rows>0){
    //update the existing volunteer
    $sql = "UPDATE volunteer 
        SET skill =?,
            availability=?,
            available_time=?,
            support_mode=?
        WHERE student_id=?";
    
    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "sssss",
        $skill,
        $availability,
        $available_time,
        $support_mode,
        $student_id
    );
} else{
    //insert as a new volunteer
    $volunter_id = "VOL". rand(1000,9999);

    $sql="INSERT INTO volunteer (volunteer_id,student_id,skill,availability,available_time,support_mode)
        VALUES (?,?,?,?,?,?)";
    
    $stmt= $conn->prepare($sql);

    $stmt->bind_param(
        "ssssss",
        $volunter_id,
        $student_id,
        $skill,
        $availability,
        $available_time,
        $support_mode
    );
}

if ($stmt->execute()) {

    // Update volunteer status in student table
    $updateSql = "UPDATE student
                  SET volunteer_status = 'Successfully'
                  WHERE student_id = ?";

    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("s", $student_id);
    $updateStmt->execute();

    // Redirect back to becomeVolunteer.php
    header("Location: studentPage.html");
    exit();

} else {

    echo "Error: " . $stmt->error;

}
?>
