<?php
// Enable error reporting to diagnose connection issues
error_reporting(E_ALL);
ini_set('display_errors', 1);
 
session_start();
require_once "db.php";

//Ensure the user is logged in as a volunteer
if (empty($_SESSION["volunteer_id"])) {
    exit("Please log in as a volunteer first.");
}

//Get the volunteer ID from the session
$volunteer_id = trim((string) $_SESSION["volunteer_id"]);

// Month being shown (?month=2026-10), default = this month
$month = $_GET["month"] ?? date("Y-m");
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
    $month = date("Y-m");
}
$first       = strtotime($month . "-01");
$daysInMonth = (int)date("t", $first);
$offset      = (int)date("N", $first) - 1;          // Monday = 0
$monthStart  = date("Y-m-01", $first);
$monthEnd    = date("Y-m-t", $first);
$prevMonth   = date("Y-m", strtotime("-1 month", $first));
$nextMonth   = date("Y-m", strtotime("+1 month", $first));
 

// 1. Load the logged-in volunteer's recurring weekly availability.
$stmt = $con->prepare(
    "SELECT day, startTime, endTime
     FROM availability
     WHERE volunteer_id = ?
     ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday')"
);
$stmt->bind_param("s", $volunteer_id);
$stmt->execute();
$weeklyAvailability = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Group availability by weekday.
$availabilityByDay = [];

foreach ($weeklyAvailability as $availability) {
    $availabilityByDay[$availability['day']][] = $availability;
}

// 2. Load this volunteer's sessions within the displayed month.
$stmt = $con->prepare(
    "SELECT
        ss.session_id,
        ss.date,
        ss.time,
        ss.status,
        ss.supportMode,
        sr.request_id,
        sr.category,
        sr.preferredDate,
        sr.preferredTime,
        sr.status AS requestStatus,
        st.username AS studentName
     FROM support_session ss
     JOIN support_request sr
        ON sr.request_id = ss.request_id
     JOIN student st
        ON st.student_id = sr.student_id
     WHERE ss.volunteer_id = ?
       AND ss.date BETWEEN ? AND ?
     ORDER BY ss.date, ss.time"
);
$stmt->bind_param("sss", $volunteer_id, $monthStart, $monthEnd);
$stmt->execute();
$monthSessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Group actual sessions by date.
$sessionsByDate = [];

foreach ($monthSessions as $session) {
    $sessionsByDate[$session['date']][] = $session;
}

// 3. Upcoming sessions.
$stmt = $con->prepare(
    "SELECT
        ss.session_id, ss.date, ss.time, ss.status,
        sr.category, st.username AS studentName
     FROM support_session ss
     JOIN support_request sr ON sr.request_id = ss.request_id
     JOIN student st ON st.student_id = sr.student_id
     WHERE ss.volunteer_id = ?
       AND ss.status IN ('Scheduled', 'Rescheduled')
       AND TIMESTAMP(ss.date, ss.time) >= NOW()
     ORDER BY ss.date, ss.time
     LIMIT 10"
);
$stmt->bind_param("s", $volunteer_id);
$stmt->execute();
$upcoming = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 4. Sessions that have started but are not yet completed.
$stmt = $con->prepare(
    "SELECT
        ss.session_id, ss.date, ss.time, ss.status,
        sr.category, st.username AS studentName
     FROM support_session ss
     JOIN support_request sr ON sr.request_id = ss.request_id
     JOIN student st ON st.student_id = sr.student_id
     WHERE ss.volunteer_id = ?
       AND ss.status IN ('Scheduled', 'Rescheduled')
       AND TIMESTAMP(ss.date, ss.time) < NOW()
     ORDER BY ss.date DESC, ss.time DESC
     LIMIT 10"
);
$stmt->bind_param("s", $volunteer_id);
$stmt->execute();
$toComplete = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

 
$csrf = $_SESSION["csrf"] ??= bin2hex(random_bytes(16));
$flash = $_SESSION["flash"] ?? null;
unset($_SESSION["flash"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Links your exact global volunteer stylesheet -->
    <link rel="stylesheet" href="style.css">
    <title>Calendar Session</title>
    <!-- FontAwesome for Star Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind CSS so the calendar grid and layout styles work properly -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
 
    <!-- INCLUDES EXACT SHARED VOLUNTEER HEADER -->
    <?php include "volheader.html"; ?>
 
    <!-- Main Container Card matching the student calendar layout -->
    <main class="flex-grow flex items-center justify-center p-4 sm:p-8">
        <div class="bg-white w-full max-w-5xl rounded-lg shadow-sm border border-slate-200 p-8 sm:p-12">
 
            <!-- result message after cancel / reschedule / complete -->
            <?php if ($flash): ?>
                <div class="mb-8 p-3 text-sm text-center border <?= $flash['ok'] ? 'border-green-600 text-green-700' : 'border-red-600 text-red-700' ?>">
                    <?= htmlspecialchars($flash['msg']) ?>
                </div>
            <?php endif; ?>
 
            <!-- Page Title -->
            <h1 class="serif-title text-3xl sm:text-4xl font-bold text-center text-slate-900 mb-12 tracking-tight">
                Calendar Session
            </h1>
 
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
 
                    <div class="grid grid-cols-7 text-center font-serif text-slate-500 text-sm mb-4">
                        <span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span><span>S</span>
                    </div>
 
                    
                <div class="grid grid-cols-7 text-center gap-y-4 text-slate-700 text-sm">
                    <?php for ($i = 0; $i < $offset; $i++): ?>
                        <div></div>
                    <?php endfor; ?>
                    
                    <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                    
                        <?php
                        $currentDate = sprintf("%s-%02d", $month, $day);
                        $weekday = date("l", strtotime($currentDate));
                    
                        $dayAvailability = $availabilityByDay[$weekday] ?? [];
                        $daySessions = $sessionsByDate[$currentDate] ?? [];
                        ?>
                
                        <div class="min-h-20 border rounded p-1 text-left">
                            <div class="font-semibold text-center">
                                <?= $day ?>
                            </div>
                    
                            <?php foreach ($dayAvailability as $slot): ?>
                                <div class="mt-1 text-xs bg-green-100 text-green-800 rounded p-1">
                                    Available<br>
                                    <?= htmlspecialchars(date("g:i A", strtotime($slot['startTime']))) ?>
                                    -
                                    <?= htmlspecialchars(date("g:i A", strtotime($slot['endTime']))) ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php foreach ($daySessions as $session): ?>
                                <?php
                                $statusClass = match ($session['status']) {
                                    'Scheduled' => 'bg-blue-100 text-blue-800',
                                    'Rescheduled' => 'bg-yellow-100 text-yellow-800',
                                    'Completed' => 'bg-purple-100 text-purple-800',
                                    'Cancelled' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                ?>
                                <div class="mt-1 text-xs <?= $statusClass ?> rounded p-1">
                                    <?= htmlspecialchars(date("g:i A", strtotime($session['time']))) ?><br>
                                    <?= htmlspecialchars($session['status']) ?><br>
                                    <?= htmlspecialchars($session['studentName']) ?>
                                </div>
                            <?php endforeach; ?>
                            
                        </div>
                    <?php endfor; ?>
                </div>

                </div>
 
                <!-- Right Side: Upcoming + To Complete -->
                <div class="w-full max-w-sm space-y-6">
 
                    <div class="border border-slate-800 p-8 text-sm text-slate-900">
                        <p class="mb-4">Upcoming Sessions:</p>
 
                        <?php if (empty($upcoming)): ?>
                            <p class="text-slate-500">No upcoming sessions.</p>
                        <?php else: ?>
                            <?php foreach ($upcoming as $i => $s): ?>
                                <div class="<?= $i > 0 ? 'mt-5' : '' ?>">
                                    <p><?= htmlspecialchars(date("j M", strtotime($s["date"]))) ?> &nbsp;|&nbsp; <?= htmlspecialchars(date("g.i A", strtotime($s["time"]))) ?></p>
                                    <p>Skill: <?= htmlspecialchars($s["category"]) ?></p>
                                    <p>Student: <?= htmlspecialchars($s["studentName"]) ?></p>
                                    <p>Status: <?= htmlspecialchars($s["status"]) ?></p>
 
                                    <form method="POST" action="session_actions.php" class="mt-2 flex flex-wrap items-center gap-2">
                                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                        <input type="hidden" name="role" value="volunteer">
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
 
                    <div class="border border-slate-800 p-8 text-sm text-slate-900">
                        <p class="mb-4">Sessions to Mark as Completed:</p>
 
                        <?php if (empty($toComplete)): ?>
                            <p class="text-slate-500">No sessions waiting.</p>
                        <?php else: ?>
                            <?php foreach ($toComplete as $i => $s): ?>
                                <div class="<?= $i > 0 ? 'mt-5' : '' ?>">
                                    <p><?= htmlspecialchars(date("j M", strtotime($s["date"]))) ?> &nbsp;|&nbsp; <?= htmlspecialchars(date("g.i A", strtotime($s["time"]))) ?></p>
                                    <p>Skill: <?= htmlspecialchars($s["category"]) ?></p>
                                    <p>Student: <?= htmlspecialchars($s["studentName"]) ?></p>
                                    <p>Status: <?= htmlspecialchars($s["status"]) ?></p>
 
                                    <form method="POST" action="session_actions.php" class="mt-2 flex flex-wrap items-center gap-2">
                                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                        <input type="hidden" name="role" value="volunteer">
                                        <input type="hidden" name="session_id" value="<?= (int)$s['session_id'] ?>">
                                        <button type="submit" name="action" value="complete" class="border border-slate-800 px-2 py-0.5">Mark Completed</button>
                                        <button type="submit" name="action" value="cancel" class="border border-slate-800 px-2 py-0.5" onclick="return confirm('Cancel this session?');">Cancel</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
 
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
