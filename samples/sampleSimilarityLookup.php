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
    $amazonEcs = new AmazonECS(AWS_API_KEY, AWS_API_SECRET_KEY, 'DE', AWS_ASSOCIATE_TAG);

    // for the new version of the wsdl its required to provide a associate Tag
    // @see https://affiliate-program.amazon.com/gp/advertising/api/detail/api-changes.html?ie=UTF8&pf_rd_t=501&ref_=amb_link_83957571_2&pf_rd_m=ATVPDKIKX0DER&pf_rd_p=&pf_rd_s=assoc-center-1&pf_rd_r=&pf_rd_i=assoc-api-detail-2-v2
    // you can set it with the setter function or as the fourth paramameter of ther constructor above
    $amazonEcs->associateTag(AWS_ASSOCIATE_TAG);

    $response = $amazonEcs->responseGroup('Large')->similarityLookup('B0017TZY5Y');
    //var_dump($response);

    $response = $amazonEcs->responseGroup('Images')->similarityLookup('B0017TZY5Y');
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
