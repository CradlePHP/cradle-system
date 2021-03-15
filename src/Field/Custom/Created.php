<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Custom;

use Cradle\Package\System\Field\AbstractField;
use Cradle\Package\System\Field\FieldInterface;
use Cradle\Package\System\Field\FieldTypes;
use Cradle\Package\System\Format\FormatTypes;

/**
 * Created Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Created extends AbstractField implements FieldInterface
{
  /**
   * @const mixed DEFAULT_VALUE
   */
  const DEFAULT_VALUE = 'NOW()';

  /**
   * @const int FORCE_FILTERABLE Whether or not to force the field to filterable
   *                             1 means force check,
   *                             0 means force uncheck,
   *                             -1 means do not force
   */
  const FORCE_FILTERABLE = 0;

  /**
   * @const ?string FORCE_LABEL the label here will be used as
   *                            the label of the field.
   */
  const FORCE_LABEL = 'Created';

  /**
   * @const ?string FORCE_NAME the name here will be used as the name of
   *                           the field. Dont confuse this with the type name.
   */
  const FORCE_NAME = 'created';

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
  const FORCE_SORTABLE = 1;

  /**
   * @const bool IS_SORTABLE Whether or not to enable the sortable checkbox
   * on the schema form if the field was chosen
   */
  const IS_SORTABLE = true;

  /**
   * @const string NAME Config name
   */
  const NAME = 'created';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Created';

  /**
   * @const bool NO_VALIDATION Whether or not to show the validation fieldset
   * on the schema form if the field was chosen
   */
  const NO_VALIDATION = true;

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FieldTypes::TYPE_CUSTOM;

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_DATETIME,
    FieldTypes::TYPE_CUSTOM
  ];

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [
    FormatTypes::TYPE_GENERAL,
    FormatTypes::TYPE_DATE,
    FormatTypes::TYPE_CUSTOM
  ];

  /**
   * Prepares the value for some sort of insertion
   *
   * @param *mixed $value
   *
   * @return ?scalar
   */
  public function prepare($value = null)
  {
    if (is_null($value)) {
      return $value;
    }

    if (strtotime($value) !== false) {
      return date('Y-m-d H:i:s', strtotime($value));
    }

    return date('Y-m-d H:i:s');
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
    return null;
  }
}
