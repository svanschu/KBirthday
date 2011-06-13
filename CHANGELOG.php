<?php
/**
 * @version $Id$
 * 
 * @package SW KBirthday Module
 *
 * @Copyright (C) 2009-2011 Schultschik Websolution All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.schultschik.de
 **/
// Dont allow direct linking

defined( '_JEXEC' ) or die();
?>
<!--

Changelog
------------

Legend:

* -> Security Fix
# -> Bug Fix
+ -> Addition
^ -> Change
- -> Removed
! -> Note

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Kunena Birthday Modul 1.7.2
14-April-2011
# [#25646] wrong default date format J1.6

09-April-2011
+ [#25590] de-DE sys.ini J1.6
# [#25598] no loading of sys.ini on installation J1.6
+ [#25599] Adding update Server J1.6 

08-April-2011
# [#25587] no thread when leap year birthday
^ [#25590] changed xml file to be J1.6 native
^ [#25590] date->toFormat() to date->format() J1.6
+ [#25590] sys.ini J1.6

Kunena Birthday Modul 1.7.1
17-February-2011
# [#24962] Missing argument
# [#24961] undefined index date

Kunena Birthday Modul 1.7.0
26-January-2011 svens
^ [#24621] rename folder
# [#24622] Bithday date not shown when activated
# [#24623] Limit birthdays works wrong
# [#24620] double posts
+ [#24626] create builder

31-October-2010 svens
+ [#23079] Add Kunena version and enable controll

30-Ocotber-2010 svens
# [#23072] fix Showed birthday from yesterday when birthday was in leap year
# [#23075] image kunena_bottom

29-Ocotber-2010 svens
^ update de-DE (thanks rich)

28-October-2010 svens
+ [#22923] add show birthdate

27-October-2010 svens
+ [#23042] add kunena_bottom tmpl
# [#23043] fix Missing name in message

24-October-2010 svens
# [#22999] fix current age instead of upcoming
^ [#23000] change clean up code
# [#23006] fix don't updated kunena-categories after create the bithday post
+ [#22922] add Multi language support

23-October-2010 svens
+ [#22995] New sort function
# [#22909] Kunena Birthday Modul 1.6.5 born in 2000


Kunena Birthday Modul 1.6.5

29-Sep-2010 Sven
^ moved additional option into advanced group
# white page with when other site is schown than kunena
# KunenaConfig
+ Botname translatedable
+ modulclass_sfx
# daytill == 1
+ language string SW_KBIRTHDAY_FORUMPOST_BOTNAME_DEF

Kunena Birthday Modul 1.6.4

10-Sep-2010 Sven
+ use autodeteced function of Kunena
+ new error language string
# wrong results when timeframe 0 for today only
# wrong leap year calc
# sort after inday
^ move daytill calc into SQL 

08-Sep-2010 Sven
# sorting of results by moving from php -> sql
# getdate calc wrong yeardate
+ second sort name/username
- server time option

07-Sep-2010 Sven
# SW_KBIRTHDAY_TIMEFROM_DESC in english file
# some misspellings in the english file
+ sorting output after daytill

Kunena Birthday Modul 1.6.3

09-Aug-2010 Sven
^ string output from str_replace to sprintf
# using the right time writing on the database without offset
# in leapyear calculation, not showing birthday when it goes over the year end
# make new thread when a new year begins

Kunena Birthday Modul 1.6.2

29-July-2010 Sven
+ reference time option: user
+ dropdown to choose template
+ language strings for new functions
^ moved stringreplace for age from template to helper
^ raiseError to raiseWarning
# check if the cb birthday field exist before reading it

28-July-2010 Sven
+ added language string if no birthdays are set
# fixed invalid argument for foreach when no birthdays
^ moved get params into helper.php
# fixed issue in leap year function when after sub the yday is smaller than today
+ serbian language - Thank you @quila from Kunena Team
+ reference time option: server,website,gmt
^ CB birthday field variable now

Kunena Birthday Modul 1.6.1

27-July-2010 Sven
# right userid in SQL query of CB and kunena
# unset user in array when no birhdate is set
# changed sql query to read the birthdate in same way no matter how it is saved
# fixed issue with leap years, 29 february when we have not currently a leap year

Kunena Birthday Modul 1.6.0
-->