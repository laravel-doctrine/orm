<?php

namespace LaravelDoctrine\ORM\Loggers\Formatters;

use DateTimeInterface;
use Exception;
use function is_array;

class ReplaceQueryParams implements QueryFormatter
{
    /**
     * @param string     $sql
     * @param array|null $params
     *
     * @return string
     */
    public function format($sql, array $params = null)
    {
        if (is_array($params)) {
            foreach ($params as $param) {
                $param = $this->convertParam($param);
                $sql   = preg_replace('/\?/', "$param", $sql, 1);
            }
        }

        return $sql;
    }

    /**
     * @param mixed $param
     *
     * @throws Exception
     * @return string
     */
    protected function convertParam($param)
    {
        if (is_object($param)) {
            if (!method_exists($param, '__toString')) {
                if ($param instanceof DateTimeInterface) {
                    $param = $param->format('Y-m-d H:i:s');
                } else {
                    throw new Exception('Given query param is an instance of ' . get_class($param) . ' and could not be converted to a string');
                }
            }
        } elseif (is_array($param)) {
            if ($this->isNestedArray($param)) {
                $param = json_encode($param, JSON_UNESCAPED_UNICODE);
            } else {
                $param = implode(
                    ', ',
                    array_map(
                        function ($part) {
                            return '"' . (string) $part . '"';
                        },
                        $param
                    )
                );

                return '(' . $param . ')';
            }
        } else {
            $param = e($param);
        }

        return '"' . (string) $param . '"';
    }

    /**
     * @param  array $array
     * @return bool
     */
    private function isNestedArray(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                return true;
            }
        }

        return false;
    }
}
