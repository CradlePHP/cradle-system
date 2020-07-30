<?php //-->

namespace Cradle\Package\System\Fieldset\Field\Pack;

use Cradle\Handlebars\HandlebarsHandler;

use Cradle\Package\System\Fieldset\Field\FieldTypes;
use Cradle\Package\System\Fieldset\Field\AbstractField;
use Cradle\Package\System\Fieldset\Field\FieldInterface;

use Cradle\Package\System\Fieldset\Format\FormatTypes;

class Active extends AbstractField implements FieldInterface
{
  /**
   * @const bool FORCE_FILTERABLE Whether or not to force the field to filterable
   */
  const FORCE_FILTERABLE = true;

  /**
   * @const bool FORCE_SORTABLE Whether or not to force the field to sortable
   */
  const FORCE_SORTABLE = true;

  /**
   * @const bool HAS_ATTRIBUTES Whether or not to show attribute fieldset
   * on the schema form if the field was chosen
   */
  const HAS_ATTRIBUTES = false;

  /**
   * @const bool HAS_OPTIONS Whether or not to show options fieldset
   * on the schema form if the field was chosen
   */
  const HAS_OPTIONS = false;

  /**
   * @const bool IS_FILTERABLE Whether or not to enable the filterable checkbox
   * on the schema form if the field was chosen
   */
  const IS_FILTERABLE = true;

  /**
   * @const bool IS_SEARCHABLE Whether or not to enable the searchable checkbox
   * on the schema form if the field was chosen
   */
  const IS_SEARCHABLE = false;

  /**
   * @const bool IS_SORTABLE Whether or not to enable the sortable checkbox
   * on the schema form if the field was chosen
   */
  const IS_SORTABLE = true;

  /**
   * @const string NAME Config name
   */
  const NAME = 'active';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Active';

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_NUMBER,
    FieldTypes::TYPE_INT,
    FieldTypes::TYPE_CUSTOM
  ];

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [
    FormatTypes::TYPE_NUMBER
  ];

  /**
   * Prepares the value for some sort of insertion
   *
   * @param *mixed $value
   *
   * @return ?scalar
   */
  public function prepare($value)
  {
    if (is_null($value)) {
      return $value;
    }

    return (int) !!$value;
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
