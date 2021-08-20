<?php
require __DIR__.'/send.php'; 
class Components_Notification {
	public $html;
	// Check the memories presents on database //
	public function get_memories (){
		$config_db = parse_ini_file(__DIR__ . '/database.ini');	
		$connection = mysqli_connect($config_db['localhost'], $config_db['username'], $config_db['password'], 
			$config_db['dbname']);

		$sql = "SELECT * FROM memories";
		$result_memories = mysqli_query($connection, $sql);
		
		while ($item_memory = mysqli_fetch_array($result_memories)) {
			$list_memories[$item_memory['ID']]['TYPE'] = $item_memory['TYPE'];
			$list_memories[$item_memory['ID']]['CAPACITY'] = $item_memory['CAPACITY'];
			$list_memories[$item_memory['ID']]['HARDWARE_ID'] = $item_memory['HARDWARE_ID'];
		}

		$count_memories = count($list_memories);
		
		$sql = "SELECT * FROM memories_cache";
		$result_query = mysqli_query($connection, $sql);
		while ($item_memory = mysqli_fetch_array($result_query)) {
			$list_memories_cache[$item_memory['ID']]['TYPE'] = $item_memory['TYPE'];
			$list_memories_cache[$item_memory['ID']]['HARDWARE_ID'] = $item_memory['H_ID'];
		}

		$count_memories_cache = count($list_memories_cache);
	
		if ($count_memories > $count_memories_cache) {
			$sql = "TRUNCATE TABLE memories_cache;";
			$sql .= "REPLACE INTO memories_cache(ID, H_ID, TYPE) SELECT id, hardware_id, type FROM memories;";
			$this->get_html_general_information($list_memories, $list_memories_cache, 
				$connection, $is_additon = true, $hard_component = "Memory");
			Send_Email($this->html);  // The notification email will be send 
			mysqli_multi_query($connection, $sql);
		} else {
			if ($count_memories < $count_memories_cache) {
				$this->get_html_general_information($list_memories, $list_memories_cache, 
					$connection, $is_addition = false, $hard_component = "Memory");
				Send_Email($this->html);  // The notification email will be send
				$sql = "TRUNCATE TABLE memories_cache;";
				$sql .= "REPLACE INTO memories_cache(ID, H_ID, TYPE) SELECT id, hardware_id, type FROM memories;";
				mysqli_multi_query($connection, $sql);
			}
		}
			$connection->close();
	}


	// Generate the html body for email //
	public function get_html_general_information($list_memories, $hardware_cache, $connection, $is_addition, $hard_component) {
		if ($is_addition) {
			$this->html .= "
				<hr>
				<center>
					<h1> Novos Componentes de Hardware Adicionados <h1>
				</center>
				<hr>
				<center>
					<h2> $hard_component <h2>	
					<table border='1' bgcolor='B8B0AE'>
					<tr>\n";
			$reference_array = '1';
			foreach ($list_memories[$reference_array] as $label => $value) {
					$this->html .= "<th>$label</th>\n";
			} 
			$this->html .= "</tr>\n<tr>";
			
			for ($i = 1; $i <= array_key_last($list_memories); $i++) {
				if (array_key_exists($i, $list_memories) and !array_key_exists($i, $hardware_cache)) {
					foreach ($list_memories["$i"] as $feature => $value) {
						if ($value == 'Unknown' or $value == '') { 
							$this->html .= "<td style='text-align:center'> Não Informado </td>\n";
							continue;
						}
						if ($feature == "HARDWARE_ID") {
							$sql = "SELECT userid FROM hardware WHERE id = '$value'";
							$result_id = mysqli_query($connection, $sql);
							$id = mysqli_fetch_array($result_id);	
							$this->html .= "<td style='text-align:center'>" . $id['userid']."</td><td bgcolor='green' style='text-align:center'> Adicionado </td>\n";
						} else {
							$this->html .= "<td style='text-align:center'>$value</td>\n";
						}
					}
				} else { continue; }
				$this->html .= "</tr><tr>";
			}
				$this->html .= "</table></center>";

		// In the case there is a hardware removed //
		} else {
			$this->html .= "
				<br>
				<hr>
				<center>
					<h1> Novos Componentes de Hardware Retirados <h1>
				</center>
				<hr>
				<center>
					<h2> $hard_component <h2>	
					<table border='1' bgcolor='B8B0AE'>
					<tr>\n";

			$reference_array = '1';
			$critical_situation = 0;	
			foreach ($hardware_cache[$reference_array] as $label => $value) {
					$this->html .= "<th>$label</th>\n";
			}
			$this->html .= "</tr>\n<tr>";
			for ($i = 1; $i <= array_key_last($list_memories); $i++) {
				if (!array_key_exists($i, $list_memories) && array_key_exists($i, $hardware_cache)) {
					foreach ($hardware_cache["$i"] as $feature => $value) {
						if ($value == 'Unknown' or $value == '') { 
							$this->html .= "<td style='text-align:center'> Não Informado </td>\n";
							continue;
						}
						if ($feature == "HARDWARE_ID") {
							$sql = "SELECT userid FROM hardware WHERE id = '$value'";
							$result_id = mysqli_query($connection, $sql);
							$id = mysqli_fetch_array($result_id);
							if ($id['userid'] == '') {
								$this->html .= "<td style='text-align:center'> Não Encontrado </td><td bgcolor='#e31111' style='text-align:center'> Computador Removido </td>\n";
								$critical_situation++;
							} else {
								$this->html .= "<td style='text-align:center'>" .$id['userid']."</td><td bgcolor='#e31111' style='text-align:center'> Removido </td>\n";
							}
						} else {
							$this->html .= "<td style='text-align:center'>$value</td>\n";
						}
					}
					$this->html .= "</tr><tr>";
				}
			}	
					$this->html .= "</table></center>";
					if ($critical_situation > 0)
						$this->html .= "Lista de Avisos:<br>- Foi detectado a remoção de um ativo no seu parque tecnológico.";
					else 
						$this->html .= "Lista de Avisos:<br>- Não foi detectado nenhuma situação crítica.";
		}
	}

}
?>

