<?php

namespace FanFerret\QuestionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SurveyController extends Controller
{
    private function getSurveySession($token)
    {
        $doctrine = $this->getDoctrine();
        $repo = $doctrine->getRepository(\FanFerret\QuestionBundle\Entity\SurveySession::class);
        $session = $repo->getByToken($token);
        if (is_null($session)) throw $this->createNotFoundException(
            sprintf(
                'No SurveySession with token "%s"',
                $token
            )
        );
        return $session;
    }

    private function createSurvey(\FanFerret\QuestionBundle\Entity\SurveySession $session)
    {
        return \FanFerret\QuestionBundle\DependencyInjection\Factory::createSurveyFromSurveySession(
            $session,
            //  Not sure whether or not I'm supposed to
            //  rely on the fact that container in the base
            //  class is protected rather than private
            $this->container
        );
    }

    public function surveyAction(\Symfony\Component\HttpFoundation\Request $request, $token)
    {
        //  Attempt to retrieve the appropriate SurveySession
        $session = $this->getSurveySession($token);
        //  Ensure the SurveySession has not been completed
        //  (you cannot redo a survey)
        if (!is_null($session->getCompleted())) throw $this->createNotFoundException(
            sprintf(
                'SurveySession with token "%s" has been completed',
                $token
            )
        );
        //  Mark the survey as seen
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        if (!$session->getSeen()) {
            $session->setSeen(new \DateTime());
            $em->persist($session);
            $em->flush();
        }
        $survey = $this->createSurvey($session);
        //  Create form
        $fb = $this->createFormBuilder();
        $survey->addToFormBuilder($fb);
        $form = $fb->getForm();
        //  Handle form submission
        $form->handleRequest($request);
        if ($form->isValid()) {
            $session->setCompleted(new \DateTime());
            $survey->getAnswers($session,$form->getData());
            $em->persist($session);
            $em->flush();
            return $this->redirectToRoute('fgms_question_survey_finish',['token' => $token]);
        }
        //  Render
        return $this->render('FanFerretQuestionBundle:Default:form.html.twig',['survey' => $survey->render($session,$form->createView())]);
    }

    public function finishAction($token)
    {
        $session = $this->getSurveySession($token);
        //  Survey must be completed in order for you
        //  to view completion
        if (is_null($session->getCompleted())) throw $this->createNotFoundException(
            sprintf(
                'SurveySession with token "%s" has not been completed',
                $token
            )
        );
        $survey = $this->createSurvey($session);
        return $this->render('FanFerretQuestionBundle:Default:finish.html.twig',['finish' => $survey->renderFinish($session)]);
    }

    public function stylesAction($id)
    {
        $id = intval($id);
        $doctrine = $this->getDoctrine();
        $repo = $doctrine->getRepository(\FanFerret\QuestionBundle\Entity\Survey::class);
        $survey = $repo->findOneById($id);
        if (is_null($survey)) throw $this->createNotFoundException(
            sprintf(
                'No Survey with ID %d',
                $id
            )
        );
        $survey = \FanFerret\QuestionBundle\DependencyInjection\Factory::createSurvey(
            $survey,
            $this->container,
            'en-CA' //  Irrelevant for our purposes so just choose one
        );
        $css = $survey->renderStyles()->render();
        return new \Symfony\Component\HttpFoundation\Response($css,200,[
            'Content-Type' => 'text/css; charset=utf-8'
        ]);
    }
}
