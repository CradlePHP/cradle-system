<?php //-->

namespace Cradle\Package\System\Fieldset\Field\Pack;

use Cradle\Handlebars\HandlebarsHandler;

use Cradle\Package\System\Fieldset\Field\FieldTypes;
use Cradle\Package\System\Fieldset\Field\AbstractField;
use Cradle\Package\System\Fieldset\Field\FieldInterface;

use Cradle\Package\System\Fieldset\Format\FormatTypes;

class Textarea extends AbstractField implements FieldInterface
{
  /**
   * @const bool HAS_ATTRIBUTES Whether or not to show attribute fieldset
   * on the schema form if the field was chosen
   */
  const HAS_ATTRIBUTES = true;

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
  const IS_SEARCHABLE = true;

  /**
   * @const bool IS_SORTABLE Whether or not to enable the sortable checkbox
   * on the schema form if the field was chosen
   */
  const IS_SORTABLE = true;

  /**
   * @const string NAME Config name
   */
  const NAME = 'textarea';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Textarea Field';

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_TEXT,
    FormatTypes::TYPE_HTML
  ];

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [
    FormatTypes::TYPE_STRING
  ];

  /**
   * Renders the field for model forms
   *
   * @param ?mixed $value
   *
   * @return ?string
   */
  public function render($value = null): ?string
  {
    $handlebars = HandlebarsHandler::i();
    $template = $handlebars->compile(file_get_contents(__DIR__ . '/template/textarea.html'));
    return $template([
      'name' => $this->name,
      'value' => $value,
      'attributes' => $this->attributes,
      'options' => $this->options,
      'parameters' => $this->parameters
    ]);
  }
}
