<?php

namespace FanFerret\QuestionBundle\Tests\Utility;

class YamlQuestionSerializerTest extends \PHPUnit_Framework_TestCase
{
    private $se;
    
    protected function setUp()
    {
        $this->se = new \FanFerret\QuestionBundle\Utility\YamlSurveySerializer();
    }
    
    public function testFromStringBadYaml()
    {
        $this->expectException(\Symfony\Component\Yaml\Exception\ParseException::class);
        $this->se->fromString('extrasquiggle: {Scratch that} squigs can go at the beginning also!');
    }
    
    public function testFromStringNonObjectRoot()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->se->fromString('hello');
    }
    
    public function testFromStringBasic()
    {
        $str =
'slug: thepalmsturksandcaicos
params: {}
language: en-CA
name: The Palms
questionGroups:
    -   order: 0
        params: {}
        questions:
            -   order: 0
                params: {}
                type: rating
                questions:
                    -   params: {}
                        type: open
                rules:
                    -   params: {}
                        type: notification
';
        $arr = $this->se->fromString($str);
        $this->assertTrue(is_array($arr));
        $s = $arr[0];
        $this->assertTrue($s instanceof \FanFerret\QuestionBundle\Entity\Survey);
        $this->assertNull($s->getId());
        $this->assertSame('en-CA',$s->getLanguage());
        $this->assertSame(0,count(get_object_vars($s->getParams())));
        $this->assertSame(0,count($s->getSurveySessions()));
        $this->assertSame('The Palms',$s->getName());
        $qgs = $s->getQuestionGroups();
        $this->assertSame(1,count($qgs));
        $qg = $qgs[0];
        $this->assertSame(0,$qg->getOrder());
        $this->assertSame(0,count(get_object_vars($qg->getParams())));
        $this->assertSame($s,$qg->getSurvey());
        $this->assertNull($qg->getId());
        $qs = $qg->getQuestions();
        $this->assertSame(1,count($qs));
        $q = $qs[0];
        $this->assertNull($q->getId());
        $this->assertSame(0,$q->getOrder());
        $this->assertSame($qg,$q->getQuestionGroup());
        $this->assertSame(0,count(get_object_vars($q->getParams())));
        $this->assertSame(0,count($q->getQuestionAnswers()));
        $this->assertSame('rating',$q->getType());
        $this->assertNull($q->getQuestion());
        $rs = $q->getRules();
        $this->assertSame(1,count($rs));
        $r = $rs[0];
        $this->assertNull($r->getId());
        $rqs = $r->getQuestions();
        $this->assertSame(1,count($rqs));
        $this->assertSame($q,$rqs[0]);
        $this->assertSame(0,count(get_object_vars($r->getParams())));
        $this->assertSame('notification',$r->getType());
        $qs = $q->getQuestions();
        $this->assertCount(1,$qs);
        $qn = $qs[0];
        $this->assertNull($qn->getId());
        $this->assertSame(0,$qn->getOrder());
        $this->assertNull($qn->getQuestionGroup());
        $this->assertSame($q,$qn->getQuestion());
        $this->assertCount(0,get_object_vars($qn->getParams()));
        $this->assertCount(0,$qn->getQuestionAnswers());
        $this->assertSame('open',$qn->getType());
    }
}
