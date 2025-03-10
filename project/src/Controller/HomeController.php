<?php

namespace App\Controller;

use App\Entity\DaysSchedule;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $em): Response   
    {
        $repository = $em->getRepository(DaysSchedule::class);
        $currentDate = new DateTime();
        $day = $repository->findBy(['date' => $currentDate]);
        $dayB1 = $repository->findBy(['date' => $currentDate, 'grade' => 'B1']);
        $dayB2 = $repository->findBy(['date' => $currentDate, 'grade' => 'B2Dev' or 'B2Design']);
        $dayB3 = $repository->findBy(['date' => $currentDate, 'grade' => 'B3']);
        $today = $currentDate->format('Y-m-d');
        $tomorrowDate = (clone $currentDate)->modify('+1 day');
        $test = $repository->findOneBy(['date' => $tomorrowDate]);
        $week = null;
        
        if ($dayB1[0] ?? null) {
            $todayName = $dayB1[0]->getName()->name;
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
            $week = "Pas de cours le week-end !";
        }

        return $this->render('final_view/index.html.twig', [
            'B1' => $dayB1,
            'B2' => $dayB2,
            'B3' => $dayB3,
            'week' => $week,
            'tomorrowSchedule' => $tomorrowDate->format('Y-m-d'),
            'date' => $currentDate->format('Y-m-d'),
        ]);
    }
}