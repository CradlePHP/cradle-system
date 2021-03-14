<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Number;

use Cradle\Package\System\Field\FieldInterface;

/**
 * Price Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Price extends Floating
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'price';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Price Field';

  /**
   * @var array $attributes Hash of attributes to consider when rendering
   */
  protected $attributes = [ 'step' => 0.01 ];

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
    $attributes['step'] = 0.01;
    return parent::setAttributes($attributes);
  }
}
