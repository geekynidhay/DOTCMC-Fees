<?php include('db_connect.php');?>
<style>
	input[type=checkbox]
{
  /* Double-sized Checkboxes */
  -ms-transform: scale(1.5); /* IE */
  -moz-transform: scale(1.5); /* FF */
  -webkit-transform: scale(1.5); /* Safari and Chrome */
  -o-transform: scale(1.5); /* Opera */
  transform: scale(1.5);
  padding: 10px;
}
</style>
<div class="container-fluid">
	
	<div class="col-lg-12">
		<div class="row mb-4 mt-4">
			<div class="col-md-12">
				
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<b>Fees Reminder System</b>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-4">
								<div class="form-group">
									<label for="" class="control-label">Select Session</label>
									<select name="session_filter" id="session_filter" class="custom-select select2">
										<option value="">All Sessions</option>
										<?php 
										$sessions = $conn->query("SELECT DISTINCT CONCAT(session, ' ', year) as session_val FROM student WHERE session != '' AND year != '' ORDER BY year DESC, session ASC");
										while($row = $sessions->fetch_assoc()):
										?>
										<option value="<?php echo $row['session_val'] ?>"><?php echo $row['session_val'] ?></option>
										<?php endwhile; ?>
									</select>
								</div>
							</div>
							<div class="col-md-8 text-right">
								<div class="form-group" style="margin-top: 30px;">
									<button class="btn btn-primary" id="send_reminders"><i class="fa fa-paper-plane"></i> Send WhatsApp Reminder</button>
								</div>
							</div>
						</div>
						<hr>
						<table class="table table-condensed table-bordered table-hover" id="reminder-table">
							<thead>
								<tr>
									<th class="text-center">
										<div class="form-check">
										  <input class="form-check-input" type="checkbox" id="selectAll">
										</div>
									</th>
									<th class="text-center">#</th>
									<th class="">ID No.</th>
									<th class="">Name</th>
									<th class="">Contact / WhatsApp</th>
									<th class="">Session</th>
									<th class="text-center">Status</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$i = 1;
								
								// First, let's get students
								$students_query = "SELECT s.*, CONCAT(s.session, ' ', s.year) as full_session FROM student s ORDER BY s.id DESC";
								$students = $conn->query($students_query);

								// Current Month and Year for checking payments
								$current_month = date('m');
								$current_year = date('Y');

								while($row=$students->fetch_assoc()):
									// Determine if paid this month
									// Get enrollments (ef_id) for this student
									$ef_ids = [];
									$ef_query = $conn->query("SELECT id FROM student_ef_list WHERE student_id = '{$row['id']}'");
									while($ef = $ef_query->fetch_assoc()) {
										$ef_ids[] = $ef['id'];
									}

									$paid_this_month = false;
									if(count($ef_ids) > 0) {
										$ef_in = implode(",", $ef_ids);
										// Check if there is any payment this month
										$pay_query = $conn->query("SELECT id FROM payments WHERE ef_id IN ($ef_in) AND MONTH(date_created) = '$current_month' AND YEAR(date_created) = '$current_year'");
										if($pay_query && $pay_query->num_rows > 0) {
											$paid_this_month = true;
										}
									}
									
									$status_badge = $paid_this_month ? '<span class="badge badge-success">Fees Paid for this month</span>' : '<span class="badge badge-danger">Not Paid</span>';
								?>
								<tr data-session="<?php echo $row['full_session'] ?>" style="display: none;" class="student-row">
									<td class="text-center">
										<div class="form-check">
										  <input class="form-check-input student-checkbox" type="checkbox" value="<?php echo $row['id'] ?>" data-paid="<?php echo $paid_this_month ? '1' : '0' ?>" <?php echo $paid_this_month ? 'disabled' : '' ?>>
										</div>
									</td>
									<td class="text-center"><?php echo $i++ ?></td>
									<td>
										<p> <b><?php echo $row['id_no'] ?></b></p>
									</td>
									<td>
										<p> <b><?php echo ucwords($row['name']) ?></b></p>
									</td>
									<td>
										<p> <small>Contact: <b><?php echo $row['contact'] ?></b></small></p>
										<p> <small>WhatsApp: <b><?php echo $row['whatsapp_number'] ?></b></small></p>
									</td>
									<td>
										<p> <b><?php echo $row['full_session'] ?></b></p>
									</td>
									<td class="text-center">
										<?php echo $status_badge ?>
									</td>
								</tr>
								<?php endwhile; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function(){
		$('.select2').select2({
			placeholder:"Please select here",
			width: "100%"
		})

		// Filter table by session
		$('#session_filter').change(function(){
			var selected_session = $(this).val();
			$('#selectAll').prop('checked', false);
			
			if(selected_session == ''){
				$('.student-row').hide(); // Show none if no session selected
			} else {
				$('.student-row').hide();
				$('.student-row[data-session="'+selected_session+'"]').show();
			}
			
			// Uncheck all visible/hidden checkboxes when changing filter
			$('.student-checkbox').prop('checked', false);
		})

		// Select All logic
		$('#selectAll').click(function(){
			var isChecked = $(this).is(':checked');
			var selected_session = $('#session_filter').val();
			
			if(selected_session != '') {
				// Only select visible ones that are NOT paid
				$('.student-row[data-session="'+selected_session+'"] .student-checkbox').not(':disabled').prop('checked', isChecked);
			}
		})

		// Send Reminders
		$('#send_reminders').click(function(){
			var selected_ids = [];
			$('.student-checkbox:checked').not(':disabled').each(function(){
				selected_ids.push($(this).val());
			});

			if(selected_ids.length == 0){
				alert_toast("Please select at least one student who hasn't paid.", 'warning');
				return false;
			}

			start_load()
			$.ajax({
				url:'ajax.php?action=send_fee_reminders',
				method:'POST',
				data: {student_ids: selected_ids},
				success:function(resp){
					if(resp == 1){
						alert_toast("Reminders queued for sending successfully.",'success')
						setTimeout(function(){
							location.reload()
						}, 1500)
					}else{
						alert_toast("An error occurred.", 'danger');
						end_load();
					}
				}
			})
		})
	})
</script>
