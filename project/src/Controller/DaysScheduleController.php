<?php

namespace App\Controller;

use App\Entity\DaysSchedule;
use App\Form\DaysScheduleType;
use App\Repository\DaysScheduleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/days/schedule')]
final class DaysScheduleController extends AbstractController
{
    #[Route(name: 'app_days_schedule_index', methods: ['GET'])]
    public function index(DaysScheduleRepository $daysScheduleRepository): Response
    {
        return $this->render('days_schedule/index.html.twig', [
            'days_schedules' => $daysScheduleRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_days_schedule_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $daysSchedule = new DaysSchedule();
        $form = $this->createForm(DaysScheduleType::class, $daysSchedule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($daysSchedule);
            $entityManager->flush();

            return $this->redirectToRoute('app_days_schedule_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('days_schedule/new.html.twig', [
            'days_schedule' => $daysSchedule,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_days_schedule_show', methods: ['GET'])]
    public function show(DaysSchedule $daysSchedule): Response
    {
        return $this->render('days_schedule/show.html.twig', [
            'days_schedule' => $daysSchedule,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_days_schedule_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DaysSchedule $daysSchedule, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DaysScheduleType::class, $daysSchedule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_days_schedule_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('days_schedule/edit.html.twig', [
            'days_schedule' => $daysSchedule,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_days_schedule_delete', methods: ['POST'])]
    public function delete(Request $request, DaysSchedule $daysSchedule, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$daysSchedule->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($daysSchedule);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_days_schedule_index', [], Response::HTTP_SEE_OTHER);
    }
}
