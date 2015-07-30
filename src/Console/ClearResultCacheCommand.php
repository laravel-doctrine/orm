<?php

namespace LaravelDoctrine\ORM\Console;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\XcacheCache;
use Doctrine\Common\Persistence\ManagerRegistry;
use InvalidArgumentException;
use LogicException;

class ClearResultCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'doctrine:clear:result:cache
    {--flush : If defined, cache entries will be flushed instead of deleted/invalidated.}
    {--em= : Clear cache for a specific entity manager }';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Clear all result cache of the various cache drivers.';

    /**
     * Execute the console command.
     *
     * @param ManagerRegistry $registry
     */
    public function fire(ManagerRegistry $registry)
    {
        $names = $this->option('em') ? [$this->option('em')] : $registry->getManagerNames();

        foreach ($names as $name) {
            $em    = $registry->getManager($name);
            $cache = $em->getConfiguration()->getResultCacheImpl();

            if (!$cache) {
                throw new InvalidArgumentException('No Result cache driver is configured on given EntityManager.');
            }

            if ($cache instanceof ApcCache) {
                throw new LogicException("Cannot clear APC Cache from Console, its shared in the Webserver memory and not accessible from the CLI.");
            }

            if ($cache instanceof XcacheCache) {
                throw new LogicException("Cannot clear XCache Cache from Console, its shared in the Webserver memory and not accessible from the CLI.");
            }

            $this->message('Clearing result cache entries for <info>' . $name . '</info> entity manager');

            $result  = $cache->deleteAll();
            $message = ($result) ? 'Successfully deleted cache entries.' : 'No cache entries were deleted.';

            if ($this->option('flush')) {
                $result  = $cache->flushAll();
                $message = ($result) ? 'Successfully flushed cache entries.' : $message;
            }

            $this->info($message);
        }
    }
}
