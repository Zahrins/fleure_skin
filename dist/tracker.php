<?php

// Database connection
$conn = new mysqli("localhost", "root", "", "fleurskin");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql_select = "SELECT * FROM tracker";
$result = $conn->query($sql_select);

session_start(); 

if (!isset($_SESSION['user_login'])) {
    header('Location: login.php');
    exit();
}

$logged_in_username = $_SESSION['user_login'];

// Database connection
$servername = "localhost";
$db_username = "root";
$password = "";
$dbname = "fleurskin";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $db_username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle AJAX requests for updating task status
if (isset($_POST['action']) && $_POST['action'] == 'update_task_status') {
    $task_id = $_POST['task_id'];
    $is_completed = $_POST['is_completed'];
    $date = $_POST['date'];
    
    try {
        // Check if record exists
        $stmt = $pdo->prepare("SELECT id FROM task_completions WHERE username = ? AND task_id = ? AND completion_date = ?");
        $stmt->execute([$logged_in_username, $task_id, $date]);
        
        if ($stmt->rowCount() > 0) {
            // Update existing record
            $stmt = $pdo->prepare("UPDATE task_completions SET is_completed = ? WHERE username = ? AND task_id = ? AND completion_date = ?");
            $stmt->execute([$is_completed, $logged_in_username, $task_id, $date]);
        } else {
            // Insert new record
            $stmt = $pdo->prepare("INSERT INTO task_completions (username, task_id, is_completed, completion_date, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$logged_in_username, $task_id, $is_completed, $date]);
        }
        
        echo json_encode(['status' => 'success']);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// Load existing schedules
$existing_schedules = [];
try {
    $stmt = $pdo->prepare("SELECT schedule_type, schedule_data FROM user_schedules WHERE username = ?");
    $stmt->execute([$logged_in_username]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existing_schedules[$row['schedule_type']] = json_decode($row['schedule_data'], true);
    }
} catch(PDOException $e) {
    // Handle error silently
}

// Load task completions for today
$today = date('Y-m-d');
$task_completions = [];
try {
    $stmt = $pdo->prepare("SELECT task_id, is_completed FROM task_completions WHERE username = ? AND completion_date = ?");
    $stmt->execute([$logged_in_username, $today]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $task_completions[$row['task_id']] = $row['is_completed'];
    }
} catch(PDOException $e) {
    // Handle error silently
}

?>

<!DOCTYPE html>
<html class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./style.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Beauty Tracker - FleurSkin</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <style>
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .schedule-section {
            animation: slideIn 0.5s ease;
        }

        /* Mobile sidebar fixes */
        .mobile-sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }
        
        .mobile-sidebar.open {
            transform: translateX(0);
        }

        /* Mobile table styles */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .mobile-table {
            min-width: 800px; /* Ensure table doesn't compress too much */
        }

        @media (max-width: 768px) {
            .table-container {
                margin: 0 -1rem; /* Extend to screen edges on mobile */
                border-radius: 0;
            }
            
            .mobile-table th,
            .mobile-table td {
                white-space: nowrap;
                padding: 0.5rem;
                font-size: 0.875rem;
            }
            
            .mobile-table img {
                width: 40px;
                height: 40px;
                object-fit: cover;
            }
        }

        
    </style>
</head>
<body class="bg-first">
    
    <!-- Mobile Menu Button -->
    <button id="mobile-menu-btn" class="fixed lg:hidden p-2">
        <span class="material-symbols-outlined">menu</span>
    </button>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-overlay" class="z-40 fixed inset-0 hidden lg:hidden"></div>
    
    <!-- Mobile Navigation Sidebar -->
    <div id="mobile-sidebar" class="mobile-sidebar fixed lg:static lg:translate-x-0 transform -translate-x-full transition-transform duration-300 ease-in-out shadow-md flex-shrink-0 z-50 lg:z-auto top-0 left-0 w-80 bg-second text-slate-700 h-full lg:hidden">
        <!-- Close button for mobile -->
        <button id="close-sidebar" class="absolute p-2">
            <span class="material-symbols-outlined">menu</span>
        </button>
        
        <!-- Sidebar Content -->
        <div class="flex flex-col px-6 py-8 space-y-4 w-full h-full">
            <h2 class="text-2xl font-bold mb-3 mt-6 text-slate-700">FleurSkin</h2>

            <nav class="flex-1 space-y-1">
                <a href="homepage.php" class="flex items-center px-4 py-3 rounded-md hover:bg-first text-slate-700">
                    <span class="material-symbols-outlined text-2xl mr-3">home</span>
                    Home
                </a>

                <a href="schedule.php" class="flex items-center px-4 py-3 rounded-md hover:bg-first text-slate-700">
                    <span class="material-symbols-outlined text-2xl mr-3">event_list</span>
                    Schedule
                </a>

                <a href="products.php" class="flex items-center px-4 py-3 rounded-md hover:bg-first text-slate-700">
                    <span class="material-symbols-outlined text-2xl mr-3">fragrance</span>
                    Products
                </a>

                <a href="tracker.php" class="flex items-center px-4 py-3 rounded-md bg-first text-slate-700">
                    <span class="material-symbols-outlined text-2xl mr-3">content_paste_search</span>
                    Beauty Tracker
                </a>

                <a href="diary.php" class="flex items-center px-4 py-3 rounded-md hover:bg-first text-slate-700">
                    <span class="material-symbols-outlined text-2xl mr-3">auto_stories</span>
                    My Diary
                </a>
            </nav>
            
            <div class="mt-auto">
                <!-- Logout Button -->
                <a href="logout.php" class="flex items-center px-4 py-3 rounded-md bg-red-100 hover:bg-red-200 text-red-700 w-full">
                    <span class="material-symbols-outlined text-2xl mr-3">logout</span>
                    Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <div class="hidden lg:flex w-54 h-screen bg-second text-slate-700 flex-col px-4 py-6 space-y-4 fixed">
        <h2 class="text-2xl font-bold mb-6">FleurSkin</h2>

        <nav class="flex-1 space-y-2">
            <a href="homepage.php" class="flex items-center px-4 py-2 rounded-md hover:bg-first">
                <span class="material-symbols-outlined text-2xl mr-3">home</span>
                Home
            </a>

            <a href="schedule.php" class="flex items-center px-4 py-2 rounded-md hover:bg-first">
                <span class="material-symbols-outlined text-2xl mr-3">event_list</span>
                Schedule
            </a>

            <a href="products.php" class="flex items-center px-4 py-2 rounded-md hover:bg-first">
                <span class="material-symbols-outlined text-2xl mr-3">fragrance</span>
                Products
            </a>

            <a href="tracker.php" class="flex items-center px-4 py-2 rounded-md bg-first">
                <span class="material-symbols-outlined text-2xl mr-3">content_paste_search</span>
                Beauty Tracker
            </a>

            <a href="diary.php" class="flex items-center px-4 py-2 rounded-md hover:bg-first">
                <span class="material-symbols-outlined text-2xl mr-3">auto_stories</span>
                My Diary
            </a>
        </nav>
        
        <div class="mt-auto">
            <!-- Logout Button -->
            <a href="logout.php" class="flex items-center px-4 py-2 rounded-md bg-red-100 hover:bg-red-200 text-red-700 w-full">
                <span class="material-symbols-outlined text-2xl mr-3">logout</span>
                Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="lg:ml-64 min-h-screen p-4 pt-16 lg:pt-4">

        <!-- Username and Date -->
        <div class="text-center mb-6">
            <div class="text-xl font-semibold text-slate-700">
            Halooooo <?php echo htmlspecialchars($logged_in_username); ?>!
            </div>
            <div class="text-lg text-slate-500 mt-2">
            Today: <?php echo date('l, F j, Y'); ?>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex justify-center mb-6">
            <div class="flex bg-white rounded-lg shadow-md overflow-hidden">
            <button onclick="showSection('daily')" id="dailyTab" class="px-6 py-3 bg-second text-slate-600 font-medium">Daily</button>
            <button onclick="showSection('weekly')" id="weeklyTab" class="px-6 py-3 text-slate-600 font-medium hover:bg-gray-50">Weekly</button>
            <button onclick="showSection('monthly')" id="monthlyTab" class="px-6 py-3 text-slate-600 font-medium hover:bg-gray-50">Monthly</button>
            </div>
        </div>

        <!-- Daily Tasks -->
        <div id="daily" class="schedule-section">
            
            <!-- Progress Bar -->
            <div class="bg-white p-4 rounded-lg shadow-md mb-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-slate-700">Daily Progress</span>
                <span id="dailyProgress" class="text-sm font-medium text-slate-700">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="dailyProgressBar" class="bg-pink-400 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            </div>

            <div class="mb-5 flex justify-between">
                <!-- morning/noon/night -->
                <div class="flex flex-wrap gap-4">
                    <a href="#morning" class="flex items-center border-2 border-second rounded-3xl cursor-pointer hover:scale-95 transition-all duration-500">
                        <span class="text-slate-900 text-[15px] font-medium mx-3 my-1">🌅 Morning</span>
                    </a>
                    <a href="#noon" class="flex items-center border-2 border-second rounded-3xl cursor-pointer hover:scale-95 transition-all duration-500">
                        <span class="text-slate-900 text-[15px] font-medium mx-3 my-1">☀️ Noon</span>
                    </a>
                    <a href="#night" class="flex items-center border-2 border-second rounded-3xl cursor-pointer hover:scale-95 transition-all duration-500">
                        <span class="text-slate-900 text-[15px] font-medium mx-3 my-1">🌙 Night</span>
                    </a>
                </div>
                    <!-- Action Buttons -->
                <div>
                    <a href="schedule.php" class="border border-slate-800 rounded-3xl px-5 py-2 bg-second text-slate-700 hover:font-semibold">Edit Schedule</a>
                </div>
            </div>

            <!-- Morning Tasks -->
            <div id="morning" class="mb-6 bg-white p-4 rounded-lg shadow-md">
            <div class="flex items-center mb-4">
                <span class="text-2xl mr-2">🌅</span>
                <h3 class="text-lg font-semibold text-slate-700">Morning Routine</h3>
            </div>
            <div id="morningTasks" class="space-y-2"></div>
            </div>

            <!-- Noon Tasks -->
            <div id="noon" class="mb-6 bg-white p-4 rounded-lg shadow-md">
            <div class="flex items-center mb-4">
                <span class="text-2xl mr-2">☀️</span>
                <h3 class="text-lg font-semibold text-slate-700">Noon Routine</h3>
            </div>
            <div id="noonTasks" class="space-y-2"></div>
            </div>

            <!-- Night Tasks -->
            <div id="night" class="mb-6 bg-white p-4 rounded-lg shadow-md">
            <div class="flex items-center mb-4">
                <span class="text-2xl mr-2">🌙</span>
                <h3 class="text-lg font-semibold text-slate-700">Night Routine</h3>
            </div>
            <div id="nightTasks" class="space-y-2"></div>
            </div>
        </div>

        <!-- Weekly Tasks -->
        <div id="weekly" class="schedule-section hidden">
            <h2 class="text-2xl font-bold text-slate-600 mb-6 text-center">Weekly Beauty Goals</h2>
            
            <!-- Progress Bar -->
            <div class="bg-white p-4 rounded-lg shadow-md mb-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-slate-700">Weekly Progress</span>
                <span id="weeklyProgress" class="text-sm font-medium text-slate-700">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="weeklyProgressBar" class="bg-purple-400 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            </div>

            <!-- Action Buttons -->
            <div class="mb-5 justify-end flex">
                <a href="schedule.php" class="border border-slate-800 rounded-3xl px-5 py-2 bg-second text-slate-700 hover:font-semibold">Edit Schedule</a>
            </div>

            <!-- Weekly Tasks -->
            <div class="bg-white p-4 rounded-lg shadow-md mb-6">
            <div class="flex items-center mb-4">
                <span class="text-2xl mr-2">📅</span>
                <h3 class="text-lg font-semibold text-slate-700">Weekly Tasks</h3>
            </div>
            <div id="weeklyTasksList" class="space-y-2"></div>
            </div>
        </div>

        <!-- Monthly Tasks -->
        <div id="monthly" class="schedule-section hidden">
            <h2 class="text-2xl font-bold text-slate-600 mb-6 text-center">Monthly Beauty Goals</h2>
            
            <!-- Progress Bar -->
            <div class="bg-white p-4 rounded-lg shadow-md mb-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-slate-700">Monthly Progress</span>
                <span id="monthlyProgress" class="text-sm font-medium text-slate-700">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="monthlyProgressBar" class="bg-indigo-400 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            </div>

            <!-- Action Buttons -->
            <div class="mb-5 justify-end flex">
                <a href="schedule.php" class="border border-slate-800 rounded-3xl px-5 py-2 bg-second text-slate-700 hover:font-semibold">Edit Schedule</a>
            </div>

            <!-- Monthly Tasks -->
            <div class="bg-white p-4 rounded-lg shadow-md mb-6">
            <div class="flex items-center mb-4">
                <span class="text-2xl mr-2">📅</span>
                <h3 class="text-lg font-semibold text-slate-700">Monthly Tasks</h3>
            </div>
            <div id="monthlyTasksList" class="space-y-2"></div>
            </div>
        </div>

        <script>
            // Initialize data from PHP
            let existingSchedules = <?php echo json_encode($existing_schedules); ?>;
            let taskCompletions = <?php echo json_encode($task_completions); ?>;
            let today = '<?php echo $today; ?>';

            // Initialize display when page loads
            document.addEventListener('DOMContentLoaded', function() {
            loadTasks();
            showSection('daily');
            });

            function showSection(section) {
            // Hide all sections
            document.querySelectorAll('.schedule-section').forEach(el => el.classList.add('hidden'));
            
            // Show selected section
            document.getElementById(section).classList.remove('hidden');
            
            // Update tab styles
            document.querySelectorAll('button[id$="Tab"]').forEach(tab => {
                tab.classList.remove('bg-second');
                tab.classList.add('hover:bg-gray-50');
            });
            
            document.getElementById(section + 'Tab').classList.add('bg-second');
            document.getElementById(section + 'Tab').classList.remove('hover:bg-gray-50');
            
            // Update progress bars
            updateProgressBars();
            }

            function loadTasks() {
            loadDailyTasks();
            loadWeeklyTasks();
            loadMonthlyTasks();
            updateProgressBars();
            }

            function loadDailyTasks() {
            const dailyData = existingSchedules.daily || { morning: [], noon: [], night: [] };
            
            loadTasksForTimeOfDay('morningTasks', dailyData.morning, 'morning');
            loadTasksForTimeOfDay('noonTasks', dailyData.noon, 'noon');
            loadTasksForTimeOfDay('nightTasks', dailyData.night, 'night');
            }

            function loadTasksForTimeOfDay(containerId, tasks, timeOfDay) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            
            if (tasks.length === 0) {
                container.innerHTML = '<p class="text-slate-500 text-sm italic">No tasks scheduled for this time</p>';
                return;
            }
            
            tasks.forEach((task, index) => {
                const taskId = `daily_${timeOfDay}_${index}`;
                const isCompleted = taskCompletions[taskId] == 1;
                
                const taskElement = document.createElement('div');
                taskElement.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-md hover:bg-gray-100 transition-colors';
                
                taskElement.innerHTML = `
                <div class="flex items-center flex-1">
                    <input type="checkbox" id="${taskId}" ${isCompleted ? 'checked' : ''} 
                    onchange="updateTaskStatus('${taskId}', this.checked)"
                    class="mr-3 h-5 w-5 text-pink-500 rounded focus:ring-pink-300">
                    <label for="${taskId}" class="flex-1 cursor-pointer ${isCompleted ? 'line-through text-slate-500' : 'text-slate-700'}">
                    ${task.task}
                    </label>
                </div>
                ${task.note ? `<div class="text-xs text-slate-500 ml-2 max-w-xs">${task.note}</div>` : ''}
                `;
                
                container.appendChild(taskElement);
            });
            }

            function loadWeeklyTasks() {
            const weeklyData = existingSchedules.weekly || [];
            const container = document.getElementById('weeklyTasksList');
            container.innerHTML = '';
            
            if (weeklyData.length === 0) {
                container.innerHTML = '<p class="text-slate-500 text-sm italic">No weekly tasks scheduled</p>';
                return;
            }
            
            weeklyData.forEach((task, index) => {
                const taskId = `weekly_${index}`;
                const isCompleted = taskCompletions[taskId] == 1;
                
                const taskElement = document.createElement('div');
                taskElement.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-md hover:bg-gray-100 transition-colors';
                
                taskElement.innerHTML = `
                <div class="flex items-center flex-1">
                    <input type="checkbox" id="${taskId}" ${isCompleted ? 'checked' : ''} 
                    onchange="updateTaskStatus('${taskId}', this.checked)"
                    class="mr-3 h-5 w-5 text-purple-500 rounded focus:ring-purple-300">
                    <label for="${taskId}" class="flex-1 cursor-pointer ${isCompleted ? 'line-through text-slate-500' : 'text-slate-700'}">
                    ${task.task}
                    </label>
                    <span class="text-xs text-slate-500 ml-2">${task.frequencyText}</span>
                </div>
                ${task.note ? `<div class="text-xs text-slate-500 ml-2 max-w-xs">${task.note}</div>` : ''}
                `;
                
                container.appendChild(taskElement);
            });
            }

            function loadMonthlyTasks() {
            const monthlyData = existingSchedules.monthly || [];
            const container = document.getElementById('monthlyTasksList');
            container.innerHTML = '';
            
            if (monthlyData.length === 0) {
                container.innerHTML = '<p class="text-slate-500 text-sm italic">No monthly tasks scheduled</p>';
                return;
            }
            
            monthlyData.forEach((task, index) => {
                const taskId = `monthly_${index}`;
                const isCompleted = taskCompletions[taskId] == 1;
                
                const taskElement = document.createElement('div');
                taskElement.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-md hover:bg-gray-100 transition-colors';
                
                taskElement.innerHTML = `
                <div class="flex items-center flex-1">
                    <input type="checkbox" id="${taskId}" ${isCompleted ? 'checked' : ''} 
                    onchange="updateTaskStatus('${taskId}', this.checked)"
                    class="mr-3 h-5 w-5 text-green-500 rounded focus:ring-green-300">
                    <label for="${taskId}" class="flex-1 cursor-pointer ${isCompleted ? 'line-through text-slate-500' : 'text-slate-700'}">
                    ${task.task}
                    </label>
                    <span class="text-xs text-slate-500 ml-2">${task.frequencyText}</span>
                </div>
                ${task.note ? `<div class="text-xs text-slate-500 ml-2 max-w-xs">${task.note}</div>` : ''}
                `;
                
                container.appendChild(taskElement);
            });
            }

            function updateTaskStatus(taskId, isCompleted) {
            // Update local storage
            taskCompletions[taskId] = isCompleted ? 1 : 0;
            
            // Update UI
            const label = document.querySelector(`label[for="${taskId}"]`);
            if (isCompleted) {
                label.classList.add('line-through', 'text-slate-500');
                label.classList.remove('text-slate-700');
            } else {
                label.classList.remove('line-through', 'text-slate-500');
                label.classList.add('text-slate-700');
            }
            
            // Update progress bars
            updateProgressBars();
            
            // Send to server
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_task_status&task_id=${taskId}&is_completed=${isCompleted ? 1 : 0}&date=${today}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status !== 'success') {
                console.error('Error updating task status:', data.message);
                // Revert the change if there was an error
                taskCompletions[taskId] = isCompleted ? 0 : 1;
                document.getElementById(taskId).checked = !isCompleted;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Revert the change if there was an error
                taskCompletions[taskId] = isCompleted ? 0 : 1;
                document.getElementById(taskId).checked = !isCompleted;
            });
            }

            function updateProgressBars() {
            // Daily progress
            const dailyData = existingSchedules.daily || { morning: [], noon: [], night: [] };
            const totalDailyTasks = dailyData.morning.length + dailyData.noon.length + dailyData.night.length;
            let completedDailyTasks = 0;
            
            ['morning', 'noon', 'night'].forEach(timeOfDay => {
                dailyData[timeOfDay].forEach((task, index) => {
                const taskId = `daily_${timeOfDay}_${index}`;
                if (taskCompletions[taskId] == 1) completedDailyTasks++;
                });
            });
            
            const dailyProgress = totalDailyTasks > 0 ? Math.round((completedDailyTasks / totalDailyTasks) * 100) : 0;
            document.getElementById('dailyProgress').textContent = dailyProgress + '%';
            document.getElementById('dailyProgressBar').style.width = dailyProgress + '%';
            
            // Weekly progress
            const weeklyData = existingSchedules.weekly || [];
            const totalWeeklyTasks = weeklyData.length;
            let completedWeeklyTasks = 0;
            
            weeklyData.forEach((task, index) => {
                const taskId = `weekly_${index}`;
                if (taskCompletions[taskId] == 1) completedWeeklyTasks++;
            });
            
            const weeklyProgress = totalWeeklyTasks > 0 ? Math.round((completedWeeklyTasks / totalWeeklyTasks) * 100) : 0;
            document.getElementById('weeklyProgress').textContent = weeklyProgress + '%';
            document.getElementById('weeklyProgressBar').style.width = weeklyProgress + '%';
            
            // Monthly progress
            const monthlyData = existingSchedules.monthly || [];
            const totalMonthlyTasks = monthlyData.length;
            let completedMonthlyTasks = 0;
            
            monthlyData.forEach((task, index) => {
                const taskId = `monthly_${index}`;
                if (taskCompletions[taskId] == 1) completedMonthlyTasks++;
            });
            
            const monthlyProgress = totalMonthlyTasks > 0 ? Math.round((completedMonthlyTasks / totalMonthlyTasks) * 100) : 0;
            document.getElementById('monthlyProgress').textContent = monthlyProgress + '%';
            document.getElementById('monthlyProgressBar').style.width = monthlyProgress + '%';
            }
        </script>

        
         <!-- Daily Skin Log -->
         <div class="mt-8 mb-10 p-4 bg-white rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-4">
                <p class="text-xl font-semibold text-slate-700">Daily Skin Log</p>
                <a href="skinCondition.php" class="inline-block border border-slate-800 rounded-3xl px-4 py-2 bg-second text-slate-700 hover:font-semibold transition-all text-sm">
                    Add Skin Condition
                </a>
            </div>
            
            <?php if ($result && $result->num_rows > 0): ?>
            <div class="table-container rounded-lg border border-slate-200">
                <table class="mobile-table min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Skin Condition</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Image</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Products Used</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Scale (1-10)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Note</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <?php
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr class='hover:bg-slate-50'>";
                            echo "<td class='px-4 py-3 text-sm text-slate-900'>" . date('M j, Y', strtotime($row['date'])) . "</td>";
                            echo "<td class='px-4 py-3 text-sm text-slate-600'>" . htmlspecialchars($row['skin_conditions']) . "</td>";
                            echo "<td class='px-4 py-3 text-sm text-slate-600'>";
                                if (!empty($row['image'])) {
                                    echo '<img src="uploads3/' . htmlspecialchars($row['image']) . '" class="w-10 h-10 object-cover rounded-md" alt="Product">';
                                } else {
                                    echo "<span class='text-slate-400 text-xs'>No picture</span>";
                                }
                            echo "</td>";
                            echo "<td class='px-4 py-3 text-sm text-slate-600'>" . htmlspecialchars($row['product']) . "</td>";
                            echo "<td class='px-4 py-3 text-sm text-slate-600'>" . htmlspecialchars($row['scale']) . "</td>";
                            echo "<td class='px-4 py-3 text-sm text-slate-600'>" . htmlspecialchars($row['note']) . "</td>";
        
                            echo "<td class='px-4 py-3 text-sm'>
                                <div class='flex space-x-1'>
                                    <a href='edit_track.php?id=" . htmlspecialchars($row['id']) . "' class='button text-blue-600 hover:text-blue-800' title='Edit'>
                                        <span class='material-symbols-outlined text-lg'>edit</span>
                                    </a>
                                    <a href='delete_track.php?id=" . htmlspecialchars($row['id']) . "' class='button text-red-600 hover:text-red-800' title='Delete' onclick='return confirm(\"Are you sure you want to delete this product?\")'>
                                        <span class='material-symbols-outlined text-lg'>delete</span>
                                    </a>
                                </div>
                            </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <!-- No Product Message -->
            <div class="text-center py-12">
                <div class="mb-4">
                    <span class="material-symbols-outlined text-6xl text-slate-300">inventory_2</span>
                </div>
                <h3 class="text-2xl font-semibold text-slate-600 mb-2">Your Skin Has a Story to Tell</h3>
                <p class="text-slate-500 mb-6">Let’s begin your beauty journey — one log at a time!</p>
                <a href="skinCondition.php" class="inline-block border border-slate-800 rounded-3xl px-8 py-3 bg-second text-slate-700 hover:font-semibold transition-all">
                    Start Your First Log
                </a>
            </div>
            <?php endif; ?>
         </div>
    </div>

    <!-- Back to Top Button -->
    <div class="w-10 h-10 bg-second rounded-full flex fixed bottom-5 right-5 cursor-pointer z-30 shadow-lg hover:shadow-xl transition-shadow" onclick="scrollToTop()">
        <span class="text-xl m-auto text-slate-700">↑</span>
    </div>

    <script>
        // Mobile menu functionality
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const closeSidebar = document.getElementById('close-sidebar');
        const mobileSidebar = document.getElementById('mobile-sidebar');
        const overlay = document.getElementById('mobile-overlay');

        function openSidebar() {
            mobileSidebar.classList.add('open');
            overlay.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeSidebarFunc() {
            mobileSidebar.classList.remove('open');
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Event listeners
        mobileMenuBtn.addEventListener('click', openSidebar);
        closeSidebar.addEventListener('click', closeSidebarFunc);
        overlay.addEventListener('click', closeSidebarFunc);

        // Close sidebar when clicking on navigation links on mobile
        const navLinks = mobileSidebar.querySelectorAll('nav a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    closeSidebarFunc();
                }
            });
        });
        
        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                closeSidebarFunc();
            }
        });

        // Scroll to top function
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Add smooth scrolling for mobile table
        const tableContainer = document.querySelector('.table-container');
        if (tableContainer) {
            let isDown = false;
            let startX;
            let scrollLeft;

            tableContainer.addEventListener('mousedown', (e) => {
                isDown = true;
                startX = e.pageX - tableContainer.offsetLeft;
                scrollLeft = tableContainer.scrollLeft;
            });

            tableContainer.addEventListener('mouseleave', () => {
                isDown = false;
            });

            tableContainer.addEventListener('mouseup', () => {
                isDown = false;
            });

            tableContainer.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - tableContainer.offsetLeft;
                const walk = (x - startX) * 2;
                tableContainer.scrollLeft = scrollLeft - walk;
            });
        }

    </script>

    <?php $conn->close(); ?>
</body>
</html>