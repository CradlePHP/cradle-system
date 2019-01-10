# Cradle System Package

Schema, Model and Relation manager.

## Install

If you already installed Cradle, you may not need to install this because it 
should be already included.

```
composer require cradlephp/cradle-system
$ bin/cradle cradlephp/cradle-system install
```

## Schema

Schemas are similar to database tables, but with more definition. With schemas
you can define field types, validation, output formats and indexing capabilities
like searchable, filterable and sortable. Schemas are designed to be very flexible
and explicit.

### Schema Routes

The following routes are available in the admin.

 - `GET /admin/system/schema/search` - Schema search page
 - `GET /admin/system/schema/create` - Schema create form
 - `GET /admin/system/schema/update/:name` - Schema update form
 - `POST /admin/system/schema/search` - Bulk action processor
 - `POST /admin/system/schema/create` - Creates a schema
 - `POST /admin/system/schema/update/:name` - Updates a schema
 - `GET /admin/system/schema/remove/:name` - Removes a Schema
 - `GET /admin/system/schema/restore/:name` - Restores a Schema

### Schema Events

 - `system-schema-create`
 - `system-schema-detail`
 - `system-schema-remove`
 - `system-schema-restore`
 - `system-schema-update`

## Relation

A relation describes the link between 2 objects. Relations can be described by
the following. Each relation type will have different sets of UI in the admin.

 - 1:0 - one-to-one optionally
 - 1:1 - one-to-one required
 - 1:N - one-to-many
 - N:N - many-to-many

### Relation Routes

The following routes are available in the admin.

 - `GET /admin/system/object/:schema1/:id/search/:schema2` - Relational Search Page
 - `GET /admin/system/object/:schema1/:id/create/:schema2` - Relational Create Form
 - `GET /admin/system/object/:schema1/:id/link/:schema2` - Relational Link Form
 - `POST /admin/system/object/:schema1/:id/search/:schema2` - Bulk action processor
 - `POST /admin/system/object/:schema1/:id/create/:schema2` - Creates an object and links
 - `POST /admin/system/object/:schema1/:id/link/:schema2` - Links an object
 - `GET /admin/system/object/:schema1/:id1/link/:schema2/:id2` - Links an object
 - `GET /admin/system/object/:schema1/:id1/unlink/:schema2/:id2` - Unlinks an object
 - `GET /admin/system/object/:schema1/:id/export/:schema2/:type` - Exports object relations
 - `GET /admin/system/object/:schema/:id/import/:schema2` - Imports object relations

The following routes are available in the front end.

 - `GET /system/object/:schema1/:id/search/:schema2` - Relational Search Page
 - `GET /system/object/:schema1/:id/create/:schema2` - Relational Create Form
 - `GET /system/object/:schema1/:id/link/:schema2` - Relational Link Form
 - `POST /system/object/:schema1/:id/search/:schema2` - Bulk action processor
 - `POST /system/object/:schema1/:id/create/:schema2` - Creates an object and links
 - `POST /system/object/:schema1/:id/link/:schema2` - Links an object
 - `GET /system/object/:schema1/:id1/link/:schema2/:id2` - Links an object
 - `GET /system/object/:schema1/:id1/unlink/:schema2/:id2` - Unlinks an object
 - `GET /system/object/:schema1/:id/export/:schema2/:type` - Exports object relations
 - `GET /system/object/:schema/:id/import/:schema2` - Imports object relations

### Relation Events

 - `system-relation-link`
 - `system-relation-unlink`
 - `system-relation-unlinkall`

## Object

Objects are similar to database table rows but its functionality is mapped by
the schema.

### Object Routes

The following routes are available in the admin.

 - `GET /admin/system/object/:schema/search` - Object search page
 - `GET /admin/system/object/:schema/create` - Object create form
 - `GET /admin/system/object/:schema/update/:id` - Object update form
 - `POST /admin/system/object/:schema/create` - Creates an object
 - `POST /admin/system/object/:schema/update/:id` - Updates an object
 - `GET /admin/system/object/:schema/remove/:id` - Removes an object
 - `GET /admin/system/object/:schema/restore/:id` - Restores an object
 - `POST /admin/system/object/:schema/import` - Imports objects via JSON
 - `GET /admin/system/object/:schema/export/:type` - Exports object to a given file type

The following routes are available in the front end.

 - `GET /system/object/:schema/search` - Object search page
 - `GET /system/object/:schema/create` - Object create form
 - `GET /system/object/:schema/update/:id` - Object update form
 - `POST /system/object/:schema/create` - Creates an object
 - `POST /system/object/:schema/update/:id` - Updates an object
 - `GET /system/object/:schema/remove/:id` - Removes an object
 - `GET /system/object/:schema/restore/:id` - Restores an object
 - `POST /system/object/:schema/import` - Imports objects via JSON
 - `GET /system/object/:schema/export/:type` - Exports object to a given file type

### Object Events

 - `system-object-create`
 - `system-object-detail`
 - `system-object-remove`
 - `system-object-restore`
 - `system-object-update`

 ----

 <a name="contributing"></a>
 # Contributing to Cradle PHP

 Thank you for considering to contribute to Cradle PHP.

 Please DO NOT create issues in this repository. The official issue tracker is located @ https://github.com/CradlePHP/cradle/issues . Any issues created here will *most likely* be ignored.

 Please be aware that master branch contains all edge releases of the current version. Please check the version you are working with and find the corresponding branch. For example `v1.1.1` can be in the `1.1` branch.

 Bug fixes will be reviewed as soon as possible. Minor features will also be considered, but give me time to review it and get back to you. Major features will **only** be considered on the `master` branch.

 1. Fork the Repository.
 2. Fire up your local terminal and switch to the version you would like to
 contribute to.
 3. Make your changes.
 4. Always make sure to sign-off (-s) on all commits made (git commit -s -m "Commit message")

 ## Making pull requests

 1. Please ensure to run [phpunit](https://phpunit.de/) and
 [phpcs](https://github.com/squizlabs/PHP_CodeSniffer) before making a pull request.
 2. Push your code to your remote forked version.
 3. Go back to your forked version on GitHub and submit a pull request.
 4. All pull requests will be passed to [Travis CI](https://travis-ci.org/CradlePHP/cradle-system) to be tested. Also note that [Coveralls](https://coveralls.io/github/CradlePHP/cradle-system) is also used to analyze the coverage of your contribution.
