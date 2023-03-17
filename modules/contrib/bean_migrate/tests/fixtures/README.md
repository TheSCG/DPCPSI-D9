# About

This document is about how to maintain and extend the Drupal 7 database fixture
and the related file assets that are used by Bean Migrate PHPUnit tests.
You will be able to easily create a Drupal 7 instance that incorporates the
database and file fixtures, add the required modifications for the new test
cases and re-export the database and the new files back to code.


## Requirements

- Alias for the source database's Drupal 7 instance: `drupal7-bean.localhost`.

- A Drupal 8|9 codebase for the database import-export script.

- (Recommended, but optional) Drush `8`. This is the last version compatible
  with Drupal 7.


## Create the Drupal 7 instance that represents the DB and file fixtures

### Codebase

- Change directory to the Bean Migrate module's root.
- `drush make ./tests/fixtures/drupal7-bean.make.yml ./tests/fixtures/bean`
-
   ```
   cp ./tests/fixtures/settings.php \
     ./tests/fixtures/bean/sites/default/settings.php
   ```
- Copy files:

   ```
   cp -r ./tests/fixtures/files/sites/default/files \
     ./tests/fixtures/bean/sites/default/
   ```


### Database

The next steps are almost the same as in the
[Generating database fixtures for D8 Migrate tests][1] documentation and require
a Drupal 8|9 instance. You can skip the _Set up Drupal 6 / 7 installation that
uses your test database_ section since it is replaced by the make files
we provide.

- If it does not exist, create a new database with name `drupal7_bean` for the
  Drupal 7 source instance. (`mysql -u <user> -p -e`)

  -
     ```
     CREATE DATABASE drupal7_bean \
       DEFAULT CHARACTER SET = 'utf8' \
       DEFAULT COLLATE 'utf8_general_ci';
     ```
  - `GRANT ALL PRIVILEGES ON drupal7_bean.* TO 'devuser'@'localhost';`

- Make sure that the `drupal7_bean` DB is empty.

- [Define a database connection to your empty database][2] in your Drupal 8|9
  `settings.php`:
  ```
    $databases['migrate']['default'] = array (
      'database' => 'drupal7_bean',
      'username' => 'devuser',
      'password' => 'devpassword',
      'prefix' => '',
      'host' => 'localhost',
      'port' => '3306',
      'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
      'driver' => 'mysql',
      // For being compatible with MySQL 5.5.
      'charset' => 'utf8',
      'collation' => 'utf8_general_ci',
      '_dsn_utf8_fallback' => TRUE,
    );
    ```

- Import the Drupal 7 Bean Migrate fixture into this database.
  From your Drupal 8|9 project root, run:
   ```
   php core/scripts/db-tools.php import \
     --database migrate \
     [path-to-bean_migrate]/tests/fixtures/drupal7_bean.php
   ```

- [Add a row for uid 0 to {users} table manually][3].
  -
    ```
    drush -u 1 sql-query \
      "INSERT INTO users \
        (name, pass, mail, theme, signature, language, init, timezone) \
        VALUES ('', '', '', '', '', '', '', '')"
    ```
  - `drush -u 1 sql-query "UPDATE users SET uid = 0 WHERE name = ''"`


##  Log in to your test site and make the necessary changes

These necessary changes could be for instance:
- Someone found a bug that can be reproduced with a well-prepared source data.
- Drupal 7 core, or one of the contrib modules that the Drupal 7 fixture
  uses got a new release, and we have to update the fixture database (and even
  the drush make file).

  In this case, after that the corresponding component was updated, we have to
  run the database updates.

Admin (uid = 1) user's credentials:
- Username is `admin`
- Password is `password`

Bean manager (uid = 2) user's credentials:
- Username is `editor`
- Password is `password`

Authenticated (uid = 3) user's credentials:
- Username is `user`
- Password is `password`

If you need to add or update a contrib module, or update core: please don't
forget to update the drush make file as well!


## Export the modifications you made

- Export the source DB to the fixture file (From your Drupal 8|9 project root):
   ```
   php core/scripts/db-tools.php dump \
     --database migrate \
     > [path-to-bean_migrate]/tests/fixtures/drupal7_bean.php
   ```

- Copy the files directory back into the git repository:
   ```
   cp -r tests/fixtures/bean/sites/default/files \
     tests/fixtures/files/sites/default/
   ```

- You can remove the untracked and ignored files if you think so:
  `git clean -fdx ./tests/fixtures/`


[1]: https://www.drupal.org/node/2583227
[2]: https://www.drupal.org/node/2583227#s-importing-data-from-the-fixture-to-your-testdatabase
[3]: https://www.drupal.org/node/1029506
