# Ribase Console

## A Console for TYPO3 inspired from drush in Drupal

### Commands

- alias
    - alias:init
    - alias:show

- database
    - database:dump                                                       
    - database:dumpthis                                                   
    - database:importthis                                                 
    - database:sync 
  
- fileadmin
    - fileadmin:sync
    
### Usage

#### Alias
I case of use please create aliases of your instances.
Recommended usage is:

    - @local for local instances
    - @dev for foreign instances (dev-branch)
    - @test for foreign instances (test-branch)
    - @prod for foreign instances (master-branch)

#### Database

For extended usage of database commands you will be forced to create aliases to sync between them

##### Examples
To just dump your local database use

    - database:dumpthis $: ../vendor/bin/typo3 database:dumpthis
    
Same for import your local database use

    - database:dumpthis $: ../vendor/bin/typo3 database:importthis

This will move your database from local to dev server

    - database:sync $: ../vendor/bin/typo3 database:sync @local @dev

If you like to dump on foreign host

    - database:dump $: ../vendor/bin/typo3 database:dump @dev
    
#### Fileadmin

A simple way to sync fileadmins between local or foreign hosts

##### Example
Sync your files

    - fileadmin:sync $: ../vendor/bin/typo3 fileadmin:sync @local @dev
