<?php

namespace FanFerret\QuestionBundle\Question;

class QuestionFactory implements QuestionFactoryInterface
{
    private $twig;
    private $tokens;
    private $translator;

    public function __construct(\FanFerret\QuestionBundle\Internationalization\TranslatorInterface $translator, \Twig_Environment $twig, \FanFerret\QuestionBundle\Utility\TokenGeneratorInterface $tokens)
    {
        $this->twig = $twig;
        $this->tokens = $tokens;
        $this->translator = $translator;
    }

    public function create(\FanFerret\QuestionBundle\Entity\Question $question)
    {
        $type = $question->getType();
        if ($type === 'open') return new \FanFerret\QuestionBundle\Question\OpenQuestion(
            $question,
            $this->translator,
            $this->twig,
            $this->tokens
        );
        if ($type === 'polar') return new \FanFerret\QuestionBundle\Question\PolarQuestion(
            $question,
            $this->translator,
            $this->twig
        );
        if ($type === 'checklist') return new \FanFerret\QuestionBundle\Question\ChecklistQuestion(
            $question,
            $this->translator,
            $this->twig
        );
        if ($type === 'rating') return new \FanFerret\QuestionBundle\Question\RatingQuestion(
            $question,
            $this->translator,
            $this->twig
        );
        throw new \InvalidArgumentException(
            sprintf(
                'Unrecognized question type "%s"',
                $type
            )
        );
    }
}