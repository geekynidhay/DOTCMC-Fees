<!DOCTYPE html>
<html lang="en">
	
<?php session_start(); ?>
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title><?php echo isset($_SESSION['system']['name']) ? $_SESSION['system']['name'] : '' ?></title>
 	

<?php
  if(!isset($_SESSION['login_id']))
    header('location:login.php');
 include('./header.php'); 
 // include('./auth.php'); 
 ?>

</head>
<style>
	/* background removed */
  .modal-dialog.large {
    width: 80% !important;
    max-width: unset;
  }
  .modal-dialog.mid-large {
    width: 50% !important;
    max-width: unset;
  }
  #viewer_modal .btn-close {
    position: absolute;
    z-index: 999999;
    /*right: -4.5em;*/
    background: unset;
    color: white;
    border: unset;
    font-size: 27px;
    top: 0;
}
#viewer_modal .modal-dialog {
        width: 80%;
    max-width: unset;
    height: calc(90%);
    max-height: unset;
}
  #viewer_modal .modal-content {
       background: black;
    border: unset;
    height: calc(100%);
    display: flex;
    align-items: center;
    justify-content: center;
  }
  #viewer_modal img,#viewer_modal video{
    max-height: calc(100%);
    max-width: calc(100%);
  }
</style>

<body>
	<?php include 'topbar.php' ?>
	<?php include 'navbar.php' ?>
  <div class="toast" id="alert_toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-body text-white">
    </div>
  </div>
  
  <main id="view-panel" >
      <?php $page = isset($_GET['page']) ? $_GET['page'] :'home'; ?>
  	<?php include $page.'.php' ?>
  </main>

  <!-- Mobile Only View for QR Scanner -->
  <div id="mobile-scan-only" class="d-none justify-content-center align-items-center flex-column" style="height: 100vh; width: 100vw; background: #f4f6f9; position: fixed; top: 0; left: 0; z-index: 1030;">
      <div class="text-center mb-5">
        <h2 style="color: var(--primary-navy); font-weight: bold; margin-bottom: 5px;">Scanner Mode</h2>
        <p class="text-muted">Tap below to scan a receipt</p>
      </div>
      <button class="btn btn-success" onclick="$('#scan-qr-btn').click();" style="padding: 25px 40px; font-size: 1.6rem; border-radius: 20px; box-shadow: 0 8px 15px rgba(0,0,0,0.15);"><i class="fa fa-qrcode fa-2x mb-2 d-block"></i> Scan QR</button>
      <a href="ajax.php?action=logout" class="btn btn-outline-danger" style="margin-top: 80px; padding: 10px 30px; border-radius: 10px;">Logout</a>
  </div>

  <style>
  @media (max-width: 768px) {
      #topbar, #sidebar, #view-panel {
          display: none !important;
      }
      #mobile-scan-only {
          display: flex !important;
      }
  }
  </style>

  <div id="preloader"></div>
  <a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>

<div class="modal fade" id="confirm_modal" role='dialog'>
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title">Confirmation</h5>
      </div>
      <div class="modal-body">
        <div id="delete_content"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id='confirm' onclick="">Continue</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="uni_modal" role='dialog'>
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title"></h5>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Save</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
      </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="viewer_modal" role='dialog'>
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
              <button type="button" class="btn-close" data-dismiss="modal"><span class="fa fa-times"></span></button>
              <img src="" alt="">
      </div>
    </div>
  </div>
</body>
<script>
	 window.start_load = function(){
    $('body').prepend('<di id="preloader2"></di>')
  }
  window.end_load = function(){
    $('#preloader2').fadeOut('fast', function() {
        $(this).remove();
      })
  }
 window.viewer_modal = function($src = ''){
    start_load()
    var t = $src.split('.')
    t = t[1]
    if(t =='mp4'){
      var view = $("<video src='"+$src+"' controls autoplay></video>")
    }else{
      var view = $("<img src='"+$src+"' />")
    }
    $('#viewer_modal .modal-content video,#viewer_modal .modal-content img').remove()
    $('#viewer_modal .modal-content').append(view)
    $('#viewer_modal').modal({
            show:true,
            backdrop:'static',
            keyboard:false,
            focus:true
          })
          end_load()  

}
  window.uni_modal = function($title = '' , $url='',$size=""){
    start_load()
    $.ajax({
        url:$url,
        error:err=>{
            console.log()
            alert("An error occured")
        },
        success:function(resp){
            if(resp){
                $('#uni_modal .modal-title').html($title)
                $('#uni_modal .modal-body').html(resp)
                if($size != ''){
                    $('#uni_modal .modal-dialog').addClass($size)
                }else{
                    $('#uni_modal .modal-dialog').removeAttr("class").addClass("modal-dialog modal-md")
                }
                $('#uni_modal').modal({
                  show:true,
                  backdrop:'static',
                  keyboard:false,
                  focus:true
                })
                end_load()
            }
        }
    })
}
window._conf = function($msg='',$func='',$params = []){
     $('#confirm_modal #confirm').attr('onclick',$func+"("+$params.join(',')+")")
     $('#confirm_modal .modal-body').html($msg)
     $('#confirm_modal').modal('show')
  }
   window.alert_toast= function($msg = 'TEST',$bg = 'success'){
      $('#alert_toast').removeClass('bg-success')
      $('#alert_toast').removeClass('bg-danger')
      $('#alert_toast').removeClass('bg-info')
      $('#alert_toast').removeClass('bg-warning')

    if($bg == 'success')
      $('#alert_toast').addClass('bg-success')
    if($bg == 'danger')
      $('#alert_toast').addClass('bg-danger')
    if($bg == 'info')
      $('#alert_toast').addClass('bg-info')
    if($bg == 'warning')
      $('#alert_toast').addClass('bg-warning')
    $('#alert_toast .toast-body').html($msg)
    $('#alert_toast').toast({delay:3000}).toast('show');
  }
  $(document).ready(function(){
    $('#preloader').fadeOut('fast', function() {
        $(this).remove();
      })
  })
  $('.datetimepicker').datetimepicker({
      format:'Y/m/d H:i',
      startDate: '+3d'
  })
  $('.select2').select2({
    placeholder:"Please select here",
    width: "100%"
  })
</script>	

<!-- QR Scanner Modal -->
<div class="modal fade" id="qrScannerModal" tabindex="-1" role="dialog" aria-labelledby="qrScannerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content" style="background: white;">
      <div class="modal-header">
        <h5 class="modal-title" style="color: var(--primary-navy);">Scan Receipt QR Code</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="close-qr-scanner">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="padding: 0;">
        <div id="qr-reader" style="width:100%;"></div>
      </div>
    </div>
  </div>
</div>
<script src="assets/js/html5-qrcode.min.js"></script>
<script>
  let html5QrcodeScanner = null;

  $('#scan-qr-btn').click(function(){
      $('#qrScannerModal').modal('show');
      
      if (!html5QrcodeScanner) {
          html5QrcodeScanner = new Html5QrcodeScanner(
              "qr-reader", 
              { 
                  fps: 30, // Increased FPS for better capture on low quality webcams
                  formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ], // Strictly only look for QR codes to save CPU
                  aspectRatio: 1.0
              }, 
              false);
      }
      
      html5QrcodeScanner.render(function(decodedText, decodedResult) {
          // Try to extract EFID and PID from either new URL format or old text format
          let efidMatch = decodedText.match(/ef_id=(\d+)/) || decodedText.match(/\[EFID:(\d+)\]/);
          let pidMatch = decodedText.match(/pid=(\d+)/) || decodedText.match(/\[PID:(\d+)\]/);
          
          if(efidMatch && pidMatch) {
              let ef_id = efidMatch[1];
              let pid = pidMatch[1];
              
              html5QrcodeScanner.clear();
              $('#qrScannerModal').modal('hide');
              uni_modal('Payment Details','receipt.php?ef_id='+ef_id+'&pid='+pid);
          } else {
              // Old QR fallback
              let receiptMatch = decodedText.match(/Receipt No:\s*(\d+)/);
              if (receiptMatch) {
                  let receiptNo = receiptMatch[1];
                  $.ajax({
                      url: 'find_receipt.php',
                      method: 'POST',
                      data: { receipt_no: receiptNo },
                      success: function(resp) {
                          if (resp) {
                              let res = JSON.parse(resp);
                              if (res.status == 1) {
                                  html5QrcodeScanner.clear();
                                  $('#qrScannerModal').modal('hide');
                                  uni_modal('Payment Details','receipt.php?ef_id='+res.ef_id+'&pid='+res.pid);
                              } else {
                                  alert_toast("Old Receipt ID Not Found", "danger");
                              }
                          }
                      }
                  });
              } else {
                  alert_toast("Invalid Receipt QR Code", "danger");
              }
          }
      }, function(errorMessage) {
          // parse error, ignore
      });
  });

  $('#qrScannerModal').on('hidden.bs.modal', function () {
      if(html5QrcodeScanner){
          html5QrcodeScanner.clear();
      }
  });
</script>

</html>