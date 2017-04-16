<?

namespace Dfi\DataTable\Helper;

use ModelCriteria;


class Options
{

    public static function get($model, $keyField, $labelField, $query = false, $with = false)
    {

        $model = $model . 'Query';
        /** @var ModelCriteria $qry */
        /** @noinspection PhpUndefinedMethodInspection */
        $qry = $model::create();

        if ($query) {
            /** @noinspection PhpParamsInspection */
            $qry->mergeWith($query);
        }
        if ($labelField) {
            $qry->orderBy($labelField);
            $qry->select(array($keyField, $labelField));
        } elseif ($with) {
            $labelField = 'alias';
            $qry->withColumn($with, $labelField);
            $qry->select($keyField);
        }
        $results = $qry->find()->toArray();

        $opt = [];
        foreach ($results as $row) {
            $opt[$row[$keyField]] = $row[$labelField];
        }

        return $opt;

    }

}