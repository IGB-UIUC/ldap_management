<?php
	if(isset($_POST['username'])){
		include_once 'includes/main.inc.php';
		$filter = "(uid=".$_POST['username'].")";
		$results = $ldap->search($filter,__LDAP_PEOPLE_OU__);
		if($results && $results['count']>0){
			echo '1';
		} else {
			$filter = "(cn=".$_POST['username'].")";
			$results = $ldap->search($filter,__LDAP_GROUP_OU__);
			if($results && $results['count']>0){
				echo '2';
			} else {
				echo '0';
			}
		}
	}
	