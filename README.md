# README

An extension for phpBB 3.2 that provides a page to rename users taking into
account the whitelist points.

# Features

* Provides the page /rename_user that enables renaming users.
* Only allows renaming in case the user has less than 300 posts. Otherwise the
  user may create a new account.
* To take into account the user's whitelist points the following rules apply:
  * for renames all data is transferred.
  * for new accounts only the neutral and negativ points are transferred

## TODO
* Show in a user's profile page that the user has been renamed / has a new account.

## Installation

clone it into phpBB3/ext/gpc/rename_users


