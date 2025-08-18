<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "fleurskin");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
        // Get form data
        $rating_skin = $conn->real_escape_string($_POST['rating_skin']);
        $rating_appearance = $conn->real_escape_string($_POST['rating_appearance']);
        $note_skin = $conn->real_escape_string($_POST['note_skin']);
        $note_appearance = $conn->real_escape_string($_POST['note_appearance']);
        $concerns = $conn->real_escape_string($_POST['concerns']);
        
        // Handle mood tags (convert array to string)
        $mood_tags = '';
        if (isset($_POST['mood_tags']) && is_array($_POST['mood_tags'])) {
            $mood_tags = $conn->real_escape_string(implode(', ', $_POST['mood_tags']));
        }
        
        $image_skin = '';
        
        // Handle image_skin upload
        if (isset($_FILES['image_skin']) && $_FILES['image_skin']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (in_array($_FILES['image_skin']['type'], $allowed_types)) {
                if ($_FILES['image_skin']['size'] <= $max_size) {
                    $image_skin = time() . '_' . basename($_FILES['image_skin']['name']);
                    $target_dir = "uploads_diary1/";
                    $target_file = $target_dir . $image_skin;
                    
                    // Create uploads directory if it doesn't exist
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    
                    // Move uploaded file
                    if (!move_uploaded_file($_FILES['image_skin']['tmp_name'], $target_file)) {
                        $error = "Failed to upload image.";
                        $image_skin = '';
                    }
                } else {
                    $error = "Image file is too large. Maximum size is 5MB.";
                }
            } else {
                $error = "Invalid image type. Only JPG, PNG, GIF, and WebP are allowed.";
            }
        }
        
        $image_appearance = '';
        
        // Handle image_appearance upload
        if (isset($_FILES['image_appearance']) && $_FILES['image_appearance']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (in_array($_FILES['image_appearance']['type'], $allowed_types)) {
                if ($_FILES['image_appearance']['size'] <= $max_size) {
                    $image_appearance = time() . '_' . basename($_FILES['image_appearance']['name']);
                    $target_dir = "uploads_diary2/";
                    $target_file = $target_dir . $image_appearance;
                    
                    // Create uploads directory if it doesn't exist
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    
                    // Move uploaded file
                    if (!move_uploaded_file($_FILES['image_appearance']['tmp_name'], $target_file)) {
                        $error = "Failed to upload image.";
                        $image_appearance = '';
                    }
                } else {
                    $error = "Image file is too large. Maximum size is 5MB.";
                }
            } else {
                $error = "Invalid image type. Only JPG, PNG, GIF, and WebP are allowed.";
            }
        }

        // Insert to database if no errors
        if (empty($error)) {
            $sql = "INSERT INTO skin_diary (rating_skin, rating_appearance, note_skin, note_appearance, concerns, mood_tags, image_skin, image_appearance) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssss", $rating_skin, $rating_appearance, $note_skin, $note_appearance, $concerns, $mood_tags, $image_skin, $image_appearance);
            
            if ($stmt->execute()) {
                header("Location: diary.php?success=1");
                exit();
            } else {
                $error = "Error: " . $stmt->error;
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
    <link href="./style.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Diary Notes - FleurSkin</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

    <style>
        .star-rating { cursor: pointer; }
        .star-rating:hover { color: #fbbf24; }
        .mood-tag input[type="checkbox"] { display: none; }
        .mood-tag input[type="checkbox"]:checked + span { 
            background-color: rgb(59 130 246 / 0.5); 
            border: 2px solid rgb(59 130 246);
        }
    </style>
</head>
<body class=" min-h-screen p-5 lg:m-6 bg-first">
    
        <div class="flex justify-center mb-8">
            <h3 class="font-bold text-slate-600 text-4xl">Glow Up Journal</h3>
        </div>     

        <!-- Show error message if exists -->
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="diaryForm">
            <div class="lg:flex w-full max-h-min mt-4 gap-5 items-stretch">
                <div class="p-4 bg-white rounded-2xl shadow-xl lg:w-1/2 h-1/2">
                    <h3 class="text-slate-700 bg-slate-100 p-3 pl-6 rounded-4xl font-bold text-l lg:text-xl mb-7">How I Feel About My Skin</h3>
                    <!-- Rating Section -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Rate your skin today</label>
                            <div class="flex gap-2 mb-4">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <span class="star-rating cursor-pointer material-symbols-outlined text-3xl text-gray-300" 
                                          data-rating="<?php echo $i; ?>" data-form="skin">star</span>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating_skin" id="rating_skin" required>
                        </div>
                    

                    <!-- skin concerns -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-3">Today's skin concerns</label>
                            <div class="flex flex-wrap gap-2">
                                <input class="w-full border border-slate-300 rounded-md p-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500" 
                                    type="text" 
                                    name="concerns" 
                                    value="<?php echo isset($_POST['concerns']) ? htmlspecialchars($_POST['concerns']) : ''; ?>"
                                    >
                                <small class="text-slate-500 text-xs mt-1 block">
                                    Contoh: Dryness, Oiliness, Acne, Sensitivity
                                </small>
                            </div>
                        </div>

                    <!-- add image -->
                        <div class="flex mt-4">
                            <span class="material-symbols-outlined mt-2 mr-3">add_photo_alternate</span>
                            <input class="w-full border border-slate-300 rounded-md p-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500" 
                                type="file" 
                                name="image_skin" 
                                accept="image/*"
                            >
                        </div>
                    
                    <!-- Notes -->
                        <div class="relative mt-5">
                            <textarea name="note_skin" rows="4" 
                                      class="w-full p-4 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-600"
                                      placeholder="Write about your skin journey today..."></textarea>
                        </div>

                        <!-- Mobile Next Button -->
                        <div class="lg:hidden mt-6 text-center">
                            <a href="#mood" class="next-btn bg-[#e6aab4] text-white px-6 py-3 rounded-full font-medium hover:bg-[#eb9fab] transition-colors inline-flex items-center">
                                Next: Mood Assessment
                                <span class="material-symbols-outlined">arrow_downward_alt</span>
                            </a>
                        </div>

                </div>
                <div id="mood" class="p-4 mt-10 lg:mt-0 bg-white rounded-2xl shadow-xl lg:w-1/2 h-1/2">
                    <h3 class="text-slate-700 bg-slate-100 p-3 pl-6 rounded-4xl font-bold text-l lg:text-xl mb-7">How I Feel About My Appearance</h3>

                    <!-- Rating Section -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-3">Rate your confidence today</label>
                            <div class="flex gap-2 mb-4">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <span class="star-rating cursor-pointer material-symbols-outlined text-3xl text-gray-300" 
                                          data-rating="<?php echo $i; ?>" data-form="appearance">star</span>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating_appearance" id="rating_appearance" required>
                        </div>
                    

                    <!-- Mood Tags -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-3">Today's mood</label>
                            <div class="flex flex-wrap gap-2">
                                <?php 
                                $moods = [
                                    ['label' => 'Confident', 'color' => 'bg-green-100 text-green-800 hover:bg-green-300'],
                                    ['label' => 'Happy', 'color' => 'bg-yellow-100 text-yellow-800 hover:bg-yellow-300'],
                                    ['label' => 'Radiant', 'color' => 'bg-orange-100 text-orange-800 hover:bg-orange-300'],
                                    ['label' => 'Self-conscious', 'color' => 'bg-blue-100 text-blue-800 hover:bg-blue-300'],
                                    ['label' => 'Natural', 'color' => 'bg-red-100 text-red-800 hover:bg-red-300'],
                                    ['label' => 'Glowing', 'color' => 'bg-purple-100 text-purple-800 hover:bg-purple-300']
                                ];
                                foreach($moods as $mood): 
                                ?>
                                    <label class="mood-tag inline-flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" name="mood_tags[]" value="<?php echo $mood['label']; ?>">
                                        <span class="px-3 py-2 rounded-full <?php echo $mood['color']; ?> text-sm font-medium transition-all">
                                            <?php echo $mood['label']; ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    <!-- add image -->
                        <div class="flex mt-4">
                            <span class="material-symbols-outlined mt-2 mr-3">add_photo_alternate</span>
                            <input class="w-full border border-slate-300 rounded-md p-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500" 
                                type="file" 
                                name="image_appearance" 
                                accept="image/*"
                            >
                        </div>
                    
                    <!-- Notes -->
                        <div class="relative mt-5">
                            <textarea name="note_appearance" rows="5" 
                                      class="w-full p-4 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-600"
                                      placeholder="Write about your appearance today..."></textarea>
                        </div>
                </div>

            </div>

            <div class="mt-5 flex gap-2 justify-end">
                <a class="border border-slate-500 text-center bg-gray-400 text-white font-medium py-2 px-6 rounded-3xl hover:bg-gray-500 transition-colors cursor-pointer" href="diary.php">Cancel</a>
                <button type="submit" class="border border-slate-500 text-center bg-[#e6aab4] text-white font-medium py-2 px-6 rounded-3xl hover:bg-[#eb9fab] transition-colors cursor-pointer">Save</button>
            </div>
        </form>

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
                document.getElementById(`rating_${form}`).value = rating;
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

        // Form validation
        document.getElementById('diaryForm').addEventListener('submit', function(e) {
            const ratingSkin = document.getElementById('rating_skin').value;
            const ratingAppearance = document.getElementById('rating_appearance').value;
            
            if (!ratingSkin) {
                alert('Please rate your skin!');
                e.preventDefault();
                return false;
            }
            
            if (!ratingAppearance) {
                alert('Please rate your confidence!');
                e.preventDefault();
                return false;
            }
            
            return true;
        });

    </script>

</body>
</html>