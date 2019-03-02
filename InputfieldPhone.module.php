<?php

/**
 * ProcessWire Phone Inputfieldtype
 * by Adrian Jones with code from "Soma" Philipp Urlich's Dimensions Fieldtype module and Ryan's core FieldtypeDatetime module
 *
 * ProcessWire 3.x
 * Copyright (C) 2010 by Ryan Cramer
 * Licensed under GNU/GPL v2, see LICENSE.TXT
 *
 * http://www.processwire.com
 * http://www.ryancramer.com
 *
 */

class InputfieldPhone extends Inputfield {

    public static function getModuleInfo() {
        return array(
            'title' => __('Phone Inputfield', __FILE__),
            'summary' => __('Multi part phone field, with custom output formatting options.', __FILE__),
            'version' => '3.1.0',
            'author' => 'Adrian Jones',
            'href' => 'http://modules.processwire.com/modules/fieldtype-phone/',
            'icon' => 'phone',
            'requires' => array("FieldtypePhone")
       );
    }

   /**
     * Default configuration for module
     *
     */
    static public function getDefaultData() {
        return array(
            "hide_input_labels" => 0,
            "placeholder_input_labels" => 0,
            "input_class" => '',
            "country_input" => 0,
            "country_input_label" => 'Ctry',
            "country_input_width" => 60,
            "area_code_input" => 0,
            "area_code_input_label" => 'Area',
            "area_code_input_width" => 80,
            "number_input_label" => 'Num',
            "number_input_width" => 140,
            "extension_input" => 0,
            "extension_input_label" => 'Ext',
            "extension_input_width" => 80,
            "output_format_override_input" => 0,
            "allow_letters_input" => 0
        );
    }

    /**
     * Construct the Inputfield, setting defaults for all properties
     *
     */
    public function __construct() {
        $this->fieldtypePhone = $this->wire('modules')->get('FieldtypePhone')->getArray();
        $this->set('output_format',$this->fieldtypePhone["output_format"]);
        $this->set('output_format_options',$this->fieldtypePhone["output_format_options"]);
        foreach(self::getDefaultData() as $key => $value) {
            $this->$key = $value;
        }
        parent::__construct();
    }

    /**
     * Per the Module interface, init() is called when the system is ready for API usage
     *
     */
    public function init() {
        return parent::init();
    }

    /**
     * Return the completed output of this Inputfield, ready for insertion in an XHTML form
     *
     * @return string
     *
     */
    public function ___render() {

        if($this->wire('languages')) {
            $language = $this->wire('user')->language;
            $lang = !$language->isDefault() ? $language : '';
        }
        else {
            $lang = '';
        }

        $out = '';

        $value = $this->attr('value') ? $this->attr('value') : new \Phone();

        $pattern = $this->allow_letters_input ? '[0-9A-Za-z]*' : '[0-9]*';

        if($this->country_input) {
            $out .= "<div class='phone_col'>";
            $out .= "<label>".($this->hide_input_labels ? '' : $this->{"country_input_label$lang"} . ' ')."<input type='text' pattern='".$pattern."' class='".$this->input_class."' ".($this->placeholder_input_labels ? ' placeholder="'.$this->{"country_input_label$lang"}.'"' : '') . ($this->country_input_width !== 0 ? ' style="width:'.$this->country_input_width.'px"' : '')." name='{$this->name}_country' id='Inputfield_{$this->name}_country' value='{$value->country}'/></label>";
            $out .= "</div>";
        }

        if($this->area_code_input) {
            $out .= "<div class='phone_col'>";
            $out .= "<label>".($this->hide_input_labels ? '' : $this->{"area_code_input_label$lang"} . ' ')."<input type='text' pattern='".$pattern."' class='".$this->input_class."' ".($this->placeholder_input_labels ? ' placeholder="'.$this->{"area_code_input_label$lang"}.'"' : '') . ($this->area_code_input_width !== 0 ? ' style="width:'.$this->area_code_input_width.'px"' : '')." name='{$this->name}_area_code' id='Inputfield_{$this->name}_area_code' value='{$value->area_code}'/></label>";
            $out .= "</div>";
        }

        $out .= "<div class='phone_col'>";
        $out .= "<label>".($this->hide_input_labels ? '' : $this->{"number_input_label$lang"} . ' ')."<input type='text' pattern='".$pattern."' class='".$this->input_class."' ".($this->placeholder_input_labels ? ' placeholder="'.$this->{"number_input_label$lang"}.'"' : '') . ($this->number_input_width !== 0 ? ' style="width:'.$this->number_input_width.'px"' : '')."  name='{$this->name}_number' id='Inputfield_{$this->name}_number' value='{$value->number}'/></label>";
        $out .= "</div>";

        if($this->extension_input) {
            $out .= "<div class='phone_col'>";
            $out .= "<label>".($this->hide_input_labels ? '' : $this->{"extension_input_label$lang"} . ' ')."<input type='text' pattern='".$pattern."' class='".$this->input_class."' ".($this->placeholder_input_labels ? ' placeholder="'.$this->{"extension_input_label$lang"}.'"' : '') . ($this->extension_input_width !== 0 ? ' style="width:'.$this->extension_input_width.'px"' : '')."  name='{$this->name}_extension' id='Inputfield_{$this->name}_extension' value='{$value->extension}'/></label>";
            $out .= "</div>";
        }

        if($this->output_format_override_input) {
            $out .= "<div class='phone_col'>";
            $out .= "<label>".($this->hide_input_labels ? '' : "{$this->_('Format')} ")."<select name='{$this->name}_output_format' id='Inputfield_{$this->name}_output_format'>";
            $out .= '<option value="" ' . ($this->output_format == '' ? 'selected' : '') . '>No Override</option>';
            $this->fieldtypePhone = $this->wire('modules')->get('FieldtypePhone');
            foreach($this->fieldtypePhone->buildOptions(explode("\n",$this->output_format_options), $this->data) as $option) {
                $out .= '<option value="'.$option[0].'" ' . (($option[0] == $value->output_format) ? 'selected' : '') . '>'.$option[1].'</option>';
            }
            $out .= "</select></label>";
            $out .= "</div>";
        }

        $out .= '<div style="clear:both; height:0">&nbsp;</div>';

        return $out;
    }

    /**
     * Process the input from the given WireInputData (usually $input->get or $input->post), load and clean the value for use in this Inputfield.
     *
     * @param WireInputData $input
     * @return $this
     *
     */
    public function ___processInput(WireInputData $input) {

        $this->fieldtypePhone = $this->wire('modules')->get('FieldtypePhone');

        $name = $this->attr('name');
        $value = $this->attr('value');

        if(is_null($value)) $value = new \Phone;

        $pn_names = array(
            'country' => $name . "_country",
            'area_code' => $name . "_area_code",
            'number' => $name . "_number",
            'extension' => $name . "_extension",
            'output_format' => $name . "_output_format"
       );

        // loop all inputs and set them if changed
        foreach($pn_names as $key => $name) {
            if(isset($input->$name)) {
                if($value->$key !== $input->$name) {
                    if(!$this->allow_letters_input && !is_numeric($input->$name) && !empty($input->$name) && $key != 'output_format') {
                        // in case the input isn't numeric show an error
                        $this->wire()->error($this->_("Field only accepts numeric values"));
                    }
                    elseif($key == 'output_format' || $this->allow_letters_input) {
                        $value->set($key, $this->wire('sanitizer')->text($input->$name));
                    }
                    else {
                        $value->set($key, $this->wire('sanitizer')->digits($input->$name));
                    }
                }
            }
        }

        if($value != $this->attr('value')) {
            $this->trackChange('value');
            // sets formatted value which is needed for Form Builder entries table
            $this->setAttribute('value', $this->fieldtypePhone->formatPhone($value->country, $value->area_code, $value->number, $value->extension, $this->fieldtypePhone->getFormatFromName($value->output_format ?: $this->output_format)));
        }
        return $this;
    }

    /**
     * Get any custom configuration fields for this Inputfield
     *
     * @return InputfieldWrapper
     *
     */
    public function ___getConfigInputfields() {

        $inputfields = parent::___getConfigInputfields();
        $this->fieldtypePhone = $this->wire('modules')->get('FieldtypePhone');
        $value = $this->hasField ?: $this;

        $f = $this->wire('modules')->get('InputfieldCheckbox');
        $f->attr('name', 'hide_input_labels');
        $f->label = __('Hide input labels', __FILE__);
        $f->description = __('Check to hide the component input labels', __FILE__);
        $f->columnWidth = 33;
        $f->attr('checked', $value->hide_input_labels ? 'checked' : '');
        $inputfields->append($f);

        $f = $this->wire('modules')->get('InputfieldCheckbox');
        $f->attr('name', 'placeholder_input_labels');
        $f->label = __('Placeholder input labels', __FILE__);
        $f->description = __('Check to show the component input labels as the placeholder', __FILE__);
        $f->columnWidth = 34;
        $f->attr('checked', $value->placeholder_input_labels ? 'checked' : '');
        $inputfields->append($f);

        $f = $this->wire('modules')->get('InputfieldText');
        $f->attr('name', 'input_class');
        $f->label = __('Input Class', __FILE__);
        $f->description = __('Class to add to component inputs.', __FILE__);
        $f->notes = __('eg. uk-input', __FILE__);
        $f->columnWidth = 33;
        $f->value = $value->input_class;
        $inputfields->append($f);

        // country
        $f = $this->wire('modules')->get('InputfieldCheckbox');
        $f->attr('name', 'country_input');
        $f->label = __('Country Code', __FILE__);
        $f->attr('checked', $value->country_input ? 'checked' : '');
        $f->description = __('Whether to ask for country code when entering phone numbers.', __FILE__);
        $f->columnWidth = 33;
        $inputfields->append($f);

        $f = $this->wire('modules')->get('InputfieldText');
        $f->attr('name', 'country_input_label');
        $f->label = __('Country input label', __FILE__);
        $f->attr('size', 100);
        $f->description = __('Name of Country input', __FILE__);
        $f->notes = __('Default: Ctry', __FILE__);
        $f->columnWidth = 34;
        $f->value = $value->country_input_label;
        if($this->wire('languages')) {
            $f->useLanguages = true;
            foreach($this->wire('languages') as $language) {
                if(!$language->isDefault() && isset($value->data["country_input_label$language"])) $f->set("value$language", $value->data["country_input_label$language"]);
            }
        }
        $inputfields->append($f);

        $f = $this->wire('modules')->get('InputfieldText');
        $f->attr('name', 'country_input_width');
        $f->label = __('Country input width', __FILE__);
        $f->attr('size', 10);
        $f->description = __('Width of the input in pixels.', __FILE__);
        $f->notes = __('Default: 60; 0 to not set width', __FILE__);
        $f->columnWidth = 33;
        $f->value = $value->country_input_width;
        $inputfields->append($f);


        // area code
        $f = $this->wire('modules')->get('InputfieldCheckbox');
        $f->attr('name', 'area_code_input');
        $f->label = __('Area Code', __FILE__);
        $f->description = __('Whether to ask for area code when entering phone numbers.', __FILE__);
        $f->notes = __('If this is unchecked, then area code and number will be store as one in the number field.', __FILE__);
        $f->columnWidth = 33;
        $f->attr('checked', $value->area_code_input ? 'checked' : '');
        $inputfields->append($f);

        $f = $this->wire('modules')->get('InputfieldText');
        $f->attr('name', 'area_code_input_label');
        $f->label = __('Area Code input name', __FILE__);
        $f->attr('size', 100);
        $f->description = __('Name of Area Code input', __FILE__);
        $f->notes = __('Default: Area', __FILE__);
        $f->columnWidth = 34;
        $f->value = $value->area_code_input_label;
        if($this->wire('languages')) {
            $f->useLanguages = true;
            foreach($this->wire('languages') as $language) {
                if(!$language->isDefault() && isset($value->data["area_code_input_label$language"])) $f->set("value$language", $value->data["area_code_input_label$language"]);
            }
        }
        $inputfields->append($f);

        $f = $this->wire('modules')->get('InputfieldText');
        $f->attr('name', 'area_code_input_width');
        $f->label = __('Area code input width', __FILE__);
        $f->attr('size', 10);
        $f->description = __('Width of the input in pixels.', __FILE__);
        $f->notes = __('Default: 80; 0 to not set width', __FILE__);
        $f->columnWidth = 33;
        $f->value = $value->area_code_input_width;
        $inputfields->append($f);

        // number
        $f = $this->wire('modules')->get('InputfieldText');
        $f->attr('name', 'number_input_label');
        $f->label = __('Number input name', __FILE__);
        $f->attr('size', 100);
        $f->description = __('Name of Number input', __FILE__);
        $f->notes = __('Default: Num', __FILE__);
        $f->columnWidth = 50;
        $f->value = $value->number_input_label;
        if($this->wire('languages')) {
            $f->useLanguages = true;
            foreach($this->wire('languages') as $language) {
                if(!$language->isDefault() && isset($value->data["number_input_label$language"])) $f->set("value$language", $value->data["number_input_label$language"]);
            }
        }
        $inputfields->append($f);

        $f = $this->wire('modules')->get('InputfieldText');
        $f->attr('name', 'number_input_width');
        $f->label = __('Number input width', __FILE__);
        $f->attr('size', 10);
        $f->description = __('Width of the input in pixels.', __FILE__);
        $f->notes = __('Default: 140; 0 to not set width', __FILE__);
        $f->columnWidth = 50;
        $f->value = $value->number_input_width;
        $inputfields->append($f);

        // extension
        $f = $this->wire('modules')->get('InputfieldCheckbox');
        $f->attr('name', 'extension_input');
        $f->label = __('Extension', __FILE__);
        $f->description = __('Whether to ask for extension when entering phone numbers.', __FILE__);
        $f->columnWidth = 33;
        $f->attr('checked', $value->extension_input ? 'checked' : '');
        $inputfields->append($f);

        $f = $this->wire('modules')->get('InputfieldText');
        $f->attr('name', 'extension_input_label');
        $f->label = __('Extension input name', __FILE__);
        $f->attr('size', 100);
        $f->description = __('Name of Extension input', __FILE__);
        $f->notes = __('Default: Ext', __FILE__);
        $f->columnWidth = 34;
        $f->value = $value->extension_input_label;
        if($this->wire('languages')) {
            $f->useLanguages = true;
            foreach($this->wire('languages') as $language) {
                if(!$language->isDefault() && isset($value->data["extension_input_label$language"])) $f->set("value$language", $value->data["extension_input_label$language"]);
            }
        }
        $inputfields->append($f);

        $f = $this->wire('modules')->get('InputfieldText');
        $f->attr('name', 'extension_input_width');
        $f->label = __('Extension input width', __FILE__);
        $f->attr('size', 10);
        $f->description = __('Width of the input in pixels.', __FILE__);
        $f->notes = __('Default: 80; 0 to not set width', __FILE__);
        $f->columnWidth = 33;
        $f->value = $value->extension_input_width;
        $inputfields->append($f);

        $f = $this->wire('modules')->get('InputfieldSelect');
        $f->attr('name', 'output_format');
        $f->label = __('Phone Output Format', __FILE__);
        $f->description = __("Select the format to be used when outputting phone numbers for this field.\n\nYou can define new formats for this dropdown select in the phone fieldtype module config settings.", __FILE__);
        $f->columnWidth = 66;
        $f->addOption('', __('None', __FILE__));
        foreach($this->fieldtypePhone->buildOptions(explode("\n", $this->fieldtypePhone->output_format_options), $this->data) as $option) {
            $f->addOption($option[0], $option[1]);
            if($value->output_format == $option[0]) $f->attr('value', $option[0]);
        }
        $inputfields->append($f);

        $f = $this->wire('modules')->get('InputfieldCheckbox');
        $f->attr('name', 'allow_letters_input');
        $f->label = __('Allow Letters in Input', __FILE__);
        $f->description = __('Whether to allow letters when entering phone numbers.', __FILE__);
        $f->notes = __('Some businesses use letters to make it easier to remember a number.', __FILE__);
        $f->columnWidth = 34;
        $f->attr('checked', $value->allow_letters_input ? 'checked' : '');
        $inputfields->append($f);

        $f = $this->wire('modules')->get('InputfieldCheckbox');
        $f->attr('name', 'output_format_override_input');
        $f->label = __('Output Format Override', __FILE__);
        $f->description = __('Whether to give option to override selected output format when entering phone numbers.', __FILE__);
        $f->attr('checked', $value->output_format_override_input ? 'checked' : '');
        $inputfields->append($f);

        return $inputfields;
    }

}
