<?php

namespace FanFerret\QuestionBundle\Command;

class NotificationCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('fanferret:notification');
        $this->setDescription('Sends notification emails.');
        $this->setHelp('Scans the database for SurveySession entities which have pending notifications and sends all pending notifications.');
    }

    private function createSurvey(\FanFerret\QuestionBundle\Entity\SurveySession $session)
    {
        return \FanFerret\QuestionBundle\DependencyInjection\Factory::createSurvey(
            $session->getSurvey(),
            $this->getContainer(),
            $session->getLanguage()
        );
    }

    private function writeln(\Symfony\Component\Console\Output\OutputInterface $output, $str)
    {
        $now = new \DateTime();
        $fmt = $now->format(\DateTime::ATOM);
        $output->writeln(
            sprintf(
                '[%s] %s',
                $fmt,
                $str
            )
        );
    }

    private function sendNotifications(\Symfony\Component\Console\Output\OutputInterface $output, array $sessions, $num)
    {
        $label = sprintf('NOTIFICATION #%d',$num);
        $this->writeln(
            $output,
            sprintf(
                '%s: Processing %d SurveySession entities',
                $label,
                count($sessions)
            )
        );
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        $sent = 0;       
        foreach ($sessions as $session) {
            $survey = $this->createSurvey($session);
            $this->writeln(
                $output,
                sprintf(
                    '<info>Sending Notification for  %s with session id of %d</info>',
                    $session->getEmail(),
                    $session->getId()
                )
            );            
            $entity = $survey->sendNotification($session,$num, true);
            // this means that if not null that it is either forced or nicetime
            if (is_null($entity)) continue;
            ++$sent;
            $em->persist($entity);
        }
        $em->flush();
        $this->writeln(
            $output,
            sprintf(
                '%s: %d emails sent',
                $label,
                $sent
            )
        );
        return $sent;
    }

    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        $this->writeln($output,'START');
        $doctrine = $this->getContainer()->get('doctrine');
        $repo = $doctrine->getRepository(\FanFerret\QuestionBundle\Entity\SurveySession::class);
        $sent = 0;
        //  First notification is sent immediately
        //  after checkout
        $since = new \DateInterval('PT0S');
        $sessions = $repo->getByNotification(0,$since,true);
        $sent += $this->sendNotifications($output,$sessions,1);
        //  Second notification is sent 7 days
        //  after first notification (ideally that's 7
        //  days after checkout)
        $since = new \DateInterval('P7D');
        $sessions = $repo->getByNotification(1,$since,true);
        $sent += $this->sendNotifications($output,$sessions,2);
        $this->writeln(
            $output,
            sprintf(
                'DONE: Sent %d notifications',
                $sent
            )
        );
    }
}
