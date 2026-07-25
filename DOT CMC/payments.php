<?php include 'db_connect.php'; ?>
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
						<b>List of Payments </b>
						<span class="float:right"><a class="btn btn-primary btn-block btn-sm col-sm-2 float-right" href="javascript:void(0)" id="new_payment">
					<i class="fa fa-plus"></i> New 
				</a></span>
					</div>
					<div class="card-body">
						<table class="table table-condensed table-bordered table-hover">
							<thead>
								<tr>
 									<th class="text-center">#</th>
 									<th class="">Date</th>
 									<th class="">ID No.</th>
 									<th class="">EF No.</th>
 									<th class="">Name</th>
 									<th class="">Paid Amount</th>
									 <th class="text-center" style="width:90px;">Receipt No.</th>
 									<th class="text-center">Action</th>
								</tr>
							</thead>
							<tbody id="payments_data">
								<!-- Lazy loading data here -->
							</tbody>
						</table>
					</div>
				</div>
			</div>
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
		max-height: :150px;
	}
</style>
<script>
	$(document).ready(function(){
		$('table').DataTable({
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": "ajax.php?action=get_payments",
				"type": "POST"
			},
			"columns": [
				{ "data": "index", "className": "text-center" },
				{ "data": "date_created" },
				{ "data": "id_no" },
				{ "data": "ef_no" },
				{ "data": "sname" },
				{ "data": "amount", "className": "text-right" },
				{ "data": "remarks", "className": "text-center" },
				{ "data": "action", "className": "text-center", "orderable": false }
			],
			"order": [[1, "desc"]],
			"drawCallback": function(settings) {
				// Re-bind click events after DataTables draws a new page
				$('.view_payment').off('click').on('click', function(){
					uni_modal("Payment Details", "view_payment.php?ef_id="+$(this).attr('data-ef_id')+"&pid="+$(this).attr('data-id'), "mid-large");
				});
				$('.edit_payment').off('click').on('click', function(){
					uni_modal("Manage Payment", "manage_payment.php?id="+$(this).attr('data-id'), "mid-large");
				});
				$('.delete_payment').off('click').on('click', function(){
					_conf("Are you sure to delete this payment ?", "delete_payment", [$(this).attr('data-id')]);
				});
			}
		});
	});
	
	$('#new_payment').click(function(){
		uni_modal("New Payment ","manage_payment.php","mid-large")
		
	})
	
	function delete_payment($id){
		start_load()
		$.ajax({
			url:'ajax.php?action=delete_payment',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully deleted",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
			}
		})
	}
</script>