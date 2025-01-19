<?php

namespace Webglobe\Payloads;

/**
 * Payload for register contact
 * https://api.webglobe.cz/#/reference/domains/register-contacts/create-register-contact
 */
class Contact
{

    public $action = "create";
    public $single_tld = "";
    public $type = "REGISTRANT";
    /**
     * "SRO" Limited Liability Company, "AS" joint stock company, "FO" nutral person
     * @var string
     */
    public $legal_form = "FO";
    public $street = "";
    public $town = "";
    public $postcode = "";
    /**
     * Country code ISO 3166-1 alpha-2
     * @var string
     */
    public $country = "";
    public $email = "";
    /**
     * Phone number in EPP style +CCC.NNNNNNNNNN (+420.193729382)
     * @var string
     */
    public $phone = "";
    public $contact_name = "";
    /**
     * ISO 639-1 language code "cs, en, hu,..."
     * @var string
     */
    public $lang = "";
    public $disclose = [];

    // Company section

    /**
     * It is required for contact except natural person (legal_form: FO). 
     * On the contrary, not to be filled in for a natural person.
     * @var string
     */
    public $company_id = "";
    /**
     * tax registration id
     * @var string
     */
    public $tax_id = "";
    /**
     * vat registration id
     * @var string
     */
    public $vat_id = "";
    /**
     * In the case of an organization, it is the person representing the company
     * @var string
     */
    public $statutory_representative = "";
    /**
     * It is required for contact except natural person (legal_form: FO)
     * @var string
     */
    public $company = "";


    /**
     * Set type: "REGISTRANT" or "ADMIN" only for EU
     * @param string $type
     * @return static
     */
    public function setType($type = "REGISTRANT") {
        $this->type = $type;
        return $this;
    }

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function setSingleTld($single_tld)
    {
        $this->single_tld = $single_tld;
        return $this;
    }

    public function setCompanyId($company_id)
    {
        $this->company_id = $company_id;
        return $this;
    }

    public function setTaxId($tax_id)
    {
        $this->tax_id = $tax_id;
        return $this;
    }

    public function setVatId($vat_id)
    {
        $this->vat_id = $vat_id;
        return $this;
    }

    public function setStatutoryRepresentative($statutory_representative)
    {
        $this->statutory_representative = $statutory_representative;
        return $this;
    }

    public function setCompany($company)
    {
        $this->company = $company;
        return $this;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }
    
    public function setLegalForm($legal_form)
    {
        $this->legal_form = $legal_form;
        return $this;
    }

    public function setStreet($street)
    {
        $this->street = $street;
        return $this;
    }

    public function setTown($town)
    {
        $this->town = $town;
        return $this;
    }

    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;
        return $this;
    }

    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function setContactName($contact_name)
    {
        $this->contact_name = $contact_name;
        return $this;
    }

    public function setLang($lang)
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * Summary of setDisclose
     * @param array $disclose
     * @return static
     */
    public function setDisclose($disclose)
    {
        $this->disclose = $disclose;
        return $this;
    }

    public function toJson()
    {
        return json_encode($this->getData(), JSON_PRETTY_PRINT);
    }

    public function toArray()
    {
        return $this->getData();
    }

    private function getData()
    {
        $vars = get_object_vars($this);

        $nonEmptyVars = array_filter($vars, function ($value) {
            return $value !== "" && $value !== null;
        });

        $data = [
            "action" => $nonEmptyVars['action'],
            "contact_data" => array_diff_key($nonEmptyVars, ["action" => true])
        ];

        return $data;
    }
}
