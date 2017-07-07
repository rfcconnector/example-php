# RFC Connector: PHP Client Example

This example project shows how to call an ABAP function module (or BAPI) with RfcConnector from PHP

Calling a function module involves three steps:

1. Connect to the SAP system using a Session instance
2. Import the function module prototype
3. Call the function with the desired parameters and receive the results

```php

// create an instance of NWRfcSession (alternatively, you could use SOAPSession)
$rfc = new COM("RfcConnector.NwRfcSession");

// set up connection data
$rfc->RfcSystemData->ConnectString = "SAPLOGON_ID=NPL";
$rfc->LogonData->Client = "001";
$rfc->LogonData->User = "DEVELOPER";
$rfc->LogonData->Password = "***";
$rfc->LogonData->Language = "EN";

// connect to the SAP system
$rfc->Connect();

// import the function call definition
$fn = $rfc->ImportCall("BAPI_FLIGHT_GETLIST");

// call the function
$rfc->CallFunction($fn, True);

// process the result
foreach($fn->Tables["FLIGHT_LIST"]->Rows as $row)
{
	print $row["AIRLINE"]; //...
}
```

# Requirements

This code requires any supported x86 (32-Bit) PHP version on Windows. amd64 (64-Bit) PHP 
is not yet supported. 

With PHP 5.4 and later, you need to enable the COM extension in `php.ini`:

```ini
# php.ini
# ...
extension=c:\path\to\php_com_dotnet.dll
```

If you are running the example from within a web server, you need to get a free, 
temporary evaluation key from [http://rfcconnector.com/order/](http://rfcconnector.com/order/),
otherwise the program will not work

# Running the example

You can run the sample either from the command line (PHP CLI) or from within a webserver (e.g. Apache or IIS).

To run the example from the command line, just call it with php.exe:

```
php.exe client.php
```

To run the example in a web server, put client.php into the webroot of your server, and call it from a browser.

For more information, please visit http://rfcconnector.com/
