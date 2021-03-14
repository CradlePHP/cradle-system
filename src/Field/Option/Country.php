<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Option;

use Cradle\Handlebars\HandlebarsHandler;

use Cradle\Package\System\Field\FieldInterface;

/**
 * Country Drop Down Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Country extends Select
{
  /**
   * @const bool HAS_OPTIONS Whether or not to show options fieldset
   * on the schema form if the field was chosen
   */
  const HAS_OPTIONS = false;

  /**
   * @const string NAME Config name
   */
  const NAME = 'country';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Country Field';

  /**
   * @var array $attributes Hash of attributes to consider when rendering
   */
  protected $attributes = ['data-do' => 'country-dropdown'];

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
    $attributes['data-do'] = 'country-dropdown';
    return parent::setAttributes($attributes);
  }
}
