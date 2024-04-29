# Islandora Citations

Display citations for Islandora Objects.

## Introduction

The module enables users to add multiple citation style languages,
map csl fields to drupal fields and render citations using these.

### Citation Type Field
A new field, 'field_csl_type' is added which references CSL type taxonomy.
This is a required field for rendering citations
and derives the citation display.

### Citation style languages
An interface is provided to add citation style languages either by pasting
the csl in the provided text-area or by uploading a csl file.

Three default citation style languages are provided with the module -
APA
MLA
Chicago Manual of Style

### Mapping CSL fields
Any drupal field can be mapped to a csl field with third party settings.
Edit any field to update the citations mapping section.

#### Entity Reference and Entity Reference Revision fields
- Users are able to map csl fields from the referenced entity
by selecting the map from entity option.

- If a direct mapping is selected for the entity reference field,
the title of the referenced entity will be mapped to the selected csl value.
This option is not available for paragraphs.

### Typed Relation fields
Typed relations fields are mapped directly from their relations.
For example, author relation would get mapped to author csl field.

### Multi Value fields
Multi value fields are displayed as comma separated strings
For multi-value date fields, only the first value is considered
and the rest are ignored.

### Citations display block
A block is provided which allows users to select a
default CSL to render the citations in.

## Installation

Install as
[usual](https://www.drupal.org/docs/extending-drupal/installing-modules).

## Configuration

Add a new citations style language
/admin/structure/islandora-citations

Configure the citations block and set a default CSL
admin/structure/block/manage/displaycitations

Map csl fields to entity fields
admin/structure/types/manage/<content_type>/fields/<field_id>
For example: /admin/structure/types/manage/islandora_object/fields/node.islandora_object.field_member_of

## Usage

Run the migration for csl type taxonomy - `drush migrate:import csl_type `
Place the citations block from the block layout section.
Map the relevant drupal fields to csl fields.

## Known Issues

Module is under active development and there are some known issues:
 - Normalisation of date range fields
   - Latest under DGIR-123 we handled below date foemates
     - 2023-12-24
     - 2023-12
     - 2023
     - 2023-10/2023-12
     - 2023-12-01/2023-12-24
     - 2005-05-05/.. OR ../2025 [ Will not map as format is not supported ]
     - /2023-10 OR 2023-10/ [ Will not map as format is not supported ]

## Sponsors
* State of Florida

## Maintainers
Current maintainers:

* [discoverygarden](http://www.discoverygarden.ca)

## License
[GPLv3](http://www.gnu.org/licenses/gpl-3.0.txt)
