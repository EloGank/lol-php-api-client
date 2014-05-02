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
     *                               contain the first array level which contain the "success" & "result"/"error" keys.
     */
    public function __construct($host, $port, $format, $throwException = true)
    {
        $this->host           = $host;
        $this->port           = $port;
        $this->format         = $format;
        $this->throwException = $throwException;
        $this->socket         = null;

        $this->formatters = [
            'json' => new JsonFormatter()
        ];
    }

    /**
     * When the instance will be killed
     */
    public function __destruct()
    {
        stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
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
     */
    public function send($region, $route, array $parameters = [], $format = null)
    {
        if (null === $this->socket) {
            $this->socket = stream_socket_client(sprintf('tcp://%s:%d', $this->host, $this->port));
        }

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
} 