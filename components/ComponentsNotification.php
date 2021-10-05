<?php

function filter_array_cells($array) {
	foreach(array_keys($array) as $array_key) {
		foreach($array[$array_key] as $key => $value) {
			if ($value == NULL or $value == '') {
				unset($array[$array_key]);
			}
		}
	} 	

	return $array;
}


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
		// for ($i = $reference_array; $i <= array_key_last($list_hardware); $i++) {
		foreach (array_keys($list_hardware) as $key) {
			foreach ($list_hardware["$key"] as $feature => $value) {
				if ($value == 'Unknown' or $value == '') { 
					$this->html_part_addition .= "<td style='text-align:center'> Uninformed </td>\n";
					continue;
				}
				if ($feature == "ASSET") {
					$sql = "SELECT USERID FROM hardware WHERE ID =" . trim($value);
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
		//for ($i = $reference_array; $i <= array_key_last($hardware_cache); $i++) {
		foreach (array_keys($hardware_cache) as $key) {
			foreach ($hardware_cache["$key"] as $feature => $value) {
				if ($value == 'Unknown' or $value == '') {
					$this->html_part_remove .= "<td style='text-align:center'> Uninformed </td>\n";
					continue;
				}
				if ($feature == "ASSET") {
					$sql = "SELECT USERID FROM id_assets_cache WHERE ID" . trim($value);
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
		$i = 1;
		while ($item_cpu = mysqli_fetch_array($result_cpus)) {
			if (array_key_exists($item_cpu['HARDWARE_ID'], $list_cpus)){
				$array_key = $item_cpu['HARDWARE_ID'] . str_repeat('*', $i++);
			} else {
				$array_key = $item_cpu['HARDWARE_ID'];
			}
				$list_cpus[$array_key]['MANUFACTURER'] = $item_cpu['MANUFACTURER'];
				$list_cpus[$array_key]['TYPE'] = $item_cpu['TYPE'];
				$list_cpus[$array_key]['ASSET'] = $item_cpu['HARDWARE_ID'];
		}

		$list_cpus_cache = array();
		$sql = "SELECT * FROM cpus_cache";
		$result_query = mysqli_query($connection, $sql);
		
		$i = 1;
		while ($item_cpu = mysqli_fetch_array($result_query)) {
			if (array_key_exists($item_cpu['H_ID'], $list_cpus_cache)) {
				$array_key = $item_cpu['H_ID'] . str_repeat('*', $i++);
			} else {
				$array_key = $item_cpu['H_ID'];
			}
			$list_cpus_cache[$array_key]['MANUFACTURER'] = $item_cpu['MANUFACTURER'];
			$list_cpus_cache[$array_key]['TYPE'] = $item_cpu['TYPE'];
			$list_cpus_cache[$array_key]['ASSET'] = $item_cpu['H_ID'];
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
		$i = 0;
		while ($item_memory = mysqli_fetch_array($result_memories)) {
			if (array_key_exists($item_memory['HARDWARE_ID'], $list_memories)){
				$array_key = $item_memory['HARDWARE_ID'] . str_repeat("*", $i++);
			} else {
				$array_key = $item_memory['HARDWARE_ID'];
			}
			$list_memories[$array_key]['TYPE'] = $item_memory['TYPE'];
			$list_memories[$array_key]['CAPACITY (MB)'] = $item_memory['CAPACITY'];
			$list_memories[$array_key]['ASSET'] = $item_memory['HARDWARE_ID'];
		}

		$i = 0;
		$list_memories_cache = array();
		$sql = "SELECT * FROM memories_cache";
		$result_query = mysqli_query($connection, $sql);
		while ($item_memory = mysqli_fetch_array($result_query)) {
			if (array_key_exists($item_memory['H_ID'], $list_memories_cache)) {
				$array_key = $item_memory['H_ID'] . str_repeat("*", $i++);
			} else {
				$array_key = $item_memory['H_ID'];
			}
			$list_memories_cache[$array_key]['TYPE'] = $item_memory['TYPE'];
			$list_memories_cache[$array_key]['CAPACITY (MB)'] = $item_memory['CAPACITY'];
			$list_memories_cache[$array_key]['ASSET'] = $item_memory['H_ID'];
		}

		
		$added_memories = array();
		$removed_memories = array();
		
		$added_memories = array_diff_key($list_memories, $list_memories_cache);
		
		if ($added_memories != NULL) {
			$added_memories = filter_array_cells($added_memories);
			$this->get_html_info_addition($added_memories, $connection, $hard_component = "Memory(ies)");
		}
		
		$removed_memories = array_diff_key($list_memories_cache, $list_memories);	

		if ($removed_memories != NULL) {
			$removed_memories = filter_array_cells($removed_memories);
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
		$result_monitor = mysqli_query($connection, $sql);
		
		$list_monitors = array();
		$i = 0;
		while($item_monitor = mysqli_fetch_array($result_monitor)) {
			if (array_key_exists($item_monitor['HARDWARE_ID'], $list_monitors)) {
				$array_key = $item_monitor['HARDWARE_ID'] . str_repeat("*", $i++);
			} else {
				$array_key = $item_monitor['HARDWARE_ID'];
			}
			
			$list_monitors[$array_key]['MANUFACTURER'] = $item_monitor['MANUFACTURER'];		
			$list_monitors[$array_key]['DESCRIPTION'] = $item_monitor['DESCRIPTION'];		
			$list_monitors[$array_key]['ASSET'] = $item_monitor['HARDWARE_ID'];		
		}
		
		$sql = "SELECT * FROM monitors_cache";
		$result_query = mysqli_query($connection, $sql);

		$list_monitors_cache = array();
		$i = 0;
		while ($item_monitor = mysqli_fetch_array($result_query)) {
			if (array_key_exists($item_monitor['H_ID'], $list_monitors_cache)) {
				$array_key = $item_monitor['H_ID'] . str_repeat("*", $i++);
			} else {
				$array_key = $item_monitor['H_ID'];
			}
			
			$list_monitors_cache[$array_key]['MANUFACTURER'] = $item_monitor['MANUFACTURER'];
			$list_monitors_cache[$array_key]['DESCRIPTION'] = $item_monitor['DESCRIPTION'];		
			$list_monitors_cache[$array_key]['ASSET'] = $item_monitor['H_ID'];		
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

	public function get_storages() {
		$connection = db_connect();
		$sql = "SELECT * FROM storages";
		$result_storages = mysqli_query($connection, $sql);
		
		$list_storages = array();
		$i = 1;
		while ($item_storages = mysqli_fetch_array($result_storages)) {
			if ($item_storages['DISKSIZE'] < 16000) {
				continue;
			}
			if (array_key_exists($item_storages['HARDWARE_ID'], $list_storages)) {
				$array_key = $item_storages['HARDWARE_ID'] . str_repeat("*", $i++);
			} else {
				$array_key = $item_storages['HARDWARE_ID'];
			}

			$list_storages[$array_key]['MANUFACTURER'] = $item_storages['MANUFACTURER'];
			$list_storages[$array_key]['DISKSIZE (MB)'] = $item_storages['DISKSIZE'];
			$list_storages[$array_key]['MODEL'] = $item_storages['MODEL'];
			$list_storages[$array_key]['ASSET'] = $item_storages['HARDWARE_ID'];
		}
		
		$sql = "SELECT * FROM storages_cache";
		$result_query = mysqli_query($connection, $sql);
		
		$list_storages_cache = array();
		$i = 1;
		while ($item_storages = mysqli_fetch_array($result_query)) {
			if ($item_storages['DISKSIZE'] < 16000) {
				continue;
			}
			if (array_key_exists($item_storages['H_ID'], $list_storages_cache)) {
				$array_key = $item_storages['H_ID'] . str_repeat("*", $i++);
			} else {
				$array_key = $item_storages['H_ID'];
			}

			$list_storages_cache[$array_key]['MANUFACTURER'] = $item_storages['MANUFACTURER'];
			$list_storages_cache[$array_key]['DISKSIZE (MB)'] = $item_storages['DISKSIZE'];
			$list_storages_cache[$array_key]['MODEL'] = $item_storages['MODEL'];
			$list_storages_cache[$array_key]['ASSET'] = $item_storages['HARDWARE_ID'];
		}
		
		$added_storages = array();
		$removed_storages = array();

		$added_storages = array_diff_key($list_storages, $list_storages_cache);
		if ($added_storages != NULL) {
			$this->get_html_info_addition($added_storages, $connection, $hard_component = "Storage(s)");
		}
		
		$removed_storages = array_diff_key($list_storages_cache, $list_storages);
		if ($removed_storages != NULL) {
			$this->get_html_info_removed($removed_storages, $connection, $hard_component = "Storage(s)");
		}

		if ($added_storages != NULL or $removed_storages != NULL) {
			$sql = "TRUNCATE TABLE storages_cache;";
			$sql .= "REPLACE INTO storages_cache(ID, H_ID, MANUFACTURER, DISKSIZE, MODEL) SELECT ID, HARDWARE_ID, MANUFACTURER, DISKSIZE, MODEL FROM storages;";
			mysqli_multi_query($connection, $sql);
		}
	}

	public function get_videos() {
		$connection = db_connect();
		$sql = "SELECT * FROM videos";
		$result_videos = mysqli_query($connection, $sql);

		$list_videos = array();
		$i = 1;
		$array_key = NULL;
		while($item_videos = mysqli_fetch_array($result_videos)) {
			if (array_key_exists($item_videos['HARDWARE_ID'], $list_videos)) {
				$array_key = $item_videos['HARDWARE_ID'] . str_repeat("*", $i++);
			} else {
				$array_key = $item_videos['HARDWARE_ID'];
			}
			$list_videos[$array_key]['NAME'] = $item_videos['NAME'];		
			$list_videos[$array_key]['MEMORY'] = $item_videos['MEMORY'];		
			$list_videos[$array_key]['ASSET'] = $item_videos['HARDWARE_ID'];		
	
		}

		$sql = "SELECT * FROM videos_cache";
		$result_query = mysqli_query($connection, $sql);

		$list_videos_cache = array();
		$i = 1;
		while($item_videos = mysqli_fetch_array($result_query)) {
			if (array_key_exists($item_videos['H_ID'], $list_videos_cache)) {
				$array_key = $item_videos['H_ID'] . str_repeat("*", $i++);
			} else {
				$array_key = $item_videos['H_ID'];
			}	
			
			$list_videos_cache[$array_key]['NAME'] = $item_videos['NAME'];		
			$list_videos_cache[$array_key]['MEMORY'] = $item_videos['MEMORY'];		
			$list_videos_cache[$array_key]['ASSET'] = $item_videos['H_ID'];		
		}
		
		$added_videos = array();
		$removed_videos = array();
		
		$added_videos = array_diff_key($list_videos, $list_videos_cache);	
		if ($added_videos != NULL) {
			$this->get_html_info_addition($added_videos, $connection, $hard_component = "C. Video(s)");
		}
		
		$removed_videos = array_diff_key($list_videos_cache, $list_videos);	
		if ($removed_videos != NULL) {
			$this->get_html_info_removed($removed_videos, $connection, $hard_component = "C. Video(s)");
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

