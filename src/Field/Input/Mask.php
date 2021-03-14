<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Input;

use Cradle\Package\System\Field\FieldInterface;

/**
 * Mask Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Mask extends Text
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'mask';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Mask Field';

  /**
   * @var array $attributes Hash of attributes to consider when rendering
   */
  protected $attributes = ['data-do' => 'mask-field'];

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
    $attributes['data-do'] = 'mask-field';
    return parent::setAttributes($attributes);
  }
}
