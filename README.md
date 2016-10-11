![](https://raw.githubusercontent.com/lezhnev74/matches/master/logo.jpg)

# Matches

Library of data validators. 
Contains many common validation rules and easy ways to add\replace with your own.
Supports integration into any framework environment.
Supports multi-locale messages.

## Installation

First, pull dependency via this command: 

``` bash
composer require lezhnev74/matches
```

The library is depending on PHP 7 and this is intentional. I strongly advocate for moving to PHP 7 from older releases.

Depending on your framework you need to instantiate a Singleton \lezhnev74\Matches\Box class

## Simple Usage

Simple usage looks like this:

```php
use \Lezhnev74\Matches\Box;

$box = Box::getInstance();
$box->setLocale('en'); // default to "en"

$errors = $box->validate($data, [
    'name' => 'required|string:min=10;max=240',
    'photo' => 'file|types:allowed=image,video'
]);

if(count($errors)) {
    // handle errors:
    // $errors = [
    //      "name" => ['String must be at least 10 chars and at max 240 chars, but 241 chars detected']    
    //]
}
```

### Advanced case when you want to set your own rule

Validation rule can detect many validation problems in given data and thus the message can vary.
If validation fails the rule will throw an exception with two arguments:

- message name (this of a template for this case);
- message parameters (data to explain the reason);

```php
use \Lezhnev74\Matches\Box;
use \Lezhnev74\Matches\Exception\RuleValidationException;

$box = Box::getInstance();

$box->addRule('image', function($value, $parameters){
    // if we want to detect the mime type of this file
    if(!file_exists($value)) {
        throw new RuleValidationException('file_not_found',["path"=>$value]);
    }
    
    // do not throw any exceptions to pass the validation
});
// or alternatively your can set FQN:
// in this case Rule must inherit \Lezhnev74\Matches\BaseRule class
// The class will be instantiated upon validation 
$box->addRule('image', \App\Validators\MyOwnRule);

// Also you want to provide your own message resolver to get proper message for your Rule (for given locale)
// $rule_name would be "image" for example
// Placeholders like ":path" will be automatically replaced with given message_parameters you passed to RuleValidationException
$box->setMessageResolver(function($rule_name, $message_name = "default", $locale = "en"){
    $messages = [
        "image" => [
                "file_not_found" => [
                    "en" => ":field file was not found at this path :path",
                    "ru" => "Картинка :field не найдена в этом месте :path",
                ]
        ]
    ];
    
    return $messages[$rule_name][$message_name][$locale] ?? "Rule " . $rule_name . " failed";
});
// or with FQN
// in that case the target class must implement MessageResolver interface
$box->setMessageResolver('\App\Validation\MessageResolver');
```

## Message placeholders

Any message can contain placeholder which will be replaced automatically.
Library understands few placeholders out of the box:

- `:field` will be replaced with field name, 
- `:rule` placeholder will be replace with rule name,
- `:value` will be replaced with data under validation

You can provide your own placeholders:

- First, add placeholder to message template like "Number must be between :min and :max, but number is :value"
- Second provide parameters to RuleValidationException like this: `throw new RuleValidationException("template_name", ["min"=>10,"max"=>20])`

## Rules

Each rule validates values against built-in logic. If validation fails - the exception is thrown.
Exception contains empty message and parameters which will be put in the message.
 
Each template has multi-language support:

```php
return [
    "en" => "Field :field must be presented and cannot be empty",
    "ru" => "Параметр :field не может отсутствовать или быть пустым",
];
```


## Testing

Test are available via this command:

``` bash
vendor/bin/phpunit
```

## Contributing

Pull requests are warmly welcome.

## Credits

Library is maintained by Dmitriy Lezhnev.
Icon is by Bohdan Burmich from the Noun Project.
