<?php //-->
return array (
  'singular' => 'Product',
  'plural' => 'Products',
  'name' => 'product',
  'group' => 'Website',
  'icon' => 'fas fa-pencil-alt',
  'detail' => 'Manages general products for the website',
  'fields' =>
  array (
    0 =>
    array (
      'label' => 'Banner',
      'name' => 'banner',
      'field' =>
      array (
        'type' => 'image',
      ),
      'list' =>
      array (
        'format' => 'image',
        'parameters' =>
        array (
          0 => '0',
          1 => '50',
        ),
      ),
      'detail' =>
      array (
        'format' => 'image',
        'parameters' =>
        array (
          0 => '100%',
          1 => '0',
        ),
      ),
      'default' => '',
    ),
    1 =>
    array (
      'label' => 'Title',
      'name' => 'title',
      'field' =>
      array (
        'type' => 'text',
      ),
      'validation' =>
      array (
        0 =>
        array (
          'method' => 'required',
          'message' => 'Title is required',
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
    2 =>
    array (
      'label' => 'Slug',
      'name' => 'slug',
      'field' =>
      array (
        'type' => 'slug',
        'attributes' =>
        array (
          'data-source' => 'input[name=product_title]',
        ),
      ),
      'validation' =>
      array (
        0 =>
        array (
          'method' => 'required',
          'message' => 'Slug is required',
        ),
        1 =>
        array (
          'method' => 'unique',
          'message' => 'Should be unique',
        ),
      ),
      'list' =>
      array (
        'format' => 'hide',
      ),
      'detail' =>
      array (
        'format' => 'hide',
      ),
      'default' => '',
    ),
    3 =>
    array (
      'label' => 'Summary',
      'name' => 'summary',
      'field' =>
      array (
        'type' => 'textarea',
      ),
      'validation' =>
      array (
        0 =>
        array (
          'method' => 'required',
          'message' => 'Summary is required',
        ),
        1 =>
        array (
          'method' => 'char_lte',
          'parameters' => '160',
          'message' => 'Should be less than 160 characters',
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
      'label' => 'Detail',
      'name' => 'detail',
      'field' =>
      array (
        'type' => 'wysiwyg',
        'attributes' =>
        array (
          'rows' => '15',
        ),
      ),
      'validation' =>
      array (
        0 =>
        array (
          'method' => 'required',
          'message' => 'Detail is required',
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
    5 =>
    array (
      'label' => 'Tags',
      'name' => 'tags',
      'field' =>
      array (
        'type' => 'tag',
      ),
      'list' =>
      array (
        'format' => 'tag',
      ),
      'detail' =>
      array (
        'format' => 'tag',
      ),
      'default' => '',
    ),
    6 =>
    array (
      'label' => 'Meta',
      'name' => 'meta',
      'field' =>
      array (
        'type' => 'meta',
      ),
      'list' =>
      array (
        'format' => 'hide',
      ),
      'detail' =>
      array (
        'format' => 'meta',
      ),
      'default' => '',
    ),
    7 =>
    array (
      'label' => 'Status',
      'name' => 'status',
      'field' =>
      array (
        'type' => 'select',
        'options' =>
        array (
          0 =>
          array (
            'key' => 'pending',
            'value' => 'Pending',
          ),
          1 =>
          array (
            'key' => 'approved',
            'value' => 'Approved',
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
            0 => 'pending',
            1 => 'approved',
          ),
          'message' => 'Should be one of pending, approved',
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
      'default' => 'pending',
      'filterable' => '1',
    ),
    8 =>
    array (
      'label' => 'Published',
      'name' => 'published',
      'field' =>
      array (
        'type' => 'datetime',
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
      'default' => '',
      'filterable' => '1',
      'sortable' => '1',
    ),
    9 =>
    array (
      'label' => 'Public',
      'name' => 'public',
      'field' =>
      array (
        'type' => 'switch',
      ),
      'validation' =>
      array (
        0 =>
        array (
          'method' => 'one',
          'parameters' =>
          array (
            0 => '0',
            1 => '1',
          ),
          'message' => 'Should be either 0 or 1',
        ),
      ),
      'list' =>
      array (
        'format' => 'yes',
      ),
      'detail' =>
      array (
        'format' => 'yes',
      ),
      'default' => '1',
      'filterable' => '1',
      'sortable' => '1',
    ),
    10 =>
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
    11 =>
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
    12 =>
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
  'relations' =>
  array (
    0 =>
    array (
      'many' => '1',
      'name' => 'profile',
    ),
  ),
  'suggestion' => '{{product_title}}',
);
