<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Json;

/**
 * Lat/Lng Fieldset
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class LatLng extends TextList
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'latlng';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Lat/Lng Fieldset';

  /**
   * Prepares the value for some sort of insertion
   *
   * @param *mixed $value
   *
   * @return ?scalar
   */
  public function prepare($value = null)
  {
    if (!is_numeric($value[0])) {
      $value[0] = 0;
    }

    if (!is_numeric($value[1])) {
      $value[1] = 0;
    }

    $value[0] = sprintf('%.8F', $value[0]);
    $value[1] = sprintf('%.8F', $value[1]);

    return json_encode($value);
  }

  /**
   * Renders the field for model forms
   *
   * @param ?mixed $value
   *
   * @return ?string
   */
  public function render($value = null): ?string
  {
    $template = cradle('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/latlng.html')
    );
    return $template([
      'name' => $this->name,
      'value' => $value,
      'attributes' => $this->attributes,
      'options' => $this->options,
      'parameters' => $this->parameters
    ]);
  }
}
