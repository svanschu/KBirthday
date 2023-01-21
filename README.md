# SchuWeb Birthday #
## Introduction ##
SchuWeb Birthday is a module to display the birthday of your users and creates greetings. It's independent to the Kunena API and can be used without having Kunena installed in your Joomla! site. That means, that you can use the module with Community Builder or JomSocial without Kunena.

SchuWeb Birthday shows the upcoming birthdays of your users. You can setup for example how much and to which date you want to display the birthdays. You can totally adjust the module after your wishes. Create your own template for the module, if you want or change the look of the string output over the language files or the Joomla! build in language override.

#### You can choose between different user linkings ####
- Link to profile
- Link to a auto generated forum thread
- Send a greetings e-mail once

You only need to add a Kunena category and a ID of an dummy user and the module will create a thread at the birthday of the an user. Feel free to change the subject string and/or the message. There is also a possibility to display the message in different languages.

#### The module supports ####
- JomSocial
- CommunityBuilder 2.7
- Kunena 6.0.0

#### Minimum Requirement ####
- PHP 8.1
- Joomla 4.0
- Kunena or Community Builder or JomSocial

If you have a suggestion or a problem please just write an issue in here or on kunena.org, where I setup a forum thread for the module.

#### Links
- [Demo](https://demo.schultschik.com/)
- [Documentation](https://extensions.schultschik.com/documentation/schuweb-birthday-module)
- [Project website](https://extensions.schultschik.com/schuweb-birthday-module)

## FAQ ##

#### How to show the actual name instead of the username? ####
- Go to your Kunena configuration under the user tab and set the option "Display User Name" to NO to display the real name.

#### Error - The language xx-XX don't exist ####
- You need to install the specific language files for you language. You can download or create those files at: https://opentranslators.transifex.com/projects/p/opentranslators/language/es_ES/?project=2719

#### How to position the module at the bottom of Kunena? ####
- Go to the module configuration and choose the kunena_bottom layout on the right. And then choose the module position kunena_bottom. If it is not in the list, just type it into the field and save the module.

## Donation
Want to support the project? 

![GitHub Sponsors](https://img.shields.io/github/sponsors/svanschu?style=social)

[![Donate](https://img.shields.io/badge/Donate-PayPal-green)](https://paypal.me/SchuWeb?locale.x=de_DE)
