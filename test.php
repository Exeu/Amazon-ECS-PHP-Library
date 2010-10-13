<pre>
<?php
require 'lib/AmazonECS.class.php';


try {
$test = new AmazonECS("", "");
}
catch(Exception $e)
{
  echo $e->getMessage();
}
$test->category('DVD')->search("Matrix Revolutions");

?>