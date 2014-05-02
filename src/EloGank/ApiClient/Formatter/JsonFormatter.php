<?php

/*
 * This file is part of the "EloGank League of Legends API Client" package.
 *
 * https://github.com/EloGank/lol-php-api-client
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EloGank\ApiClient\Formatter;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class JsonFormatter implements FormatterInterface
{
    /**
     * @param string $string
     *
     * @return array
     */
    public function format($string)
    {
        return json_decode($string, true);
    }
} 