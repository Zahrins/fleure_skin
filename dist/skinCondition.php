<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "fleurskin");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate required fields
    if (empty($_POST['skin_condition']) || empty($_POST['product']) || empty($_POST['scale'])) {
        $error = "All fields are required!";
    } else {
        // Get form data
        $skin_condition = $conn->real_escape_string($_POST['skin_condition']);
        $product = $conn->real_escape_string($_POST['product']);
        $scale = $conn->real_escape_string($_POST['scale']);
        $note = $conn->real_escape_string($_POST['note']);
        
        $image = '';
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (in_array($_FILES['image']['type'], $allowed_types)) {
                if ($_FILES['image']['size'] <= $max_size) {
                    $image = time() . '_' . basename($_FILES['image']['name']);
                    $target_dir = "uploads3/";
                    $target_file = $target_dir . $image;
                    
                    // Create uploads directory if it doesn't exist
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    
                    // Move uploaded file
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $error = "Failed to upload image.";
                        $image = '';
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
            $sql = "INSERT INTO tracker (skin_conditions, image, product, scale, note) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $skin_condition,  $image, $product, $scale, $note);
            
            if ($stmt->execute()) {
                header("Location: tracker.php?success=1");
                exit();
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
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
    <title>Add Skin Log - FleurSkin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>
<body class="flex justify-center items-center min-h-screen m-6 bg-first">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        
        <h2 class="text-slate-700 font-bold text-2xl mb-6">Add Skin Log</h2>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-slate-700 font-medium mb-2">Skin Condition <span class="text-red-500">*</span></label>
                <input class="w-full border border-slate-300 rounded-md p-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500" 
                       type="text" 
                       name="skin_condition" 
                       value="<?php echo isset($_POST['skin_condition']) ? htmlspecialchars($_POST['skin_condition']) : ''; ?>"
                       required>
            </div>
            
            <div>
                <label class="block text-slate-700 font-medium mb-2">Image </label>
                <input class="w-full border border-slate-300 rounded-md p-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500" 
                       type="file" 
                       name="image" 
                       accept="image/*">
            </div>
            
            <div>
                <label class="block text-slate-700 font-medium mb-2">Product Used <span class="text-red-500">*</span></label>
                <input class="w-full border border-slate-300 rounded-md p-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500" 
                       type="text" 
                       name="product" 
                       value="<?php echo isset($_POST['product']) ? htmlspecialchars($_POST['product']) : ''; ?>"
                       required>
            </div>
            
            <div>
                <label class="block text-slate-700 font-medium mb-2">Scale (1-10) <span class="text-red-500">*</span></label>
                <input class="w-full border border-slate-300 rounded-md p-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500" 
                       type="text" 
                       name="scale" 
                       value="<?php echo isset($_POST['scale']) ? htmlspecialchars($_POST['scale']) : ''; ?>"
                       required>
            </div>
            
            <div>
                <label class="block text-slate-700 font-medium mb-2">Note </label>
                <input class="w-full border border-slate-300 rounded-md p-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500" 
                       type="text" 
                       name="note" 
                       value="<?php echo isset($_POST['note']) ? htmlspecialchars($_POST['note']) : ''; ?>"
                       required>
            </div>
            
            <div class="mt-4">
                <input class="submit text-center w-full bg-[#e6aab4] text-white font-bold py-2 px-6 rounded-md hover:bg-[#eba3af] transition-colors cursor-pointer" type="submit" value="Add Product" href="tracker.php">
                <a href="tracker.php" class="block text-center bg-slate-200 text-slate-700 font-medium py-2 px-4 rounded-md hover:bg-slate-300 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</body>
</html>