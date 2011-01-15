#Amazon ECS PHP Library V1.1
AmazonECS is a class which searchs products and fetchs information
about it from tha amazon productdatabase.

This is realized by the Product Advertising API (former ECS) from Amazon WS Front.
https://affiliate-program.amazon.com/gp/advertising/api/detail/main.html

This class fetchs productinformation via the Product Advertising API by Amazon (formerly ECS).
It supports two basic operations: ItemSearch and ItemLookup.
These operations could be expanded with extra prarmeters to specialize the query.

Requirement is the PHP extension SOAP.

#Basic Usage:

    $client = new AmazonECS('YOUT API KEY', 'YOUR SECRET KEY', 'DE');

    $response  = $client->category('Books')->search('PHP 5');
    var_dump($response);

For more Examples go to the samples folder an have a look at the sample files.

#Webservice Documentation
http://docs.amazonwebservices.com/AWSECommerceService/2010-09-01/DG/