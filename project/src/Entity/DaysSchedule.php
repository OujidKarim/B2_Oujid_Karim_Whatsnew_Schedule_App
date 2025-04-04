<?php

namespace App\Entity;

use App\Enum\Grades;
use App\Enum\Schedules;
use App\Enum\WeekDays;
use App\Repository\DaysScheduleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DaysScheduleRepository::class)]
class DaysSchedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: Schedules::class)]
    private ?Schedules $schedules = null;

    #[ORM\Column(enumType: Grades::class)]
    private ?Grades $grades = null;

    #[ORM\Column(enumType: WeekDays::class)]
    private ?WeekDays $weekdays = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSchedules(): ?Schedules
    {
        return $this->schedules;
    }

    public function setSchedules(Schedules $schedules): static
    {
        $this->schedules = $schedules;

        return $this;
    }

    public function getGrades(): ?Grades
    {
        return $this->grades;
    }

    public function setGrades(Grades $grades): static
    {
        $this->grades = $grades;

        return $this;
    }

    public function getWeekdays(): ?WeekDays
    {
        return $this->weekdays;
    }

    public function setWeekdays(WeekDays $weekdays): static
    {
        $this->weekdays = $weekdays;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }
}
