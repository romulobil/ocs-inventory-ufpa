<?php
require __DIR__.'/send.php'; 
class Components_Notification {
	public $amount_memories;
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
		}
		$count_memories = count($list_memories);
		if ($count_memories > $this->amount_memories) {
			$this->amount_memories = $count_memories;
			$this-verify_db_table($connection, $this->amount_memories, "memory");
			$this->get_html_general_information($list_memories);
			Send_Email($this->html);  // The notification email will be send 
		} else {
			if ($count_memories < $this->amount_memories)
			$this->amount_memories = $count_memories;
			$this->get_html_general_information($list_memories);
			Send_Email($this->html);  // The notification email will be send
		}
			$connection->close();
	}

	// Generate the html body for email //
	public function get_html_general_information($list_memories) {
		$this->html = "<h1> Novos componentes de hardware foram adicionados <h1><br><h2>Informações da memória<h2><br>";
		foreach ($list_memories as $memory) {
			foreach ($memory as $feature => $value) {
				if ($value == "Unknown") continue;
				$this->html .= " - $value<br>";
			}
		}
	}

	// Check whether components table exists on database //	
	private function verify_db_table($connection, $amount_memories, $component) {
		$table = 'hardware_components'
		$sql_create_table = "CREATE TABLE hardware_components (component_id INT NOT NULL AUTO_INCREMENT, name VARCHAR(40), amount INT NOT NULL, PRIMARY KEY ( id_component )";
		$sql_insert_value = "INSERT INTO hardware_components (name, amount) VALUES ('$component', '$amount_memories')";
		$sql_update_value = "UPDATE hardware_components SET amount=$amount_memories WHERE name=$component"
		$query_table_result = mysqli_query("SHOW TABLES LIKE '$table'");
		$query_value_result = mysqli_query("SELECT component_id FROM hardware_components WHERE name = '$component'");

		$query_table_result = $result && $result->num_rows > 0;
		// check if table exist //
		if ($query_table_result) {
			// check if the entry exist //
			if ($query_value_result) {
				if ($connection->query($sql_update_value) === TRUE)
					echo "Value has been update sucessfully\n";
				else 
					echo "Value hasn't been update" . $connection->error;
			} else {
				if ($connection->query($sql_insert_value) === TRUE) 
					echo "Value has been insert sucessfully\n";
				else 
					echo "Value hasn't been insert" . $connection->error;

			}		
		} else {		
			if ($connection->query($sql_create_table) === TRUE)
				echo "Table has been create sucessfully\n";
			else 
				echo "Don't hasn't been create" . $connection->error;
		}
	}
}
?>

