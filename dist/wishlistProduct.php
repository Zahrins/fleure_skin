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
    if (empty($_POST['product_name']) || empty($_POST['brand']) || empty($_POST['priority'])) {
        $error = "Product name, brand, and priority are required!";
    } else {
        // Get form data
        $product_name = $conn->real_escape_string($_POST['product_name']);
        $brand = $conn->real_escape_string($_POST['brand']);
        $priority = $conn->real_escape_string($_POST['priority']);
        $note = !empty($_POST['note']) ? $conn->real_escape_string($_POST['note']) : null;
        
        // Use current timestamp if created_at is not provided
        $created_at = date('Y-m-d H:i:s');
        
        $image = '';
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (in_array($_FILES['image']['type'], $allowed_types)) {
                if ($_FILES['image']['size'] <= $max_size) {
                    $image = time() . '_' . basename($_FILES['image']['name']);
                    $target_dir = "uploads2/";
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
            $sql = "INSERT INTO wishlist_product (product_name, brand, image, priority, price, created_at) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $product_name, $brand, $image, $priority, $price, $created_at);
            
            if ($stmt->execute()) {
                header("Location: products.php?success=1");
                exit();
            }else {
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
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Add Wishlist - FleurSkin</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>
<body class="flex justify-center items-center min-h-screen m-6 bg-first">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        
        <h2 class="text-slate-700 font-bold text-2xl mb-6">Add New Wishlist</h2>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-slate-700 font-medium mb-2">Product Name <span class="text-red-500">*</span></label>
                <input class="w-full border border-slate-300 rounded-md p-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500" 
                       type="text" 
                       name="product_name" 
                       value="<?php echo isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : ''; ?>"
                       required>
            </div>
            
            <div>
                <label class="block text-slate-700 font-medium mb-2">Brand <span class="text-red-500">*</span></label>
                <input class="w-full border border-slate-300 rounded-md p-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500" 
                       type="text" 
                       name="brand" 
                       value="<?php echo isset($_POST['brand']) ? htmlspecialchars($_POST['brand']) : ''; ?>"
                       required>
            </div>
            
            <div>
                <label class="block text-slate-700 font-medium mb-2">Product Image</label>
                <input class="w-full border border-slate-300 rounded-md p-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500" 
                       type="file" 
                       name="image" 
                       accept="image/*">
            </div>

            <div>
                <label class="block text-slate-700 font-medium mb-2">Price</label>
                <input class="w-full border border-slate-300 rounded-md p-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500" 
                       type="text" 
                       name="price" 
                       placeholder="IDR ..."
                       value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>"
                       required>
            </div>
            
            <div>
                <label class="block text-slate-700 font-medium mb-3">Priority <span class="text-red-500">*</span></label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="radio" name="priority" value="Just Curious" class="mr-2" 
                               <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'Just Curious') ? 'checked' : ''; ?> required>
                        <span class="text-slate-700">Just Curious</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="priority" value="Worth Considering" class="mr-2" 
                               <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'Worth Considering') ? 'checked' : ''; ?> required>
                        <span class="text-slate-700">Worth Considering</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="priority" value="Really Want" class="mr-2" 
                               <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'Really Want') ? 'checked' : ''; ?> required>
                        <span class="text-slate-700">Really Want</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="priority" value="Must have!" class="mr-2" 
                               <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'Must have!') ? 'checked' : ''; ?> required>
                        <span class="text-slate-700">Must Have!</span>
                    </label>
                </div>
            </div>
            
            <div class="mt-6 space-y-2">
                <input class="w-full bg-[#e6aab4] text-white font-bold py-2 px-6 rounded-md hover:bg-[#eba3af] transition-colors cursor-pointer" 
                       type="submit" 
                       value="Add Product">
                <a href="products.php" class="block text-center bg-slate-200 text-slate-700 font-medium py-2 px-4 rounded-md hover:bg-slate-300 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</body>
</html>