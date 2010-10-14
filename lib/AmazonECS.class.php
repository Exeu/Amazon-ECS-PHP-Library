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
   * Basic Responsetypes
   * @var integer
   * @var integer
   */
  const RETURN_TYPE_ARRAY  = 1;
  const RETURN_TYPE_OBJECT = 2;

  /**
   * Baseconfigurationstorage
   *
   * @var array
   */
  private $requestConfig = array();


  /**
   * Responseconfig
   *
   * @var array
   */
  private $responseConfig = array(
    'returnType' => self::RETURN_TYPE_OBJECT,
    'responseGroup' => 'Small',
  );

  /**
   * @param string $accessKey
   * @param string $secretKey
   */
  public function __construct($accessKey, $secretKey, $country = 'DE')
  {
    if (empty($accessKey) || empty($secretKey))
    {
      throw new Exception('No Access Key or Secret Key has been set');
    }

    $this->requestConfig['accessKey']   = $accessKey;
    $this->requestConfig['secretKey']   = $secretKey;
    $this->responseConfig['country']    = $country;
  }

  public function search($pattern)
  {
    if (false === isset($this->requestConfig['category']))
    {
      throw new Exception('No Category given: Please set it up before');
    }

    $params = $this->buildRequestParams('ItemSearch', array(
      'Keywords' => $pattern,
      'SearchIndex' => $this->requestConfig['category']
    ));

    return $this->returnData(
      $this->performSoapRequest("ItemSearch", $params)
    );
  }

  protected function buildRequestParams($function, $params)
  {
    return array(
      'AWSAccessKeyId' => $this->requestConfig['accessKey'],
      'Request' => array_merge(
        array('Operation' => $function),
        $params,
        array('ResponseGroup' => $this->responseConfig['responseGroup'])
      )
    );
  }

  public function country($country = null)
  {
    if (null !== $country)
    {
      $this->responseConfig['country'] = $country;
    }

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

  public function responseGroup($responseGroup = null)
  {
    if (null !== $responseGroup)
    {
      $this->responseConfig['responseGroup'] = $responseGroup;
    }

    return $this;
  }

  public function setReturnType($type)
  {
    $this->responseConfig['returnType'] = $type;
    return $this;
  }

  /**
   * Returns the Response either as Array or Array/Object
   *
   * @param object $object
   *
   * @return mixed
   */
  protected function returnData($object)
  {
    switch ($this->responseConfig['returnType'])
    {
      case self::RETURN_TYPE_OBJECT:
        return $object;
      break;

      case self::RETURN_TYPE_ARRAY:
        return $this->objectToArray($object);
      break;

      default:
        return false;
      break;
    }
  }

  /**
   * Transforms the responseobject to an array
   *
   * @param object $object
   *
   * @return array An arrayrepresentation of the given object
   */
  protected function objectToArray($object)
  {
    $out = array();
    foreach ($object as $key => $value)
    {
      switch(true)
      {
        case is_object($value):
          $out[$key] = $this->objectToArray($value);
          break;
        case is_array($value):
          $out[$key] = $this->objectToArray($value);
        break;
      default:
        $out[$key] = $value;
      }
    }
    return $out;
  }

  /**
   * @param string $function Name of the function which should be called
   * @param array $params Requestparameters 'ParameterName' => 'ParameterValue'
   *
   * @return array The response as an array with stdClass objects
   */
  protected function performSoapRequest($function, $params)
  {
    $soapClient = new SoapClient(
      'http://ecs.amazonaws.com/AWSECommerceService/2010-09-01/'.strtoupper($this->responseConfig['country']).'/AWSECommerceService.wsdl',
      array('exceptions' => 0)
    );

    $soapClient->__setSoapHeaders($this->buildSoapHeader($function));

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
