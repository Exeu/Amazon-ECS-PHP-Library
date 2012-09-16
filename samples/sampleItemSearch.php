<?php
/**
 * For a running Search Demo see: http://amazonecs.pixel-web.org
 */

if ("cli" !== PHP_SAPI)
{
    echo "<pre>";
}

if (is_file('sampleSettings.php'))
{
  include 'sampleSettings.php';
}

defined('AWS_API_KEY') or define('AWS_API_KEY', 'API KEY');
defined('AWS_API_SECRET_KEY') or define('AWS_API_SECRET_KEY', 'SECRET KEY');
defined('AWS_ASSOCIATE_TAG') or define('AWS_ASSOCIATE_TAG', 'ASSOCIATE TAG');

require '../lib/AmazonECS.class.php';

try
{
    // get a new object with your API Key and secret key. Lang is optional.
    // if you leave lang blank it will be US.
    $amazonEcs = new AmazonECS(AWS_API_KEY, AWS_API_SECRET_KEY, 'de', AWS_ASSOCIATE_TAG);

    // If you are at min version 1.3.3 you can enable the requestdelay.
    // This is usefull to get rid of the api requestlimit.
    // It depends on your current associate status and it is disabled by default.
    // $amazonEcs->requestDelay(true);

    // for the new version of the wsdl its required to provide a associate Tag
    // @see https://affiliate-program.amazon.com/gp/advertising/api/detail/api-changes.html?ie=UTF8&pf_rd_t=501&ref_=amb_link_83957571_2&pf_rd_m=ATVPDKIKX0DER&pf_rd_p=&pf_rd_s=assoc-center-1&pf_rd_r=&pf_rd_i=assoc-api-detail-2-v2
    // you can set it with the setter function or as the fourth paramameter of ther constructor above
    $amazonEcs->associateTag(AWS_ASSOCIATE_TAG);

    // changing the category to DVD and the response to only images and looking for some matrix stuff.
    $response = $amazonEcs->category('DVD')->responseGroup('Large')->search("Matrix Revolutions");
    //var_dump($response);

    // from now on you want to have pure arrays as response
    $amazonEcs->returnType(AmazonECS::RETURN_TYPE_ARRAY);

    // searching again
    $response = $amazonEcs->search('Bud Spencer');
    //var_dump($response);

    // and again... Changing the responsegroup and category before
    $response = $amazonEcs->responseGroup('Small')->category('Books')->search('PHP 5');
    //var_dump($response);

    // category has been set so lets have a look for another book
    $response = $amazonEcs->search('MySql');
    //var_dump($response);

    // want to look in the US Database? No Problem
    $response = $amazonEcs->country('com')->search('MySql');
    //var_dump($response);

    // or Japan?
    $response = $amazonEcs->country('co.jp')->search('MySql');
    //var_dump($response);

   // Back to DE and looking for some Music !! Warning "Large" produces a lot of Response
   $response = $amazonEcs->country('de')->category('Music')->responseGroup('Small')->search('The Beatles');
   //var_dump($response);

   // Or doing searchs in a loop?
   for ($i = 1; $i < 4; $i++)
   {
     $response = $amazonEcs->search('Matrix ' . $i);
     //var_dump($response);
   }

   // Want to have more Repsonsegroups?                         And Maybe you want to start with resultpage 2?
   $response = $amazonEcs->responseGroup('Small,Images')->optionalParameters(array('ItemPage' => 2))->search('Bruce Willis');
   //var_dump($response);

   // With version 1.2 you can use the page function to set up the page of the resultset
   $response = $amazonEcs->responseGroup('Small,Images')->page(3)->search('Bruce Willis');
   //var_dump($response);
}
catch(Exception $e)
{
  echo $e->getMessage();
}

if ("cli" !== PHP_SAPI)
{
    echo "</pre>";
}
