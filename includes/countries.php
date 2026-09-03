<?php
/**
 * Countries and their states/provinces/regions, for the application form.
 * Countries without a listed set fall back to a free-text "State / Region" input.
 * Add more countries here any time by adding a new "Country Name" => [...] entry.
 */
function countries_with_states(): array {
    return [
        'United States' => [
            'Alabama','Alaska','Arizona','Arkansas','California','Colorado','Connecticut','Delaware',
            'Florida','Georgia','Hawaii','Idaho','Illinois','Indiana','Iowa','Kansas','Kentucky',
            'Louisiana','Maine','Maryland','Massachusetts','Michigan','Minnesota','Mississippi','Missouri',
            'Montana','Nebraska','Nevada','New Hampshire','New Jersey','New Mexico','New York',
            'North Carolina','North Dakota','Ohio','Oklahoma','Oregon','Pennsylvania','Rhode Island',
            'South Carolina','South Dakota','Tennessee','Texas','Utah','Vermont','Virginia','Washington',
            'West Virginia','Wisconsin','Wyoming','District of Columbia',
        ],
        'Canada' => [
            'Alberta','British Columbia','Manitoba','New Brunswick','Newfoundland and Labrador',
            'Northwest Territories','Nova Scotia','Nunavut','Ontario','Prince Edward Island','Quebec',
            'Saskatchewan','Yukon',
        ],
        'Nigeria' => [
            'Abia','Adamawa','Akwa Ibom','Anambra','Bauchi','Bayelsa','Benue','Borno','Cross River',
            'Delta','Ebonyi','Edo','Ekiti','Enugu','Gombe','Imo','Jigawa','Kaduna','Kano','Katsina',
            'Kebbi','Kogi','Kwara','Lagos','Nasarawa','Niger','Ogun','Ondo','Osun','Oyo','Plateau',
            'Rivers','Sokoto','Taraba','Yobe','Zamfara','Federal Capital Territory',
        ],
        'United Kingdom' => [
            'England','Scotland','Wales','Northern Ireland',
        ],
        'Australia' => [
            'New South Wales','Victoria','Queensland','Western Australia','South Australia',
            'Tasmania','Australian Capital Territory','Northern Territory',
        ],
        'India' => [
            'Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat',
            'Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra',
            'Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim',
            'Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Delhi',
        ],
        'South Africa' => [
            'Eastern Cape','Free State','Gauteng','KwaZulu-Natal','Limpopo','Mpumalanga',
            'Northern Cape','North West','Western Cape',
        ],
    ];
}

/** Full alphabetical list of country names for the Country dropdown. */
function all_countries(): array {
    $withStates = array_keys(countries_with_states());
    $others = [
        'Ghana','Kenya','Egypt','Germany','France','Spain','Italy','Netherlands','Ireland','Sweden',
        'Switzerland','United Arab Emirates','Saudi Arabia','China','Japan','South Korea','Singapore',
        'Malaysia','Philippines','Indonesia','Brazil','Mexico','Argentina','New Zealand', 'Other',
    ];
    $all = array_unique(array_merge($withStates, $others));
    sort($all);
    // Keep "Other" at the very end regardless of alphabetical sort
    $all = array_values(array_diff($all, ['Other']));
    $all[] = 'Other';
    return $all;
}
