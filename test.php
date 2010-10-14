<?php
if ("cli" !== PHP_SAPI)
{
    echo "<pre>";
}

require 'lib/AmazonECS.class.php';

try
{
    $amazonEcs = new AmazonECS('', '', 'DE');
    $response = $amazonEcs->category('DVD')->responseGroup('Images')->country('US')->search("Matrix Revolutions");

    // vardumping the response
    var_dump($response);
}
catch(Exception $e)
{
  echo $e->getMessage();
}

if ("cli" !== PHP_SAPI)
{
    echo "</pre>";
}