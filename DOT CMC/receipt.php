<?php 
include 'db_connect.php';
$fees = $conn->query("SELECT ef.*,s.name as sname,s.id_no,c.course as `class` FROM student_ef_list ef inner join student s on s.id = ef.student_id inner join courses c on c.id = ef.course_id  where ef.id = {$_GET['ef_id']}");
foreach($fees->fetch_array() as $k => $v){
	$$k= $v;
}
$payments = $conn->query("SELECT * FROM payments where ef_id = $id ");
$pay_arr = array();
while($row=$payments->fetch_array()){
	$pay_arr[$row['id']] = $row;
}
?>

<style>
	/* Compact layout for 1/3 A4 size paper */
	body {
		font-size: 11px;
	}
	.flex{
		display: inline-flex;
		width: 100%;
	}
	.w-50{
		width: 50%;
	}
	.text-center{
		text-align:center;
	}
	.text-right{
		text-align:right;
	}
	table.wborder{
		width: 100%;
		border-collapse: collapse;
	}
	table.wborder>tbody>tr>th,
	table.wborder>tbody>tr>td {
		border: 1px solid;
		padding: 2px 4px; /* Compress table cell height */
		font-size: 11px;
	}
	p{
		margin: 0; /* Remove paragraph margins */
	}
	h4 {
		margin: 0;
		font-size: 14px;
	}
	hr {
		margin: 2px 0;
	}
	@media print {
		.hide-on-print {
			display: none !important;
		}
		@page {
			margin: 5mm; /* Minimal page margins */
		}
	}
		@media screen and (max-width: 768px) {
			.qr-container {
				display: none !important;
			}
			.content-container {
				width: 100% !important;
				padding-right: 0 !important;
			}
		}
	</style>
<div class="container-fluid flex" style="justify-content: space-between; align-items: stretch; padding: 0;">
	
	<!-- LEFT SIDE: Text and Tables -->
	<div class="content-container" style="width: 75%; padding-right: 10px;">
		<div style="text-align: center; margin-bottom: 5px;">
			<h4 style="margin: 0;"><b><?php echo $_GET['pid'] == 0 ? "Payments" : 'DOT-CMC Computer' ?></b></h4>
		</div>
		
		<div style="border-top: 1px solid #333; border-bottom: 1px solid #333; padding: 2px 0; margin-bottom: 5px;" class="flex">
			<div style="width: 50%;">
				<p>EF. No: <b><?php echo $ef_no ?></b></p>
				<p>Student: <b><?php echo ucwords($sname) ?></b></p>
				<p>Course: <b><?php echo $class ?></b></p>
			</div>
			<?php if($_GET['pid'] > 0): ?>
			<div style="width: 50%;">
				<p>Payment Date: <b><?php echo isset($pay_arr[$_GET['pid']]) ? date("M d,Y",strtotime($pay_arr[$_GET['pid']]['date_created'])): '' ?></b></p>
				<p>Paid Amount: <b><?php echo isset($pay_arr[$_GET['pid']]) ? number_format($pay_arr[$_GET['pid']]['amount'],2): '' ?></b></p>
				<p>Receipt No.: <b><?php echo isset($pay_arr[$_GET['pid']]) ? $pay_arr[$_GET['pid']]['remarks'] : '' ?></b> <b>(<?php echo isset($pay_arr[$_GET['pid']]) ? $pay_arr[$_GET['pid']]['mode'] : '' ?>)</b></p>
			</div>
			<?php else: ?>
			<div style="width: 50%;"></div>
			<?php endif; ?>
		</div>
		
		<p style="margin-bottom: 2px;"><b>Payment Summary</b></p>
		<table class="wborder">
			<tr>
				<td width="50%" style="vertical-align: top;">
					<p><b>Fee Details</b></p>
					<table width="100%">
						<tr>
							<td width="50%">Fee Type</td>
							<td width="50%" class='text-right'>Amount</td>
						</tr>
						<?php 
					$cfees = $conn->query("SELECT * FROM fees where course_id = $course_id");
					$ftotal = 0;
					while ($row = $cfees->fetch_assoc()) {
						$ftotal += $row['amount'];
					?>
					<tr>
						<td><b><?php echo $row['description'] ?></b></td>
						<td class='text-right'><b><?php echo number_format($row['amount']) ?></b></td>
					</tr>
					<?php
					}
					?>
					   <tr style="border-top:2px solid #000;">
						   <th style="text-align:left;">Total</th>
						   <th class='text-right'><b><?php echo number_format($ftotal) ?></b></th>
					   </tr>
					</table>
				</td>			
				<td width="50%" style="vertical-align: top;">
				<p><b>Payment Details</b></p>
					<table width="100%" class="wborder">
						<tr>
							<td width="50%">Date</td>
							<td width="50%" class='text-right'>Amount</td>
						</tr>
						<?php 
							$ptotal = 0;
							foreach ($pay_arr as $row) {
								if($row["id"] <= $_GET['pid'] || $_GET['pid'] == 0){
								$ptotal += $row['amount'];
								$print_class = ($row['id'] != $_GET['pid'] && $_GET['pid'] > 0) ? 'hide-on-print' : '';
						?>
						<tr class="<?php echo $print_class; ?>">
							<td><b><?php echo date("Y-m-d",strtotime($row['date_created'])) ?></b></td>
							<td class='text-right'><b><?php echo number_format($row['amount']) ?></b></td>
						</tr>
						<?php
							}
							}
						?>
						   <tr style="border-top:2px solid #000;">
							   <th style="text-align:left;">Total</th>
							   <th class='text-right'><b><?php echo number_format($ptotal) ?></b></th>
						   </tr>
					</table>
					<table width="100%">
						<tr>
							<td>Total Payable Fee</td>
							<td class='text-right'><b><?php echo number_format($ftotal) ?></b></td>
						</tr>
						<tr>
							<td>Total Paid</td>
							<td class='text-right'><b><?php echo number_format($ptotal) ?></b></td>
						</tr>
						<tr>
							<td>Balance</td>
							<td class='text-right'><b><?php echo number_format($ftotal-$ptotal) ?></b></td>
						</tr>
					</table>
				</td>			
			</tr>
		</table>
	</div>

	<!-- RIGHT SIDE: Huge QR Code spanning vertically outside the lines -->
	<div class="qr-container" style="width: 25%; display: flex; justify-content: center; align-items: center;">
		<div id="qrcode"></div>
	</div>

</div>
<script src="assets/js/qrcode.min.js"></script>
<script>
	<?php 
	$ip = $_SERVER['REMOTE_ADDR'];
	$timestamp = date('Y-m-d H:i:s');
	
	// Fast API call for location (silently fail if no internet)
	$location = 'Unknown';
	$api_url = "http://ip-api.com/json/{$ip}";
	$api_data = @file_get_contents($api_url);
	if($api_data) {
		$geo = json_decode($api_data, true);
		if($geo && $geo['status'] == 'success') {
			$location = $geo['city'] . ', ' . $geo['countryCode'];
		}
	}
	
	// Create a URL for the QR code so students can view it publicly
	$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
	$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
	
	// Optional: add a small hash for integrity/security if needed, but for now just the IDs
	$qr_data = $base_url . "/view_receipt.php?ef_id=" . $_GET['ef_id'] . "&pid=" . (int)$_GET['pid'];
	
	// Escape JS string
	$qr_data = json_encode($qr_data);
	?>
	setTimeout(function(){
		new QRCode(document.getElementById("qrcode"), {
			text: <?php echo $qr_data; ?>,
			width: 130,
			height: 130,
			colorDark : "#000000",
			colorLight : "#ffffff",
			correctLevel : QRCode.CorrectLevel.L
		});
	}, 100);
</script>