<?php
session_start();

require_once "../../../utils/config.php";
require_once "../../../view/site/ViewTemplateSite.php";
require_once "../../../view/site/ViewOrders.php";
require_once "../../../model/ModelOrders.php";

// Customer not connected
if ( !isset($_SESSION['site']['id']) ) {   
  header('Location: ../customer/login.php');
  exit;
}

$modelOrders = new ModelOrders();
$orders = $modelOrders->getOrdersTable( $_SESSION['site']['id'] );

ViewTemplateSite::genHead( $config, 'Mes Commandes' );
ViewTemplateSite::genHeader( $config, 'Mes Commandes' );
ViewTemplateSite::genNavBar( $config );
ViewOrders::genOrders( $config, $customer );
ViewTemplateSite::genFooter( $config, [] );

?>
