<?php

declare(strict_types = 1);

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

class AveragePostsPerUserPerMonth extends AbstractCalculator
{
    protected const UNITS = 'posts';

    /**
     * @var array
     */
    private $users = [];

    /**
     * @var int
     */
    private $postCount = 0;

    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $userId = $postTo->getAuthorId();

        if (!in_array($userId, $this->users)) {
            $this->users[] = $userId;
        }

        $this->postCount++;
    }

    protected function doCalculate(): StatisticsTo
    {
        $usersCount = count($this->users);
        $average = $usersCount > 0 ? $this->postCount / $usersCount : 0;

        return (new StatisticsTo())->setValue(round($average, 2));
    }
}
