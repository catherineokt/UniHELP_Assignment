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
//Variable used to display success / error messages
$message = "";


//Safely display data in HTML
if (!function_exists("h")) {
    function h($value) {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES,
            "UTF-8"
        );
    }
}

//Read the request ID from the redirect URL
$request_id = filter_input(
    INPUT_GET,
    "request_id",
    FILTER_VALIDATE_INT
);

//Validate request id
if (!$request_id || $request_id < 1) {
    exit("Invalid request ID.");
}

//Retrieve only a request belonging to this student
$stmt = $con->prepare(
    "SELECT *
     FROM support_request
     WHERE request_id = ?
       AND student_id = ?"
);

$stmt->bind_param("is", $request_id, $student_id);
$stmt->execute();

$request = $stmt->get_result()->fetch_assoc();
$stmt->close();

//Stop access if request does not belong to the student
if (!$request) {
    exit("Request not found or you do not have permission to view it.");
}

//Process the Select Volunteer button
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    //Get selected volunteer id
    $volunteer_id = trim(
        (string) ($_POST["volunteer_id"] ?? "")
    );

    //Check the request status
    if ($request["status"] !== "Pending") {
        $message = "This request is no longer pending.";
    } elseif ($volunteer_id === "") {
        $message = "Please select a volunteer.";
    } else {
        try {
            //Start to maintain data consistency
            $con->begin_transaction();

            //Lock the request and confirm it belongs to this student
            $lock = $con->prepare(
                "SELECT status, category, preferredDate, preferredTime, supportMode
                 FROM support_request
                 WHERE request_id = ? AND student_id = ?
                 FOR UPDATE"
            );

            $lock->bind_param("is", $request_id, $student_id);
            $lock->execute();
            $lockedRequest = $lock->get_result()->fetch_assoc();
            $lock->close();

            if (!$lockedRequest ||
                $lockedRequest["status"] !== "Pending") {
                throw new RuntimeException(
                    "This request is no longer pending."
                );
            }

            //Check the volunteer's skills and availability
            $check = $con->prepare(
                "SELECT v.volunteer_id
                 FROM volunteer v
                 WHERE v.volunteer_id = ?
                   AND v.availability = 'Active'
                   AND (
                       CASE ?
                           WHEN 'Academic' THEN
                               v.skill IN ('Mathematics', 'Assignment Guidance')
                               OR EXISTS (
                                   SELECT 1
                                   FROM skill sk
                                   WHERE sk.volunteer_id = v.volunteer_id
                                     AND sk.skillName IN ('Mathematics', 'Assignment Guidance')
                               )
                           WHEN 'Technology' THEN
                               v.skill IN ('Programming', 'Database')
                               OR EXISTS (
                                   SELECT 1
                                   FROM skill sk
                                   WHERE sk.volunteer_id = v.volunteer_id
                                     AND sk.skillName IN ('Programming', 'Database')
                               )
                           WHEN 'New Student' THEN
                               v.skill IN ('Campus Facilities', 'University Registration')
                               OR EXISTS (
                                   SELECT 1
                                   FROM skill sk
                                   WHERE sk.volunteer_id = v.volunteer_id
                                     AND sk.skillName IN ('Campus Facilities', 'University Registration')
                               )
                           WHEN 'General' THEN 1 = 1
                           ELSE 1 = 0
                       END
                   )
                   /* CHeck volunteer availability */ 
                   AND EXISTS (
                       SELECT 1
                       FROM availability a
                       WHERE a.volunteer_id = v.volunteer_id
                         AND a.day = ?
                         AND ? >= a.startTime
                         AND ? <= a.endTime
                   )
                 LIMIT 1"
            );

            $category = $lockedRequest["category"];
            $preferredDate = $lockedRequest["preferredDate"];
            $preferredTime = $lockedRequest["preferredTime"];

            $check->bind_param(
                "sssss",
                $volunteer_id, $category, $preferredDate, $preferredTime, $preferredTime
            );

            $check->execute();
            $matchingVolunteer = $check->get_result()->fetch_assoc();
            $check->close();

            //Volunteer not suitable
            if (!$matchingVolunteer) {
                throw new RuntimeException(
                    "This volunteer does not match your request's skills or availability."
                );
            }

            //Do not allow second volunteer selection
            $existing = $con->prepare(
                "SELECT match_id
                 FROM request_match
                 WHERE request_id = ?
                 LIMIT 1"
            );

            $existing->bind_param("i", $request_id);
            $existing->execute();
            $alreadyMatched = $existing->get_result()->fetch_assoc();
            $existing->close();

            if ($alreadyMatched) {
                throw new RuntimeException(
                    "You have already selected a volunteer for this request."
                );
            }

            //Save the selected volunteer
            $insert = $con->prepare(
                "INSERT INTO request_match
                 (volunteer_id, request_id, matchStatus, createAt)
                 VALUES (?, ?, 'Pending', NOW())"
            );

            $insert->bind_param("si", $volunteer_id, $request_id);
            $insert->execute();
            $insert->close();

            //Save changes peranently
            $con->commit();

            //Redirect student to request status page
            header("Location: RequestStatus.php");
            exit;
        } catch (Throwable $e) {
            //Undo all changes if an error occurs
            try {
                $con->rollback();
            } catch (Throwable $rollbackError) {
                //Ignore rollback errors
            }

            //Log error for debugging
            error_log($e->getMessage());

            //Show error message
            $message = $e instanceof RuntimeException
                ? $e->getMessage()
                : "Unable to select the volunteer. Please try again.";
        }
    }
}


//Retrieve volunteers matching the request
$stmt = $con->prepare(
    "SELECT
        v.volunteer_id, s.username, s.email,
        /* Combine multiple skills into one string */
        GROUP_CONCAT(
            DISTINCT sk.skillName
            ORDER BY sk.skillName
            SEPARATOR ', '
        ) AS skills
     FROM volunteer v
     JOIN student s ON s.student_id = v.student_id
     LEFT JOIN skill sk ON sk.volunteer_id = v.volunteer_id
     WHERE v.availability = 'Active'
     /* Match volunteer skills with request category */
       AND (
           CASE ?
               WHEN 'Academic' THEN
                   v.skill IN ('Mathematics', 'Assignment Guidance')
                   OR sk.skillName IN ('Mathematics', 'Assignment Guidance')

               WHEN 'Technology' THEN
                   v.skill IN ('Programming', 'Database')
                   OR sk.skillName IN ('Programming', 'Database')

               WHEN 'New Student' THEN
                   v.skill IN ('Campus Facilities', 'University Registration')
                   OR sk.skillName IN ('Campus Facilities', 'University Registration')

               WHEN 'General' THEN 1 = 1

               ELSE 1 = 0
           END
       )
       /* Match volunteer availability */
       AND EXISTS (
           SELECT 1
           FROM availability a
           WHERE a.volunteer_id = v.volunteer_id
             AND a.day = ?
             AND ? >= a.startTime
             AND ? <= a.endTime
       )
     GROUP BY v.volunteer_id, s.username, s.email
     ORDER BY s.username"
);

$category = $request["category"];
$preferredDate = $request["preferredDate"];
$preferredTime = $request["preferredTime"];

$stmt->bind_param(
    "ssss",
    $category, $preferredDate, $preferredTime, $preferredTime
);

$stmt->execute();

//Store all volunteers in an array
$volunteers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Matching Volunteer Results</title>
</head>
<body>
    <!-- Add student header menu -->
    <?php include "stuheader.html"; ?>

    <h1>Matching Volunteer Results</h1>

    <!-- Display an error / status message once exists -->
    <div class="errorMsg">
        <?php if ($message !== ""): ?>
            <p class="error"><?= h($message) ?></p>
        <?php endif; ?>
    </div>

    <!-- Display detials of student's submitted request -->
    <h3>Your Support Request</h3>

    <p>
        <strong>Request ID:</strong>
        <?= h($request["request_id"]) ?><br>

        <strong>Category:</strong>
        <?= h($request["category"]) ?><br>

        <strong>Description:</strong>
        <?= h($request["description"]) ?><br>

        <strong>Date:</strong>
        <?= h($request["preferredDate"]) ?><br>

        <strong>Time:</strong>
        <?= h($request["preferredTime"]) ?><br>

        <strong>Mode:</strong>
        <?= h($request["supportMode"]) ?><br>

        <strong>Status:</strong>
        <?= h($request["status"]) ?>
    </p>

    <!-- List volunteers who match request -->
    <h3>Available Volunteers</h3>
    <!-- Count & display number of matching volunteers -->
    <p>Number of matching volunteers: <strong><?= count($volunteers) ?></strong> </p>

    <!-- Chehck whether any matching volunteers found -->
    <?php if (empty($volunteers)): ?>
        <!-- Display message if no volunteer match -->
        <p>No volunteers match your preferred date, time and support mode. </p>

    <?php else: ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Volunteer Name</th>
                        <th>Volunteer Email</th>
                        <th>Skills</th>
                        <th>Support Mode</th>
                        <th>Preferred Date</th>
                        <th>Preferred Time</th>
                        <th>Selection</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach (
                        $volunteers as $index => $volunteer
                    ): ?>
                        <tr>
                            <!-- Display the row number, start from 1 -->
                            <td><?= $index + 1 ?></td>

                            <td>
                                <?= h($volunteer["username"]) ?>
                            </td>

                            <td>
                                <?= h($volunteer["email"]) ?>
                            </td>

                            <td>
                                <?= h(
                                    $volunteer["skills"]
                                    ?: "Not specified"
                                ) ?>
                            </td>

                            <td>
                                <?= h($request["supportMode"]) ?>
                            </td>

                            <td>
                                <?= h($request["preferredDate"]) ?>
                            </td>

                            <td>
                                <?= h($request["preferredTime"]) ?>
                            </td>

                            <!-- Povide a selection volunteer-->
                            <td>
                                <form method="POST"
                                    action="matchingResult.php?request_id=<?= urlencode((string) $request_id) ?>">

                                    <input type="hidden" name="volunteer_id" value="<?= h($volunteer["volunteer_id"]) ?>">

                                    <button type="submit" <?= $request["status"] !== "Pending" ? "disabled" : "" ?>>Select Volunteer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

</body>
</html>
