<?php

ini_set("display_errors", true);
ini_set("error_reporting", E_ALL);


//use Magento\Framework\App\Bootstrap;
require '/app/bootstrap.php'; // Magento bootstrap path

function get_cart_items(){

    $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);

    $app = $bootstrap->createApplication('Magento\Framework\App\Http');

    $obj = \Magento\Framework\App\ObjectManager::getInstance();

    $obj->get('Magento\Framework\App\State')->setAreaCode('frontend');

    $storeManager = $obj->create("\Magento\Store\Model\StoreManagerInterface");
    $baseUrl = $storeManager->getStore()->getBaseUrl();

    $variable  = $obj->get('\Magento\Variable\Model\Variable');

    // Set the state
    $state = $obj->get('Magento\Framework\App\State');

    // Getting the object managers dependencies
    $quote          = $obj->get('Magento\Checkout\Model\Session')->getQuote();
    $shoppingcart   = $obj->get('\Magento\Checkout\Model\Cart');
    $imagehelper    = $obj->get('\Magento\Catalog\Helper\Image');

    // Get quote and cart items collection
    $quote = $shoppingcart->getQuote();
    $quoteitems = $quote->getAllItems();

    // Getting cart
    $cart = $shoppingcart->getCart();

    // Getting the subtotal of the cart
    $subtotal = number_format($quote->getBaseSubtotal(), 2);
    $qty = 0; //set quantity 0

    $arrProducts = array();

    $mediaurl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

    $j = 0;

    foreach ($quoteitems as $item) {

        $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

        if (isset($options['options'])) {
            $options['options'] = $options['options'] ? $options['options'] : null;
            $customOptions = $options['options'] ? $options['options'] : null;

            if (!empty($customOptions)) {
                foreach ($customOptions as $option) {
                    $arrProducts[$j]['productOption'][] = ['label' => $option['label'], 'value' => $option['value'], 'name' => $item->getName()];
                }
            }
        }

        $arrProducts[$j]['productID'] = $item->getProductId();
        $arrProducts[$j]['productName'] = $item->getName();
        $arrProducts[$j]['productQty'] = $item->getQty();
        $arrProducts[$j]['productPrice'] = number_format($item->getPrice(), 2);
        $qty += $item->getQty();

        $_product = $obj->get('Magento\Catalog\Model\Product')->load($item->getProductId());
        $arrProducts[$j]['productUrl'] = $_product->getUrlKey();

        $_product->getSmallImage();
        $arrProducts[$j]['productImage'] = $mediaurl . 'catalog/product' . $_product->getSmallImage();

        $j++;
    }

    return $arrProducts;
}

?>
