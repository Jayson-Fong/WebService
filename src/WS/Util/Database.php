<?php

namespace WS\Util;

use Medoo\Medoo;

class Database extends Medoo
{

    public function selectSingle(string $table, $join, $columns = null, $where = null)
    {
        $result = $this->select($table, $join, $columns, $where);
        if (\count($result) > 0)
        {
            return $result[0];
        }

        return $result;
    }

}