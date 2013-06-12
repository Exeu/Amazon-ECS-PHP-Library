#No longer maintained
Please use the brand new lib: https://github.com/Exeu/apai-io



#Amazon ECS PHP Library Version 1.3
AmazonECS is a class which searches products and fetches information about it from the amazon product database.

See a working Search-Demo at: http://amazonecs.pixel-web.org

You can see the simple code of the demosite here: https://gist.github.com/4674423

This is realized by the Product Advertising API (former ECS) from Amazon WS Front.
https://affiliate-program.amazon.com/gp/advertising/api/detail/main.html

The AmazonECS class fetches product information via SOAP requests directly from the Amazon-Database.

It supports four basic operations: ItemSearch, ItemLookup, BrowseNodeLookup, SimilarityLookup

These operations could be expanded with extra prarmeters to specialize the query.

Requirement is the PHP extension SOAP.

##Basic Usage:
The usage is quite simple.
Just require the class, create a new object and it's ready to use.
Nothing else to configure.

You just need to pass a category name when doing searches.

``` php
<?php

require_once 'lib/AmazonECS.class.php';
$client = new AmazonECS('YOUR API KEY', 'YOUR SECRET KEY', 'DE', 'YOUR ASSOCIATE TAG');

$response  = $client->category('Books')->search('PHP 5');
var_dump($response);
```

For some very simple examples go to the samples-folder and have a look at the sample files.
These files contain all information you need for building queries successful.

##Demo Site:
Simple Product Search: http://amazonecs.pixel-web.org

##Webservice Documentation:
Hosted on Amazon.com:
http://docs.amazonwebservices.com/AWSECommerceService/latest/DG/

##More information:
See wikipages for  more information:
https://github.com/Exeu/Amazon-ECS-PHP-Library/wiki
