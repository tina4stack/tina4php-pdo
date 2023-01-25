<?php

namespace Tina4;

class PDOQuery extends DataConnection implements DataBaseQuery
{


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
                case "dblib":
                    //$limit = " TOP {$noOfRecords} ";
                    //$posSelect = stripos($initialSQL, "select") + strlen("select");
                    //$sql = substr($initialSQL, 0, $posSelect) . $limit . substr($initialSQL, $posSelect);
                case "sqlite":
                    if (stripos($sql, "limit") === false && stripos($sql, "call") === false) {
                        $sql .= " limit {$offSet},{$noOfRecords}";
                    }
                default:
                    if (stripos($sql, "limit") === false && stripos($sql, "call") === false) {
                        $sql .= " limit {$noOfRecords} offset {$offSet}";
                    }
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
            if (stripos($initialSQL, "returning") === false) {
                $sqlCount = "select count(*) as COUNT_RECORDS from ($initialSQL)";
                $recordCount = $this->getDbh()->query($sqlCount);
                $resultCount = $recordCount->fetch();
            } else {
                $resultCount["COUNT_RECORDS"] = count($records); //used for insert into or update
            }
        } else {
            $resultCount["COUNT_RECORDS"] = count($records);
        }

        $error = $this->getConnection()->error();

        return (new DataResult($records, $fields, $resultCount["COUNT_RECORDS"], $offSet, $error));
    }
}