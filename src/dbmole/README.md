DbMole
======

[![Build Status](https://app.travis-ci.com/atk14/DbMole.svg?branch=master)](https://app.travis-ci.com/atk14/DbMole)

DbMole provides basic functionality with database (Postgresql, MySQL or Oracle).

Basic usage
-----------

At first, define the global function dbmole_connection which returns connection to the database.

Only one Postgresql database is considered in this example.

    function dbmole_connection($dbmole){
      return pg_connect("dbname=testing_database host=localhost user=test password=test123");
    }

#### Instantiating

    $dbmole = PgMole::GetInstance();

#### Selecting rows

    $rows = $dbmole->selectRows("SELECT id,title,author FROM books");
    foreach($rows as $row){
      echo $row["id"].": ".$row["title"]." (".$row["author"].")<br>";
    }

#### Iterating over rows

For large result sets, use `iterateRows()` instead of `selectRows()`. It returns a generator and fetches rows one by one, keeping memory usage low.

    foreach($dbmole->iterateRows("SELECT id,title,author FROM books") as $row){
      echo $row["id"].": ".$row["title"]." (".$row["author"].")<br>";
    }

Limit and offset are supported:

    foreach($dbmole->iterateRows("SELECT * FROM employees",[],["limit" => 100, "offset" => 0]) as $row){
      // process $row
    }

The `cache` option is not available for `iterateRows()`.

#### Selecting single row

    $row = $dbmole->selectRow("SELECT id,title,author FROM books WHERE id=123");
    var_dump($row); // ["id" => "123", "title" => "Book Title", "author" => "John Doe"]

#### Selecting single value

    $amount_of_books = $dbmole->selectSingleValue("SELECT COUNT(*) FROM books");
    // or better
    $amount_of_books = $dbmole->selectInt("SELECT COUNT(*) FROM books");

For selecting single values, there are also methods:

- selectValue()
- selectInt()
- selectFloat()
- selectBool()
- selectString()

#### Safe binding of the query variables

    $rows = $dbmole->selectRows("SELECT id,title,author FROM books WHERE UPPER(title) LIKE UPPER(:search)",[":search" => "%Goodies%"]);
    $row = $dbmole->selectRow("SELECT id,title,author FROM books WHERE id=:id",[":id" => 123]);
    $dbmole->doQuery("UPDATE books SET title=:title, author=:author WHERE id=:id",[":id" => 123,":title" => "Good Reading", ":author" => "Samantha Doe"]);

Objects can be used as bind values. If the object has a `getId()` method, its return value is used; otherwise the object is cast to string.

    $dbmole->selectRow("SELECT * FROM books WHERE id=:id",[":id" => $book]);
    $dbmole->doQuery("UPDATE books SET author_id=:author WHERE id=:id",[":author" => $author, ":id" => $book]);

#### Limiting rows:

    $rows = $dbmole->selectRows("SELECT * FROM employees",[],["limit" => "10", "offset" => 0]);
    $rows = $dbmole->selectRows("SELECT * FROM employees WHERE created_at>=:date",[":date" => "2020-01-01"],["limit" => "10", "offset" => 0]);

#### Working in transaction

    $dbmole->begin();

    // do something with $dbmole

    $dbmole->commit();

#### Working in transaction, avoiding unnecessary database connections

    $dbmole->begin(["execute_after_connecting" => true]);

    // do something with $dbmole and sometimes do nothig

    if($dbmole->isConnected()){
      $dbmole->commit();
    }

#### Insering new record into a table

    $dbmole->insertIntoTable("books",[
      "id" => 123,
      "title" => "Nice Reading",
      "author" => "Brody Doe"
    ]);

#### Sequencies

    $next_id = $dbmole->selectSequenceNextval("seq_book");
    $curr_id = $dbmole->selectSequenceCurrval("seq_book");

#### Error callback

When an error occurs on SQL level, DbMole call the specified callback.

    DbMole::RegisterErrorHandler("dbmole_error_handler");

    function dbmole_error_handler($dbmole){
      echo "Dear visitor, unfortunately an error has occurred";
      $dbmole->sendErrorReportToEmail("admin@example.com");
      $dbmole->logErrorReport();
      exit(1);
    }

#### Connecting to more databases

Specify all connections in the dbmole_connection function:

    function dbmole_connection($dbmole){
      $database_type = $dbmole->getDatabaseType();
      $config = $dbmole->getConfigurationName();

      switch($database_type){
        case "postgresql":
          if($config=="default"){
            return pg_connect("dbname=testing_database host=localhost user=test password=test123");
          }
          if($config=="import"){
            return pg_connect("dbname=import_database host=localhost user=import password=import11122");
          }
          break;

        case "mysql":
          return mysqli_connect("127.0.0.1", "username", "password", "database", 3306);
          break;
      }
    }

Instantiating:

    $dbmole = PgMole::GetInstance(); // same as PgMole::GetInstance("default")
    $dbmole_import = PgMole::GetInstance("import");
    $dbmole_mysql = MysqlMole::GetInstance();

Installation
------------

Just use the Composer:

    composer require atk14/dbmole

Testing
-------

Install required dependencies for development:

    composer update --dev

Run tests:

    cd test
    ../vendor/bin/run_unit_tests

License
-------

DbMole is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)

[//]: # ( vim: set ts=2 et: )

