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
$error = "";

//Variables used to preserve from values after valiadate error
$category = "";
$description = "";
$preferredDate = "";
$preferredTime = "";
$supportMode = "";

//Safely display user data in HTML
if (!function_exists("h")) {
    function h($value) {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES,
            "UTF-8"
        );
    }
}

//Process form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    //Retrieve submittedd form values
    $category = trim($_POST["category"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $preferredDate = trim($_POST["preferredDate"] ?? "");
    $preferredTime = trim($_POST["preferredTime"] ?? "");
    $supportMode = trim($_POST["supportMode"] ?? "");

    //Allow request categories
    $validCategories = ["Academic", "Technology", "New Student", "General"];
    //Allow support mode
    $validModes = ["Virtual", "Face-to-Face"];

    //Check whether all require fields contain valid values
    if (
        !in_array($category, $validCategories, true) ||
        !in_array($supportMode, $validModes, true) ||
        $description === "" ||
        $preferredDate === "" ||
        $preferredTime === ""
    ) {
        $error = "Please complete all fields correctly.";

    //Ensure student choose today or feature date
    } elseif ($preferredDate < date("Y-m-d")) {
        $error = "Please choose today or a future date.";

    } else {
        try {
            //Confirm that the student exists
            $check = $con->prepare(
                "SELECT student_id  FROM student WHERE student_id = ?"
            );

            $check->bind_param("s", $student_id);
            $check->execute();

            $student = $check->get_result()->fetch_assoc();
            $check->close();

            if (!$student) {
                throw new RuntimeException(
                    "Your student account could not be found."
                );
            }
            //Save the request
            
            //Request_id is generated automatically by MySQL
            $stmt = $con->prepare(
                "INSERT INTO support_request
                (student_id, category, description, preferredDate, preferredTime, supportMode, status)
                VALUES (?, ?, ?, ?, ?, ?, 'Pending')"
            );

            $stmt->bind_param(
                "ssssss",
                $student_id, $category, $description, $preferredDate, $preferredTime, $supportMode
            );

            $stmt->execute();

            //Store generated request id
            $request_id = $con->insert_id;

            $stmt->close();

            //Pass the new request to MatchingResult.php
            header(
                "Location: matchingResult.php?request_id=" .
                urlencode((string)$request_id)
            );
            exit;

        }  catch (Throwable $e) {
            //Log unexpected error for debugging
            if ($con->errno || $con->connect_errno === 0) {
                // Log the error for debugging.
                error_log($e->getMessage());
            }

            //Display error message
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Student header menu
include "stuheader.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Support Request</title>
</head>
<body>

    <h1>Support Request</h1>

    <!-- Display error / status message if form validate / submit -->
    <?php if ($error !== ""): ?>
        <p class="error"><?= h($error) ?></p>
    <?php endif; ?>

    <!-- Submit the request data to SupportRequest using POST -->
    <form method="POST" action="supportRequest.php">

        <div class="req-container">

            <div class="req-form">
                <label for="category">Category</label>
                <span>:</span>
                <select name="category" id="category" required>
                    <option value="">Select category</option>
                    <?php
                    //Define available support categories
                    $categories = [
                        "Academic", "Technology", "New Student", "General"
                    ];
                    //Loop the categories to generate dropdown options
                    foreach ($categories as $item):
                    ?>
                        <option
                            value="<?= h($item) ?>"
                            <?= $category === $item
                                ? "selected" : "" ?>>
                            <!-- Display category name, keep the previous selection after validate error -->
                            <?= h($item) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="req-form">
                <label for="description">Description</label>
                <span>:</span>
                <textarea name="description" id="description" required placeholder="Describe the support you need"><?= h($description) ?></textarea>
            </div>

            <div class="req-form">
                <label for="preferredDate">Preferred Date</label>
                <span>:</span>
                <!-- Prevent selection dates earlier than today, retain previous entered date -->
                <input type="date" id="preferredDate" name="preferredDate"
                    min="<?= h(date('Y-m-d')) ?>" 
                    value="<?= h($preferredDate) ?>"
                    required>
            </div>

            <div class="req-form">
                <label for="preferredTime">Preferred Time</label>
                <span>:</span>
                <input type="time" id="preferredTime" name="preferredTime" value="<?= h($preferredTime) ?>" required>
            </div>

            <div class="req-form">
                <label for="supportMode">Support Mode</label>
                <span>:</span>
                <select name="supportMode" id="supportMode" required>
                    <option value="">Select support mode</option>
                    <option value="Virtual"
                        <?= $supportMode === "Virtual"
                            ? "selected" : "" ?>>
                        Virtual
                    </option>
                    <option value="Face-to-Face"
                        <?= $supportMode === "Face-to-Face"
                            ? "selected" : "" ?>>
                        Face-to-Face
                    </option>
                </select>
            </div>

        </div>

        <button type="submit">Find Volunteers</button>
    </form>

</body>
</html>
