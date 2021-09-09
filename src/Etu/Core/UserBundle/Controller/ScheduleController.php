<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\Course;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Model\SemesterManager;
use Etu\Core\UserBundle\Schedule\Helper\ScheduleBuilder;
use Sabre\VObject\Component\VCalendar;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

class ScheduleController extends Controller
{
    /**
     * @Route("/schedule/print", name="user_schedule_print")
     * @Template()
     */
    public function schedulePrintAction()
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_SCHEDULE_OWN');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $myCourses Course[] */
        $courses = $em->getRepository('EtuUserBundle:Course')->findByUser($this->getUser());

        // Builder to create the schedule
        $builder = new ScheduleBuilder();

        foreach ($courses as $course) {
            $builder->addCourse($course);
        }

        return [
            'courses' => $builder->build(),
        ];
    }

    /**
     * @Route("/schedule/course/{id}", name="schedule_course")
     * @Template()
     */
    public function courseAction(Course $course)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_SCHEDULE');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $students = $em->createQueryBuilder()
            ->select('c, u')
            ->from('EtuUserBundle:Course', 'c')
            ->leftJoin('c.user', 'u')
            ->where('c.uv = :uv')
            ->andWhere('c.start = :start')
            ->andWhere('c.end = :end')
            ->andWhere('c.week = :week')
            ->andWhere('c.room = :room')
            ->andWhere('c.day = :day')
            ->setParameter('uv', $course->getUv())
            ->setParameter('start', $course->getStart())
            ->setParameter('end', $course->getEnd())
            ->setParameter('week', $course->getWeek())
            ->setParameter('room', $course->getRoom())
            ->setParameter('day', $course->getDay())
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();

        $users = [];
        $studentsPublicSchedule = [];
        foreach ($students as $student) {
            if ($student->getUser()->getSchedulePrivacy() === $student->getUser()::PRIVACY_PUBLIC || !$this->getUser()->getIsStudent()) {
                $users[] = $student->getUser()->getLogin();
                $studentsPublicSchedule[] = $student;
            }
        }
        $cumulLogins = implode(':', $users);

        return [
            'cumulLogins' => $cumulLogins,
            'course' => $course,
            'students' => $studentsPublicSchedule,
        ];
    }

    /**
     * @Route("/schedule/export", name="user_schedule_export")
     * @Template()
     */
    public function exportAction()
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_SCHEDULE_OWN');

        return $this->generateVCalendar($this->getUser());
    }

    /**
     * @Route("/schedule/export/{token}/schedule.ics", name="user_token_schedule_export")
     * @Template()
     *
     * @param mixed $token
     */
    public function exportTokenAction($token)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->createQueryBuilder()
            ->select('u')
            ->from('EtuUserBundle:User', 'u')
            ->where('u.privateToken = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getSingleResult();

        if (!$user) {
            $this->createAccessDeniedException();
        }

        return $this->generateVCalendar($user);
    }

    protected function generateVCalendar(User $user)
    {
        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $courses Course[] */
        $courses = $em->getRepository('EtuUserBundle:Course')->findByUser($user);

        $vcalendar = new VCalendar();

        $semesterEnd = SemesterManager::current()->getEnd()->format('Ymd\THis');

        foreach ($courses as $course) {
            if ('SPJE' == $course->getUv()) {
                continue;
            }

            if (Course::DAY_SATHURDAY == $course->getDay()) {
                $day = 'saturday';
            } else {
                $day = $course->getDay();
            }

            $day = new \DateTime('last '.$day, new \DateTimeZone('Europe/Paris'));

            $start = clone $day;
            $time = explode(':', $course->getStart());
            $start->setTime($time[0], $time[1]);

            $end = clone $day;
            $time = explode(':', $course->getEnd());
            $end->setTime($time[0], $time[1]);

            $summary = ('T' != $course->getWeek()) ? $course->getUv().' ('.$course->getWeek().')' : $course->getUv();

            $vcalendar->add('VEVENT', [
                'SUMMARY' => $summary.' - '.$course->getType(),
                'DTSTART' => $start,
                'DTEND' => $end,
                'RRULE' => 'FREQ=WEEKLY;INTERVAL=1;UNTIL='.$semesterEnd,
                'LOCATION' => $course->getRoom(),
                'CATEGORIES' => $course->getType(),
            ]);
        }

        $response = new Response($vcalendar->serialize());

        $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="etuutt_schedule.ics"');

        return $response;
    }

    /**
     * @Route("/schedule/{day}", defaults={"day" = "current"}, name="user_schedule")
     * @Template()
     *
     * @param mixed $day
     */
    public function scheduleAction($day = 'current')
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_SCHEDULE_OWN');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $myCourses Course[] */
        $courses = $em->getRepository('EtuUserBundle:Course')->findByUser($this->getUser());

        // Builder to create the schedule
        $builder = new ScheduleBuilder();

        foreach ($courses as $course) {
            $builder->addCourse($course);
        }

        $days = [
            Course::DAY_MONDAY, Course::DAY_TUESDAY, Course::DAY_WENESDAY,
            Course::DAY_THURSDAY, Course::DAY_FRIDAY, Course::DAY_SATHURDAY,
        ];

        if (!\in_array($day, $days)) {
            if (0 == date('w')) { // Sunday
                $day = Course::DAY_MONDAY;
            } else {
                $day = $days[date('w') - 1];
            }
        }

        return [
            'courses' => $builder->build(),
            'currentDay' => $day,
        ];
    }
}
