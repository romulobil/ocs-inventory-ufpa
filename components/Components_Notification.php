<?php
require __DIR__.'/send.php'; 
class Components_Notification {
	public $amount_memories;
	public $html;
	// Verify the memories presents on database //
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
		// TODO Desmembrar as condições em dois if's //
		if ($count_memories > $this->amount_memories || $count_memories < $this->amount_memories) {
			$this->amount_memories = $count_memories;
			$this->get_html_general_information($list_memories);
			Send_Email($this->html);  // The notification email will be send 
		}
	}

	public function get_html_general_information($list_memories) {
		$this->html = "<h1> Novos componentes de hardware foram adicionados <h1><br><h2>Informações da memória<h2><br>";
		foreach ($list_memories as $memory) {
			foreach ($memory as $feature => $value) {
				if ($value == "Unknown") continue;
				$this->html .= " - $value<br>";
			}
		}
	}
}
?>

