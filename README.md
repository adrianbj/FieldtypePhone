FieldtypePhone
==============

ProcessWire Fieldtype for entering 4 part phone numbers: country/area code/number/extension as integers


# Phone Fieldtype

## What it does

This fieldtype let's you define phone numbers in 4 parts country / area code / number / extension as integers.

### Output the values in templates

There's a property for each part

```
echo $page->fieldname->country;
echo $page->fieldname->area_code;
echo $page->fieldname->number;
echo $page->fieldname->extension;
```
### Use in selectors strings

The parts of the phone number can be used in selectors like:

`$pages->find("phone.area_code=123");`

### Field Settings

* There's field settings for the width of the inputs in pixels.

## How to install

1. Download and place the module folder named "FieldtypePhone" in:
/site/modules/

2. In the admin control panel, go to Modules. At the bottom of the
screen, click the "Check for New Modules" button.

3. Now scroll to the FieldtypePhone module and click "Install". The required InputfieldPhone will get installed automatic.

4. Create a new Field with the new "Phone" Fieldtype.

## Support thread

[http://processwire.com/talk/topic/4388-phone-number-fieldtype/](http://processwire.com/talk/topic/4388-phone-number-fieldtype/)
