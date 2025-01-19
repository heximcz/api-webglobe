<?php

namespace Webglobe\Payloads;

use Webglobe\Exceptions\ApiWebglobeException;

/**
 * Payload for order
 * https://api.webglobe.cz/#/reference/order/order/send-order
 */
class Order
{
    // Allowed order types - now only for registration
    private $allowed_types = ["registration", "transfer", "server", "extra", "renew"];

    private $payment_type = "credit";

    // Domain
    private $domain_name = "";
    private $period = 12;
    private $pack = "registration";
    private $type = "registration";

    // Registrants
    private $id_registrant = null;
    private $id_registrant_admin = null;

    // DNS data
    private $dns_type = "G"; // G = Group
    private $nsset_id = null;
    private $group_id = null;

    /**
     * Order payload
     * @param mixed $type possible values: - "registration","transfer","server","extra","renew"
     */
    public function __construct($type)
    {
        if (!in_array($type, $this->allowed_types)) {
            throw new ApiWebglobeException("Type does not match!");
        }
        $this->type = $type;
    }

    /**
     * Set payment type
     * Help:
     * "credit" pay from credit
     * "transaction" pay from bank transfer
     * @param mixed $payment_type
     * @return static
     */
    public function setPaymentType($payment_type)
    {
        $this->payment_type = $payment_type;
        return $this;
    }

    /**
     * Set full domain name
     * @param mixed $domain
     * @return static
     */
    public function setDomainName($domain)
    {
        $this->domain_name = $domain;
        return $this;
    }

    /**
     * Set pack
     * @param mixed $pack
     * @return static
     */
    public function setPack($pack)
    {
        $this->pack = $pack;
        return $this;
    }

    /**
     * Domain period in month
     * @param mixed $period
     * @return static
     */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * DNS type - G = group
     * @param mixed $type
     * @return static
     */
    public function setDnsType($type)
    {
        $this->dns_type = $type;
        return $this;
    }

    /**
     * NSSET ID - (NSSET:YOUR-ANY)
     * Only for CZ domain
     * @param mixed $nsset_id
     * @return static
     */
    public function setNssetId($nsset_id)
    {
        $this->nsset_id = $nsset_id;
        return $this;
    }

    /**
     * DNS Group ID
     * @param mixed $group_id
     * @return static
     */
    public function setGroupId($group_id)
    {
        $this->group_id = $group_id;
        return $this;
    }

    /**
     * Domain owner registrant webglobe ID
     * @param mixed $id_registrant
     * @return static
     */
    public function setIdRegistrant($id_registrant)
    {
        $this->id_registrant = $id_registrant;
        return $this;
    }

    /**
     * Domain Admin contact registrant webglobe ID
     * @param mixed $id_registrant_admin
     * @return static
     */
    public function setIdRegistrantAdmin($id_registrant_admin)
    {
        $this->id_registrant_admin = $id_registrant_admin;
        return $this;
    }

    // get

    /**
     * Get payment type
     * @return mixed
     */
    public function getPaymentType()
    {
        return $this->payment_type;
    }

    /**
     * Get domain name
     * @return mixed
     */
    public function getDomainName()
    {
        return $this->domain_name;
    }

    /**
     * Get pack
     * @return mixed
     */
    public function getPack()
    {
        return $this->pack;
    }

    /**
     * Get period
     * @return mixed
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Get DNS type - typically "G" (group)
     * @return mixed
     */
    public function getDnsType()
    {
        return $this->dns_type;
    }

    /**
     * Get NSSET ID - (NSSET:YOUR-ANY)
     * Only for CZ domain
     * @return mixed
     */
    public function getNssetId()
    {
        return $this->nsset_id;
    }

    /**
     * Get DNS Group ID
     * @return mixed
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * Get Domain owner registrant Webglobe ID
     * @return mixed
     */
    public function getIdRegistrant()
    {
        return $this->id_registrant;
    }

    /**
     * Get Domain Admin contact registrant Webglobe ID
     * @return mixed
     */
    public function getIdRegistrantAdmin()
    {
        return $this->id_registrant_admin;
    }

    /**
     * Get type
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    public function toJson()
    {
        if ($this->getType() === "registration") {
            return json_encode($this->getDataRegister(), JSON_PRETTY_PRINT);
        }
        // if ($this->type === "renew") {
        //     return json_encode($this->getDataRenew(), JSON_PRETTY_PRINT);
        // }
        return "{}";
    }

    /**
     * Return object as array
     * @return array
     */
    public function toArray()
    {
        if ($this->getType() === "registration") {
            return $this->getDataRegister();
        }
        // if ($this->type === "renew") {
        //     return $this->getDataRenew();
        // }
        return [];
    }

    /**
     * Create domain register order payload
     * @return array
     */
    private function getDataRegister()
    {
        // minimal payload data
        return [
            "currency" => "CZK",
            "order" => [
                "payment_type" => $this->getPaymentType(),
                "items" => [
                    [
                        "name" => $this->getDomainName(),
                        "pack" => $this->getPack(),
                        "period" => $this->getPeriod(),
                        "type" => $this->getType(),
                    ]
                ],
                "default_regdata" => [
                    "IDregistrant" => $this->getIdRegistrant(),
                    "type" => "REGISTRANT"
                ],
                "default_regcontacts" => [
                    [
                        "IDregistrant" => $this->getIdRegistrantAdmin(),
                        "type" => "ADMIN"
                    ]
                ],
                "default_dnsdata" => [
                    "type" => "G",
                    "nsset_id" => $this->getNssetId(),
                    "group_id" => $this->getGroupId()
                ]
            ]
        ];
    }
}
