<?php
<<<<<<< HEAD
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . "/db.php";

// STRICT LOGIN CHECK: If not logged in as a volunteer, redirect immediately
if (empty($_SESSION["volunteer_id"])) {
    header("Location: login.html");
    exit();
}
$volunteer_id = $_SESSION["volunteer_id"];
?>

=======
// Enable error reporting to diagnose connection issues
error_reporting(E_ALL);
sql: ini_set('display_errors', 1);

// Use your friend's shared database connection file
require_once "db.php";
?>
>>>>>>> origin/useCase3
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <title>UniHELP - Feedback Received</title>

=======
    <!-- Links your exact global volunteer stylesheet -->
    <link rel="stylesheet" href="style.css">
    <title>UniHELP - Feedback Received</title>
    <!-- FontAwesome for Star Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
>>>>>>> origin/useCase3
    <style>
        .feedback-table-box {
            max-width: 950px;
            margin: 40px auto;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
<<<<<<< HEAD
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .feedback-table-box h1 {
            font-size: 26px;
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .feedback-table-wrapper {
            overflow-x: auto;
        }

        .feedback-table-box table {
=======
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }
        table {
>>>>>>> origin/useCase3
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 14px;
            border: 1px solid black;
        }
<<<<<<< HEAD

        .feedback-table-box th,
        .feedback-table-box td {
            border: 1px solid black;
            padding: 12px;
        }

        .feedback-table-box th {
=======
        th, td {
            border: 1px solid black;
            padding: 12px;
        }
        th {
>>>>>>> origin/useCase3
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
<<<<<<< HEAD

        .feedback-table-box td {
            color: #333;
        }

        .feedback-table-box .stars {
            color: #e56b53;
            white-space: nowrap;
        }

        .feedback-table-box .empty-message {
            color: #666;
            padding: 20px;
        }

        @media (max-width: 600px) {
            .feedback-table-box {
                margin: 20px 10px;
                padding: 15px;
            }
        }
    </style>
</head>

<body>

    <?php 
    // Safely loads the volunteer header regardless of whether it's .php or .html
    if (file_exists(__DIR__ . "/volheader.php")) {
        include __DIR__ . "/volheader.php";
    } elseif (file_exists(__DIR__ . "/volheader.html")) {
        include __DIR__ . "/volheader.html";
    }
    ?>

    <div class="feedback-table-box">
        <h1>Feedback Received from Students</h1>

        <div class="feedback-table-wrapper">
=======
    </style>
</head>
<body>

    <!-- EXACT VOLUNTEER PAGE HEADER MATCHING STYLE.CSS -->
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

    <!-- Main Content Container matching style.css box styling -->
    <div class="feedback-table-box">
        <h1 style="font-size: 26px; text-align: center; margin-bottom: 30px; color: #333;">Feedback Received from Students</h1>

        <div style="overflow-x: auto;">
>>>>>>> origin/useCase3
            <table>
                <thead>
                    <tr>
                        <th>Feedback ID</th>
<<<<<<< HEAD
                        <th>Session ID</th>
=======
                        <th>Feedback</th>
>>>>>>> origin/useCase3
                        <th>Overall Rating</th>
                        <th>Comments</th>
                        <th>Date Received</th>
                    </tr>
                </thead>
<<<<<<< HEAD

                <tbody>
                    <?php
                    $sql = "
                        SELECT
                            f.feedback_id,
                            f.session_id,
                            f.overall_rating,
                            f.comments
                        FROM feedback f
                        INNER JOIN support_session ss
                            ON f.session_id = ss.session_id
                        WHERE ss.volunteer_id = ?
                        ORDER BY f.feedback_id DESC
                    ";

                    $stmt = $con->prepare($sql);
                    $stmt->bind_param("s", $volunteer_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $feedback_id = (int)$row["feedback_id"];
                            $session_id = (int)$row["session_id"];
                            $rating = (int)$row["overall_rating"];
                            $comments = htmlspecialchars(
                                $row["comments"],
                                ENT_QUOTES,
                                "UTF-8"
                            );

                            echo "<tr>";

                            echo "<td>#" . $feedback_id . "</td>";
                            echo "<td>" . $session_id . "</td>";

                            echo "<td class='stars'>";
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating) {
                                    echo "<i class='fa-solid fa-star'></i> ";
                                } else {
                                    echo "<i class='fa-regular fa-star' style='color:#ccc;'></i> ";
                                }
                            }
                            echo " (" . $rating . "/5)";
                            echo "</td>";

                            echo "<td>" . $comments . "</td>";
                            echo "<td>N/A</td>";

                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='empty-message'>
                                    No feedback received yet.
                                    </td></tr>";
                    }

                    $stmt->close();
=======
                <tbody>
                    <?php
                    // Fetch all feedback records from the database using $con from db.php
                    $query = "SELECT * FROM feedback ORDER BY submitted_at ASC";
                    $result = mysqli_query($con, $query);

                    if ($result && mysqli_num_rows($result) > 0) {
                        $counter = 1; // Start counting from 1
                        while ($row = mysqli_fetch_assoc($result)) {
                            $rating = intval($row['overall_rating']);
                            $date = date("d/m/Y H:i", strtotime($row['submitted_at']));
                            
                            echo "<tr>";
                            echo "<td style='color: #333;'>#" . $counter . "</td>";
                            echo "<td style='color: #333;'>" . htmlspecialchars($row['feedback']) . "</td>";
                            
                            // Render star icons dynamically based on rating number
                            echo "<td style='color: #e56b53;'>";
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating) {
                                    echo '<i class="fa-solid fa-star"></i> ';
                                } else {
                                    echo '<i class="fa-solid fa-star" style="color: #ccc;"></i> ';
                                }
                            }
                            echo "</td>";

                            echo "<td style='color: #333;'>" . htmlspecialchars($row['comments']) . "</td>";
                            echo "<td style='color: #666;'>" . $date . "</td>";
                            echo "</tr>";
                            
                            $counter++; // Increment counter for the next row
                        }
                    } else {
                        echo "<tr><td colspan='5' style='color: #666; padding: 20px;'>No feedback received yet.</td></tr>";
                    }

                    mysqli_close($con);
>>>>>>> origin/useCase3
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <hr style="margin-top: 50px; border: 0; border-top: 1px solid #ccc;">
<<<<<<< HEAD

    <footer>
        <p style="text-align: center; font-size: 14px; color: #666;">
            Copyright &copy; UniHELP System 2026
        </p>
    </footer>

=======
    <footer>
        <p style="text-align: center; font-size: 14px; color: #666;">CopyRight®UniHelp System 2026</p>
    </footer>
>>>>>>> origin/useCase3
</body>
</html>