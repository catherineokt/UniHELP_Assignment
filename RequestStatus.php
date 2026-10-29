<?php
//Start the session to access student login information
session_start();
require_once "db.php"; //Database connection

//Ensure the user is logged in as a student
if (empty($_SESSION["student_id"])) {
    exit("Please log in as a student first.");
}

//Get student ID from session
$student_id = trim((string) $_SESSION["student_id"]);

//Safely display data in HTML
if (!function_exists("h")) {
    function h($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
    }
}

//Get all support request submitted by current student & retrieve selected volunteer information & match status
$stmt = $con->prepare(
    "SELECT
        sr.request_id, sr.category, sr.description, sr.preferredDate, sr.preferredTime, sr.supportMode, sr.status AS requestStatus, rm.matchStatus,v.volunteer_id, 
        volunteerStudent.username AS volunteerName, volunteerStudent.email AS volunteerEmail
     FROM support_request sr
     LEFT JOIN request_match rm ON rm.request_id = sr.request_id
     LEFT JOIN volunteer v  ON v.volunteer_id = rm.volunteer_id
     LEFT JOIN student volunteerStudent ON volunteerStudent.student_id = v.student_id
     WHERE sr.student_id = ?
     ORDER BY sr.request_id DESC"
);

$stmt->bind_param("s", $student_id);
//Execute query
$stmt->execute();

//Store all support requests into an array
$requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>My Support Requests</title>
</head>
<body>

<h1>My Support Requests</h1>

<!-- Check whether student has submitted any support request -->
<?php if (!$requests): ?>
    <p>You have not submitted any support requests.</p>
<?php else: ?>

    <!-- Loop and display support request -->
    <?php foreach ($requests as $request): ?>
        <div class="request-card">
            <!-- Display unique id -->
            <h3>Request #<?= h($request["request_id"]) ?></h3>

            <!-- Display details -->
            <p>Category: <?= h($request["category"]) ?></p>
            <p>Description: <?= h($request["description"]) ?></p>
            <p>
                Preferred Date:
                <?= h($request["preferredDate"]) ?>
            </p>
            <p>
                Preferred Time:
                <?= h($request["preferredTime"]) ?>
            </p>
            <p>
                Support Mode:
                <?= h($request["supportMode"]) ?>
            </p>

            <p>
                Request Status:
                <strong><?= h($request["requestStatus"]) ?></strong>
            </p>

            <!-- Check whether volunteer has been selected -->
            <?php if ($request["volunteer_id"] !== null): ?>

                <p>
                    Selected Volunteer:
                    <?= h(
                        $request["volunteerName"]
                        ?: $request["volunteer_id"]
                    ) ?>
                </p>

                <!-- Display selected volunteer's email & status -->
                <p>
                    Volunteer Email:
                    <?= h($request["volunteerEmail"] ?? "") ?>
                </p>

                <p>
                    Volunteer Response:
                    <?= h($request["matchStatus"] ?? "Pending") ?>
                </p>

            <?php else: ?>

                <!-- Display message if no volunteer has been selected -->
                <p>No volunteer has been selected.</p>

            <?php endif; ?>

            <!-- Display different message depending on request status -->
            <?php if ($request["requestStatus"] === "Pending"): ?>

                <p>Your request is waiting for a volunteer's response.</p>

            <?php elseif ($request["requestStatus"] === "Accepted"): ?>

                <p>
                    Your request has been accepted.
                    Please check your session details.
                </p>

            <?php elseif ($request["requestStatus"] === "Rejected"): ?>

                <p>
                    Your request was rejected.
                    You may submit a new request if needed.
                </p>

            <?php endif; ?>

        </div>
        <hr>
    <?php endforeach; ?>

<?php endif; ?>

</body>
</html>
