<?php 
include('db_connect.php');

// Auto-create table if missing
$conn->query("CREATE TABLE IF NOT EXISTS `expenses` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `amount` float NOT NULL,
  `date_created` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
?>

<script>
	window.saveMyExpense = function() {
		var title = $('#title').val();
		var amount = $('#amount').val();
		if(!title || !amount) {
			alert("ERROR: Please fill out the Title and Amount fields!");
			return;
		}
		
		var formObj = document.getElementById('manage-expense');
		var fd = new FormData(formObj);
		
		if(typeof start_load === 'function') start_load();
		
		$.ajax({
			url: 'ajax.php?action=save_expense',
			data: fd,
		    cache: false,
		    contentType: false,
		    processData: false,
		    method: 'POST',
		    type: 'POST',
			success: function(resp) {
				if(resp == 1) {
					alert_toast("Expense successfully added", 'success');
					setTimeout(function(){
						location.reload();
					}, 1500);
				} else {
					if(typeof end_load === 'function') end_load();
					alert("Failed to save expense: " + resp);
				}
			},
			error: function(err) {
				if(typeof end_load === 'function') end_load();
				alert("Server Error! Check network or server configuration.");
			}
		});
	}

	window.delete_expense = function($id){
		if(typeof start_load === 'function') start_load();
		$.ajax({
			url:'ajax.php?action=delete_expense',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Expense successfully deleted",'success');
					setTimeout(function(){
						location.reload();
					},1500);
				}
			}
		});
	}

	$(document).ready(function(){
		$('#filter').click(function(){
			var start_date = $('#start_date').val();
			var end_date = $('#end_date').val();
			if(start_date != '' && end_date != ''){
				location.replace('index.php?page=expenses&start_date='+start_date+'&end_date='+end_date);
			}
		});

		$('.delete_expense').click(function(){
			_conf("Are you sure to delete this expense?","delete_expense",[$(this).attr('data-id')])
		});
	});
</script>

<div class="container-fluid">
	
	<div class="col-lg-12">
		<div class="row mb-4 mt-4">
			<div class="col-md-12">
				<!-- Date Filter -->
				<div class="card">
					<div class="card-body">
						<div class="row justify-content-center">
							<label for="" class="mt-2">Start Date</label>
							<div class="col-sm-3">
								<input type="date" name="start_date" id="start_date" value="<?php echo $start_date ?>" class="form-control">
							</div>
							<label for="" class="mt-2">End Date</label>
							<div class="col-sm-3">
								<input type="date" name="end_date" id="end_date" value="<?php echo $end_date ?>" class="form-control">
							</div>
							<div class="col-sm-2">
								<button class="btn btn-primary" type="button" id="filter" style="margin-top: 2px;">Filter</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<!-- FORM Panel -->
			<div class="col-md-4">
			<form action="javascript:void(0)" id="manage-expense">
				<div class="card">
					<div class="card-header">
						    Expense Form
				  	</div>
					<div class="card-body">
							<input type="hidden" name="id">
							<div class="form-group">
								<label class="control-label">Expense Title</label>
								<input type="text" name="title" id="title" class="form-control" list="expense-titles" required placeholder="e.g. Rent, Internet, etc.">
								<datalist id="expense-titles">
									<option value="Internet Bill">
									<option value="Office Rent">
									<option value="Electricity Bill">
									<option value="Staff Salary">
									<option value="Stationery">
									<option value="Marketing">
									<option value="Maintenance">
								</datalist>
							</div>
							<div class="form-group">
								<label class="control-label">Amount</label>
								<input type="number" step="any" name="amount" id="amount" class="form-control text-right" required>
							</div>
							<div class="form-group">
								<label class="control-label">Date</label>
								<input type="date" name="date_created" id="date_created" class="form-control" value="<?php echo date('Y-m-d') ?>">
							</div>
					</div>
					<div class="card-footer">
						<div class="row">
							<div class="col-md-12 text-center">
								<button class="btn btn-sm btn-primary px-4 mr-2" type="button" onclick="saveMyExpense()">Save</button>
								<button class="btn btn-sm btn-secondary px-4" type="button" onclick="$('#manage-expense').get(0).reset()">Cancel</button>
							</div>
						</div>
					</div>
				</div>
			</form>
			</div>
			<!-- FORM Panel -->

			<!-- Table Panel -->
			<div class="col-md-8">
				<div class="card">
					<div class="card-header">
						<b>Expense List</b>
					</div>
					<div class="card-body">
						<table class="table table-bordered table-hover">
							<thead>
								<tr>
									<th class="text-center">#</th>
									<th class="text-center">Date</th>
									<th class="text-center">Title</th>
									<th class="text-center">Amount</th>
									<th class="text-center">Action</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$i = 1;
								$total = 0;
								$expenses = $conn->query("SELECT * FROM expenses where date(date_created) between '$start_date' and '$end_date' order by unix_timestamp(date_created) desc");
								if($expenses && $expenses->num_rows > 0):
								while($row=$expenses->fetch_assoc()):
									$total += $row['amount'];
								?>
								<tr>
									<td class="text-center"><?php echo $i++ ?></td>
									<td class="text-center"><?php echo date("M d, Y", strtotime($row['date_created'])) ?></td>
									<td class=""><b><?php echo $row['title'] ?></b></td>
									<td class="text-right"><b><?php echo number_format($row['amount'], 2) ?></b></td>
									<td class="text-center">
										<button class="btn btn-sm btn-outline-danger delete_expense" type="button" data-id="<?php echo $row['id'] ?>">Delete</button>
									</td>
								</tr>
								<?php endwhile; ?>
								<?php else: ?>
								<tr>
									<td class="text-center" colspan="5">No expenses recorded for this period.</td>
								</tr>
								<?php endif; ?>
							</tbody>
							<tfoot>
								<tr>
									<th colspan="3" class="text-right">Total Expenses</th>
									<th class="text-right"><?php echo number_format($total, 2) ?></th>
									<th></th>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>
			<!-- Table Panel -->
		</div>
	</div>	
</div>
