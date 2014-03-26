#Lavender
**php templates that don't suck**

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

markup can be conditionally rendered using the `if` keyword. Lavender supports all the conditional operations you know and love `!`, `&&`, `||`, `<`. `>`, `<=`, `>=`, `==` and does a truthy check against the result.

```lavender
- some_variable = "this is stored in a variable"

if some_variable
  span.foo= some_variable

if some_variable_that_doesnt_exist
  span.foo this won't be rendered
```

becomes

```html
<span class="foo">this is stored in a variable</div>
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

**else**

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

math. you can do it. supported operators are `%`, `+`, `-`, `*`, `/` 

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
```

**extends**

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
