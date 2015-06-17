<!-- 
/*******************************************************************************
 *  Copyright 2013 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *
 *  You may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at:
 *  http://aws.amazon.com/apache2.0
 *  This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 *  CONDITIONS OF ANY KIND, either express or implied. See the License
 *  for the
 *  specific language governing permissions and limitations under the
 *  License.
 * *****************************************************************************
 */
 -->
<html>
<head>
<title>Split Shipments Checkout Example</title>
</head>
<body>
<h1>Split Shipments Checkout Example Cont...</h1>


<form name="input" action="SplitShipmentsCheckoutResult.php">
<input type="hidden" name="orderReferenceId" value = '<?php echo $_REQUEST["orderReferenceId"]?>'/>
<input type="hidden" name="totalShipments" value = '<?php echo $_REQUEST["totalShipments"]?>'/>
Select an shipment item for each shipment
<?php 
for($i=1; $i<=$_REQUEST["totalShipments"];$i++) {
 print "<br/> <br/> Item for shipment #$i: ";
 print <<<itemToSelect
<select name="item$i">
<option value="0">Apple</option>
<option value="1">Pineapple</option>
<option value="2">Bannana</option>
<option value="3">Orange</option>
<option value="4">Pear</option>
</select>
itemToSelect;
}

?> 

<br/><br/><br/>
<input type="submit" value="Run Sample"/>
</form>
</body>
</html>