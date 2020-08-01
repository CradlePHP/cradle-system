<?php //-->
return array (
  'singular' => 'Address',
  'plural' => 'Addresses',
  'name' => 'address',
  'group' => 'Users',
  'icon' => 'fas fa-map-marker-alt',
  'detail' => 'Manages Addresses',
  'fields' => 
  array (
    0 => 
    array (
      'label' => 'Label',
      'name' => 'label',
      'field' => 
      array (
        'type' => 'text',
        'attributes' => 
        array (
          'placeholder' => 'eg. My Home',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'Label is required',
        ),
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '',
      'searchable' => '1',
    ),
    1 => 
    array (
      'label' => 'Street 1',
      'name' => 'street_1',
      'field' => 
      array (
        'type' => 'text',
        'attributes' => 
        array (
          'placeholder' => '123 Sesame Street',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'Street 1 is required',
        ),
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '',
    ),
    2 => 
    array (
      'label' => 'Street 2',
      'name' => 'street_2',
      'field' => 
      array (
        'type' => 'text',
        'attributes' => 
        array (
          'placeholder' => 'eg. Unit 100, Building B',
        ),
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '',
    ),
    3 => 
    array (
      'label' => 'Neighborhood',
      'name' => 'neighborhood',
      'field' => 
      array (
        'type' => 'text',
        'attributes' => 
        array (
          'placeholder' => 'eg. Skyler Plains',
        ),
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '',
    ),
    4 => 
    array (
      'label' => 'City',
      'name' => 'city',
      'field' => 
      array (
        'type' => 'text',
        'attributes' => 
        array (
          'placeholder' => 'eg. White Plains',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'City is required',
        ),
      ),
      'list' => 
      array (
        'format' => 'capital',
      ),
      'detail' => 
      array (
        'format' => 'capital',
      ),
      'default' => '',
      'filterable' => '1',
    ),
    5 => 
    array (
      'label' => 'State',
      'name' => 'state',
      'field' => 
      array (
        'type' => 'text',
        'attributes' => 
        array (
          'placeholder' => 'eg. New York',
        ),
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '',
      'filterable' => '1',
    ),
    6 => 
    array (
      'label' => 'Region',
      'name' => 'region',
      'field' => 
      array (
        'type' => 'text',
        'attributes' => 
        array (
          'placeholder' => 'eg. North East',
        ),
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '',
      'filterable' => '1',
    ),
    7 => 
    array (
      'label' => 'Country',
      'name' => 'country',
      'field' => 
      array (
        'type' => 'select',
        'attributes' => 
        array (
          'data-do' => 'country-dropdown',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'Country is required',
        ),
        1 => 
        array (
          'method' => 'regexp',
          'parameters' => '#^[A-Z]{2}$#',
          'message' => 'Should be a valid country code format',
        ),
      ),
      'list' => 
      array (
        'format' => 'upper',
      ),
      'detail' => 
      array (
        'format' => 'upper',
      ),
      'default' => '',
      'filterable' => '1',
    ),
    8 => 
    array (
      'label' => 'Postal Code',
      'name' => 'postal_code',
      'field' => 
      array (
        'type' => 'text',
        'attributes' => 
        array (
          'placeholder' => 'eg. 12345',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'Postal code is required',
        ),
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '',
      'filterable' => '1',
    ),
    9 => 
    array (
      'label' => 'Landmarks',
      'name' => 'landmarks',
      'field' => 
      array (
        'type' => 'text',
        'attributes' => 
        array (
          'placeholder' => 'eg. Near McDonalds',
        ),
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '',
    ),
    10 => 
    array (
      'label' => 'Contact Name',
      'name' => 'contact_name',
      'field' => 
      array (
        'type' => 'text',
        'attributes' => 
        array (
          'placeholder' => 'eg. John Doe',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'Contact name is required',
        ),
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '',
      'searchable' => '1',
    ),
    11 => 
    array (
      'label' => 'Contact Email',
      'name' => 'contact_email',
      'field' => 
      array (
        'type' => 'email',
        'attributes' => 
        array (
          'placeholder' => 'eg. John Doe',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'email',
          'message' => 'Should be a valid email format',
        ),
      ),
      'list' => 
      array (
        'format' => 'email',
        'parameters' => '{{address_contact_email}}',
      ),
      'detail' => 
      array (
        'format' => 'email',
        'parameters' => '{{address_contact_email}}',
      ),
      'default' => '',
    ),
    12 => 
    array (
      'label' => 'Contact Phone',
      'name' => 'contact_phone',
      'field' => 
      array (
        'type' => 'text',
        'attributes' => 
        array (
          'placeholder' => 'eg. 555-2424',
        ),
      ),
      'list' => 
      array (
        'format' => 'phone',
        'parameters' => '{{address_contact_phone}}',
      ),
      'detail' => 
      array (
        'format' => 'phone',
        'parameters' => '{{address_contact_phone}}',
      ),
      'default' => '',
    ),
    13 => 
    array (
      'label' => 'Latitude',
      'name' => 'latitude',
      'field' => 
      array (
        'type' => 'float',
        'attributes' => 
        array (
          'min' => '-90',
          'max' => '90',
          'step' => '0.00000001',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'number',
          'message' => 'Should be a valid number',
        ),
        1 => 
        array (
          'method' => 'lte',
          'parameters' => '90',
          'message' => 'Should be less than 90',
        ),
        2 => 
        array (
          'method' => 'gte',
          'parameters' => '-90',
          'message' => 'Should be greater than -90',
        ),
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '0.00000000',
    ),
    14 => 
    array (
      'label' => 'Longitude',
      'name' => 'longitude',
      'field' => 
      array (
        'type' => 'float',
        'attributes' => 
        array (
          'min' => '-180',
          'max' => '180',
          'step' => '0.00000001',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'number',
          'message' => 'Should be a valid number',
        ),
        1 => 
        array (
          'method' => 'lte',
          'parameters' => '180',
          'message' => 'Should be less than 180',
        ),
        2 => 
        array (
          'method' => 'gte',
          'parameters' => '-180',
          'message' => 'Should be greater than -180',
        ),
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '0.00000000',
    ),
    15 => 
    array (
      'label' => 'Active',
      'name' => 'active',
      'field' => 
      array (
        'type' => 'active',
      ),
      'list' => 
      array (
        'format' => 'hide',
      ),
      'detail' => 
      array (
        'format' => 'hide',
      ),
      'default' => '1',
      'sortable' => '1',
    ),
    16 => 
    array (
      'label' => 'Created',
      'name' => 'created',
      'field' => 
      array (
        'type' => 'created',
      ),
      'list' => 
      array (
        'format' => 'date',
        'parameters' => 'F d, Y g:iA',
      ),
      'detail' => 
      array (
        'format' => 'date',
        'parameters' => 'F d, Y g:iA',
      ),
      'default' => 'NOW()',
      'sortable' => '1',
    ),
    17 => 
    array (
      'label' => 'Updated',
      'name' => 'updated',
      'field' => 
      array (
        'type' => 'updated',
      ),
      'list' => 
      array (
        'format' => 'date',
        'parameters' => 'F d, Y g:iA',
      ),
      'detail' => 
      array (
        'format' => 'date',
        'parameters' => 'F d, Y g:iA',
      ),
      'default' => 'NOW()',
      'sortable' => '1',
    ),
  ),
  'suggestion' => '{{address_contact_name}} - {{address_name}}, {{address_city}}',
);