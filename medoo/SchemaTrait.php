<?php

namespace Medoo;

trait SchemaTrait {

    public function getDatabaseSchema() {
        return $this->query("SELECT
            substr(name, POSITION('$this->prefix' IN name) + LENGTH('$this->prefix')) name,
            lower(type) type
            FROM (
                -- TABLAS
                (SELECT
                    information_schema.tables.TABLE_NAME AS 'name',
                    'TABLE' AS 'type',
                    information_schema.tables.TABLE_SCHEMA AS 'db'
                FROM INFORMATION_SCHEMA.tables)

                -- PROCEDURES
                UNION (SELECT
                    information_schema.routines.ROUTINE_NAME AS 'name',
                    information_schema.routines.ROUTINE_TYPE AS 'type',
                    information_schema.routines.ROUTINE_SCHEMA AS 'db'
                FROM INFORMATION_SCHEMA.routines
                WHERE ((information_schema.routines.ROUTINE_TYPE = 'PROCEDURE')))

                -- VISTAS
                UNION (SELECT
                    information_schema.views.TABLE_NAME AS 'name',
                    'VIEW' AS 'type',
                    information_schema.views.TABLE_SCHEMA AS 'db'
                FROM INFORMATION_SCHEMA.views)
            ) AS esquema
            WHERE esquema.db = '$this->databasename'")->fetchAll();
    }

    public function getTableSchema(string $table) {
        return $this->query("SELECT
                `column`,
                `type`,
                `size`,
                `required`
            FROM (
                (SELECT
                    table_schema db,
                    table_name origin,
                    column_name 'column',
                    data_type 'type',
                    CHARACTER_MAXIMUM_LENGTH size,
                    IF (COLUMN_KEY = '', 0, 1) required
                FROM information_schema.COLUMNS)
                UNION ALL (SELECT
                    specific_schema db,
                    SPECIFIC_NAME origin,
                    parameter_name 'column',
                    data_type 'type',
                    CHARACTER_MAXIMUM_LENGTH size,
                    1 required
                FROM information_schema.PARAMETERS
                WHERE ROUTINE_TYPE = 'PROCEDURE')
            ) esquema
            WHERE db = '$this->databasename'
            AND origin = '$this->prefix$table';")->fetchAll();
    }

}
