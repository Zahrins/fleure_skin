<?php

require_once 'koneksi.php';
$conn = getConnection();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate required fields
    if (empty($_POST['product_name']) || empty($_POST['brand']) || empty($_POST['type']) || empty($_POST['expiration_date']) || empty($_POST['status'])) {
        $error = "All fields are required!";
    } else {
        // Get form data
        $product_name = $conn->real_escape_string($_POST['product_name']);
        $brand = $conn->real_escape_string($_POST['brand']);
        $type = $conn->real_escape_string($_POST['type']);
        $expiration_date = $conn->real_escape_string($_POST['expiration_date']);
        $status = $conn->real_escape_string($_POST['status']);
        
        $image = '';
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (in_array($_FILES['image']['type'], $allowed_types)) {
                if ($_FILES['image']['size'] <= $max_size) {
                    $image = time() . '_' . basename($_FILES['image']['name']);
                    $target_dir = "uploads/";
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
            $sql = "INSERT INTO product_inventory (product_name, brand, image, type, expiration_date, status) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $product_name, $brand, $image, $type, $expiration_date, $status);
            
            if ($stmt->execute()) {
                header("Location: products.php?success=1");
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
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Add Product - FleurSkin</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>
<body class="flex justify-center items-center min-h-screen m-6 bg-first">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        
        <h2 class="text-slate-700 font-bold text-2xl mb-6">Add New Product</h2>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
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
                <label class="block text-slate-700 font-medium mb-2">Type <span class="text-red-500">*</span></label>
                <input class="w-full border border-slate-300 rounded-md p-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500" 
                       type="text" 
                       name="type" 
                       value="<?php echo isset($_POST['type']) ? htmlspecialchars($_POST['type']) : ''; ?>"
                       required>
            </div>
            
            <div>
                <label class="block text-slate-700 font-medium mb-2">Expiration Date <span class="text-red-500">*</span></label>
                <input class="w-full border border-slate-300 rounded-md p-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500" 
                       type="date" 
                       name="expiration_date" 
                       value="<?php echo isset($_POST['expiration_date']) ? htmlspecialchars($_POST['expiration_date']) : ''; ?>"
                       required>
            </div>
            
            <div>
                <label class="block text-slate-700 font-medium mb-3">Usage Status <span class="text-red-500">*</span></label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="radio" name="status" value="New" class="mr-2" 
                               <?php echo (isset($_POST['status']) && $_POST['status'] == 'New') ? 'checked' : ''; ?> required>
                        <span class="text-slate-700">New / Unused</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="status" value="Used" class="mr-2" 
                               <?php echo (isset($_POST['status']) && $_POST['status'] == 'Used') ? 'checked' : ''; ?> required>
                        <span class="text-slate-700">Used / In Use</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="status" value="Almost Expired" class="mr-2" 
                               <?php echo (isset($_POST['status']) && $_POST['status'] == 'Almost Expired') ? 'checked' : ''; ?> required>
                        <span class="text-slate-700">Almost Expired</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="status" value="Expired" class="mr-2" 
                               <?php echo (isset($_POST['status']) && $_POST['status'] == 'Expired') ? 'checked' : ''; ?> required>
                        <span class="text-slate-700">Expired</span>
                    </label>
                </div>
            </div>
            
            <div class="mt-4">
                <input class="submit text-center w-full bg-[#e6aab4] text-white font-bold py-2 px-6 rounded-md hover:bg-[#eba3af] transition-colors cursor-pointer" type="submit" value="Add Product" href="products.php">
                <a href="products.php" class="block text-center bg-slate-200 text-slate-700 font-medium py-2 px-4 rounded-md hover:bg-slate-300 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</body>
</html>