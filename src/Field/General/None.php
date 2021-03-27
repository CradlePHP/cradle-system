<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\General;

use Cradle\Package\System\Field\AbstractField;
use Cradle\Package\System\Field\FieldInterface;
use Cradle\Package\System\Field\FieldTypes;
use Cradle\Package\System\Format\FormatTypes;

/**
 * No Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class None extends AbstractField implements FieldInterface
{
  /**
   * @const int FORCE_FILTERABLE Whether or not to force the field to filterable
   *                             1 means force check,
   *                             0 means force uncheck,
   *                             -1 means do not force
   */
  const FORCE_FILTERABLE = 0;

  /**
   * @const int FORCE_SEARCHABLE Whether or not to force the field to searchable
   *                             1 means force check,
   *                             0 means force uncheck,
   *                             -1 means do not force
   */
  const FORCE_SEARCHABLE = 0;

  /**
   * @const int FORCE_SORTABLE Whether or not to force the field to sortable
   *                             1 means force check,
   *                             0 means force uncheck,
   *                             -1 means do not force
   */
  const FORCE_SORTABLE = 0;

  /**
   * @const string NAME Config name
   */
  const NAME = 'none';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'No Field';

  /**
   * @const bool NO_VALIDATION Whether or not to show the validation fieldset
   * on the schema form if the field was chosen
   */
  const NO_VALIDATION = true;

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FieldTypes::TYPE_GENERAL;

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [
    FormatTypes::TYPE_GENERAL,
    FormatTypes::TYPE_STRING,
    FormatTypes::TYPE_NUMBER,
    FormatTypes::TYPE_DATE,
    FormatTypes::TYPE_HTML,
    FormatTypes::TYPE_JSON,
    FormatTypes::TYPE_CUSTOM
  ];

  /**
   * Renders the field for model forms
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the column in the row
   * @param ?array  $row   the row submitted with the value
   *
   * @return ?string
   */
  public function render(
    $value = null,
    string $name = null,
    array $row = []
  ): ?string
  {
    return null;
  }
}
