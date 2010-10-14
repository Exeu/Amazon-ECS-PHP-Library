<?php

/**
 * Amazon ECS Class
 * http://www.amazon.com
 *
 *
 * This Class fetchs Data from the Amazon Productdatabase
 * and returns it in different types.
 *
 * More information under http://www.pixel-web.org
 *
 * @author Jan
 *
 */
class AmazonECS
{
  /**
   * Baseconfigurationstorage
   * @var array
   */
  private $requestConfig = array();

  /**
   *
   * Enter description here ...
   * @var $requestHeaderPattern
   */
  private $requestHeaderPattern = "GET\n%HOST%\n/onca/xml\n";

  /**
   * Enter description here ...
   * @param $accessKey
   * @param $secretKey
   * @param $requestHost
   */
  public function __construct($accessKey, $secretKey)
  {
    if(empty($accessKey) || empty($secretKey))
    {
      throw new Exception('No Access Key or Secret Key has been set');
    }

    $this->requestConfig['accessKey']   = $accessKey;
    $this->requestConfig['secretKey']   = $secretKey;
  }

  public function search($options)
  {

    if(!isset($this->requestConfig['category']))
    {
      throw new Exception('No Category given: Please set it up before');
    }

     $params = array(
      'AWSAccessKeyId' => $this->requestConfig['accessKey'],
      'Request' => array(
      'Operation' => 'ItemSearch',
      'Keywords' => $pattern,
      'SearchIndex' => $this->requestConfig['category'])
    );

    $this->performSoapRequest("ItemSearch", $params);

    return $this;
  }

  public function category($category)
  {
    $this->requestConfig['category'] = $category;
    return $this;
  }

  protected function performSoapRequest($function, $params )
  {
    $timeStamp = $this->getTimestamp();
    $signature = $this->buildSignature($function.$timeStamp);

    $soapHeader = array(
        new SoapHeader('http://security.amazonaws.com/doc/2007-01-01/', 'AWSAccessKeyId',  $this->requestConfig['accessKey']),
        new SoapHeader('http://security.amazonaws.com/doc/2007-01-01/', 'Timestamp',  $timeStamp),
        new SoapHeader('http://security.amazonaws.com/doc/2007-01-01/', 'Signature',  $signature)
    );

    $soapClient = new SoapClient('http://ecs.amazonaws.com/AWSECommerceService/2010-09-01/DE/AWSECommerceService.wsdl', array('exceptions' => 0));
    $soapClient->__setSoapHeaders($soapHeader);

    $res = $soapClient->__soapCall($function, array($params));

    var_dump($res);
  }

  protected function getTimestamp()
  {
    return gmdate("Y-m-d\TH:i:s\Z");
  }

  protected function buildSignature($request)
  {
    return base64_encode(hash_hmac("sha256", $request, $this->requestConfig['secretKey'], True));
  }


}

?>