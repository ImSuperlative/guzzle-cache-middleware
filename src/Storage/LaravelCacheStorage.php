<?php

namespace Kevinrob\GuzzleCache\Storage;

use Illuminate\Contracts\Cache\Repository as Cache;
use Kevinrob\GuzzleCache\CacheEntry;

class LaravelCacheStorage implements CacheStorageInterface
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($key)
    {
        try {
            $cache = unserialize($this->cache->get($key));
            if ($cache instanceof CacheEntry) {
                return $cache;
            }
        } catch (\Exception $ignored) {
            return;
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function save($key, CacheEntry $data)
    {
        try {
            // getTTL returns seconds.
            // Laravel needs datetime (<5.8 needs minutes, >5.8 needs seconds, both accept datetime)
            $lifeTime = new \DateTime($data->getTTL() . 'seconds');
            if ($lifeTime === 0) {
                return $this->cache->forever(
                    $key,
                    serialize($data)
                );
            } else if ($lifeTime > 0) {
                return $this->cache->add(
                    $key,
                    serialize($data),
                    $lifeTime
                );
            }
        } catch (\Exception $ignored) {
            // No fail if we can't save it the storage
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        return $this->cache->forget($key);
    }
}
