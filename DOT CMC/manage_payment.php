<?php include 'db_connect.php' ?>
<?php
if(isset($_GET['id'])){
	$qry = $conn->query("SELECT * FROM payments where id = {$_GET['id']} ");
	foreach($qry->fetch_array() as $k => $v){
		$$k = $v;
	}
}
?>
<div class="container-fluid">
	<form id="manage-payment">
		<div id="msg"></div>
		<input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
		<div class="form-group">
			<label for="" class="control-label">EF.NO./Student</label>
			<select name="ef_id" id="ef_id" class="custom-select input-sm select2">
				<option value=""></option>
				<?php
					$fees = $conn->query("SELECT ef.*,s.name as sname,s.id_no FROM student_ef_list ef inner join student s on s.id = ef.student_id order by s.name asc ");
					while($row= $fees->fetch_assoc()):
						$paid = $conn->query("SELECT sum(amount) as paid FROM payments where ef_id=".$row['id'].(isset($id) ? " and id!=$id " : ''));
						$paid = $paid->num_rows > 0 ? $paid->fetch_array()['paid']:'';
						$balance = $row['total_fee'] - $paid;
				?>
				<option value="<?php echo $row['id'] ?>" data-balance="<?php echo $balance ?>" <?php echo isset($ef_id) && $ef_id == $row['id'] ? 'selected' : '' ?>><?php echo  $row['ef_no'].' | '.ucwords($row['sname']) ?></option>
				<?php endwhile; ?>
			</select>
		</div>
		 <div class="form-group">
            <label for="" class="control-label">Outstanding Balance</label>
            <input type="text" class="form-control text-right" id="balance"  value="" required readonly>
        </div>
		  <div class="form-group">
			  <label for="" class="control-label">Amount</label>
			  <input type="text" class="form-control text-right" name="amount"  value="<?php echo isset($amount) ? number_format($amount) :0 ?>" required>
		  </div>

	  <div class="form-group">
		  <label for="" class="control-label">Mode</label>
		  <select name="mode" class="form-control" required>
				<option value="">Select Mode</option>
				<option value="UPI" <?php echo (isset($mode) && $mode == 'UPI') ? 'selected' : '' ?>>UPI</option>
				<option value="Cash" <?php echo (isset($mode) && $mode == 'Cash') ? 'selected' : '' ?>>Cash</option>
		  </select>
	  </div>
	  <div class="form-group">
		   <label for="" class="control-label">Receipt No.</label>
			<?php
			// Always start at 13207, ignore higher numbers in DB
			$start_number = 13207;
			if (!isset($id)) {
				// Count how many payments exist with remarks >= 13207
				$result = $conn->query("SELECT COUNT(*) as cnt FROM payments WHERE remarks REGEXP '^[0-9]+$' AND CAST(remarks AS UNSIGNED) >= $start_number");
				$row = $result ? $result->fetch_assoc() : ['cnt' => 0];
				$next_number = $start_number + $row['cnt'];
			} else {
				$next_number = isset($remarks) ? $remarks : $start_number;
			}
			?>
			<input type="text" name="remarks" class="form-control" value="<?php echo $next_number; ?>" readonly required>
	  </div>
	</form>
</div>
<script>
	$('.select2').select2({
		placeholder:'Please select here',
		width:'100%'
	})
	$('#ef_id').change(function(){
		var amount= $('#ef_id option[value="'+$(this).val()+'"]').attr('data-balance')
		$('#balance').val(parseFloat(amount).toLocaleString('en-US',{style:'decimal',maximumFractionDigits:2,minimumFractionDigits:2}))
	})
	$('#manage-payment').submit(function(e){
		e.preventDefault()
		start_load()
		$.ajax({
			url:'ajax.php?action=save_payment',
			method:'POST',
			data:$(this).serialize(),
			error:err=>{
				console.log(err)
				end_load()
			},
			success:function(resp){
				resp = JSON.parse(resp)
				if(resp.status == 1){
					alert_toast("Data successfully saved.",'success')
					setTimeout(function(){
						var receiptUrl = 'receipt.php?ef_id='+resp.ef_id+'&pid='+resp.pid;
						var nw = window.open(receiptUrl, "_blank", "width=900,height=600");
						if (!nw) {
							console.error('Popup blocked or window could not be opened.');
							alert('Please allow pop-ups for this site to print receipts.');
							location.reload();
							return;
						}
						// Force reload to ensure latest data
						nw.location.href = receiptUrl;
						nw.onload = function() {
							alert_toast("Generating Receipt and QR Code...", "info");
							setTimeout(function() {
								nw.print();
								setTimeout(function(){
									nw.close();
									location.reload();
								}, 500);
							}, 800);
						};
					}, 500);
				}
			}
		})
	})
</script>
