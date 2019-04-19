<?php
/**
 * Created by PhpStorm.
 * User: samar
 * Date: 02/04/19
 * Time: 11:11
 */

namespace App\Controller\front_end;
use App\Entity\ParticipantQuiz;
use App\Entity\Quiz;
use App\Entity\ReponseParticipant;
use App\Entity\User;
use App\Repository\ParticipantQuizRepository;
use App\Repository\QuizRepository;
use App\Repository\ReponseParticipantRepository;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use http\Env\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Tests\Fixtures\ToString;

/**
 * @Route("/index")
 */
class IndexController extends Controller
{
    /**
     * @Route("/", name="index", methods={"GET" , "POST"})
     */
    public function show( QuizRepository $quizRepository, ParticipantQuizRepository $participantQuizRepository)
    {
        $listquiz= $quizRepository->findAll();
        $listparticipantquiz= $participantQuizRepository->findAll();




        return $this->render('front_end/index.html.twig',[
            'listquiz'=> $listquiz,
            'userconct' => $this->getUser(),
            'listparticipantquiz' => $listparticipantquiz
        ]);

    }


    /**
     * @Route("/saveparticipant/", name="saveparticipant", methods={"GET" , "POST"})
     */
    public function saveparticipant()
    {
        $entityManager = $this->getDoctrine()->getManager();


        return $this->render('front_end/test.html.twig');

    }



    /**
     * @Route("/resultat/{quiz}", name="resultat", methods={"GET" , "POST"})
     */
    public function resultat( Quiz $quiz, QuizRepository $quizRepository,ParticipantQuizRepository $participantQuizRepository ,ReponseParticipantRepository $reponseParticipantRepository, \Symfony\Component\HttpFoundation\Request $request)
    {
        $listquiz= $quizRepository->findAll();


        $listparticipantquiz= $participantQuizRepository->findAll();
        $reponseParticipant = $reponseParticipantRepository->findAll();

        $max= max($reponseParticipant);




        return $this->render('front_end/resultat.html.twig',[
            'quiz'=> $quiz,
            'listquiz'=> $listquiz,
            'userconct' => $this->getUser(),
            'listparticipantquiz' => $listparticipantquiz,
            'reponseParticipant' => $reponseParticipant,
            'max' => $max




        ]);
    }
    /**
     * @Route("/quizpublic/{quiz}", name="quizpublic", methods={"GET" , "POST"})
     */
    public function quizpublic( Quiz $quiz, QuizRepository $quizRepository,ParticipantQuizRepository $participantQuizRepository ,ReponseParticipantRepository $reponseParticipantRepository, \Symfony\Component\HttpFoundation\Request $request)
    {

        $entityManager = $this->getDoctrine()->getManager();

        $listquiz= $quizRepository->findAll();


        $listparticipantquiz= $participantQuizRepository->findAll();





        $nbTentative=1;

        for($i=0; $i < count($listparticipantquiz)-1; $i++){

            if ($listparticipantquiz[$i]->getUser()==$this->getUser() && $listparticipantquiz[$i]->getQuiz()==$quiz ){
                $nbTentative=$nbTentative+1;
            }
        }

        if ($nbTentative >= $quiz->getNbTentative()){
            $this->render('front_end/index.html.twig',[
                'listquiz'=> $listquiz,
                'userconct' => $this->getUser(),
                'listparticipantquiz' => $listparticipantquiz
            ]);
        }

        else{
            $p=new ParticipantQuiz();

            $p->setQuiz($quiz);

            $p->setUser($this->getUser());

            $entityManager->persist($p);

            $text = $request->get('typetext');
            $nbtexte = $request->get('nbtext');
            $t=0;

            if($text != null){


                for ($i = 0; $i < count($nbtexte); $i++) {

                        $rep = new ReponseParticipant();


                        foreach ($quiz->getPage() as $q)
                            {foreach ($q->getQuestion() as $question) {
                                if ($question == $nbtexte[$i])
                                {
                                    $t=$t+1;

                                    $rep->setReponse($text[$i]);
                                    $rep->setParticipantquiz($p);
                                    $rep->setIdQuestion($question);
                                    $rep->setTentative($nbTentative);
                                    $entityManager->persist($rep);
                                    $entityManager->flush();


                                }
                            }

                            }


              }
                $entityManager->flush();


                return $this->redirectToRoute('resultat',[
                    'quiz'=> $quiz->getId(),
                    'text' => $text,
                ]);


            }




        }

        return $this->render('front_end/quizpublic.html.twig',[
            'quiz'=> $quiz,
            'listquiz'=> $listquiz,
            'userconct' => $this->getUser(),
            'listparticipantquiz' => $listparticipantquiz,




        ]);

    }




    /**
     * @Route("/test", name="test", methods={"GET" , "POST"})
     */
    public function test(QuizRepository $quizRepository,ParticipantQuizRepository $participantQuizRepository)
    {
        $listquiz= $quizRepository->findAll();

        $listparticipantquiz= $participantQuizRepository->findAll();

        return $this->render('front_end/test.html.twig',[

            'listquiz'=> $listquiz,
            'userconct' => $this->getUser(),
            'listparticipantquiz' => $listparticipantquiz

        ]);
    }


}