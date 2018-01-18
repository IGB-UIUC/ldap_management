<?php
	$title = "User List";
	$sitearea = "users";
	require_once 'includes/header.inc.php';

	$get_array = array();
	$start = 0;
	$count = 30;
	
	if ( isset($_GET['start']) && is_numeric($_GET['start']) ){
		$start = $_GET['start'];
		$get_array['start'] = $start;
	}
	
	$search = "";
	if ( isset($_GET['search']) ){
		$search = trim($_GET['search']);
		$get_array['search'] = $search;
	}
	
	$sort = 'username';
	if(isset($_GET['sort'])){
		$sort = $_GET['sort'];
		$get_array['sort'] = $sort;
	}
	
	$asc = "true";
	if(isset($_GET['asc'])){
		$asc = $_GET['asc'];
		$get_array['asc'] = $asc;
	}
	
	$filter = 'none';
	if(isset($_GET['filter'])){
		$filter = $_GET['filter'];
		$get_array['filter'] = $filter;
	}
	setcookie("lastUserSearchSort",$sort);
	setcookie("lastUserSearchAsc",$asc);
	setcookie("lastUserSearchFilter",$filter);
	setcookie("lastUserSearch",$search);
	$all_users = user::get_search_users($ldap,$search,$start,$count,$sort,$asc,$filter);
	$num_users = user::get_search_users_count($ldap,$search,$filter);
	$pages_url = $_SERVER['PHP_SELF']."?".http_build_query($get_array);
	$pages_html = html::get_pages_html($pages_url,$num_users,$start,$count);
	$users_html = "";
	$user_count = 0;
	
	$users_html = html::get_users_rows($all_users,($filter=='expiring'||$filter=='expired'));
	?>
	
	<h3 class="mt-4">List of Users</h3>
	
	<?php if(isset($_GET['message'])){
		echo $_GET['message'];
	} ?>
	<div class="card">
		<div class="card-body">
			<form method="get" action='<?php echo $_SERVER['PHP_SELF']; ?>' class="form-inline">
				<div class="form-group">
					<input type="text" name="search" class="form-control" value="<?php if (isset($search)){echo $search; } ?>" placeholder="Search" />
				</div>
				
				<input type="hidden" name="sort" value="<?php echo $sort; ?>" />
				<input type="hidden" name="asc" value="<?php echo $asc; ?>" />
				<input type="submit" class="btn btn-primary ml-1 mr-auto" value="Go" />
				
				<div class="btn-group">
					<button type="button" class="btn <?php if($filter=='expiring'){echo 'btn-warning';}else{echo 'btn-light';} ?>" id="expiring-button" onclick="filter_table('expiring')"><span class="fa fa-clock-o"></span> Expiring</button>
					<button type="button" class="btn <?php if($filter=='expired'){echo 'btn-danger';}else{echo 'btn-light';} ?>" id="expired-button" onclick="filter_table('expired')"><span class="fa fa-clock-o"></span> Expired</button>
					<button type="button" class="btn <?php if($filter=='left'){echo 'btn-warning';}else{echo 'btn-light';} ?>" id="ad-button" onclick="filter_table('left')"><span class="fa fa-graduation-cap"></span> Left Campus</button>
					<button type="button" class="btn <?php if($filter=='noncampus'){echo 'btn-info';}else{echo 'btn-light';} ?>" id="noncampus-button" onclick="filter_table('noncampus')"><span class="fa fa-graduation-cap"></span> Non-Campus</button>
				</div>
			</form>
		</div>
		<form action="multi_user_action.php" method="post">
			<table class="table table-sm table-striped table-responsive-md table-igb-bordered mb-0">
				<thead>
					<tr>
						<th><input type="checkbox" id="select-all"/></th>
						<th class="sortable-th pl-2" onclick="sort_table('username')">NetID<?php echo html::sort_icon('username', $sort, $asc); ?></th>
						<th class="sortable-th" onclick="sort_table('name')">Name<?php echo html::sort_icon('name', $sort, $asc); ?></th>
						<th class="sortable-th" onclick="sort_table('email')">Email<?php echo html::sort_icon('email', $sort, $asc); ?></th>
						<th class="sortable-th" onclick="sort_table('emailforward')">Forwarding Email<?php echo html::sort_icon('emailforward', $sort, $asc); ?></th>
						<?php if ($filter == 'expired' || $filter == 'expiring'){ ?>
							<th class="sortable-th" onclick="sort_table('shadowexpire')">Expiration<?php echo html::sort_icon('shadowexpire',$sort,$asc); ?></th>
						<?php } ?>
					</tr>
				</thead>
				<tbody>
					<?php echo $users_html; ?>
				</tbody>
			</table>
			
			<div class="card-footer text-muted">
				<input type="hidden" name="sort" value="<?php echo $sort; ?>" />
				<input type="hidden" name="asc" value="<?php echo $asc; ?>" />
				<input type="hidden" name="search" value="<?php echo $search; ?>" />
				<input type="hidden" name="start" value="<?php echo $start; ?>" />
				<input type="hidden" name="filter" value="<?php echo $filter; ?>" />
				<label>All checked: </label> <select class="custom-select" name="action"><option hidden>Select an action...</option><option value="add-to-group">Add to group</option></select> <input type="submit" class="btn btn-primary"><br>
				<span class="fa fa-clock-o text-warning"> </span>=expiration set &nbsp;
				<span class="fa fa-clock-o text-danger"> </span>=expired &nbsp;
				<span class="fa fa-graduation-cap text-warning"> </span>=left campus &nbsp;
				<span class="fa fa-graduation-cap text-info"> </span>=non-campus &nbsp;
				<span class='fa fa-hdd-o text-success' title='User has Crashplan'></span>=has crashplan &nbsp;
				<span class='fa fa-key text-danger' title='Password Expired'></span>=password expired &nbsp;
				<span class='fa fa-book text-info' title='Classroom User'></span>=classroom user
			</div>
		</form>
	</div>
			<?php echo $pages_html; ?>

	<script type="text/javascript">
		$('#expiring-button').on('click',function(){
			var $this = $(this);
			toggleClasses($this,'btn-light','btn-warning active');
		});
		$('#expired-button').on('click',function(){
			var $this = $(this);
			toggleClasses($this,'btn-light','btn-danger active');
		});
		$('#ad-button').on('click',function(){
			var $this = $(this);
			toggleClasses($this,'btn-light','btn-warning active');
		});
		$('#noncampus-button').on('click',function(){
			var $this = $(this);
			toggleClasses($this,'btn-light','btn-info active');
		});
		$('#select-all').change(function(){
			var $checkboxes = $(this).closest("form").find("input[type=checkbox]");
			if(this.checked){
				$checkboxes.prop("checked", true);
			} else {
				$checkboxes.prop("checked", false);
			}
		})
	</script>
<?php
	require_once 'includes/footer.inc.php';