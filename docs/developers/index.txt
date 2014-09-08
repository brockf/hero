# Developers' Guide

You can do a lot with Hero by configuring it to taste in the control panel and [customizing your templates](/docs/designers/index), but that's just the beginning of this powerful web framework.  Beneath every module, template plugin, controller, and feature lies PHP code written to the highest [standards](/docs/developers/standards).  The framework is built so that any PHP developer can jump right into the code and begin [creating his/her own modules](/docs/developers/modules), [developing custom fieldtypes](/docs/developers/forms), and tapping into the developer-friendly API's in the form of models and libraries.

First, let's take a quick overview of the system and give you some jumping off points to learn about development with Hero.

## Hero is built on the CodeIgniter Framework

[CodeIgniter](http://www.codeigniter.com) is an open source, free PHP web framework that [outperforms other PHP frameworks](http://www.google.ca/search?q=codeigniter+performance).  By developing Hero on top of this framework, we adopt a number of programming standards and widely understood model-view-controller framework.  This allows outside developers to understand and hop into the Hero source code very easily.

For information on CodeIgniter, view this guide's [CodeIgniter](/docs/developers/codeigniter) page.  For information on our own coding standards and best practices, visit the [Standards and Best Practices](/docs/developers/standards) guide.

## Everything is modular

Every template plugin, control panel controller, frontend controller, view file, and library is modular.  Each module exists as a folder in `/app/modules/`.  This means that you never have to touch "core" Hero modules which may be overwritten by a future upgrade - you can just create your own module and drop it in to your installation.

*All modules have the same priority and features - no special treatment!*  We don't hide features from outside developers and every core model and library is documented in the Reference section of this Developer's guide.

## Upgrade logic

Hero is upgraded by [uploading all of the new release files over the old files](/docs/installation/upgrading).  What does this mean for developers?  *You never want to edit the core files, unless you plan on not upgrading to future releases or merging new releases into your existing install*.  This is an easy thing to say, but many frameworks don't make it possible.  With Hero, it's one of our main goals, and you'll see that you can do almost anything imaginable without touching the core files and ruining your eligibility for upgrades.

## Hooks

In keeping with the methodology that you should never have to modify a core file, we offer a simple yet powerful [hook system](/docs/developers/reference/app_hooks_library) that allows you to bind any of your functions or methods to a system hook.  Your code will then be triggered when this hook is tripped.

## How to start

* Take a look at the [standards and practices](/docs/developers/standards) for Hero development.
* Not familiar with CodeIgniter?  [Get up to speed](/docs/developers/codeigniter).
* Learn how to [create a module](/docs/developers/modules).
* Take a peek at [developing template plugins](/docs/developers/template_plugins) to be called in templates.
* Extend functionality universally by [creating new custom fieldtypes](/docs/developers/forms).
* Use the Reference guide to learn about any internal model or library.