
<?php
session_start();
require_once "db.php";

header('Content-Type: application/json; charset=utf-8');

// Ensure student is logged in
if (empty($_SESSION["student_id"])) {
    echo json_encode([
        "status" => "error",
        "message" => "Please log in as a student first."
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {
        $student_id = trim((string)$_SESSION["student_id"]);

        $session_id = filter_input(
            INPUT_POST,
            "session_id",
            FILTER_VALIDATE_INT
        );

        $rating = (int)($_POST["rating"] ?? 0);
        $comments = trim($_POST["comments"] ?? "");

        // Validate submitted data
        if (!$session_id || $rating < 1 || $rating > 5 || $comments === "") {
            throw new Exception("Please provide a valid session, rating and comments.");
        }

        if (strlen($comments) > 500) {
            throw new Exception("Comments cannot exceed 500 characters.");
        }

        // Verify that this session belongs to the logged-in student
        $check = $con->prepare(
            "SELECT ss.session_id
             FROM support_session ss
             JOIN support_request sr
                 ON sr.request_id = ss.request_id
             WHERE ss.session_id = ?
               AND sr.student_id = ?
               AND ss.status = 'Completed'"
        );

        $check->bind_param("is", $session_id, $student_id);
        $check->execute();

        $session = $check->get_result()->fetch_assoc();
        $check->close();

        if (!$session) {
            throw new Exception("Session not found, not completed, or you do not have permission to submit feedback.");
        }

        // Insert feedback using your actual table columns
        $stmt = $con->prepare(
            "INSERT INTO feedback
             (session_id, student_id, rating, comments)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "isis",
            $session_id,
            $student_id,
            $rating,
            $comments
        );

        $stmt->execute();
        $stmt->close();

        echo json_encode([
            "status" => "success",
            "message" => "Feedback saved successfully!"
        ]);

    } catch (Throwable $e) {

        error_log("Feedback submission error: " . $e->getMessage());

        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }

} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method."
    ]);
}
?>
