<?php

namespace Webglobe;

use Webglobe\Exceptions\ApiWebglobeException;
use Webglobe\Exceptions\ApiWebglobeResponseException;

/**
 * RESTful API WebGlobe Wrapper for https://api.webglobe.com/
 * 
 * API Wrapper for communication with WebGlobe REST API.
 * Automatically manages JWT tokens, performs authentication, 
 * and provides methods for various API operations.
 *
 * Public methods:
 *  
 * getResponse
 * getReturnCode
 * getErrorCode
 * getBalance
 * 
 * Public API methods:
 * 
 * myAccount
 * checkDomainDAS
 * checkDomain
 * listAllTLD
 * checkAvailableNicId
 * listOfRegistrants
 * contactCreate
 * contactsList
 * contactDetailById
 * contactCreateInfo
 * domainInfoByName
 * domainContacts
 * domainRegistrationInfo
 * order
 * detailOrderById
 * dnsNssetList
 * dnsNssetShowById
 * nameserversInfo
 * nameserversGroupList
 * nameserversGroupShow
 * listAllDomains
 * sendAuthCode
 * invoiceDetailById
 * invoicePayByCredit
 * servicesList
 * serviceUpdateById
 */

class ApiWebglobe
{

    /**
     * API URL for staging and production environments
     */
    private $api_url = "";
    private $login = [];
    private $token = "";
    private $token_expire = 0;
    private $auth_data = [];

    // return html code
    private $return_code = null;

    // return error code
    private $error_code = null;

    /**
     * Response from curl
     * @var array
     */
    private $response = [];
    private $auth_in_progress = false; // Prevents infinite loop during authentication

    /**
     * Initializes authentication
     * @param string $api_url
     * @param string $login Login username
     * @param string $password Password
     */
    public function __construct($api_url, $login, $password)
    {
        $this->api_url = $api_url;
        $this->login = ["login" => $login, "password" => $password];
        $this->auth();
    }

    /**
     * Retrieves the last API response as array
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Retrieves the HTTP return code from the last request
     * @return int|null
     */
    public function getReturnCode()
    {
        return $this->return_code;
    }

    /**
     * Error code when return html code >= 400
     * @return int|null
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }

    /**
     * Retrieves the current account balance
     * @return string
     */
    public function getBalance()
    {
        return $this->auth_data["credit_account_info"]["balance_base"];
    }

    /**
     * Get account restrictions details currently logged in.
     * https://api.webglobe.com/#/reference/accounts/my-account/get-account-restrictions-details
     * @return void
     */
    public function myAccount()
    {
        $this->get("/my-account");
    }

    /**
     * Checks if the domain is available in the registry with a faster, simpler query
     * https://api.webglobe.com/#/reference/order/check-domain-name-das/check-domain-name-das
     * @param string $domain Domain name
     * @return void
     */
    public function checkDomainDAS($domain)
    {
        $this->post("/order/checkDomainDas", ["domain" => $domain]);
    }

    /**
     * Checks the domain name and its current status in the internal system.
     * This is a more detailed check compared to checkDomainDAS.
     * https://api.webglobe.com/#/reference/order/check-domain-name/check-domain-name
     * @param string $domain_name Domain name
     * @param string $tld Top-level domain (TLD)
     * @return void
     */
    public function checkDomain($domain_name, $tld, $withPrice = false)
    {
        $payload = [
            "domain_name" => $domain_name,
            "toplevel" => $tld,
            "with_price" => $withPrice,
            "currency" => "CZK"
        ];
        $this->get("/order/checkDomainName", $payload);
    }

    /**
     * Retrieves a list of all available TLDs with price information
     * https://api.webglobe.com/#/reference/order/list-all-tld/list-all-tld
     * @return void
     */
    public function listAllTLD()
    {
        $this->get("/order/listTld", ["apply_discounts" => true, "with_price" => true, "currency" => "CZK"]);
    }

    /**
     * Checks whether the specified identifier nic_id (registrant_id) for specified register tld,
     * is available to register.The sucess response contains the parameter available,
     * whether it is possible to register the contact with specified nic_id (registrant_id).
     * If the attribute nic_id (registrant_id) has invalid length or has invalid characters,
     * error response will be returned with proper code (1221,1222,1223 or 1224).
     * If the request against register failed, error response with code 1234 will be returned.
     * https://api.webglobe.com/#/reference/domains/register-contacts/check-available-nic-id
     * 
     * Error codes:
     * - 1221	Nic id for cz must have maximal 30 chars.
     * - 1222	Invalid characters used in cz nic id (regex:[a-zA-Z0-9](-?[a-zA-Z0-9])*).
     * - 1223	Nic id for sk must have minimum 3 and maximal 16 chars
     * - 1224	Invalid characters used in cz nic id (regex:[a-zA-Z0-9-_.]*).
     * - 1234	Check nic id '{$req_registrant_id}' failed.
     * Only CZ and SK TLD
     * @param string $tld
     * @param string $registrant_id
     * @return void
     */
    public function checkAvailableNicId($tld, $registrant_id)
    {
        $this->post("/reg/contacts/checkAvailableNicId", ["tld" => $tld, "nic_id" => $registrant_id]);
    }

    /**
     * Return available registrant list for logged customer and toplevel domain
     * https://api.webglobe.com/#/reference/order/check-domain-name/list-of-registrants
     * @param string $tld
     * @return void
     */
    public function listOfRegistrants($tld)
    {
        $this->get("/order/listRegistrants", ["tld" => $tld]);
    }

    /**
     * Create new registration contact.
     * You can specify for cz or sk tld registration contact ID.
     * It will be checked against register, if it is available to register.
     * https://api.webglobe.com/#/reference/domains/register-contacts/create-register-contact
     * @param array $payload 
     * @return void
     */
    public function contactCreate($payload)
    {
        $this->post("/reg/contacts#create", $payload);
    }

    /**
     * List all register contacts.
     * https://api.webglobe.com/#/reference/domains/register-contacts/list
     * @param int $page
     * @return void
     */
    public function contactsList($page = 1)
    {
        $this->get("/reg/contacts?page={$page}&from=");
    }

    /**
     * Get detail register contact.
     * https://api.webglobe.com/#/reference/domains/register-contacts/detail
     * @param int $contact_id 
     * @return void
     */
    public function contactDetailById($contact_id)
    {
        $this->get("/reg/contacts/{$contact_id}");
    }

    /**
     * Get contact create info
     * https://api.webglobe.com/#/reference/domains/register-contacts/create-info
     * @param string $tld 
     * @return void
     */
    public function contactCreateInfo($tld)
    {
        $this->get("/reg/contacts/create?{$tld}");
    }

    /**
     * Get domain information by name
     * https://api.webglobe.com/#/reference/domains/domains/get-domain-information-by-name
     * @param string $domain Domain name
     * @return void
     */
    public function domainInfoByName($domain)
    {
        $this->get("/domains/{$domain}");
    }

    /**
     * Gets contacts for the domain with or whithout info contact from registry.
     * For some tlds this request will not work. Error response with code 1261 will be returned.
     * https://api.webglobe.com/#/reference/domains/domains/list-contacts
     * @param int $domain_id
     * @return void
     */
    public function domainContacts($domain_id)
    {
        $this->get("/domain/{$domain_id}/list-contacts");
    }

    /**
     * Getting information about domain from registry directly.
     * Rate limit is 20 request per minute per IP. You will receive a 429 HTTP response if you exceed the rate limit.
     * https://api.webglobe.com/#/reference/domains/domains/registration-info
     * @param int $domain_id Domain id
     * @return void
     */
    public function domainRegistrationInfo($domain_id)
    {
        $this->get("/domain/{$domain_id}/reg-info");
    }

    /**
     * Send order
     * https://api.webglobe.com/#/reference/order/order/send-order
     * @param array $payload 
     * @return void
     */
    public function order($payload)
    {
        $this->post("/order/submit", $payload);
    }

    /**
     * Get detail order by ID
     * https://api.webglobe.com/#/reference/order/order/detail-order
     * @param int $order_id 
     * @return void
     */
    public function detailOrderById($order_id)
    {
        $this->get("/order/detailOrder/{$order_id}");
    }

    /**
     * Get list of yours dns nssets for cznic without pagination.
     * https://api.webglobe.com/#/reference/dns/ns-sety-cznic/list
     * @return void
     */
    public function dnsNssetList()
    {
        $this->get("/dns-nsset");
    }

    /**
     * Get specific nsset group by ID 
     * https://api.webglobe.com/#/reference/dns/ns-sety-cznic/show
     * @param mixed $dnsgroup_id
     * @return void
     */
    public function dnsNssetShowById($dnsgroup_id)
    {
        $this->get("/dns-nsset/{$dnsgroup_id}");
    }

    /**
     * Get domain nameservers info
     * https://api.webglobe.com/#/reference/dns/nameservers/info
     * @param mixed $domain_id
     * @return void
     */
    public function nameserversInfo($domain_id)
    {
        $this->get("/{$domain_id}/dns-set");
    }

    /**
     * Get domain nameservers groups list
     * https://api.webglobe.com/#/reference/dns/nameservers-group/list
     * @param mixed $domain_id
     * @return void
     */
    public function nameserversGroupList($domain_id)
    {
        $this->get("/{$domain_id}/dns-group");
    }

    /**
     * Get the details of a DNS group and all domains that use this group.
     * A domain does not have to use this group. It is a means to display the group.
     * https://api.webglobe.com/#/reference/dns/nameservers-group/show
     * @param mixed $domain_id
     * @param mixed $dns_group_id
     * @return void
     */
    public function nameserversGroupShow($domain_id, $dns_group_id)
    {
        $this->get("/{$domain_id}/dns-group/{$dns_group_id}");
    }

    /**
     * Get a simple list of your domains without specifying a category.
     * The response is different against response from same request with specifying category (below).
     * In this reponse the domains are in different elements:data 
     * - key:value (id:name) map of all domains,all 
     * - basic info for all domains,multihosting_domains 
     * - list of multihosting domains with their parent domain,hostplus 
     * - list of subdomains with parent domains, which have package hosting plus,last_used_domains 
     * - pinned domains, if you have access on them,byPackage 
     * - domains grouped by main service package.
     * https://api.webglobe.com/#/reference/invoice-system/services/list-of-your-domains
     * @return void
     */
    public function listAllDomains()
    {
        $this->get("/domains?full=true");
    }

    /**
     * Sents the auth code for domain transfer out. Email with auth code will be sent to the owner email.
     * https://api.webglobe.com/#/reference/domains/domains/send-auth-code
     * @param int $domain_id
     * @return void
     */
    public function sendAuthCode($domain_id)
    {
        $this->post("/{$domain_id}/auth-info");
    }

    /**
     * Get invoice detail
     * https://api.webglobe.cz/#/reference/invoices/invoices/invoice-detail
     * @param mixed $invoice_id
     * @return void
     */
    public function invoiceDetailById($invoice_id)
    {
        $this->get("/invoices/{$invoice_id}");
    }

    /**
     * Pay invoice by credit
     * https://api.webglobe.cz/#/reference/invoices/invoices/invoice-pay-by-credit
     * @param mixed $invoice_id
     * @return void
     */
    public function invoicePayByCredit($invoice_id)
    {
        $this->put("/invoices/{$invoice_id}", ["use_credit" => true]);
    }

    /**
     * List of services
     * https://api.webglobe.com/#/reference/services/services/list
     * @param mixed $domain
     * @param mixed $page default 1
     * @param mixed $per_page
     * @param mixed $package
     * @return void
     */
    public function servicesList($domain = "", $page = 1, $per_page = "", $package = "")
    {
        // if domain is set, page is always = 1
        if (!empty($domain)) {
            $page = 1;
        }
        $payload = [
            "page" => $page,
            "per_page" => $per_page,
            "domain" => $domain,
            "package" => $package
        ];
        $this->get("/services/service", array_filter($payload));
    }

    /**
     * Service update
     * https://api.webglobe.com/#/reference/services/services/update
     * Payload example: ["automated_billing" => 0]
     * @param mixed $service_id
     * @param array $payload
     * @return void
     */
    public function serviceUpdateById($service_id, $payload = [])
    {
        $this->put("/services/service/{$service_id}", $payload);
    }

    // Private methods

    /**
     * Authenticates and retrieves the JWT token
     * @return void
     */
    private function auth()
    {
        $this->auth_in_progress = true;

        $this->post("/auth/login", $this->login);

        $this->token = $this->response["data"]["token"];
        $this->auth_data = $this->response["data"];
        $this->token_expire = time() + $this->response["data"]["expires_in"];
        $this->auth_in_progress = false;
    }

    /**
     * Sends a GET request to the specified API endpoint
     * @param string $endpoint API endpoint URL
     * @param array $payload Query parameters
     * @return void
     */
    private function get($endpoint, $payload = [])
    {
        $this->checkTokenExpiration();
        $payload = $this->addFormName($payload);
        $url = "{$this->api_url}{$endpoint}" . (!empty($payload) ? '?' . http_build_query($payload) : '');
        $this->sendRequest($url, $payload, "GET");
    }

    /**
     * Sends a POST request to the specified API endpoint
     * @param string $endpoint API endpoint URL
     * @param array $payload POST payload
     * @return void
     */
    private function post($endpoint, $payload = [])
    {
        $this->checkTokenExpiration();
        $url = "{$this->api_url}{$endpoint}";
        $payload = $this->addFormName($payload);
        $this->sendRequest($url, $payload, "POST");
    }

    /**
     * Sends a PUT request to the specified API endpoint
     * @param string $endpoint API endpoint URL
     * @param array $payload PUT payload
     * @return void
     */
    private function put($endpoint, $payload = [])
    {
        $this->checkTokenExpiration();
        $url = "{$this->api_url}{$endpoint}";
        $payload = $this->addFormName($payload);
        $this->sendRequest($url, $payload, "PUT");
    }

    /**
     * Sends a DELETE request to the specified API endpoint
     * @param string $endpoint API endpoint URL
     * @param array $payload DELETE payload
     * @return void
     */
    private function delete($endpoint, $payload = [])
    {
        $this->checkTokenExpiration();
        $url = "{$this->api_url}{$endpoint}";
        $payload = $this->addFormName($payload);
        $this->sendRequest($url, $payload, "DELETE");
    }

    /**
     * Sends an HTTP request to the specified URL using cURL.
     *
     * @param string $url The full URL to which the request is sent.
     * @param array $payload The payload to send with the request. For POST requests, this is the POST body payload.
     * @param string $methode Whether the request is a POST, GET, PUT or DELETE request. Default "GET"
     *
     * @throws ApiWebglobeException If return code >=400 or Json and Curl errors
     * @return void
     */
    private function sendRequest($url, $payload, $methode = "GET")
    {
        // Clears the return code and response array before making a new request
        $this->return_code = null;
        $this->error_code = null;
        $this->response = [];

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            // Set CURL timeouts
            curl_setopt($ch, CURLOPT_TIMEOUT, 90);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);

            // Builds HTTP headers for the request, including the JWT token if available
            $headers = ["Content-Type: application/json"];
            if (preg_match("/^[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+$/", $this->token)) {
                $headers[] = "Authorization: Bearer {$this->token}";
            }

            if (in_array($methode, ["POST", "PUT", "DELETE"])) {
                if ($methode === "POST") {
                    curl_setopt($ch, CURLOPT_POST, true);
                }
                if (in_array($methode, ["PUT", "DELETE"])) {
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $methode);
                }
                $json_data = !empty($payload) ? json_encode($payload) : "{}";
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
                $headers[] = "Content-Length: " . strlen($json_data);
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);

            $this->return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($response === false) {
                $error = curl_error($ch);
                throw new ApiWebglobeException("ApiWebglobe: CURL Error: {$error}");
            }

            $this->response = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $jsonError = json_last_error_msg();
                throw new ApiWebglobeException("ApiWebglobe: JSON Decode Error: {$jsonError}. Response: {$response}");
            }

            // handle response errors
            if ($this->return_code >= 400) {
                throw new ApiWebglobeResponseException("ApiWebglobe: API Error ({$this->return_code}): {$this->getError()}");
            }
        } finally {
            curl_close($ch);
        }
    }

    /**
     * Add form_name value to query
     * @param array $payload
     * @return array
     */
    private function addFormName($payload = [])
    {
        if (array_key_exists("form_name", $this->auth_data)) {
            $payload["form_name"] = $this->auth_data["form_name"];
        }
        return $payload;
    }

    /**
     * Checks if the JWT token has expired and re-authenticates if necessary
     * @return void
     */
    private function checkTokenExpiration()
    {
        if ($this->auth_in_progress) {
            return; // Wait if authentication is already in progress
        }

        // re-auth after token expiration
        if (time() > $this->token_expire) {
            $this->auth();
            return;
        }
        // refresh token 10 min before expiration
        if (time() + 600 > $this->token_expire) {
            $this->refreshToken();
        }
    }

    /**
     * Refresh token
     * @return void
     */
    private function refreshToken()
    {
        $this->auth_in_progress = true;
        $this->get("/auth/refresh");
        $this->auth_in_progress = false;
        if ($this->return_code == 200) {
            $this->token = $this->response["token"];
            $this->token_expire = time() + $this->response["expires_in"];
        }
    }

    /**
     * Safely retrieves a value from a multi-dimensional array using a list of keys.
     *
     * @param array $array The array to search.
     * @param array $keys An array of keys specifying the path to the desired value.
     * @return mixed|null The value if found, or null otherwise.
     */
    private function getArrayValue($array, $keys)
    {
        foreach ($keys as $key) {
            if (is_array($array) && array_key_exists($key, $array)) {
                $array = $array[$key];
            } else {
                return null;
            }
        }
        return $array;
    }

    /**
     * Attempts to retrieve a value from a multi-dimensional array using multiple possible key paths.
     *
     * @param array $array The array to search.
     * @param array $keyPaths An array of key paths (each key path is an array of keys).
     * @return mixed|null The first found value, or null if none are found.
     */
    private function getFirstAvailableValue($array, $keyPaths)
    {
        foreach ($keyPaths as $keys) {
            $value = $this->getArrayValue($array, $keys);
            if ($value !== null) {
                return $value;
            }
        }
        return null;
    }

    /**
     * Find and return a error from response
     * @return mixed|string
     */
    private function getError()
    {
        // different errors in API response
        $keyPaths = [
            ['data', 'error'],
            ['error', 'message'],
            ['message'],
        ];

        $keyPathsCode = [
            ['data', 'code'],
            ['error', 'code'],
            ['code'],
        ];

        $errorMessage = $this->getFirstAvailableValue($this->response, $keyPaths);
        $this->error_code = $this->getFirstAvailableValue($this->response, $keyPathsCode);

        if ($errorMessage === null) {
            $errorMessage = 'Unknown API error';
        }
        return $errorMessage;
    }
}
