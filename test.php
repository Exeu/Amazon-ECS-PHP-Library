<?php
if ("cli" !== PHP_SAPI)
{
    echo "<pre>";
}

require 'lib/AmazonECS.class.php';


try 
{
    $test = new AmazonECS("", "");
    $test->category('DVD')->search("Matrix Revolutions");
}
catch(Exception $e)
{
  echo $e->getMessage();
}

