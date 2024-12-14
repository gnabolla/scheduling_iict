<?php
// filebrowser.php

// Define the base directory for security
define('BASE_DIR', realpath(__DIR__));

// List of core files relative to BASE_DIR
$coreFiles = [
    'core/Auth.php',
    'core/Database.php',
    'core/Middleware.php',
    'config.php',
    'functions.php',
    'router.php',
    'index.php',
    'views/partials/foot.php',
    'views/partials/head.php',
    'views/partials/nav.php',
    'views/partials/side.php',
    'views/index.view.php',
    'views/layout.view.php',
    'controllers/index.php',
    'controllers/login.php',
    'controllers/signup.php',
    'db.sql'
];

// List of items to ignore (files and directories)
$ignoreItems = [
    'assets',
    'filebrowser.php',
    '.htaccess'
];

// Handle AJAX request to get file content
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'getContent') {
    $file = $_POST['file'] ?? '';

    // Security Check: Prevent directory traversal and ensure the file is within BASE_DIR
    $realBase = BASE_DIR;
    $realFile = realpath(BASE_DIR . DIRECTORY_SEPARATOR . $file);

    if ($realFile === false || strpos($realFile, $realBase) !== 0 || !is_file($realFile)) {
        http_response_code(400);
        echo 'Invalid file path';
        exit;
    }

    // Restrict to certain file types
    $allowedExtensions = ['php', 'html', 'js', 'css', 'sql', 'txt'];
    $extension = pathinfo($realFile, PATHINFO_EXTENSION);
    if (!in_array(strtolower($extension), $allowedExtensions)) {
        http_response_code(403);
        echo 'Access denied';
        exit;
    }

    // Output file content
    header('Content-Type: text/plain');
    echo file_get_contents($realFile);
    exit;
}

// Function to list files and directories recursively
function listFiles($dir, $coreFiles, $ignoreItems) {
    $realBase = realpath(BASE_DIR);
    $realDir = realpath($dir);

    // Prevent directory traversal
    if ($realDir === false || strpos($realDir, $realBase) !== 0) {
        echo "<li>Access Denied</li>";
        return;
    }

    $files = scandir($realDir);
    echo "<ul>";
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        if (in_array($file, $ignoreItems)) continue; // Skip ignored items

        $path = $realDir . DIRECTORY_SEPARATOR . $file;
        $relativePath = str_replace(BASE_DIR . DIRECTORY_SEPARATOR, '', $path);
        $relativePath = str_replace('\\', '/', $relativePath); // Normalize for comparison
        $isCore = in_array($relativePath, $coreFiles);

        if (is_dir($path)) {
            echo "<li>";
            echo "<span class='folder' data-path='" . htmlspecialchars($relativePath, ENT_QUOTES) . "' onclick='toggleFolder(this)'>📁 " . htmlspecialchars($file) . "</span>";
            listFiles($path, $coreFiles, $ignoreItems);
            echo "</li>";
        } else {
            echo "<li>";
            echo "<span class='file' data-path='" . htmlspecialchars($relativePath, ENT_QUOTES) . "' onclick='toggleFile(this)'>📄 " . htmlspecialchars($file) . "</span>";
            if ($isCore) {
                echo " (Core)";
            }
            echo "<pre class='content' style='display:none;'></pre>";
            echo "</li>";
        }
    }
    echo "</ul>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple File Browser</title>
    <meta charset="UTF-8">
</head>
<body>
    <h1>File Browser</h1>
    
    <!-- Buttons -->
    <div>
        <button onclick="showCoreFiles()">Show Core Files</button>
        <button onclick="expandAll()">Expand All</button>
        <button onclick="collapseAll()">Collapse All</button>
        <button onclick="showAllContent()">Show All Content</button>
    </div>
    
    <!-- File Browser -->
    <div id="file-browser">
        <?php listFiles('.', $coreFiles, $ignoreItems); // Start from base directory ?>
    </div>

    <script>
        // Function to toggle folder visibility
        function toggleFolder(element) {
            const nextSibling = element.nextElementSibling;
            if (nextSibling) {
                if (nextSibling.style.display === 'none') {
                    nextSibling.style.display = 'block';
                } else {
                    nextSibling.style.display = 'none';
                }
            }
        }

        // Function to toggle file content visibility
        async function toggleFile(element) {
            const pre = element.nextElementSibling;
            if (pre.style.display === 'block') {
                pre.style.display = 'none';
                return;
            }

            if (pre.textContent.trim() === '') {
                const path = element.getAttribute('data-path');
                try {
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=getContent&file=${encodeURIComponent(path)}`
                    });
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    const content = await response.text();
                    pre.textContent = content;
                } catch (error) {
                    pre.textContent = 'Error loading file content';
                }
            }

            pre.style.display = 'block';
        }

        // Function to show core files and their content
        async function showCoreFiles() {
            // Hide all files and folders first
            collapseAll();
            hideAllContent();

            const coreFiles = <?php echo json_encode($coreFiles); ?>;
            const files = document.querySelectorAll('.file');

            for (const file of files) {
                const path = file.getAttribute('data-path').replace(/\\/g, '/');
                if (coreFiles.includes(path)) {
                    file.parentElement.style.display = 'list-item';
                    
                    // Expand all parent folders
                    let parent = file.parentElement.parentElement.previousElementSibling;
                    while (parent && parent.classList.contains('folder')) {
                        parent.nextElementSibling.style.display = 'block';
                        parent = parent.parentElement.parentElement.previousElementSibling;
                    }

                    // Show file content
                    const pre = file.nextElementSibling;
                    if (pre.textContent.trim() === '') {
                        try {
                            const response = await fetch(window.location.href, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `action=getContent&file=${encodeURIComponent(path)}`
                            });
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            const content = await response.text();
                            pre.textContent = content;
                        } catch (error) {
                            pre.textContent = 'Error loading file content';
                        }
                    }
                    pre.style.display = 'block';
                }
            }
        }

        // Function to expand all folders
        function expandAll() {
            const folders = document.querySelectorAll('.folder');
            folders.forEach(folder => {
                const nextSibling = folder.nextElementSibling;
                if (nextSibling) {
                    nextSibling.style.display = 'block';
                }
            });
        }

        // Function to collapse all folders
        function collapseAll() {
            const folders = document.querySelectorAll('.folder');
            folders.forEach(folder => {
                const nextSibling = folder.nextElementSibling;
                if (nextSibling) {
                    nextSibling.style.display = 'none';
                }
            });

            // Hide all file contents
            const contents = document.querySelectorAll('.content');
            contents.forEach(content => {
                content.style.display = 'none';
            });
        }

        // Function to show all content (expand all folders and display all file contents)
        async function showAllContent() {
            // Expand all folders
            expandAll();

            // Show content for all files
            const files = document.querySelectorAll('.file');
            for (const file of files) {
                const pre = file.nextElementSibling;
                if (pre.style.display !== 'block') {
                    const path = file.getAttribute('data-path');
                    if (pre.textContent.trim() === '') {
                        try {
                            const response = await fetch(window.location.href, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `action=getContent&file=${encodeURIComponent(path)}`
                            });
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            const content = await response.text();
                            pre.textContent = content;
                        } catch (error) {
                            pre.textContent = 'Error loading file content';
                        }
                    }
                    pre.style.display = 'block';
                }
            }
        }

        // Function to hide all content
        function hideAllContent() {
            const contents = document.querySelectorAll('.content');
            contents.forEach(content => {
                content.style.display = 'none';
            });
        }

        // Initially collapse all folders except top-level
        document.addEventListener("DOMContentLoaded", function() {
            const subLists = document.querySelectorAll('#file-browser ul ul');
            subLists.forEach(function(ul) {
                ul.style.display = 'none';
            });
        });
    </script>
</body>
</html>
