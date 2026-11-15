<?php
session_start();
require_once "db.php";

// Ensure the user is logged in as a student
if (empty($_SESSION["student_id"])) {
    exit("Please log in as a student first.");
}

// Get student ID from session
$student_id = trim((string) $_SESSION["student_id"]);

// Get session_id from URL, or fall back to the student's latest session
$session_id = filter_input(INPUT_GET, "session_id", FILTER_VALIDATE_INT);
if (!$session_id) {
    $stmt = $con->prepare("
        SELECT ss.session_id 
        FROM support_session ss
        JOIN support_request sr ON sr.request_id = ss.request_id
        WHERE sr.student_id = ?
        ORDER BY ss.session_id DESC LIMIT 1
    ");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($res) {
        $session_id = $res['session_id'];
    } else {
        exit("No support sessions found for your account yet.");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>UniHELP - Submit Feedback</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Student header (same as the Support Request page) -->
    <?php include "stuheader.html"; ?>

    <div class="container" style="width: 450px; max-width: 90%; margin: 40px auto 0 auto;">
        <div class="text-center" style="text-align: center;">
            <h1 style="font-size: 24px; margin-bottom: 25px; color: #333; text-align: center;">Submit Feedback</h1>
        </div>

        <form id="feedback-form" onsubmit="handleFormSubmit(event)">
            <input type="hidden" id="session-id" value="<?= htmlspecialchars((string) $session_id) ?>">

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="feedback-input">Feedback :</label>
                <input type="text" id="feedback-input" class="form-control" required placeholder="Give your feedback" style="width: 100%; padding: 8px; box-sizing: border-box;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label>Overall Rating :</label>
                <div id="star-container" style="display: flex; gap: 8px; margin-top: 5px;">
                    <button type="button" class="star-btn" data-value="1" style="background:none; border:none; font-size:24px; cursor:pointer; color:#e56b53;"><i class="fa-solid fa-star"></i></button>
                    <button type="button" class="star-btn" data-value="2" style="background:none; border:none; font-size:24px; cursor:pointer; color:#e56b53;"><i class="fa-solid fa-star"></i></button>
                    <button type="button" class="star-btn" data-value="3" style="background:none; border:none; font-size:24px; cursor:pointer; color:#e56b53;"><i class="fa-solid fa-star"></i></button>
                    <button type="button" class="star-btn" data-value="4" style="background:none; border:none; font-size:24px; cursor:pointer; color:#e56b53;"><i class="fa-solid fa-star"></i></button>
                    <button type="button" class="star-btn" data-value="5" style="background:none; border:none; font-size:24px; cursor:pointer; color:#ccc;"><i class="fa-solid fa-star"></i></button>
                    <input type="hidden" id="rating-value" value="4">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="feedback-comments">Comments :</label>
                <textarea id="feedback-comments" class="form-control" rows="4" required placeholder="Add comments here" style="resize: none;"></textarea>
            </div>

            <button type="submit" class="btn" style="display: block; margin: 0 auto;">Submit feedback</button>
        </form>
    </div>

    <div id="toast-notification" style="position: fixed; bottom: 20px; right: 20px; background: #000B3D; color: #fff; padding: 15px 20px; border-radius: 8px; display: none; box-shadow: 0px 4px 10px rgba(0,0,0,0.2);">
        <strong>Success!</strong> Your feedback has been successfully submitted.
    </div>

    <hr style="margin-top: 50px; border: 0; border-top: 1px solid #ccc;">
    <footer>
        <p style="text-align: center; font-size: 14px; color: #666;">CopyRight®UniHelp System 2026</p>
    </footer>

    <script>
        const stars = document.querySelectorAll('.star-btn');
        const ratingInput = document.getElementById('rating-value');

        stars.forEach((star, index) => {
            star.addEventListener('click', () => {
                const val = index + 1;
                ratingInput.value = val;
                stars.forEach((s, idx) => {
                    s.style.color = idx < val ? "#e56b53" : "#ccc";
                });
            });
        });

        async function handleFormSubmit(event) {
            event.preventDefault();

            const formData = new URLSearchParams();
            formData.append('session_id', document.getElementById('session-id').value);
            formData.append('feedback', document.getElementById('feedback-input').value);
            formData.append('rating', ratingInput.value);
            formData.append('comments', document.getElementById('feedback-comments').value);

            try {
                const response = await fetch('submit_feedback.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });

                const responseText = await response.text();
                console.log("PHP response:", responseText);

                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (error) {
                    alert("PHP did not return valid JSON. Check the browser console for details.");
                    return;
                }

                if (result.status === "success") {
                    const toast = document.getElementById('toast-notification');
                    toast.style.display = "block";

                    setTimeout(() => {
                        toast.style.display = "none";
                    }, 3500);

                    document.getElementById('feedback-form').reset();
                    ratingInput.value = 4;
                    stars.forEach((s, idx) => {
                        s.style.color = idx < 4 ? "#e56b53" : "#ccc";
                    });
                } else {
                    alert("Error: " + result.message);
                }
            } catch (error) {
                console.error('Error connecting to database script:', error);
                alert('Could not submit feedback. Make sure your local server (XAMPP/PHP) is running.');
            }
        }
    </script>
</body>
</html>
