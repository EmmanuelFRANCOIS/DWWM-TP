<?php
require_once('../../../model/DBUtils.php');
require_once("../../../utils/config.php");
require_once("../../../utils/acl.php");

/**
 * @class   ModProductsByRating module
 * @summary Class to extract rated products in DB
 *          and display them where this module
 *          is applied
 */
Class ModProductsByRating {

  /**
   * @function getProductsByRating()
   * @summary return popular products by universe,
   *          by category and/or by brand
   * @param $options = [
   *          'display'     => ['H-Blocks', 'V-Blocks', 'table'] (default : 'H-Blocks')
   *          'moduleTitle' => ['module title'] (default : 'Livres les mieux notés')
   *          'universe_id' => [id of the Universe] (default : null)
   *          'category_id' => [id of the Category] (default : null)
   *          'brand_id'    => [id of the Brand]    (default : null)
   *          'mode'        => ['best' or 'worst']  (default: 'best')
   *          'nb'          => [# of products to return] (default : 4)
   *        ]
   */
  private function getProductsByRating( $options = null ) {

    $whereUnv = $options['universe_id'] ? 'prd.universe_id = ' . $options['universe_id'] : null;
    $whereCat = $options['category_id'] ? 'prd.category_id = ' . $options['category_id'] : null;
    $whereBrd = $options['brand_id']    ? 'prd.brand_id = '    . $options['brand_id']    : null;
    $where    = $whereUnv               ? $whereUnv                                      : '';
    $where   .= $whereCat               ? ($where !== '' ? ' AND ' : '') . $whereCat     : '';
    $where   .= $whereBrd               ? ($where !== '' ? ' AND ' : '') . $whereCat     : '';
    $where    = $where !== ''           ? 'WHERE ' . $where . ' '                        : '';

    switch ($options['mode']) {
      case 'best'  : $orderby = 'ORDER BY pop_score DESC '; break;
      case 'worst' : $orderby = 'ORDER BY prd.rating ASC, prd.rating_num ASC   '; break;
      default      : $orderby = 'ORDER BY pop_score DESC '; break;
    }

    $dbconn = DBUtils::getDBConnection();
    $req = $dbconn->prepare("
      SELECT prd.*, 
             unv.title AS universe, unv.image as universe_image, 
             cat.title as category, cat.image AS category_image, 
             brd.title AS brand, brd.image AS brand_image, 
             prd.rating_num * prd.rating AS pop_score
      FROM product AS prd 
      INNER JOIN universe AS unv ON unv.id = prd.universe_id
      INNER JOIN category AS cat ON cat.id = prd.category_id
      INNER JOIN brand    AS brd ON brd.id = prd.brand_id " . 
      $where . 
      $orderby . " 
      LIMIT 0, " . $options['nb']
    );
    $req->execute();
    // Debug query
    //$req->debugDumpParams();
    return $req->fetchAll(PDO::FETCH_ASSOC);

  }


  /**
   * @function genProductsByRating()
   * @summary  return Html generated code for a module with n popular products
   *           by universe, by category and/or by brand
   * @param    $options = [
   *             'display'     => ['H-Blocks', 'V-Blocks', 'table'] (default : 'H-Blocks')
   *             'moduleTitle' => ['module title'] (default : 'Nouveautés')
   *             'universe_id' => [id of the Universe] (default : null)
   *             'category_id' => [id of the Category] (default : null)
   *             'brand_id'    => [id of the Brand]    (default : null)
   *             'mode'        => ['created' or 'modified'] (default: 'created')
   *             'nb'          => [# of products to return] (default : 4)
   *           ]
   */
  public static function genProductsByRating( $options = null ) {

    // Check Display mode
    switch ( $options['display'] ) {
      case 'H-Blocks' : $display = 'H-Blocks'; break;
      case 'V-Blocks' : $display = 'V-Blocks'; break;
      case 'table'    : $display = 'table';    break;
      default         : $display = 'H-Blocks'; break;
    }

    // Check $nb value
    $nb = $options['nb'];
    $nbH = min($nb, 6);

    // Get Products from DB Product table
    $products = ModProductsByRating::getProductsByRating( $options );
    
    // Generate Html module
  ?>
    <div class="container-fluid">
      <div class="container py-4 my-3">
        <h3 class="text-success text-uppercase module-title"><?php echo $options['moduleTitle']; ?></h3>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-lg-<?php echo $nbH; ?> g-4 mt-2">
          <?php foreach( $products as $product ) { 
            $stockColor = $product['stock'] < 5 ? 'text-danger fw-bold' : 'text-dark';
            $imgsrc = '../../../../images/products/' . ( $product['image'] ? $product['image'] : 'image_BOOK_empty.svg' );
            $imgcatsrc = '../../../../images/categories/' . ( $product['category_image'] ? $product['category_image'] : '' );
          ?>
            <div class="col">
                <div class="card h-100 bg-light text-center">
                  <img src="<?php echo $imgsrc; ?>" class="card-img-top py-0 my-3 mx-auto" style="max-width: 80%; max-height:128px;" alt="<?php echo $product['title']; ?>">
                  <div class="card-body text-start p-2">
                    <a class="text-decoration-none text-dark stretched-link" 
                       href="../../../controller/site/product/show.php?id=<?php echo $product['id']; ?>">
                      <h5 class="card-title"><?php echo $product['title']; ?></h5>
                    </a>
                    <div class="card-text"><?php echo $product['maker']; ?></div>
                    <div class="card-text mt-2"><?php echo $product['brand']; ?></div>
                  </div>
                  <div class="card-footer text-start mt-2 pt-2">
                    <div class="d-flex align-items-content category">
                      <img src="<?php echo $imgcatsrc; ?>" class="" style="max-width: 32px; max-height:24px;" alt="<?php echo $product['category']; ?>">
                      <div class="card-text ms-2"><?php echo $product['category']; ?></div>
                    </div>
                    <?php if ( $product['rating'] ) { ?>
                      <div class="rating mt-3">Note : <?php echo $product['rating']; ?> (#<?php echo $product['rating_num']; ?>)</div>
                    <?php } else { ?>
                      <div class="rating mt-3">Pas de note</div>
                    <?php } ?>
                  </div>
                  <div class="card-footer d-flex justify-content-between p-2 text-end">
                    <div class="price fs-5"><?php echo $product['price']; ?> €</div>
                    <a href="list.php" style="z-index: 10;" class="btn btn-success px-1 pt-1 pb-0"><i class="fa-solid fa-cart-plus fs-5"></i></a>
                  </div>
                </div>
            </div>
          <?php } ?>
        </div>
        <div class="my-3 fs-5 text-end">Voir le classement complet des <a class="btn btn-success fs-5 fw-bold py-1 px-3 more" href="#">"Livres" notés</a></div>
      </div>
    </div>
  <?php

  }

}

?>