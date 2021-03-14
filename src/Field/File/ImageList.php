<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\File;

use Cradle\Package\System\Field\FieldInterface;

/**
 * Image List Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class ImageList extends FileList
{
  /**
   * @const bool HAS_ATTRIBUTES Whether or not to show attribute fieldset
   * on the schema form if the field was chosen
   */
  const HAS_ATTRIBUTES = true;

  /**
   * @const string NAME Config name
   */
  const NAME = 'imagelist';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Image List Fieldset';

  /**
   * @var array $attributes Hash of attributes to consider when rendering
   */
  protected $attributes = [
    'data-accept' => 'image/png,image/jpg,image/jpeg,image/gif'
  ];

  /**
   * Sets the attributes that will be
   * considered when rendering the template
   *
   * @param *array $attributes
   *
   * @return FieldConfigInterface
   */
  public function setAttributes(array $attributes): FieldInterface
  {
    $attributes['data-accept'] = 'image/png,image/jpg,image/jpeg,image/gif';
    return parent::setAttributes($attributes);
  }
}
