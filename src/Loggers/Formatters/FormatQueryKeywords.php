<?php

namespace LaravelDoctrine\ORM\Loggers\Formatters;

class FormatQueryKeywords implements QueryFormatter
{
    /**
     * @var QueryFormatter
     */
    protected $formatter;

    /**
     * @var array
     */
    protected $keywords = [
        'select',
        'insert',
        'update',
        'delete',
        'where',
        'set',
        'into',
        'values',
        'from',
        'limit',
        'offset',
        'is',
        'not',
        'null',
        'having',
        'group by',
        'order by',
        'asc',
        'desc',
        'count'
    ];

    /**
     * @param QueryFormatter $formatter
     */
    public function __construct(QueryFormatter $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * @param string     $sql
     * @param array|null $params
     *
     * @return string
     */
    public function format($sql, array $params = null)
    {
        $sql = $this->formatter->format($sql, $params);

        return preg_replace_callback('/\b' . implode('\b|\b', $this->keywords) . '\b/i', function ($match) {
            return strtoupper($match[0]);
        }, $sql);
    }
}
