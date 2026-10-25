<?php
// Enable error reporting to diagnose connection issues
error_reporting(E_ALL);
ini_set('display_errors', 1);
 
session_start();
require_once "db.php";
 
// STRICT LOGIN CHECK: If not logged in as a student, redirect to login page
if (empty($_SESSION["student_id"])) {
    header("Location: login.html");
    exit();
}
$student_id = $_SESSION["student_id"];
 
// Month being shown (?month=2026-10), default = this month
$month = $_GET["month"] ?? date("Y-m");
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
    $month = date("Y-m");
}
$first      = strtotime($month . "-01");
$daysInMonth = (int)date("t", $first);
$offset      = (int)date("N", $first) - 1;          // Monday = 0
$monthStart  = date("Y-m-01", $first);
$monthEnd    = date("Y-m-t", $first);
$prevMonth   = date("Y-m", strtotime("-1 month", $first));
$nextMonth   = date("Y-m", strtotime("+1 month", $first));
 
// 1) Days in this month that have a scheduled session (for the dots)
$stmt = $con->prepare(
    "SELECT DISTINCT DAY(ss.date) AS d
     FROM support_session ss
     JOIN support_request sr ON sr.request_id = ss.request_id
     WHERE sr.student_id = ?
       AND ss.status IN ('Scheduled','Rescheduled')
       AND ss.date BETWEEN ? AND ?"
);
$stmt->bind_param("sss", $student_id, $monthStart, $monthEnd);
$stmt->execute();
$bookedDays = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), "d");
$stmt->close();
 
// 2) Upcoming sessions for the student
$stmt = $con->prepare(
    "SELECT ss.session_id, ss.date, ss.time, ss.status, sr.category, vs.username AS volunteerName
     FROM support_session ss
     JOIN support_request sr ON sr.request_id = ss.request_id
     JOIN volunteer v ON v.volunteer_id = ss.volunteer_id
     JOIN student vs ON vs.student_id = v.student_id
     WHERE sr.student_id = ?
       AND ss.status IN ('Scheduled','Rescheduled')
       AND (ss.date > CURDATE() OR (ss.date = CURDATE() AND ss.time >= CURTIME()))
     ORDER BY ss.date ASC, ss.time ASC
     LIMIT 10"
);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$upcoming = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
 
// ===== Manage Support Sessions =====
$csrf = $_SESSION["csrf"] ??= bin2hex(random_bytes(16));
$flash = $_SESSION["flash"] ?? null;
unset($_SESSION["flash"]);
 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Calendar Session</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .history-box {
            max-width: 950px;
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
    </style>
</head>
<body>
 
    <!-- ALWAYS USES STUDENT HEADER FOR STUDENT PAGES -->
    <?php include "stuheader.php"; ?>
 
    <!-- Main Container Card -->
    <main class="flex-grow flex items-center justify-center p-4 sm:p-8">
        <div class="bg-white w-full max-w-5xl rounded-lg shadow-sm border border-slate-200 p-8 sm:p-12">
 
            <?php if ($flash): ?>
                <div class="mb-8 p-3 text-sm text-center border <?= $flash['ok'] ? 'border-green-600 text-green-700' : 'border-red-600 text-red-700' ?>">
                    <?= htmlspecialchars($flash['msg']) ?>
                </div>
            <?php endif; ?>
 
            <!-- Page Title -->
            <h1 class="serif-title text-3xl sm:text-4xl font-bold text-center text-slate-900 mb-12 tracking-tight">
                Calendar Session
            </h1>
 
            <!-- Content Grid (Calendar on Left, Upcoming Session on Right) -->
            <div class="flex flex-col lg:flex-row items-center lg:items-start justify-center gap-12 lg:gap-20">
 
                <!-- Left Side: Calendar -->
                <div class="w-full max-w-sm">
                    <div class="flex items-center justify-between mb-6">
                        <a href="?month=<?= $prevMonth ?>" class="text-slate-500">&laquo;</a>
                        <h2 class="text-xl font-bold text-slate-800 text-center tracking-wider">
                            <?= strtoupper(date("F Y", $first)) ?>
                        </h2>
                        <a href="?month=<?= $nextMonth ?>" class="text-slate-500">&raquo;</a>
                    </div>
 
                    <!-- Days Header -->
                    <div class="grid grid-cols-7 text-center font-serif text-slate-500 text-sm mb-4">
                        <span>M</span>
                        <span>T</span>
                        <span>W</span>
                        <span>T</span>
                        <span>F</span>
                        <span>S</span>
                        <span>S</span>
                    </div>
 
                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 text-center gap-y-4 text-slate-700 text-sm font-light">
                        <?php for ($i = 0; $i < $offset; $i++): ?>
                            <span></span>
                        <?php endfor; ?>
 
                        <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                            <?php if (in_array($day, $bookedDays)): ?>
                                <div class="flex flex-col items-center justify-center relative">
                                    <span><?= $day ?></span>
                                    <span class="w-1.5 h-1.5 bg-slate-900 rounded-full absolute -bottom-2"></span>
                                </div>
                            <?php else: ?>
                                <span><?= $day ?></span>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>
 
                <!-- Right Side: Upcoming Sessions -->
                <div class="w-full max-w-sm border border-slate-800 p-8 text-sm text-slate-900">
                    <p class="mb-4">Upcoming Sessions:</p>
 
                    <?php if (empty($upcoming)): ?>
                        <p class="text-slate-500">No upcoming sessions.</p>
                    <?php else: ?>
                        <?php foreach ($upcoming as $i => $s): ?>
                            <div class="<?= $i > 0 ? 'mt-5' : '' ?>">
                                <p><?= htmlspecialchars(date("j M", strtotime($s["date"]))) ?> &nbsp;|&nbsp; <?= htmlspecialchars(date("g.i A", strtotime($s["time"]))) ?></p>
                                <p>Skill: <?= htmlspecialchars($s["category"]) ?></p>
                                <p>Volunteer: <?= htmlspecialchars($s["volunteerName"]) ?></p>
                                <p>Status: <?= htmlspecialchars($s["status"]) ?></p>
 
                                <!-- reschedule / cancel -->
                                <form method="POST" action="session_actions.php" class="mt-2 flex flex-wrap items-center gap-2">
                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                    <input type="hidden" name="role" value="student">
                                    <input type="hidden" name="session_id" value="<?= (int)$s['session_id'] ?>">
                                    <input type="date" name="new_date" min="<?= date('Y-m-d') ?>" class="border border-slate-400 px-1">
                                    <input type="time" name="new_time" class="border border-slate-400 px-1">
                                    <button type="submit" name="action" value="reschedule" class="border border-slate-800 px-2 py-0.5">Reschedule</button>
                                    <button type="submit" name="action" value="cancel" class="border border-slate-800 px-2 py-0.5" onclick="return confirm('Cancel this session?');">Cancel</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
 
            </div>
 
        </div>
    </main>
 
    <hr style="margin-top: 50px; border: 0; border-top: 1px solid #ccc;">
    <footer>
        <p style="text-align: center; font-size: 14px; color: #666;">CopyRight®UniHelp System 2026</p>
    </footer>
</body>
</html>