# Change Log for Ark-PDO

## 2.x

### 2.0.10

New model class `ArkDatabaseViewModel` for view, a.k.a. the read only table like entity;
New method `fetchByPaging` for one table paging. 

### 2.0.9

Merge = and in

### 2.0.8

String NOT LIKE

### 2.0.7

Select with INDEX!

### 2.0.6
Simple way to reach data again

### 2.0.5
Simple way to reach data

## 1.x

## 1.7.6

Add two kinds of select methods in model.

## 1.7.5

Add NULL support to the modify data parameters.

## 1.7.3

Add the checking package for is null and is empty string.

## 1.7.2

Fix a bug for MySQL `<=>` operator.

## 1.7.1

Support the parentheses in SQL Condition Class.

## 1.7.0

Table Model Architecture Change. 

## 1.6.2

Make Table and Schema Name Public.

### 1.6.1

Fetch Table Max PK with AI.

### 1.6.0

PDO Stream Fetching. 

### 1.5.4

Add back `dbname` to DSN if available.

### 1.5.3

Fix Model ISSET lack.

### 1.5.2 
Fix `selectRowsForCount` of `ArkDatabaseTableModel`.

### 1.5.1

Add `GROUP BY` support to `ArkDatabaseTableModel`;
Add `(NOT) EXISTS` support to `ArkSQLCondition`.

### 1.4

Use version 2 of Ark-Core.

### 1.3

Small Fix and Refactor.

### 1.2.3

Fix a long term bug for IN and NOT IN conditions.

### 1.2.2

Add quick condition generate methods.
Better implode default option.

### 1.2.1

Support for select some filed in table model.
Hot Fix.

### 1.1

Add function `executeInTransaction` for quick use of transaction.

### 1.0

This version is based on PDO functions of Ark version 1.7.1.

