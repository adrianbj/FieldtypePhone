<?php

/**
 * ProcessWire Phone Fieldtype
 * by Adrian Jones with code from "Soma" Philipp Urlich's Dimensions Fieldtype module and Ryan's core FieldtypeDatetime module
 *
 * Field that stores 4 numeric values for country/area code/number/extension and allows for multiple formatting options.
 *
 * ProcessWire 3.x
 * Copyright (C) 2010 by Ryan Cramer
 * Licensed under GNU/GPL v2, see LICENSE.TXT
 *
 * http://www.processwire.com
 * http://www.ryancramer.com
 *
 */

class FieldtypePhone extends Fieldtype implements Module, ConfigurableModule {


    public static function getModuleInfo() {
        return array(
            'title' => __('Phone', __FILE__),
            'summary' => __('Multi part phone field, with custom output formatting options.', __FILE__),
            'version' => '3.1.0',
            'author' => 'Adrian Jones',
            'href' => 'http://modules.processwire.com/modules/fieldtype-phone/',
            'installs' => 'InputfieldPhone',
            'requiredBy' => 'InputfieldPhone',
            'icon' => 'phone'
       );
    }

   /**
     * Default configuration for module
     *
     */
    static public function getDefaultData() {
        return array(
            "output_format" => "",
            "output_format_options" => '

/*North America without separate area code*/
northAmericaStandardNoSeparateAreaCode | {+[phoneCountry]} {([phoneNumber,0,3])} {[phoneNumber,3,3]}-{[phoneNumber,6,4]} {x[phoneExtension]} | 1,,2215673456,123
northAmericaStandardNoSeparateAreaCodeNoNumberDashes | {+[phoneCountry]} {([phoneNumber,0,3])} {[phoneNumber,3,7]} {x[phoneExtension]} | 1,,2215673456,123
northAmericaStandardNoSeparateAreaAllDashes | {+[phoneCountry]}-{[phoneNumber,0,3]}-{[phoneNumber,3,3]}-{[phoneNumber,6,4]} {x[phoneExtension]} | 1,,2215673456,123
northAmericaStandardNoSeparateAreaDashesNoNumberDashes | {+[phoneCountry]}-{[phoneNumber]} {x[phoneExtension]} | 1,,2215673456,123

/*North America with separate area code*/
northAmericaStandard | {+[phoneCountry]} {([phoneAreaCode])} {[phoneNumber,0,3]}-{[phoneNumber,3,4]} {x[phoneExtension]} | 1,221,5673456,123
northAmericaNoNumberDashes | {+[phoneCountry]} {([phoneAreaCode])} {[phoneNumber]} {x[phoneExtension]} | 1,221,5673456,123
northAmericaAllDashes| {+[phoneCountry]}-{[phoneAreaCode]}-{[phoneNumber,0,3]}-{[phoneNumber,3,4]} {x[phoneExtension]} | 1,221,5673456,123
northAmericaDashesNoNumberDashes | {+[phoneCountry]}-{[phoneAreaCode]}-{[phoneNumber]} {x[phoneExtension]} | 1,221,5673456,123

/*Australia*/
australiaNoCountryAreaCodeLeadingZero | {([phoneAreaCode,0,2])} {[phoneNumber,0,4]} {[phoneNumber,4,4]} {x[phoneExtension]} | 61,07,45673456,123
australiaWithCountryAreaCodeNoLeadingZero | {+[phoneCountry]} {([phoneAreaCode,1,1])} {[phoneNumber,0,4]} {[phoneNumber,4,4]} {x[phoneExtension]} | 61,07,45673456,123
'
        );
    }

    /**
     * Data as used by the get/set functions
     *
     */
    protected $data = array();

    /**
     * Populate the default config data
     *
     */
    public static $_data;

    public function __construct() {
        foreach(self::getDefaultData() as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Format the value for output, according to selected format and language
     *
     */
    public function ___formatValue(Page $page, Field $field, $value) {

        $outputCode = $this->getOutputFormat($value, $field);

        $value->formattedNumber = $this->formatPhone($value->country, $value->area_code, $value->number, $value->extension, $outputCode);
        $value->formattedNumberNoCtryNoExt = $this->formatPhone(null, $value->area_code, $value->number, null, $outputCode);
        $value->formattedNumberNoCtry = $this->formatPhone(null, $value->area_code, $value->number, $value->extension, $outputCode);
        $value->formattedNumberNoExt = $this->formatPhone($value->country, $value->area_code, $value->number, null, $outputCode);

        $value->unformattedNumberNoCtryNoExt = ($value->area_code ? $value->area_code : null) . ($value->number ? $value->number : null);
        $value->unformattedNumberNoCtry = ($value->area_code ? $value->area_code : null) . ($value->number ? $value->number : null) . ($value->extension ? $value->extension : null);
        $value->unformattedNumberNoExt = ($value->country ? $value->country : null) . ($value->area_code ? $value->area_code : null) . ($value->number ? $value->number : null);
        $value->unformattedNumber = $value->unformattedNumberNoExt . ($value->extension ? $value->extension : null);

        foreach(explode("\n",$this->data["output_format_options"]) as $format) {
            if(trim(preg_replace('!/\*.*?\*/!s', '', $format)) == '') continue;
            $formatParts = explode('|', $format);
            $formatName = trim($formatParts[0]);
            $formatCode = trim($formatParts[1]);
            $value->$formatName = $this->formatPhone($value->country, $value->area_code, $value->number, $value->extension, $formatCode);
        }

        return $value;
    }

    /**
     * Format the value for string output, eg in a Lister table
     *
     */
    public function ___markupValue(Page $page, Field $field, $value = null, $property = '') {
        if(is_null($value)) return;
        $outputCode = $this->getOutputFormat($value, $field);
        return $this->formatPhone($value->country, $value->area_code, $value->number, $value->extension, $outputCode);
    }

    /**
     *
     * Add mapping to different name for use in page selectors
     * This enables us to use it like "field.country=61, field.area_code=225, field.number=123456, field.extension=123"
     */
    public function getMatchQuery($query, $table, $subfield, $operator, $value) {
        if($subfield == 'raw') $subfield = 'data';
        if($subfield == 'country') $subfield = 'data_country';
        if($subfield == 'area_code') $subfield = 'data_area_code';
        if($subfield == 'number') $subfield = 'data_number';
        if($subfield == 'extension') $subfield = 'data_extension';

		if($this->wire('database')->isOperator($operator)) {
			// if dealing with something other than address, or operator is native to SQL,
			// then let Fieldtype::getMatchQuery handle it instead
			return parent::getMatchQuery($query, $table, $subfield, $operator, $value);
		}
		// if we get here, then we're performing either %= (LIKE and variations) or *= (FULLTEXT and variations)
		$ft = new DatabaseQuerySelectFulltext($query);
		$ft->match($table, $subfield, $operator, $value);
		return $query;

    }

    /**
     * get Inputfield for this fieldtype, set config attributes so they can be used in the inputfield
     *
     */
    public function getInputfield(Page $page, Field $field) {
        $pn = $this->wire('modules')->get('InputfieldPhone');
        return $pn;
    }

    /**
     * there's none compatible
     *
     */
    public function ___getCompatibleFieldtypes(Field $field) {
        return null;
    }

    /**
     * blank value is an WireData object Phone
     *
     */
    public function getBlankValue(Page $page, Field $field) {
        return new Phone($field);
    }

    /**
     * Any value will get sanitized before setting it to a page object
     * and before saving the data
     *
     * If value not of instance Phone return empty instance
     */
    public function sanitizeValue(Page $page, Field $field, $value) {

        if(!$value instanceof Phone) $value = $this->getBlankValue($page, $field);

        // report any changes to the field values
        if($value->isChanged('country')
            || $value->isChanged('area_code')
            || $value->isChanged('number')
            || $value->isChanged('extension')
            || $value->isChanged('output_format')) {
                $page->trackChange($field->name);
        }
        return $value;
    }

    /**
     * get values converted when fetched from db
     *
     */
    public function ___wakeupValue(Page $page, Field $field, $value) {

        // get blank phone number (pn)
        $pn = $this->getBlankValue($page, $field);

        $sanitizerType = $field->allow_letters_input ? 'text' : 'digits';

        // populate the pn
        if(isset($value['data'])) $pn->raw = $this->wire('sanitizer')->$sanitizerType($value['data']);
        if(isset($value['data_country'])) $pn->country = $this->wire('sanitizer')->$sanitizerType($value['data_country']);
        if(isset($value['data_area_code'])) $pn->area_code = $this->wire('sanitizer')->$sanitizerType($value['data_area_code']);
        if(isset($value['data_number'])) $pn->number = $this->wire('sanitizer')->$sanitizerType($value['data_number']);
        if(isset($value['data_extension'])) $pn->extension = $this->wire('sanitizer')->$sanitizerType($value['data_extension']);
        if(isset($value['data_output_format'])) $pn->output_format = $this->wire('sanitizer')->text($value['data_output_format']);

        return $pn;
    }

    /**
     * return converted from object to array for storing in database
     *
     */
    public function ___sleepValue(Page $page, Field $field, $value) {

        // throw error if value is not of the right type
        if(!$value instanceof Phone)
            throw new WireException("Expecting an instance of Phone");

        $sanitizerType = $field->allow_letters_input ? 'text' : 'digits';

        $sleepValue = array(
            'data' => $this->wire('sanitizer')->$sanitizerType($value->country . $value->area_code . $value->number),
            'data_country' => $this->wire('sanitizer')->$sanitizerType($value->country),
            'data_area_code' => $this->wire('sanitizer')->$sanitizerType($value->area_code),
            'data_number' => $this->wire('sanitizer')->$sanitizerType($value->number),
            'data_extension' => $this->wire('sanitizer')->$sanitizerType($value->extension),
            'data_output_format' => $this->wire('sanitizer')->text($value->output_format)
       );

        return $sleepValue;
    }

    /**
     * Get the database schema for this field
     *
     * @param Field $field In case it's needed for the schema, but usually should not.
     * @return array
     */
    public function getDatabaseSchema(Field $field) {

        $schema = parent::getDatabaseSchema($field);
        $schema['data'] = 'varchar(15) NOT NULL';
        $schema['data_country'] = 'varchar(15) NOT NULL';
        $schema['data_area_code'] = 'varchar(15) NOT NULL';
        $schema['data_number'] = 'varchar(15) NOT NULL';
        $schema['data_extension'] = 'varchar(15) NOT NULL';
        $schema['data_output_format'] = 'varchar(255) NOT NULL';
        // key for data will already be added from the parent
        $schema['keys']['data_country'] = 'KEY data_country(data_country)';
        $schema['keys']['data_area_code'] = 'KEY data_area_code(data_area_code)';
        $schema['keys']['data_number'] = 'KEY data_number(data_number)';
        $schema['keys']['data_extension'] = 'KEY data_extension(data_extension)';
        $schema['keys']['data_output_format'] = 'KEY data_output_format(data_output_format)';
        return $schema;
    }

    /**
     * Get any inputfields used for configuration of this Fieldtype.
     *
     * This is in addition to any configuration fields supplied by the parent Inputfield.
     *
     * @param Field $field
     * @return InputfieldWrapper
     *
     */
    public function getModuleConfigInputfields(array $data) {

        foreach(self::getDefaultData() as $key => $value) {
            if(!isset($data[$key]) || $data[$key]=='') $data[$key] = $value;
        }

        $inputfields = new InputfieldWrapper();

        $f = $this->wire('modules')->get('InputfieldSelect');
        $f->attr('name', 'output_format');
        $f->label = __('Phone Output Format', __FILE__);
        $f->description = __("Select the default format to be used when outputting phone numbers.\n\nYou can define new formats for this dropdown select in the 'Phone Output Format Options' field below.", __FILE__);
        $f->notes = __("This can be overridden on the Input tab of each 'phone' field.", __FILE__);
        $f->addOption('', __('None', __FILE__));
        foreach($this->buildOptions(explode("\n",$this->data["output_format_options"]), $this->data) as $option) {
            $f->addOption($option[0], $option[1]);
            if($this->data["output_format"] == $option[0]) $f->attr('value', $option[0]);
        }
        $inputfields->add($f);

        $f = $this->wire('modules')->get("InputfieldTextarea");
        $f->attr('name', 'output_format_options');
        $f->attr('value', $this->data["output_format_options"]);
        $f->attr('rows', 10);
        $f->label = __('Phone Output Format Options', __FILE__);
        $f->description = __("Any formats listed here will be available from the Phone Output Format selector above, as well as the Format Override selector when entering data for phone number fields.\n\nOne format per line: `name | format | example numbers`\n\nEach component of the phone number is surrounded by { }\nThe names of the component parts are surrounded by [ ]\nTwo optional comma separated numbers after the component name are used to get certain parts of the number using the [PHP substr() function](http://php.net/manual/function.substr.php), allowing for complete flexibility.\nAnything outside the [ ] or { } is used directly: +,-,(,),x, spaces, etc - whatever you want to use.\n\nPlease send me a PR on Github, or post to the support forum any new formats you create that you think others would find useful.", __FILE__);
        $inputfields->add($f);

        return $inputfields;
    }


    /**
     * Format a phone number with the given number format
     *
     * @param text $phoneCountry country code
     * @param text $phoneAreaCode area code
     * @param text $phoneNumber number
     * @param text $phoneExtension phone extension
     * @param string $format to use for formatting
     * @return string Formatted phone string
     *
     */
    public function formatPhone($phoneCountry, $phoneAreaCode, $phoneNumber, $phoneExtension, $format) {

        if(!$phoneNumber) return '';
        if(!strlen($format) || $format == '%s') return ($phoneCountry ? $phoneCountry : null) . ($phoneAreaCode ? $phoneAreaCode : null) . ($phoneNumber ? $phoneNumber : null) . ($phoneExtension ? $phoneExtension : null); // no formatting

        $pattern = preg_match_all("/{(.*?)}[^{]*/", $format, $components);

        $finalValue = '';
        $lastSuffix = '';
        foreach ($components[0] as $component) {

            $prefix = strstr($component, '[', true);
            $suffix = str_replace(']','',strstr($component, ']'));
            $component = str_replace(array($prefix, $suffix, '[', ']'), null, $component);

            if(strcspn($component, '0123456789') != strlen($component)) {
                $component_name = strstr($component, ',', true);
                $char_cutoffs = explode(',',ltrim(str_replace($component_name, '', $component),','));
                $value = trim(substr($$component_name, $char_cutoffs[0], $char_cutoffs[1]));
            }
            else {
                $component_name = $component;
                $value = $$component_name;
            }
            $finalValue .= ($value != '' ? $prefix . $value . $suffix : null);
            // if this component has no value, or is not numeric, remove the last suffix
            if($value == '' || !is_numeric($value)) $finalValue = rtrim($finalValue, $lastSuffix);
            $lastSuffix = str_replace('}', '', $suffix);
        }
        $finalValue = trim(str_replace(array('{', '}'), null, $finalValue));
        return $finalValue;
    }

    public function buildOptions($options, $data) {
        $optionsArr = array();
        foreach($options as $format) {
            if(trim(preg_replace('!/\*.*?\*/!s', '', $format)) == '') continue;
            $formatParts = explode('|', $format);
            $formatName = trim($formatParts[0]);
            $formatCode = trim($formatParts[1]);
            $defaultExampleNumbers = array(1,221,5673456,123);
            $exampleNumbers = isset($formatParts[2]) ? array_map('trim', explode(',', trim($formatParts[2]))) : $defaultExampleNumbers;
            $phoneNumberFormatted = $this->formatPhone(
                isset($exampleNumbers[0]) ? $exampleNumbers[0] : $defaultExampleNumbers[0],
                isset($exampleNumbers[1]) ? $exampleNumbers[1] : $defaultExampleNumbers[1],
                isset($exampleNumbers[2]) ? $exampleNumbers[2] : $defaultExampleNumbers[2],
                isset($exampleNumbers[3]) ? $exampleNumbers[3] : $defaultExampleNumbers[3],
                $formatCode
            );
            $optionsArr[] = array($formatName, $formatName . ' | ' . $phoneNumberFormatted);
        }
        return $optionsArr;
    }

    public function getFormatFromName($formatName) {
        foreach(explode("\n",$this->data['output_format_options']) as $format) {
            if(trim(preg_replace('!/\*.*?\*/!s', '', $format)) == '') continue;
            $formatParts = explode('|', $format);
            if(trim($formatParts[0]) == $formatName) {
                return trim($formatParts[1]);
            }
        }
    }

    public function getOutputFormat($value, $field) {
        if($value->output_format) {
            $output_format = $value->output_format;
        }
        elseif($field->output_format) {
            $output_format = $field->output_format;
        }
        else {
            $output_format = $this->data["output_format"];
        }

        return $this->getFormatFromName($output_format);
    }

}


/**
 * Helper WireData Class to hold a Phone object
 *
 */
class Phone extends WireData {

    public function __construct($field = null) {
        $this->field = $field;
        $this->set('country', null);
        $this->set('area_code', null);
        $this->set('number', null);
        $this->set('extension', null);
        $this->set('output_format', null);
    }

    public function set($key, $value) {

        if($key == 'country' || $key == 'area_code' || $key == 'number' || $key == 'extension') {
            // if value isn't numeric set it to blank and throw an exception so it can be seen on API usage
            if($this->field && !$this->field->allow_letters_input && !is_numeric($value) && !is_null($value) && $value != '') {
                $value = $this->$key ? $this->$key : '';
                throw new WireException("Phone Object only accepts numbers");
            }
        }
        return parent::set($key, $value);
    }

    public function get($key) {
        return parent::get($key);
    }

    public function __toString() {
        $number = (string)$this->formattedNumber ? (string)$this->formattedNumber : $this->data['number'];
        if(!$number) $number = '';
        return $number;
    }


}