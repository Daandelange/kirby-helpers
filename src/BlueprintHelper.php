<?php

namespace Daandelange\Helpers;

use \Kirby\Content\Field;
use \Kirby\Toolkit\Str;
use \Kirby\Toolkit\I18n;
use \Kirby\Cms\Blueprint;
use \Kirby\Panel\Field as PanelField;
use \Kirby\Exception\InvalidArgumentException;

class BlueprintHelper {
    // Returns the field blueprint or throws with fail reason
    public static function getFieldBlueprint(Field $field, bool $returnParsed=false) : array {
        // Only tested on existing fields !
        if(!$field->exists() ){
            throw new InvalidArgumentException('fieldBlueprint() only works on existing fields !');
        }

        // We need a key !
        $key = $field->key();
        if(!$key || empty($key)){
            throw new InvalidArgumentException('Sorry, Kirby provides no FormField nor FieldBlueprint for the title !'); // Todo: provide a fallback ?
        }

        // Grab model
        $page = $field->model();

        // Should this rather return null ?
        // if($page instanceof \Kirby\Cms\Page === true || $page instanceof \Kirby\Cms\Site === true){
        if(!$page){
            throw new InvalidArgumentException('$field->fieldBlueprint() :: The provided field has no valid model !');
        }

        // Handle exceptions (reserved keys)
        // If code breaks, check : kirby/config/areas/site/dialogs.php --> 'page.changeTitle'
        if($key==='title' || $key==='slug'){
            // Grab dunamic blueprint vars
            $permissions = $page->permissions();
            $path = match ($field->kirby()->multilang()) {
                true  => Str::after($field->kirby()->site()->url(), $field->kirby()->url()) . '/',
                false => '/'
            };

            // Format the props
            $props = ($key==='title') ? PanelField::title([
                'required'  => true,
                //'preselect' => $select === 'title',
                'disabled'  => $permissions->can('changeTitle') === false
            ]) : PanelField::slug([
                'required'  => true,
                //'preselect' => $select === 'slug',
                'path'      => $path,
                'disabled'  => $permissions->can('changeSlug') === false,
                'wizard'    => [
                    'text'  => I18n::translate('page.changeSlug.fromTitle'),
                    'field' => 'title'
                ]
            ]);

            return $returnParsed ? Blueprint::fieldProps($props):$props;
        }
        
        // Get props from blueprint
        $pageBlueprint = $page->blueprint();
        $fieldBlueprint = $pageBlueprint->field($key);
        // SiteBlueprint has no title field... try calling the method directly ?
        if(!$fieldBlueprint) $fieldBlueprint = $pageBlueprint->{$key}();

        // Check
        if(!$fieldBlueprint || !is_array($fieldBlueprint) ) throw new InvalidArgumentException('Weirdly, the field "'.$key.'" doesn\'t exist in the blueprint !');
        
        // Format & return
        return $returnParsed ? Blueprint::fieldProps($fieldBlueprint??[]) : $fieldBlueprint??[];    
    }
}