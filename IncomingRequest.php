<?php
//Start the session to access volunteer login information
session_start();
require_once "db.php"; //Database connection

//Ensure the user is logged in as a volunteer
if (empty($_SESSION["volunteer_id"])) {
    exit("Please log in as a volunteer first.");
}

//Get the volunteer ID from the session
$volunteer_id = trim((string) $_SESSION["volunteer_id"]);
//Message variable for success / error feedback
$message = "";

//Help function to safely output text in HTML
if (!function_exists("h")) {
    function h($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
    }
}

//Process form submission when volunteer accepts / rejects request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    //Validate match id
    $match_id = filter_input(
        INPUT_POST, "match_id", FILTER_VALIDATE_INT
    );
    //Get action: accept / reject
    $action = $_POST["action"] ?? "";

    //Validate request data
    if (
        !$match_id ||
        $match_id < 1 ||
        !in_array($action, ["Accept", "Reject"], true)
    ) {
        $message = "Invalid request action.";
    } else {
        try {
            //Begin database
            $con->begin_transaction();

            //Only the assigned volunteer can respond
            $stmt = $con->prepare(
                "SELECT
                    rm.match_id, rm.request_id, rm.matchStatus, sr.status AS requestStatus, sr.preferredDate, sr.preferredTime, sr.supportMode
                 FROM request_match rm
                 JOIN support_request sr ON sr.request_id = rm.request_id
                 WHERE rm.match_id = ? AND rm.volunteer_id = ?
                 FOR UPDATE"
            );

            $stmt->bind_param("is", $match_id, $volunteer_id);
            $stmt->execute();
            $match = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            //Ensure match exists
            if (!$match) {
                throw new RuntimeException(
                    "This request was not found for your account."
                );
            }

            //Prevent processing request more than one
            if (
                $match["matchStatus"] !== "Pending" ||
                $match["requestStatus"] !== "Pending"
            ) {
                throw new RuntimeException(
                    "This request has already been processed."
                );
            }

            $request_id = (int) $match["request_id"];

            //Accept the volunteer match
            if ($action === "Accept") {
                //Update match status to Accepted
                $stmt = $con->prepare(
                    "UPDATE request_match SET matchStatus = 'Accepted'
                     WHERE match_id = ?
                       AND volunteer_id = ?
                       AND matchStatus = 'Pending'"
                );

                $stmt->bind_param("is", $match_id, $volunteer_id);
                $stmt->execute();
                $updated = $stmt->affected_rows;
                $stmt->close();

                if ($updated !== 1) {
                    throw new RuntimeException(
                        "The match could not be accepted."
                    );
                }

                //Update the student's request status
                $stmt = $con->prepare(
                    "UPDATE support_request SET status = 'Accepted'
                     WHERE request_id = ? AND status = 'Pending'"
                );

                $stmt->bind_param("i", $request_id);
                $stmt->execute();
                $updated = $stmt->affected_rows;
                $stmt->close();

                if ($updated !== 1) {
                    throw new RuntimeException(
                        "The request has already been processed."
                    );
                }

                //Check if support session already exists
                $stmt = $con->prepare(
                    "SELECT session_id
                     FROM support_session
                     WHERE request_id = ?
                     LIMIT 1"
                );

                $stmt->bind_param("i", $request_id);
                $stmt->execute();
                $existingSession = $stmt->get_result()->fetch_assoc();
                $stmt->close();

            //Create support session if not exists
            if (!$existingSession) {
                $sessionDate = $match["preferredDate"];
                $sessionTime = $match["preferredTime"];
                $supportMode = $match["supportMode"];

                $stmt = $con->prepare(
                    "INSERT INTO support_session
                     (request_id, volunteer_id, date, time, supportMode, status)
                     VALUES (?, ?, ?, ?, ?, 'Scheduled')"
                );

                $stmt->bind_param(
                    "issss",
                    $request_id, $volunteer_id, $sessionDate, $sessionTime, $supportMode
                );

                $stmt->execute();
                $stmt->close();
            }
                $message = "Request accepted successfully.";

            } else { //Reject the volunteer match
                //Update match status to Rejected
                $stmt = $con->prepare(
                    "UPDATE request_match
                     SET matchStatus = 'Rejected'
                     WHERE match_id = ?
                       AND volunteer_id = ?
                       AND matchStatus = 'Pending'"
                );

                $stmt->bind_param("is", $match_id, $volunteer_id);
                $stmt->execute();
                $updated = $stmt->affected_rows;
                $stmt->close();

                if ($updated !== 1) {
                    throw new RuntimeException(
                        "The match could not be rejected."
                    );
                }

                //Update the student's request status
                $stmt = $con->prepare(
                    "UPDATE support_request
                     SET status = 'Rejected'
                     WHERE request_id = ? AND status = 'Pending'"
                );

                $stmt->bind_param("i", $request_id);
                $stmt->execute();
                $updated = $stmt->affected_rows;
                $stmt->close();

                if ($updated !== 1) {
                    throw new RuntimeException(
                        "The request has already been processed."
                    );
                }

                $message = "Request rejected successfully.";
            }

            //Save all database changes
            $con->commit();

        } catch (Throwable $e) {
            //Roll back changes if have any error
            try {
                $con->rollback();
            } catch (Throwable $rollbackError) {
                //Hide database error from user
            }

            //Show safe error message
            $message = $e instanceof RuntimeException
                ? $e->getMessage()
                : "Unable to process this request. Please try again.";
        }
    }
}

//Retrieve the specific request selected by the volunteer
$requests = [];

$stmt = $con->prepare(
    "SELECT
        rm.match_id, rm.matchStatus, sr.request_id, sr.category, sr.description, sr.preferredDate, sr.preferredTime, sr.supportMode, sr.status AS requestStatus,
        s.username AS studentName, s.email AS studentEmail
     FROM request_match rm
     JOIN support_request sr ON sr.request_id = rm.request_id
     JOIN student s ON s.student_id = sr.student_id
     WHERE rm.volunteer_id = ?
       AND rm.matchStatus = 'Pending'
       AND sr.status = 'Pending'
     ORDER BY rm.createAt DESC"
);

$stmt->bind_param("s", $volunteer_id);
$stmt->execute();

//Store all fetched record in array
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
    <title>Incoming Request</title>
</head>
<body>
    <!-- Add student header menu -->
    <?php include "volheader.html"; ?>

<h1>Incoming Support Requests</h1>

<!-- Display success / error message -->
<div class="errorMsg">
    <?php if ($message !== ""): ?>
        <p><?= h($message) ?></p>
    <?php endif; ?>
</div>

<!-- Show message when no pending requests -->
<div class="notification">
<?php if (!$requests): ?>
    <p>You have no incoming requests.</p>
<?php else: ?>
</div>

    <!-- Loop each pending request -->
    <?php foreach ($requests as $request): ?>
        <div class="request-card">
            <h3>Request #<?= h($request["request_id"]) ?></h3>

            <p>Student: <?= h($request["studentName"]) ?></p>
            <p>Email: <?= h($request["studentEmail"]) ?></p>
            <p>Category: <?= h($request["category"]) ?></p>
            <p>Description: <?= h($request["description"]) ?></p>
            <p>Date: <?= h($request["preferredDate"]) ?></p>
            <p>Time: <?= h($request["preferredTime"]) ?></p>
            <p>Support Mode: <?= h($request["supportMode"]) ?></p>
            <p>
                Request Status:
                <?= h($request["requestStatus"]) ?>
            </p>
            <p>
                Match Status:
                <?= h($request["matchStatus"]) ?>
            </p>

            <!-- Show accepts / rejects button if request is still pending -->
            <?php if (
                $request["requestStatus"] === "Pending" &&
                $request["matchStatus"] === "Pending"
            ): ?>
                <form method="POST" action="IncomingRequest.php">
                    <input type="hidden" name="match_id" value="<?= h($request["match_id"]) ?>">

                    <button type="submit" name="action" value="Accept">
                        Accept
                    </button>

                    <button type="submit" name="action" value="Reject">
                        Reject
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <hr>
    <?php endforeach; ?>

<?php endif; ?>

</body>
</html>
