<?php
session_start();

// Include file koneksi database
require_once 'koneksi.php';

$error_message = '';
$success_message = '';
$username_error = '';
$email_error = '';
$password_error = '';

// Cek jika form sudah disubmit untuk registrasi
if (isset($_POST['submit'])) {
    // Validasi input
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $has_error = false;
    
    // Validasi username
    if (empty($username)) {
        $username_error = "Username harus diisi!";
        $has_error = true;
    } elseif (strlen($username) < 3) {
        $username_error = "Username minimal 3 karakter!";
        $has_error = true;
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $username_error = "Username hanya boleh berisi huruf, angka, dan underscore!";
        $has_error = true;
    }
    
    // Validasi email
    if (empty($email)) {
        $email_error = "Email harus diisi!";
        $has_error = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Format email tidak valid!";
        $has_error = true;
    }
    
    // Validasi password
    if (empty($password)) {
        $password_error = "Password harus diisi!";
        $has_error = true;
    } elseif (strlen($password) < 6) {
        $password_error = "Password minimal 6 karakter!";
        $has_error = true;
    }
    
    // Jika tidak ada error validasi, lanjut ke database
    if (!$has_error) {
        try {
            // Menggunakan koneksi dari file koneksi.php
            $conn = getConnection();
            
            // Cek apakah username atau email sudah ada
            $check_sql = "SELECT username, email FROM users WHERE username = ? OR email = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ss", $username, $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $row = $check_result->fetch_assoc();
                if ($row['username'] == $username) {
                    $username_error = "Username sudah terdaftar!";
                }
                if ($row['email'] == $email) {
                    $email_error = "Email sudah terdaftar!";
                }
            } else {
                // Hash password untuk keamanan yang lebih baik (opsional)
                // $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user baru
                $insert_sql = "INSERT INTO users (username, email, pwd) VALUES (?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("sss", $username, $email, $password);
                // Jika menggunakan hash: $insert_stmt->bind_param("sss", $username, $email, $hashed_password);
                
                if ($insert_stmt->execute()) {
                    $success_message = "Registrasi berhasil! Silakan login.";
                    // Reset form values
                    $username = '';
                    $email = '';
                    $password = '';
                } else {
                    $error_message = "Terjadi kesalahan saat mendaftar!";
                }
                
                $insert_stmt->close();
            }
            
            $check_stmt->close();
            $conn->close();
            
        } catch (Exception $e) {
            $error_message = "Terjadi kesalahan koneksi database!";
            // Optional: log error untuk debugging
            // error_log($e->getMessage());
        }
    }
}
?>

<!doctype html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="./style.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Register Page</title>
</head>
<body class="flex justify-center items-center min-h-screen m-6 bg-first">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-sm">
        <h2 class="text-2xl font-bold text-center text-gray-700 mb-6">Sign Up</h2>
    
        <form action="register.php" method="POST" class="space-y-5">
          <!-- Username -->
          <div>
            <label for="username" class="block text-sm font-medium text-gray-600 mb-1"></label>
            <div class="relative">
              <i class="material-icons absolute left-3 mt-2  text-gray-400">person</i> 
              <input type="text" id="username" name="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" placeholder="Username"
              class="<?php echo $username_error ? 'border-red-500' : 'border-slate-200'; ?> <?php echo $username_error ? 'focus:ring-red-400' : 'focus:ring-slate-400'; ?> w-full pl-10 px-4 py-2 border border-slate-200 shadow block text-sm placeholder:text-slate-400 rounded-md focus:outline-none focus:ring-2 focus:ring-slate-400"/>
            </div>
            <?php if ($username_error): ?>
                <p class="text-red-500 text-xs mt-1"><?php echo $username_error; ?></p>
            <?php endif; ?>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-600 mb-1"></label>
            <div class="relative">
              <i class="material-icons absolute left-3 mt-2 text-gray-400">email</i> 
              <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" placeholder="E-mail" 
              class="<?php echo $email_error ? 'border-red-500' : 'border-slate-200'; ?> <?php echo $email_error ? 'focus:ring-red-400' : 'focus:ring-slate-400'; ?> w-full pl-10 px-4 py-2 border border-slate-200 shadow block text-sm placeholder:text-slate-400 rounded-md focus:outline-none focus:ring-2 focus:ring-slate-400"/>
            </div>
            <?php if ($email_error): ?>
                <p class="text-red-500 text-xs mt-1"><?php echo $email_error; ?></p>
            <?php endif; ?>
          </div>
    
          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-600"></label>
            <div class="relative">
                <i class="material-icons mt-3 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">lock</i> 

                <input type="password" id="password" name="password" placeholder="Password"
                    class="<?php echo $password_error ? 'border-red-500' : 'border-slate-200'; ?> <?php echo $password_error ? 'focus:ring-red-400' : 'focus:ring-slate-400'; ?> w-full pl-10 pr-10 py-2 border shadow block text-sm placeholder:text-slate-400 rounded-md focus:outline-none focus:ring-2" />

                <i class="material-icons mt-3 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer" onclick="togglePassword()">visibility_off</i> 
            </div>

            <?php if ($password_error): ?>
                <p class="text-red-500 text-xs mt-1"><?php echo $password_error; ?></p>
            <?php endif; ?>
          </div>

          <?php if ($success_message): ?>
            <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
                <?php echo $success_message; ?>
            </div>
          <?php endif; ?>
          
          <?php if ($error_message): ?>
              <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                  <?php echo $error_message; ?>
              </div>
          <?php endif; ?>
      
          <!-- Sign Up Button -->
          <input type="submit" name="submit" value="Sign Up" class="block text-center w-full bg-[#e6aab4] text-white font-bold py-2 px-6 rounded-md hover:bg-[#eba3af] transition-colors cursor-pointer">

          <?php if ($success_message): ?>
          <div class="mt-4">
            <a href="login.php" class="block text-center w-full bg-[#eca8b3] text-white font-bold py-2 px-6 rounded-md hover:bg-[#eba3af] transition-colors">
              Login
            </a>
          </div>
          <?php endif; ?>
          
          <!-- Link to login if already have account -->
          <p class="text-center text-sm text-gray-600 mt-4">
            Already have an account? 
            <a href="login.php" class="text-blue-500 hover:underline">Sign In</a>
          </p>
        </form>
    </div>

    <script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.querySelector('.material-icons[onclick="togglePassword()"]');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.textContent = 'visibility';
        } else {
            passwordInput.type = 'password';
            toggleIcon.textContent = 'visibility_off';
        }
    }
    </script>
</body>
</html>