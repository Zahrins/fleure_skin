<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "fleurskin");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit_skin_feeling'])) {
        // Handle skin feeling form
        $skin_rating = $conn->real_escape_string($_POST['skin_rating']);
        $skin_notes = $conn->real_escape_string($_POST['skin_notes']);
        $skin_concerns = isset($_POST['skin_concerns']) ? implode(',', $_POST['skin_concerns']) : '';
        
        $sql = "INSERT INTO skin_diary (rating, notes, concerns, date_created) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $skin_rating, $skin_notes, $skin_concerns);
        
        if ($stmt->execute()) {
            $message = "Skin diary entry saved successfully!";
        } else {
            $error = "Error saving skin diary entry.";
        }
        $stmt->close();
    }
    
    if (isset($_POST['submit_appearance_feeling'])) {
        // Handle appearance feeling form
        $appearance_rating = $conn->real_escape_string($_POST['appearance_rating']);
        $appearance_notes = $conn->real_escape_string($_POST['appearance_notes']);
        $mood_tags = isset($_POST['mood_tags']) ? implode(',', $_POST['mood_tags']) : '';
        
        $sql = "INSERT INTO appearance_diary (rating, notes, mood_tags, date_created) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $appearance_rating, $appearance_notes, $mood_tags);
        
        if ($stmt->execute()) {
            $message = "Appearance diary entry saved successfully!";
        } else {
            $error = "Error saving appearance diary entry.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Diary Notes - FleurSkin</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .glass-effect { 
            backdrop-filter: blur(10px); 
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .star-rating { cursor: pointer; }
        .star-rating:hover { color: #fbbf24; }
        .star-rating.active { color: #f59e0b; }
        .floating-label { transition: all 0.3s ease; }
        .mood-tag { transition: all 0.2s ease; }
        .mood-tag:hover { transform: translateY(-2px); }
        .concern-tag { transition: all 0.2s ease; }
        .concern-tag:hover { transform: scale(1.05); }
    </style>
</head>
<body class="min-h-screen gradient-bg">
    <!-- Header -->
    <div class="p-6">
        <div class="flex items-center gap-4 mb-6">
            <span class="material-symbols-outlined cursor-pointer text-white hover:text-gray-200 transition-colors text-2xl" 
                  onclick="history.back()">arrow_back</span>
            <h1 class="text-white text-2xl font-bold">My Skin Journey</h1>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="grid md:grid-cols-2 gap-8">
            <!-- Skin Feeling Form -->
            <div class="glass-effect rounded-2xl shadow-2xl p-6">
                <div class="flex items-center gap-3 mb-6">
                    <span class="material-symbols-outlined text-purple-600 text-2xl">face</span>
                    <h3 class="text-gray-800 font-bold text-xl">How I Feel About My Skin</h3>
                </div>
                
                <form method="POST" class="space-y-6">
                    <!-- Rating Section -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-3">Rate your skin today</label>
                        <div class="flex gap-2 mb-4">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <span class="material-symbols-outlined text-3xl star-rating text-gray-300" 
                                      data-rating="<?php echo $i; ?>" data-form="skin">star</span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="skin_rating" id="skin_rating" required>
                    </div>

                    <!-- Skin Concerns -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-3">Today's skin concerns</label>
                        <div class="grid grid-cols-2 gap-2">
                            <?php 
                            $concerns = ['Dryness', 'Oiliness', 'Acne', 'Sensitivity', 'Dark spots', 'Redness', 'Pores', 'Fine lines'];
                            foreach($concerns as $concern): 
                            ?>
                                <label class="concern-tag flex items-center space-x-2 p-2 rounded-lg border border-gray-200 hover:bg-purple-50 cursor-pointer">
                                    <input type="checkbox" name="skin_concerns[]" value="<?php echo $concern; ?>" class="rounded">
                                    <span class="text-sm"><?php echo $concern; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="relative">
                        <textarea name="skin_notes" id="skin_notes" rows="4" 
                                  class="w-full p-4 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"
                                  placeholder="Write about your skin journey today..."></textarea>
                    </div>

                    <button type="submit" name="submit_skin_feeling" 
                            class="w-full bg-gradient-to-r from-purple-500 to-purple-600 text-white font-medium py-3 px-6 rounded-lg hover:from-purple-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <span class="material-symbols-outlined inline mr-2">save</span>
                        Save Skin Entry
                    </button>
                </form>
            </div>

            <!-- Appearance Feeling Form -->
            <div class="glass-effect rounded-2xl shadow-2xl p-6">
                <div class="flex items-center gap-3 mb-6">
                    <span class="material-symbols-outlined text-pink-600 text-2xl">sentiment_satisfied</span>
                    <h3 class="text-gray-800 font-bold text-xl">How I Feel About My Appearance</h3>
                </div>
                
                <form method="POST" class="space-y-6">
                    <!-- Rating Section -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-3">Rate your confidence today</label>
                        <div class="flex gap-2 mb-4">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <span class="material-symbols-outlined text-3xl star-rating text-gray-300" 
                                      data-rating="<?php echo $i; ?>" data-form="appearance">star</span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="appearance_rating" id="appearance_rating" required>
                    </div>

                    <!-- Mood Tags -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-3">Today's mood</label>
                        <div class="flex flex-wrap gap-2">
                            <?php 
                            $moods = [
                                ['label' => 'Confident', 'color' => 'bg-green-100 text-green-800'],
                                ['label' => 'Happy', 'color' => 'bg-yellow-100 text-yellow-800'],
                                ['label' => 'Radiant', 'color' => 'bg-orange-100 text-orange-800'],
                                ['label' => 'Self-conscious', 'color' => 'bg-blue-100 text-blue-800'],
                                ['label' => 'Natural', 'color' => 'bg-emerald-100 text-emerald-800'],
                                ['label' => 'Glowing', 'color' => 'bg-purple-100 text-purple-800']
                            ];
                            foreach($moods as $mood): 
                            ?>
                                <label class="mood-tag inline-flex items-center space-x-2 px-3 py-2 rounded-full <?php echo $mood['color']; ?> cursor-pointer">
                                    <input type="checkbox" name="mood_tags[]" value="<?php echo $mood['label']; ?>" class="rounded">
                                    <span class="text-sm font-medium"><?php echo $mood['label']; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="relative">
                        <textarea name="appearance_notes" id="appearance_notes" rows="4" 
                                  class="w-full p-4 border border-gray-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent resize-none"
                                  placeholder="Share how you're feeling about yourself today..."></textarea>
                    </div>

                    <button type="submit" name="submit_appearance_feeling" 
                            class="w-full bg-gradient-to-r from-pink-500 to-pink-600 text-white font-medium py-3 px-6 rounded-lg hover:from-pink-600 hover:to-pink-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <span class="material-symbols-outlined inline mr-2">favorite</span>
                        Save Confidence Entry
                    </button>
                </form>
            </div>
        </div>

        <!-- Recent Entries Preview -->
        <div class="mt-12">
            <h2 class="text-white text-xl font-bold mb-6">Recent Journey Highlights</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="glass-effect rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-yellow-500">star</span>
                        <span class="text-gray-700 font-medium">Today's Average</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-800">4.5/5</p>
                    <p class="text-sm text-gray-600">Feeling great!</p>
                </div>
                
                <div class="glass-effect rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-green-500">trending_up</span>
                        <span class="text-gray-700 font-medium">Progress</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-800">+12%</p>
                    <p class="text-sm text-gray-600">This week</p>
                </div>
                
                <div class="glass-effect rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-purple-500">calendar_today</span>
                        <span class="text-gray-700 font-medium">Streak</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-800">7 days</p>
                    <p class="text-sm text-gray-600">Keep it up!</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Star rating functionality
        document.querySelectorAll('.star-rating').forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                const form = this.dataset.form;
                const stars = document.querySelectorAll(`.star-rating[data-form="${form}"]`);
                
                // Reset all stars for this form
                stars.forEach(s => s.classList.remove('active'));
                
                // Highlight selected stars
                for(let i = 0; i < rating; i++) {
                    stars[i].classList.add('active');
                }
                
                // Set hidden input value
                document.getElementById(`${form}_rating`).value = rating;
            });
            
            // Hover effect
            star.addEventListener('mouseenter', function() {
                const rating = this.dataset.rating;
                const form = this.dataset.form;
                const stars = document.querySelectorAll(`.star-rating[data-form="${form}"]`);
                
                stars.forEach((s, index) => {
                    if(index < rating) {
                        s.style.color = '#fbbf24';
                    } else {
                        s.style.color = '#d1d5db';
                    }
                });
            });
            
            star.addEventListener('mouseleave', function() {
                const form = this.dataset.form;
                const stars = document.querySelectorAll(`.star-rating[data-form="${form}"]`);
                
                stars.forEach(s => {
                    if(s.classList.contains('active')) {
                        s.style.color = '#f59e0b';
                    } else {
                        s.style.color = '#d1d5db';
                    }
                });
            });
        });

        // Auto-save draft functionality (optional)
        let autoSaveTimer;
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(() => {
                    // Auto-save logic here if needed
                    console.log('Auto-saving draft...');
                }, 2000);
            });
        });
    </script>
</body>
</html>