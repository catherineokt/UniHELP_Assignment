<?php
session_start();
require_once "db.php";
<<<<<<< HEAD

// STRICT LOGIN CHECK: If not logged in as a volunteer, redirect to login page
if (empty($_SESSION["volunteer_id"])) {
    header("Location: login.html");
    exit();
}
$volunteer_id = $_SESSION["volunteer_id"];

// Fetch session history for the logged-in volunteer
=======
 
// Bypass login check for testing and set default volunteer ID
$volunteer_id = $_SESSION["volunteer_id"] ?? "VOL001";
 
// Fetch session history for the volunteer
>>>>>>> origin/useCase3
$stmt = $con->prepare(
    "SELECT ss.session_id, ss.date, ss.time, ss.supportMode, ss.status,
            sr.category, sr.description,
            st.email AS otherEmail,
<<<<<<< HEAD
            f.overall_rating AS overall_rating 
=======
            f.overall_rating
>>>>>>> origin/useCase3
     FROM support_session ss
     JOIN support_request sr ON sr.request_id = ss.request_id
     JOIN student st ON st.student_id = sr.student_id
     LEFT JOIN feedback f ON f.session_id = ss.session_id
     WHERE ss.volunteer_id = ?
     ORDER BY ss.date DESC, ss.time DESC"
);
$stmt->bind_param("s", $volunteer_id);
<<<<<<< HEAD

=======
 
>>>>>>> origin/useCase3
$stmt->execute();
$sessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<<<<<<< HEAD

=======
 
>>>>>>> origin/useCase3
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>UniHELP - Support Session History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .session-table-box {
            max-width: 1000px;
            margin: 40px auto;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 14px;
            border: 1px solid black;
        }
        th, td {
            border: 1px solid black;
            padding: 12px;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
<<<<<<< HEAD
    <?php include "volheader.html"; ?>

    <div class="session-table-box">
        <h1 style="font-size: 26px; text-align: center; margin-bottom: 30px; color: #333;">Support Session History</h1>

=======
 
    <header>
        <!-- Logo -->
        <div class="logo">
            <img src="GraduateIcon.png" alt="Graduation Icon">
            <h1>UniHELP</h1>
        </div>
        <!-- Navigation -->
        <nav class="menu" id="menu">
            <a href="volunteerpage.html">Home</a>
            <a href="">Incoming Request</a>
            <a href="">Session History</a>
            <a href="volunteer_feedback.php">Feedback Received</a>
            <a href="">Profile</a>
            <a href="login.html">Logout</a>
        </nav>
    </header>
 
    <div class="session-table-box">
        <h1 style="font-size: 26px; text-align: center; margin-bottom: 30px; color: #333;">Support Session History</h1>
 
>>>>>>> origin/useCase3
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Support Category</th>
                        <th>Support Required</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sessions)): ?>
                        <tr>
                            <td colspan="6" style="color: #666; padding: 20px;">No support sessions found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sessions as $session): ?>
                            <tr>
                                <td style="color: #333;"><?= htmlspecialchars($session["otherEmail"]) ?></td>
                                <td style="color: #333;"><?= htmlspecialchars($session["category"]) ?></td>
                                <td style="color: #333;"><?= htmlspecialchars($session["description"]) ?></td>
                                <td style="color: #666;"><?= htmlspecialchars($session["date"] . " " . $session["time"]) ?></td>
                                <td style="color: #333;"><strong><?= htmlspecialchars($session["status"]) ?></strong></td>
                                <td style="color: #e56b53;">
                                    <?php 
                                        if (!empty($session["overall_rating"])) {
                                            $rating = intval($session["overall_rating"]);
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $rating) {
                                                    echo '<i class="fa-solid fa-star"></i> ';
                                                } else {
                                                    echo '<i class="fa-solid fa-star" style="color: #ccc;"></i> ';
                                                }
                                            }
                                        } else {
                                            echo '<span style="color: #999;">Not Rated</span>';
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<<<<<<< HEAD

=======
 
>>>>>>> origin/useCase3
    <hr style="margin-top: 50px; border: 0; border-top: 1px solid #ccc;">
    <footer>
        <p style="text-align: center; font-size: 14px; color: #666;">CopyRight®UniHelp System 2026</p>
    </footer>
</body>
</html>