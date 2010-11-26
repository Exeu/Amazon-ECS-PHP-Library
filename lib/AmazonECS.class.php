<?php
/**
 * Amazon ECS Class
 * http://www.amazon.com
 * =====================
 *
 * This class fetchs productinformation via the Product Advertising API by Amazon (formerly ECS).
 * It supports two basic operations: ItemSearch and ItemLookup.
 * These operations could be expanded with extra prarmeters to specialize the query.
 *
 * Requirement is the PHP extension SOAP.
 *
 * @package AmazonECS
 * @license http://www.gnu.org/licenses/gpl.txt GPL
 * @version 1.0
 * @author  Exeu <exeu65@googlemail.com>
 * @link http://github.com/Exeu/Amazon-ECS-PHP-Library/wiki Wiki
 * @link http://github.com/Exeu/Amazon-ECS-PHP-Library Source
 */
class AmazonECS
{
  /**
   * Basic Responsetypes
   *
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
    'returnType'    => self::RETURN_TYPE_OBJECT,
    'responseGroup' => 'Small',
    'optionalParameters' => array()
  );

  /**
   * @param string $accessKey
   * @param string $secretKey
   * @param string $country
   * @param string $associateTag
   */
  public function __construct($accessKey, $secretKey, $country = 'US', $associateTag = '')
  {
    if (empty($accessKey) || empty($secretKey))
    {
      throw new Exception('No Access Key or Secret Key has been set');
    }

    $this->requestConfig['accessKey']     = $accessKey;
    $this->requestConfig['secretKey']     = $secretKey;
    $this->requestConfig['associateTag']  = $associateTag;
    $this->responseConfig['country']      = $country;
  }

  /**
   * execute search
   *
   * @param string $pattern
   *
   * @return array|object return type depends on setting
   *
   * @see returnType()
   */
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


  public function lookup($asin)
  {
    $params = $this->buildRequestParams('ItemLookup', array(
      'ItemId' => $asin,
    ));

    return $this->returnData(
      $this->performSoapRequest("ItemLookup", $params)
    );
  }

  /**
   * Builds the request parameters
   *
   * @param string $function
   * @param array  $params
   *
   * @return array
   */
  protected function buildRequestParams($function, array $params)
  {
    $associateTag = array();

    if(false === empty($this->requestConfig['associateTag']))
    {
      $associateTag = array('AssociateTag' => $this->requestConfig['associateTag']);
    }

    return array_merge(
      $associateTag,
      array(
        'AWSAccessKeyId' => $this->requestConfig['accessKey'],
        'Request' => array_merge(
          array('Operation' => $function),
          $params,
          $this->responseConfig['optionalParameters'],
          array('ResponseGroup' => $this->prepareResponseGroup())
    )));
  }

  /**
   * Prepares the responsegroups and returns them as array
   *
   * @return array|prepared responsegroups
   */
  protected function prepareResponseGroup()
  {
    if (false === strstr($this->responseConfig['responseGroup'], ','))
      return $this->responseConfig['responseGroup'];

    return explode(',', $this->responseConfig['responseGroup']);
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
      array('exceptions' => 1)
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

  /**
   * provides current gm date
   *
   * primary needed for the signature
   *
   * @return string
   */
  final protected function getTimestamp()
  {
    return gmdate("Y-m-d\TH:i:s\Z");
  }

  /**
   * provides the signature
   *
   * @return string
   */
  final protected function buildSignature($request)
  {
    return base64_encode(hash_hmac("sha256", $request, $this->requestConfig['secretKey'], true));
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
        throw new InvalidArgumentException(sprintf(
          "Unknwon return type %s", $this->responseConfig['returnType']
        ));
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
      switch (true)
      {
        case is_object($value):
          $out[$key] = $this->objectToArray($value);
        break;

        case is_array($value):
          $out[$key] = $this->objectToArray($value);

        break;

        default:
          $out[$key] = $value;
        break;
      }
    }

    return $out;
  }

  /**
   * set or get optional parameters
   *
   * if the argument params is null it will reutrn the current parameters,
   * otherwise it will set the params and return itself.
   *
   * @param array $params the optional parameters
   *
   * @return array|AmazonECS depends on params argument
   */
  public function optionalParameters($params = null)
  {
    if (null === $params)
    {
      return $this->responseConfig['optionalParameters'];
    }

    $this->responseConfig['optionalParameters'] = $params;

    return $this;
  }

  /**
   * Set or get the country
   *
   * if the country argument is null it will return the current
   * country, otherwise it will set the country and reutrn itself.
   *
   * @param string|null $country
   *
   * @return string|AmazonECS depends on country argument
   */
  public function country($country = null)
  {
    if (null === $country)
    {
      return $this->responseConfig['country'];
    }

    $this->responseConfig['country'] = $country;

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
    if (null === $responseGroup)
    {
      return $this->responseConfig['responseGroup'];
    }

    $this->responseConfig['responseGroup'] = $responseGroup;

    return $this;
  }

  public function returnType($type = null)
  {
    if (null === $type)
    {
      return $this->responseConfig['returnType'];
    }

    $this->responseConfig['returnType'] = $type;

    return $this;
  }

  /**
   * Setter/Getter of the AssociateTag.
   * This could be used for late bindings of this attribute
   *
   * @param string $associateTag
   *
   * @return string|AmazonECS depends on associateTag argument
   */
  public function associateTag($associateTag = null)
  {
    if (null === $associateTag)
    {
      return $this->requestConfig['associateTag'];
    }

    $this->requestConfig['associateTag'] = $associateTag;

    return $this;
  }

  /**
   * @deprected use returnType() instead
   */
  public function setReturnType($type)
  {
    return $this->returnType($type);
  }
}
