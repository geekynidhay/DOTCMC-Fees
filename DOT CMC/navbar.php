
<style>
	.collapse a{
		text-indent:10px;
	}
	nav#sidebar{
		/*background: url(assets/uploads/<?php echo $_SESSION['system']['cover_img'] ?>) !important*/
	}
</style>

<nav id="sidebar" class='mx-lt-5'>
		<div class="sidebar-list" style="margin-top: 45px;">
				<a href="index.php?page=home" class="nav-item nav-home" style="font-size: 1.0rem;"><span class='icon-field'><i class="fa fa-tachometer-alt "></i></span> Dashboard</a>
				<a href="index.php?page=inquiries" class="nav-item nav-inquiries" style="font-size: 1.0rem; background: #28a745 !important; color: white !important; font-weight: bold;"><span class='icon-field'><i class="fa fa-plus-circle text-white"></i></span> New Inquiry</a>
				<a href="index.php?page=fees" class="nav-item nav-fees" style="font-size: 1.0rem;"><span class='icon-field'><i class="fa fa-money-check "></i></span> Student Fees</a>
				<a href="index.php?page=payments" class="nav-item nav-payments" style="font-size: 1.0rem;"><span class='icon-field'><i class="fa fa-receipt "></i></span> Payments</a>
				<div class="mx-2 text-white mt-3 mb-1" style="font-size: 0.85rem; opacity: 0.7;">Master List</div>
				<a href="index.php?page=courses" class="nav-item nav-courses" style="font-size: 1.0rem;"><span class='icon-field'><i class="fa fa-scroll "></i></span> Courses & Fees</a>
				<a href="index.php?page=students" class="nav-item nav-students" style="font-size: 1.0rem;"><span class='icon-field'><i class="fa fa-users "></i></span> Students</a>
				<div class="mx-2 text-white mt-3 mb-1" style="font-size: 0.85rem; opacity: 0.7;">Report</div>
				<a href="index.php?page=payments_report" class="nav-item nav-payments_report"><span class='icon-field'><i class="fa fa-th-list"></i></span> Payments Report</a>
				<a href="index.php?page=expenses" class="nav-item nav-expenses"><span class='icon-field'><i class="fa fa-chart-bar"></i></span> Business Expenses</a>
				<div class="mx-2 text-white mt-3 mb-1" style="font-size: 0.85rem; opacity: 0.7;">Systems</div>
				<?php if($_SESSION['login_type'] == 1): ?>
				<a href="index.php?page=users" class="nav-item nav-users"><span class='icon-field'><i class="fa fa-users "></i></span> Users</a>
				<!-- <a href="index.php?page=site_settings" class="nav-item nav-site_settings"><span class='icon-field'><i class="fa fa-cogs"></i></span> System Settings</a> -->
			<?php endif; ?>
				<a href="index.php?page=manage_storage" class="nav-item nav-manage_storage"><span class='icon-field'><i class="fa fa-cloud"></i></span> Manage Storage</a>
		</div>

</nav>
<script>
	$('.nav_collapse').click(function(){
		console.log($(this).attr('href'))
		$($(this).attr('href')).collapse()
	})
	$('.nav-<?php echo isset($_GET['page']) ? $_GET['page'] : '' ?>').addClass('active')
</script>
