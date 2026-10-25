<?php
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <title>UniHELP - Feedback Received</title>

    <style>
        .feedback-table-box {
            max-width: 950px;
            margin: 40px auto;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
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
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 14px;
            border: 1px solid black;
        }

        .feedback-table-box th,
        .feedback-table-box td {
            border: 1px solid black;
            padding: 12px;
        }

        .feedback-table-box th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }

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
            <table>
                <thead>
                    <tr>
                        <th>Feedback ID</th>
                        <th>Session ID</th>
                        <th>Overall Rating</th>
                        <th>Comments</th>
                        <th>Date Received</th>
                    </tr>
                </thead>

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
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <hr style="margin-top: 50px; border: 0; border-top: 1px solid #ccc;">

    <footer>
        <p style="text-align: center; font-size: 14px; color: #666;">
            Copyright &copy; UniHELP System 2026
        </p>
    </footer>

</body>
</html>