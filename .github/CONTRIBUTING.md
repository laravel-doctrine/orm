# Contribution Guide

- [Bug Reports](#bug-reports)
- [Core Development Discussion](#core-development-discussion)
- [Which Branch?](#which-branch)
- [Coding Style](#coding-style)

<a name="bug-reports"></a>
## Bug Reports

To encourage active collaboration, we strongly encourages pull requests, not just bug reports. "Bug reports" may also be sent in the form of a pull request containing a failing test.

However, if you file a bug report, your issue should contain a title and a clear description of the issue. You should also include as much relevant information as possible and a code sample that demonstrates the issue. The goal of a bug report is to make it easy for yourself - and others - to replicate the bug and develop a fix.

Remember, bug reports are created in the hope that others with the same problem will be able to collaborate with you on solving it. Do not expect that the bug report will automatically see any activity or that others will jump to fix it. Creating a bug report serves to help yourself and others start on the path of fixing the problem.

The Laravel Doctrine source code is managed on Github, and there are repositories for each of the Laravel Doctrine projects:

- [Laravel Doctrine ORM](https://github.com/laravel-doctrine/orm)
- [Laravel Doctrine ACL](https://github.com/laravel-doctrine/acl)
- [Laravel Doctrine Extensions](https://github.com/laravel-doctrine/extensions)
- [Laravel Doctrine Migrations](https://github.com/laravel-doctrine/migrations)
- [Laravel Doctrine Documentation](https://github.com/laravel-doctrine/docs)
- [Laravel Doctrine Website](https://github.com/laravel-doctrine/laraveldoctrine.org)

<a name="core-development-discussion"></a>
## Core Development Discussion

Discussion regarding bugs, new features, and implementation of existing features takes place in the channel linked to the package of the [Laravel Doctrine Slack](http://slack.laraveldoctrine.org) Slack team. 

<a name="which-branch"></a>
## Which Branch?

**All** bug fixes should be sent to the latest stable branch. Bug fixes should **never** be sent to the `master` branch unless they fix features that exist only in the upcoming release.

**Minor** features that are **fully backwards compatible** with the current Laravel Doctrine release may be sent to the latest stable branch.

**Major** new features should always be sent to the `master` branch, which contains the upcoming Laravel Doctrine release.

If you are unsure if your feature qualifies as a major or minor, please ask help in the `#general` channel of the [Laravel Doctrine Slack](http://slack.laraveldoctrine.org) Slack team.

It's always a good idea to back your Pull Request with tests, proving your implementation works and gives an idea of what your code does. Always run the tests locally to see if you didn't break any existing code.

<a name="coding-style"></a>
## Coding Style

Laravel Doctrine follows the [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) coding standard and the [PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md) autoloading standard.

A config file for [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) is included. Every PR will be analyzed for PSR-2 and PSR-4 by [StyleCI](https://styleci.io/).