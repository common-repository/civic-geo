<?php
/**
 * Extends pods framework to store person info related to congress lookups/address
 *
 * https://www.experiencesolutionsnow.com/write-your-legislator/?civic_lookup_id=2
 */
namespace CivicLookup;

/**
 *
 */
class Person extends PodTable {
    public $podName = 'person';
    public $name = 'N/A';
    public $data;
    /**
     * The two-letter country code.
     *
     * @var string
     */
    protected $countryCode;

    /**
     * The top-level administrative subdivision of the country.
     *
     * @var string
     */
    protected $administrativeArea;

    /**
     * The locality (i.e. city).
     *
     * @var string
     */
    protected $locality;

    /**
     * The dependent locality (i.e. neighbourhood).
     *
     * @var string
     */
    protected $dependentLocality;

    /**
     * The postal code.
     *
     * @var string
     */
    protected $postalCode;

    /**
     * The sorting code.
     *
     * @var string
     */
    protected $sortingCode;

    /**
     * The first line of the address block.
     *
     * @var string
     */
    protected $addressLine1;

    /**
     * The second line of the address block.
     *
     * @var string
     */
    protected $addressLine2;

    /**
     * The organization.
     *
     * @var string
     */
    protected $organization;

    /**
     * The given name.
     *
     * @var string
     */
    protected $givenName;

    /**
     * The additional name
     *
     * @var string
     */
    protected $additionalName;

    /**
     * The family name.
     *
     * @var string
     */
    protected $familyName;

    /**
     * The locale.
     *
     * @var string
     */
    protected $locale;

    /**
     * Creates an Address instance.
     *
     * @param string $countryCode        The two-letter country code.
     * @param string $administrativeArea The administrative area.
     * @param string $locality           The locality.
     * @param string $dependentLocality  The dependent locality.
     * @param string $postalCode         The postal code.
     * @param string $sortingCode        The sorting code
     * @param string $addressLine1       The first line of the address block.
     * @param string $addressLine2       The second line of the address block.
     * @param string $organization       The organization.
     * @param string $givenName          The given name.
     * @param string $additionalName     The additional name.
     * @param string $familyName         The family name.
     * @param string $locale             The locale. Defaults to 'und'.
     */
    public function __construct($id=false)
    {
        $this->id = (int)$id;
        /*
        $this->administrativeArea = $administrativeArea;
        $this->locality = $locality;
        $this->dependentLocality = $dependentLocality;
        $this->postalCode = $postalCode;
        $this->sortingCode = $sortingCode;
        $this->addressLine1 = $addressLine1;
        $this->addressLine2 = $addressLine2;
        $this->organization = $organization;
        $this->givenName = $givenName;
        $this->additionalName = $additionalName;
        $this->familyName = $familyName;
        $this->locale = $locale;
        */
        $pods = pods($this->podName);
        if($this->id) {
            $this->data = (object)$pods->fetch((int)$this->id);
        }
    }
    public function getFullAddress() {
        $fs = [
            'address_1'
        ];
        $address = $this->data->address_1.' '.$this->data->address_2.' '.$this->data->city;
        $address .= ', '.$this->data->state.' '.$this->data->zip;
        $this->fullAddress = $address;
        return $this->fullAddress;
    }
    /**
     * {@inheritdoc}
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

    }

    /**
     * {@inheritdoc}
     */
    public function getAdministrativeArea()
    {
        return $this->administrativeArea;
    }

    /**
     * {@inheritdoc}
     */
    public function withAdministrativeArea($administrativeArea)
    {
        $new = clone $this;
        $new->administrativeArea = $administrativeArea;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocality()
    {
        return $this->locality;
    }

    /**
     * {@inheritdoc}
     */
    public function withLocality($locality)
    {
        $new = clone $this;
        $new->locality = $locality;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependentLocality()
    {
        return $this->dependentLocality;
    }

    /**
     * {@inheritdoc}
     */
    public function withDependentLocality($dependentLocality)
    {
        $new = clone $this;
        $new->dependentLocality = $dependentLocality;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * {@inheritdoc}
     */
    public function withPostalCode($postalCode)
    {
        $new = clone $this;
        $new->postalCode = $postalCode;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortingCode()
    {
        return $this->sortingCode;
    }

    /**
     * {@inheritdoc}
     */
    public function withSortingCode($sortingCode)
    {
        $new = clone $this;
        $new->sortingCode = $sortingCode;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddressLine1($addressLine1)
    {
        $new = clone $this;
        $new->addressLine1 = $addressLine1;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddressLine2($addressLine2)
    {
        $new = clone $this;
        $new->addressLine2 = $addressLine2;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * {@inheritdoc}
     */
    public function withOrganization($organization)
    {
        $new = clone $this;
        $new->organization = $organization;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getGivenName()
    {
        return $this->givenName;
    }

    /**
     * {@inheritdoc}
     */
    public function withGivenName($givenName)
    {
        $new = clone $this;
        $new->givenName = $givenName;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalName()
    {
        return $this->additionalName;
    }

    /**
     * {@inheritdoc}
     */
    public function withAdditionalName($additionalName)
    {
        $new = clone $this;
        $new->additionalName = $additionalName;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * {@inheritdoc}
     */
    public function withFamilyName($familyName)
    {
        $new = clone $this;
        $new->familyName = $familyName;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function withLocale($locale)
    {
        $new = clone $this;
        $new->locale = $locale;

        return $new;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = trim($name);
    }
}
