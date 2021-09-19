<?php

function db_connect() {
	require_once __DIR__ . "/../../var.php";
	require_once(CONF_MYSQL);
	require_once __DIR__ . "/../function_commun.php";

	$_SESSION["OCS"]["readServer"] = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);

	return $_SESSION["OCS"]["readServer"];
}


class ComponentsNotification 
{
	public $html_part_addition;
	public $html_part_remove;

	public function get_html_info_addition($list_hardware, $connection, $hard_component) {
		if ($this->html_part_addition == '') {
				$this->html_part_addition .= "
				<hr>
				<center>
					<h1> New Hardware Components Has been Added <h1>
				</center>
				<hr>";
		}
		$this->html_part_addition .= "
				<br><center>
				<h2> $hard_component <h2>	
				<table border='collapse' bgcolor='B8B0AE'>
				<tr>\n";

		$reference_array = array_key_first($list_hardware);
			foreach ($list_hardware[$reference_array] as $label => $value) {
				$this->html_part_addition .= "<th>$label</th>\n";
			} 
		$this->html_part_addition .= "</tr>\n<tr>";
	
		$id_asset = NULL; 
		for ($i = $reference_array; $i <= array_key_last($list_hardware); $i++) {
			foreach ($list_hardware["$i"] as $feature => $value) {
				if ($value == 'Unknown' or $value == '') { 
					$this->html_part_addition .= "<td style='text-align:center'> Uninformed </td>\n";
					continue;
				}
				if ($feature == "ASSET") {
					$sql = "SELECT USERID FROM hardware WHERE ID = '$value'";
					$result_id = mysqli_query($connection, $sql);
					$id_asset = mysqli_fetch_array($result_id);

					if (isset($id_asset)) {	
						$this->html_part_addition .= "<td style='text-align:center'>" . $id_asset['USERID'] . "</td><td bgcolor='green' style='text-align:center'> Added </td>\n";
					} else {
						$this->html_part_addition .= "<td style='text-align:center'> Not Found </td><td bgcolor='green' style='text-align:center'> Added </td>\n";
					}
				} else {
					$this->html_part_addition .= "<td style='text-align:center'>$value</td>\n";
				}
		}
			$this->html_part_addition .= "</tr><tr>";
		}
		$this->html_part_addition .= "<td bgcolor='#4169e1' style='text-align:center'>Total: " . count($list_hardware) . "</td></tr>";
		$this->html_part_addition .= "</table></center><br>";
	}
	
	public function get_html_info_removed($hardware_cache, $connection, $hard_component) {
		if ($this->html_part_remove == '') {
				$this->html_part_remove .= "
					<br>
					<hr>
					<center>
						<h1> Hardware Components has been Removed <h1>
					</center>
					<hr>";
		}
		$this->html_part_remove .= "
				<br><center>
					<h2> $hard_component <h2>	
					<table border='1' bgcolor='B8B0AE'>
					<tr>\n";
		$critical_situation = 0;	
		$reference_array = array_key_first($hardware_cache);
		foreach ($hardware_cache[$reference_array] as $label => $value) {
			$this->html_part_remove .= "<th>$label</th>\n";
		}
		$this->html_part_remove .= "</tr>\n<tr>";

		$id_asset = NULL;
		for ($i = $reference_array; $i <= array_key_last($hardware_cache); $i++) {
			foreach ($hardware_cache["$i"] as $feature => $value) {
				if ($value == 'Unknown' or $value == '') {
					$this->html_part_remove .= "<td style='text-align:center'> Uninformed </td>\n";
					continue;
				}
				if ($feature == "ASSET") {
					$sql = "SELECT USERID FROM id_assets_cache WHERE ID = '$value'";
					$result_id = mysqli_query($connection, $sql);
					$id_asset = mysqli_fetch_array($result_id);

					if (isset($id_asset)) {
						$this->html_part_remove .= "<td style='text-align:center'>" . $id_asset['USERID'] . "</td><td bgcolor='#e31111' style='text-align:center'> Removed </td>\n";
					} else {
						$this->html_part_remove .= "<td style='text-align:center'> Not Found </td><td bgcolor='#e31111' style='text-align:center'> Removed </td>\n";
					}
				} else {
					$this->html_part_remove .= "<td style='text-align:center'>$value</td>\n";
				}
			}
			$this->html_part_remove .= "</tr><tr>";
		}
		$this->html_part_remove .= "<td bgcolor='#4169e1' style='text-align:center'>Total: " . count($hardware_cache) . "</td></tr>";
		$this->html_part_remove .= "</table></center><br>";
			
	}

	public function get_cpus() {
		$connection = db_connect();
		$sql = "SELECT * FROM cpus";
		$result_cpus = mysqli_query($connection, $sql);
		
		$list_cpus = array();
		while ($item_cpu = mysqli_fetch_array($result_cpus)) {
			$list_cpus[$item_cpu['ID']]['MANUFACTURER'] = $item_cpu['MANUFACTURER'];
			$list_cpus[$item_cpu['ID']]['TYPE'] = $item_cpu['TYPE'];
			$list_cpus[$item_cpu['ID']]['ASSET'] = $item_cpu['HARDWARE_ID'];
		}

		$list_cpus_cache = array();
		$sql = "SELECT * FROM cpus_cache";
		$result_query = mysqli_query($connection, $sql);
		while ($item_cpu = mysqli_fetch_array($result_query)) {
			$list_cpus_cache[$item_cpu['ID']]['MANUFACTURER'] = $item_cpu['MANUFACTURER'];
			$list_cpus_cache[$item_cpu['ID']]['TYPE'] = $item_cpu['TYPE'];
			$list_cpus_cache[$item_cpu['ID']]['ASSET'] = $item_cpu['H_ID'];
		}

		$added_cpus = array();
		$removed_cpus = array();
		$added_cpus = array_diff_key($list_cpus, $list_cpus_cache);
		if ($added_cpus != NULL) {
			$this->get_html_info_addition($added_cpus, $connection, $hard_component = "Cpu(s)");
		}

		$removed_cpus = array_diff_key($list_cpus_cache, $list_cpus);
		if ($removed_cpus != NULL) {
			$this->get_html_info_removed($removed_cpus, $connection, $hard_component = "Cpu(s)");
		}

		if ($added_cpus != NULL or $removed_cpus != NULL) {
			$sql = "TRUNCATE TABLE cpus_cache;";
			$sql .= "REPLACE INTO cpus_cache(ID, H_ID, TYPE, MANUFACTURER) SELECT ID, HARDWARE_ID, TYPE, MANUFACTURER FROM cpus;";
			mysqli_multi_query($connection, $sql);
		}
	}

	public function get_memories() {
		$connection = db_connect();
		$sql = "SELECT * FROM memories";
		$result_memories = mysqli_query($connection, $sql);
		
		$list_memories = array();
		while ($item_memory = mysqli_fetch_array($result_memories)) {
			$list_memories[$item_memory['ID']]['TYPE'] = $item_memory['TYPE'];
			$list_memories[$item_memory['ID']]['CAPACITY (MB)'] = $item_memory['CAPACITY'];
			$list_memories[$item_memory['ID']]['ASSET'] = $item_memory['HARDWARE_ID'];
		}

		$list_memories_cache = array();
		$sql = "SELECT * FROM memories_cache";
		$result_query = mysqli_query($connection, $sql);
		while ($item_memory = mysqli_fetch_array($result_query)) {
			$list_memories_cache[$item_memory['ID']]['TYPE'] = $item_memory['TYPE'];
			$list_memories_cache[$item_memory['ID']]['CAPACITY (MB)'] = $item_memory['CAPACITY'];
			$list_memories_cache[$item_memory['ID']]['ASSET'] = $item_memory['H_ID'];
		}

		
		$added_memories = array();
		$removed_memories = array();
		
		$added_memories = array_diff_key($list_memories, $list_memories_cache);
		
		if ($added_memories != NULL) {
			$this->get_html_info_addition($added_memories, $connection, $hard_component = "Memory(ies)");
		}
		
		$removed_memories = array_diff_key($list_memories_cache, $list_memories);	

		if ($removed_memories != NULL) {
			$this->get_html_info_removed($removed_memories, $connection, $hard_component = "Memory(ies)");
		}

		if ($added_memories != NULL or $removed_memories != NULL) {
			$sql = "TRUNCATE TABLE memories_cache;";
			$sql .= "REPLACE INTO memories_cache(ID, H_ID, CAPACITY, TYPE) SELECT ID, HARDWARE_ID, CAPACITY, TYPE FROM memories;";
			mysqli_multi_query($connection, $sql);
		}
	}

	public function get_monitors() {
		$connection = db_connect();
		$sql = "SELECT * FROM monitors";
		$result_monitors = mysqli_query($connection, $sql);
		
		$list_monitors = array();
		while($item_monitors = mysqli_fetch_array($result_monitors)) {
			$list_monitors[$item_monitors['ID']]['MANUFACTURER'] = $item_monitors['MANUFACTURER'];		
			$list_monitors[$item_monitors['ID']]['DESCRIPTION'] = $item_monitors['DESCRIPTION'];		
			$list_monitors[$item_monitors['ID']]['ASSET'] = $item_monitors['HARDWARE_ID'];		
		}
		
		$list_monitors_cache = array();
		$sql = "SELECT * FROM monitors_cache";
		$result_query = mysqli_query($connection, $sql);
		while ($item_monitor = mysqli_fetch_array($result_query)) {
			$list_monitors_cache[$item_monitor['ID']]['MANUFACTURER'] = $item_monitor['MANUFACTURER'];
			$list_monitors_cache[$item_monitor['ID']]['DESCRIPTION'] = $item_monitor['DESCRIPTION'];		
			$list_monitors_cache[$item_monitor['ID']]['ASSET'] = $item_monitor['H_ID'];		
		}
		
		$added_monitors = array();
		$removed_monitors = array();

		$added_monitors = array_diff_key($list_monitors, $list_monitors_cache);
		if ($added_monitors != NULL) {
			$this->get_html_info_addition($added_monitors, $connection, $hard_component = "Monitor(s)");
		}
		
		$removed_monitors = array_diff_key($list_monitors_cache, $list_monitors);
		if ($removed_monitors != NULL) {
			$this->get_html_info_removed($removed_monitors, $connection, $hard_component = "Monitor(s)");
		}
		
		if ($added_monitors != NULL or $removed_monitors != NULL)
			$sql = "TRUNCATE TABLE monitors_cache;";
			$sql .= "REPLACE INTO monitors_cache(ID, H_ID, MANUFACTURER, DESCRIPTION) SELECT ID, HARDWARE_ID, MANUFACTURER, DESCRIPTION FROM monitors;";
			mysqli_multi_query($connection, $sql);
	}

	// In this method has been used HARDWARE_ID as a key for array due 
	// a particular conditions of database. I don't extend me
	// because this explanation will be too much long. :)
	public function get_disks() {
		$connection = db_connect();
		$sql = "SELECT * FROM drives";
		$result_disks = mysqli_query($connection, $sql);
		
		$list_disks = array();
		while ($item_disks = mysqli_fetch_array($result_disks)) {
			$list_disks[$item_disks['HARDWARE_ID']]['TYPE'] = $item_disks['TYPE'];
			$list_disks[$item_disks['HARDWARE_ID']]['FILESYS'] = $item_disks['FILESYSTEM'];
			$list_disks[$item_disks['HARDWARE_ID']]['TOTAL (MB)'] = $item_disks['TOTAL'];
			$list_disks[$item_disks['HARDWARE_ID']]['FREE (MB)'] = $item_disks['FREE'];
			// Evaluate the disk usage
			$list_disks[$item_disks['HARDWARE_ID']]['USED (%)'] = intval(((floatval($item_disks['TOTAL']) - 
				floatval($item_disks['FREE'])) * 100) / $item_disks['TOTAL']);
			$list_disks[$item_disks['HARDWARE_ID']]['ASSET'] = $item_disks['HARDWARE_ID'];
		}
		
		$sql = "SELECT * FROM drives_cache";
		$result_query = mysqli_query($connection, $sql);
		
		$list_disks_cache = array();
		while ($item_disks = mysqli_fetch_array($result_query)) {
			$list_disks_cache[$item_disks['H_ID']]['TYPE'] = $item_disks['TYPE'];
			$list_disks_cache[$item_disks['H_ID']]['FILESYSTEM'] = $item_disks['FILESYSTEM'];
			$list_disks_cache[$item_disks['H_ID']]['TOTAL (MB)'] = $item_disks['TOTAL'];
			$list_disks_cache[$item_disks['H_ID']]['FREE (MB)'] = $item_disks['FREE'];
			$list_disks_cache[$item_disks['H_ID']]['ASSET'] = $item_disks['H_ID'];
		}
		
		$added_disks = array();
		$removed_disks = array();

		$added_disks = array_diff_key($list_disks, $list_disks_cache);
		if ($added_disks != NULL) {
			$this->get_html_info_addition($added_disks, $connection, $hard_component = "Disk(s)");
		}
		
		$removed_disks = array_diff_key($list_disks_cache, $list_disks);
		if ($removed_disks != NULL) {
			$this->get_html_info_removed($removed_disks, $connection, $hard_component = "Disk(s)");
		}

		if ($added_disks != NULL or $removed_disks != NULL) {
			$sql = "TRUNCATE TABLE drives_cache;";
			$sql .= "REPLACE INTO drives_cache(ID, H_ID, TYPE, TOTAL, FREE, FILESYSTEM) SELECT ID, HARDWARE_ID, TYPE, TOTAL, FREE, FILESYSTEM FROM drives;";
			mysqli_multi_query($connection, $sql);
		}
	}

	public function get_videos() {
		$connection = db_connect();
		$sql = "SELECT * FROM videos";
		$result_videos = mysqli_query($connection, $sql);

		$list_videos = array();
		while($item_videos = mysqli_fetch_array($result_videos)) {
			$list_videos[$item_videos['ID']]['NAME'] = $item_videos['NAME'];		
			$list_videos[$item_videos['ID']]['MEMORY'] = $item_videos['MEMORY'];		
			$list_videos[$item_videos['ID']]['ASSET'] = $item_videos['HARDWARE_ID'];		
		}
		
		$sql = "SELECT * FROM videos_cache";
		$result_query = mysqli_query($connection, $sql);

		$list_videos_cache = array();
		while($item_videos = mysqli_fetch_array($result_query)) {
			$list_videos_cache[$item_videos['ID']]['NAME'] = $item_videos['NAME'];		
			$list_videos_cache[$item_videos['ID']]['MEMORY'] = $item_videos['MEMORY'];		
			$list_videos_cache[$item_videos['ID']]['ASSET'] = $item_videos['H_ID'];		
		}
		
		$added_videos = array();
		$removed_videos = array();
		
		$added_videos = array_diff_key($list_videos, $list_videos_cache);	
		if ($added_videos != NULL) {
			$this->get_html_info_addition($added_videos, $connection, $hard_component = "C. Video(s)");
		}
		
		$removed_videos = array_diff_key($list_videos_cache, $list_videos);	
		if ($removed_videos != NULL) {
			$this->get_html_info_removed($removed_videos, $connection, $hard_component = "B. Video(s)");
		}

		if ($added_videos != NULL or $removed_videos != NULL) {
			$sql = "TRUNCATE TABLE videos_cache;";
			$sql .= "REPLACE INTO videos_cache(ID, H_ID, NAME, MEMORY) SELECT ID, HARDWARE_ID, NAME, MEMORY FROM videos;";
			mysqli_multi_query($connection, $sql);
		}
	}

	public function update_id_assets() {
		$connection = db_connect();
		$sql = "TRUNCATE TABLE id_assets_cache;";
		$sql .= "REPLACE INTO id_assets_cache(ID, USERID) SELECT ID, USERID FROM hardware;";
		mysqli_multi_query($connection, $sql);
	}
}

