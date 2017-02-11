# Добавление комментариев в схему базы данных

Иногда полезно иметь возможность добавить комментарий к таблице или колонке таблицы в базе данных. Этот комментарий сообщит другим разработчикам, что именно хранится в этой колонке или таблице, а также предупредит о каких-то особенностях работы с этими данными. 

Стандарт SQL не предлагает способов сделать это, но отдельные системы баз данных поддерживают свои нестандартные команды для добавления комментариев. 

## MySQL 

В MySQL можно указать комментарий к таблице или полю при создании с помощью слова `COMMENT`: 

```sql
CREATE TABLE example (
    id INT NOT NULL COMMENT 'Это пример комментария к колонке таблицы'
) COMMENT 'Это пример комментария к таблице'
```

Позже комментарий можно изменить командой `ALTER TABLE`:

```sql
ALTER TABLE example COMMENT 'Новый комментарий к таблице';
ALTER TABLE example 
    MODIFY COLUMN id INT NOT NULL COMMENT 'Новый комментарий к полю'
```

Увидеть эти комментарии можно командой `SHOW CREATE TABLE example`, также они могут отображаться в GUI программах вроде phpMyAdmin. 

Мануал: 

- (англ.) https://dev.mysql.com/doc/refman/5.7/en/create-table.html
- (англ.) https://dev.mysql.com/doc/refman/5.7/en/alter-table.html
- (рус., устаревший) http://www.mysql.ru/docs/man/CREATE_TABLE.html

## Postgresql

В Postgresql можно добавлять и изменять комментарии к любым объектам базы данных (таблицы, колонки, индексы) с помощью отдельной команды `COMMENT`:

```sql
COMMENT ON TABLE example IS 'Пример комментария к таблице';
COMMENT ON COLUMN example.id IS 'Пример комментария к колонке таблицы';
```

- (рус., хороший перевод) https://postgrespro.ru/docs/postgrespro/9.6/sql-comment.html
- (англ.) https://www.postgresql.org/docs/current/static/sql-comment.html

## Oracle Database

Oracle использует аналогичный Postgresql синтаксис. 

- (англ.) https://docs.oracle.com/cd/B19306_01/server.102/b14200/statements_4009.htm

## MSSQL 

В MSSQL проще всего добавить комментарий к колонке через GUI программу SQL Server Management Studio. С помощью SQL кода добавлять комментарии довольно неудобно: http://stackoverflow.com/questions/4586842/sql-comments-on-create-table-on-sql-server-2008

