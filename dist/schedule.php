<?php
session_start(); 

require_once 'koneksi.php';

if (!isset($_SESSION['user_login'])) {
    header('Location: login.php');
    exit();
}

$logged_in_username = $_SESSION['user_login'];

// Gunakan fungsi getPDOConnection()
try {
    $pdo = getPDOConnection();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
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
    // Handle error silently or log it
}

// Handle AJAX requests for saving schedule
if (isset($_POST['action']) && $_POST['action'] == 'save_schedule') {
    $schedule_data = json_decode($_POST['schedule_data'], true);
    $username = $_SESSION['user_login'];
    
    try {
        // Delete existing schedules for this user
        $stmt = $pdo->prepare("DELETE FROM user_schedules WHERE username = ?");
        $stmt->execute([$username]);
        
        // Insert new schedule data
        foreach ($schedule_data as $type => $data) {
            if (!empty($data)) {
                $stmt = $pdo->prepare("INSERT INTO user_schedules (username, schedule_type, schedule_data, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$username, $type, json_encode($data)]);
            }
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Schedule saved successfully!']);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error saving schedule: ' . $e->getMessage()]);
    }
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./style.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Schedule</title>
</head>
<body class="m-6 bg-first">

  <div class="flex flex-col justify-center items-center mb-8">
    <h4 class="font-logo text-6xl font-bold text-slate-600 lg:mb-2 mb-5 text-center">FleurSkin <a class="text-slate-500">Schedule</a></h4>
    <p class="text-lg text-slate-500 mb-4">Schedule your glow-up time!</p>
  </div>

  <!-- Username -->
  <div class="text-center text-xl font-semibold text-slate-700">
    Halooooo <?php echo htmlspecialchars($logged_in_username); ?>!
  </div>

  <!-- Dropdown Section -->
  <div class="relative inline-block text-left mt-5 mb-6">
    <button onclick="toggleDropdown()" type="button" class="inline-flex justify-between w-56 rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-slate-50">
      <span id="dropdownText">Choose the time</span>
      <svg class="-mr-1 ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>
  
    <div id="dropdownMenu" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden z-10">
      <div class="py-1">
        <a href="#" onclick="showSection('daily')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-second hover:text-gray-900">Daily</a>
        <a href="#" onclick="showSection('weekly')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-second hover:text-gray-900">Weekly</a>
        <a href="#" onclick="showSection('monthly')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-second hover:text-gray-900">Monthly</a>
      </div>
    </div>
  </div>

  <!-- Daily Schedule -->
  <div id="daily" class="schedule-section hidden">
    <h2 class="text-2xl font-bold text-slate-500 mb-6">Daily Schedule</h2>
    
    <!-- Morning -->
    <div class="mb-6 bg-white p-4 rounded-lg shadow-md">
      <div class="flex items-center mb-4">
        <span class="text-2xl mr-2">🌅</span>
        <h3 class="text-lg font-semibold text-slate-700">Morning</h3>
      </div>
      
      <div class="flex gap-2 mb-4">
        <input type="text" id="morningInput" placeholder="Add morning activity..." 
          class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-300"/>
        <button onclick="addTask('morningTasks', 'morningInput')" 
          class="bg-second text-slate-600 px-4 py-2 rounded-md hover:bg-rose-200 hover:font-bold">+</button>
      </div>
      
      <ul id="morningTasks" class="space-y-2"></ul>
    </div>

    <!-- Noon -->
    <div class="mb-6 bg-white p-4 rounded-lg shadow-md">
      <div class="flex items-center mb-4">
        <span class="text-2xl mr-2">☀️</span>
        <h3 class="text-lg font-semibold text-slate-700">Noon</h3>
      </div>
      
      <div class="flex gap-2 mb-4">
        <input type="text" id="noonInput" placeholder="Add noon activity..." 
          class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-300"/>
        <button onclick="addTask('noonTasks', 'noonInput')" 
          class="bg-second text-slate-600 px-4 py-2 rounded-md hover:bg-rose-200 hover:font-bold">+</button>
      </div>
      
      <ul id="noonTasks" class="space-y-2"></ul>
    </div>

    <!-- Night -->
    <div class="mb-6 bg-white p-4 rounded-lg shadow-md">
      <div class="flex items-center mb-4">
        <span class="text-2xl mr-2">🌙</span>
        <h3 class="text-lg font-semibold text-slate-700">Night</h3>
      </div>
      
      <div class="flex gap-2 mb-4">
        <input type="text" id="nightInput" placeholder="Add night activity..." 
          class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-300"/>
        <button onclick="addTask('nightTasks', 'nightInput')" 
          class="bg-second text-slate-600 px-4 py-2 rounded-md hover:bg-rose-200 hover:font-bold">+</button>
      </div>
      
      <ul id="nightTasks" class="space-y-2"></ul>
    </div>
  </div>

  <!-- Weekly Schedule -->
  <div id="weekly" class="schedule-section hidden">
    <h2 class="text-2xl font-bold text-slate-600 mb-6">Weekly Schedule</h2>
      <!--  Weekly Frequency Selection -->
      <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h3 class="text-lg font-semibold mb-4">Select Frequency</h3>
        <div class="flex gap-4 mb-4">
          <label class="flex items-center">
            <input type="radio" name="weeklyFreq" value="1" checked class="mr-2">
            <span>Every Week</span>
          </label>
          <label class="flex items-center">
            <input type="radio" name="weeklyFreq" value="2" class="mr-2">
            <span>Every 2 Weeks</span>
          </label>
          <label class="flex items-center">
            <input type="radio" name="weeklyFreq" value="3" class="mr-2">
            <span>Every 3 Weeks</span>
          </label>
        </div>
      </div>
      <!-- Weekly Tasks -->
      <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center mb-4">
          <span class="text-2xl mr-2">📅</span>
          <h3 class="text-lg font-semibold text-slate-700">Weekly Tasks</h3>
        </div>
        
        <div class="flex gap-2 mb-4">
          <input type="text" id="weeklyInput" placeholder="Add weekly recurring task..." 
            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-300"/>
          <button onclick="addWeeklyTask()" 
            class="bg-second text-slate-600 px-4 py-2 rounded-md hover:bg-rose-200 hover:font-bold">+</button>
        </div>
        
        <ul id="weeklyTasks" class="space-y-2"></ul>
      </div>
  </div>

  <!-- Monthly Schedule -->
  <div id="monthly" class="schedule-section hidden">
    <h2 class="text-2xl font-bold text-slate-600 mb-6">Monthly Schedule</h2>
    
    <!-- Monthly Frequency Selection -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
      <h3 class="text-lg font-semibold mb-4">Select Frequency</h3>
      <div class="flex gap-4 mb-4">
        <label class="flex items-center">
          <input type="radio" name="monthlyFreq" value="1" checked class="mr-2">
          <span>Every Month</span>
        </label>
        <label class="flex items-center">
          <input type="radio" name="monthlyFreq" value="2" class="mr-2">
          <span>Every 2 Months</span>
        </label>
        <label class="flex items-center">
          <input type="radio" name="monthlyFreq" value="3" class="mr-2">
          <span>Every 3 Months</span>
        </label>
      </div>
    </div>

    <!-- Monthly Tasks -->
    <div class="bg-white p-6 rounded-lg shadow-md">
      <div class="flex items-center mb-4">
        <span class="text-2xl mr-2">📅</span>
        <h3 class="text-lg font-semibold text-slate-700">Monthly Tasks</h3>
      </div>
      
      <div class="flex gap-2 mb-4">
        <input type="text" id="monthlyInput" placeholder="Add monthly recurring task..." 
          class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-300"/>
        <button onclick="addMonthlyTask()" 
          class="bg-second text-slate-600 px-4 py-2 rounded-md hover:bg-rose-200 hover:font-bold">+</button>
      </div>
      
      <ul id="monthlyTasks" class="space-y-2"></ul>
    </div>
  </div>

  <!-- Finish Button -->
  <div class="text-right mt-10">
    <button onclick="saveSchedule()" class="border border-slate-800 rounded-3xl px-5 py-2 bg-second text-slate-700 hover:font-semibold mr-2">Save</button>
    <a href="homepage.php" class="border border-slate-800 rounded-3xl px-5 py-2 bg-second text-slate-700 hover:font-semibold">Finish</a>
  </div>

  <script>
    // Initialize data structure with existing schedules from PHP
    let existingSchedules = <?php echo json_encode($existing_schedules); ?>;
    
    let dailyTasks = {
      morning: existingSchedules.daily?.morning || [],
      noon: existingSchedules.daily?.noon || [],
      night: existingSchedules.daily?.night || []
    };
    let monthlyTasks = existingSchedules.monthly || [];
    let weeklyTasks = existingSchedules.weekly || [];

    // Initialize display when page loads
    document.addEventListener('DOMContentLoaded', function() {
      loadExistingSchedules();
      showSection('daily');
    });
    
    function loadExistingSchedules() {
      // Load daily tasks
      loadDailyTasks();
      
      // Load weekly tasks
      loadWeeklyTasks();
      
      // Load monthly tasks
      loadMonthlyTasks();
    }
    
    function loadDailyTasks() {
      // Load morning tasks
      const morningList = document.getElementById('morningTasks');
      morningList.innerHTML = '';
      dailyTasks.morning.forEach((task, index) => {
        addExistingTask('morningTasks', 'morning', task, index);
      });
      
      // Load noon tasks
      const noonList = document.getElementById('noonTasks');
      noonList.innerHTML = '';
      dailyTasks.noon.forEach((task, index) => {
        addExistingTask('noonTasks', 'noon', task, index);
      });
      
      // Load night tasks
      const nightList = document.getElementById('nightTasks');
      nightList.innerHTML = '';
      dailyTasks.night.forEach((task, index) => {
        addExistingTask('nightTasks', 'night', task, index);
      });
    }
    
    function loadWeeklyTasks() {
      const weeklyList = document.getElementById('weeklyTasks');
      weeklyList.innerHTML = '';
      weeklyTasks.forEach((task, index) => {
        addExistingWeeklyTask(task, index);
      });
    }
    
    function loadMonthlyTasks() {
      const monthlyList = document.getElementById('monthlyTasks');
      monthlyList.innerHTML = '';
      monthlyTasks.forEach((task, index) => {
        addExistingMonthlyTask(task, index);
      });
    }
    
    function addExistingTask(listId, timeOfDay, task, index) {
      const ul = document.getElementById(listId);
      const li = document.createElement('li');
      li.className = 'flex flex-col bg-gray-50 px-3 py-2 rounded-md shadow-sm';
      li.setAttribute('data-task-index', index);

      li.innerHTML = `
        <div class="flex justify-between items-center">
          <span class="flex-1">${task.task}</span>
          <div class="flex gap-2 ml-2">
            <button onclick="toggleNote(this)" class="text-slate-500 hover:text-slate-700 text-sm px-2">Note</button>
            <button onclick="removeTask(this, '${timeOfDay}')" class="text-red-500 hover:text-red-700 text-sm px-2">Delete</button>
          </div>
        </div>
        <div class="note-container ${task.note ? '' : 'hidden'} mt-2">
          <textarea placeholder="Add notes here..." class="w-full px-2 py-1 border border-slate-300 rounded-md text-sm" onchange="updateTaskNote(this, '${timeOfDay}')">${task.note || ''}</textarea>
        </div>
      `;

      ul.appendChild(li);
    }
    
    function addExistingWeeklyTask(task, index) {
      const ul = document.getElementById('weeklyTasks');
      const li = document.createElement('li');
      li.className = 'flex flex-col bg-gray-50 px-3 py-2 rounded-md shadow-sm';
      li.setAttribute('data-task-index', index);

      li.innerHTML = `
        <div class="flex justify-between items-center">
          <div class="flex-1">
            <span class="font-medium">${task.task}</span>
            <span class="text-sm text-slate-500 ml-2">(${task.frequencyText})</span>
          </div>
          <div class="flex gap-2 ml-2">
            <button onclick="toggleNote(this)" class="text-slate-500 hover:text-slate-700 text-sm px-2">Note</button>
            <button onclick="removeWeeklyTask(this)" class="text-red-500 hover:text-red-700 text-sm px-2">Delete</button>
          </div>
        </div>
        <div class="note-container ${task.note ? '' : 'hidden'} mt-2">
          <textarea placeholder="Add notes here..." class="w-full px-2 py-1 border border-slate-300 rounded-md text-sm" onchange="updateWeeklyTaskNote(this)">${task.note || ''}</textarea>
        </div>
      `;

      ul.appendChild(li);
    }
    
    function addExistingMonthlyTask(task, index) {
      const ul = document.getElementById('monthlyTasks');
      const li = document.createElement('li');
      li.className = 'flex flex-col bg-gray-50 px-3 py-2 rounded-md shadow-sm';
      li.setAttribute('data-task-index', index);

      li.innerHTML = `
        <div class="flex justify-between items-center">
          <div class="flex-1">
            <span class="font-medium">${task.task}</span>
            <span class="text-sm text-slate-500 ml-2">(${task.frequencyText})</span>
          </div>
          <div class="flex gap-2 ml-2">
            <button onclick="toggleNote(this)" class="text-slate-500 hover:text-slate-700 text-sm px-2">Note</button>
            <button onclick="removeMonthlyTask(this)" class="text-red-500 hover:text-red-700 text-sm px-2">Delete</button>
          </div>
        </div>
        <div class="note-container ${task.note ? '' : 'hidden'} mt-2">
          <textarea placeholder="Add notes here..." class="w-full px-2 py-1 border border-slate-300 rounded-md text-sm" onchange="updateMonthlyTaskNote(this)">${task.note || ''}</textarea>
        </div>
      `;

      ul.appendChild(li);
    }
    
    function toggleDropdown() {
      const menu = document.getElementById('dropdownMenu');
      menu.classList.toggle('hidden');
    }

    function showSection(section) {
      // Hide all sections
      document.querySelectorAll('.schedule-section').forEach(el => el.classList.add('hidden'));
      
      // Show selected section
      document.getElementById(section).classList.remove('hidden');
      
      // Update dropdown text
      const text = section.charAt(0).toUpperCase() + section.slice(1);
      document.getElementById('dropdownText').textContent = text;
      
      // Hide dropdown
      document.getElementById('dropdownMenu').classList.add('hidden');
    }

    function addTask(listId, inputId) {
      const input = document.getElementById(inputId);
      const taskName = input.value.trim();
      
      if (taskName === "") {
        alert("Please enter a task!");
        return;
      }

      // Store in memory
      let timeOfDay = '';
      if (listId === 'morningTasks') timeOfDay = 'morning';
      else if (listId === 'noonTasks') timeOfDay = 'noon';
      else if (listId === 'nightTasks') timeOfDay = 'night';
      
      dailyTasks[timeOfDay].push({
        task: taskName,
        note: ''
      });

      const ul = document.getElementById(listId);
      const li = document.createElement('li');
      li.className = 'flex flex-col bg-gray-50 px-3 py-2 rounded-md shadow-sm';
      li.setAttribute('data-task-index', dailyTasks[timeOfDay].length - 1);

      li.innerHTML = `
        <div class="flex justify-between items-center">
          <span class="flex-1">${taskName}</span>
          <div class="flex gap-2 ml-2">
            <button onclick="toggleNote(this)" class="text-slate-500 hover:text-slate-700 text-sm px-2">Note</button>
            <button onclick="removeTask(this, '${timeOfDay}')" class="text-red-500 hover:text-red-700 text-sm px-2">Delete</button>
          </div>
        </div>
        <div class="note-container hidden mt-2">
          <textarea placeholder="Add notes here..." class="w-full px-2 py-1 border border-slate-300 rounded-md text-sm" onchange="updateTaskNote(this, '${timeOfDay}')"></textarea>
        </div>
      `;

      ul.appendChild(li);
      input.value = "";
    }

    function removeTask(button, timeOfDay) {
      const li = button.closest('li');
      const taskIndex = parseInt(li.getAttribute('data-task-index'));
      
      // Remove from memory
      dailyTasks[timeOfDay].splice(taskIndex, 1);
      
      // Remove from DOM
      li.remove();
      
      // Update indices for remaining tasks
      const ul = li.parentElement;
      const remainingTasks = ul.querySelectorAll('li');
      remainingTasks.forEach((task, index) => {
        task.setAttribute('data-task-index', index);
      });
    }

    function updateTaskNote(textarea, timeOfDay) {
      const li = textarea.closest('li');
      const taskIndex = parseInt(li.getAttribute('data-task-index'));
      dailyTasks[timeOfDay][taskIndex].note = textarea.value;
    }

    function addMonthlyTask() {
      const input = document.getElementById('monthlyInput');
      const taskName = input.value.trim();
      
      if (taskName === "") {
        alert("Please enter a task!");
        return;
      }

      // Get selected frequency
      const selectedFreq = document.querySelector('input[name="monthlyFreq"]:checked').value;
      let frequencyText = "";
      
      switch(selectedFreq) {
        case "1":
          frequencyText = "Every month";
          break;
        case "2":
          frequencyText = "Every 2 months";
          break;
        case "3":
          frequencyText = "Every 3 months";
          break;
      }

      // Store in memory
      monthlyTasks.push({
        task: taskName,
        frequency: selectedFreq,
        frequencyText: frequencyText,
        note: ''
      });

      const ul = document.getElementById('monthlyTasks');
      const li = document.createElement('li');
      li.className = 'flex flex-col bg-gray-50 px-3 py-2 rounded-md shadow-sm';
      li.setAttribute('data-task-index', monthlyTasks.length - 1);

      li.innerHTML = `
        <div class="flex justify-between items-center">
          <div class="flex-1">
            <span class="font-medium">${taskName}</span>
            <span class="text-sm text-slate-500 ml-2">(${frequencyText})</span>
          </div>
          <div class="flex gap-2 ml-2">
            <input type="checkbox">
            <button onclick="toggleNote(this)" class="text-slate-500 hover:text-slate-700 text-sm px-2">Note</button>
            <button onclick="removeMonthlyTask(this)" class="text-red-500 hover:text-red-700 text-sm px-2">Delete</button>
          </div>
        </div>
        <div class="note-container hidden mt-2">
          <textarea placeholder="Add notes here..." class="w-full px-2 py-1 border border-slate-300 rounded-md text-sm" onchange="updateMonthlyTaskNote(this)"></textarea>
        </div>
      `;

      ul.appendChild(li);
      input.value = "";
    }

    function removeMonthlyTask(button) {
      const li = button.closest('li');
      const taskIndex = parseInt(li.getAttribute('data-task-index'));
      
      // Remove from memory
      monthlyTasks.splice(taskIndex, 1);
      
      // Remove from DOM
      li.remove();
      
      // Update indices for remaining tasks
      const ul = li.parentElement;
      const remainingTasks = ul.querySelectorAll('li');
      remainingTasks.forEach((task, index) => {
        task.setAttribute('data-task-index', index);
      });
    }

    function updateMonthlyTaskNote(textarea) {
      const li = textarea.closest('li');
      const taskIndex = parseInt(li.getAttribute('data-task-index'));
      monthlyTasks[taskIndex].note = textarea.value;
    }

    function toggleNote(button) {
      const li = button.closest('li');
      const noteContainer = li.querySelector('.note-container');
      
      if (noteContainer.classList.contains('hidden')) {
        noteContainer.classList.remove('hidden');
        button.textContent = "Hide Note";
      } else {
        noteContainer.classList.add('hidden');
        button.textContent = "Note";
      }
    }

    function saveSchedule() {
      const scheduleData = {
        daily: dailyTasks,
        weekly: weeklyTasks,
        monthly: monthlyTasks
      };

      // Send data to server
      fetch(window.location.href, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=save_schedule&schedule_data=' + encodeURIComponent(JSON.stringify(scheduleData))
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          alert(data.message + " 🎉");
        } else {
          alert("Error: " + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert("Error saving schedule!");
      });
    }

    //Weekly Script
    function addWeeklyTask() {
      const input = document.getElementById('weeklyInput');
      const taskName = input.value.trim();
      
      if (taskName === "") {
        alert("Please enter a task!");
        return;
      }

      // Get selected frequency
      const selectedFreq = document.querySelector('input[name="weeklyFreq"]:checked').value;
      let frequencyText = "";
      
      switch(selectedFreq) {
        case "1":
          frequencyText = "Every week";
          break;
        case "2":
          frequencyText = "Every 2 weeks";
          break;
        case "3":
          frequencyText = "Every 3 weeks";
          break;
      }

      // Store in memory
      weeklyTasks.push({
        task: taskName,
        frequency: selectedFreq,
        frequencyText: frequencyText,
        note: ''
      });

      const ul = document.getElementById('weeklyTasks');
      const li = document.createElement('li');
      li.className = 'flex flex-col bg-gray-50 px-3 py-2 rounded-md shadow-sm';
      li.setAttribute('data-task-index', weeklyTasks.length - 1);

      li.innerHTML = `
        <div class="flex justify-between items-center">
          <div class="flex-1">
            <span class="font-medium">${taskName}</span>
            <span class="text-sm text-slate-500 ml-2">(${frequencyText})</span>
          </div>
          <div class="flex gap-2 ml-2">
            <input type="checkbox">
            <button onclick="toggleNote(this)" class="text-slate-500 hover:text-slate-700 text-sm px-2">Note</button>
            <button onclick="removeWeeklyTask(this)" class="text-red-500 hover:text-red-700 text-sm px-2">Delete</button>
          </div>
        </div>
        <div class="note-container hidden mt-2">
          <textarea placeholder="Add notes here..." class="w-full px-2 py-1 border border-slate-300 rounded-md text-sm" onchange="updateWeeklyTaskNote(this)"></textarea>
        </div>
      `;

      ul.appendChild(li);
      input.value = "";
    }

    function removeWeeklyTask(button) {
      const li = button.closest('li');
      const taskIndex = parseInt(li.getAttribute('data-task-index'));
      
      // Remove from memory
      weeklyTasks.splice(taskIndex, 1);
      
      // Remove from DOM
      li.remove();
      
      // Update indices for remaining tasks
      const ul = li.parentElement;
      const remainingTasks = ul.querySelectorAll('li');
      remainingTasks.forEach((task, index) => {
        task.setAttribute('data-task-index', index);
      });
    }

    function updateWeeklyTaskNote(textarea) {
      const li = textarea.closest('li');
      const taskIndex = parseInt(li.getAttribute('data-task-index'));
      weeklyTasks[taskIndex].note = textarea.value;
  }

  </script>

</body>
</html>