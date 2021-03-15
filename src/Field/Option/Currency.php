<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Option;

use Cradle\Package\System\Field\FieldInterface;
use Cradle\Package\System\Format\FormatTypes;

/**
 * Currency Drop Down Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Currency extends Select
{
  /**
   * @const bool HAS_OPTIONS Whether or not to show options fieldset
   * on the schema form if the field was chosen
   */
  const HAS_OPTIONS = false;

  /**
   * @const string NAME Config name
   */
  const NAME = 'currency';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Currency Field';

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [
    FormatTypes::TYPE_GENERAL,
    FormatTypes::TYPE_STRING,
    FormatTypes::TYPE_HTML,
    FormatTypes::TYPE_CUSTOM
  ];

  /**
   * @var array $attributes Hash of attributes to consider when rendering
   */
  protected $attributes = ['data-do' => 'currency-dropdown'];

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
    $attributes['data-do'] = 'currency-dropdown';
    return parent::setAttributes($attributes);
  }
}
