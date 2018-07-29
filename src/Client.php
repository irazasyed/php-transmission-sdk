<?php

namespace Transmission;

use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\Plugin\AuthenticationPlugin;
use Http\Client\Common\Plugin\HeaderDefaultsPlugin;
use Http\Client\Common\Plugin\HistoryPlugin;
use Http\Client\HttpClient;
use Http\Message\Authentication\BasicAuth;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use Transmission\Exception\InvalidArgumentException;
use Transmission\Exception\TransmissionException;
use Transmission\HttpClient\Builder;
use Transmission\HttpClient\Message\ParamBuilder;
use Transmission\HttpClient\Message\ResponseMediator;
use Transmission\HttpClient\Plugin\AuthSession;
use Transmission\HttpClient\Plugin\ExceptionThrower;
use Transmission\HttpClient\Plugin\History;
use Transmission\Models\Torrent;

/**
 * Transmission-RPC API SDK Client
 */
class Client
{
    /**
     * SDK Version
     *
     * @var string
     */
    const VERSION = '1.1.0';

    /**
     * Transmission-RPC Hostname
     *
     * @var string
     */
    public $hostname;

    /**
     * Transmission-RPC Port
     *
     * @var string
     */
    public $port;

    /**
     * Transmission-RPC Path
     *
     * @var string
     */
    public $path = '/transmission/rpc';

    /**
     * @var History
     */
    private $responseHistory;

    /**
     * @var Builder
     */
    private $httpClientBuilder;

    /**
     * Instantiate a new Transmission Client.
     *
     * @param null|string  $hostname
     * @param null|int     $port
     * @param null|string  $username
     * @param null|string  $password
     * @param Builder|null $httpClientBuilder
     */
    public function __construct(
        string $hostname = null,
        int $port = null,
        string $username = null,
        string $password = null,
        Builder $httpClientBuilder = null
    ) {
        $this->hostname = $hostname ?? env('TRANSMISSION_HOSTNAME', '127.0.0.1');
        $this->port = $port ?? env('TRANSMISSION_PORT', 9091);

        $this->responseHistory = new History();
        $this->httpClientBuilder = $httpClientBuilder ?? new Builder();
        $this->httpClientBuilder->addPlugin(new ExceptionThrower());
        $this->httpClientBuilder->addPlugin(new HistoryPlugin($this->responseHistory));
        $this->httpClientBuilder->addPlugin(new HeaderDefaultsPlugin([
            'User-Agent' => $this->defaultUserAgent(),
        ]));

        $username = $username ?? env('TRANSMISSION_USERNAME');
        $password = $password ?? env('TRANSMISSION_PASSWORD', '');

        if (filled($username)) {
            $this->authenticate($username, $password);
        }
    }

    /**
     * Create a Transmission\Client.
     *
     * @param null|string $hostname
     * @param null|int    $port
     * @param null|string $username
     * @param null|string $password
     *
     * @return Client
     */
    public static function create(
        string $hostname = null,
        int $port = null,
        string $username = null,
        string $password = null
    ): self {
        $client = new static($hostname, $port, $username, $password);

        return $client;
    }

    /**
     * Create a Transmission\Client using an HttpClient.
     *
     * @param HttpClient  $httpClient
     * @param null|string $hostname
     * @param null|int    $port
     * @param null|string $username
     * @param null|string $password
     *
     * @return Client
     */
    public static function createWithHttpClient(
        HttpClient $httpClient,
        string $hostname = null,
        int $port = null,
        string $username = null,
        string $password = null
    ): self {
        $builder = new Builder($httpClient);

        return new static($hostname, $port, $username, $password, $builder);
    }

    /**
     * Get Client Instance.
     *
     * @return Client
     */
    public function instance(): self
    {
        return $this;
    }

    /**
     * Authenticate the user for all next requests
     *
     * @param string      $username
     * @param null|string $password
     *
     * @return Client
     */
    public function authenticate(string $username, string $password = ''): self
    {
        $authentication = new BasicAuth($username, $password);

        $this->httpClientBuilder->removePlugin(AuthenticationPlugin::class);
        $this->httpClientBuilder->addPlugin(new AuthenticationPlugin($authentication));

        return $this;
    }

    /**
     * Set Session ID.
     *
     * @param string $sessionId
     *
     * @return Client
     */
    public function setSessionId(string $sessionId): self
    {
        $this->httpClientBuilder->removePlugin(AuthSession::class);
        $this->httpClientBuilder->addPlugin(new AuthSession($sessionId));

        return $this;
    }

    /**
     * Start one or more torrents.
     *
     * @see https://git.io/transmission-rpc-specs Torrent Action Requests.
     *
     * @param mixed $ids One or more torrent ids, sha1 hash strings, or both OR "recently-active", for recently-active
     *                   torrents. All torrents are used if no value is given.
     *
     * @return bool
     */
    public function start($ids = null): bool
    {
        $this->api('torrent-start', compact('ids'));

        return true;
    }

    /**
     * Start Now one or more torrents.
     *
     * @see https://git.io/transmission-rpc-specs Torrent Action Requests.
     *
     * @param mixed $ids One or more torrent ids, as described in 3.1 of specs.
     *
     * @return bool
     */
    public function startNow($ids = null): bool
    {
        $this->api('torrent-start-now', compact('ids'));

        return true;
    }

    /**
     * Stop one or more torrents.
     *
     * @see https://git.io/transmission-rpc-specs Torrent Action Requests.
     *
     * @param mixed $ids One or more torrent ids, as described in 3.1 of specs.
     *
     * @return bool
     */
    public function stop($ids = null): bool
    {
        $this->api('torrent-stop', compact('ids'));

        return true;
    }

    /**
     * Verify one or more torrents.
     *
     * @see https://git.io/transmission-rpc-specs Torrent Action Requests.
     *
     * @param mixed $ids One or more torrent ids, as described in 3.1 of specs.
     *
     * @return bool
     */
    public function verify($ids = null): bool
    {
        $this->api('torrent-verify', compact('ids'));

        return true;
    }

    /**
     * Reannounce one or more torrents.
     *
     * @see https://git.io/transmission-rpc-specs Torrent Action Requests.
     *
     * @param mixed $ids One or more torrent ids, as described in 3.1 of specs.
     *
     * @return bool
     */
    public function reannounce($ids = null): bool
    {
        $this->api('torrent-reannounce', compact('ids'));

        return true;
    }

    /**
     * Set properties of one or more torrents.
     *
     * @see https://git.io/transmission-rpc-specs "torrent-set" for available arguments.
     *
     * @param mixed $ids       One or more torrent ids, as described in 3.1 of specs.
     * @param array $arguments An associative array of arguments to set.
     *
     * @return bool
     */
    public function set($ids, array $arguments): bool
    {
        $arguments['ids'] = $ids;
        $this->api('torrent-set', $arguments);

        return true;
    }

    /**
     * Get All Torrents.
     *
     * @param array|null $fields
     *
     * @return Collection
     */
    public function getAll(array $fields = null): Collection
    {
        return $this->get(null, $fields);
    }

    /**
     * Get information on torrents, if the ids parameter is
     * null all torrents will be returned.
     *
     * @see https://git.io/transmission-rpc-specs "torrent-get" for available fields.
     *
     * @param mixed $ids    One or more torrent ids, as described in 3.1 of specs.
     * @param array $fields An array of return fields, no value will fallback to default fields.
     *
     * @return Collection
     */
    public function get($ids = null, array $fields = null): Collection
    {
        $fields = $fields ?? Torrent::$fields['default'];
        $data = $this->api('torrent-get', compact('ids', 'fields'));

        $torrentsInfo = data_get($data, 'arguments.torrents', 0);

        if (blank($torrentsInfo)) {
            return collect();
        }

        $torrents = collect($torrentsInfo)->mapInto(Torrent::class);

        return $torrents->count() > 1 ? $torrents : $torrents->first();
    }

    /**
     * Add a torrent to the download queue
     *
     * @see https://git.io/transmission-rpc-specs "torrent-add" for available arguments.
     *
     * @param  string  $torrent   Filename/URL of the .torrent file OR Magnet URI/.torrent content.
     * @param  boolean $metainfo  Is given torrent a metainfo? (default: false).
     * @param  string  $savepath  Path to download the torrent to.
     * @param  array   $arguments Other optional arguments.
     *
     * @return Collection
     */
    public function add(
        string $torrent,
        bool $metainfo = false,
        string $savepath = null,
        array $arguments = []
    ): Collection {
        $arguments[$metainfo ? 'metainfo' : 'filename'] = $metainfo ? base64_encode($torrent) : $torrent;

        if ($savepath !== null) {
            $arguments['download-dir'] = (string)$savepath;
        }

        $data = $this->api('torrent-add', $arguments);

        if (array_key_exists('torrent-duplicate', $data['arguments'])) {
            return $this->get($data['arguments']['torrent-duplicate']['id']);
        }

        if (!array_key_exists('torrent-added', $data['arguments'])) {
            throw new InvalidArgumentException($data['result']);
        }

        return collect($data['arguments']['torrent-added']);
    }

    /**
     * Remove one or more torrents.
     *
     * @see https://git.io/transmission-rpc-specs "torrent-remove" for available arguments.
     *
     * @param mixed $ids             One or more torrent ids, as described in 3.1 of specs.
     * @param bool  $deleteLocalData Also remove local data? (default: false).
     *
     * @return bool
     */
    public function remove($ids, bool $deleteLocalData = false): bool
    {
        $arguments = ['ids' => $ids, 'delete-local-data' => $deleteLocalData];
        $this->api('torrent-remove', $arguments);

        return true;
    }

    /**
     * Move one or more torrents to new location.
     *
     * @see https://git.io/transmission-rpc-specs "torrent-set-location" for available arguments.
     *
     * @param mixed  $ids      One or more torrent ids, as described in 3.1 of specs.
     * @param string $location The new torrent location.
     * @param bool   $move     Move from previous location or search "location" for files (default: true).
     *
     * @return bool
     */
    public function move($ids, string $location, bool $move = true): bool
    {
        $this->api('torrent-set-location', compact('ids', 'location', 'move'));

        return true;
    }

    /**
     * Rename a Torrent's Path.
     *
     * @see https://git.io/transmission-rpc-specs "torrent-rename-path" for available arguments.
     *
     * @param mixed  $ids  One torrent id, as described in 3.1 of specs.
     * @param string $path The path to the file or folder that will be renamed.
     * @param string $name The file or folder's new name.
     *
     * @return array
     */
    public function rename($ids, string $path, string $name): array
    {
        return $this->api('torrent-rename-path', compact('ids', 'path', 'name'));
    }

    /**
     * Set the transmission settings.
     *
     * @see https://git.io/transmission-rpc-specs "session-set" for available arguments.
     *
     * @param array $arguments one or more of spec's arguments, except: "blocklist-size",
     *                         "config-dir", "rpc-version", "rpc-version-minimum",
     *                         "version", and "session-id"
     *
     * @return bool
     */
    public function setSettings(array $arguments): bool
    {
        $this->api('session-set', $arguments);

        return true;
    }

    /**
     * Get the transmission settings.
     *
     * @see https://git.io/transmission-rpc-specs "session-get" for available fields.
     *
     * @param array|null $fields
     *
     * @return array
     */
    public function getSettings(array $fields = null): array
    {
        return $this->api('session-get', compact('fields'));
    }

    /**
     * Get Session Stats.
     *
     * @see https://git.io/transmission-rpc-specs "session-stats" for response arguments.
     *
     * @return array
     */
    public function sessionStats(): array
    {
        return $this->api('session-stats');
    }

    /**
     * Trigger Blocklist Update.
     *
     * @see https://git.io/transmission-rpc-specs "blocklist-update" for response arguments.
     *
     * @return array
     */
    public function updateBlocklist(): array
    {
        return $this->api('blocklist-update');
    }

    /**
     * Port Test: See if your incoming peer port is accessible from the outside world.
     *
     * @see https://git.io/transmission-rpc-specs "port-test" for response arguments.
     *
     * @return bool
     */
    public function portTest(): bool
    {
        return $this->api('port-test')['arguments']['port-is-open'];
    }

    /**
     * Shutdown Transmission.
     *
     * @see https://git.io/transmission-rpc-specs "session-close".
     *
     * @return bool
     */
    public function close(): bool
    {
        $this->api('session-close');

        return true;
    }

    /**
     * Move one or more torrents to top in queue.
     *
     * @see https://git.io/transmission-rpc-specs Queue Movement Requests.
     *
     * @param mixed $ids One or more torrent ids, sha1 hash strings, or both OR "recently-active", for recently-active
     *                   torrents. All torrents are used if no value is given.
     *
     * @return bool
     */
    public function queueMoveTop($ids = null): bool
    {
        $this->api('queue-move-top', compact('ids'));

        return true;
    }

    /**
     * Move one or more torrents up in queue.
     *
     * @see https://git.io/transmission-rpc-specs Queue Movement Requests.
     *
     * @param mixed $ids One or more torrent ids, sha1 hash strings, or both OR "recently-active", for recently-active
     *                   torrents. All torrents are used if no value is given.
     *
     * @return bool
     */
    public function queueMoveUp($ids = null): bool
    {
        $this->api('queue-move-top', compact('ids'));

        return true;
    }

    /**
     * Move one or more torrents down in queue.
     *
     * @see https://git.io/transmission-rpc-specs Queue Movement Requests.
     *
     * @param mixed $ids One or more torrent ids, sha1 hash strings, or both OR "recently-active", for recently-active
     *                   torrents. All torrents are used if no value is given.
     *
     * @return bool
     */
    public function queueMoveDown($ids = null): bool
    {
        $this->api('queue-move-down', compact('ids'));

        return true;
    }

    /**
     * Move one or more torrents to bottom in queue.
     *
     * @see https://git.io/transmission-rpc-specs Queue Movement Requests.
     *
     * @param mixed $ids One or more torrent ids, sha1 hash strings, or both OR "recently-active", for recently-active
     *                   torrents. All torrents are used if no value is given.
     *
     * @return bool
     */
    public function queueMoveBottom($ids = null): bool
    {
        $this->api('queue-move-bottom', compact('ids'));

        return true;
    }

    /**
     * Free Space: Tests how much free space is available in a client-specified folder.
     *
     * @see https://git.io/transmission-rpc-specs "free-space" for arguments.
     *
     * @param null|string $path Path to check free space (default: download-dir).
     *
     * @return array
     */
    public function freeSpace(string $path = null): array
    {
        if (blank($path)) {
            $path = $this->getSettings()['arguments']['download-dir'];
        }

        return $this->api('free-space', compact('path'))['arguments'];
    }

    /**
     * Seed Ratio Limit.
     *
     * @return mixed
     */
    public function seedRatioLimit()
    {
        $settings = $this->getSettings(['seedRatioLimited', 'seedRatioLimit'])['arguments'];

        if (isset($settings['seedRatioLimited'])) {
            return $settings['seedRatioLimit'];
        }

        return -1;
    }

    /**
     * Update Download Dir.
     *
     * @param string $downloadDir Path to download torrents.
     *
     * @return bool
     */
    public function updateDownloadDir(string $downloadDir): bool
    {
        $settings = [
            'download-dir' => $downloadDir,
        ];

        return $this->setSettings($settings);
    }

    /**
     * Update & Enable Incomplete Dir.
     *
     * @param string $incompleteDir       Path to store incomplete torrents.
     * @param bool   $enableIncompleteDir Is incomplete dir enabled? (default: true).
     *
     * @return bool
     */
    public function updateIncompleteDir(string $incompleteDir, bool $enableIncompleteDir = true): bool
    {
        $settings = [
            'incomplete-dir-enabled' => $enableIncompleteDir,
            'incomplete-dir'         => $incompleteDir,
        ];

        return $this->setSettings($settings);
    }

    /**
     * Request API.
     *
     * @param string $method
     * @param array  $params
     *
     * @return mixed
     */
    protected function api(string $method, array $params = [])
    {
        $arguments = ParamBuilder::build($params);

        $body = json_encode(compact('method', 'arguments'));

        $response = $this->getHttpClient()
            ->post(
                $this->transmissionUrl(),
                ['Content-Type' => 'application/json'],
                $body
            );

        if (ResponseMediator::isConflictError($response)) {
            $this->findAndSetSessionId($response);

            return $this->api($method, $params);
        }

        return ResponseMediator::getContent($response);
    }

    /**
     * Find and Set Session ID from the response.
     *
     * @param ResponseInterface $response
     *
     * @return Client
     * @throws TransmissionException
     */
    protected function findAndSetSessionId(ResponseInterface $response): self
    {
        $sessionId = $response->getHeaderLine('x-transmission-session-id');

        if (blank($sessionId)) {
            throw new TransmissionException('Unable to retrieve X-Transmission-Session-Id');
        }

        $this->setSessionId($sessionId);

        return $this;
    }

    /**
     * Transmission-RPC API URL.
     *
     * @return string
     */
    protected function transmissionUrl(): string
    {
        return $this->hostname . ':' . $this->port . $this->path;
    }

    /**
     * Default User Agent for all HTTP Requests.
     *
     * @return string HTTP User Agent.
     */
    protected function defaultUserAgent(): string
    {
        return 'PHP-Transmission-SDK/' . self::VERSION;
    }

    /**
     * Get HTTP Client.
     *
     * @return HttpMethodsClient
     */
    public function getHttpClient(): HttpMethodsClient
    {
        return $this->httpClientBuilder->getHttpClient();
    }

    /**
     * @return History
     */
    public function getResponseHistory()
    {
        return $this->responseHistory;
    }

    /**
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        throw new \BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}