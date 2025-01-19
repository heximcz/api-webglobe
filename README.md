# PHP Wrapper for <https://api.webglobe.com/>

## Examples

### GET domain info by name

```php
use Webglobe\ApiWebglobe;
use Webglobe\Exceptions\ApiWebglobeException;
use Webglobe\Exceptions\ApiWebglobeResponseException;

$production = false; 
$login = "login";
$password = "password";

try {
    // URL
    $api_url = "https://api." . ($production ? "" : "staging.") . "webglobe.com";

    $api = new ApiWebglobe($api_url, $login, $password);

    // get domain info
    $api->domainInfoByName("example.com");

    var_dump($api->getResponse());

} catch (ApiWebglobeException $e) {
    // CURL or JSON decode errors
    if (isset($api)) {
        var_dump($api->getResponse());
        var_dump($api->getReturnCode());
    }
    echo "API error occurred: " . $e->getMessage();
} catch (ApiWebglobeResponseException $e) {
    // Handle response errors (return code >= 400)
    if (isset($api)) {
        var_dump($api->getResponse());
        var_dump($api->getReturnCode());
    }
    echo "API error occurred: " . $e->getMessage();
}

```

### Register new FO contact

```php
use Webglobe\ApiWebglobe;
use Webglobe\Exceptions\ApiWebglobeException;
use Webglobe\Exceptions\ApiWebglobeResponseException;
use Webglobe\Payloads\Contact;

$production = false; 
$login = "login";
$password = "password";

try {
    // URL
    $api_url = "https://api." . ($production ? "" : "staging.") . "webglobe.com";

    $api = new ApiWebglobe($api_url, $login, $password);

    // Prepare payload for contact data
    // in this case is used 'FO'
    $contact = new Contact();
    $contact->setAction("create")
        ->setSingleTld("cz")
        ->setLegalForm("FO")
        ->setStreet("Street 1")
        ->setTown("City")
        ->setPostcode("11150")
        ->setCountry("CZ")
        ->setEmail("example@example.com")
        ->setContactName("John Doe")
        ->setLang("cs")
        ;

    // echo $contact->toJson();
    // var_dump($contact->toArray());
    // die;

    $api->contactCreate($contact->toArray());

    // show response
    var_dump($api->getResponse());

    // Get info about new crated contact
    $contact_create_response = $api->getResponse();
    if ($contact_create_response["success"]){
        $api->contactDetailById($contact_create_response["contact_id"]);

        // show response
        var_dump($api->getResponse());
    }

} catch (ApiWebglobeException $e) {
    // CURL or JSON decode errors
    if (isset($api)) {
        var_dump($api->getResponse());
        var_dump($api->getReturnCode());
    }
    echo "API error occurred: " . $e->getMessage();
} catch (ApiWebglobeResponseException $e) {
    // Handle response errors (return code >= 400)
    if (isset($api)) {
        var_dump($api->getResponse());
        var_dump($api->getReturnCode());
    }
    echo "API error occurred: " . $e->getMessage();
}
```

### Payload for order

```php
$domain = "example.com";
$tld = "com";

// only "registration" method is implemented for now
$order = new Order("registration");

$order->setPaymentType("credit")
      ->setDomainName($domain)
      ->setPeriod(12)
      ->setIdRegistrant(<int>)
      ->setIdRegistrantAdmin(<int>)
      ;

switch ($tld) {
    case "cz":
        // NSSET ID for CZ domain:
        $order->setDnsType("G")
              ->setNssetId("<YOUR:NSSET-NAME-FOR-CZ>")
              ->setGroupId(<int>);
        break;
    default:
        // Nameservers group ID for other TLDs
        $order->setDnsType("G")
              ->setGroupId(<int>);
        break;
}

// show payload in json or array
echo $order->toJson();
var_dump($order->toArray());

```

## Implemented Methods

This wrapper was primarily developed for domain registration and renewal, so it implements only the following methods from the entire API. However, it can be easily extended to include additional methods if needed.

- myAccount
- checkDomainDAS
- checkDomain
- listAllTLD
- checkAvailableNicId
- listOfRegistrants
- contactCreate
- contactsList
- contactDetailById
- contactCreateInfo
- domainInfoByName
- domainContacts
- domainRegistrationInfo
- order
- detailOrderById
- dnsNssetList
- dnsNssetShowById
- nameserversInfo
- nameserversGroupList
- nameserversGroupShow
- listAllDomains
- sendAuthCode
- invoiceDetailById
- invoicePayByCredit
- servicesList
- serviceUpdateById

## [License MIT](LICENSE.md)
