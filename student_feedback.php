<?php
session_start();
require_once "db.php";

<<<<<<< HEAD
// STRICT LOGIN CHECK: If not logged in, redirect to login page immediately
if (empty($_SESSION["student_id"])) {
    header("Location: login.html");
    exit();
}
$student_id = $_SESSION["student_id"];

// FIXED: Removed the aggressive redirect header("Location: volunteer_feedback.php") 
// so that student-volunteers can safely use this form if they want, 
// while keeping their session volunteer_id properly populated.
$vol_check = $con->prepare("SELECT volunteer_id FROM volunteer WHERE student_id = ?");
$vol_check->bind_param("s", $student_id);
$vol_check->execute();
$vol_res = $vol_check->get_result();
if ($vol_res->num_rows > 0) {
    $vol_row = $vol_res->fetch_assoc();
    $_SESSION["volunteer_id"] = $vol_row["volunteer_id"];
} else {
    unset($_SESSION["volunteer_id"]);
}
$vol_check->close();

// Get session_id from URL, or automatically fallback to your latest session if not specified
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
=======
// Login check removed so the page loads directly
$student_id = "STD001"; // Default fallback student ID for testing
$session_id = filter_input(INPUT_GET, "session_id", FILTER_VALIDATE_INT) ?? 1;
>>>>>>> origin/useCase3
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

<<<<<<< HEAD
    <!-- DYNAMIC HEADER: Automatically switches between student and volunteer header -->
    <?php 
    if (isset($_SESSION['volunteer_id']) && !empty($_SESSION['volunteer_id'])) {
        include "volunteer_header.php"; 
    } else {
        include "stuheader.php"; 
    }
    ?>
=======
    <header>
        <div class="logo">
            <img src="GraduateIcon.png" alt="Graduation Icon">
            <h1>UniHELP</h1>
        </div>
        <nav class="menu" id="menu">
            <a href="studentpage.html">Home</a>
            <a href="">My Request</a>
            <a href="support_session_history.php">Session History</a>
            <a href="student_feedback.php">Feedback</a>
            <a href="">Profile</a>
            <a href="login.html">Logout</a>
        </nav>
    </header>
>>>>>>> origin/useCase3

    <div class="container" style="width: 450px; margin-top: 40px;">
        <div class="text-center">
            <h1 style="font-size: 24px; margin-bottom: 25px; color: #333;">Submit Feedback</h1>
        </div>

        <form id="feedback-form" onsubmit="handleFormSubmit(event)">
<<<<<<< HEAD
            <input type="hidden" id="session-id" value="<?= htmlspecialchars($session_id) ?>">

=======
            <!-- Hidden inputs for database foreign keys -->
            <input type="hidden" id="session-id" value="<?= htmlspecialchars($session_id) ?>">
            <input type="hidden" id="student-id" value="<?= htmlspecialchars($student_id) ?>">

            <!-- Added back your Feedback input field -->
>>>>>>> origin/useCase3
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

            <button type="submit" class="btn">Submit feedback</button>
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
<<<<<<< HEAD
            formData.append('feedback', document.getElementById('feedback-input').value);
=======
            formData.append('student_id', document.getElementById('student-id').value);
            formData.append('feedback', document.getElementById('feedback-input').value); // Added feedback value
>>>>>>> origin/useCase3
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
                
                const result = await response.json();

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