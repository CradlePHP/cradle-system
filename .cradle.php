<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
require_once __DIR__ . '/src/Schema/events.php';
//require_once __DIR__ . '/src/Schema/controller.php';
//require_once __DIR__ . '/src/Model/events.php';
//require_once __DIR__ . '/src/Model/controller.php';
//require_once __DIR__ . '/src/Fieldset/events.php';
//require_once __DIR__ . '/src/Fieldset/controller.php';
//require_once __DIR__ . '/src/Relation/events.php';
//require_once __DIR__ . '/src/Relation/controller.php';

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
  Cradle\Package\System\Fieldset\Field\Pack\Textarea::class
));

FieldHandler::register($this('resolver')->resolve(
  Cradle\Package\System\Fieldset\Field\Pack\Select::class
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
/*
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
*/

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

//bootstrap
$this->preprocess(include __DIR__ . '/src/bootstrap/paths.php');
