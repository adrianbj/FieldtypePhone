# Phone Number Fieldtype

A ProcessWire fieldtype to enter phone numbers with 4 integer values for country, area code, number and extension and format the output based on predefined or custom options.

## StyledOutput

The most common usage option will be:
```
echo $page->fieldname //eg. +1 (123) 456-7890 x123
```
This provides a fully formatted phone number, based on the output format chosen from module's configuration page, or with the format override option (if enabled), when entering a phone number on a page.

This is a shortcut that produces the same output as:
```
echo $page->fieldname->formattedNumber //eg. +1 (123) 456-7890 x123
```

Alternate styled options are:

```
echo $page->fieldname->formattedNumberNoExt: //eg. +1 (123) 456-7890
echo $page->fieldname->formattedNumberNoCtry: //eg. (123) 456-7890 x123
echo $page->fieldname->formattedNumberNoCtryNoExt: //eg. (123) 456-7890

echo $page->fieldname->unformattedNumber: //eg. 11234567890123
echo $page->fieldname->unformattedNumberNoExt: //eg. 11234567890
echo $page->fieldname->unformattedNumberNoCtry: //eg. 1234567890123
echo $page->fieldname->unformattedNumberNoCtryNoExt: //eg. 1234567890
```

Of course the actual output is determined by the selected format output.

You can also call any of the defined formats manually like this:

```
echo $page->fieldname->australiaWithCountryAreaCodeNoLeadingZero;
```


## Raw Output

You can output the values for the component parts of the phone number like this:

```
echo $page->fieldname->country;
echo $page->fieldname->area_code;
echo $page->fieldname->number;
echo $page->fieldname->extension;
```

## Output for mobile compatibility

To get iOS and other mobile platforms to recognize numbers and be able to automatically dial them, use something like this:
```
echo '<a href="tel:+'.$page->fieldname->unformattedNumberNoExt.'">'.$page->fieldname->formattedNumber.'</a>';
```

## Selectors for searching

The component parts can be used in selectors like this:
```
$pages->find("phone.area_code=123");
```

## Field Settings

There is a field settings for the width of each number component in pixels.

You can also choose whether to display the country and extension fields for input. Off by default.

There is an additional checkbox that determines whether there is an option to override the default format option on a per entry basis, which will be useful when styling phone numbers from different countries on the one website. Off by default.


## Custom formatting options

On the module's configuration page you can choose from predefined formats, or create custom formats using syntax like this with one format per line: `name | format | example numbers` eg.
```
australiaWithCountryAreaCodeNoLeadingZero | {+[phoneCountry]} {([phoneAreaCode])} {[phoneNumber,0,3]}-{[phoneNumber,3,4]} {x[phoneExtension]} | 61,07,12345678,123
```

Note: when dialing from within Australia, area codes start with a 0, but when dialing from another country, the 0 must be omitted. The example format above handles this by truncating the first number from an Australian two digit area code which generates: 
`+1 (7) 1234 5678 x123` even though the full "07" is stored in the area code field.


**Component Notes**

Each component is surrounded by { }

The names of the component parts are surrounded by [ ]

Two optional comma separated numbers after the component name are used to get certain parts of the number using [PHP's substr function](http://php.net/manual/function.substr.php), allowing for complete flexibility.

Anything outside the [ ] or { } is used directly: +,-,(,),x, spaces, etc - whatever every you want to use.


## Setup

* Choose a Phone Output Format from the module's configuration page. You can also set the numbers that will be used in the formatted example which may be helpful in certain regions to give a more realistic example.
* Create a new Field with the new "Phone" Fieldtype.


## Support

http://processwire.com/talk/topic/4388-phone-number-fieldtype/


## To Do

Need to increase the number of pre-defined formats. There seem to be so many options and no real standards, so I thought rather than create a huge list of options that no-one will use, I thought I'd wait and get you guys to contribute them as you need them. Either post your formats here, or send me a PR on github and I'll add them.


## Acknowledgments

This module uses code from Soma's DimensionFieldtype and the core FieldtypeDatetime module - thanks guys for making it so easy.


## License

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

(See included LICENSE file for full license text.)