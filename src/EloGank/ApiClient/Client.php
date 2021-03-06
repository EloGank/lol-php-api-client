<?php

/*
 * This file is part of the "EloGank League of Legends API Client" package.
 *
 * https://github.com/EloGank/lol-php-api-client
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EloGank\ApiClient;

use EloGank\ApiClient\Exception\ApiException;
use EloGank\ApiClient\Exception\ConnectionException;
use EloGank\ApiClient\Formatter\Exception\UnknownFormatterException;
use EloGank\ApiClient\Formatter\FormatterInterface;
use EloGank\ApiClient\Formatter\JsonFormatter;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class Client
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $format;

    /**
     * @var bool
     */
    protected $throwException;

    /**
     * @var float
     */
    protected $timeout;

    /**
     * @var FormatterInterface[]
     */
    protected $formatters;

    /**
     * @var resource
     */
    protected $socket;


    /**
     * @param string $host           The server host
     * @param int    $port           The server port
     * @param string $format         The default output format
     * @param bool   $throwException If true, an ApiException will be throw on error and the response won't<br />
     *                               contain the first array level which contain "success" & "result"/"error" keys.
     * @param float  $timeout        The socket connection timeout, in second
     */
    public function __construct($host, $port, $format, $throwException = true, $timeout = null)
    {
        $this->host           = $host;
        $this->port           = $port;
        $this->format         = $format;
        $this->throwException = $throwException;
        $this->timeout        = $timeout;
        $this->socket         = null;

        if (null == $timeout) {
            $this->timeout = ini_get('default_socket_timeout');
        }

        $this->formatters = [
            'json' => new JsonFormatter()
        ];
    }

    /**
     * @param string      $region     The region shot name (EUW, NA, ...)
     * @param string      $route      The route, see the documentation for the route list
     * @param array       $parameters The route parameters
     * @param string|null $format     The output format, if null the default format will be used
     *
     * @return array
     *
     * @throws ApiException
     * @throws UnknownFormatterException
     * @throws ConnectionException
     */
    public function send($region, $route, array $parameters = [], $format = null)
    {
        $this->socket = @stream_socket_client(sprintf('tcp://%s:%d', $this->host, $this->port), $errno, $errstr, $this->timeout);
        if (0 < $errno) {
            throw new ConnectionException($errstr . ' (code: ' . $errno . ')');
        }

        stream_set_timeout($this->socket, $this->timeout);

        $data = [
            'region'     => $region,
            'route'      => $route,
            'parameters' => $parameters
        ];

        if (null != $format) {
            $data['format'] = $format;
        }

        fwrite($this->socket, json_encode($data));

        $results = fgets($this->socket);
        if (null != $this->socket) {
            stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
            $this->socket = null;
        }

        if (false === $results) {
            throw new ConnectionException('API timed out, the client will restart, please retry in a few seconds');
        }

        if (null == $format) {
            $format = $this->format;
        }

        if (!isset($this->formatters[$format])) {
            throw new UnknownFormatterException('Unknown formatter for format "' . $format . '"');
        }

        $response = $this->formatters[$format]->format($results);
        if (true !== $response['success']) {
            if (true === $this->throwException) {
                throw new ApiException($response['error']);
            }

            return $response;
        }

        if (true === $this->throwException) {
            return $response['result'];
        }

        return $response;
    }

    /**
     * @param float $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }
}
