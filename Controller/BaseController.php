<?php

namespace FanFerret\QuestionBundle\Controller;

abstract class BaseController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    protected function createSurvey(\FanFerret\QuestionBundle\Entity\Survey $survey)
    {
        return \FanFerret\QuestionBundle\DependencyInjection\Factory::createSurvey($survey,$this->container);
    }

    protected function createSurveyFromSurveySession(\FanFerret\QuestionBundle\Entity\SurveySession $session)
    {
        return \FanFerret\QuestionBundle\DependencyInjection\Factory::createSurveyFromSurveySession($session,$this->container);
    }

    protected function getSurveySessionRepository()
    {
        $doctrine = $this->getDoctrine();
        return $doctrine->getRepository(\FanFerret\QuestionBundle\Entity\SurveySession::class);
    }

    protected function getEntityManager() {
        $doctrine = $this->getDoctrine();
        return $doctrine->getManager();
    }

    protected function getSurveyRepository()
    {
        $doctrine = $this->getDoctrine();
        return $doctrine->getRepository(\FanFerret\QuestionBundle\Entity\Survey::class);
    }

    protected function createBadRequestException($message = 'Bad Request', $previous = null)
    {
        return \Symfony\Component\HttpKernel\Exception\BadRequestHttpException($message,$previous);
    }

    protected function createInternalServerErrorException($message = 'Internal Server Error', $previous = null)
    {
        return \Symfony\Component\HttpKernel\Exception\HttpException(500,$message,$previous);
    }
}
