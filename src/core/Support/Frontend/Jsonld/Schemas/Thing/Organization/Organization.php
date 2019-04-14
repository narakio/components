<?php namespace Naraki\Core\Support\Frontend\Jsonld\Schemas\Thing\Organization;

use Naraki\Core\Support\Frontend\Jsonld\Schemas\Thing\Thing;

class Organization extends Thing
{
    static $organizationList = [
        'Airline' => '',
        'AnimalShelter' => 'LocalBusiness',
        'AutomotiveBusiness' => 'LocalBusiness',
        'ChildCare' => 'LocalBusiness',
        'Corporation' => '',
        'Dentist' => 'LocalBusiness',
        'DryCleaningOrLaundry' => 'LocalBusiness',
        'EducationalOrganization' => '',
        'EmergencyService' => 'LocalBusiness',
        'EmploymentAgency' => 'LocalBusiness',
        'EntertainmentBusiness' => 'LocalBusiness',
        'FinancialService' => 'LocalBusiness',
        'FoodEstablishment' => 'LocalBusiness',
        'GovernmentOffice' => 'LocalBusiness',
        'GovernmentOrganization' => '',
        'HealthAndBeautyBusiness' => 'LocalBusiness',
        'HomeAndConstructionBusiness' => 'LocalBusiness',
        'InternetCafe' => 'LocalBusiness',
        'LegalService' => 'LocalBusiness',
        'Library' => 'LocalBusiness',
        'LocalBusiness' => 'LocalBusiness',
        'LodgingBusiness' => 'LocalBusiness',
        'MedicalOrganization' => '',
        'NewsMediaOrganization' => '',
        'NGO' => '',
        'Organization' => '',
        'PerformingGroup' => '',
        'ProfessionalService' => 'LocalBusiness',
        'RadioStation' => 'LocalBusiness',
        'RealEstateAgent' => 'LocalBusiness',
        'RecyclingCenter' => 'LocalBusiness',
        'SelfStorage' => 'LocalBusiness',
        'ShoppingCenter' => 'LocalBusiness',
        'SportsActivityLocation' => 'LocalBusiness',
        'SportsOrganization' => '',
        'Store' => 'LocalBusiness',
        'TelevisionStation' => 'LocalBusiness',
        'TouristInformationCenter' => 'LocalBusiness',
        'TravelAgency' => 'LocalBusiness'
    ];
    protected $address;
    protected $logo;
    protected $contactPoint;
    protected $brand;

    /**
     * @param string $value
     * @return string
     */
    public static function getClassName($value): string
    {
        if (!isset(static::$organizationList[$value])) {
            throw new \InvalidArgumentException('Invalid organization type for structured data.');
        }
        /**
         * Organizations belong to two different namespaces:
         * the general \Organization
         * and the more particular \Organization\LocalBusiness.
         * The @see \Naraki\Core\Support\Frontend\Jsonld\Schemas\Thing\Organization\Organization::$organizationList
         * contains the name of the class and whether it's in the localBusiness namespace. We use that to build
         * the classname.
         */
        return sprintf(
            '%s\%s',
            __NAMESPACE__,
            !empty(static::$organizationList[$value]) ?
                sprintf(
                    '%s\%s',
                    static::$organizationList[$value],
                    $value) :
                $value
        );
    }
}