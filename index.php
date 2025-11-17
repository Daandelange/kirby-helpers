<?php

@include_once __DIR__ . '/vendor/autoload.php';
@require_once(__DIR__ . '/src/BlueprintHelper.php');

use \Daandelange\Helpers\BlueprintHelper;
use \Kirby\Content\Field;
use \Kirby\Cms\App;

App::plugin('daandelange/helpers', [
    'fieldmethods' => [
        // Returns the blueprint array from a field.
        'fieldBlueprint' => function(Field $field, bool $returnParsed = false) : array {
            return BlueprintHelper::getFieldBlueprint($field, $returnParsed);
        },
    ],
    
]);

?>