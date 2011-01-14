<?php
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

require '../lib/AmazonECS.class.php';

try
{
    // get a new object with your API Key and secret key. Lang is optional.
    // if you leave lang blank it will be US.
    $amazonEcs = new AmazonECS(AWS_API_KEY, AWS_API_SECRET_KEY, 'DE');


    // changing the category to DVD and the response to only images and looking for some matrix stuff.
    $response = $amazonEcs->category('DVD')->responseGroup('Images')->search("Matrix Revolutions");
    //var_dump($response);

    // from now on you want to have pure arrays as response
    $amazonEcs->setReturnType(AmazonECS::RETURN_TYPE_ARRAY);

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
    $response = $amazonEcs->country('us')->search('MySql');
    //var_dump($response);

    // or Japan?
    $response = $amazonEcs->country('jp')->search('MySql');
    //var_dump($response);

   // Back to DE and looking for some Music !! Warning "Large" produces a lot of Response
   $response = $amazonEcs->country('de')->category('Music')->responseGroup('Large')->search('The Beatles');
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
}
catch(Exception $e)
{
  echo $e->getMessage();
}

if ("cli" !== PHP_SAPI)
{
    echo "</pre>";
}
