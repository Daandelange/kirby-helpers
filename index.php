<?php

@include_once __DIR__ . '/vendor/autoload.php';
@require_once(__DIR__ . '/src/BlueprintHelper.php');
@require_once(__DIR__ . '/src/FieldHelper.php');

use \Daandelange\Helpers\BlueprintHelper;
use \Daandelange\Helpers\FieldHelper;
use \Kirby\Content\Field as ContentField;
use \Kirby\Cms\App;

App::plugin('daandelange/helpers', [
    'fieldmethods' => [
        // Returns the blueprint array from a field.
        'getFieldBlueprint' => function(ContentField $field, bool $returnParsed = false) : array {
            return BlueprintHelper::getFieldBlueprint($field, $returnParsed);
        },
        // Experimental : to turn a Cms\Field into a Form\Field and be able to call $field->formFieldMethod()
        'toFormField' => function(ContentField $field) : ?\Kirby\Form\Field {
            return FieldHelper::getFormFieldFromCmsField($field);
        },
    ],
    
]);

?>