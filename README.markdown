#Amazon ECS PHP Library Version 1.1
AmazonECS is a class which searchs products and fetchs information
about it from tha amazon productdatabase.

This is realized by the Product Advertising API (former ECS) from Amazon WS Front.
https://affiliate-program.amazon.com/gp/advertising/api/detail/main.html

The AmazonECS class fetchs productinformation via SOAP Requests directly from the Amazon-Database.

It supports two basic operations: ItemSearch and ItemLookup (**Version <= 1.0**)
and three operations: ItemSearch, ItemLookup and BrowseNodeLookup (**Version >= 1.1**)

These operations could be expanded with extra prarmeters to specialize the query.

Requirement is the PHP extension SOAP.

##Basic Usage:

    require_once 'lib/AmazonECS.class.php';
    $client = new AmazonECS('YOUT API KEY', 'YOUR SECRET KEY', 'DE');

    $response  = $client->category('Books')->search('PHP 5');
    var_dump($response);

For some very simple examples go to the samples-folder and have a look at the sample files.
These files contain all information you need for building querys successful.

##Webservice Documentation:
Hosted on Amazon.com:
http://docs.amazonwebservices.com/AWSECommerceService/2010-09-01/DG/

##More information:
See wikipages for  more information:
https://github.com/Exeu/Amazon-ECS-PHP-Library/wiki