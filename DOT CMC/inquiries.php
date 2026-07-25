<?php include('db_connect.php');?>

<div class="container-fluid">
	
	<div class="col-lg-12">
		<div class="row mb-4 mt-4">
			<div class="col-md-12">
				
			</div>
		</div>
		<div class="row">
			<!-- FORM Panel -->

			<!-- Table Panel -->
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<b>List of Inquiries</b>
						<span class="float:right"><a class="btn btn-success btn-block btn-sm col-sm-2 float-right" href="javascript:void(0)" id="new_inquiry">
					<i class="fa fa-plus"></i> New Inquiry
				</a></span>
					</div>
					<div class="card-body">
						<table class="table table-condensed table-bordered table-hover">
							<thead>
								<tr>
									<th class="text-center">#</th>
									<th class="">Date</th>
									<th class="">Name</th>
									<th class="">Mobile</th>
									<th class="">Course</th>
									<th class="">Fees Quoted</th>
									<th class="">Session</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$i = 1;
								$inquiries = $conn->query("SELECT * FROM inquiries ORDER BY id DESC");
								if ($inquiries) {
									while($row=$inquiries->fetch_assoc()):
								?>
								<tr>
									<td class="text-center"><?php echo $i++ ?></td>
									<td>
										<p> <b><?php echo date("M d, Y h:i A",strtotime($row['date_created'])) ?></b></p>
									</td>
									<td>
										<p> <b><?php echo ucwords($row['name']) ?></b></p>
									</td>
									<td>
										<p> <b><?php echo $row['mobile'] ?></b></p>
									</td>
									<td>
										<p> <b><?php echo $row['course'] ?></b></p>
									</td>
									<td>
										<p> <b><?php echo number_format($row['fees'],2) ?></b></p>
									</td>
									<td>
										<p> <b><?php echo $row['session'] ?></b></p>
									</td>
								</tr>
								<?php endwhile; } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<!-- Table Panel -->
		</div>
	</div>	

</div>

<!-- Inquiry Modal -->
<div class="modal fade" id="inquiryModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">New Inquiry</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="manage-inquiry">
      <div class="modal-body">
      		<input type="hidden" name="id">
        	<div class="form-group">
        		<label class="control-label">Name</label>
        		<input type="text" class="form-control" name="name" required style="text-transform: uppercase;">
        	</div>
        	<div class="form-group">
        		<label class="control-label">Mobile Number</label>
        		<input type="text" class="form-control" name="mobile" required pattern="[0-9]{10,12}">
        	</div>
        	<div class="form-group">
        		<label class="control-label">Course</label>
        		<select class="custom-select browser-default" name="course" required>
        			<option value="DCA">DCA</option>
        			<option value="PGDCA">PGDCA</option>
        		</select>
        	</div>
        	<div class="form-group">
        		<label class="control-label">Fees</label>
        		<input type="number" class="form-control text-right" name="fees" required>
        	</div>
        	<div class="form-group">
        		<label class="control-label">Session</label>
        		<select class="custom-select browser-default" name="session" required>
        			<option value="Jan">Jan</option>
        			<option value="July">July</option>
        		</select>
        	</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save & Send WhatsApp</button>
      </div>
      </form>
    </div>
  </div>
</div>

<style>
	
	td{
		vertical-align: middle !important;
	}
	td p{
		margin: unset
	}
	img{
		max-width:100px;
		max-height: 150px;
	}
</style>
<script>
	$(document).ready(function(){
		$('table').dataTable()
	})
	
	$('#new_inquiry').click(function(){
		$('#inquiryModal').modal('show');
		$('#manage-inquiry').get(0).reset();
	});

	$('#manage-inquiry').submit(function(e){
		e.preventDefault();
		
		// Disable button to prevent multiple clicks
		var submitBtn = $(this).find('button[type="submit"]');
		submitBtn.attr('disabled', true).text('Saving...');
		
		start_load();
		$.ajax({
			url:'ajax.php?action=save_inquiry',
			method:'POST',
			data:$(this).serialize(),
			success:function(resp){
				if(resp==1){
					alert_toast("Inquiry successfully saved and WhatsApp message queued.","success");
					setTimeout(function(){
						location.reload()
					},1500)
				} else {
					alert_toast("Error saving inquiry","error");
					submitBtn.attr('disabled', false).text('Save & Send WhatsApp');
					end_load();
				}
			},
			error: function() {
			    submitBtn.attr('disabled', false).text('Save & Send WhatsApp');
			    end_load();
			}
		})
	})
</script>
