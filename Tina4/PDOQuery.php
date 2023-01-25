<?php

namespace Tina4;

class PDOQuery extends DataConnection implements DataBaseQuery
{

    /**
     * @param string $initialSQL
     * @param int $noOfRecords
     * @param int $offSet
     * @return string
     */
    final public function mssql_query(string $initialSQL, int $noOfRecords, int $offSet) : string
    {
        $locateFrom = stripos($initialSQL, "from");
        $fields = substr($initialSQL, strlen("select"),$locateFrom - strlen("select"));

        $locateWhere = strlen($initialSQL);
        if (stripos($initialSQL, " where ") !== false) $locateWhere = stripos($initialSQL, " where ");

        $tables = substr($initialSQL, ($locateFrom + strlen("from")) , $locateWhere - ($locateFrom + strlen("from")));

        $where = "";
       if ($locateWhere <>  strlen($initialSQL)) {
            $endWhere = strlen($initialSQL);
            if (stripos($initialSQL, " group ") !== false) {
                $endWhere = stripos($initialSQL, " group ");
            } elseif (stripos($initialSQL, " order ") !== false) {
                $endWhere = stripos($initialSQL, " order ");
            }
            $where = substr($initialSQL, $locateWhere + strlen(" where "), $endWhere - ($locateWhere + strlen(" where ")));
        }

        $groupBy = "";
        if (stripos($initialSQL, " group by ") !== false) {
            $endGroupBy = strlen($initialSQL);
            if (stripos($initialSQL, " order by ") !== false) {
                $endGroupBy = stripos($initialSQL, " order by");
            }
            $locateGroupBy = stripos($initialSQL, " group by");
            if ($locateGroupBy > 0) $groupBy = substr($initialSQL,$locateGroupBy + strlen(" group by") ,$endGroupBy - ($locateGroupBy + strlen("group by")));
        }

        $orderBy = "";
        if (stripos($initialSQL, " order by ") !== false) {
            $locateOrderBy = stripos($initialSQL, " order by");
            $orderBy = substr($initialSQL, $locateOrderBy + strlen(" order by"), strlen($initialSQL));
        }

        //"SELECT $fields FROM $tables WHERE $where GROUP BY $groupBy ORDER BY $orderBy\n";

        return "select top {$noOfRecords} {$fields} from (
                  select {$fields}, ROW_NUMBER() over (order by {$orderBy}) as r_n_n
              from {$tables} ".(!empty($where) ? " where {$where}": "")."
                         ".(!empty($groupBy) ? " group by {$groupBy}": "")."
            ) a where r_n_n > {$offSet}";
    }

    /**
     * @inheritDoc
     */
    final public function query($sql, int $noOfRecords = 10, int $offSet = 0, array $fieldMapping = []): ?DataResult
    {
        $params = [];
        if (is_array($sql)) {
            $initialSQL = $sql[0];
            unset($sql[0]);
            foreach($sql as $param) {
                $params[] =  $param;
            }

            $sql = $initialSQL;
        } else {
            $initialSQL = $sql;
        }

        if (stripos($initialSQL, "returning") === false) {
            switch ($this->getDbh()->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
                case "firebird":
                    $limit = " first {$noOfRecords} skip {$offSet} ";
                    $posSelect = stripos($initialSQL, "select") + strlen("select");
                    $sql = substr($initialSQL, 0, $posSelect) . $limit . substr($initialSQL, $posSelect);
                    //select top 10 * from table
                    break;
                case "dblib":
                    if (stripos($initialSQL, "select")) $sql = $this->mssql_query($initialSQL, $noOfRecords, $offSet);
                    break;
                case "sqlite":
                    if (stripos($sql, "limit") === false && stripos($sql, "call") === false) {
                        $sql .= " limit {$offSet},{$noOfRecords}";
                    }
                    break;
                default:
                    if (stripos($sql, "limit") === false && stripos($sql, "call") === false) {
                        $sql .= " limit {$noOfRecords} offset {$offSet}";
                    }
                    break;
            }

        }

        if (is_array($params)) {
            $recordCursor = $this->getDbh()->prepare($sql);
            $recordCursor->execute($params);
        } else {
            $recordCursor = $this->getDbh()->query($sql);
        }

        //populate the fields
        $fieldTypes = [
            \PDO::PARAM_BOOL => 'BOOL',
            \PDO::PARAM_NULL => 'NULL',
            \PDO::PARAM_INT  => 'INTEGER',
            \PDO::PARAM_STR  => 'VARCHAR',
            \PDO::PARAM_LOB  => 'BLOB',
            \PDO::PARAM_STMT => 'STATEMENT'  //Not used right now
        ];

        $fields = [];
        $colCount = $recordCursor->columnCount();
        for ($fid = 0; $fid < $colCount; $fid++) {
            $fieldInfo = $recordCursor->getColumnMeta($fid);

            $fields[] = (new DataField(
                $fid,
                $fieldInfo["name"],
                $fieldInfo["name"],
                $fieldTypes[$fieldInfo["pdo_type"]],
                $fieldInfo["len"]
            ));

            $fid++;
        }

        $records = [];
        if (!empty($recordCursor)) {
            while ($record = $recordCursor->fetch()) {
                $records[] = (new DataRecord(
                    $record,
                    $fieldMapping,
                    $this->getConnection()->getDefaultDatabaseDateFormat(),
                    $this->getConnection()->dateFormat
                ));
            }
        }

        if (is_array($records) && count($records) > 1) {
            if (stripos($initialSQL, "@") !== false || stripos($initialSQL, "sp_") !== false) {
                $resultCount["COUNT_RECORDS"] = count($records);
            } else {
                if (stripos($initialSQL, "returning") === false) {
                    $initialSQL = explode("order", strtolower($initialSQL))[0];

                    $sqlCount = "select count(*) as COUNT_RECORDS from ($initialSQL) as t";

                    if (is_array($params)) {
                        $recordCount = $this->getDbh()->prepare($sqlCount);
                        $recordCount->execute($params);
                    } else {
                        $recordCount = $this->getDbh()->query($sqlCount);
                    }

                    $resultCount = $recordCount->fetch();
                } else {
                    $resultCount["COUNT_RECORDS"] = count($records); //used for insert into or update
                }
            }
        } else {
            $resultCount["COUNT_RECORDS"] = count($records);
        }

        $error = $this->getConnection()->error();

        return (new DataResult($records, $fields, $resultCount["COUNT_RECORDS"], $offSet, $error));
    }
}