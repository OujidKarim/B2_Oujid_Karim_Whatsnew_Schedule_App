<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\MainNews;
use App\Repository\MainNewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DomCrawler\Crawler;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\MainNewsType;

class NewsController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/news', name: 'app_news')]
    public function index(MainNewsRepository $mainNewsRepository): Response
    {
        $user = $this->getUser();

        if(!$user){
            return $this->redirectToRoute('app_login');
        }

        $localNews = $mainNewsRepository->findAll();
        $externalNews = $this->getExternalNews();

        return $this->render('news/index.html.twig', [
            'localNews' => $localNews,
            'externalNews' => $externalNews,
        ]);
    }

    #[Route('/news/new', name: 'app_news_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if(!$user){
            return $this->redirectToRoute('app_login');
        }

        $news = new MainNews();
        $form = $this->createForm(MainNewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($news);
            $news->setCreatedAt(new DateTimeImmutable());
            $entityManager->flush();

            return $this->redirectToRoute('app_news_show', ['id' => $news->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('news/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/news/{id}', name: 'app_news_show', methods: ['GET'])]
    public function show($id, MainNewsRepository $mainNewsRepository): Response
    {
        $user = $this->getUser();

        if(!$user){
            return $this->redirectToRoute('app_login');
        }

        $news = $mainNewsRepository->find($id);

        if (!$news) {
            throw $this->createNotFoundException('Actualité non trouvée');
        }

        return $this->render('news/show.html.twig', [
            'news' => $news,
        ]);
    }

    private function getExternalNews(): array
    {

        $rssUrl = 'https://www.amiens.fr/flux-rss/actus';
        $news = [];

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

            $news = array_slice($items, -3);
        } catch (\Exception $e) {
        }

        return $news;
    }
}
