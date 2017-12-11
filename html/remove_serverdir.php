<?php
	$title = "Add Server Directory";
	$sitearea = "groups";
	require_once 'includes/header.inc.php';

	$message="";
	if (isset($_POST['remove_dir'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['group']==""){
			$message .= html::error_message("Please select a group.");
		} elseif (!group::is_ldap_group($ldap,$_POST['group'])) {
			$message .= html::error_message("Invalid group name. Please stop trying to break my web interface.");
		}
		if($_POST['serverdir']==""){
			$message .= html::error_message("Invalid server/directory. Please stop trying to break my web interface.");
		}
		
		if($message == ""){
			$group = new group($ldap,$_POST['group']);
			$result = $group->remove_serverdir($_POST['serverdir']);
		
			if($result['RESULT'] == true){
				if($_POST['from']==""){
					$_POST['from']="group.php?gid=".$_POST['group'];
				}
				header("Location: ".$_POST['from']);
				exit();
			} else if ($result['RESULT'] == false) {
				$message = $result['MESSAGE'];
			}
		}
	} else if (isset($_POST['cancel_dir'])) {
		header("Location: ".$_POST['from']);
		unset($_POST);
		exit();
	}

	$from = "";	
	$gid = "";
	$groupusers = array();
	if(isset($_GET['gid']) || isset($_POST['group'])){
		$gid = isset($_GET['gid'])?$_GET['gid']:$_POST['group'];
		if(group::is_ldap_group($ldap,$gid)){
			$from = "group.php?gid=$gid";
		} else {
			header("location: index.php");
			exit();
		}
	} else {
		header("location: index.php");
		exit();
	}

	$groupshtml = "";
	if($gid != ""){
		$groupshtml = "<input type='hidden' name='group' value='$gid'/><label class='col-form-label'>$gid</label>";
	}
	
	$serverdir = "";
	$serverarr = array();
	if(isset($_REQUEST['serverdir'])){
		$serverdir = $_REQUEST['serverdir'];
		$serverarr = explode(":", $serverdir);
	} 
	if(count($serverarr) !=2) {
		header("location: index.php");
		exit();
	}
?>
<form class="mt-4" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Remove Server Directory from Group</legend>
		<hr>
		<div class="form-group row">
			<label class="col-sm-3 col-form-label" for="group-input">Group:</label>
			<div class="col-sm-5">
				<?php echo $groupshtml; ?>
			</div>
		</div>
		<div class="form-group row">
			<label class="col-sm-3 col-form-label" for="username-input">Server:</label>
			<div class="col-sm-5">
				<input type="hidden" name="serverdir" value="<?php echo $serverdir; ?>"/>
				<label class="col-form-label"><?php echo $serverarr[0]; ?></label>
			</div>
		</div>
		<div class="form-group row">
			<label class="col-sm-3 col-form-label" for="username-input">Directory:</label>
			<div class="col-sm-5">
				<label class="col-form-label"><?php echo $serverarr[1]; ?></label>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-sm-5 offset-sm-3">
				<input type="hidden" name="from" value="<?php echo $from; ?>"/>
				<div class="btn-group">
					<input class="btn btn-danger" type="submit" name="remove_dir" value="Remove directory" /> <input class="btn btn-light" type="submit" name="cancel_dir" value="Cancel" />
				</div>
			</div>
		</div>
	</fieldset>
</form>
<script type="text/javascript">
	$(document).ready(function(){
		$(".server-select").select2({
			placeholder: "Please select a server",
			width: 'element'
		});
	});
</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>