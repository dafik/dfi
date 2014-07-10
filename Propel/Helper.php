<?php

class Dfi_Propel_Helper
{
    private static $scores = array();

    public static function bulidRelationsOnFK(TableMap $map)
    {
        $map->relationsFK = array();
        /** @var $relation RelationMap */
        foreach ($map->getRelations() as $relation) {
            /** @var $localColumn ColumnMap */
            if (count($relation->getLocalColumns()) > 1) {
                $x = 0;
            }
            $localColumn = $relation->getLocalColumns()[0];
            $map->relationsFK[$localColumn->getColumnName()] = $relation;
        }
    }

    public static function findAutoLabel(TableMap $map)
    {
        if (count(self::$scores) == 0) {
            self::$scores = array(
                'VARCHAR' => 6,

                'LONGVARCHAR' => 5,

                'ENUM' => 4,
                'CHAR' => 4,


                'DATE' => 3,
                'DATETIME' => 3,
                'TIMESTAMP' => 3,
                'TIME' => 3,
                'YEAR' => 3,

                'TEXT' => 2,
                'BLOB' => 2,
                'CLOB' => 2,
                'TINYBLOB' => 2,
                'TINYTEXT' => 2,
                'MEDIUMBLOB' => 2,
                'MEDIUMTEXT' => 2,
                'LONGTEXT' => 2,

                'BOOLEAN' => 1,
                'INT' => 1,
                'INTEGER' => 1,
                'TINYINT' => 1,
                'SMALLINT' => 1,
                'MEDIUMINT' => 1,
                'BIGINT' => 1,
                'FLOAT' => 1,
                'DOUBLE' => 1,
                'DECIMAL' => 1


            );

        }

        /** @var $column ColumnMap */
        $scoreTable = array();
        foreach ($map->getColumns() as $key => $column) {

            if (isset(self::$scores[$column->getType()])) {
                $score = self::$scores[$column->getType()];
            } else {
                $x = 'tablenotfound';
                $score = 0;
            }
            if (!isset($scoreTable[$score])) {
                $scoreTable[$score] = $column;
            }
        }
        $key = max(array_keys($scoreTable));

        $map->autoLabel = $scoreTable[$key];
    }
}