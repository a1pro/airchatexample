<?php
require_once('dbcon.php');
require_once('functions.inc.php');

$dataArray = array();
if ($_POST) {
	$dataArray = $_POST;
}
else {
	$dataArray = $_GET;
}

$method = $dataArray['method'];
$format = $dataArray['format'];

if ($format == "json") {
	header('Content-type: application/json');
}
else {
	header('Content-type: text/xml');
}

switch ($method) {
		case "ping":
			ping($method, $format, 
				isset($dataArray['pingText']) ? $dataArray['pingText'] : ""
			);
			break;
		case "createChatMessage":
			createChatMessage($method, $format,
				isset($dataArray['username']) ? $dataArray['username'] : "",
				isset($dataArray['message']) ? $dataArray['message'] : ""
			);
			break;
		case "getChatMessagesAfterTimestamp":
			getChatMessagesAfterTimestamp($method, $format,
				isset($dataArray['timestamp']) ? $dataArray['timestamp'] : ""
			);
			break;
		default:
			echo getFunctionNotImplementedError($method, $format);
}

function ping($method, 
				$format,
				$pingText) {
	if ($format == "json") {
		echo json_encode(array('operation'=>$method, 'code'=>0, 'message'=>"Ping worked: " . $pingText));
	}
	else {
		echo getResultXML($method, 0, "Ping worked: " . $pingText);
	}			
}

function createChatMessage($method, 
							$format,
							$username, 
							$message) {
	$resultCode = 0;
	$output = "";
								
	$query = sprintf("INSERT INTO `chatmessages` (`username`, `received`, `message`, `ipaddress`) VALUES ('%s', %d, '%s', '%s')",
		mysql_real_escape_string($username),
		time(),
		mysql_real_escape_string($message),
		mysql_real_escape_string($_SERVER['REMOTE_ADDR']) 
	);
	
	// Perform Query
	$result = mysql_query($query);
	
	// Check result
	// This shows the actual query sent to MySQL, and the error. Useful for debugging.
	if (!$result) {
		$resultCode = -1;
		if ($format == "json") {
			$output  = 'Invalid query: ' . mysql_error() . "\n";
	    	$output .= 'Whole query: ' . $query;
		}
		else {
			$output = "<error>";
			$output .= 'Invalid query: ' . mysql_error() . "\n";
	    	$output .= 'Whole query: ' . $query;
	    	$output .= "</error>";
		}
	}
	else {
		$resultCode = mysql_insert_id();
		if ($format == "json") {
			$output = $resultCode;
		}
		else {
			$output = "<id>" . $resultCode . "</id>";
		}
	}
	
	if ($format == "json") {
		echo json_encode(array('operation'=>$method, 'code'=>$resultCode, 'message'=> $output));
	}
	else {
		echo getResultXML($method, $resultCode, $output);
	}
	
	mysql_close();
}

function getChatMessagesAfterTimestamp($method, 
										$format,
										$timestamp) {
	$resultCode = 0;
	if ($format == "json") {
		$output = array();
	}
	else {	
		$output = "";
	}
								
	$query = sprintf("SELECT * FROM chatmessages WHERE `received` >= %d",
		$timestamp 
	);
	error_log($query);
	
	// Perform Query
	$result = mysql_query($query);
	
	// Check result
	// This shows the actual query sent to MySQL, and the error. Useful for debugging.
	if (!$result) {
		$resultCode = -1;
		if ($format == "json") {
			$output = array('error'=>'Invalid query: ' . mysql_error() . "\n" . 'Whole query: ' . $query);
		}
		else {
			$output = "<error>";
			$output .= 'Invalid query: ' . mysql_error() . "\n";
	    	$output .= 'Whole query: ' . $query;
	    	$output .= "</error>";
		}
	}
	else {
		$resultCode = 0;
		while ($row = mysql_fetch_assoc($result)) {
			if ($format == "json") {
				array_push($output, 
					array(
						'id'=>$row['id'], 
						'username'=>$row['username'], 
						'received'=>$row['received'], 
						'message'=>$row['message'], 
						'ipaddress'=>$row['ipaddress']
					)
				);
			}
			else {
				$output .= "<ChatMessage>";
				$output .= "<id>" . $row['id'] . "</id>";
				$output .= "<username>" . $row['username'] . "</username>";
				$output .= "<received>" . $row['received'] . "</received>";
				$output .= "<message>" . $row['message'] . "</message>";
				$output .= "<ipaddress>" . $row['ipaddress'] . "</ipaddress>";
				$output .= "</ChatMessage>";
			}
		}
	}
								
	if ($format == "json") {
		echo json_encode(array('operation'=>$method, 'code'=>$resultCode, 'message'=>$output));
	}
	else {
		echo getResultXML($method, $resultCode, $output);
	}
	mysql_close();
}
?>