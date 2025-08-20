<?php

require_once 'koneksi.php';
$conn = getConnection();

$sql_select = "SELECT * FROM product_inventory";
$result = $conn->query($sql_select);

$sql_select_wishlist = "SELECT * FROM wishlist_product";
$wishlist = $conn->query($sql_select_wishlist);

?>

<!DOCTYPE html>
<html class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./style.css" rel="stylesheet">
    <title>Products - FleurSkin</title>
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

                <a href="products.php" class="flex items-center px-4 py-3 rounded-md bg-first text-slate-700">
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
            <a href="homepage.php" class="flex items-center px-4 py-2 rounded-md hover:bg-first">
                <span class="material-symbols-outlined text-2xl mr-3">home</span>
                Home
            </a>

            <a href="schedule.php" class="flex items-center px-4 py-2 rounded-md hover:bg-first">
                <span class="material-symbols-outlined text-2xl mr-3">event_list</span>
                Schedule
            </a>

            <a href="products.php" class="flex items-center px-4 py-2 rounded-md bg-first">
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
    <div class="lg:ml-64 min-h-screen p-4 pt-16 lg:pt-4">

       <div class="flex flex-wrap gap-4">
            <a href="#beautyCollection" class="bg-rose-100 rounded-3xl cursor-pointer hover:scale-95 transition-all duration-500">
                <div class="flex items-center gap-3 mx-2 p-2">
                    <img width="25" height="25" src="https://img.icons8.com/external-rabit-jes-detailed-outline-rabit-jes/62/external-shelf-home-decoration-rabit-jes-detailed-outline-rabit-jes-2.png" alt="Shelf Icon"/>
                    <span class="text-slate-900 text-[15px] font-medium">Beauty Collection</span>
                </div>
            </a>
            <a href="#wishlist" class="bg-rose-100 rounded-3xl cursor-pointer hover:scale-95 transition-all duration-500">
                <div class="flex items-center gap-3 mx-2 p-2">
                    <img width="25" height="25" src="https://img.icons8.com/?size=100&id=trcGXUzGmZQM&format=png&color=000000" alt="Shelf Icon"/>
                    <span class="text-slate-900 text-[15px] font-medium">My Product Cravings</span>
                </div>
            </a>
        </div>

        
         <!-- Product Inventory-->
         <div id="beautyCollection" class="lg:mt-8 mt-4 mb-10 p-4 bg-white rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-4">
                <p class="text-xl font-semibold text-slate-700">Beauty Collection</p>
                <a href="writeProduct.php" class="inline-block border border-slate-800 rounded-3xl px-4 py-2 bg-second text-slate-700 hover:font-semibold transition-all text-sm">
                    Add Product
                </a>
            </div>
            
            <?php if ($result && $result->num_rows > 0): ?>
            <div class="table-container rounded-lg border border-slate-200">
                <table class="mobile-table min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Product Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Brand</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Image</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Expiration Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Usage Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <?php
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr class='hover:bg-slate-50'>";
                            echo "<td class='px-4 py-3 text-sm text-slate-900'>" . htmlspecialchars($row['product_name']) . "</td>";
                            echo "<td class='px-4 py-3 text-sm text-slate-600'>" . htmlspecialchars($row['brand']) . "</td>";
                            echo "<td class='px-4 py-3 text-sm text-slate-600'>";
                                if (!empty($row['image'])) {
                                    echo '<img src="uploads/' . htmlspecialchars($row['image']) . '" class="w-10 h-10 object-cover rounded-md" alt="Product">';
                                } else {
                                    echo "<span class='text-slate-400 text-xs'>No picture</span>";
                                }
                            echo "</td>";
                            echo "<td class='px-4 py-3 text-sm text-slate-600'>" . htmlspecialchars($row['type']) . "</td>";
                            echo "<td class='px-4 py-3 text-sm text-slate-600'>" . htmlspecialchars($row['expiration_date']) . "</td>";
                            
                            // Status with color coding
                            $status = htmlspecialchars($row['status']);
                            $statusClass = '';
                            switch(strtolower($status)) {
                                case 'new':
                                case 'unused':
                                    $statusClass = 'bg-green-100 text-green-800';
                                    break;
                                case 'used':
                                case 'in use':
                                    $statusClass = 'bg-blue-100 text-blue-800';
                                    break;
                                case 'expired':
                                    $statusClass = 'bg-red-100 text-red-800';
                                    break;
                                default:
                                    $statusClass = 'bg-gray-100 text-gray-800';
                            }
                            echo "<td class='px-4 py-3'><span class='px-2 py-1 text-xs font-medium rounded-full {$statusClass}'>" . $status . "</span></td>";
                            
                            echo "<td class='px-4 py-3 text-sm'>
                                <div class='flex space-x-1'>
                                    <a href='edit_product.php?id=" . htmlspecialchars($row['id']) . "' class='button text-blue-600 hover:text-blue-800' title='Edit'>
                                        <span class='material-symbols-outlined text-lg'>edit</span>
                                    </a>
                                    <a href='delete_product.php?id=" . htmlspecialchars($row['id']) . "' class='button text-red-600 hover:text-red-800' title='Delete' onclick='return confirm(\"Are you sure you want to delete this product?\")'>
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
                <h3 class="text-2xl font-semibold text-slate-600 mb-2">No Products Found</h3>
                <p class="text-slate-500 mb-6">You haven't added any products yet. Let's get started!</p>
                <a href="writeProduct.php" class="inline-block border border-slate-800 rounded-3xl px-8 py-3 bg-second text-slate-700 hover:font-semibold transition-all">
                    Add Your First Product
                </a>
            </div>
            <?php endif; ?>
         </div>

         <!-- Wishlist Product -->
         <div id="wishlist" class="mt-8 mb-10">
            <div class="flex justify-between items-center mb-4">
                <p class="text-xl font-semibold text-slate-700">My Product Cravings</p>
                <a href="wishlistProduct.php" class="inline-block border border-slate-800 rounded-3xl px-4 py-2 bg-second text-slate-700 hover:font-semibold transition-all text-sm">
                    Want This!
                </a>
            </div>
            <?php if ($wishlist && $wishlist->num_rows > 0): ?>
            <div class="flex flex-wrap justify-center mx-auto gap-5">
                <?php while ($row = $wishlist->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-lg">
                        <!-- Product Image -->
                        <div class="bg-gray-100 flex items-center justify-center p-2">
                            <?php if (!empty($row['image'])): ?>
                                <img src="uploads2/<?php echo htmlspecialchars($row['image']); ?>" 
                                     class="w-20 h-20 rounded-md" alt="Product Image">
                            <?php else: ?>
                                <span class="text-slate-400 text-sm">No Image</span>
                            <?php endif; ?>
                        </div>

                        <!-- Product Details -->
                        <div class="p-4">
                            <h3 class="font-bold text-lg mb-2 text-slate-700">
                                <?php echo htmlspecialchars($row['product_name']); ?>
                            </h3>

                            <!-- Priority Badge -->
                            <div class="flex justify-between">
                                <p class="text-slate-600 text-sm mt-1">
                                    Brand: <?php echo htmlspecialchars($row['brand']); ?>
                                </p>

                                <?php
                                $priority_colors = [
                                    'Just Curious' => 'bg-blue-100 text-blue-800',
                                    'Worth Considering' => 'bg-yellow-100 text-yellow-800', 
                                    'Really Want' => 'bg-orange-100 text-orange-800',
                                    'Must have!' => 'bg-red-100 text-red-800'
                                ];
                                $color_class = $priority_colors[$row['priority']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-block ml-5 p-2 text-xs font-semibold rounded-full <?php echo $color_class; ?>">
                                    <?php echo htmlspecialchars($row['priority']); ?>
                                </span>
                            </div>

                            <!-- Price -->
                            <p class="text-pink-600 text-sm">
                                IDR <?php echo htmlspecialchars($row['price']); ?>
                            </p>

                            <div class="flex justify-between items-center">
                                <!-- Date Added -->
                                <p class="text-slate-500 text-xs">
                                    Added: <?php echo date('M j, Y', strtotime($row['created_at'])); ?>
                                </p>

                                <!-- Action Buttons -->
                                 <div class="flex space-x-1">
                                    <a href="edit_wishlist.php?id=<?php echo htmlspecialchars($row['id']); ?>">
                                        <span class="material-symbols-outlined text-[17px] text-sky-500 hover:text-sky-800">edit</span>
                                    </a>
                                    <a href="delete_wishlist.php?id=<?php echo htmlspecialchars($row['id']); ?>" onclick="return confirm('Yakin ingin menghapus?')">
                                        <span class="material-symbols-outlined text-[17px] text-red-600 hover:text-red-800">delete</span>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php else: ?>
            <!-- No wishlist Message -->
            <div class="text-center py-12">
                <div class="mb-4">
                    <span class="material-symbols-outlined text-6xl text-slate-300">inventory_2</span>
                </div>
                <h3 class="text-2xl font-semibold text-slate-600 mb-2">Looks like your wishlist is empty</h3>
                <p class="text-slate-500 mb-6">Found something you like? Save it here for later!</p>
                <a href="wishlistProduct.php" class="inline-block border border-slate-800 rounded-3xl px-8 py-3 bg-second text-slate-700 hover:font-semibold transition-all">
                    Add Your First Wishlist
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