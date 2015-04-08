#Lavender

Lavender is a templating language for php loosely based on [jade](http://jade-lang.com/). 

##installation

**via composer**:

first get yourself some [composer](https://getcomposer.org/doc/00-intro.md#installation-nix). next you need to make yourself a `composer.json`, here's an example.

```json
{
  "require": {
    "lavender/lavender": "*"
  }
}
```

then run `php composer.phar install` or `composer install` depending on how you installed composer. once composer has finished it will generate an autoloader in `vendor/autoload.php` which you can `require()` from your application's bootstrap process.

**via git**:

add the lavender submodule with `git submodule add git@github.com:golavender/lavender.git <folder to clone to>` and include it with `require "<where you put lavender>/src/Lavender/lavender.php"` 

##usage

once you have installed and included lavender the only required configuration is to tell lavender where the views directory is.

```php
Lavender::config(array(
  
  /*
   * required - path to views directory
   */
  'view_dir'       => String,
  
  /*
   * optional - cache view files, if this is true
   *            the cache folder needs to be emptied
   *            every time view files are changed
   */
  //'caching'        => FALSE,
  
  /*
   * optional - defaults to something random in your tmp directory
   */
  //'cache_dir'      => NULL,

  /*
   * optional - defaults to "lavender"
   */
  //'file_extension' => String,

  /*
   * optional - defaults to TRUE
   *
   * renders a debugging error page instead of throwing an exception.
   * in production you should disable this and use a 500 page.
   */
  //'handle_errors'  => Boolean, 
));
```

rendering a template is as easy as

```php
$output = Lavender::view('some_template')->compile();

// or if you need to pass data into the template (probably the case)

$output = Lavender::view('some_template')->compile(array(
  'data'      => 'some data that the template will use',
  'more_data' => "moar data",
));
```

##language reference

**html**:

```lavender
section#some_id.foo.bar(attribute1='foo',attribute2='bar')
```

becomes

```html
<section id="some_id" class="foo bar" attribute1="foo" attribute2="bar"></section>
```

child nodes are indented below their parent

```lavender
section.foo
  div(data-something='foo') 
  div(data-something='bar') 
```

becomes

```html
<section class="foo">
  <div data-something="foo"></div>
  <div data-something="bar"></div>
</section>
```

there is a shortcut if you just want a div, you can skip right to class or id definitions


```lavender
.foobar text text
#foobar text text
```

becomes

```html
<div class="foobar">text text</div>
<div id="foobar">text text</div>
```

text can be added to nodes in two ways

```lavender
section.foo
  div(data-something='foo') this is some text 
  div(data-something='bar')
    | this is also some text 
```

Lavender knows not to put a closing tag on certain nodes. (we got the list from [here](http://www.w3.org/html/wg/drafts/html/master/syntax.html#void-elements))

```lavender
input(type="text",value="fooooobar")
```

becomes

```html
<input type="text" value="fooooobar">
```

**doctype**:

do you always forget the syntax for doctype? Lavender exists to solve your first world problems. just throw a `!!!` at the top of your layout and you get yourself a nice html doctype. doctypes supported are `html`, `transitional`, `strict`, `frameset`, `1.1`, `basic` and `mobile`. here are some pretty examples.

```lavender
!!!
```
defaults to html but you can also specify
```lavender
!!! html
```
the word `doctype` works too
```lavender
doctype html
```
```lavender
doctype strict
```

**comments**

comments are a thing

```lavender
// this is an html comment. it will be rendered to the page
//- this is a lavender comment. it will not be rendered
```

**conditional comments**

the `IE` shorthand can be used to generate conditional comments for Internet Explorer. check out [this](http://www.quirksmode.org/css/condcom.html) if you're not farmiliar with conditional comments.

```lavender
IE
  script(src="some script to make IE work")
IE lt 7
  script(src="some script to make super old IE work")
```
becomes
```html
<!--[if IE]>
  <script src="some script to make IE work"></script>
<![endif]-->
<!--[if lt IE 7]>
  <script src="some script to make super old IE work"></script>
<![endif]-->
```


**variables**:

lavender views can be passed variables from the parent php application or they can be defined right in the template.

this just assigns a variable without outputting anything to the page

```lavender
- some_variable = "this is stored in a variable"
```

note the `-` symbol, which evaluates an expression without outputting. the content of the variable can then later be output using the `=` symbol

```lavender
- some_variable = "this is stored in a variable"

span.foo= some_variable

span.bar
  = some_variable
```

becomes

```html
<span class="foo">this is stored in a variable</div>
<span class="bar">this is stored in a variable</div>
```

arrays too

```lavender
- some_array = ['foo', 'bar', 'baz']
```

and associative arrays

```lavender
- some_array = {key1: 'foo', key2: 'bar', key3: 'baz'} 
```

**conditionals**:

markup can be conditionally rendered using the `if` keyword. Lavender supports all the conditional operations you know and love `!`, `&&`, `||`, `<`. `>`, `<=`, `>=`, `==`, `!=` and does a truthy check against the result.

```lavender
- some_variable = "this is stored in a variable"

if some_variable
  span.foo= some_variable

if some_variable_that_doesnt_exist
  span.foo this won't be rendered

if FALSE
  div this isn't rendered
elseif FALSE
  div still not rendered
elseif TRUE
  div wooooooooo
else
  div sadface
```

becomes

```html
<span class="foo">this is stored in a variable</div>
<div>wooooooooo</div>
```

`TRUE` and `FALSE` (and `true` and `false`) are keywords in Lavender, they can be assinged to variables or used in conditionals directly (but if you actually do that then [wat](https://www.destroyallsoftware.com/talks/wat))

```lavender
- my_variable = TRUE

if my_variable
  div this will show up

if false
  div this will not
```

**loops**:

Lavender supports iterating over arrays and associative arrays with the `each` keyword

```lavender
ul
  each value in some_random_array
    li= value 

ul
  each value, key in some_random_array
    li(data-key=key)= value 
```

you may have noticed how i snuck in using variables for your html attributes there, yea you can do that too.

**else**:

the else keyword can be used after loops or conditionals

```lavender
if FALSE
  div will not show up
else
  div will show up

- empty_array = []

each value in empty_array
  div nothing to see here
else
  div empty array!
```

**math**:

math. you can do it. supported operators are `%`, `+`, `-`, `*`, `/`, `(`, `)` 

```lavender
div
  | 1 + 1 = 
  = 1 + 1

div
  | 2 - 3 = 
  = 2 - 3

div
  | 2 * 3 = 
  = 2 * 3

div
  | 10 / 5 = 
  = 10 / 5

ul
  each value, key in some_random_array
    if key % 2 == 0
      li(data-key=key)= value 
```

**include**:

partials are a thing

```lavender
span stuff and things

div.content
  include /path/relative/to/view/directory/somefile

div.content
  include /path/relative/to/view/directory/somefile with {stuff: "Asdfasdf"}
```

**extends**:

Lavender supports block style layout extension. this means that in the parent template you define blocks using the `block` keyword. then in the child template you **only have blocks** which override the blocks in the parent template

so if this was layout.lavender
```lavender
h1 this is a pretty cool web page

block header
  | you can put some default content in here
  | it will be displayed if no child template
  | overrides it

div.content
  block content
```

and this is the child template

```
extends layout

block header
  div
    span
      | foo bar baz


block content
  | content
  | more content
  | moar content
```

and you were to render the child temlate, you would get

```html
<h1>this is a pretty cool web page</h1>

<div>
  <span>foo bar baz</span>
</div>

<div class="content">
  content
  more content
  moar content
</div>
```

a special case has been added to allow definining variables at the top of your child templates, these variables can be referenced from the layout to do cool things like specifying a list of javascript files necessary for a template to work
or setting the page title.

###whitespace

newlines are added automatically after every html element, if you don't want the newline you can add a `-` to the 
node definition like so.

```lavender
p
  a(href="/some/place")- click here for some cool stuff
  |.
```


automagic whitespace management is hard and subject to change.

###filters

programatic expressions in Lavender are a little limited, none of your favorite php functions are available for modifying the template data. this is by design, we don't think there should be a ton of logic in templates when that logic could be in controllers or models. however since you have to be able to do *some* templating logic we added filters. it's super easy to add your own filters to Lavender and there are (or will be) plenty in place out of the box. here's how they work.

```lavender
- myvariable = "some really cool text"

div
  span
    | i'm gonna filter some stuff. it's gonna be cool. 
    = myvariable | upper 
```

becomes

```html
<div>
  <span>im gonna filter some stuff. it's gonna be cool. SOME REALLY COOL TEXT</span>
</div>
````
profound right? not that we invented this, we copied the idea from [twig](http://twig.sensiolabs.org/). the filters Lavender comes with are:

**upper**:
`="string"|upper` becomes `STRING`

**trim**:
`=" string  "|trim` becomes `string`

**title**:
`="this is a title"|title` becomes `This Is A Title`

**split**:
`"these are some words"|split(' ')` becomes the array `['these', 'are', 'some', 'words']`

**sort**:
`"these are some words"|split(' ')|sort` becomes the array `['are', 'some', 'these', 'words']`

**capitalize**:
`= "these are some words"|capitalize` becomes `These are some words`

**date**:
`= "1396372567"|date` becomes `4/1/2014`
`= "1396372567"|date('Y')` becomes `2014`

**default**:
`= []|default('foobar')` becomes `foobar`

**first**:
`= ['asdf','qwer']|first` becomes `asdf`

**join**:
`= ['asdf','qwer']|join` becomes `asdf qwer`
`= ['asdf','qwer']|join('-')` becomes `asdf-qwer`

**last**:
`= ['asdf','qwer']|last` becomes `qwer`
`= ['asdf','qwer', 'zxcv']|last(2)|join('-')` becomes `qwer-zxcv`

**keys**:
`{'asdf': 'foo','qwer': 'bar'}|keys` becomes the array `['asdf', 'qwer']`

**length**:
`= ['asdf','qwer']|length` becomes `2`

**lower**:
`= "FOO BAR"|lower` becomes `foo bar`

**nl2br**:
`= "new\nline"|nl2br` becomes `new<br>line`

**round**:
`= 12345.123|round(2)` becomes `12345.12`

**merge**:
`['foo', 'bar']|merge(['baz'])` becomes the array `['foo', 'bar', 'baz']`

**replace**:
`= "foo bar"|replace(' ', '-')` becomes `foo-bar`

**reverse**:
`['asdf', 'qwer']|reverse` becomes the array `['qwer', 'asdf']`

**slice**:
`['foo', 'bar', 'baz']|slice(1, 1)` becomes the array `['bar']`

**number_format**:
`= 10000000000.12345|number_format()` becomes `10,000,000,000`
`= 10000000000.12345|number_format(2, ',', '.')` becomes `10.000.000.000,12`

**ceil**:
`= 10000000000.12345|ceil` becomes `10000000001`
    
**floor**:
`= 10000000000.12345|floor` becomes `10000000000`

**relative**:
if you had a `timestamp` variable `timestamp|relative` would return things like 'just now' or 'in 3 days' or `6 minutes ago`

**contains**:
`= ['asdf', 'qwer']|contains('asdf')` returns TRUE 

**is**:

`= ['asdf', 'qwer']|is('list')` returns TRUE 

`= {asdf: 'qwer'}|is('object')` returns TRUE 

`= 8|is('number')` returns TRUE 

`= myVariable|is('My_Class_Name')` also works 

**json**:
`= ['asdf', 'qwer']|json` returns "['asdf','qwer']" 

