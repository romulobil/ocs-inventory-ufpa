<?php
/**
* function that filter empty cells of any
* @author claudio966
* @acess public
* @return an array 
*/
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
				// Import css style 
				$this->html_part_addition .= "
				 " .  file_get_contents(__DIR__ . '/html_css_part.html') . "
				<hr>
				<center>
					<h1> New Hardware Components Has been Added <h1>
				</center>
				<hr>";
		}
		$this->html_part_addition .= "
				<br>
				<table class='tabelaRelatorio' width='100%'>
				<caption> $hard_component <caption>
				<tr><thead>\n";

		$reference_array = array_key_first($list_hardware);
		foreach ($list_hardware[$reference_array] as $label => $value) {
			$this->html_part_addition .= "<th class='nota'>$label</th>\n";
		} 
			$this->html_part_addition .= "<th class='nota'> STATUS </th>";
		$this->html_part_addition .= "</tr></thead>\n<tbody><tr class='linhaPar linha'>";
	
		$id_asset = NULL; 
		// for ($i = $reference_array; $i <= array_key_last($list_hardware); $i++) {
		foreach (array_keys($list_hardware) as $key) {
			foreach ($list_hardware["$key"] as $feature => $value) {
				if ($value == 'Unknown' or $value == '') { 
					$this->html_part_addition .= "<td class='nota' nowrap='nowrap'> Uninformed </td>\n";
					continue;
				}
				if ($feature == "ASSET") {
					$sql = "SELECT TAG FROM accountinfo WHERE HARDWARE_ID = " . trim($value);
					$result_id = mysqli_query($connection, $sql);
					$id_asset = mysqli_fetch_array($result_id);

					if (isset($id_asset)) {	
						$this->html_part_addition .= "<td class='nota' nowrap='nowrap'>" . $id_asset['TAG'] . "</td><td class='nota'> <font color='green'> Added </font></td>\n";
					} else {
						$this->html_part_addition .= "<td class='nota' nowrap='nowrap'> Not Found </td><td class='nota'> <font color='green'> Added </font></td>\n";
					}
				} else {
					$this->html_part_addition .= "<td class='nota'>$value</td>\n";
				}
		} 
			$this->html_part_addition .= "</tr><tr class='linhaPar linha'>";
		}
		$this->html_part_addition .= "<td class='nota' nowrap='nowrap' bgcolor='#4169e1'>Total: " . count($list_hardware) . "</td></tr>";
		$this->html_part_addition .= "</table></tbody><br>";
	}
	
	public function get_html_info_removed($hardware_cache, $connection, $hard_component) {
		if ($this->html_part_remove == '') {
				// import css style
				$this->html_part_remove .= "
				" . file_get_contents(__DIR__ . '/html_css_part.html') ."
					<hr>
					<center>
						<h1> Hardware Components has been Removed <h1>
					</center>
					<hr>";
		}
		$this->html_part_remove .= "
				<br>
					<table class='tabelaRelatorio' width='100%'>
					<caption> $hard_component <caption>	
					<tr><thead>\n";
		$reference_array = array_key_first($hardware_cache);
		foreach ($hardware_cache[$reference_array] as $label => $value) {
			$this->html_part_remove .= "<th class='nota'>$label</th>\n";
		}
		$this->html_part_remove .= "<th class='nota'> STATUS </th>";
		$this->html_part_remove .= "</tr>\n</thead><tbody><tr class='linhaPar linha'>";

		$id_asset = NULL;
		foreach (array_keys($hardware_cache) as $key) {
			foreach ($hardware_cache["$key"] as $feature => $value) {
				if ($value == 'Unknown' or $value == '') {
					$this->html_part_remove .= "<td class='nota' nowrap='nowrap'> Uninformed </td>\n";
					continue;
				}
				if ($feature == "ASSET") {
					$sql = "SELECT TAG FROM id_assets_cache WHERE H_ID = " . trim($value);
					$result_id = mysqli_query($connection, $sql);
					$id_asset = mysqli_fetch_array($result_id);
					if (isset($id_asset)) {
						$this->html_part_remove .= "<td class='nota' nowrap='nowrap'>" . $id_asset['TAG'] . "</td><td class='nota'> <font color='#e31111'> Removed </font></td>\n";
					} else {
						$this->html_part_remove .= "<td class='nota' nowrap='nowrap'> Not Found </td><td class='nota' nowrap='nowrap'> <font color='#e31111'> Removed </font></td>\n";
					}
				} else {
					$this->html_part_remove .= "<td class='nota' nowrap='nowrap'>$value</td>\n";
				}
			}

			$this->html_part_remove .= "</tr><tr class='linhaPar linha'>";
		}
		$this->html_part_remove .= "<td class='nota' bgcolor='#4169e1' nowrap='nowrap'>Total: " . count($hardware_cache) . "</td></tr>";
		$this->html_part_remove .= "</table></tbody><br>";
			
	}
	/**
	* A method that collect information about the cpu of asset
	* @acess public
	* @return void 
	*/
	public function get_cpus() {
		$connection = db_connect();
		$sql = "SELECT * FROM cpus";
		$result_cpus = mysqli_query($connection, $sql);
		
		$list_cpus = array();
		$asterix_amount = 0;
		while ($item_cpu = mysqli_fetch_array($result_cpus)) {
			if (array_key_exists($item_cpu['HARDWARE_ID'], $list_cpus)){
				$array_key = $item_cpu['HARDWARE_ID'] . str_repeat('*', ++$asterix_amount);
			} else {
				$array_key = $item_cpu['HARDWARE_ID'];
				$asterix_amount = 0;
			}
				$list_cpus[$array_key]['MANUFACTURER'] = $item_cpu['MANUFACTURER'];
				$list_cpus[$array_key]['TYPE'] = $item_cpu['TYPE'];
				$list_cpus[$array_key]['ASSET'] = $item_cpu['HARDWARE_ID'];
		}

		$list_cpus_cache = array();
		$sql = "SELECT * FROM cpus_cache";
		$result_query = mysqli_query($connection, $sql);
		
		$asterix_amount = 0;
		while ($item_cpu = mysqli_fetch_array($result_query)) {
			if (array_key_exists($item_cpu['H_ID'], $list_cpus_cache)) {
				$array_key = $item_cpu['H_ID'] . str_repeat('*', ++$asterix_amount);
			} else {
				$array_key = $item_cpu['H_ID'];
				$asterix_amount = 0;
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

	/**
	* A method that collect information about the memories of asset
	* @acess public
	* @return void 
	*/
	public function get_memories() {
		$connection = db_connect();
		$sql = "SELECT * FROM memories";
		$result_memories = mysqli_query($connection, $sql);
		
		$list_memories = array();
		$asterix_amount = 0;
		while ($item_memory = mysqli_fetch_array($result_memories)) {
			if ($item_memory['CAPACITY'] == NULL or $item_memory['CAPACITY'] == 0) {
				continue;
			}
			if (array_key_exists($item_memory['HARDWARE_ID'], $list_memories)){
				$array_key = $item_memory['HARDWARE_ID'] . str_repeat("*", ++$asterix_amount);
			} else {
				$array_key = $item_memory['HARDWARE_ID'];
				$asterix_amount = 0;
			}
			$list_memories[$array_key]['TYPE'] = $item_memory['TYPE'];
			$list_memories[$array_key]['CAPACITY (MB)'] = $item_memory['CAPACITY'];
			$list_memories[$array_key]['ASSET'] = $item_memory['HARDWARE_ID'];
		}
		$asterix_amount = 0;
		$list_memories_cache = array();
		$sql = "SELECT * FROM memories_cache";
		$result_query = mysqli_query($connection, $sql);
		while ($item_memory = mysqli_fetch_array($result_query)) {
			if ($item_memory['CAPACITY'] == NULL or $item_memory['CAPACITY'] == 0) {
				continue;
			}
			if (array_key_exists($item_memory['H_ID'], $list_memories_cache)) {
				$array_key = $item_memory['H_ID'] . str_repeat("*", ++$asterix_amount);
			} else {
				$array_key = $item_memory['H_ID'];
				$asterix_amount = 0;
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

	/**
	* A method that collect information about the monitors of asset
	* @acess public
	* @return void 
	*/
	public function get_monitors() {
		$connection = db_connect();
		$sql = "SELECT * FROM monitors";
		$result_monitor = mysqli_query($connection, $sql);
		
		$list_monitors = array();
		$asterix_amount = 0;
		while($item_monitor = mysqli_fetch_array($result_monitor)) {
			if (array_key_exists($item_monitor['HARDWARE_ID'], $list_monitors)) {
				$array_key = $item_monitor['HARDWARE_ID'] . str_repeat("*", ++$asterix_amount);
			} else {
				$array_key = $item_monitor['HARDWARE_ID'];
				$asterix_amount = 0;
			}
			
			$list_monitors[$array_key]['MANUFACTURER'] = $item_monitor['MANUFACTURER'];		
			$list_monitors[$array_key]['DESCRIPTION'] = $item_monitor['DESCRIPTION'];		
			$list_monitors[$array_key]['ASSET'] = $item_monitor['HARDWARE_ID'];		
		}
		
		$sql = "SELECT * FROM monitors_cache";
		$result_query = mysqli_query($connection, $sql);

		$list_monitors_cache = array();
		$asterix_amount = 0;
		while ($item_monitor = mysqli_fetch_array($result_query)) {
			if (array_key_exists($item_monitor['H_ID'], $list_monitors_cache)) {
				$array_key = $item_monitor['H_ID'] . str_repeat("*", ++$asterix_amount);
			} else {
				$array_key = $item_monitor['H_ID'];
				$asterix_amount = 0;
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

	/**
	* A method that collect information about the storages(like HD, SSD, ...) of asset
	* @acess public
	* @return void 
	*/
	public function get_storages() {
		$connection = db_connect();
		$sql = "SELECT * FROM storages";
		$result_storages = mysqli_query($connection, $sql);
		
		$list_storages = array();
		$asterix_amount = 0;
		while ($item_storages = mysqli_fetch_array($result_storages)) {
			if ($item_storages['DISKSIZE'] < 16000) {
				continue;
			}
			if (array_key_exists($item_storages['HARDWARE_ID'], $list_storages)) {
				$array_key = $item_storages['HARDWARE_ID'] . str_repeat("*", ++$asterix_amount);
			} else {
				$array_key = $item_storages['HARDWARE_ID'];
				$asterix_amount = 0;
			}

			$list_storages[$array_key]['MANUFACTURER'] = $item_storages['MANUFACTURER'];
			$list_storages[$array_key]['DISKSIZE (MB)'] = $item_storages['DISKSIZE'];
			$list_storages[$array_key]['MODEL'] = $item_storages['MODEL'];
			$list_storages[$array_key]['ASSET'] = $item_storages['HARDWARE_ID'];
		}
		
		$sql = "SELECT * FROM storages_cache";
		$result_query = mysqli_query($connection, $sql);
		
		$list_storages_cache = array();
		$asterix_amount = 0;
		while ($item_storages = mysqli_fetch_array($result_query)) {
			// check if this device is which really is
			if ($item_storages['DISKSIZE'] < 16000) {
				continue;
			}
			if (array_key_exists($item_storages['H_ID'], $list_storages_cache)) {
				$array_key = $item_storages['H_ID'] . str_repeat("*", ++$asterix_amount);
			} else {
				$array_key = $item_storages['H_ID'];
				$asterix_amount = 0;
			}

			$list_storages_cache[$array_key]['MANUFACTURER'] = $item_storages['MANUFACTURER'];
			$list_storages_cache[$array_key]['DISKSIZE (MB)'] = $item_storages['DISKSIZE'];
			$list_storages_cache[$array_key]['MODEL'] = $item_storages['MODEL'];
			$list_storages_cache[$array_key]['ASSET'] = $item_storages['H_ID'];
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
	/**
	* A method that collect information about the board videos of asset
	* @acess public
	* @return void 
	*/
	public function get_videos() {
		$connection = db_connect();
		$sql = "SELECT * FROM videos";
		$result_videos = mysqli_query($connection, $sql);

		$list_videos = array();
		$asterix_amount = 0;
		$array_key = NULL;
		while($item_videos = mysqli_fetch_array($result_videos)) {
			if (array_key_exists($item_videos['HARDWARE_ID'], $list_videos)) {
				$array_key = $item_videos['HARDWARE_ID'] . str_repeat("*", ++$asterix_amount);
			} else {
				$array_key = $item_videos['HARDWARE_ID'];
				$asterix_amount = 0;
			}
			$list_videos[$array_key]['NAME'] = $item_videos['NAME'];		
			$list_videos[$array_key]['MEMORY'] = $item_videos['MEMORY'];		
			$list_videos[$array_key]['ASSET'] = $item_videos['HARDWARE_ID'];		
	
		}

		$sql = "SELECT * FROM videos_cache";
		$result_query = mysqli_query($connection, $sql);

		$list_videos_cache = array();
		$asterix_amount = 0;
		while($item_videos = mysqli_fetch_array($result_query)) {
			if (array_key_exists($item_videos['H_ID'], $list_videos_cache)) {
				$array_key = $item_videos['H_ID'] . str_repeat("*", ++$asterix_amount);
			} else {
				$array_key = $item_videos['H_ID'];
				$asterix_amount = 0;
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
	/**
	* A function that update the fields id and tag of cache table, whenever a asset is removed or added
	* @acess public
	* @return void 
	*/
	public function update_id_assets() {
		$connection = db_connect();
		$sql = "TRUNCATE TABLE id_assets_cache;";
		$sql .= "REPLACE INTO id_assets_cache(H_ID, TAG) SELECT HARDWARE_ID, TAG FROM accountinfo;";
		mysqli_multi_query($connection, $sql);
	}
}

