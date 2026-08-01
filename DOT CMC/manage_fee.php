<?php include 'db_connect.php' ?>
<?php
if(isset($_GET['id'])){
	$qry = $conn->query("SELECT * FROM student_ef_list where id = {$_GET['id']} ");
	foreach($qry->fetch_array() as $k => $v){
		$$k = $v;
	}
}
?>
<div class="container-fluid">
	<form id="manage-fees">
		<div id="msg"></div>
		<input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
		 <div class="form-group">
            <label for="" class="control-label">Enrollment No./ E.F. No.</label>
            <input type="text" class="form-control" name="ef_no"  value="<?php echo isset($ef_no) ? $ef_no :'' ?>" required>
        </div>
		<div class="form-group">
			<label for="" class="control-label">Student</label>
			<select name="student_id" id="student_id" class="custom-select input-sm select2">
				<option value=""></option>
				<?php
					$student = $conn->query("SELECT * FROM student order by name asc ");
					while($row= $student->fetch_assoc()):
				?>
				<option value="<?php echo $row['id'] ?>" <?php echo isset($student_id) && $student_id == $row['id'] ? 'selected' : '' ?>><?php echo ucwords($row['name']).' | '. $row['id_no'] ?></option>
				<?php endwhile; ?>
			</select>
		</div>
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
            <label for="course_name_select" class="control-label">Course Name</label>
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
            <label for="course_id" class="control-label">Fee</label>
            <select name="course_id" id="course_id" class="custom-select select2" required>
                <option value=""></option>
                <?php 
                if($current_course_name != ''){
                    $c_esc = $conn->real_escape_string($current_course_name);
                    $fees = $conn->query("SELECT * FROM courses WHERE course = '$c_esc' order by level asc");
                    while($row = $fees->fetch_assoc()):
                ?>
                <option value="<?php echo $row['id'] ?>" data-amount="<?php echo $row['total_amount'] ?>" <?php echo isset($course_id) && $course_id == $row['id'] ? 'selected' : '' ?>><?php echo $row['level'] . ' - ' . number_format($row['total_amount']) ?></option>
                <?php endwhile; } ?>
            </select>
        </div>
        <input type="hidden" name="total_fee" value="<?php echo isset($total_fee) ? $total_fee : '' ?>">
	</form>
</div>
<script>
	$('.select2').select2({
		placeholder:'Please select here',
		width:'100%'
	})
	$('#course_name_select').change(function(){
        start_load()
        $.ajax({
            url:'ajax.php?action=get_course_fees',
            method:'POST',
            data:{course_name: $(this).val()},
            success:function(resp){
                if(resp){
                    try {
                        var startIndex = resp.indexOf('[');
                        var endIndex = resp.lastIndexOf(']');
                        if(startIndex !== -1 && endIndex !== -1) {
                            var cleanJson = resp.substring(startIndex, endIndex + 1);
                            var data = JSON.parse(cleanJson);
                            var opt = '<option value=""></option>';
                            data.forEach(function(item){
                                opt += '<option value="'+item.id+'" data-amount="'+item.total_amount+'">'+item.level+' - '+parseFloat(item.total_amount).toLocaleString('en-US')+'</option>'
                            })
                            $('#course_id').html(opt).trigger('change')
                            $('[name="total_fee"]').val('')
                        }
                    } catch(e) {
                        console.error("JSON Parse Error: ", e);
                        console.error("Response was: ", resp);
                    }
                }
                end_load()
            },
            error: function(err){
                console.error("AJAX Error: ", err);
                end_load();
            }
        })
    })

	$('#course_id').change(function(){
		var selected_opt = $('#course_id option[value="'+$(this).val()+'"]');
		var amount = selected_opt.attr('data-amount');
		if(amount !== undefined && amount !== ''){
			$('[name="total_fee"]').val(amount)
		} else {
			$('[name="total_fee"]').val('')
		}
	})
	$('#manage-fees').submit(function(e){
		e.preventDefault()
		start_load()
		$.ajax({
			url:'ajax.php?action=save_fees',
			method:'POST',
			data:$(this).serialize(),
			error:err=>{
				console.log(err)
				end_load()
			},
			success:function(resp){
				if(resp == 1){
					location.reload();
					alert_toast("Data successfully saved.",'success')
						setTimeout(function(){
							location.reload()
						},1000)
				}else if(resp == 2){
					$('#msg').html('<div class="alert alert-danger">EF Nunmber already exist.</div>')
					end_load()
				}
			}
		})
	})
</script>
