<?php
class ShoppingCart
{
private $DBConnect = "";
private $DBName = "";
private $TableName = "";
private $storeID = "";
private $inventory = array();
private $shoppingCart = array();
private $Orders = array();
private $OrderTable = array();
private $Count = 0;

public function __construct()
{
$this->DBConnect = @new mysqli("localhost", "root", "", "ggg");
if (mysqli_connect_errno())
die("<p>Unable to connect to database.</p>"
. "<p>Error code " . mysqli_connect_errno()
. ": " . mysqli_connect_error()) . "</p>";
}

public function setDatabase($Database) {
$this->DBName = $Database;
@$this->DBConnect->select_db($this->DBName)
or die("<p>Unable to select the database.</p>"
. "<p>Error code " . mysqli_errno($this->DBConnect)
. ": " . mysqli_error($this->DBConnect)) . "</p>";
}

public function setTableName($Table) {
$this->TableName = $Table;
}

public function setStoreID($storeID)
{
if($this->storeID != $storeID)
{
$this->storeID = $storeID;
$SQLString = "SELECT * FROM inventory " .
" WHERE storeID = '" .$this->storeID ."'";
$QueryResult = @$this->DBConnect->query($SQLString);
if($QueryResult === FALSE)
{
$this->storeID = "";
}
else
{
$this->inventory = array();
while(($Row = $QueryResult->fetch_assoc()) !== NULL)
{
$this->inventory[$Row['productID']] = array();
$this->inventory[$Row['productID']]['name'] = $Row['name'];
$this->inventory[$Row['productID']]['description'] = $Row['description'];
$this->inventory[$Row['productID']]['price'] = $Row['price'];
$this->shoppingCart[$Row['productID']] = 0;
}
}//ELSE
} //IF
}

public function getProductList(){
$retval = FALSE;
$subtotal = 0;
if(count($this->inventory) > 0)
{
echo"<table width= '100%'>\n";
echo"<tr><th>Product</th><th>Description</th>"."<th>Price Each</th><th># in Cart</th>" ."<th>Total Price</th><th>&nbsp;</th></tr>\n";
foreach($this->inventory as $ID => $Info)
{
echo "<tr><td>".
htmlentities($Info['name'])."</td>\n";
echo "<td>".htmlentities($Info['description'])."</td>\n";
printf("<td class= 'currency'>$%.2f</td>\n", $Info['price']);
echo "<td class= 'currency'>".(isset($this->shoppingCart[$ID]) ? $this->shoppingCart[$ID] : 0)."</td>\n";
printf("<td class= 'currency'>$%.2f</td>\n", $Info['price'] * (isset($this->shoppingCart[$ID]) ? $this->shoppingCart[$ID] : 0));
echo "<td><a href='" .$_SERVER['SCRIPT_NAME'] ."?PHPSESSID=" . session_id() ."&ItemToAdd=$ID'>Add " ." Item</a><br />\n";
echo "<a href='" . $_SERVER['SCRIPT_NAME']."?PHPSESSID=" . session_id() ."&ItemToRemove=$ID'>Remove " ." Item</a></td>\n";
$subtotal += ($Info['price'] * (isset($this->shoppingCart[$ID]) ? $this->shoppingCart[$ID] : 0)); }
echo "<tr><td colspan= '4'>Subtotal</td>\n";
printf("<td class= 'currency'>$%.2f</td>\n", $subtotal);
echo "<td><a href='" .$_SERVER['SCRIPT_NAME'] ."?PHPSESSID=" . session_id() ."&EmptyCart=TRUE'>Empty "." Cart</a></td></tr>\n";
echo"</table>";
$retval = TRUE; }
return($retval);
}

private function addItem()
{
if(!empty($_GET['ItemToAdd']))
{
$ProdID = $_GET['ItemToAdd'];
if(array_key_exists($ProdID, $this->shoppingCart))
{
$this->shoppingCart[$ProdID]++;
}
else
{
$this->shoppingCart[$ProdID] = 1;
}
}
}

private function addOne()
{
if(!empty($_GET['AddOne']))
{
$itemID = $_GET['AddOne'];
if(isset($this->shoppingCart[$itemID]))
{
$this->shoppingCart[$itemID]++;
}
}
}

private function removeItem()
{
if(!empty($_GET['ItemToRemove']))
{
$itemID = $_GET['ItemToRemove'];
if(isset($this->shoppingCart[$itemID]) && $this->shoppingCart[$itemID] > 0)
{
$this->shoppingCart[$itemID]--;
$this->Count--;
if($this->shoppingCart[$itemID] == 0)
{
unset($this->shoppingCart[$itemID]);
}
}
}
}

private function emptyCart()
{
if(!empty($_GET['EmptyCart']))
{
$this->shoppingCart = array();
$this->Count = 0;
}
}

private function removeAll()
{
if(!empty($_GET['RemoveLine']))
{
$itemID = $_GET['RemoveLine'];
if(isset($this->shoppingCart[$itemID]))
{
$this->Count -= $this->shoppingCart[$itemID];
unset($this->shoppingCart[$itemID]);
}
}
}

public function processUserInput()
{
if(!empty($_GET['ItemToAdd']))
$this->addItem();
if(!empty($_GET['AddOne']))
$this->addOne();
if(!empty($_GET['ItemToRemove']))
$this->removeItem();
if(!empty($_GET['EmptyCart']))
$this->emptyCart();
if(!empty($_GET['RemoveAll']))
$this->removeAll();
}

public function showCart()
{
echo "<h2>Your Shopping Cart</h2>";
$hasItems = false;
$grandTotal = 0;
foreach($this->shoppingCart as $quantity)
{
	if($quantity > 0)
	{
		$hasItems = true;
		break;
		}
		}
		if($hasItems)
		{
echo "<form method='post' action='checkout.php'>";
echo "<table width='100%' border='1'>";
echo "<tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th></tr>";
foreach($this->inventory as $ID => $Info)
{
if(isset($this->shoppingCart[$ID]) && $this->shoppingCart[$ID] > 0)
{
$itemTotal = $Info['price'] * $this->shoppingCart[$ID];
$grandTotal += $itemTotal;
echo "<tr>";
echo "<td>" . htmlentities($Info['name']) . "</td>";
printf("<td class='currency'>$%.2f</td>", $Info['price']);
echo "<td>" . $this->shoppingCart[$ID] . "</td>";
printf("<td class='currency'>$%.2f</td>", $itemTotal);
echo "</tr>";
}
}
echo "<tr><td colspan='3'><strong>Grand Total:</strong></td>";
printf("<td class='currency'><strong>$%.2f</strong></td></tr>", $grandTotal);
echo "</table>";
echo "<p><input type='hidden' name='PHPSESSID' value='" . session_id() . "' /></p>";
echo "<br><input type='submit' name='checkout' value='Proceed to Checkout'>";
echo " <a href='" . $_SERVER['SCRIPT_NAME'] . "?PHPSESSID=" . session_id() . "&EmptyCart=TRUE'>Empty Cart</a>";
echo "</form>";
}
else
{
echo "<p>Your cart is empty.</p>";
}
echo "<p><a href='index.php?PHPSESSID=" . session_id() . "'>Continue Shopping</a></p>";
}

public function getStoreInformation()
{
return array(
'name' => 'Gosselin Gourmet Coffees',
'description' => 'Premium Coffee Store',
'welcome' => 'Welcome to our gourmet coffee store!',
'css_file' => 'style.css'
);
}
}
?>