<?php

namespace App\Form;

use App\Entity\DaySchedule;
use App\Entity\DaysSchedule;
use App\Enum\Grades;
use App\Enum\Schedules;
use App\Enum\WeekDays;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DaysScheduleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('weekdays', EnumType::class, ['class' => WeekDays::class, 'required' => true])
            ->add('schedules', EnumType::class, ['class' => Schedules::class, 'required' => true])
            ->add('grades', EnumType::class, ['class' => Grades::class, 'required' => true])
            ->add('subject', TextType::class, ['required' => true])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DaysSchedule::class,
        ]);
    }
}

