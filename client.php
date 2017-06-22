<?php 
/*
 * This example shows how to use Rfc Connector with PHP. You need to have
 * the COM extension enabled in php.ini, e.g. add
 * 
 *   extension=c:\path\to\php_com_dotnet.dll
 * 
 * to your php.ini. The code will only work with 32bit(x86) PHP on Windows. 
 */

$ts0 = microtime(true);

error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("display_errors", true);

/***************************************************************
 * §1: set up connection, logon, and license key
 ***************************************************************/
 
$rfc = new COM("RfcConnector.NwRfcSession");
$rfc->RfcSystemData->ConnectString = "SAPLOGON_ID=NPL";

// NOTE: using SOAP instead of RFC is faster, but you need to 
// enable it on the server first (transaction SICF):
//$rfc = new COM("RfcConnector.SoapSession");
//$rfc->HttpSystemData->Host = "was-cv3";
//$rfc->HttpSystemData->Port = "8001";

$rfc->LogonData->Client = "001";
$rfc->LogonData->User = "DEVELOPER";
$rfc->LogonData->Password = "developer1";
$rfc->LogonData->Language = "EN";

// NOTE: If you run this example in a server context (e.g. Apache or IIS), you
//       absolutely need a valid license key (otherwise the "nag screen" would
//       cause the process to terminate). If you are using PHP in CLI 
//       (command line) mode, you can comment out the check if you wish
// 
// To get a free evaluation license, visit http://rfcconnector.com/order/

$rfc->LicenseData->owner = "(unregistered DEMO version)";
$rfc->LicenseData->key = "126TYLUD7U7ID2INO4FR9DW7RD7PTSD";

if ($rfc->LicenseData->IsValidLicense()==False) {
	print "ERROR: no valid license set. This program won't work without a license key<br/>";
	print '<br/>If you do not have a license, get a free evaluation license from <a href="http://rfcconnector.com/shop">http://rfcconnector.com/shop</a><br/>';
	print "and insert it into this PHP file";
	die;
}

/***************************************************************
 * §2: connect to SAP system
 ***************************************************************/
try {
	$rfc->Connect();
} catch (com_exception $ex) {
	if ($rfc->Error) {
		print "Error from RFC: '".$rfc->ErrorInfo->Message."'";
	} else {
		print $ex;
	}
	die;
}

/***************************************************************
 * §4: import the function call prototype from the backend
 ***************************************************************/

try {
	$fn = $rfc->ImportCall("BAPI_FLIGHT_GETLIST");
} catch (com_exception $ex) {
	die("ERROR importing function: " . $rfc->ErrorInfo->Message);
}
	
/***************************************************************
 IMPORTANT NOTE REGARDING PERFORMANCE:
 The code above asks the SAP system for the function prototype
 for every HTTP request, which causes unnecessary round trips
 and can seriously impact performance.
 
 It is recommended NOT to use this in production, but serialize
 it to XML using
  
	file_put_contents("name_of_prototype.xml", $fn->ToXML());
 
 ONCE during development, then later, instead of importing the
 call prototype, load it from disk using
 
	$fn = $rfc->CreateCall();
	$fn->FromXML(file_get_contents("name_of_prototype.xml"));
 
 This will save about 300-500ms for each request, compared to
 the import above, and also lower load on network and SAP server
 ***************************************************************/

/***************************************************************
 * §5: set importing parameters (which are sent to backend)
 ***************************************************************/
$fn->Importing["AIRLINE"]->value = "LH";

/***************************************************************
 * §6: call the function
 ***************************************************************/
$rfc->CallFunction($fn, True);

/***************************************************************
 * §7: iterate over result and print it
 ***************************************************************/
print "<table><tr><th>Airline</th><th>Date</th><th>From</th><th>To</th></tr>";

foreach($fn->Tables["FLIGHT_LIST"]->Rows as $row)
{
	print "<tr><td>".$row["AIRLINE"]."</td><td>".$row["FLIGHTDATE"]."</td><td>".$row["CITYFROM"]."</td><td>".$row["CITYTO"]."</td></tr>";
}
print "</table>";

/***************************************************************
 * §8: disconnect from SAP when you are done
 ***************************************************************/
$rfc->disconnect();

print "<small>total processing time: ";
print round(microtime(true)-$ts0,3);
print "s</small>";
