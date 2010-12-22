<?php
function getResultXML($operation, $code, $message) {
	$output = "<webServiceResult><operation>$operation</operation><code>$code</code><message>$message</message></webServiceResult>";
	return $output;
}

function getFunctionNotImplementedError($operation, $format="xml") {
	$code = -4;
	$message = "This function has not been implemented.";
	if ($format == "json") {
		return json_encode(array('operation'=>$operation, 'code'=>$code, 'message'=>$message));
	}
	else {
		return getResultXML($operation, $code, $message);
	}
}

function getNoDataError($operation, $format="xml") {
	$code = -6;
	$message = "No data passed to service";
	if ($format == "json") {
		return json_encode(array('operation'=>$operation, 'code'=>$code, 'message'=>$message));
	}
	else {
		return getResultXML($operation, $code, $message);
	}
}

function getUnknownError($operation, $errorMessage, $format="xml") {
	$code = -100;
	$message = $errorMessage;
	if ($format == "json") {
		return json_encode(array('operation'=>$operation, 'code'=>$code, 'message'=>$message));
	}
	else {
		return getResultXML($operation, $code, $message);
	}
}

function getSoapFault($ret) {
	return new soap_fault($ret['errorCode'], $ret['errorActor'], $ret['errorMessage'], $ret['errorDetail']);
}
?>
