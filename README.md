# Kunena Birthday Module #
## Introduction ##
The Kunena Birthday Module shows the upcoming birthdays of your users. You can setup for example how much and to which date you want to display the birthdays. You can totally adjust the module after your wishes. Create your own template for the module, if you want or change the look of the string output over the language files or the Joomla! 2.5 build in language override.

#### You can choose between different user linkings ####
- Link to profile
- Link to a auto generated forum thread

You only need to add a Kunena category and a ID of an dummy user and the module will create a thread at the birthday of the an user. Feel free to change the subject string and/or the message. There is also a possibility to display the message in different languages.

#### The module supports ####
- JomSocial
- CommunityBuilder

#### Minimum Requirement ####
- PHP 5.2
- Joomla 1.6.0
- Kunena 1.6.0

If you have a suggestion or a problem please just write an issue in here or on kunena.org, where I setup a forum thread for the module.

## You want to support the development of the module? ##
[![Flattr this git repo](http://api.flattr.com/button/flattr-badge-large.png)](https://flattr.com/submit/auto?user_id=svanschu&url=https://github.com/svanschu/mod_sw_kbirthday_J16&title=mod_sw_kbirthday_J16&language=&tags=github&category=software)

## FAQ ##

#### How to show the actual name instead of the username? ####
- Go to your Kunena configuration under the user tab and set the option "Display User Name" to NO to display the real name.

#### Error - The language xx-XX don't exist ####
- You need to install the specific language files for you language. You can download or create those files at: https://opentranslators.transifex.com/projects/p/opentranslators/language/es_ES/?project=2719

#### How to position the module at the bottom of Kunena? ####
- Go to the module configuration and choose the kunena_bottom layout on the right. And then choose the module position kunena_bottom. If it is not in the list, just type it into the field and save the module.