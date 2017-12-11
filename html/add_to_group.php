<?php
	$title = "Add to Group";
	$sitearea = "groups";
	require_once 'includes/header.inc.php';

	$message="";
	if (isset($_POST['add_user'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['username']==""){
			$message .= html::error_message("Please select a user.");
		} elseif (!user::is_ldap_user($ldap,$_POST['username'])) {
			$message .= html::error_message("Invalid username. Please stop trying to break my web interface.");
		}
		if($_POST['group']==""){
			$message .= html::error_message("Please select a group.");
		} elseif (!group::is_ldap_group($ldap,$_POST['group'])) {
			$message .= html::error_message("Invalid group name. Please stop trying to break my web interface.");
		}
		
		if($message == ""){
			$group = new group($ldap,$_POST['group']);
			$result = $group->add_user($_POST['username']);
		
			if($result['RESULT'] == true){
				if($_POST['from']==""){
					$_POST['from']="user.php?uid=".$_POST['username'];
				}
				header("Location: ".$_POST['from']);
			} else if ($result['RESULT'] == false) {
				$message = $result['MESSAGE'];
			}
		}
	} else if (isset($_POST['cancel_user'])) {
		header("Location: ".$_POST['from']);
		unset($_POST);
	}
	
	$from = "";
	$uid = "";
	$usergroups = array();
	if(isset($_GET['uid']) && user::is_ldap_user($ldap,$_GET['uid'])){
		$uid = $_GET['uid'];
		$usertoadd = new user($ldap,$uid);
		$usergroups = $usertoadd->get_groups();
		$from = "user.php?uid=$uid";
	}
	
	$gid = "";
	$groupusers = array();
	if(isset($_GET['gid']) && group::is_ldap_group($ldap,$_GET['gid'])){
		$gid = $_GET['gid'];
		$grouptoadd = new group($ldap,$gid);
		$groupusers = $grouptoadd->get_users();
		$from = "group.php?gid=$gid";
	}
	
	$usershtml = "";
	$users = user::get_all_users($ldap);
	if($uid != ""){
		$usershtml = "<input type='hidden' name='username' value='$uid'/><label class='col-form-label'>$uid</label>";
	} else {
		$usershtml .= "<select name='username' class='form-control username-select'><option></option>";
		foreach($users as $user){
			if(!in_array($user,$groupusers)){
				$usershtml .= "<option value='".$user."'";
				if($uid == $user){
					$usershtml .= " selected";
				}
				$usershtml .= ">".$user."</option>";
			}
		}
		$usershtml .= "</select>";
	}
	
	$groupshtml = "";
	$groups = group::get_search_groups($ldap,"",-1,-1,'name',"true",1);
	if($gid != ""){
		$groupshtml = "<input type='hidden' name='group' value='$gid'/><label class='col-form-label'>$gid</label>";
	} else {
		$groupshtml .= "<select name='group' class='form-control group-select'><option></option>";
		foreach($groups as $group){
			if(!in_array($group['name'], $usergroups)){
				$groupshtml .= "<option value='".$group['name']."'";
				if($gid == $group['name']){
					$groupshtml .= " selected";
				}
				$groupshtml .= ">".$group['name']."</option>";
			}
		}
		$groupshtml .= "</select>";
	}
?>
<form class="mt-4" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Add User to Group</legend>
		<hr>
		<?php if($uid != "" && count($usertoadd->get_groups()) > 15){
			echo html::warning_message('<span class="fa fa-exclamation-triangle"> </span> After this operation, '.$uid.' will be a member of >16 groups. This may cause issues with NFS.');
		} ?>
		<div class="form-group row">
			<label class="col-sm-3 col-form-label" for="username-input">Username:</label>
			<div class="col-sm-5">
				<?php echo $usershtml; ?>
			</div>
		</div>
		<div class="form-group row">
			<label class="col-sm-3 col-form-label" for="group-input">Group:</label>
			<div class="col-sm-5">
				<?php echo $groupshtml; ?>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-sm-5 offset-sm-3">
				<input type="hidden" name="from" value="<?php echo $from; ?>"/>
				<div class="btn-group">
					<input class="btn btn-success" type="submit" name="add_user" value="Add user" /> <input class="btn btn-light" type="submit" name="cancel_user" value="Cancel" />
				</div>
			</div>
		</div>
	</fieldset>
</form>
<script type="text/javascript">
	$(document).ready(function(){
		$(".username-select").select2({
			placeholder: "Please select a user",
			width: 'element'
		});
		$(".group-select").select2({
			placeholder: "Please select a group",
			width: 'element'
		});
	});
</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>