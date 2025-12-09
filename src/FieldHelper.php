<?php

namespace Daandelange\Helpers;

@require_once(__DIR__ . '/BlueprintHelper.php');

use \Kirby\Content\Field as ContentField;
use \Kirby\Form\Field as FormField;
use \Kirby\Form\FieldClass as FormFieldClass;
use \Kirby\Toolkit\Str;
use \Kirby\Toolkit\I18n;
use \Kirby\Cms\Blueprint;
use \Kirby\Panel\Field as PanelField;
use \Kirby\Exception\InvalidArgumentException;
use \Daandelange\Helpers\BlueprintHelper;
use \Closure;

class FieldHelper {
    // Experimental! : Transforms a content field to a form field
    // Note: this doesn't set the sibling values, 3rd argument to factory()
    public static function getFormFieldFromCmsField(ContentField $field): FormField | FormFieldClass {
        $blueprintData = BlueprintHelper::getFieldBlueprint($field, true);
        $blueprintData['model'] = $field->model();
        $blueprintData['value'] = $field->value(); // Works... but maybe better to use $field->fill($value) afterwards ?
        $blueprintData['name'] = $field->key();

        // WARNING: every call builds a new form field... How to grab it from pages ? Better cache this !
        return FormField::factory($blueprintData['type'], $blueprintData);
    }

    // Returns a field prop from a FormField if $propName is provided. Otherwise returns all the field's props.
    // Fixme : might return other vartypes
    public static function getFieldPropsFromFormField(FormField $formField, ?string $propName=null) : ?array {
        if(!$propName || empty($propName)) $propName = null;

        if($propName && is_string($propName)){ // IMPORTANT: Dont call the whole props() object, only the needed prop ! Prevents recursion issues
            return @$formField->{$propName}()??null;
        }

        $props = $formField->props(); // Note: if a props['someprop'] calls all props to fill its value, values won't get parsed correctly (infinite recursion?)
        return ($props && is_array($props))?$props:null;
    }

    // Returns a field prop from a ContentField if $propName is provided. Otherwise returns all the field's props.
    // Fixme : might return other vartypes
    public static function getFieldPropsFromCmsField(ContentField $field, ?string $propName=null) : ?array {
        $formField = static::getFormFieldFromCmsField($field);
        // Fixme: this could be done without building a formfield...
        return $formField ? static::getFieldPropsFromFormField($formField, $propName) : null;
    }

    // Grab field method from kirby's field config file, call it with the extra args and returns the result.
    // On fail, it returns the provided closure fallback or throws and error;
    // Dynamic runtime way of calling native kirby field functions as they are in the kirby installation.
    // Useful to handle inheritance in kirby 'extends' overrides where parent::sameFunction() inheritance is not available.
    // Note: not good for performance, parses the functions from data file on each first call, then uses cached function !!!
    public static function nativeFieldFunction(string $fromFieldType, string $functionName, FormField $field, ?Closure $fallback = null, ...$args ) {
        // Create Cache
        static $importedFunctions = [];
        // Build cache key
        $key = $fromFieldType.'-'.$functionName;

        // Get function from field definition if not already cached
        if( !isset($importedFunctions[$key]) ){ // Not yet loaded ?
            $importedData = @(require ($field->kirby()->root('kirby').'/config/fields/'.$fromFieldType.'.php'));
            
            // Couldn't import ?
            if( !$importedData ){
                throw new InvalidArgumentException('TaxonomyHelper::nativeFieldFunction() : The field `'.$fromFieldType.'` doesn\'t exist !');
            }
            
            // Resolve function address
            $functionNameResolved = $functionName;
            do {
                $separatorPos = strpos($functionNameResolved, '.');
                if($separatorPos!==false && is_array($importedData)){
                    $addr = substr($functionNameResolved, 0, $separatorPos);

                    if(array_key_exists($addr, $importedData) && is_array($importedData[$addr])){
                        $functionNameResolved = substr($functionNameResolved, $separatorPos+1);
                        $importedData = $importedData[$addr];
                        continue;
                    }
                }
                break;
            } while ($separatorPos!==false);

            // Now we should have the function
            if(!isset($importedData[$functionName]) || !($importedData[$functionName] instanceof Closure ) ){
                    throw new InvalidArgumentException('TaxonomyHelper::nativeFieldFunction() : Could not fetch entry "'.$functionName.'" as a closure in the native '.$fromFieldType.' field !');
            }
            $importedFunction[$key] = $importedData[$functionName];
        }
        // Continue : Use cached
        if( isset($importedFunction[$key]) && $importedFunction[$functionName] instanceof Closure){
            return $importedFunction[$key]->call($field, ...$args);
        }
        // If not found, this fallback allows hardcoding the required function :
        else {
            if($fallback){
                return $fallback->call($field, $value);
            }
            else {
                throw new InvalidArgumentException('TaxonomyHelper::nativeFieldFunction() : Could not call the native `'.$fromFieldType.'` field function `'.$functionName.'`, and no valid fallback was provided.');
            }
        }
    }
}