# Cradle System Package

Schema, Object and Relation manager.

## Install

```
composer install cradlephp/cradle-system
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
