<?php

// Database connection
$conn = new mysqli("localhost", "root", "", "fleurskin");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql_select = "SELECT * FROM skin_diary";
$result = $conn->query($sql_select);

?>

<!DOCTYPE html>
<html class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./style.css" rel="stylesheet">
    <title>Diary - FleurSkin</title>
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

                <a href="tracker.php" class="flex items-center px-4 py-3 rounded-md hover:bg-first text-slate-700">
                    <span class="material-symbols-outlined text-2xl mr-3">content_paste_search</span>
                    Beauty Tracker
                </a>

                <a href="diary.php" class="flex items-center px-4 py-3 rounded-md bg-first text-slate-700">
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

            <a href="tracker.php" class="flex items-center px-4 py-2 rounded-md hover:bg-first">
                <span class="material-symbols-outlined text-2xl mr-3">content_paste_search</span>
                Beauty Tracker
            </a>

            <a href="diary.php" class="flex items-center px-4 py-2 rounded-md bg-first">
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

        
         <!-- Diary-->
         <div class="lg:mt-8 mt-4 mb-10 p-4 min-h-screen bg-white rounded-lg shadow-md">

            <div class="flex justify-between">
                <h2 class="text-slate-900 font-medium text-2xl p-4">A Glimpse Within</h2>
            </div>
            
            <?php if ($result && $result->num_rows > 0): ?>
            <div class="flex flex-wrap justify-center mx-auto gap-5 rounded-lg">

                <!-- Add Diary Message -->
                    <div class="bg-gray-100 rounded-lg shadow-lg flex items-center justify-center p-2 cursor-pointer hover:scale-95 transition-all duration-500">
                        <a href="diaryNote.php">
                            <img src="https://img.icons8.com/?size=90&id=104152&format=png&color=faa4bd" 
                                     class="w-30 h-30" alt="Add">
                        </a>            
                    </div>

                <?php while ($row = $result->fetch_assoc()): ?>
                    <a href="edit_diary.php?id=<?php echo htmlspecialchars($row['id']); ?>">
                        <div class="bg-white rounded-lg shadow-lg cursor-pointer hover:scale-95 transition-all duration-500">
                        <!-- Image -->
                        <div class="bg-gray-100 flex items-center justify-center p-2">
                                <img src="https://img.icons8.com/?size=100&id=KHCQpouEN7kb&format=png&color=000000" 
                                     class="w-20 h-20 rounded-md" alt="Paper Image">
                        </div>
                        <!-- Diary Details -->
                        <div class="p-4">
                            <div class="flex justify-between items-center">
                                <!-- Date Added -->
                                <p class="text-slate-500 text-xs mr-5 mb-1">
                                    <?php echo date('M j, Y', strtotime($row['date_created'])); ?>
                                </p>

                                <!-- Action Buttons -->
                                 <div class="flex space-x-1">
                                    <a href="delete_diary.php?id=<?php echo htmlspecialchars($row['id']); ?>" onclick="return confirm('Yakin ingin menghapus?')">
                                        <span class="material-symbols-outlined text-[17px] text-red-600 hover:text-red-800">delete</span>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                    </a>

                <?php endwhile; ?>
                    
            </div>
            <?php else: ?>
            <!-- No Diary Message -->
            <div class="text-center py-12">
                <div class="mb-4">
                    <span class="material-symbols-outlined text-6xl text-slate-300">inventory_2</span>
                </div>
                <h3 class="text-2xl font-semibold text-slate-600 mb-2">This page is lonely...</h3>
                <p class="text-slate-500 mb-6">write something while masking</p>
                <a href="diaryNote.php" class="inline-block border border-slate-800 rounded-3xl px-8 py-3 bg-second text-slate-700 hover:font-semibold transition-all">
                    Add Your First Diary
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

    </script>

    <?php $conn->close(); ?>
</body>
</html>