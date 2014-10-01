<?php
include('db_setup.php');
	if (!empty($_POST)){
		$name = $_POST['search_name'];
		$link = db_default_connection();
		mysql_select_db("mitats+families", $link);
		$sql = mysql_query("SELECT family, nodeID FROM nodes WHERE name LIKE '%$name%' OR email LIKE '%$name%'");
		$family = mysql_fetch_array($sql);
		mysql_close($link);
		if (!$family){
			header("Location: ../index.php");
		}
		else{
			$famID = $family['family'];
			$nodeID = $family['nodeID'];
			header("Location: /~mitats/family/family.php?family=$famID&node=$nodeID");
		}
	}
?>