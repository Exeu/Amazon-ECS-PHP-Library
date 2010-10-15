<?php
if ("cli" !== PHP_SAPI)
{
    echo "<pre>";
}

require 'lib/AmazonECS.class.php';

try
{
    $amazonEcs = new AmazonECS('API KEY', 'SECRET KEY', 'DE');

    $response = $amazonEcs->lookup('B0017TZY5Y');
    //var_dump($response);

    $response = $amazonEcs->responseGroup('Images')->lookup('B0017TZY5Y');
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