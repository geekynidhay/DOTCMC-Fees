<?php 
include 'db_connect.php'; 
if(isset($_GET['id'])){
$qry = $conn->query("SELECT * FROM student where id= ".$_GET['id']);
foreach($qry->fetch_array() as $k => $val){
    $$k=$val;
}
}
?>
<div class="container-fluid">
    <form action="" id="manage-student">
        <style>
            #manage-student input[type="text"], 
            #manage-student input[type="email"], 
            #manage-student textarea {
                text-transform: uppercase;
            }
        </style>
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
        <div id="msg" class="form-group"></div>
        <div class="form-group">
            <label for="" class="control-label">Ledger Number</label>
            <input type="text" class="form-control" name="id_no"  value="<?php echo isset($id_no) ? $id_no :'' ?>" required>
        </div>
        <div class="form-group">
            <label for="" class="control-label">Year</label>
            <input type="number" class="form-control" name="year"  value="<?php echo isset($year) ? $year : date('Y') ?>" required>
        </div>
        <div class="form-group">
            <label for="" class="control-label">Session</label>
            <select name="session" id="session" class="custom-select" required>
                <option value="January" <?php echo isset($session) && $session == 'January' ? 'selected' : '' ?>>January</option>
                <option value="July" <?php echo isset($session) && $session == 'July' ? 'selected' : '' ?>>July</option>
            </select>
        </div>
        <div class="form-group">
            <label for="course_id" class="control-label">Course</label>
            <select name="course_id" id="course_id" class="custom-select select2" required>
                <option value=""></option>
                <?php 
                // Group by course to only get unique courses, and we just use MIN(id) as the course_id representing it
                $course_qry = $conn->query("SELECT MIN(id) as id, course FROM courses GROUP BY course order by course asc");
                while($row=$course_qry->fetch_assoc()):
                ?>
                <option value="<?php echo $row['id'] ?>" <?php echo isset($course_id) && $course_id == $row['id'] ? 'selected' : '' ?>><?php echo $row['course'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="" class="control-label">Name</label>
            <input type="text" class="form-control" name="name"  value="<?php echo isset($name) ? $name :'' ?>">
        </div>
        <div class="form-group">
            <label for="" class="control-label">Father's Name</label>
            <input type="text" class="form-control" name="father_name"  value="<?php echo isset($father_name) ? $father_name :'' ?>">
        </div>
        <div class="form-group">
            <label for="" class="control-label">Mobile Number</label>
            <input type="text" class="form-control" name="contact"  value="<?php echo isset($contact) ? $contact :'' ?>">
        </div>
        <div class="form-group">
            <label for="" class="control-label">WhatsApp Number</label>
            <input type="text" class="form-control" name="whatsapp_number"  value="<?php echo isset($whatsapp_number) ? $whatsapp_number :'' ?>">
        </div>
        <div class="form-group">
            <label for="" class="control-label">Email</label>
            <input type="email" class="form-control" name="email"  value="<?php echo isset($email) ? $email :'' ?>">
        </div>
        <div class="form-group">
            <label for="" class="control-label">Address</label>
            <textarea name="address" id="" cols="30" rows="3" class="form-control"><?php echo isset($address) ? $address :'' ?></textarea>
        </div>
    </form>
</div>
<script>
    $('#manage-student').on('reset',function(){
        $('#msg').html('')
        $('input:hidden').val('')
    })
    $('#manage-student').submit(function(e){
        e.preventDefault()
        start_load()
        $('#msg').html('')
        $.ajax({
            url:'ajax.php?action=save_student',
            data: new FormData($(this)[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            type: 'POST',
            success:function(resp){
                if(resp==1){
                    alert_toast("Data successfully saved.",'success')
                        setTimeout(function(){
                            location.reload()
                        },1000)
                }else if(resp == 2){
                $('#msg').html('<div class="alert alert-danger mx-2">ID # already exist.</div>')
                end_load()
                }   
            }
        })
    })

    $('.select2').select2({
        placeholder:"Please Select here",
        width:'100%'
    })
</script>