<?php
require __DIR__.'/send.php'; 
class Components_Notification {
	
	private function db_connect() {	
		$config_db = parse_ini_file(__DIR__ . '/database.ini');	
		$connection = mysqli_connect($config_db['localhost'], $config_db['username'], $config_db['password'], 
			$config_db['dbname']);
		if (!$connection)
			die("Não foi possível estabelecer uma conexão: " . mysqli_error($connection));
		else
			return $connection;
	}
	
	public $html_part_addition;
	public $html_part_remove;

	// check the monitors presents on database //
	public function get_monitors () {
		$connection = $this->db_connect();
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
		if ($count_monitors > $count_monitors_cache) {
			$this->get_html_general_information($list_monitors, $list_monitors_cache, 
				$connection, $is_addition = true, $hard_component = "Monitors");
			$sql = "TRUNCATE TABLE monitors_cache;";
			$sql .= "REPLACE INTO monitors_cache(ID, H_ID, MANUFACTURER) SELECT id, hardware_id, MANUFACTURER FROM monitors;";
			mysqli_multi_query($connection, $sql);
		} else {
			if ($count_monitors < $count_monitors_cache) {
				$this->get_html_general_information($list_monitors, $list_monitors_cache, 
					$connection, $is_addition = false, $hard_component = "Monitors");
				$sql = "TRUNCATE TABLE monitors_cache;";
				$sql .= "REPLACE INTO monitors_cache(ID, H_ID, MANUFACTURER) SELECT id, hardware_id, MANUFACTURER FROM monitors;";
				mysqli_multi_query($connection, $sql);
			}
		}
	}


	// Check the memories presents on database //
	public function get_memories (){
		$connection = $this->db_connect();
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
	
		if ($count_memories > $count_memories_cache) {
			$this->get_html_general_information($list_memories, $list_memories_cache, 
				$connection, $is_addition = true, $hard_component = "Memory");
			$sql = "TRUNCATE TABLE memories_cache;";
			$sql .= "REPLACE INTO memories_cache(ID, H_ID, TYPE) SELECT id, hardware_id, type FROM memories;";
			mysqli_multi_query($connection, $sql);
		} else {
			if ($count_memories < $count_memories_cache) {
				$this->get_html_general_information($list_memories, $list_memories_cache, 
					$connection, $is_addition = false, $hard_component = "Memory");
				$sql = "TRUNCATE TABLE memories_cache;";
				$sql .= "REPLACE INTO memories_cache(ID, H_ID, TYPE) SELECT id, hardware_id, type FROM memories;";
				mysqli_multi_query($connection, $sql);
			}
		}
	}


	// Generate the html body for email //
	public function get_html_general_information($list_hardware, $hardware_cache, $connection, $is_addition, $hard_component) {
		if ($is_addition) {
			if ($this->html_part_addition == '') {
					$this->html_part_addition .= "
						<hr>
					<center>
						<h1> Novos Componentes de Hardware Adicionados <h1>
					</center>
					<hr>";
			}
			$this->html_part_addition .= "<br><center>
				<h2> $hard_component <h2>	
				<table border='1' bgcolor='B8B0AE'>
				<tr>\n";
			$reference_array = '1';
			foreach ($list_hardware[$reference_array] as $label => $value) {
					$this->html_part_addition .= "<th>$label</th>\n";
			} 
			$this->html_part_addition .= "</tr>\n<tr>";
			
			for ($i = 1; $i <= array_key_last($list_hardware); $i++) {
				if (array_key_exists($i, $list_hardware) and !array_key_exists($i, $hardware_cache)) {
					foreach ($list_hardware["$i"] as $feature => $value) {
						if ($value == 'Unknown' or $value == '') { 
							$this->html_part_addition .= "<td style='text-align:center'> Não Informado </td>\n";
							continue;
						}
						if ($feature == "HARDWARE_ID") {
							$sql = "SELECT userid FROM hardware WHERE id = '$value'";
							$result_id = mysqli_query($connection, $sql);
							$id = mysqli_fetch_array($result_id);	
							$this->html_part_addition .= "<td style='text-align:center'>" . $id['userid']."</td><td bgcolor='green' style='text-align:center'> Adicionado </td>\n";
						} else {
							$this->html_part_addition .= "<td style='text-align:center'>$value</td>\n";
						}
					}
				} else { continue; }
				$this->html_part_addition .= "</tr><tr>";
			}
				$this->html_part_addition .= "</table></center><br>";

		// In the case there is a hardware removed //
		} else {
			if ($this->html_part_remove == '') {
				$this->html_part_remove .= "
					<br>
					<hr>
					<center>
						<h1> Novos Componentes de Hardware Retirados <h1>
					</center>
					<hr>";
			}
			$this->html_part_remove .= "
				<br><center>
					<h2> $hard_component <h2>	
					<table border='1' bgcolor='B8B0AE'>
					<tr>\n";

			$reference_array = '1';
			$critical_situation = 0;	
			foreach ($hardware_cache[$reference_array] as $label => $value) {
					$this->html_part_remove .= "<th>$label</th>\n";
			}
			$this->html_part_remove .= "</tr>\n<tr>";
			for ($i = 1; $i <= array_key_last($hardware_cache); $i++) {
				if (!array_key_exists($i, $list_hardware) && array_key_exists($i, $hardware_cache)) {
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
								$this->html_part_remove .= "<td style='text-align:center'>" .$id['userid']."</td><td bgcolor='#e31111' style='text-align:center'> Removido </td>\n";
							}
						} else {
							$this->html_part_remove .= "<td style='text-align:center'>$value</td>\n";
						}
					}
					$this->html_part_remove .= "</tr><tr>";
				} else { continue; }
			}	
					$this->html_part_remove .= "</table></center><br>";
					if ($critical_situation > 0)
						$this->html_part_remove .= "Lista de Avisos:<br>- Foi detectado uma possível remoção de um ativo no seu parque tecnológico.<br>";
					else 
						$this->html_part_remove .= "Lista de Avisos:<br>- Não foi detectado nenhuma situação crítica.<br>";
		}
	}

}
?>

