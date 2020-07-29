<?php //-->
return array (
  'singular' => 'Profile',
  'plural' => 'Profiles',
  'name' => 'profile',
  'group' => 'Users',
  'icon' => 'fas fa-user',
  'detail' => 'Generic user profiles designed to separate public data from sensitive data like passwords. Best used with auth tables.',
  'fields' => 
  array (
    0 => 
    array (
      'label' => 'Image',
      'name' => 'image',
      'field' => 
      array (
        'type' => 'image',
      ),
      'list' => 
      array (
        'format' => 'image',
        'parameters' => 
        array (
          0 => '50',
          1 => '50',
        ),
      ),
      'detail' => 
      array (
        'format' => 'image',
        'parameters' => 
        array (
          0 => '100',
          1 => '100',
        ),
      ),
      'default' => '',
    ),
    1 => 
    array (
      'label' => 'First Name',
      'name' => 'name',
      'field' => 
      array (
        'type' => 'text',
        'attributes' => 
        array (
          'placeholder' => 'eg. John',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'First Name is Required',
        ),
        1 => 
        array (
          'method' => 'empty',
          'message' => 'Cannot be empty',
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
      'filterable' => '1',
      'disable' => '1',
    ),
    2 => 
    array (
      'label' => 'Last Name',
      'name' => 'last_name',
      'field' => 
      array (
        'type' => 'text',
        'attributes' => 
        array (
          'placeholder' => 'eg. Doe',
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
      'searchable' => '1',
    ),
    3 => 
    array (
      'label' => 'Gender',
      'name' => 'gender',
      'field' => 
      array (
        'type' => 'select',
        'options' => 
        array (
          0 => 
          array (
            'key' => 'na',
            'value' => 'Choose an Option',
          ),
          1 => 
          array (
            'key' => 'male',
            'value' => 'Male',
          ),
          2 => 
          array (
            'key' => 'female',
            'value' => 'Female',
          ),
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'one',
          'parameters' => 
          array (
            0 => 'male',
            1 => 'female',
          ),
          'message' => 'Should be na, male or female',
        ),
      ),
      'list' => 
      array (
        'format' => 'lower',
      ),
      'detail' => 
      array (
        'format' => 'lower',
      ),
      'default' => 'na',
      'filterable' => '1',
    ),
    4 => 
    array (
      'label' => 'Birthday',
      'name' => 'birthday',
      'field' => 
      array (
        'type' => 'date',
      ),
      'list' => 
      array (
        'format' => 'date',
        'parameters' => 'F d, Y',
      ),
      'detail' => 
      array (
        'format' => 'date',
        'parameters' => 'F d, Y',
      ),
      'default' => '',
      'sortable' => '1',
    ),
    5 => 
    array (
      'label' => 'About Me',
      'name' => 'bio',
      'field' => 
      array (
        'type' => 'wysiwyg',
        'attributes' => 
        array (
          'rows' => '5',
        ),
      ),
      'list' => 
      array (
        'format' => 'hide',
      ),
      'detail' => 
      array (
        'format' => 'html',
      ),
      'default' => '',
    ),
    6 => 
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
      'disable' => '1',
    ),
    7 => 
    array (
      'label' => 'Created',
      'name' => 'created',
      'field' => 
      array (
        'type' => 'created',
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => 'NOW()',
      'sortable' => '1',
      'disable' => '1',
    ),
    8 => 
    array (
      'label' => 'Updated',
      'name' => 'updated',
      'field' => 
      array (
        'type' => 'updated',
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => 'NOW()',
      'sortable' => '1',
      'disable' => '1',
    ),
  ),
  'relations' => 
  array (
    0 => 
    array (
      'many' => '2',
      'name' => 'address',
    ),
    1 => 
    array (
      'many' => '2',
      'name' => 'file',
    ),
  ),
  'suggestion' => '{{profile_name}}',
  'disable' => '1',
);