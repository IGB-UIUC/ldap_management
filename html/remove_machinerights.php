<?php
	$title = "Remove Host Access";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['remove_user'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['username']==""){
			$message .= html::error_message("Please select a user.");
		} elseif (!user::is_ldap_user($ldap,$_POST['username'])) {
			$message .= html::error_message("Invalid username. Please stop trying to break my web interface.");
		}
		$user = new user($ldap,$_POST['username']);
		
		if($_POST['host']==""){
			$message .= html::error_message("Please select a host.");
		} elseif (!$ldap->is_ldap_host($_POST['host']) && !in_array($_POST['host'], $user->get_machinerights())) {
			$message .= html::error_message("Invalid host name. Please stop trying to break my web interface.");
		}
		
		if($message == ""){
			$result = $user->remove_machinerights($_POST['host']);
		
			if($result['RESULT'] == true){
				header("Location: user.php?uid=".$result['uid']);
			} else if ($result['RESULT'] == false) {
				$message = $result['MESSAGE'];
			}
		}
	} else if (isset($_POST['cancel_user'])) {
		header("Location: user.php?uid=".$_POST['username']);
		unset($_POST);
	}
	
	$uid = "";
	$userrights = array();
	if(isset($_GET['uid'])){
		$uid = $_GET['uid'];
		$usertoadd = new user($ldap,$uid);
		$userrights = $usertoadd->get_machinerights();
	}
	
	$hid = "";
	$machineusers = array();
	if(isset($_GET['hid'])){
		$hid = $_GET['hid'];
		$machineusers = $ldap->search("(host=".$hid.")", __LDAP_PEOPLE_OU__, array('uid'));
	}
	
	$usershtml = "";
	$users = $ldap->get_all_users();
	if($uid != ""){
		$usershtml = "<input type='hidden' name='username' value='$uid'/><label class='control-label'>$uid</label>";
	} else {
		$usershtml .= "<select name='username' class='form-control username-select'><option></option>";
		foreach($users as $user){
			
			$usershtml .= "<option value='".$user."'";
			if($uid == $user){
				$usershtml .= " selected";
			}
			$usershtml .= ">".$user."</option>";
		}
		$usershtml .= "</select>";
	}
	
	$hostshtml = "";
	if($hid != ""){
		$hostshtml = "<input type='hidden' name='host' value='$hid'/><label class='control-label'>$hid</label>";
	} else {
		$hosts = host::get_all_hosts($ldap);
		
		$igb = array();
		$biotech = array();
		$other = array();
		foreach($hosts as $host){
			if(!$userrights || in_array($host['name'],$userrights)){
				if(strpos($host['name'],"biotec") !== false){
					$biotech[] = $host['name'];
				} else if(strpos($host['name'],"igb") !== false){
					$igb[] = $host['name'];
				} else {
					$other[] = $host['name'];
				}
			}
		}
		sort($igb);
		sort($biotech);
		sort($other);
		
		$hostshtml .= "<select name='host' class='form-control host-select'><option></option>";
		$hostshtml .= "<optgroup label='IGB Hosts'>";
		foreach($igb as $host){
			$hostshtml .= "<option value='".$host."'";
			if($hid == $host){
				$hostshtml .= " selected";
			}
			$hostshtml .= ">".$host."</option>";
		}
		$hostshtml .= "/<optgroup>";
		$hostshtml .= "<optgroup label='Biotech Hosts'>";
		foreach($biotech as $host){
			$hostshtml .= "<option value='".$host."'";
			if($hid == $host){
				$hostshtml .= " selected";
			}
			$hostshtml .= ">".$host."</option>";
		}
		$hostshtml .= "/<optgroup>";
		if(count($other)!=0){
			$hostshtml .= "<optgroup label='Other Hosts'>";
			foreach($other as $host){
				$hostshtml .= "<option value='".$host."'";
				if($hid == $host){
					$hostshtml .= " selected";
				}
				$hostshtml .= ">".$host."</option>";
			}
			$hostshtml .= "/<optgroup>";
		}
		$hostshtml .= "</select>";
	}
?>
<form class="form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Revoke Machine Rights</legend>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="username-input">Username:</label>
			<div class="col-sm-4">
				<?php echo $usershtml; ?>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="group-input">Host:</label>
			<div class="col-sm-4">
				<?php echo $hostshtml; ?>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-4 col-sm-offset-2">
				<div class="btn-group">
					<input class="btn btn-danger" type="submit" name="remove_user" value="Remove Access" /> <input class="btn btn-default" type="submit" name="cancel_user" value="Cancel" />
				</div>
			</div>
		</div>
	</fieldset>
</form>
<script type="text/javascript">
	$(document).ready(function(){
		$(".username-select").select2({
			placeholder: "Please select a user"
		});
		$(".host-select").select2({
			placeholder: "Please select a host"
		});
	});
</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>