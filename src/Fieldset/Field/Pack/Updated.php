<?php //-->

namespace Cradle\Package\System\Fieldset\Field\Pack;

use Cradle\Handlebars\HandlebarsHandler;

use Cradle\Package\System\Fieldset\Field\FieldTypes;
use Cradle\Package\System\Fieldset\Field\AbstractField;
use Cradle\Package\System\Fieldset\Field\FieldInterface;

use Cradle\Package\System\Fieldset\Format\FormatTypes;

class Updated extends Created
{
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
  const IS_FILTERABLE = false;

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
  const NAME = 'updated';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Updated';

  /**
   * Prepares the value for some sort of insertion
   *
   * @param *mixed $value
   *
   * @return ?scalar
   */
  public function prepare($value)
  {
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
