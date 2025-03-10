<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DomCrawler\Crawler;

final class NewsController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/news', name: 'app_news')]
    public function index(): Response
    {
        $rssUrl = 'https://www.amiens.fr/flux-rss/actus';
        
        try {
            $response = $this->httpClient->request('GET', $rssUrl);
            $content = $response->getContent();
            
            $crawler = new Crawler($content);
            $items = $crawler->filter('item')->each(function (Crawler $node) {
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

            $items = array_slice($items, -3);

            return $this->render('news/index.html.twig', [
                'items' => array_reverse($items),
            ]);
        } catch (\Exception $e) {
            return $this->render('news/index.html.twig', [
                'error' => 'Unable to fetch news feed. Please try again later.',
            ]);
        }
    }
}
