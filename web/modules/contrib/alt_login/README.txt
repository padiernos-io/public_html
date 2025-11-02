$Id: README.txt

****************************************************

Alternate login module -- README

Created by Chad Phillips: thehunmonkgroup at yahoo dot com
D8 version maintained by Matthew Slater: matslats dot net
****************************************************

This module provides an interface that allows registered users to use a login
name which is different than their username.

The login name is chosen from existing text fields on the user entity, including
he uid. To use, simply enable the module, visit admin/config/people/alt_login

Note that an alternate login name must be unique, and must not clash with any of
the site's usernames.

INSTALLATION:

1. Put the entire 'alt_login' folder in either:
      a.  /modules
      b.  /sites/YOURSITE/modules

2. Enable the module at Administer -> Modules.

3. At Administration » Config » People » Alt Login, and nominate the field to be 
   used.

4. At Administration » Config » People » Accounts » Fields arrange the forms and
   displays so that the user can see the field when they need to
