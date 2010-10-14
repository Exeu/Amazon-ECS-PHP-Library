<?php
if ("cli" !== PHP_SAPI)
{
    echo "<pre>";
}

require 'lib/AmazonECS.class.php';

try
{
    $amazonEcs = new AmazonECS("AKIAJE5FDEZCRP3YZZDQ", "U2O/i63B/PnuKDQiD8p3oWsOZpxQI6Fqp+kMus9R");
    $response = $amazonEcs->category('DVD')->responseGroup('Images')->search("Matrix Revolutions");

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