<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
require_once __DIR__ . '/src/bootstrap/methods.php';

require_once __DIR__ . '/src/events/collection.php';
require_once __DIR__ . '/src/events/fieldset.php';
require_once __DIR__ . '/src/events/model.php';
require_once __DIR__ . '/src/events/schema.php';

//require_once __DIR__ . '/src/controller/schema/search.php';
//require_once __DIR__ . '/src/controller/schema/create.php';
//require_once __DIR__ . '/src/controller/schema/remove.php';
//require_once __DIR__ . '/src/controller/schema/restore.php';
//require_once __DIR__ . '/src/controller/schema/update.php';

use Cradle\Package\System\Fieldset\Field\FieldHandler;
use Cradle\Package\System\Fieldset\Validation\ValidationHandler;
use Cradle\Package\System\Fieldset\Format\FormatHandler;

//register types
FieldHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Field\Pack\Text::class
));

FieldHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Field\Pack\Email::class
));

FieldHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Field\Pack\Password::class
));

FieldHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Field\Pack\Url::class
));

FieldHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Field\Pack\Color::class
));

FieldHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Field\Pack\Checkbox::class
));

FieldHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Field\Pack\Date::class
));

FieldHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Field\Pack\Time::class
));

FieldHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Field\Pack\Datetime::class
));

FieldHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Field\Pack\Number::class
));

FieldHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Field\Pack\Textarea::class
));

FieldHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Field\Pack\Select::class
));

FieldHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Field\Pack\Active::class
));

FieldHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Field\Pack\Created::class
));

FieldHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Field\Pack\Updated::class
));

ValidationHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Validation\Pack\Required::class
));

ValidationHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Validation\Pack\NotEmpty::class
));

ValidationHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Validation\Pack\NotEqual::class
));

ValidationHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Validation\Pack\ValidOption::class
));

ValidationHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Validation\Pack\NumberLessThan::class
));

ValidationHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Validation\Pack\NumberLessThanEqual::class
));

ValidationHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Validation\Pack\NumberGreaterThan::class
));

ValidationHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Validation\Pack\NumberGreaterThanEqual::class
));

ValidationHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Validation\Pack\ValidPastDate::class
));

ValidationHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Validation\Pack\ValidDate::class
));

ValidationHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Validation\Pack\ValidFutureDate::class
));

ValidationHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Validation\Pack\ValidExpression::class
));

FormatHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Format\Pack\None::class
));

FormatHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Format\Pack\Lowercase::class
));

FormatHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Format\Pack\Uppercase::class
));

FormatHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Format\Pack\Capitalize::class
));

FormatHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Format\Pack\NumberComma::class
));

FormatHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Format\Pack\YesNo::class
));

FormatHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Format\Pack\Date::class
));
