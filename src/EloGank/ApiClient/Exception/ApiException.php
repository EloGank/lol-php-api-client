<?php

/*
 * This file is part of the "EloGank League of Legends API Client" package.
 *
 * https://github.com/EloGank/lol-php-api-client
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EloGank\ApiClient\Exception;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class ApiException extends \RuntimeException
{
    /**
     * @var array
     */
    protected $errorBody;


    /**
     * @param array $errorBody
     */
    public function __construct(array $errorBody)
    {
        $this->errorBody = $errorBody;

        parent::__construct($errorBody['message']);
    }

    /**
     * Return the internal exception class namespace, as string
     *
     * @return string
     */
    public function getCause()
    {
        return $this->errorBody['caused_by'];
    }
} 