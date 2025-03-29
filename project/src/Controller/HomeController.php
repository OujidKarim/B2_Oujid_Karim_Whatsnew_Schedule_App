<?php

namespace App\Controller;

use App\Entity\DaysSchedule;
use App\Entity\MainNews;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HomeController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $em): Response
    {
        
        $repository = $em->getRepository(DaysSchedule::class);
        $currentDate = (new DateTimeImmutable())->setTime(0, 0);
        $dayB1 = $repository->findBy(['date' => $currentDate, 'grades' => 'B1']);
        $dayB2 = $repository->findBy(['date' => $currentDate, 'grades' => 'B2']);
        $dayB3 = $repository->findBy(['date' => $currentDate, 'grades' => 'B3']);
        $tomorrowDate = (clone $currentDate)->modify('+1 day')->setTime(0, 0);
        $week = null;
        if ($dayB1[0] ?? null) {
            $todayName = $dayB1[0]->getWeekdays()->value;
            $week = [
                'first' => null,
                'last' => null,
            ];
            $weekDate = clone $currentDate;
            if ($todayName == 'Lundi') {
                $week['first'] = $weekDate->format('d-M-y');
                $week['last'] = $weekDate->modify('+4 day')->format('d-M-y');
            }
            elseif ($todayName == 'Mardi') {
                $week['first'] = $weekDate->modify('-1 day')->format('d-M-y');
                $week['last'] = $weekDate->modify('+3 day')->format('d-M-y');
            }
            elseif ($todayName == 'Mercredi') {
                $week['first'] = $weekDate->modify('-2 day')->format('d-M-y');
                $week['last'] = $weekDate->modify('+2 day')->format('d-M-y');
            }
            elseif ($todayName == 'Jeudi') {
                $week['first'] = $weekDate->modify('-3 day')->format('d-M-y');
                $week['last'] = $weekDate->modify('+1 day')->format('d-M-y');
            }
            elseif ($todayName == 'Vendredi') {
                $week['first'] = $weekDate->modify('-4 day')->format('d-M-y');
                $week['last'] = $weekDate->format('d-M-y');
            }
        }
        else {
            $week = [
                'first' => "Pas de cours",
                'last' => "le week-end !"
            ];
        }

        $newsRepository = $em->getRepository(MainNews::class);
        $latestNews = $newsRepository->findBy([], ['created_at' => 'DESC'], 3);

        // Récupération de la dernière news externe
        $externalNews = [];
        try {
            $rssUrl = 'https://www.amiens.fr/flux-rss/actus';
            $response = $this->httpClient->request('GET', $rssUrl);
            $content = $response->getContent();

            $crawler = new \Symfony\Component\DomCrawler\Crawler($content);
            $items = $crawler->filter('item')->each(function (\Symfony\Component\DomCrawler\Crawler $node) {
                $description = $node->filter('description')->text();
                $image = null;

                $enclosure = $node->filter('enclosure');
                if ($enclosure->count() > 0 && $enclosure->attr('type') === 'image/jpeg') {
                    $image = $enclosure->attr('url');
                }

                return [
                    'title' => $node->filter('title')->text(),
                    'link' => $node->filter('link')->text(),
                    'description' => strip_tags($description),
                    'pubDate' => $node->filter('pubDate')->text(),
                    'image' => $image,
                ];
            });

            $externalNews = $items[0] ?? []; // On ne garde que la dernière news
        } catch (\Exception $e) {
        }

        return $this->render('final_view/index.html.twig', [
            'B1' => $dayB1,
            'B2' => $dayB2,
            'B3' => $dayB3,
            'week' => $week,
            'tomorrowSchedule' => $tomorrowDate->format('Y-m-d'),
            'date' => $currentDate->format('Y-m-d'),
            'latestNews' => $latestNews,
            'externalNews' => $externalNews,
        ]);
    }
}

