<?php

namespace Transmission\Models;

use Transmission\Client;

/**
 * Torrent
 *
 * @method mixed getLeftUntilDone(bool $castingEnabled = false) Get Left Until Done Value.
 * @method mixed getHaveValid(bool $castingEnabled = false) Get Have Valid Value.
 * @method mixed getHaveUnchecked(bool $castingEnabled = false) Get Have Unchecked Value.
 */
class Torrent extends AbstractModel
{
    /**
     * Statuses.
     *
     * Field: status
     */
    const STATUS_STOPPED = 0;
    const STATUS_CHECK_WAIT = 1;
    const STATUS_CHECK = 2;
    const STATUS_DOWNLOAD_WAIT = 3;
    const STATUS_DOWNLOAD = 4;
    const STATUS_SEED_WAIT = 5;
    const STATUS_SEED = 6;
    const STATUS_ISOLATED = 7;

    /**
     * Seed Ratio Modes.
     *
     * Field: seedRatioMode
     */
    const RATIO_USE_GLOBAL = 0;
    const RATIO_USE_LOCAL = 1;
    const RATIO_UNLIMITED = 2;

    /**
     * Errors.
     *
     * Field: error
     */
    const ERROR_NONE = 0;
    const ERROR_TRACKER_WARNING = 1;
    const ERROR_TRACKER_ERROR = 2;
    const ERROR_LOCAL_ERROR = 3;

    /**
     * Tracker Stats.
     *
     * Field: trackerStats->announceState
     */
    const TRACKER_INACTIVE = 0;
    const TRACKER_WAITING = 1;
    const TRACKER_QUEUED = 2;
    const TRACKER_ACTIVE = 3;

    /**
     * Common Fields.
     *
     * @var array
     */
    public static $fields = [
        'default'    => [
            'id',
            'eta',
            'name',
            'status',
            'isFinished',
            'files',
            'hashString',
            'downloadDir',
            'percentDone',
            'haveValid',
            'haveUnchecked',
            'totalSize',
            'leftUntilDone',
            'addedDate',
            'doneDate',
            'activityDate',
        ],
        'stats'      => [
            'error',
            'errorString',
            'eta',
            'isFinished',
            'isStalled',
            'leftUntilDone',
            'metadataPercentComplete',
            'peersConnected',
            'peersGettingFromUs',
            'peersSendingToUs',
            'percentDone',
            'queuePosition',
            'rateDownload',
            'rateUpload',
            'recheckProgress',
            'seedRatioMode',
            'seedRatioLimit',
            'sizeWhenDone',
            'status',
            'trackers',
            'downloadDir',
            'uploadedEver',
            'uploadRatio',
            'webseedsSendingToUs',
        ],
        'statsExtra' => [
            'activityDate',
            'corruptEver',
            'desiredAvailable',
            'downloadedEver',
            'fileStats',
            'haveUnchecked',
            'haveValid',
            'peers',
            'startDate',
            'trackerStats',
        ],
        'infoExtra'  => [
            'comment',
            'creator',
            'dateCreated',
            'files',
            'hashString',
            'isPrivate',
            'pieceCount',
            'pieceSize',
        ],
    ];

    /**
     * The attributes that should be cast to native and other supported types.
     *
     * Casts only when formatting is enabled.
     *
     * @var array
     */
    protected $casts = [
        'doneDate'      => 'datetime',
        'startDate'     => 'datetime',
        'activityDate'  => 'datetime',
        'addedDate'     => 'datetime',
        'dateCreated'   => 'datetime',
        'haveValid'     => 'bytes',
        'haveUnchecked' => 'bytes',
        'totalDone'     => 'bytes', // Custom
        'leftUntilDone' => 'bytes',
        'totalSize'     => 'bytes',
        'sizeWhenDone'  => 'bytes',
    ];

    /**
     * Get Name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->get('name', 'Unknown');
    }

    /**
     * Get Percent Done.
     *
     * @param bool $format
     *
     * @return int
     */
    public function getPercentDone($format = false): int
    {
        $percentDone = $this->get('percentDone', 0);

        return $format ? $percentDone * 100 : $percentDone;
    }

    /**
     * Get Percent Done String.
     *
     * @return string
     */
    public function getPercentDoneString(): string
    {
        return $this->getPercentDone(true) . '%';
    }

    /**
     * Get Total Done.
     *
     * @param null|bool $castingEnabled
     *
     * @return mixed
     */
    public function getTotalDone($castingEnabled = null)
    {
        $value = $this->getHaveValid(false) + $this->getHaveUnchecked(false);

        return $this->castAttribute('totalDone', $value, $castingEnabled ?? $this->castingEnabled);
    }

    /**
     * Get File Count.
     *
     * @return mixed
     */
    public function getFileCount()
    {
        return count($this->get('files', 0));
    }

    /**
     * Get a File by ID.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function getFile(int $id)
    {
        return data_get($this->items, "files.$id");
    }

    /**
     * Check if status is stopped.
     *
     * @return bool
     */
    public function isStopped(): bool
    {
        return $this->isStatus(static::STATUS_STOPPED);
    }

    /**
     * Check if status is checking.
     *
     * @return bool
     */
    public function isChecking(): bool
    {
        return $this->isStatus(static::STATUS_CHECK);
    }

    /**
     * Check if status is downloading.
     *
     * @return bool
     */
    public function isDownloading(): bool
    {
        return $this->isStatus(static::STATUS_DOWNLOAD);
    }

    /**
     * Check if status is queued.
     *
     * @return bool
     */
    public function isQueued(): bool
    {
        return $this->isStatus(static::STATUS_DOWNLOAD_WAIT) || $this->isStatus(static::STATUS_SEED_WAIT);
    }

    /**
     * Check if status is seeding.
     *
     * @return bool
     */
    public function isSeeding(): bool
    {
        return $this->isStatus(static::STATUS_SEED);
    }

    /**
     * Check if done downloading.
     *
     * @return bool
     */
    public function isDone(): bool
    {
        return $this->getLeftUntilDone(false) < 1;
    }

    /**
     * Check if given status matches the current status.
     *
     * @param $status
     *
     * @return bool
     */
    public function isStatus($status): bool
    {
        return $this->get('status') === $status;
    }

    /**
     * Get Status String.
     *
     * @return string
     */
    public function getStatusString()
    {
        switch ($this->get('status')) {
            case static::STATUS_STOPPED:
                return $this->get('isFinished', false) ? 'Seeding complete' : 'Paused';
            case static::STATUS_CHECK_WAIT:
                return 'Queued for verification';
            case static::STATUS_CHECK:
                return 'Verifying local data';
            case static::STATUS_DOWNLOAD_WAIT:
                return 'Queued for download';
            case static::STATUS_DOWNLOAD:
                return 'Downloading';
            case static::STATUS_SEED_WAIT:
                return 'Queued for seeding';
            case static::STATUS_SEED:
                return 'Seeding';
            case null:
                return 'Unknown';
            default:
                return 'Error';

        }
    }

    /**
     * Get Seed Ratio Limit.
     *
     * @param Client $client
     *
     * @return int|string
     */
    public function seedRatioLimit(Client $client)
    {
        switch ($this->get('seedRatioMode')) {
            case static::RATIO_USE_GLOBAL:
                return $client->seedRatioLimit();
            case static::RATIO_USE_LOCAL:
                return $this->get('seedRatioLimit');
            default:
                return -1;
        }
    }

    /**
     * Get Error Message.
     *
     * @return null|string
     */
    public function getErrorMessage()
    {
        $str = $this->get('errorString');
        switch ($this->get('error')) {
            case static::ERROR_TRACKER_WARNING:
                return 'Tracker returned a warning: ' . $str;
            case static::ERROR_TRACKER_ERROR:
                return 'Tracker returned an error: ' . $str;
            case static::ERROR_LOCAL_ERROR:
                return 'Error: ' . $str;
            default:
                return null;
        }
    }
}