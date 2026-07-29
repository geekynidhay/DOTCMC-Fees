<?php
$base_dir = "assets/uploads/students";

// Create base dir if it doesn't exist
if (!file_exists($base_dir)) {
    mkdir($base_dir, 0777, true);
}

// Handle ZIP Download Request
if (isset($_GET['action']) && $_GET['action'] == 'download_zip' && isset($_GET['folder'])) {
    $folder_path = urldecode($_GET['folder']);
    
    // Security check to prevent directory traversal
    $real_base = realpath($base_dir);
    $real_requested = realpath($folder_path);
    
    if ($real_requested && strpos($real_requested, $real_base) === 0 && is_dir($real_requested)) {
        $zip_name = basename($real_requested) . "_archive.zip";
        $zip_path = sys_get_temp_dir() . "/" . $zip_name;
        
        $zip = new ZipArchive();
        if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($real_requested),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($real_requested) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
            $zip->close();
            
            header('Content-Type: application/zip');
            header('Content-disposition: attachment; filename='.$zip_name);
            header('Content-Length: ' . filesize($zip_path));
            readfile($zip_path);
            unlink($zip_path); // delete temp zip
            exit;
        }
    }
}

$current_dir = isset($_GET['dir']) ? urldecode($_GET['dir']) : $base_dir;

// Security check
$real_base = realpath($base_dir);
$real_current = realpath($current_dir);

if (!$real_current || strpos($real_current, $real_base) !== 0) {
    $current_dir = $base_dir; // Fallback to base if invalid
    $real_current = $real_base;
}

$files = scandir($real_current);
$items = [];
foreach ($files as $file) {
    if ($file == '.' || $file == '..') continue;
    $items[] = [
        'name' => $file,
        'path' => $current_dir . '/' . $file,
        'is_dir' => is_dir($real_current . '/' . $file)
    ];
}

$is_root = ($real_current == $real_base);
$parent_dir = dirname($current_dir);
?>

<div class="container-fluid">
    <div class="card mt-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="m-0"><i class="fa fa-folder-open"></i> Manage Storage (Local)</h5>
            <div>
                <?php if (!$is_root): ?>
                    <a href="index.php?page=manage_storage&dir=<?php echo urlencode($parent_dir); ?>" class="btn btn-sm btn-light"><i class="fa fa-arrow-up"></i> Up</a>
                    <a href="index.php?page=manage_storage" class="btn btn-sm btn-light"><i class="fa fa-home"></i> Root</a>
                    <a href="manage_storage.php?action=download_zip&folder=<?php echo urlencode($current_dir); ?>" class="btn btn-sm btn-warning"><i class="fa fa-download"></i> Download Folder (ZIP)</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <h4><i class="fa fa-folder-open text-warning"></i> <?php echo htmlspecialchars(basename($current_dir) == 'students' ? 'Website Database' : basename($current_dir)); ?></h4>
            <hr>
            <div class="row">
                <?php if (count($items) == 0): ?>
                    <div class="col-12 text-center text-muted">
                        <p>No files or folders found here.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <div class="col-md-3 mb-4">
                            <div class="card text-center shadow-sm p-3 h-100">
                                <?php if ($item['is_dir']): ?>
                                    <a href="index.php?page=manage_storage&dir=<?php echo urlencode($item['path']); ?>" class="text-decoration-none text-dark">
                                        <i class="fa fa-folder text-warning" style="font-size: 3rem;"></i>
                                        <p class="mt-2 mb-0 font-weight-bold"><?php echo htmlspecialchars($item['name']); ?></p>
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo htmlspecialchars($item['path']); ?>" target="_blank" class="text-decoration-none text-dark">
                                        <i class="fa fa-file-image text-info" style="font-size: 3rem;"></i>
                                        <p class="mt-2 mb-0 font-weight-bold" style="word-wrap: break-word;"><?php echo htmlspecialchars($item['name']); ?></p>
                                    </a>
                                    <div class="mt-2">
                                        <a href="<?php echo htmlspecialchars($item['path']); ?>" download class="btn btn-sm btn-outline-primary"><i class="fa fa-download"></i> Download</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
