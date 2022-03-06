<?php

declare(strict_types = 1);

namespace Tests\unit;

use DateTime;
use PHPUnit\Framework\TestCase;
use Statistics\Dto\ParamsTo;
use SocialPost\Dto\SocialPostTo;
use Statistics\Calculator\AveragePostsPerUserPerMonth;
use Statistics\Builder\ParamsBuilder;

/**
 * Class ATestTest
 *
 * @package Tests\unit
 */
class AveragePostsPerUserPerMonthTest extends TestCase
{

    private const POST_CREATED_DATE_FORMAT = DateTime::ATOM;

    /**
     * @test
    */
    public function avaragePostsPerUserPerMonth(): void
    {
        $expectedAverage = 1;
        $targetedMonth = 'August, 2018';

        $dateParam  = DateTime::createFromFormat('F, Y', $targetedMonth);
        $parameters = ParamsBuilder::reportStatsParams($dateParam);
       
        $postsResponse = json_decode(@file_get_contents($_ENV['POSTS_RESPONSE_PATH'], true));

        $params = new ParamsTo();
        foreach ($parameters as $paramsTo) {
            $statName = $paramsTo->getStatName();
            if ($statName == 'average-posts-per-user') {
                $params = $paramsTo;
                break;
            }
        }


        $posts = $postsResponse->data->posts;
        $averageCalculator = new AveragePostsPerUserPerMonth();
        $averageCalculator->setParameters($params);
        foreach ($posts as $post) {

            $postDate = DateTime::createFromFormat(
                self::POST_CREATED_DATE_FORMAT,
                $post->created_time
            );

            $dto = (new SocialPostTo())
                ->setId($post->id ?? null)
                ->setAuthorName($post->from_name ?? null)
                ->setAuthorId($post->from_id ?? null)
                ->setText($post->message ?? null)
                ->setType($post->type ?? null)
                ->setDate($postDate ?? null);

                $averageCalculator->accumulateData($dto);
        }

        $this->assertEquals($averageCalculator->calculate()->getValue(), $expectedAverage);
    }

}
