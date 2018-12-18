..  vim: set ts=4 sw=4 tw=79 :

************
Introduction
************

==================
About this Version
==================

This is the Doctrine |version| ORM Manual, covering up to version
|release|.

This manual is currently being "transliterated" to use reStructuredText. This
will allow it to be built with Sphinx, hosted by readthedocs and hopefully
integrated into the new official Doctrine documentation server. Chapters up to
and including :doc:`working-with-models` should display properly, but the rest
of the manual still hasn't been fully rewritten. This can make code examples
and tables hard to read.

.. caution::

    The text in this book contains lots of PHP code
    examples. All starting and ending PHP tags have been removed to
    reduce the length of the book. Be sure to include the PHP tags when
    you copy and paste the examples.

=================
How to Contribute
=================

If you'd like to help convert the manual to reST (particularly if you can read
Japanese), you can fork and send pull requests via
http://github.com/dominics/doctrine1-documentation. It might be a good idea to
open an issue if you're going to work on a page, so that work isn't duplicated.

Eventually, this version will hopefully end up back in
http://github.com/doctrine/doctrine1-documentation

=================
What is Doctrine?
=================

Doctrine is an object relational mapper (ORM) for PHP 5.2.3+ that sits
on top of a powerful database abstraction layer (DBAL). One of its key
features is the option to write database queries in a proprietary object
oriented SQL dialect called Doctrine Query Language (DQL), inspired by
Hibernates HQL. This provides developers with a powerful alternative to
SQL that maintains flexibility without requiring unnecessary code
duplication.

===============
What is an ORM?
===============

Object relational mapping is a technique used in programming languages
when dealing with databases for translating incompatible data types in
relational databases. This essentially allows for us to have a "virtual
object database," that can be used from the programming language. Lots
of free and commercial packages exist that allow this but sometimes
developers chose to create their own ORM.

====================
What is the Problem?
====================

We are faced with many problems when building web applications. Instead
of trying to explain it all it is best to read what Wikipedia has to say
about object relational mappers.

    Data management tasks in object-oriented (OO) programming are typically
    implemented by manipulating objects, which are almost always non-scalar
    values. For example, consider an address book entry that represents a
    single person along with zero or more phone numbers and zero or more
    addresses. This could be modeled in an object-oriented implementation by
    a "person object" with "slots" to hold the data that comprise the entry:
    the person's name, a list (or array) of phone numbers, and a list of
    addresses. The list of phone numbers would itself contain "phone number
    objects" and so on. The address book entry is treated as a single value
    by the programming language (it can be referenced by a single variable,
    for instance). Various methods can be associated with the object, such
    as a method to return the preferred phone number, the home address, and
    so on.

    However, many popular database products such as SQL DBMS can only store
    and manipulate scalar values such as integers and strings organized
    within tables.

    The programmer must either convert the object values into groups of
    simpler values for storage in the database (and convert them back upon
    retrieval), or only use simple scalar values within the program.
    Object-relational mapping is used to implement the first approach.

    The height of the problem is translating those objects to forms that can
    be stored in the database for easy retrieval, while preserving the
    properties of the objects and their relationships; these objects are
    then said to be persistent.

    -- Pulled from `Wikipedia <http://en.wikipedia.org/wiki/Object-relational_mapping>`_

====================
Minimum Requirements
====================

Doctrine requires PHP >= 5.2.3+, although it doesn't require any
external libraries. For database function call abstraction Doctrine uses
PDO which comes bundled with the PHP official release that you get from
www.php.net.

.. note::

    If you use a 3 in 1 package under windows like Uniform
    Server, MAMP or any other non-official package, you may be required
    to perform additional configurations.

==============
Basic Overview
==============

Doctrine is a tool for object-relational mapping in PHP. It sits on top
of PDO and is itself divided into two main layers, the DBAL and the ORM.
The picture below shows how the layers of Doctrine work together.

.. image:: /_static/images/doctrine-layers.jpg
   :alt: Doctrine Layers: The ORM relies on the DBAL, which relies on PDO

The DBAL (Database Abstraction Layer) completes and extends the basic
database abstraction/independence that is already provided by PDO. The
DBAL library can be used standalone, if all you want is a powerful
database abstraction layer on top of PDO. The ORM layer depends on the
DBAL and therefore, when you load the ORM package the DBAL is already
included.

==================
Doctrine Explained
==================

The following section tries to explain where Doctrine stands in the world of
ORM tools. The Doctrine ORM is mainly built around the `Active Record
<http://www.martinfowler.com/eaaCatalog/activeRecord.html>`_, `Data Mapper
<http://www.martinfowler.com/eaaCatalog/dataMapper.html>`_ and `Data Mapping
<http://www.martinfowler.com/eaaCatalog/metadataMapping.html Meta>`_ patterns.

Through extending a specific base class named :php:class:`Doctrine_Record`, all
the child classes get the typical ActiveRecord interface (save/delete/etc.) and
it allows Doctrine to easily participate in and monitor the lifecycles of your
records. The real work, however, is mostly forwarded to other components, like
the :php:class:`Doctrine_Table` class. This class has the typical Data Mapper
interface, :php:meth:`createQuery`, :php:meth:`find(id)`, :php:meth:`findAll`,
:php:meth:`findBy*`, :php:meth:`findOneBy*` etc. So the ActiveRecord base class
enables Doctrine to manage your records and provides them with the typical
ActiveRecord interface whilst the mapping footwork is done elsewhere.

The ActiveRecord approach comes with its typical limitations. The most obvious
is the enforcement for a class to extend a specific base class in order to be
persistent (a :php:class:`Doctrine_Record`). In general, the design of your
domain model is pretty much restricted by the design of your relational model.
There is an exception though. When dealing with inheritance structures,
Doctrine provides some sophisticated mapping strategies which allow your domain
model to diverge a bit from the relational model and therefore give you a bit
more freedom.

Doctrine is in a continuous development process and we always try to add new
features that provide more freedom in the modeling of the domain.  However, as
long as Doctrine remains mainly an ActiveRecord approach, there will always be
a pretty large, (forced) similarity of these two models.

The current situation is depicted in the following picture.

.. image:: /_static/images/relational-bounds.jpg
   :alt: The Relational Model and Object Model are distinct, but mostly overlap.

As you see in the picture, the domain model can't drift far away from the bounds
of the relational model.

After mentioning these drawbacks, it's time to mention some advantages of the
ActiveRecord approach. Apart from the (arguably slightly) simpler programming
model, it turns out that the strong similarity of the relational model and the
Object Oriented (OO) domain model also has an advantage: It makes it relatively
easy to provide powerful generation tools, that can create a basic domain model
out of an existing relational schema. Further, as the domain model can't drift
far from the relational model due to the reasons above, such generation and
synchronization tools can easily be used throughout the development process.
Such tools are one of Doctrine's strengths.

We think that these limitations of the ActiveRecord approach are not that much
of a problem for the majority of web applications because the complexity of the
business domains is often moderate, but we also admit that the ActiveRecord
approach is certainly not suited for complex business logic (which is often
approached using Domain-Driven Design) as it simply puts too many restrictions
and has too much influence on your domain model.

Doctrine is a great tool to drive the persistence of simple or moderately
complex domain models [#domain_complexity]_ and you may even find that it's a good choice for
complex domain models if you consider the trade-off between making your domain
model more database-centric and implementing all the mapping on your own
(because at the time of this writing we are not aware of any powerful ORM tools
for PHP that are not based on an ActiveRecord approach).

Now you already know a lot about what Doctrine is and what it is not. If you
would like to dive in now and get started right away, jump straight to the next
chapter :doc:`getting-started`.

.. rubric:: Notes

.. [#domain_complexity] Complexity != Size. A domain model can be pretty large without being
    complex and vice versa. Obviously, larger domain models have a greater
    probability of being complex.

============
Key Concepts
============

The Doctrine Query Language (DQL) is an object query language. It let's
you express queries for single objects or full object graphs, using the
terminology of your domain model: class names, field names, relations
between classes, etc. This is a powerful tool for retrieving or even
manipulating objects without breaking the separation of the domain model
(field names, class names, etc) from the relational model (table names,
column names, etc). DQL looks very much like SQL and this is intended
because it makes it relatively easy to grasp for people knowing SQL.
There are, however, a few very important differences you should always
keep in mind:

Take this example DQL query:

 FROM User u LEFT JOIN u.Phonenumbers where u.level > 1

The things to notice about this query:

-  We select from **classes** and not **tables**. We are selecting from
   the :php:class:`User` class/model.
-  We join along **associations** (u.Phonenumbers)
-  We can reference **fields** (u.level)
-  There is no join condition (ON x.y = y.x). The associations between
   your classes and how these are expressed in the database are known to
   Doctrine (You need to make this mapping known to Doctrine, of course.
   How to do that is explained later in the :doc:`defining-models`
   chapter.).

.. note::

    DQL expresses a query in the terms of your domain model
    (your classes, the attributes they have, the relations they have to
    other classes, etc.).

It's very important that we speak about classes, fields and associations
between classes here. :php:class:`User` is **not** a table / table name . It may
be that the name of the database table that the :php:class:`User` class is mapped
to is indeed named :php:class:`User` but you should nevertheless adhere to this
differentiation of terminology. This may sound nit picky since, due to
the ActiveRecord approach, your relational model is often very similar
to your domain model but it's really important. The column names are
rarely the same as the field names and as soon as inheritance is
involved, the relational model starts to diverge from the domain model.
You can have a class :php:class:`User` that is in fact mapped to several tables
in the database. At this point it should be clear that talking about
"selecting from the :php:class:`User` table" is simply wrong then. And as
Doctrine development continues there will be more features available
that allow the two models to diverge even more.

===============
Further Reading
===============

For people new to object-relational mapping and (object-oriented) domain
models we recommend the following literature:

The `books by Martin Fowler <http://www.martinfowler.com/books.html>`_
cover a lot of the basic ORM terminology, the different approaches of
modeling business logic and the patterns involved.

Another good read is about `Driven Design <http://domaindrivendesign.org/books/#DDD
Domain>`_. Though serious Domain-Driven Design is currently
not possible with Doctrine, this is an excellent resource for good
domain modeling, especially in complex business domains, and the
terminology around domain models that is pretty widespread nowadays is
explained in depth (Entities, Value Objects, Repositories, etc).

==========
Conclusion
==========

Well, now that we have given a little educational reading about the
methodologies and principals behind Doctrine we are pretty much ready to
dive in to everything that is Doctrine. Lets dive in to setting up
Doctrine in the :doc:`getting-started` chapter.
