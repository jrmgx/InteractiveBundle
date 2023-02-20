# InteractiveBundle

A bundle to use the php REPL [PsySH][1] with [Symfony][2], learn more at [psysh.org][1].

What this Bundle do exactly?
 - Boots [PsySH][1] with your application dependencies
 - Exposes helpers commands to ease the use of your application


## Quick Look

To start:

```bash
$ bin/console interactive
- or - 
$ bin/interactive
```

### `service` helper

`service $your_variable_name = the_service_identifier`

You don't have to put the full service name, the helper will do its best to find out.
 
```
service $repo = BlogPostRepo
var_dump($repo) 
object(App\Repository\BlogPostRepository) { ... }
```


### `instance` helper

`instance $your_variable_name = the_class_name`

You don't have to put the full class name, the helper will do its best to find out.

``` 
instance $post = BlogPost
var_dump($post)
object(App\Entity\BlogPost) { ... }
```

### Full Example

```
$ bin/console interactive
This is an enhanced interactive PHP prompt for your Symfony project!

You can use those command to get class instances and services easily:
 - instance $i = YourClass will give you an instance of the class
 - service $s = ServiceName will give you the service

if any of those fail, you will have a prompt with a list of entries that could match,
pass *null* to be prompted from all
Psy Shell v0.11.12 (PHP 8.1.12 — cli) by Justin Hileman
> instance $post = blogPost

   FOUND  Class "\App\Entity\BlogPost" found for identifier: blogPost

- $post = resolved_class('\App\Entity\BlogPost');
= App\Entity\BlogPost {#8136}

> $post->setTitle('My Title')->setDate(new \DateTime())->setContent('Content');
= App\Entity\BlogPost {#8136}

> service $repo = blogpostRepository

   FOUND  Service "App\Repository\BlogPostRepository" found for identifier: blogpostRepository

- $repo = resolved_service('App\Repository\BlogPostRepository');
= App\Repository\BlogPostRepository {#8212}

> $repo->save($post, true);
= null

> 
```

That's it! You now have a new entry in your database.

Aside from that it's the plain old [PsySH][1]! 
You can also [customize it](#customize-psysh) to add your own commandes and variables.


## Install

You should use [Composer](https://getcomposer.org/) to install the bundle to your project:

```bash
$ composer require --dev jrmgx/interactive-bundle
```

## Usage

```bash
$ bin/console interactive
- or - 
$ bin/interactive
```

## Customize PsySH

### Adding a custom command

Adding a custom command for PsySH is as simple as inheriting from `Psy\Command\Command`.

### Adding custom variables

It is possible to add custom variables to the shell via configuration.
Variables can be of any type, container parameters references (e.g. `%kernel.debug%`) or even services
(prefixed with `@`, e.g. `"@my_service"`).

```yaml
# config/psysh.yml

psysh:
    variables:
        foo: bar
        router: "@router"
        some: [thing, else]
        debug: "%kernel.debug%"
```

Now if you run `bin/interactive` and then `ls`, 
you will see the variables `$foo`, `$router`, `$some` and `$debug`.

```
> ls
Variables: $foo, $router, $some, $debug...
```

## Credits

This bundle is developed by [Jerome Gangneux](https://jerome.gangneux.net)

This project has been made possible thanks to:

 - [Théo FIDRY](https://github.com/theofidry): main author of this Bundle before the fork ([found here](https://github.com/theofidry/PsyshBundle))
 - [Justin Hileman](https://github.com/bobthecow): author of [PsySH][1] and [all the contributors of the PsySH project](https://github.com/bobthecow/psysh/graphs/contributors)
 - [Adrian Palmer](https://github.com/navitronic): gave the lead for porting [PsySH][1] on [Symfony][2]


[1]: https://psysh.org/
[2]: https://symfony.com/
