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
    $amazonEcs = new AmazonECS(AWS_API_KEY, AWS_API_SECRET_KEY, 'DE');

    $amazonEcs->responseGroup('BrowseNodeInfo');

    $response = $amazonEcs->browseNodeLookup(541686);
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
