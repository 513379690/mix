<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PoolTest extends TestCase
{

    public function test(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $redis = redis();
            $max   = $redis->maxActive * 2;
            $time  = time();
            $chan  = new \Swoole\Coroutine\Channel();
            for ($i = 0; $i < $max; $i++) {
                go(function () use ($redis, $chan) {
                    $redis->blPop('foo_list', 2);
                    $chan->push(true);
                });
            }
            for ($i = 0; $i < $max; $i++) {
                $chan->pop();
            }
            $duration = time() - $time;
            $_this->assertTrue($duration - 4 < 1 && $duration - 4 >= 0);
        };
        run($func);
    }

}
