<?php

namespace AppBundle\Controller;

use AppBundle\Form\StressLogForm;
use AppBundle\Util\TagManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\StressLog;
use Symfony\Component\HttpFoundation\Request;

class StressLogController extends Controller
{

    /**
     * @Route("/new", name="newLog")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function newAction(Request $request)
    {
        $log = StressLog::create($this->getUser());

        $form = $this->createForm(StressLogForm::class, $log);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($log);
            $em->flush();

            return $this->redirectToRoute('showLog', array('id' => $log->getId()));
        }

        /** @var TagManager $tagManager */
        $tagManager = $this->get('app.tag_manager');

        return $this->render('stresslog/new.html.twig', array(
            'form' => $form->createView(),
            'suggestedFactors' => $tagManager->getSuggestions($this->getUser()),
        ));
    }

    /**
     * @Route("/log/{id}/edit", name="editLog")
     * @Security("user == log.getUser() || has_role('ROLE_ADMIN')")
     */
    public function editAction(StressLog $log, Request $request)
    {
        $form = $this->createForm(StressLogForm::class, $log);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('showLog', array('id' => $log->getId()));
        }

        /** @var TagManager $tagManager */
        $tagManager = $this->get('app.tag_manager');

        return $this->render('stresslog/edit.html.twig', array(
            'log' => $log,
            'form' => $form->createView(),
            'suggestedFactors' => $tagManager->getSuggestions($this->getUser()),
        ));
    }

    /**
     * @Route("/log/{id}", name="showLog")
     * @Security("user == log.getUser() || has_role('ROLE_ADMIN')")
     */
    public function showAction(StressLog $log)
    {
        return $this->render('stresslog/show.html.twig', array(
            'log' => $log,
        ));
    }

    /**
     * @Route("/delete")
     */
    public function deleteAction()
    {
        return $this->render('stresslog/delete.html.twig', array(
            // ...
        ));
    }

}
