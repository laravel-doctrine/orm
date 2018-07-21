<?php

namespace LaravelDoctrine\ORM\Loggers\Formatters;

use Doctrine\DBAL\Platforms\AbstractPlatform;

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
     * @param AbstractPlatform
     * @param string     $sql
     * @param array|null $params
     * @param array|null $types
     *
     * @return string
     */
    public function format($platform, $sql, array $params = null, array $types = null)
    {
        $sql = $this->formatter->format($platform, $sql, $params, $types);

        return preg_replace_callback('/\b' . implode('\b|\b', $this->keywords) . '\b/i', function ($match) {
            return strtoupper($match[0]);
        }, $sql);
    }
}
