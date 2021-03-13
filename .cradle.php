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

use Cradle\Package\System\Fieldset\Field\FieldHandler;
use Cradle\Package\System\Fieldset\Validation\ValidationHandler;
use Cradle\Package\System\Fieldset\Format\FormatHandler;

//register fields
FieldHandler::register(Cradle\Package\System\Fieldset\Field\None::class);

FieldHandler::register(Cradle\Package\System\Fieldset\Field\Input::class);

FieldHandler::register(Cradle\Package\System\Fieldset\Field\Datetime::class);

FieldHandler::register(Cradle\Package\System\Fieldset\Field\Number::class);

FieldHandler::register(Cradle\Package\System\Fieldset\Field\Textarea::class);

FieldHandler::register(Cradle\Package\System\Fieldset\Field\Select::class);

FieldHandler::register(Cradle\Package\System\Fieldset\Field\TextList::class);

FieldHandler::register(Cradle\Package\System\Fieldset\Field\Meta::class);

FieldHandler::register(Cradle\Package\System\Fieldset\Field\Active::class);

FieldHandler::register(Cradle\Package\System\Fieldset\Field\Created::class);

FieldHandler::register(Cradle\Package\System\Fieldset\Field\Updated::class);

//register validators
ValidationHandler::register(Cradle\Package\System\Fieldset\Validation\Required::class);

ValidationHandler::register(Cradle\Package\System\Fieldset\Validation\NotEmpty::class);

ValidationHandler::register(Cradle\Package\System\Fieldset\Validation\NotEqual::class);

ValidationHandler::register(Cradle\Package\System\Fieldset\Validation\ValidOption::class);

ValidationHandler::register(Cradle\Package\System\Fieldset\Validation\NumberLessThan::class);

ValidationHandler::register(Cradle\Package\System\Fieldset\Validation\NumberLessThanEqual::class);

ValidationHandler::register(Cradle\Package\System\Fieldset\Validation\NumberGreaterThan::class);

ValidationHandler::register(Cradle\Package\System\Fieldset\Validation\NumberGreaterThanEqual::class);

ValidationHandler::register(Cradle\Package\System\Fieldset\Validation\ValidPastDate::class);

ValidationHandler::register(Cradle\Package\System\Fieldset\Validation\ValidDate::class);

ValidationHandler::register(Cradle\Package\System\Fieldset\Validation\ValidFutureDate::class);

ValidationHandler::register(Cradle\Package\System\Fieldset\Validation\ValidExpression::class);

//register formats
FormatHandler::register(Cradle\Package\System\Fieldset\Format\None::class);

FormatHandler::register(Cradle\Package\System\Fieldset\Format\Lowercase::class);

FormatHandler::register(Cradle\Package\System\Fieldset\Format\Hide::class);

FormatHandler::register(Cradle\Package\System\Fieldset\Format\Uppercase::class);

FormatHandler::register(Cradle\Package\System\Fieldset\Format\Capitalize::class);

FormatHandler::register(Cradle\Package\System\Fieldset\Format\Number::class);

FormatHandler::register(Cradle\Package\System\Fieldset\Format\YesNo::class);

FormatHandler::register(Cradle\Package\System\Fieldset\Format\Date::class);
