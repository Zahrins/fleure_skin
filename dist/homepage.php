<?php
session_start(); 

if (!isset($_SESSION['user_login'])) {
    header('Location: login.php');
    exit();
}

$logged_in_username = $_SESSION['user_login'];

// Include koneksi database
require_once 'koneksi.php';
$conn = getConnection();
$pdo = getPDOConnection();

// Handle AJAX request for deleting individual schedule items
if (isset($_POST['action']) && $_POST['action'] == 'delete_schedule_item') {
    $schedule_type = $_POST['schedule_type'];
    $item_index = $_POST['item_index'];
    
    try {
        // Fetch current schedule data
        $stmt = $pdo->prepare("SELECT schedule_data FROM user_schedules WHERE username = ? AND schedule_type = ?");
        $stmt->execute([$logged_in_username, $schedule_type]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $schedule_data = json_decode($result['schedule_data'], true);
            
            if ($schedule_type == 'weekly') {
                // Remove specific task from weekly schedule
                if (isset($schedule_data[$item_index])) {
                    unset($schedule_data[$item_index]);
                    $schedule_data = array_values($schedule_data); // Re-index array
                }
            } else if ($schedule_type == 'daily') {
                // Remove specific task from daily schedule
                $time_of_day = $_POST['time_of_day'];
                if (isset($schedule_data[$time_of_day][$item_index])) {
                    unset($schedule_data[$time_of_day][$item_index]);
                    $schedule_data[$time_of_day] = array_values($schedule_data[$time_of_day]); // Re-index array
                }
            } else if ($schedule_type == 'monthly') {
                // Remove specific task from monthly schedule
                if (isset($schedule_data[$item_index])) {
                    unset($schedule_data[$item_index]);
                    $schedule_data = array_values($schedule_data); // Re-index array
                }
            }
            
            // Update database with modified schedule
            if (empty($schedule_data) || (is_array($schedule_data) && count(array_filter($schedule_data)) == 0)) {
                // If schedule is completely empty, delete the record
                $stmt = $pdo->prepare("DELETE FROM user_schedules WHERE username = ? AND schedule_type = ?");
                $stmt->execute([$logged_in_username, $schedule_type]);
            } else {
                // Update with modified data
                $stmt = $pdo->prepare("UPDATE user_schedules SET schedule_data = ?, created_at = NOW() WHERE username = ? AND schedule_type = ?");
                $stmt->execute([json_encode($schedule_data), $logged_in_username, $schedule_type]);
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Schedule item deleted successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Schedule not found!']);
        }
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error deleting schedule item: ' . $e->getMessage()]);
    }
    exit();
}

// Handle AJAX request for clearing entire schedule type
if (isset($_POST['action']) && $_POST['action'] == 'clear_schedule_type') {
    $schedule_type = $_POST['schedule_type'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM user_schedules WHERE username = ? AND schedule_type = ?");
        $stmt->execute([$logged_in_username, $schedule_type]);
        
        echo json_encode(['status' => 'success', 'message' => ucfirst($schedule_type) . ' schedule cleared successfully!']);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error clearing schedule: ' . $e->getMessage()]);
    }
    exit();
}

// Fetch user schedules with error handling
$schedules = [];
try {
    $stmt = $pdo->prepare("SELECT schedule_type, schedule_data, created_at FROM user_schedules WHERE username = ? ORDER BY created_at DESC");
    $stmt->execute([$logged_in_username]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $schedules[$row['schedule_type']] = json_decode($row['schedule_data'], true);
    }
} catch(PDOException $e) {
    $error_message = "Error loading schedules: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./style.css" rel="stylesheet">
    <title>Homepage - FleurSkin</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        <div class="flex flex-col px-5 py-8 space-y-4 w-full h-full">
            <h2 class="text-2xl font-bold mb-3 mt-6 text-slate-700">FleurSkin</h2>

            <nav class="flex-1 space-y-1">
                <a href="homepage.php" class="flex items-center px-4 py-3 rounded-md bg-first text-slate-700">
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

                <a href="tracker.php" class="flex items-center px-4 py-3 rounded-md hover:bg-first text-slate-700">
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
            <a href="homepage.php" class="flex items-center px-4 py-2 rounded-md bg-first">
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

            <a href="tracker.php" class="flex items-center px-4 py-2 rounded-md hover:bg-first">
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
    <div class="lg:ml-64 flex-1 min-h-screen p-4 pt-16 lg:pt-4">
        
        <!-- Welcome Section -->
        <div class="lg:mt-6 mb-8 text-center">
            <h1 class="text-3xl lg:text-4xl font-bold text-slate-700 mb-2">
                Welcome back, <span class="text-rose-500"><?php echo htmlspecialchars($logged_in_username); ?></span>! 
            </h1>
            <p class="text-slate-600">Ready to glow up today? ✨</p>
        </div>

        <?php if (isset($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>

        <!-- Daily Schedule Section -->
        <?php if (isset($schedules['daily']) && !empty($schedules['daily'])): ?>
        <div class="mb-8 schedule-section">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <h2 class="text-2xl lg:text-3xl font-bold text-slate-600">📅 Daily Schedule</h2>
                <div class="flex gap-2">
                    <a href="schedule.php" class="text-sm bg-second px-3 py-1 rounded-full">
                        Edit
                    </a>
                    <button onclick="clearScheduleType('daily')" class="text-sm bg-red-400 px-3 py-1 rounded-full">
                        Clear All
                    </button>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Morning -->
                <?php if (!empty($schedules['daily']['morning'])): ?>
                <div class="bg-gradient-to-br from-yellow-50 to-orange-50 p-6 rounded-lg shadow-lg">
                    <div class="flex items-center mb-4">
                        <span class="text-3xl mr-3">🌅</span>
                        <h3 class="text-xl font-semibold text-slate-700">Morning</h3>
                    </div>
                    <ul class="space-y-3">
                        <?php foreach ($schedules['daily']['morning'] as $index => $task): ?>
                        <li class="bg-white p-3 rounded-md shadow-sm">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="font-medium text-slate-800"><?php echo htmlspecialchars($task['task']); ?></div>
                                    <?php if (!empty($task['note'])): ?>
                                    <div class="text-sm text-slate-600 mt-1 italic"><?php echo htmlspecialchars($task['note']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Noon -->
                <?php if (!empty($schedules['daily']['noon'])): ?>
                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 p-6 rounded-lg shadow-lg">
                    <div class="flex items-center mb-4">
                        <span class="text-3xl mr-3">☀️</span>
                        <h3 class="text-xl font-semibold text-slate-700">Noon</h3>
                    </div>
                    <ul class="space-y-3">
                        <?php foreach ($schedules['daily']['noon'] as $index => $task): ?>
                        <li class="bg-white p-3 rounded-md shadow-sm">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="font-medium text-slate-800"><?php echo htmlspecialchars($task['task']); ?></div>
                                    <?php if (!empty($task['note'])): ?>
                                    <div class="text-sm text-slate-600 mt-1 italic"><?php echo htmlspecialchars($task['note']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Night -->
                <?php if (!empty($schedules['daily']['night'])): ?>
                <div class="bg-gradient-to-br from-purple-50 to-indigo-50 p-6 rounded-lg shadow-lg">
                    <div class="flex items-center mb-4">
                        <span class="text-3xl mr-3">🌙</span>
                        <h3 class="text-xl font-semibold text-slate-700">Night</h3>
                    </div>
                    <ul class="space-y-3">
                        <?php foreach ($schedules['daily']['night'] as $index => $task): ?>
                        <li class="bg-white p-3 rounded-md shadow-sm">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="font-medium text-slate-800"><?php echo htmlspecialchars($task['task']); ?></div>
                                    <?php if (!empty($task['note'])): ?>
                                    <div class="text-sm text-slate-600 mt-1 italic"><?php echo htmlspecialchars($task['note']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Weekly Schedule Section -->
        <?php if (isset($schedules['weekly']) && !empty($schedules['weekly'])): ?>
        <div class="mb-8 schedule-section">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <h2 class="text-2xl lg:text-3xl font-bold text-slate-600">📅 Weekly Schedule</h2>
                <div class="flex gap-2">
                    <a href="schedule.php" class="text-sm bg-second px-3 py-1 rounded-full">
                        Edit
                    </a>
                    <button onclick="clearScheduleType('weekly')" class="text-sm bg-red-400 px-3 py-1 rounded-full hover:bg-red-200">
                        Clear All
                    </button>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="grid gap-4">
                    <?php foreach ($schedules['weekly'] as $index => $task): ?>
                    <div class="border border-slate-200 p-4 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-slate-800"><?php echo htmlspecialchars($task['task']); ?></h4>
                                <p class="text-sm text-slate-600 mt-1">
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">
                                        <?php echo htmlspecialchars($task['frequencyText']); ?>
                                    </span>
                                </p>
                                <?php if (!empty($task['note'])): ?>
                                <p class="text-sm text-slate-600 mt-2 italic"><?php echo htmlspecialchars($task['note']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">📋</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Monthly Schedule Section -->
        <?php if (isset($schedules['monthly']) && !empty($schedules['monthly'])): ?>
        <div class="mb-8 schedule-section">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <h2 class="text-2xl lg:text-3xl font-bold text-slate-600">📅 Monthly Schedule</h2>
                <div class="flex gap-2">
                    <a href="schedule.php" class="text-sm bg-second px-3 py-1 rounded-full">
                        Edit
                    </a>
                    <button onclick="clearScheduleType('monthly')" class="text-sm bg-red-400 px-3 py-1 rounded-full hover:bg-red-200">
                        Clear All
                    </button>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="grid gap-4">
                    <?php foreach ($schedules['monthly'] as $index => $task): ?>
                    <div class=" border border-slate-200 p-4 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-slate-800"><?php echo htmlspecialchars($task['task']); ?></h4>
                                <p class="text-sm text-slate-600 mt-1">
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">
                                        <?php echo htmlspecialchars($task['frequencyText']); ?>
                                    </span>
                                </p>
                                <?php if (!empty($task['note'])): ?>
                                <p class="text-sm text-slate-600 mt-2 italic"><?php echo htmlspecialchars($task['note']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">📋</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- No Schedule Message -->
        <?php if (empty($schedules)): ?>
        <div class="text-center py-12">
            <div class="text-6xl mb-4">📋</div>
            <h3 class="text-2xl font-semibold text-slate-600 mb-2">No Schedule Found</h3>
            <p class="text-slate-500 mb-6">You haven't created any schedules yet. Let's get started!</p>
            <a href="schedule.php" class="inline-block border border-slate-800 rounded-3xl px-8 py-3 bg-second text-slate-700 hover:font-semibold transition-all">
                Create Your First Schedule
            </a>
        </div>
        <?php endif; ?>

        <!-- Today's Overview -->
        <?php if (!empty($schedules)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Quick Actions -->
            <div>
                <h4 class="text-lg font-semibold text-slate-700 mb-3">Quick Actions</h4>
                <div class="space-y-2">
                    <a href="schedule.php" class="flex items-center p-3 rounded-md bg-white hover:bg-[#e6aab4] transition-colors">
                        <span class="text-2xl mr-2">✏️</span>
                        <span class="font-medium text-slate-700">Edit Schedule</span>
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Back to Top Button -->
    <div class="w-10 h-10 bg-second rounded-full flex fixed bottom-5 right-5 cursor-pointer z-30" onclick="scrollToTop()">
        <span class="text-xl m-auto">↑</span>
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
            mobileMenuBtn.classList.add('hidden'); // Hide menu button when sidebar is open
            document.body.classList.add('overflow-hidden');
        }

        function closeSidebarFunc() {
            mobileSidebar.classList.remove('open');
            overlay.classList.add('hidden');
            mobileMenuBtn.classList.remove('hidden'); // Show menu button when sidebar is closed
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

        // Delete individual schedule item
        function deleteScheduleItem(scheduleType, itemIndex, timeOfDay = null, dateKey = null) {
            if (!confirm('Are you sure you want to delete this item?')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'delete_schedule_item');
            formData.append('schedule_type', scheduleType);
            formData.append('item_index', itemIndex);
            
            if (timeOfDay) {
                formData.append('time_of_day', timeOfDay);
            }

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') 
                {
                    } else {
                        alert('Error: ' + data.message);
                    }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting schedule item!');
            });
        }

        // Clear entire schedule type
        function clearScheduleType(scheduleType) {
            if (!confirm(`Are you sure you want to clear all ${scheduleType} schedules? This action cannot be undone.`)) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'clear_schedule_type');
            formData.append('schedule_type', scheduleType);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error clearing schedule!');
            });
        }

        // Scroll to top function
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    </script>
</body>
</html>