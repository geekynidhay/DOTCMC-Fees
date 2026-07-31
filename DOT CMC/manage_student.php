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
        <?php if(is_feature_enabled('dynamic_fee_dropdown')): ?>
            <?php 
            $current_course_name = '';
            if(isset($course_id) && $course_id > 0){
                $c_qry = $conn->query("SELECT course FROM courses WHERE id = $course_id");
                if($c_qry && $c_qry->num_rows > 0){
                    $current_course_name = $c_qry->fetch_assoc()['course'];
                }
            }
            ?>
            <div class="form-group">
                <label for="course_name_select" class="control-label">Course</label>
                <select id="course_name_select" class="custom-select select2" required>
                    <option value=""></option>
                    <?php 
                    $course = $conn->query("SELECT DISTINCT course FROM courses order by course asc");
                    while($row=$course->fetch_assoc()):
                    ?>
                    <option value="<?php echo $row['course'] ?>" <?php echo $current_course_name == $row['course'] ? 'selected' : '' ?>><?php echo $row['course'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="course_id" class="control-label">Fee Package</label>
                <select name="course_id" id="course_id" class="custom-select select2" required>
                    <option value=""></option>
                    <?php 
                    if($current_course_name != ''){
                        $c_esc = $conn->real_escape_string($current_course_name);
                        $fees = $conn->query("SELECT * FROM courses WHERE course = '$c_esc' order by level asc");
                        while($row = $fees->fetch_assoc()):
                    ?>
                    <option value="<?php echo $row['id'] ?>" <?php echo isset($course_id) && $course_id == $row['id'] ? 'selected' : '' ?>><?php echo $row['level'] . ' - ' . number_format($row['total_amount']) ?></option>
                    <?php endwhile; } ?>
                </select>
            </div>
        <?php else: ?>
            <div class="form-group">
                <label for="" class="control-label">Course</label>
                <select name="course_id" id="course_id" class="custom-select select2" required>
                    <option value=""></option>
                    <?php 
                    $course = $conn->query("SELECT * FROM courses order by course asc");
                    while($row=$course->fetch_assoc()):
                    ?>
                    <option value="<?php echo $row['id'] ?>" <?php echo isset($course_id) && $course_id == $row['id'] ? 'selected' : '' ?>><?php echo $row['course'] . " - " . $row['level'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        <?php endif; ?>
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

<?php if(is_feature_enabled('dynamic_fee_dropdown')): ?>
    $('#course_name_select').change(function(){
        start_load()
        $.ajax({
            url:'ajax.php?action=get_course_fees',
            method:'POST',
            data:{course_name: $(this).val()},
            success:function(resp){
                if(resp){
                    var data = JSON.parse(resp)
                    var opt = '<option value=""></option>';
                    data.forEach(function(item){
                        opt += '<option value="'+item.id+'">'+item.level+' - '+parseFloat(item.total_amount).toLocaleString('en-US')+'</option>'
                    })
                    $('#course_id').html(opt)
                }
                end_load()
            }
        })
    })
<?php endif; ?>
</script>