<?php 
session_start();

require_once("../../../utils/config.php");
require_once("../../../utils/acl.php");
require_once("../../../utils/fileManager.php");
require_once('../../../view/admin/ViewTemplateAdmin.php');
require_once('../../../view/admin/ViewBrand.php');
require_once('../../../model/ModelBrand.php');

// Check if User can reach that controlleur
$right = ACL::getRight( $_SERVER["REQUEST_URI"], $_SESSION['admin']['role_id'] );

if ( $right && isset($_POST['add']) ) {   // Add mode

  if ( $_FILES['image']["name"] != "" && $_FILES['image']["name"] != null ) {
    $extensions = ["jpg", "JPG", "jpeg", "JPEG", "png", "PNG", "gif", "GIF", "svg", "SVG"];
    $upload = FileManager::upload( $extensions, $_FILES['image'], $config['imagePath']['products'] );
    $_POST['image'] = $upload['uploadOk'] ? $upload['file_name'] : null;
  } else {
    $_POST['image'] = null;
  }

  $modelBrand = new ModelBrand();
  $brandId = $modelBrand->addBrand( $_POST );

  header('Location: show.php?id=' . $brandId);

} else if ( $right && isset($_POST['cancel']) ) {

  header('Location: list.php'); 
  
} else {

  $modelBrand = new ModelBrand();
  $brands = $modelBrand->getBrands();
  
  $brand = [ 
    'id'            => null, 
    'title'         => null, 
    'image'         => null,
    'description'   => null
  ];

  $pageTitle = "Nouvelle Marque"
?>
  <?php ViewTemplateAdmin::genHead( $config, $pageTitle . " - " . $config['companyName'] ); ?>
  <main class="container-fluid p-0 w-100 d-flex">
    <aside class="sidebar"><?php ViewTemplateAdmin::genSidebar(); ?></aside>
    <section class="w-100 h-100 content">
      <?php 
        ViewTemplateAdmin::genHeader( $config, "Marques" );
        ViewBrand::genBrandsToolbar( $pageTitle, true );
        if ( !$right ) {
          echo '<h2 class="mt-5 fw-bold text-center text-danger">Désolé, vous n\'avez pas l\'authorisation de venir ici...</h2>';
        } else {
          ViewBrand::genBrandForm( 'add', $config, $brand );
        }
      ?>
    </section>
  </main>
<?php ViewTemplateAdmin::genFooter( $config, [] ); ?>

<?php
}
?>
