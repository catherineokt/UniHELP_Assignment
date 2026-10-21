<?php
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    if ($password != $confirmPassword) {
        echo "<script>
                alert('Passwords do not match!');
                window.location='forgotPassword.html';
              </script>";
        exit();
    }

    $sql = "UPDATE student
            SET password='$password',
                confirmPassword='$confirmPassword'
            WHERE email='$email'";

    if(mysqli_query($conn, $sql)) {

        if(mysqli_affected_rows($conn) > 0){
            echo "<script>
                    alert('Password updated successfully!');
                    window.location='login.html';
                  </script>";
        } else {
            echo "<script>
                    alert('Email not found!');
                    window.location='forgotPassword.html';
                  </script>";
        }

    } else {
        echo "Error: " . mysqli_error($conn);
    }

}

mysqli_close($conn);
?>