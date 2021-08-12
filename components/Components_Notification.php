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
		//sql .= "SELECT * FROM hardware";
		$result_memories = mysqli_query($connection, $sql);
		
		$count_memories = 0;
		while ($item_memory = mysqli_fetch_array($result_memories)) {
			$list_memories[$item_memory['ID']]['TYPE'] = $item_memory['TYPE'];
			$list_memories[$item_memory['ID']]['CAPACITY'] = $item_memory['CAPACITY'];
		//	$list_memories[$item_memory['ID']]['HOST'] = $item_memory['NAME'];
		}
		$count_memories = count($list_memories);

		$sql = "SELECT amount FROM hardware_components WHERE name = 'memories'";
		$result_hardware = mysqli_query($connection, $sql);
		$hardware_result = mysqli_fetch_array($result_hardware);
					
		if ($count_memories > $hardware_result['amount']) {
			$sql = "UPDATE hardware_components SET amount = $count_memories WHERE name = 'memories'";
			mysqli_query($connection, $sql);
			$this->get_html_general_information($list_memories);
			echo 'i am here';
			//Send_Email($this->html);  // The notification email will be send 
		} else {
			if ($count_memories < $hardware_result['amount'])
				$this->amount_memories = $count_memories;
				$this->get_html_general_information($list_memories);
				//Send_Email($this->html);  // The notification email will be send
		}
			$connection->close();
	}

	// Generate the html body for email //
	public function get_html_general_information($list_memories) {
		$this->html .= "
			<hr>
			<center>
				<h1> Novos Componentes de Hardware Adicionados <h1>
			</center>
			<hr>
			<center>
				<table border='1'>
					<h2> Memories <h2>	
				<tr>";
		foreach ($list_memories as $memory) {
			foreach ($memory as $feature => $value) {
				$this->html .= "<td> $feature <td>";
			} 
		}

		$this->html .= "</tr> <tr>";

		foreach ($list_memories as $memory) {
			foreach ($memory as $feature => $value) {
				if ($value == "Unknown") continue;
				$this->html .= "<td> $value </td>";
			}		
		}
		$this->html .= "</tr>
			</table>
			<center>"; 
	}
}
?>

