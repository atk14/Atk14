TableRecord
===========

[![Tests](https://github.com/atk14/TableRecord/actions/workflows/tests.yml/badge.svg)](https://github.com/atk14/TableRecord/actions/workflows/tests.yml)

TableRecord is a small, straightforward ORM library for mapping database table rows onto PHP objects, originally designed for (and used throughout) the [ATK14](http://www.atk14.net/) framework, but usable standalone as well.

Basic usage
-----------

#### Finding records

    $finder = Book::Finder([
      "conditions" => ["title LIKE :title"],
      "bind_ar" => [":title" => "%Goodies%"],
      "order_by" => "title",
      "limit" => 10,
      "offset" => 0,
    ]);

    $finder->getTotalAmount(); // total number of matching records, regardless of limit/offset

    foreach($finder as $book){
      echo $book->getTitle()."\n";
    }

#### Getting a record by id

    $book = Book::GetInstanceById(123); // a single TableRecord, or null when not found
    $books = Book::GetInstanceById([123,124,125]); // array in, array out, in the same order

    // keys of an associative array are preserved in the result, null entries can be dropped
    $books = Book::GetInstanceById(["a" => 123,"b" => 124],["omit_nulls" => true]);

#### FindFirst() and FindAll()

Short of constructing a full `Finder`, `FindFirst()`/`FindAll()` look up records directly:

    $book = Book::FindFirst(["conditions" => ["published" => true], "order_by" => "created_at DESC"]);
    $books = Book::FindAll(["conditions" => ["published" => true], "order_by" => "created_at DESC", "limit" => 20]);

    // multiple condition fragments with bound parameters, including LIKE and IN
    $books = Book::FindAll([
      "conditions" => ["author_id=:author_id","title LIKE :q","year_of_publication IN :years"],
      "bind_ar" => [":author_id" => $author_id,":q" => "%$q%",":years" => [2020,2021,2022]],
    ]);

    // shortcut for a single field/value condition
    $book = Book::FindFirst("title","Goodies");
    $books = Book::FindAll("author_id",123);

    // multiple conditions as sequential field/value pairs, plus trailing options
    $book = Book::FindFirst("title","Goodies","author_id",123,["order_by" => "created_at DESC"]);

#### Magic finders

`FindBy<Column>()` (and its `FindFirstBy<Column>()`/`FindAllBy<Column>()` siblings) are shortcuts for a single-field `FindFirst()`/`FindAll()` call, with `<Column>` camelCased:

    $book = Book::FindByTitle("Goodies"); // same as Book::FindFirst("title","Goodies")
    $book = Book::FindFirstByTitle("Goodies"); // same as FindByTitle()
    $books = Book::FindAllByAuthorId(123); // same as Book::FindAll("author_id",123)

Only a single column is supported this way -- `FindByTitleAndAuthorId(...)` is **not** a compound condition; it tries (and fails) to query a column literally named `title_and_author_id`. For more than one condition, call `FindFirst()`/`FindAll()` directly with multiple field/value pairs, as shown above.

#### Iterating over a large number of records

For a finder matching (and processing) a large number of records, use `iterateRecords()` instead of `getRecords()`/`foreach($finder as ...)`. It returns a generator and reads records in bounded batches, so the whole matched resultset never has to be held in memory at once.

    $finder = Book::Finder(["limit" => null]); // no limit -> finds all the records

    foreach($finder->iterateRecords() as $book){
      // $book is read from the database without all the other books being loaded into memory at once
    }

The `batch_size` option controls how many records are kept resident (and, when the finder uses the cache, identity-mapped) at a time. It's a memory/query-count tradeoff, not a hard cap on a single query's size: both the cache and non-cache paths already split any individual read into `TABLERECORD_MAX_NUMBER_OF_RECORDS_READ_AT_ONCE`-sized SQL queries regardless of `batch_size`. A bigger `batch_size` keeps more records resident simultaneously, so code that cross-references other records of the same class while processing one (e.g. a related/accessory lookup) is more likely to find them already cached instead of triggering an extra query -- at the cost of proportionally higher peak memory.

    foreach($finder->iterateRecords(["batch_size" => 500]) as $book){
      // process $book
    }

#### Caching

Records can be read through a shared, per-class identity map instead of being instantiated directly, so that the same id is never read from the database more than once in a single request:

    Cache::Prepare("Book",[123,124,125]); // declares ids about to be needed, no query happens yet
    $book = Cache::Get("Book",123); // reads all previously prepared ids in one query, returns #123
    $book = Cache::Get("Book",123); // served straight from the cache, no query

    Cache::Clear(); // flushes every object in cache, for every class
    Cache::Clear("Book"); // flushes every Book instance, including any still-unread Cache::Prepare() queue
    Cache::Clear("Book",123); // flushes just Book#123

`Cache::Unprepare()` cancels ids previously declared via `Cache::Prepare()` without reading them, leaving any already resolved (cached) records alone -- unlike `Cache::Clear()`, it doesn't need to know which ids are queued:

    Cache::Unprepare("Book"); // forgets everything queued for Book, nothing is read
    Cache::Unprepare("Book",123); // forgets just id 123, other queued ids (if any) stay queued

#### Lister -- ordered many-to-many associations

While `Finder` runs a database query, `Lister` manages a sortable *M:N* association through a plain join table, keyed by an explicit `rank` column -- e.g. the authors of a book, in a deliberately chosen order.

    $lister = $book->getLister("Authors");

    $lister->append($bob); // added at the end
    $lister->prepend($bill); // added at the beginning
    $lister->contains($bob); // true
    $lister->getRecords(); // [$bill,$bob], ordered by rank
    $lister->setRecordRank($bob,0); // reorder: $bob becomes first
    $lister->remove($bill);

`getLister($subjects)` derives the join table and its columns from the owner's and the subject's class names -- for `$book->getLister("Authors")` that's a `book_authors` table with `book_id`, `author_id` and `rank` columns (all overridable via a second `$options` argument, see `TableRecord_Lister::__construct()`). No special implementation is needed on the `Author` model itself.

Installation
------------

Just use the Composer:

    composer require atk14/table_record

Testing
-------

Install required dependencies for development:

    composer update --dev

Run tests:

    cd test
    ../vendor/bin/run_unit_tests

License
-------

TableRecord is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)

[//]: # ( vim: set ts=2 et: )

