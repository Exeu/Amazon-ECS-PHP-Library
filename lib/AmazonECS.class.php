<?php
/**
 * Amazon ECS Class
 * http://www.amazon.com
 * =====================
 *
 * This class fetchs productinformation via the Product Advertising API by Amazon (formerly ECS).
 * It supports three basic operations: ItemSearch, ItemLookup and BrowseNodeLookup.
 * These operations could be expanded with extra prarmeters to specialize the query.
 *
 * Requirement is the PHP extension SOAP.
 *
 * @package      AmazonECS
 * @license      http://www.gnu.org/licenses/gpl.txt GPL
 * @version      1.3.3-DEV
 * @author       Exeu <exeu65@googlemail.com>
 * @contributor  Julien Chaumond <chaumond@gmail.com>
 * @link         http://github.com/Exeu/Amazon-ECS-PHP-Library/wiki Wiki
 * @link         http://github.com/Exeu/Amazon-ECS-PHP-Library Source
 */
class AmazonECS
{
  const RETURN_TYPE_ARRAY  = 1;
  const RETURN_TYPE_OBJECT = 2;

  /**
   * Baseconfigurationstorage
   *
   * @var array
   */
  private $requestConfig = array();

  /**
   * Responseconfigurationstorage
   *
   * @var array
   */
  private $responseConfig = array(
    'returnType'          => self::RETURN_TYPE_OBJECT,
    'responseGroup'       => 'Small',
    'optionalParameters'  => array()
  );

  /**
   * All possible locations
   *
   * @var array
   */
  private $possibleLocations = array(
    'de'    =>   1,
    'com'   =>   2,
    'co.uk' =>   4,
    'ca'    =>   8,
    'fr'    =>  16,
    'co.jp' =>  32,
    'it'    =>  64,
    'cn'    => 128,
    'es'    => 256,
  );

  /**
   * The WSDL File
   *
   * @var string
   */
  protected $webserviceWsdl = 'http://webservices.amazon.com/AWSECommerceService/AWSECommerceService.wsdl';

  /**
   * The SOAP Endpoint
   *
   * @var string
   */
  protected $webserviceEndpoint = 'https://webservices.amazon.%%COUNTRY%%/onca/soap?Service=AWSECommerceService';

  /**
   * @param string $accessKey
   * @param string $secretKey
   * @param string $country
   * @param string $associateTag
   */
  public function __construct($accessKey, $secretKey, $country, $associateTag)
  {
    if (empty($accessKey) || empty($secretKey))
    {
      throw new Exception('No Access Key or Secret Key has been set');
    }

    $this->requestConfig['accessKey']     = $accessKey;
    $this->requestConfig['secretKey']     = $secretKey;
    $this->associateTag($associateTag);
    $this->country($country);
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
  public function search($pattern, $nodeId = null)
  {
    if (false === isset($this->requestConfig['category']))
    {
      throw new Exception('No Category given: Please set it up before');
    }

    $browseNode = array();
    if (null !== $nodeId && true === $this->validateNodeId($nodeId))
    {
      $browseNode = array('BrowseNode' => $nodeId);
    }

    $params = $this->buildRequestParams('ItemSearch', array_merge(
      array(
        'Keywords' => $pattern,
        'SearchIndex' => $this->requestConfig['category']
      ),
      $browseNode
    ));

    return $this->returnData(
      $this->performSoapRequest("ItemSearch", $params)
    );
  }

  /**
   * Get the SearchIndexes for the given country (otherwise return all)
   *
   * @see <a href="http://docs.amazonwebservices.com/AWSECommerceService/latest/DG/APPNDX_SearchIndexValues.html">AWS Documentation - Product Advertising API - Search Index Support by Locale</a>
   *
   * @param string|null $country
   *
   * @return array
   */
  public function searchIndex($country = null)
  {
    //-- Shorthands for bit map
    $LOC_US = $this->possibleLocations['com'];
    $LOC_UK = $this->possibleLocations['co.uk'];
    $LOC_DE = $this->possibleLocations['de'];
    $LOC_JP = $this->possibleLocations['co.jp'];
    $LOC_FR = $this->possibleLocations['fr'];
    $LOC_CA = $this->possibleLocations['ca'];
    $LOC_IT = $this->possibleLocations['it'];
    $LOC_CN = $this->possibleLocations['cn'];
    $LOC_ES = $this->possibleLocations['es'];

    if(null === $country)
    {
      //-- no country specified, use set value
      if(array_key_exists('country', $this->responseConfig))
      {
        $country = $this->responseConfig['country'];
      }
    }

    $possibleSearchIndexes = array(
      'All' =>                 $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR | $LOC_CA | $LOC_IT | $LOC_CN | $LOC_ES,
      'Apparel' =>             $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR |                     $LOC_CN          ,
      'Appliances' =>          $LOC_US |                     $LOC_JP |                               $LOC_CN          ,
      'ArtsAndCrafts' =>       $LOC_US                                                                                ,
      'Automotive' =>          $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP |                               $LOC_CN          ,
      'Baby' =>                $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR |                     $LOC_CN          ,
      'Beauty' =>              $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR |                     $LOC_CN          ,
      'Blended' =>             $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR | $LOC_CA                              ,
      'Books' =>               $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR | $LOC_CA | $LOC_IT | $LOC_CN | $LOC_ES,
      'Classical' =>           $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR | $LOC_CA                              ,
      'DigitalMusic' =>        $LOC_US                                                                                ,
      'DVD' =>                 $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR | $LOC_CA | $LOC_IT |           $LOC_ES,
      'Electronics' =>         $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR | $LOC_CA | $LOC_IT | $LOC_CN | $LOC_ES,
      'ForeignBooks' =>                            $LOC_DE | $LOC_JP | $LOC_FR | $LOC_CA | $LOC_IT |           $LOC_ES,
      'Garden' =>                                                                          $LOC_IT                    ,
      'GourmetFood' =>         $LOC_US                                                                                ,
      'Grocery' =>             $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP |                               $LOC_CN          ,
      'HealthPersonalCare' =>  $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR |                     $LOC_CN          ,
      'Hobbies' =>                                           $LOC_JP                                                  ,
      'Home' =>                                                                                      $LOC_CN          ,
      'HomeGarden' =>          $LOC_US | $LOC_UK | $LOC_DE                                                            ,
      'HomeImprovement' =>               $LOC_UK | $LOC_DE | $LOC_JP |                               $LOC_CN          ,
      'Industrial' =>          $LOC_US                                                                                ,
      'Jewelry' =>             $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR |                     $LOC_CN          ,
      'KindleStore' =>         $LOC_US | $LOC_UK                                                                      ,
      'Kitchen' =>             $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR |           $LOC_IT |           $LOC_ES,
      'LawnAndGarden' =>       $LOC_US                                                                                ,
      'Lighting' =>                      $LOC_UK | $LOC_DE |           $LOC_FR                                        ,
      'Magazines' =>           $LOC_US |           $LOC_DE                                                            ,
      'Marketplace' =>         $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP                                                  ,
      'Miscellaneous' =>       $LOC_US |                                                             $LOC_CN          ,
      'MobileApps' =>          $LOC_US                                                                                ,
      'MP3Downloads' =>        $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR                                        ,
      'Music' =>               $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR | $LOC_CA | $LOC_IT | $LOC_CN | $LOC_ES,
      'MusicalInstruments' =>  $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR                                        ,
      'MusicTracks' =>         $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR                                        ,
      'OfficeProducts' =>      $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR |                     $LOC_CN          ,
      'OutdoorLiving' =>       $LOC_US | $LOC_UK | $LOC_DE                                                            ,
      'Outlet' =>                        $LOC_UK | $LOC_DE                                                            ,
      'PCHardware' =>          $LOC_US | $LOC_UK | $LOC_DE |           $LOC_FR                                        ,
      'PetSupplies' =>         $LOC_US                                                                                ,
      'Photo' =>               $LOC_US |           $LOC_DE |                                         $LOC_CN          ,
      'Shoes' =>               $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR |           $LOC_IT | $LOC_CN          ,
      'Software' =>            $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR | $LOC_CA | $LOC_IT | $LOC_CN | $LOC_ES,
      'SoftwareVideoGames' =>            $LOC_UK | $LOC_DE |           $LOC_FR | $LOC_CA                              ,
      'SportingGoods' =>       $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR |                     $LOC_CN          ,
      'Tools' =>               $LOC_US | $LOC_UK | $LOC_DE                                                            ,
      'Toys' =>                $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR |           $LOC_IT | $LOC_CN | $LOC_ES,
      'UnboxVideo' =>          $LOC_US                                                                                ,
      'VHS' =>                 $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR | $LOC_CA                              ,
      'Video' =>               $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR | $LOC_CA |           $LOC_CN          ,
      'VideoGames' =>          $LOC_US | $LOC_UK | $LOC_DE | $LOC_JP | $LOC_FR | $LOC_CA | $LOC_IT | $LOC_CN | $LOC_ES,
      'Watches' =>             $LOC_US | $LOC_UK | $LOC_DE |           $LOC_FR |           $LOC_IT | $LOC_CN | $LOC_ES,
      'Wireless' =>            $LOC_US                                                                                ,
      'WirelessAccessories' => $LOC_US                                                                                ,
    );

    if(null === $country)
    {
      //-- if it is still null, return all of the keys
      return array_keys($possibleSearchIndexes);
    }

    $this->validateCountry($country);

    $country_mask = $this->possibleLocations[$country];

    return array_keys(array_filter($possibleSearchIndexes, function($var) use ($country_mask) {
      return ($country_mask === ($var & $country_mask));
    }));
  }

  /**
   * execute ItemLookup request
   *
   * @param string $asin
   *
   * @return array|object return type depends on setting
   *
   * @see returnType()
   */
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
   * Implementation of BrowseNodeLookup
   * This allows to fetch information about nodes (children anchestors, etc.)
   *
   * @param integer $nodeId
   */
  public function browseNodeLookup($nodeId)
  {
    $this->validateNodeId($nodeId);

    $params = $this->buildRequestParams('BrowseNodeLookup', array(
      'BrowseNodeId' => $nodeId
    ));

    return $this->returnData(
      $this->performSoapRequest("BrowseNodeLookup", $params)
    );
  }

  /**
   * Implementation of SimilarityLookup
   * This allows to fetch information about product related to the parameter product
   *
   * @param string $asin
   */
  public function similarityLookup($asin)
  {
    $params = $this->buildRequestParams('SimilarityLookup', array(
      'ItemId' => $asin
    ));

    return $this->returnData(
      $this->performSoapRequest("SimilarityLookup", $params)
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
      $this->webserviceWsdl,
      array('exceptions' => 1)
    );

    $soapClient->__setLocation(str_replace(
      '%%COUNTRY%%',
      $this->responseConfig['country'],
      $this->webserviceEndpoint
    ));

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
   * Basic validation of the nodeId
   *
   * @param integer $nodeId
   *
   * @return boolean
   */
  final protected function validateNodeId($nodeId)
  {
    if (false === is_numeric($nodeId) || $nodeId <= 0)
    {
      throw new InvalidArgumentException(sprintf('Node has to be a positive Integer.'));
    }

    return true;
  }

  /**
   * Returns the response either as Array or Array/Object
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

    if (false === is_array($params))
    {
      throw new InvalidArgumentException(sprintf(
        "%s is no valid parameter: Use an array with Key => Value Pairs", $params
      ));
    }

    $this->responseConfig['optionalParameters'] = $params;

    return $this;
  }

  /**
   * Set or get the country
   *
   * if the country argument is null it will return the current
   * country, otherwise it will set the country and return itself.
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

    $this->validateCountry($country);

    $this->responseConfig['country'] = strtolower($country);

    return $this;
  }

  /**
   * Verify that the given $country is a valid value
   *
   * @param string $country
   *
   * @thorws InvalidArgumentException
   */
  protected function validateCountry($country)
  {
    if (false === array_key_exists(strtolower($country), $this->possibleLocations))
    {
      throw new InvalidArgumentException(sprintf(
        "Invalid Country-Code: %s! Possible Country-Codes: %s",
        $country,
        implode(', ', array_keys($this->possibleLocations))
      ));
    }
  }

  /**
   * Setting/Getting the amazon category
   *
   * @param string $category
   *
   * @return string|AmazonECS depends on category argument
   */
  public function category($category = null)
  {
    if (null === $category)
    {
      return isset($this->requestConfig['category']) ? $this->requestConfig['category'] : null;
    }

    $this->requestConfig['category'] = $category;

    return $this;
  }

  /**
   * Setting/Getting the responsegroup
   *
   * @param string $responseGroup Comma separated groups
   *
   * @return string|AmazonECS depends on responseGroup argument
   */
  public function responseGroup($responseGroup = null)
  {
    if (null === $responseGroup)
    {
      return $this->responseConfig['responseGroup'];
    }

    $this->responseConfig['responseGroup'] = $responseGroup;

    return $this;
  }

  /**
   * Setting/Getting the returntype
   * It can be an object or an array
   *
   * @param integer $type Use the constants RETURN_TYPE_ARRAY or RETURN_TYPE_OBJECT
   *
   * @return integer|AmazonECS depends on type argument
   */
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
   * @deprecated use returnType() instead
   */
  public function setReturnType($type)
  {
    return $this->returnType($type);
  }

  /**
   * Setting the resultpage to a specified value.
   * Allows to browse resultsets which have more than one page.
   *
   * @param integer $page
   *
   * @return AmazonECS
   */
  public function page($page)
  {
    if (false === is_numeric($page) || $page <= 0)
    {
      throw new InvalidArgumentException(sprintf(
        '%s is an invalid page value. It has to be numeric and positive',
        $page
      ));
    }

    $this->responseConfig['optionalParameters'] = array_merge(
      $this->responseConfig['optionalParameters'],
      array("ItemPage" => $page)
    );

    return $this;
  }
}
