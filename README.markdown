#Amazon ECS PHP Library Version 1.3
AmazonECS is a class which searchs products and fetchs information
about it from tha amazon productdatabase.

See a working Search-Demo at: http://amazonecs.pixel-web.org

This is realized by the Product Advertising API (former ECS) from Amazon WS Front.
https://affiliate-program.amazon.com/gp/advertising/api/detail/main.html

The AmazonECS class fetchs productinformation via SOAP Requests directly from the Amazon-Database.

It supports four basic operations: ItemSearch, ItemLookup, BrowseNodeLookup, SimilarityLookup

These operations could be expanded with extra prarmeters to specialize the query.

Requirement is the PHP extension SOAP.

##Basic Usage:

    require_once 'lib/AmazonECS.class.php';
    $client = new AmazonECS('YOUT API KEY', 'YOUR SECRET KEY', 'DE', 'YOUR ASSOCIATE TAG');

    $response  = $client->category('Books')->search('PHP 5');
    var_dump($response);

For some very simple examples go to the samples-folder and have a look at the sample files.
These files contain all information you need for building querys successful.

##Webservice Documentation:
Hosted on Amazon.com:
http://docs.amazonwebservices.com/AWSECommerceService/latest/DG/

##More information:
See wikipages for  more information:
https://github.com/Exeu/Amazon-ECS-PHP-Library/wiki