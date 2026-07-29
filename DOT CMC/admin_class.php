<?php
session_start();
ini_set('display_errors', 1);
Class Action {
	private $db;

	public function __construct() {
		ob_start();
   	include 'db_connect.php';
    
    $this->db = $conn;
	}
	function __destruct() {
	    $this->db->close();
	    ob_end_flush();
	}

	function login(){
		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);		
		$qry = $this->db->query("SELECT * FROM users where username = '".$username."' and password = '".md5($password)."' ");
		if($qry->num_rows > 0){
			foreach ($qry->fetch_array() as $key => $value) {
				if($key != 'passwors' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
				return 1;
		}else{
			return 3;
		}
	}
	function login2(){
		
		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);		
		$qry = $this->db->query("SELECT * FROM complainants where email = '".$email."' and password = '".md5($password)."' ");
		if($qry->num_rows > 0){
			foreach ($qry->fetch_array() as $key => $value) {
				if($key != 'passwors' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
				return 1;
		}else{
			return 3;
		}
	}
	function logout(){
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
	}
	function logout2(){
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:../index.php");
	}

	function save_user(){
		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);
		$data = " name = '$name' ";
		$data .= ", username = '$username' ";
		if(!empty($password))
		$data .= ", password = '".md5($password)."' ";
		$data .= ", type = '$type' ";
		if($type == 1)
			$establishment_id = 0;
		$data .= ", establishment_id = '$establishment_id' ";
		$chk = $this->db->query("Select * from users where username = '$username' and id !='$id' ")->num_rows;
		if($chk > 0){
			return 2;
			exit;
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO users set ".$data);
		}else{
			$save = $this->db->query("UPDATE users set ".$data." where id = ".$id);
		}
		if($save){
			return 1;
		}
	}
	function delete_user(){
		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);
		$delete = $this->db->query("DELETE FROM users where id = ".$id);
		if($delete)
			return 1;
	}
	function signup(){
		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);
		$data = " name = '$name' ";
		$data .= ", email = '$email' ";
		$data .= ", address = '$address' ";
		$data .= ", contact = '$contact' ";
		$data .= ", password = '".md5($password)."' ";
		$chk = $this->db->query("SELECT * from complainants where email ='$email' ".(!empty($id) ? " and id != '$id' " : ''))->num_rows;
		if($chk > 0){
			return 3;
			exit;
		}
		if(empty($id))
			$save = $this->db->query("INSERT INTO complainants set $data");
		else
			$save = $this->db->query("UPDATE complainants set $data where id=$id ");
		if($save){
			if(empty($id))
				$id = $this->db->insert_id;
				$qry = $this->db->query("SELECT * FROM complainants where id = $id ");
				if($qry->num_rows > 0){
					foreach ($qry->fetch_array() as $key => $value) {
						if($key != 'password' && !is_numeric($key))
							$_SESSION['login_'.$key] = $value;
					}
						return 1;
				}else{
					return 3;
				}
		}
	}
	function update_account(){
		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);
		$data = " name = '".$firstname.' '.$lastname."' ";
		$data .= ", username = '$email' ";
		if(!empty($password))
		$data .= ", password = '".md5($password)."' ";
		$chk = $this->db->query("SELECT * FROM users where username = '$email' and id != '{$_SESSION['login_id']}' ")->num_rows;
		if($chk > 0){
			return 2;
			exit;
		}
			$save = $this->db->query("UPDATE users set $data where id = '{$_SESSION['login_id']}' ");
		if($save){
			$data = '';
			foreach($_POST as $k => $v){
				if($k =='password')
					continue;
				if(empty($data) && !is_numeric($k) )
					$data = " $k = '$v' ";
				else
					$data .= ", $k = '$v' ";
			}
			if($_FILES['img']['tmp_name'] != ''){
							$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
							$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
							$data .= ", avatar = '$fname' ";

			}
			$save_alumni = $this->db->query("UPDATE alumnus_bio set $data where id = '{$_SESSION['bio']['id']}' ");
			if($data){
				foreach ($_SESSION as $key => $value) {
					unset($_SESSION[$key]);
				}
				$login = $this->login2();
				if($login)
				return 1;
			}
		}
	}

	function save_settings(){
		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);
		$data = " name = '".str_replace("'","&#x2019;",$name)."' ";
		$data .= ", email = '$email' ";
		$data .= ", contact = '$contact' ";
		$data .= ", about_content = '".htmlentities(str_replace("'","&#x2019;",$about))."' ";
		if($_FILES['img']['tmp_name'] != ''){
						$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
						$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
					$data .= ", cover_img = '$fname' ";

		}
		
		// echo "INSERT INTO system_settings set ".$data;
		$chk = $this->db->query("SELECT * FROM system_settings");
		if($chk->num_rows > 0){
			$save = $this->db->query("UPDATE system_settings set ".$data);
		}else{
			$save = $this->db->query("INSERT INTO system_settings set ".$data);
		}
		if($save){
		$query = $this->db->query("SELECT * FROM system_settings limit 1")->fetch_array();
		foreach ($query as $key => $value) {
			if(!is_numeric($key))
				$_SESSION['system'][$key] = $value;
		}

			return 1;
				}
	}
	function save_course(){
		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','fid','type','amount')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		$check = $this->db->query("SELECT * FROM courses where course ='$course' and level ='$level' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO courses set $data");
			if($save){
				$id = $this->db->insert_id;
				foreach($fid as $k =>$v){
					$data = " course_id = '$id' ";
					$data .= ", description = '{$type[$k]}' ";
					$data .= ", amount = '{$amount[$k]}' ";
					$save2[] = $this->db->query("INSERT INTO fees set $data");
				}
				if(isset($save2))
						return 1;
			}
		}else{
			$save = $this->db->query("UPDATE courses set $data where id = $id");
			if($save){
				$this->db->query("DELETE FROM fees where course_id = $id and id not in (".implode(',',$fid).") ");
				foreach($fid as $k =>$v){
					$data = " course_id = '$id' ";
					$data .= ", description = '{$type[$k]}' ";
					$data .= ", amount = '{$amount[$k]}' ";
					if(empty($v)){
						$save2[] = $this->db->query("INSERT INTO fees set $data");
					}else{
						$save2[] = $this->db->query("UPDATE fees set $data where id = $v");
					}
				}
				if(isset($save2))
						return 1;
			}
		}

	}
	function delete_course(){
		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);
		$delete = $this->db->query("DELETE FROM courses where id = ".$id);
		$delete2 = $this->db->query("DELETE FROM fees where course_id = ".$id);
		if($delete && $delete2){
			return 1;
		}
	}
	function save_student(){
		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);
		$data = "";
		
		if(empty($id) && empty($form_token)){
			$form_token = md5(uniqid());
			$_POST['form_token'] = $form_token;
		}

		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				// Convert to uppercase for consistency
				$v_upper = strtoupper($v);
				if(empty($data)){
					$data .= " `$k`='$v_upper' ";
				}else{
					$data .= ", `$k`='$v_upper' ";
				}
			}
		}
		
		if(empty($id)){
			$save = $this->db->query("INSERT INTO student set $data");
			if($save){
				$inserted_id = $this->db->insert_id;
				
				// WhatsApp Automation Logic (Bilingual)
				if(is_feature_enabled('whatsapp_automation')){
					if(!empty($whatsapp_number) && !empty($course_id)){
						$course_qry = $this->db->query("SELECT course FROM courses WHERE id = $course_id");
						$course_name = ($course_qry->num_rows > 0) ? $course_qry->fetch_assoc()['course'] : 'our course';
						
						$base_url = "http://dotcmc.ct.ws";
						$link = $base_url . "/student_form.php?token=" . $form_token;
						
						$combined_msg = "*Greetings from DOT CMC Computer Education!*\n\nDear *" . ucwords(strtolower($name)) . "*,\nThank you for taking admission in *$course_name*.\n\n*DOT CMC कंप्यूटर एजुकेशन में आपका स्वागत है!*\n*$course_name* में एडमिशन लेने के लिए धन्यवाद।\n\nPlease submit your remaining details and documents securely by clicking the link below:\nकृपया अपनी बाकी की जानकारी और डाक्यूमेंट्स नीचे दी गई लिंक पर क्लिक करके सबमिट करें:\n\n👇 *Registration Link:*\n$link";
						
						$phone_esc = $this->db->real_escape_string($whatsapp_number);
						$msg_esc = $this->db->real_escape_string($combined_msg);
						
						$this->db->query("INSERT INTO whatsapp_queue (phone, message) VALUES ('$phone_esc', '$msg_esc')");
					}
				}
			}
		}else{
			$save = $this->db->query("UPDATE student set $data where id = $id");
		}
		
		if($save)
			return 1;
	}
	function delete_student(){
		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);
		
		// Fetch file paths before deleting
		$qry = $this->db->query("SELECT photo, marksheet FROM student WHERE id = ".$id);
		if($qry->num_rows > 0) {
			$row = $qry->fetch_assoc();
			$photo = $row['photo'];
			$marksheet = $row['marksheet'];
			
			// Get student directory path if photo exists
			$student_dir = '';
			if(!empty($photo) && file_exists($photo)){
				$student_dir = dirname($photo);
				@unlink($photo);
			}
			if(!empty($marksheet) && file_exists($marksheet)){
				if(empty($student_dir)) $student_dir = dirname($marksheet);
				@unlink($marksheet);
			}
			
			// Remove the student's folder if it exists
			if(!empty($student_dir) && is_dir($student_dir)){
				// Ensure it's not deleting the base directory
				if(strpos($student_dir, 'assets/uploads/students') !== false) {
					@rmdir($student_dir); 
				}
			}
		}

		$delete = $this->db->query("DELETE FROM student where id = ".$id);
		if($delete){
			return 1;
		}
	}
	function resend_link(){
		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);
		$new_token = md5(uniqid());
		$update = $this->db->query("UPDATE student set form_token = '$new_token', form_submitted = 0 where id = $id");
		if($update){
			$stu_qry = $this->db->query("SELECT name, whatsapp_number, course_id FROM student WHERE id = $id");
			if($stu_qry->num_rows > 0){
				$stu_data = $stu_qry->fetch_assoc();
				$name = $stu_data['name'];
				$whatsapp_number = $stu_data['whatsapp_number'];
				$course_id = $stu_data['course_id'];
				if(is_feature_enabled('whatsapp_automation')){
					if(!empty($whatsapp_number) && !empty($course_id)){
						$course_qry = $this->db->query("SELECT course FROM courses WHERE id = $course_id");
						$course_name = ($course_qry->num_rows > 0) ? $course_qry->fetch_assoc()['course'] : 'our course';
						
						$base_url = "http://dotcmc.ct.ws";
						$link = $base_url . "/student_form.php?token=" . $new_token;
						
						$combined_msg = "*Greetings from DOT CMC Computer Education!*\n\nDear *" . ucwords(strtolower($name)) . "*,\nHere is your new registration link for *$course_name*.\n\n*DOT CMC कंप्यूटर एजुकेशन में आपका स्वागत है!*\n*$course_name* के लिए आपकी नई रजिस्ट्रेशन लिंक यहाँ है।\n\nPlease submit your remaining details and documents securely by clicking the link below:\nकृपया अपनी बाकी की जानकारी और डाक्यूमेंट्स नीचे दी गई लिंक पर क्लिक करके सबमिट करें:\n\n👇 *Registration Link:*\n$link";
						
						$phone_esc = $this->db->real_escape_string($whatsapp_number);
						$msg_esc = $this->db->real_escape_string($combined_msg);
						
						$this->db->query("INSERT INTO whatsapp_queue (phone, message) VALUES ('$phone_esc', '$msg_esc')");
					}
				}
			}
			return 1;
		} else {
			return "Database Update Failed: " . $this->db->error;
		}
	}
	function save_fees(){
		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				if($k == 'total_fee'){
					$v = str_replace(',', '', $v);
				}
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		$check = $this->db->query("SELECT * FROM student_ef_list where ef_no ='$ef_no' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO student_ef_list set $data");
		}else{
			$save = $this->db->query("UPDATE student_ef_list set $data where id = $id");
		}
		if($save)
			return 1;
	}
	function delete_fees(){
		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);
		$delete = $this->db->query("DELETE FROM student_ef_list where id = ".$id);
		if($delete){
			return 1;
		}
	}
	function save_payment(){
		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
				if(!in_array($k, array('id')) && !is_numeric($k)){
					if($k == 'amount'){
						$v = str_replace(',', '', $v);
					}
					// Ensure mode is always present
					if($k == 'mode' && empty($v)){
						$v = 'Cash'; // Default to Cash if not set
					}
					if(empty($data)){
						$data .= " $k='$v' ";
					}else{
						$data .= ", $k='$v' ";
					}
				}
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO payments set $data");
			if($save)
				$id= $this->db->insert_id;
		}else{
			$save = $this->db->query("UPDATE payments set $data where id = $id");
		}
		if($save){
			// WhatsApp Automation Logic
			$stu = $this->db->query("SELECT s.name, s.contact, c.course FROM student_ef_list ef INNER JOIN student s on s.id = ef.student_id INNER JOIN courses c on c.id = ef.course_id WHERE ef.id = $ef_id");
			if($stu->num_rows > 0){
				$stu_data = $stu->fetch_assoc();
				$phone = $stu_data['contact'];
				if(!empty($phone)){
					$name = $stu_data['name'];
					$amt = str_replace(',', '', $_POST['amount']);
					// Adjust timezone for India if needed
					date_default_timezone_set('Asia/Kolkata');
					$date_time = date('d-M-Y h:i A');
					
					$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
					$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
					$link = $base_url . "/view_receipt.php?ef_id=" . $ef_id . "&pid=" . $id;
					
					$msg = "Hello, *$name*\n\nToday on *$date_time* you have submitted *INR $amt* at DOT-CMC Computer.\n\nLink for the payment is attached for reference : $link";
					
					$msg_esc = $this->db->real_escape_string($msg);
					$phone_esc = $this->db->real_escape_string($phone);
					$this->db->query("INSERT INTO whatsapp_queue (phone, message) VALUES ('$phone_esc', '$msg_esc')");
				}
			}
			return json_encode(array('ef_id'=>$ef_id, 'pid'=>$id,'status'=>1));
		}
	}
	function delete_payment(){
		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);
		$delete = $this->db->query("DELETE FROM payments where id = ".$id);
		if($delete){
			return 1;
		}
	}
	function get_payments(){
		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);
		$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
		$row = isset($_POST['start']) ? intval($_POST['start']) : 0;
		$rowperpage = isset($_POST['length']) ? intval($_POST['length']) : 10;
		$columnIndex = isset($_POST['order'][0]['column']) ? $_POST['order'][0]['column'] : 1;
		
		// Map index to column name
		$columnsMap = array(
			0 => 'index',
			1 => 'date_created',
			2 => 'id_no',
			3 => 'ef_no',
			4 => 'sname',
			5 => 'amount',
			6 => 'remarks',
			7 => 'action'
		);
		$columnName = isset($columnsMap[$columnIndex]) ? $columnsMap[$columnIndex] : 'date_created';
		$columnSortOrder = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'desc';
		$searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

		$searchQuery = " ";
		if($searchValue != ''){
			$searchQuery = " and (s.name like '%".$searchValue."%' or 
								  s.id_no like '%".$searchValue."%' or 
								  ef.ef_no like '%".$searchValue."%' or 
								  p.amount like '%".$searchValue."%' or
								  p.remarks like '%".$searchValue."%' ) ";
		}

		$totalRecordsQuery = $this->db->query("SELECT count(*) as allcount FROM payments");
		$totalRecords = $totalRecordsQuery->fetch_assoc()['allcount'];
		
		$totalRecordwithFilterQuery = $this->db->query("SELECT count(*) as allcount FROM payments p inner join student_ef_list ef on ef.id = p.ef_id inner join student s on s.id = ef.student_id WHERE 1 ".$searchQuery);
		$totalRecordwithFilter = $totalRecordwithFilterQuery->fetch_assoc()['allcount'];

		$orderQuery = " ORDER BY p.date_created desc ";
		if($columnName == 'date_created') $orderQuery = " ORDER BY p.date_created ".$columnSortOrder;
		if($columnName == 'id_no') $orderQuery = " ORDER BY s.id_no ".$columnSortOrder;
		if($columnName == 'ef_no') $orderQuery = " ORDER BY ef.ef_no ".$columnSortOrder;
		if($columnName == 'sname') $orderQuery = " ORDER BY s.name ".$columnSortOrder;
		if($columnName == 'amount') $orderQuery = " ORDER BY p.amount ".$columnSortOrder;
		if($columnName == 'remarks') $orderQuery = " ORDER BY p.remarks ".$columnSortOrder;

		$empQuery = "SELECT p.*,s.name as sname, ef.ef_no,s.id_no FROM payments p inner join student_ef_list ef on ef.id = p.ef_id inner join student s on s.id = ef.student_id WHERE 1 ".$searchQuery." ".$orderQuery." LIMIT ".$row.",".$rowperpage;
		$empRecords = $this->db->query($empQuery);
		
		$data = array();
		$i = $row + 1;
		while ($row = $empRecords->fetch_assoc()) {
			$action = '<button class="btn btn-sm btn-outline-primary view_payment" type="button" data-id="'.$row['id'].'" data-ef_id="'.$row['ef_id'].'">View</button> ';
			$action .= '<button class="btn btn-sm btn-outline-primary edit_payment" type="button" data-id="'.$row['id'].'">Edit</button> ';
			$action .= '<button class="btn btn-sm btn-outline-danger delete_payment" type="button" data-id="'.$row['id'].'">Delete</button>';
			
			$data[] = array(
				"index" => $i++,
				"date_created" => '<p> <b>'.date("M d,Y h:i A",strtotime($row['date_created'])).'</b></p>',
				"id_no" => '<p> <b>'.$row['id_no'].'</b></p>',
				"ef_no" => '<p> <b>'.$row['ef_no'].'</b></p>',
				"sname" => '<p> <b>'.ucwords($row['sname']).'</b></p>',
				"amount" => '<p> <b>'.number_format($row['amount'],2).'</b></p>',
				"remarks" => '<p> <b>'.$row['remarks'].'</b></p>',
				"action" => $action
			);
		}

		$response = array(
			"draw" => intval($draw),
			"iTotalRecords" => $totalRecords,
			"iTotalDisplayRecords" => $totalRecordwithFilter,
			"aaData" => $data,
			"data" => $data
		);

		return json_encode($response);
	}
	
	function save_expense(){
		// Auto-create table if missing so user doesn't need to run SQL manually
		$this->db->query("CREATE TABLE IF NOT EXISTS `expenses` (
		  `id` int(30) NOT NULL AUTO_INCREMENT,
		  `title` varchar(200) NOT NULL,
		  `amount` float NOT NULL,
		  `date_created` date NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id', 'action')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO expenses set $data");
		}else{
			$save = $this->db->query("UPDATE expenses set $data where id = $id");
		}
		if($save)
			return 1;
	}
	
	function delete_expense(){
		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);
		$delete = $this->db->query("DELETE FROM expenses where id = ".$id);
		if($delete)
			return 1;
	}
	
	function save_inquiry(){
		foreach($_POST as $k => $v){ if(!is_array($v)) $_POST[$k] = $this->db->real_escape_string($v); } extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id', 'action'))){
				if(empty($data)){
					$data .= " `$k`='$v' ";
				}else{
					$data .= ", `$k`='$v' ";
				}
			}
		}
		
		if(empty($id)){
			$save = $this->db->query("INSERT INTO inquiries set $data");
		}else{
			$save = $this->db->query("UPDATE inquiries set $data where id = $id");
		}
		
		if($save){
			$phone = $mobile;
			$msg_eng = "Greetings from *DOT CMC Computer Education*! 🎓\n\nHi *$name*, it was our pleasure to give you information about the *$course* course. For your better understanding, we are sharing the complete fee structure and the syllabus that will be covered under *$course*.\n\nYour total fees for this course will be: *$fees*\n\nFor any further assistance, you can contact us:\n📞 9685192443\n📞 07562-796465";
			
			$msg_hin = "*DOT CMC Computer Education* की तरफ से नमस्कार!\n\nनमस्ते *$name*, हमें आपको *$course* कोर्स के बारे में जानकारी देकर बहुत खुशी हुई। आपकी बेहतर जानकारी के लिए, हम आपके साथ फीस स्ट्रक्चर और पूरा सिलेबस शेयर कर रहे हैं जो *$course* में कवर किया जाएगा।\n\nइस कोर्स की आपकी कुल फीस होगी: *$fees*\n\nकिसी भी अन्य जानकारी या मदद के लिए आप हमसे संपर्क कर सकते हैं:\n📞 9685192443\n📞 07562-796465";
			
			$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
			$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
			
			$syllabus_file = ($course == 'DCA') ? 'DCA Syllabus.jpg' : 'pgdca Syllabus.jpg';
			if ($course == 'DCA') {
			    $fee_file = ($session == 'Jan') ? 'dca jan.jpg' : 'dca fees.jpg';
			} else {
			    $fee_file = ($session == 'Jan') ? 'pgdca jan.jpg' : 'pgdca fees.jpg';
			}
			
			$media_url1 = $base_url . '/assets/images/inquiries/' . rawurlencode($syllabus_file);
			$media_url2 = $base_url . '/assets/images/inquiries/' . rawurlencode($fee_file);
			
			$msg_eng_esc = $this->db->real_escape_string($msg_eng);
			$msg_hin_esc = $this->db->real_escape_string($msg_hin);
			$phone_esc = $this->db->real_escape_string($phone);
			$url1_esc = $this->db->real_escape_string($media_url1);
			$url2_esc = $this->db->real_escape_string($media_url2);
			
			if(is_feature_enabled('whatsapp_automation')){
				// Prevent duplicate clicks by checking if same phone got a message in the last 1 minute
				$check_dup = $this->db->query("SELECT id FROM whatsapp_queue WHERE phone = '$phone_esc' AND status = 'pending' AND created_at >= NOW() - INTERVAL 1 MINUTE");
				
				if($check_dup->num_rows == 0) {
					// Insert English message with media
					$this->db->query("INSERT INTO whatsapp_queue (phone, message, media_url1, media_url2) VALUES ('$phone_esc', '$msg_eng_esc', '$url1_esc', '$url2_esc')");
					// Insert Hindi message without media (so media is not sent twice)
					$this->db->query("INSERT INTO whatsapp_queue (phone, message, media_url1, media_url2) VALUES ('$phone_esc', '$msg_hin_esc', NULL, NULL)");
				}
			}
			
			return 1;
		}
	}
	function get_course_fees(){
		extract($_POST);
		$course = $this->db->real_escape_string($course_name);
		$qry = $this->db->query("SELECT * FROM courses where course = '$course' order by level asc");
		$data = array();
		while($row = $qry->fetch_assoc()){
			$data[] = $row;
		}
		return json_encode($data);
	}
}