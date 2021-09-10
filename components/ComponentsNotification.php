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
		for ($i = 0; $i <= array_key_last($list_hardware); $i++) {
			if ($this->html_part_addition == '') {
				$this->html_part_addition .= "
				<hr>
				<center>
					<h1> Novos Componentes de Hardware Adicionados <h1>
				</center>
				<hr>";
				$this->html_part_addition .= "
					<br><center>
					<h2> $hard_component <h2>	
					<table border='collapse' bgcolor='B8B0AE'>
					<tr>\n";
				}

			$reference_array = array_key_first($list_hardware);
			foreach ($list_hardware[$reference_array] as $label => $value) {
				$this->html_part_addition .= "<th>$label</th>\n";
			} 
			$this->html_part_addition .= "</tr>\n<tr>";

			foreach ($list_hardware["$i"] as $feature => $value) {
				if ($value == 'Unknown' or $value == '') { 
					$this->html_part_addition .= "<td style='text-align:center'> Não Informado </td>\n";
					continue;
				}
				if ($feature == "HARDWARE_ID") {
					$sql = "SELECT userid FROM hardware WHERE ID = '$value'";
					$result_id = mysqli_query($connection, $sql);
					$id = mysqli_fetch_array($result_id);	
					$this->html_part_addition .= "<td style='text-align:center'>" . $id['userid'] . "</td><td bgcolor='green' style='text-align:center'> Adicionado </td>\n";
				} else {
					$this->html_part_addition .= "<td style='text-align:center'>$value</td>\n";
				}
			}
			$this->html_part_addition .= "</tr><tr>";
		}
		$this->html_part_addition .= "</table></center><br>";
	}
	
	public function get_html_info_removed($hardware_cache, $connection, $hard_component) {
		for ($i = 0; $i <= array_key_last($hardware_cache); $i++) {
			if ($this->html_part_remove == '') {
				$this->html_part_remove .= "
					<br>
					<hr>
					<center>
						<h1> Novos Componentes de Hardware Retirados <h1>
					</center>
					<hr>";
				$this->html_part_remove .= "
					<br><center>
						<h2> $hard_component <h2>	
						<table border='1' bgcolor='B8B0AE'>
						<tr>\n";
			}
			$critical_situation = 0;	
			$reference_array = array_key_first($hardware_cache);
			foreach ($hardware_cache[$reference_array] as $label => $value) {
				$this->html_part_remove .= "<th>$label</th>\n";
			}
			$this->html_part_remove .= "</tr>\n<tr>";

			foreach ($hardware_cache["$i"] as $feature => $value) {
				if ($value == 'Unknown' or $value == '') {
					$this->html_part_remove .= "<td style='text-align:center'> Não Informado </td>\n";
					continue;
				}
				if ($feature == "HARDWARE_ID") {
					$sql = "SELECT userid FROM hardware WHERE id = '$value'";
					$result_id = mysqli_query($connection, $sql);
					$id = mysqli_fetch_array($result_id);
					if ($id['userid'] == '') {
						$this->html_part_remove .= "<td style='text-align:center'> Não Encontrado </td><td bgcolor='#e31111' style='text-align:center'> Computador Removido </td>\n";
						$critical_situation++;
					} else {
						$this->html_part_remove .= "<td style='text-align:center'>" . $id['userid'] . "</td><td bgcolor='#e31111' style='text-align:center'> Removido </td>\n";
					}
				} else {
					$this->html_part_remove .= "<td style='text-align:center'>$value</td>\n";
				}
			}
			$this->html_part_remove .= "</tr><tr>";
		}
			$this->html_part_remove .= "</table></center><br>";
			if ($critical_situation > 0)
				$this->html_part_remove .= "Warnings:<br>- Has been detected a critical situation. It's possible some asset has been removed.<br>";
			else 
				$this->html_part_remove .= "Warnings:<br>- Hasn't been detected anyone critical situation.<br>";
	}


	public function get_cpus() {
		$connection = db_connect();
		$sql = "SELECT * FROM cpus";
		$result_cpus = mysqli_query($connection, $sql);
		
		$list_cpus = array();
		while ($item_cpu = mysqli_fetch_array($result_cpus)) {
			$list_cpus[$item_cpu['ID']]['MANUFACTURER'] = $item_cpu['MANUFACTURER'];
			$list_cpus[$item_cpu['ID']]['TYPE'] = $item_cpu['TYPE'];
			$list_cpus[$item_cpu['ID']]['HARDWARE_ID'] = $item_cpu['HARDWARE_ID'];
		}

		$count_cpus = count($list_cpus);
		$list_cpus_cache = array();
		$sql = "SELECT * FROM cpus_cache";
		$result_query = mysqli_query($connection, $sql);
		while ($item_cpu = mysqli_fetch_array($result_query)) {
			$list_cpus_cache[$item_cpu['ID']]['MANUFACTURER'] = $item_cpu['MANUFACTURER'];
			$list_cpus_cache[$item_cpu['ID']]['TYPE'] = $item_cpu['TYPE'];
			$list_cpus_cache[$item_cpu['ID']]['HARDWARE_ID'] = $item_cpu['H_ID'];
		}

		$count_cpus_cache = count($list_cpus_cache);
		
		$added_cpus = array();
		$removed_cpus = array();
		for ($i = 1; $i <= array_key_last($list_cpus); $i++) {
			if (array_key_exists($i, $list_cpus) and !array_key_exists($i, $list_cpus_cache)) {
				array_push($added_cpus, $list_cpus["$i"]);
			}
		}

		if ($added_cpus != NULL) {
			$this->get_html_info_addition($added_cpus, $connection, $hard_component = "Cpu");
		}

		for ($i = 1; $i <= array_key_last($list_cpus_cache); $i++) {
			if (!array_key_exists($i, $list_cpus) and array_key_exists($i, $list_cpus_cache)) {
				array_push($removed_cpus, $list_cpus_cache["$i"]);
			}
		}

		if ($removed_cpus != NULL) {
			$this->get_html_info_removed($removed_cpus, $connection, $hard_component = "Cpu");
		}

		if ($added_cpus != NULL or $removed_cpus != NULL) {
			$sql = "TRUNCATE TABLE cpus_cache;";
			$sql .= "REPLACE INTO cpus_cache(ID, H_ID, TYPE, MANUFACTURER) SELECT ID, HARDWARE_ID, TYPE, MANUFACTURER FROM cpus;";
			mysqli_multi_query($connection, $sql);
		}
	}

	
	// Verifica as memórias presentes no banco de dados 
	public function get_memories() {
		$connection = db_connect();
		$sql = "SELECT * FROM memories";
		$result_memories = mysqli_query($connection, $sql);
		
		$list_memories = array();
		while ($item_memory = mysqli_fetch_array($result_memories)) {
			$list_memories[$item_memory['ID']]['TYPE'] = $item_memory['TYPE'];
			$list_memories[$item_memory['ID']]['CAPACITY'] = $item_memory['CAPACITY'];
			$list_memories[$item_memory['ID']]['HARDWARE_ID'] = $item_memory['HARDWARE_ID'];
		}

		$count_memories = count($list_memories);
		$list_memories_cache = array();
		$sql = "SELECT * FROM memories_cache";
		$result_query = mysqli_query($connection, $sql);
		while ($item_memory = mysqli_fetch_array($result_query)) {
			$list_memories_cache[$item_memory['ID']]['TYPE'] = $item_memory['TYPE'];
			$list_memories_cache[$item_memory['ID']]['HARDWARE_ID'] = $item_memory['H_ID'];
		}

		$count_memories_cache = count($list_memories_cache);

		if (($count_memories != 0 and $count_memories_cache != 0) and $count_memories == $count_memories_cache) {

		} elseif ($count_memories > $count_memories_cache) {
			$this->get_html_info_addition($list_memories, $list_memories_cache, 
				$connection, $hard_component = "Memory");
			$this->get_html_info_remove($list_memories, $list_memories_cache, 
				$connection, $hard_component = "Memory");
			$sql = "TRUNCATE TABLE memories_cache;";
			$sql .= "REPLACE INTO memories_cache(ID, H_ID, TYPE) SELECT ID, HARDWARE_ID, TYPE FROM memories;";
			mysqli_multi_query($connection, $sql);
		} elseif ($count_memories < $count_memories_cache) {
			$this->get_html_info_remove($list_memories, $list_memories_cache, 
				$connection, $hard_component = "Memory");
			$this->get_html_info_addition($list_memories, $list_memories_cache, 
				$connection, $hard_component = "Memory");
			$sql = "TRUNCATE TABLE memories_cache;";
			$sql .= "REPLACE INTO memories_cache(ID, H_ID, TYPE) SELECT ID, HARDWARE_ID, TYPE FROM memories;";
			mysqli_multi_query($connection, $sql);
		}
	}

	// Verifica os monitores presentes no banco de dados 
	public function get_monitors() {
		$connection = db_connect();
		$sql = "SELECT * FROM monitors";
		$result_monitors = mysqli_query($connection, $sql);
		
		$list_monitors = array();
		while($item_monitors = mysqli_fetch_array($result_monitors)) {
			$list_monitors[$item_monitors['ID']]['MANUFACTURER'] = $item_monitors['MANUFACTURER'];		
			$list_monitors[$item_monitors['ID']]['DESCRIPTION'] = $item_monitors['DESCRIPTION'];		
			$list_monitors[$item_monitors['ID']]['HARDWARE_ID'] = $item_monitors['HARDWARE_ID'];		
		}
		
		$count_monitors = count($list_monitors);
		
		$list_monitors_cache = array();
		$sql = "SELECT * FROM monitors_cache";
		$result_query = mysqli_query($connection, $sql);
		while ($item_monitor = mysqli_fetch_array($result_query)) {
			$list_monitors_cache[$item_monitor['ID']]['MANUFACTURER'] = $item_monitor['MANUFACTURER'];
			$list_monitors_cache[$item_monitor['ID']]['HARDWARE_ID'] = $item_monitor['H_ID'];		
		}
		
		$count_monitors_cache = count($list_monitors_cache);
		if (($count_monitors != 0 and $count_monitors_cache != 0) and $count_monitors == $count_monitors_cache) {
			// houve alteracao
		} elseif ($count_monitors > $count_monitors_cache) {
			$this->get_html_info_addition($list_monitors, $list_monitors_cache, 
				$connection, $hard_component = "Monitors");
			$this->get_html_info_remove($list_monitors, $list_monitors_cache, 
				$connection, $hard_component = "Monitors");
			$sql = "TRUNCATE TABLE monitors_cache;";
			$sql .= "REPLACE INTO monitors_cache(ID, H_ID, MANUFACTURER) SELECT ID, HARDWARE, MANUFACTURER FROM monitors;";
			mysqli_multi_query($connection, $sql);
		} elseif ($count_monitors < $count_monitors_cache) {
				$this->get_html_info_remove($list_monitors, $list_monitors_cache, 
					$connection, $hard_component = "Monitors");
				$this->get_html_info_addition($list_monitors, $list_monitors_cache, 
					$connection, $hard_component = "Monitors");
				$sql = "TRUNCATE TABLE monitors_cache;";
				$sql .= "REPLACE INTO monitors_cache(ID, H_ID, MANUFACTURER) SELECT ID, HARDWARE_ID, MANUFACTURER FROM monitors;";
				mysqli_multi_query($connection, $sql);
		}
	}

	// Verifica os disp. de armazenamento no banco de dados 
	public function get_storages() {
		$connection = db_connect();
		$sql = "SELECT * FROM storages";
		$result_storages = mysqli_query($connection, $sql);
		
		$list_storages = array();
		while ($item_storages = mysqli_fetch_array($result_storages)) {
			$list_storages[$item_storages['ID']]['NAME'] = $item_storages['NAME'];
			$list_storages[$item_storages['ID']]['MANUFACTURER'] = $item_storages['MANUFACTURER'];
			$list_storages[$item_storages['ID']]['DESCRIPTION'] = $item_storages['DESCRIPTION'];
			$list_storages[$item_storages['ID']]['HARDWARE_ID'] = $item_storages['HARDWARE_ID'];

		}
		
		$count_storages = count($list_storages);

		$sql = "SELECT * FROM storages_cache";
		$result_query = mysqli_query($connection, $sql);
		
		$list_storages_cache = array();
		while ($item_storages = mysqli_fetch_array($result_query)) {
			$list_storages_cache[$item_storages['ID']]['NAME'] = $item_storages['NAME'];
			$list_storages_cache[$item_storages['ID']]['MANUFACTURER'] = $item_storages['MANUFACTURER'];
			$list_storages_cache[$item_storages['ID']]['DESCRIPTION'] = $item_storages['DESCRIPTION'];
			$list_storages_cache[$item_storages['ID']]['HARDWARE_ID'] = $item_storages['H_ID'];
		}
		
		$count_storages_cache = count($list_storages_cache);
		if (($count_storages != 0 and $count_storages_cache != 0) and $count_storages == $count_storages_cache) {
			// houve alteracao
		} elseif ($count_storages > $count_storages_cache) {
			$this->get_html_info_addition($list_storages, $list_storages_cache, 
				$connection, $hard_component = "Storages");
			$this->get_html_info_remove($list_storages, $list_storages_cache, 
				$connection, $hard_component = "Storages");
			$sql = "TRUNCATE TABLE storages_cache;";
			$sql .= "REPLACE INTO storages_cache(ID, H_ID, NAME, DESCRIPTION, MANUFACTURER) SELECT ID, HARDWARE_ID, NAME, DESCRIPTION, MANUFACTURER FROM storages;";
			mysqli_multi_query($connection, $sql);
		} elseif ($count_storages < $count_storages_cache) {
				$this->get_html_info_remove($list_storages, $list_storages_cache, 
					$connection, $hard_component = "Storages");
				$this->get_html_info_addition($list_storages, $list_storages_cache, 
					$connection, $hard_component = "Storages");
				$sql = "TRUNCATE TABLE storages_cache;";
				$sql .= "REPLACE INTO storages_cache(ID, H_ID, NAME, DESCRIPTION, MANUFACTURER) SELECT ID, HARDWARE_ID, NAME, DESCRIPTION, MANUFACTURER FROM storages;";
				mysqli_multi_query($connection, $sql);
		}
	}

	// Verifica as placas de video presentes no banco de dados
	public function get_videos() {
		$connection = db_connect();
		$sql = "SELECT * FROM videos";
		$result_videos = mysqli_query($connection, $sql);

		$list_videos = array();
		while($item_videos = mysqli_fetch_array($result_videos)) {
			$list_videos[$item_videos['ID']]['NAME'] = $item_videos['NAME'];		
			$list_videos[$item_videos['ID']]['MEMORY'] = $item_videos['MEMORY'];		
			$list_videos[$item_videos['ID']]['HARDWARE_ID'] = $item_videos['HARDWARE_ID'];		
		}
	
		$count_videos = count($list_videos);
		
		$sql = "SELECT * FROM videos_cache";
		$result_query = mysqli_query($connection, $sql);

		$list_videos_cache = array();
		while($item_videos = mysqli_fetch_array($result_query)) {
			$list_videos_cache[$item_videos['ID']]['NAME'] = $item_videos['NAME'];		
			$list_videos_cache[$item_videos['ID']]['HARDWARE_ID'] = $item_videos['H_ID'];		
		}
	
		$count_videos_cache = count($list_videos_cache);
		
		if (($count_videos != 0 and $count_videos_cache != 0) and $count_videos == $count_videos_cache) {

		} elseif ($count_videos > $count_videos_cache) {
			$this->get_html_info_addition($list_videos, $list_videos_cache, 
				$connection, $hard_component = "Board Videos");
			$this->get_html_info_remove($list_videos, $list_videos_cache, 
				$connection, $hard_component = "Board Videos");
			$sql = "TRUNCATE TABLE videos_cache;";
			$sql .= "REPLACE INTO videos_cache(ID, H_ID, NAME) SELECT ID, HARDWARE_ID, NAME FROM videos;";
			mysqli_multi_query($connection, $sql);
		} elseif ($count_videos < $count_videos_cache) {
				$this->get_html_info_remove($list_videos, $list_videos_cache, 
					$connection, $hard_component = "Board Videos");
				$this->get_html_info_addition($list_videos, $list_videos_cache, 
					$connection, $hard_component = "Board Videos");
				$sql = "TRUNCATE TABLE videos_cache;";
				$sql .= "REPLACE INTO videos_cache(ID, H_ID, NAME) SELECT ID, HARDWARE_ID, NAME FROM videos;";
				mysqli_multi_query($connection, $sql);
		}

	}
}

