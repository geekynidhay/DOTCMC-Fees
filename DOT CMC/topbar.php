<style>
	.logo {
    margin: auto;
    font-size: 20px;
    background: white;
    padding: 7px 11px;
    border-radius: 50% 50%;
    color: #000000b3;
}
</style>

<nav id="topbar" class="navbar navbar-light fixed-top" style="padding: 10px 0; background-color: white !important; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); height: auto !important; min-height: 60px;">
  <div class="container-fluid mt-2 mb-2 d-flex align-items-center">
  	<div class="col-lg-12 d-flex align-items-center justify-content-between">
      
      <!-- LEFT: Title -->
      <div class="text-dark" style="margin-left: 15px;">
        <large style="font-weight: 700; font-size: 1.4rem; color: var(--primary-navy);"><?php echo isset($_SESSION['system']['name']) ? $_SESSION['system']['name'] : 'DOT CMC Fees Management' ?></large>
      </div>
      
      <!-- CENTER: Scan QR -->
      <div class="text-center d-flex align-items-center justify-content-center" style="position: absolute; left: 50%; transform: translateX(-50%);">
          <button class="btn btn-sm btn-outline-success" id="scan-qr-btn" style="font-weight: 600; padding: 6px 15px; font-size: 1.1rem;"><i class="fa fa-qrcode"></i> Scan QR</button>
      </div>

      <!-- RIGHT: Time & Admin -->
	  <div class="d-flex align-items-center justify-content-end">
          
          <!-- HDFC Quick Link -->
          <a href="https://now.hdfc.bank.in/auth/realms/retail/protocol/openid-connect/auth?response_type=code&client_id=bb-web-client&state=UEZCbndKMVMxYUVjOFFfNUt4OGN1MmdTNGIwREF-TG1tck9oaGNLWVdMUnRH&redirect_uri=https%3A%2F%2Fnow.hdfc.bank.in%2Fretail-app%2Fselect-context&scope=openid&code_challenge=h6zSXJQPwR9s6162ZLE5uCT9H3sTDFHldnRvvr-xiAE&code_challenge_method=S256&nonce=UEZCbndKMVMxYUVjOFFfNUt4OGN1MmdTNGIwREF-TG1tck9oaGNLWVdMUnRH&login_hint=284136469" target="_blank" style="margin-right: 25px;" title="HDFC NetBanking">
              <img src="https://upload.wikimedia.org/wikipedia/commons/2/28/HDFC_Bank_Logo.svg" alt="HDFC Logo" style="height: 25px; width: auto; object-fit: contain;">
          </a>

          <span id="liveClock" style="font-size: 1.1rem; font-weight: 600; color: var(--primary-navy); margin-right: 25px;"></span>
          <div class="dropdown">
              <a href="#" class="text-dark dropdown-toggle" id="account_settings" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <img src="assets/logo.png" alt="Admin" style="height: 35px; width: 35px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-navy);">
              </a>
                <div class="dropdown-menu shadow" aria-labelledby="account_settings" style="left: -2.5em; border: 1px solid var(--border-color);">
                  <a class="dropdown-item" href="javascript:void(0)" id="manage_my_account"><i class="fa fa-cog"></i> Manage Account</a>
                <a class="dropdown-item" href="ajax.php?action=logout"><i class="fa fa-power-off"></i> Logout</a>
              </div>
        </div>
      </div>
  </div>
  
</nav>

<script>
function updateClock() {
    var now = new Date();
    var hours = now.getHours();
    var minutes = now.getMinutes();
    var seconds = now.getSeconds();
    var ampm = hours >= 12 ? 'PM' : 'AM';
    
    hours = hours % 12;
    hours = hours ? hours : 12; 
    minutes = minutes < 10 ? '0' + minutes : minutes;
    seconds = seconds < 10 ? '0' + seconds : seconds;
    
    var timeString = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
    
    document.getElementById('liveClock').innerHTML = timeString;
}
setInterval(updateClock, 1000);
updateClock();
</script>

<script>
  $('#manage_my_account').click(function(){
    uni_modal("Manage Account","manage_user.php?id=<?php echo $_SESSION['login_id'] ?>&mtype=own")
  })
</script>