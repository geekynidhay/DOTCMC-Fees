<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include('db_connect.php');

if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("<h3 style='text-align:center; margin-top:50px;'>Invalid Link.</h3>");
}

$token = $conn->real_escape_string($_GET['token']);
$query = $conn->query("SELECT s.*, c.course FROM student s LEFT JOIN courses c ON s.course_id = c.id WHERE s.form_token = '$token'");

if ($query->num_rows == 0) {
    die("<h3 style='text-align:center; margin-top:50px;'>Invalid Link.</h3>");
}

$student = $query->fetch_assoc();

if ($student['form_submitted'] == 1) {
    die("<h3 style='text-align:center; margin-top:50px;'>Link expired. You have already submitted your details.</h3>");
}

$course_name = $student['course'] ? $student['course'] : 'Course';
$course_upper = strtoupper($course_name);

$marksheet_required = true;
if ($course_upper == 'BASIC' || $course_upper == 'TALLY' || strpos($course_upper, 'BASIC TALLY') !== false) {
    $marksheet_required = false;
}
$marksheet_label = ($course_upper == 'PGDCA') ? 'Graduation Marksheet' : '12th Marksheet';

$message = '';

function compressImage($source, $destination, $quality) {
    $info = getimagesize($source);
    if ($info['mime'] == 'image/jpeg') 
        $image = imagecreatefromjpeg($source);
    elseif ($info['mime'] == 'image/gif') 
        $image = imagecreatefromgif($source);
    elseif ($info['mime'] == 'image/png') 
        $image = imagecreatefrompng($source);
    else return false;
    
    // Resize image to ensure it's small (max 1000px)
    $maxWidth = 1000;
    $maxHeight = 1000;
    $width = imagesx($image);
    $height = imagesy($image);

    if ($width > $maxWidth || $height > $maxHeight) {
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency
        if ($info['mime'] == 'image/png' || $info['mime'] == 'image/gif') {
            imagecolortransparent($newImage, imagecolorallocatealpha($newImage, 0, 0, 0, 127));
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }
        
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);
        $image = $newImage;
    }

    imagejpeg($image, $destination, $quality);
    imagedestroy($image);
    return $destination;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // If admin already set these, use DB value, otherwise use POST value
    $name = !empty($student['name']) ? $student['name'] : strtoupper($conn->real_escape_string($_POST['name']));
    $father_name = !empty($student['father_name']) ? $student['father_name'] : strtoupper($conn->real_escape_string($_POST['father_name']));
    $email = !empty($student['email']) ? $student['email'] : strtoupper($conn->real_escape_string($_POST['email']));
    $address = !empty($student['address']) ? $student['address'] : strtoupper($conn->real_escape_string($_POST['address']));
    
    // Gender is always filled by student
    $gender = strtoupper($conn->real_escape_string($_POST['gender']));
    
    try {
        // Directory structure
        $session = $student['session'];
        $year = $student['year'];
        
        $folder_name = trim($name);
        if ($gender == 'MALE') {
            $folder_name .= " S-O " . trim($father_name);
        } elseif ($gender == 'FEMALE') {
            $folder_name .= " D-O " . trim($father_name);
        }
        $folder_name = preg_replace('/[^A-Za-z0-9\- ]/', '', $folder_name);
        
        // Create local folders
        $base_dir = "assets/uploads/students";
        $session_dir = $base_dir . "/" . trim("$session $year");
        $course_dir = $session_dir . "/" . trim($course_name);
        $student_dir = $course_dir . "/" . $folder_name;
        
        if (!file_exists($student_dir)) {
            mkdir($student_dir, 0777, true);
        }
        
        $photo_id = $student['photo'] ?? '';
        $marksheet_id = $student['marksheet'] ?? '';
        
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            // Delete old photo if it exists to save space
            if (!empty($student['photo']) && file_exists($student['photo'])) {
                @unlink($student['photo']);
            }
            
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $photo_filename = trim($name) . "-P." . $ext;
            $photo_path = $student_dir . "/" . $photo_filename;
            
            compressImage($_FILES['photo']['tmp_name'], $photo_path, 60);
            $photo_id = $photo_path; // Save relative path to DB
        }
        
        if ($marksheet_required && isset($_FILES['marksheet']) && $_FILES['marksheet']['error'] == 0) {
            // Delete old marksheet if it exists to save space
            if (!empty($student['marksheet']) && file_exists($student['marksheet'])) {
                @unlink($student['marksheet']);
            }
            
            $ext = pathinfo($_FILES['marksheet']['name'], PATHINFO_EXTENSION);
            $marksheet_filename = trim($name) . "-M." . $ext;
            $marksheet_path = $student_dir . "/" . $marksheet_filename;
            
            compressImage($_FILES['marksheet']['tmp_name'], $marksheet_path, 60);
            $marksheet_id = $marksheet_path; // Save relative path to DB
        }
    
    $update_query = "UPDATE student SET 
        name = '$name',
        gender = '$gender',
        father_name = '$father_name',
        email = '$email',
        address = '$address',
            photo = '$photo_id',
            marksheet = '$marksheet_id',
            form_submitted = 1
            WHERE id = {$student['id']}";
            
        if ($conn->query($update_query)) {
            $message = "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Thank You, " . addslashes(ucwords(strtolower($name))) . "!',
                        text: 'Your details and documents have been submitted successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        window.close();
                    });
                });
            </script>";
            $student['form_submitted'] = 1; // Prevent showing form again
        } else {
            $message = "<div class='alert alert-danger'>Database Error: Could not save details.</div>";
        }
    } catch (Throwable $e) {
        $message = "<div class='alert alert-danger'><strong>System Error:</strong> " . htmlspecialchars($e->getMessage()) . " in " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #f4f7f6; padding: 10px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { max-width: 600px; margin: 10px auto; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border: none; }
        .card-header { border-radius: 12px 12px 0 0 !important; padding: 15px; }
        input[type="text"], input[type="email"], textarea { text-transform: uppercase; }
        .form-control, .custom-select { border-radius: 8px; padding: 10px 15px; height: auto; border: 1px solid #ced4da; }
        .form-group label { font-weight: 600; color: #495057; margin-bottom: 5px; }
        .btn-primary { border-radius: 8px; padding: 12px; font-size: 1.1rem; font-weight: bold; background-color: #007bff; border: none; box-shadow: 0 4px 6px rgba(0,123,255,0.2); transition: all 0.3s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 8px rgba(0,123,255,0.3); }
        .preview-img { max-width: 100%; height: auto; max-height: 250px; margin-top: 15px; display: none; border-radius: 8px; border: 2px dashed #007bff; padding: 4px; object-fit: contain; }
        .form-control-file { border: 1px solid #ced4da; padding: 10px; border-radius: 8px; background: #fff; width: 100%; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container-fluid px-0">
        <div class="card">
            <div class="card-header bg-primary text-white text-center">
                <h4>Registration - <?php echo $course_name; ?></h4>
            </div>
            <div class="card-body">
                <?php echo $message; ?>
                
                <?php if($student['form_submitted'] == 0): ?>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($student['name'] ?? ''); ?>" <?php echo !empty($student['name']) ? 'readonly' : ''; ?>>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender" class="form-control" required>
                            <option value="">Select Gender</option>
                            <option value="MALE">Male</option>
                            <option value="FEMALE">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Father's Name</label>
                        <input type="text" name="father_name" class="form-control" required value="<?php echo htmlspecialchars($student['father_name'] ?? ''); ?>" <?php echo !empty($student['father_name']) ? 'readonly' : ''; ?>>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>" <?php echo !empty($student['email']) ? 'readonly' : ''; ?>>
                    </div>
                    <div class="form-group">
                        <label>Home Address</label>
                        <textarea name="address" class="form-control" rows="3" required <?php echo !empty($student['address']) ? 'readonly' : ''; ?>><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group mt-4">
                        <label>Passport Size Photo <span class="text-danger">*</span></label>
                        <input type="file" name="photo" id="photo_input" class="form-control-file" accept="image/*" required>
                        <small class="text-muted d-block mt-1"><i class="fa fa-info-circle"></i> Max size: 5MB. Click below to preview.</small>
                        <div class="text-center">
                            <img id="photo_preview" class="preview-img" alt="Photo Preview">
                        </div>
                    </div>
                    <?php if ($marksheet_required): ?>
                    <div class="form-group mt-4">
                        <label><?php echo $marksheet_label; ?> <span class="text-danger">*</span></label>
                        <input type="file" name="marksheet" id="marksheet_input" class="form-control-file" accept="image/*" required>
                        <small class="text-muted d-block mt-1"><i class="fa fa-info-circle"></i> Max size: 5MB. Click below to preview.</small>
                        <div class="text-center">
                            <img id="marksheet_preview" class="preview-img" alt="Marksheet Preview">
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="mt-4 pt-2">
                        <button type="submit" class="btn btn-primary btn-block">Submit Details</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Image Preview Logic
        function setupImagePreview(inputId, previewId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            
            if (input && preview) {
                input.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.src = e.target.result;
                            preview.style.display = 'inline-block';
                        }
                        reader.readAsDataURL(file);
                    } else {
                        preview.src = '';
                        preview.style.display = 'none';
                    }
                });
            }
        }
        
        document.addEventListener("DOMContentLoaded", function() {
            setupImagePreview('photo_input', 'photo_preview');
            setupImagePreview('marksheet_input', 'marksheet_preview');
        });
    </script>
</body>
</html>
