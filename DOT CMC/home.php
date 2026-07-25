<?php include 'db_connect.php'; 

// Fetch basic stats
$students = $conn->query("SELECT * FROM student")->num_rows;
$total_payments = $conn->query("SELECT sum(amount) as total FROM payments")->fetch_array()['total'];
$today = date('Y-m-d');
$today_payments = $conn->query("SELECT sum(amount) as total FROM payments WHERE date(date_created) = '$today'")->fetch_array()['total'];

// Fetch breakdown for today
$breakdown = $conn->query("SELECT mode, sum(amount) as total FROM payments WHERE date(date_created) = '$today' GROUP BY mode");
$upi = 0;
$cash = 0;
while($row = $breakdown->fetch_assoc()){
    if(strtolower($row['mode']) == 'upi') $upi = $row['total'];
    else $cash += $row['total']; // Default to cash for others/null
}
$today_payments = $today_payments > 0 ? $today_payments : 0;
$total_payments = $total_payments > 0 ? $total_payments : 0;

$upi_percent = $today_payments > 0 ? ($upi / $today_payments) * 100 : 0;
$cash_percent = $today_payments > 0 ? ($cash / $today_payments) * 100 : 0;
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12 mb-4">
            <h4 style="color: var(--primary-navy); font-weight: 600;">Dashboard</h4>
            <span class="text-muted">Welcome back, <?php echo $_SESSION['login_name']; ?>! Here's what's happening today.</span>
        </div>
    </div>

    <div class="row mb-4">
        <!-- KPI Card 1 -->
        <div class="col-md-4 mb-3">
            <div class="card kpi-card h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="kpi-title mb-1">Total Students</div>
                            <div class="kpi-value"><?php echo number_format($students); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fa fa-users fa-2x text-muted" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Card 2 -->
        <div class="col-md-4 mb-3">
            <div class="card kpi-card h-100 py-2" style="border-left-color: #1cc88a !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="kpi-title mb-1">Total Payments (All Time)</div>
                            <div class="kpi-value">₹<?php echo number_format($total_payments, 2); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fa fa-money-check fa-2x text-muted" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Card 3 -->
        <div class="col-md-4 mb-3">
            <div class="card kpi-card h-100 py-2" style="border-left-color: #f6c23e !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="kpi-title mb-1">Today's Collection</div>
                            <div class="kpi-value">₹<?php echo number_format($today_payments, 2); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fa fa-calendar-day fa-2x text-muted" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Today's Payment Breakdown -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold" style="color: var(--primary-navy);">Today's Collection Breakdown</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span style="font-size: 1.2rem; font-weight: 600;">UPI</span>
                        <span style="font-size: 1.2rem; font-weight: 700; color: var(--primary-navy);">₹<?php echo number_format($upi, 2); ?></span>
                    </div>
                    <div class="progress mb-4" style="height: 25px; border-radius: 5px;">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo $upi_percent; ?>%; background-color: var(--primary-navy);" aria-valuenow="<?php echo $upi_percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span style="font-size: 1.2rem; font-weight: 600;">Cash</span>
                        <span style="font-size: 1.2rem; font-weight: 700; color: #1cc88a;">₹<?php echo number_format($cash, 2); ?></span>
                    </div>
                    <div class="progress mb-4" style="height: 25px; border-radius: 5px;">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo $cash_percent; ?>%; background-color: #1cc88a;" aria-valuenow="<?php echo $cash_percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <?php if($today_payments == 0): ?>
                        <div class="text-center text-muted mt-4">
                            <i class="fa fa-box-open fa-3x mb-3" style="color: #cbd5e1;"></i>
                            <p style="font-size: 1.2rem; font-weight: 500;">No payments recorded today yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>