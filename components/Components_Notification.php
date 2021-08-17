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
		
		$count_memories = 0;
		while ($item_memory = mysqli_fetch_array($result_memories)) {
			$list_memories[$item_memory['ID']]['TYPE'] = $item_memory['TYPE'];
			$list_memories[$item_memory['ID']]['CAPACITY'] = $item_memory['CAPACITY'];
			$list_memories[$item_memory['ID']]['HARDWARE_ID'] = $item_memory['HARDWARE_ID'];
		}
		$count_memories = count($list_memories);
		$sql = "SELECT amount FROM hardware_components WHERE name = 'memories'";
		$result_hardware = mysqli_query($connection, $sql);
		$hardware_result = mysqli_fetch_array($result_hardware);
					
		if ($count_memories > 0) {
			$this->get_html_general_information($list_memories, $hardware_result, $connection, $is_additon = true);
			//Send_Email($this->html);  // The notification email will be send 
			$sql = "UPDATE hardware_components SET amount = $count_memories WHERE name = 'memories'";
			mysqli_query($connection, $sql);
		} else {
			if ($count_memories < $hardware_result['amount']) {
				$this->get_html_general_information($list_memories, $hardware_result, $connection, $is_addition = false);
				$sql = "UPDATE hardware_components SET amount = $count_memories WHERE name = 'memories'";
				//Send_Email($this->html);  // The notification email will be send
			}
		}
			$connection->close();
	}

	// Generate the html body for email //
	public function get_html_general_information($list_memories, $hardware_result, $connection, $is_addition) {
		if ($is_addition) {
			$this->html .= "
				<hr>
				<center>
					<h1> Novos Componentes de Hardware Adicionados <h1>
				</center>
				<hr>
				<center>
					<h2> Memories <h2>	
					<table border='1'>
					<tr>\n";
			$reference_array = '1';
			foreach ($list_memories[$reference_array] as $label => $value) {
					$this->html .= "<th>$label</th>\n";
			} 
			$this->html .= "</tr>\n<tr>";
			
			for ($i = 1; $i <= array_key_last($list_memories); $i++) {
				if (!array_key_exists($i, $list_memories)) {
					continue;
				}
				foreach ($list_memories["$i"] as $feature => $value) {
					if ($value == 'Unknown' or $value == '') { 
						$this->html .= "<td style='text-align:center'> Não Informado </td>\n";
						continue;
					}
					if ($feature == "HARDWARE_ID") {
						$sql = "SELECT userid FROM hardware WHERE id = '$value'";
						$result_id = mysqli_query($connection, $sql);
						$id = mysqli_fetch_array($result_id);	
						$this->html .= "<td style='text-align:center'>" . $id['userid']."</td><td style='text-align:center'> Adicionado </td>\n";
					} else {
						$this->html .= "<td style='text-align:center'>$value</td>\n";
					}
				}
				$this->html .= "</tr><tr>";
			}
				$this->html .= "</table>";
		} else {
			$this->html .= "
				<br>
				<hr>
				<center>
					<h1> Novos Componentes de Hardware Retirados <h1>
				</center>
				<hr>
				<center>
					<h2> Memories <h2>	
					<table border='1'>
					<tr>\n";

			$reference_array = '1';
			foreach ($list_memories[$reference_array] as $label => $value) {
					$this->html .= "<th>$label</th>\n";
			}
			$this->html .= "</tr>\n<tr>";
			
			for ($i = 1; $i <= array_key_last($list_memories); $i++) {
				if (!array_key_exists($i, $list_memories)) {
					$this->html .= "<td style='text-align:center'> Removido </td>\n";	
					$this->html .= "</tr><tr>\n";
				}
			}
				$this->html .= "</table>";
		
		}
	}

}
?>

