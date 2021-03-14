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

use Cradle\Package\System\Field\FieldRegistry;
use Cradle\Package\System\Validation\ValidatorRegistry;
use Cradle\Package\System\Format\FormatterRegistry;

//register fields
FieldRegistry::register(Cradle\Package\System\Field\General\None::class);

FieldRegistry::register(Cradle\Package\System\Field\Input\Input::class);

FieldRegistry::register(Cradle\Package\System\Field\Input\Text::class);

FieldRegistry::register(Cradle\Package\System\Field\Input\Color::class);

FieldRegistry::register(Cradle\Package\System\Field\Input\Email::class);

FieldRegistry::register(Cradle\Package\System\Field\Input\Url::class);

FieldRegistry::register(Cradle\Package\System\Field\Input\Slug::class);

FieldRegistry::register(Cradle\Package\System\Field\Input\Mask::class);

FieldRegistry::register(Cradle\Package\System\Field\Input\Password::class);

FieldRegistry::register(Cradle\Package\System\Field\Date\Date::class);

FieldRegistry::register(Cradle\Package\System\Field\Date\Time::class);

FieldRegistry::register(Cradle\Package\System\Field\Date\Datetime::class);

FieldRegistry::register(Cradle\Package\System\Field\Date\Week::class);

FieldRegistry::register(Cradle\Package\System\Field\Date\Month::class);

FieldRegistry::register(Cradle\Package\System\Field\Number\Number::class);

FieldRegistry::register(Cradle\Package\System\Field\Number\Floating::class);

FieldRegistry::register(Cradle\Package\System\Field\Number\Price::class);

FieldRegistry::register(Cradle\Package\System\Field\Number\Range::class);

FieldRegistry::register(Cradle\Package\System\Field\Number\Rating::class);

FieldRegistry::register(Cradle\Package\System\Field\Number\Small::class);

FieldRegistry::register(Cradle\Package\System\Field\Number\Knob::class);

FieldRegistry::register(Cradle\Package\System\Field\Textarea\Textarea::class);

FieldRegistry::register(Cradle\Package\System\Field\Textarea\Wysiwyg::class);

FieldRegistry::register(Cradle\Package\System\Field\Textarea\Markdown::class);

FieldRegistry::register(Cradle\Package\System\Field\Textarea\Code::class);

FieldRegistry::register(Cradle\Package\System\Field\Option\Select::class);

FieldRegistry::register(Cradle\Package\System\Field\Option\Radio::class);

FieldRegistry::register(Cradle\Package\System\Field\Option\Country::class);

FieldRegistry::register(Cradle\Package\System\Field\Option\Currency::class);

FieldRegistry::register(Cradle\Package\System\Field\Option\Checkbox::class);

FieldRegistry::register(Cradle\Package\System\Field\Option\SwitchField::class);

FieldRegistry::register(Cradle\Package\System\Field\Option\CheckList::class);

FieldRegistry::register(Cradle\Package\System\Field\Option\Multiselect::class);

FieldRegistry::register(Cradle\Package\System\Field\File\File::class);

FieldRegistry::register(Cradle\Package\System\Field\File\Image::class);

FieldRegistry::register(Cradle\Package\System\Field\File\FileList::class);

FieldRegistry::register(Cradle\Package\System\Field\File\ImageList::class);

FieldRegistry::register(Cradle\Package\System\Field\Json\TextList::class);

FieldRegistry::register(Cradle\Package\System\Field\Json\Meta::class);

FieldRegistry::register(Cradle\Package\System\Field\Custom\Active::class);

FieldRegistry::register(Cradle\Package\System\Field\Custom\Created::class);

FieldRegistry::register(Cradle\Package\System\Field\Custom\Updated::class);

//register validators
ValidatorRegistry::register(Cradle\Package\System\Validation\General\Required::class);

ValidatorRegistry::register(Cradle\Package\System\Validation\General\NotEmpty::class);

ValidatorRegistry::register(Cradle\Package\System\Validation\General\ValidOption::class);

ValidatorRegistry::register(Cradle\Package\System\Validation\Number\NotEqual::class);

ValidatorRegistry::register(Cradle\Package\System\Validation\Number\LessThan::class);

ValidatorRegistry::register(Cradle\Package\System\Validation\Number\LessThanEqual::class);

ValidatorRegistry::register(Cradle\Package\System\Validation\Number\GreaterThan::class);

ValidatorRegistry::register(Cradle\Package\System\Validation\Number\GreaterThanEqual::class);

ValidatorRegistry::register(Cradle\Package\System\Validation\Date\ValidPastDate::class);

ValidatorRegistry::register(Cradle\Package\System\Validation\Date\ValidDate::class);

ValidatorRegistry::register(Cradle\Package\System\Validation\Date\ValidFutureDate::class);

ValidatorRegistry::register(Cradle\Package\System\Validation\Custom\ValidExpression::class);

//register formats
FormatterRegistry::register(Cradle\Package\System\Format\General\None::class);

FormatterRegistry::register(Cradle\Package\System\Format\String\Lowercase::class);

FormatterRegistry::register(Cradle\Package\System\Format\Custom\Hide::class);

FormatterRegistry::register(Cradle\Package\System\Format\String\Uppercase::class);

FormatterRegistry::register(Cradle\Package\System\Format\String\Capitalize::class);

FormatterRegistry::register(Cradle\Package\System\Format\Number\Number::class);

FormatterRegistry::register(Cradle\Package\System\Format\Number\YesNo::class);

FormatterRegistry::register(Cradle\Package\System\Format\Date\Date::class);
