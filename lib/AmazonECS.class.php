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
   * Pattern for GET header
   *
   * @var $requestHeaderPattern
   */
  private $requestHeaderPattern = "GET\n%HOST%\n/onca/xml\n";

  /**
   * @param string $accessKey
   * @param string $secretKey
   */
  public function __construct($accessKey, $secretKey)
  {
    if (empty($accessKey) || empty($secretKey))
    {
      throw new Exception('No Access Key or Secret Key has been set');
    }

    $this->requestConfig['accessKey']   = $accessKey;
    $this->requestConfig['secretKey']   = $secretKey;
  }

  public function search($pattern)
  {
    if (false === isset($this->requestConfig['category']))
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

  public function category($category = null)
  {
    if (null === $category)
    {
      return isset($this->requestConfig['category']) ? $this->requestConfig['category'] : null;
    }

    $this->requestConfig['category'] = $category;
    
    return $this;
  }

  protected function performSoapRequest($function, $params)
  {
    $soapClient = new SoapClient(
      'http://ecs.amazonaws.com/AWSECommerceService/2010-09-01/DE/AWSECommerceService.wsdl', 
      array('exceptions' => 0)
    );
    $soapClient->__setSoapHeaders($this->builSoapHeader($function));

    return $soapClient->__soapCall($function, array($params));
  }

  /**
   * Provides some necessary soap headers
   *
   * @param string $function
   *
   * @return array Each element is a concrete SoapHeader object
   */
  protected function buildSoapHeader($function)
  {
    $timeStamp = $this->getTimestamp();
    $signature = $this->buildSignature($function . $timeStamp);

    return array(
      new SoapHeader(
        'http://security.amazonaws.com/doc/2007-01-01/', 
        'AWSAccessKeyId',  
        $this->requestConfig['accessKey']
      ),
      new SoapHeader(
        'http://security.amazonaws.com/doc/2007-01-01/', 
        'Timestamp',  
        $timeStamp
      ),
      new SoapHeader(
        'http://security.amazonaws.com/doc/2007-01-01/', 
        'Signature',
        $signature
      )
    );
  }

  final protected function getTimestamp()
  {
    return gmdate("Y-m-d\TH:i:s\Z");
  }

  final protected function buildSignature($request)
  {
    return base64_encode(hash_hmac("sha256", $request, $this->requestConfig['secretKey'], true));
  }
}
