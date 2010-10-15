<?php
if ("cli" !== PHP_SAPI)
{
    echo "<pre>";
}

require 'lib/AmazonECS.class.php';

try
{
    $amazonEcs = new AmazonECS('API KEY', 'SECRET KEY', 'DE');

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

   $response = $amazonEcs->responseGroup('Small')->optionalParameters(array('ItemPage' => 2))->search('Bruce Willis');
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