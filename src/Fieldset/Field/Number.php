<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Fieldset\Field;

use Cradle\Package\System\Fieldset\Format\FormatTypes;

class Number extends Input
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'number';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Number Field';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FieldTypes::TYPE_NUMBER;

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_NUMBER
  ];

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [
    FormatTypes::TYPE_NUMBER
  ];

  /**
   * @var array $attributes Hash of attributes to consider when rendering
   */
  protected $attributes = ['type' => 'number'];

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
    $attributes['type'] = 'number';
    return parent::setAttributes($attributes);
  }

  /**
   * Validation check
   *
   * @param *mixed $value
   *
   * @return bool
   */
  public function valid($value): bool
  {
    return is_numeric($value)
      && (
        !isset($this->attributes['min'])
        || !is_numeric($this->attributes['min'])
        || $this->attributes['min'] <= $value
      )
      && (
        !isset($this->attributes['max'])
        || !is_numeric($this->attributes['max'])
        || $this->attributes['max'] >= $value
      );
  }
}
