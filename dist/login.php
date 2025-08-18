<?php
session_start();

// Cek jika sudah login (via session atau cookie)
if (isset($_SESSION['user_login']) || isset($_COOKIE['user_login'])) {
    header('Location: schedule.php');
    exit();
}

$error_message = '';
$username_error = '';
$password_error = '';

// Proses login
if (isset($_POST['submit'])) {
    $username_input = trim($_POST['username']);
    $password_input = $_POST['password'];
    
    $has_error = false;
    
    // Validasi input dasar
    if (empty($username_input)) {
        $username_error = "Username harus diisi!";
        $has_error = true;
    }
    
    if (empty($password_input)) {
        $password_error = "Password harus diisi!";
        $has_error = true;
    }
    
    // Jika tidak ada error validasi, lanjut ke database
    if (!$has_error) {
        // Koneksi ke database
        $conn = new mysqli("localhost", "root", "", "fleurskin");
        
        // Periksa koneksi
        if ($conn->connect_error) {
            $error_message = "Koneksi database gagal!";
        } else {
            // Query untuk mendapatkan data user
            $sql = "SELECT username, pwd FROM users WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username_input);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $stored_username = $row['username'];
                $stored_password = $row['pwd'];
                
                // Verifikasi password (plain text comparison)
                if ($password_input === $stored_password) {
                    // Login berhasil
                    $_SESSION['user_login'] = $stored_username;
                    
                    // Set cookie jika Remember Me dicentang
                    if (isset($_POST['remember'])) {
                        setcookie('user_login', $stored_username, time() + (86400 * 30), "/"); // 30 hari
                    }
                    
                    header("Location: schedule.php");
                    exit();
                } else {
                    $password_error = "Password salah!";
                }
            } else {
                $username_error = "Username tidak ditemukan!";
            }
            
            $stmt->close();
            $conn->close();
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
  <title>Login Page</title>
</head>
<body class="flex justify-center items-center min-h-screen m-6 bg-first">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-sm">
        <h2 class="text-2xl font-bold text-center text-gray-700 mb-10">Sign In</h2>
    
        <form action="login.php" method="POST" class="space-y-5">
          <!-- Username -->
          <div>
            <label for="username" class="block text-sm font-medium text-gray-600 mb-1"></label>
            <div class="relative"> 
              <i class="material-icons absolute left-3 mt-7 transform -translate-y-1/2 text-gray-400">person</i> 
              <input type="text" id="username" name="username" value="<?php echo isset($username_input) ? htmlspecialchars($username_input) : ''; ?>" placeholder="Username"
              class="<?php echo $username_error ? 'border-red-500' : 'border-slate-200'; ?> <?php echo $username_error ? 'focus:ring-red-400' : 'focus:ring-slate-400'; ?> w-full pl-10 px-4 py-2 border border-slate-200 shadow block text-sm placeholder:text-slate-400 rounded-md focus:outline-none focus:ring-2 focus:ring-slate-400"/>
            </div>
            <?php if ($username_error): ?>
                <p class="text-red-500 text-xs mt-1"><?php echo $username_error; ?></p>
            <?php endif; ?>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-600 mb-1"></label>
            <div class="relative">
              <i class="material-icons absolute left-3 mt-7 transform -translate-y-1/2 text-gray-400">lock</i> 
              <input type="password" id="password" name="password" placeholder="Password"
                class="<?php echo $password_error ? 'border-red-500' : 'border-slate-200'; ?> <?php echo $password_error ? 'focus:ring-red-400' : 'focus:ring-slate-400'; ?> w-full pl-10 pr-10 py-2 border shadow block text-sm placeholder:text-slate-400 rounded-md focus:outline-none focus:ring-2"/>
              <i class="material-icons absolute right-3 transform top-1/2 mt-3 -translate-y-1/2 text-gray-400 cursor-pointer" onclick="togglePassword()">visibility_off</i> 
            </div>
            <?php if ($password_error): ?>
                <p class="text-red-500 text-xs mt-1"><?php echo $password_error; ?></p>
            <?php endif; ?>
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="flex justify-between items-center text-sm mt-4">
              <label class="flex items-center space-x-2">
                  <input type="checkbox" name="remember" value="1" class="form-checkbox text-rose-600 rounded">
                  <span class="text-sm">Remember Me</span>
              </label>
              <a href="#" class="text-rose-600 hover:text-rose-800 transition-colors duration-200 text-sm">Forgot Password?</a>
          </div>

          <?php if ($error_message): ?>
            <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                <?php echo $error_message; ?>
            </div>
          <?php endif; ?>

          <!-- Sign In Button -->
          <input type="submit" name="submit" value="Sign In" class="block text-center w-full bg-[#e6aab4] text-white font-bold py-2 px-6 rounded-md hover:bg-[#eba3af] transition-colors cursor-pointer">
    
          <!-- Link to register -->
          <p class="text-center text-sm text-gray-600 mt-4">
            Don't have an account? 
            <a href="register.php" class="text-blue-500 hover:underline">Register</a>
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