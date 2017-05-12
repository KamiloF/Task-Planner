<?php

namespace TaskPlannerBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use TaskPlannerBundle\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * Comment controller.
 *@Security("has_role('ROLE_USER')")
 *
 */
class CommentController extends Controller
{
    /**
     * Creates a new comment entity.
     *
     * @Route("/{id}/addComm", name="addComm")
     * @Method({"GET","POST"})
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function newAction(Request $request, $id)
    {
        $task = $this->getDoctrine()->getRepository('TaskPlannerBundle:Task')->find($id);

        $comment = new Comment();

        $form = $this->createFormBuilder($comment)
            ->setAction($this->generateUrl('addComm', ['id'=>$id]))
            ->setMethod('POST')
            ->add('text', TextareaType::class, array(
                'attr'=>array('cols'=>20, 'rows'=>5)))
            ->add('save', SubmitType::class)
            ->getForm();

        $comments = $task->getComments();

        if ($request->isMethod('POST'))
        {
            $form->handleRequest($request);

            $em = $this->getDoctrine()->getManager();

            $comment = $form->getData();
            $comment->setTask($task);

            $em->persist($comment);
            $em->flush();


        }
        return $this->render('TaskPlannerBundle:Comment:comment.html.twig', array(
            'form'=>$form->createView(), 'comments'=>$comments
        ));

    }

    /**
     * @Route("/{id}/deleteCom", name="deleteCom")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteComAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $comment = $this->getDoctrine()->getRepository('TaskPlannerBundle:Comment')->find($id);

        $task = $comment->getTask();

        $qb = $em->createQueryBuilder();
        $query = $qb->delete('TaskPlannerBundle:Comment', 'comment')
            ->where('comment.id = :id')
            ->setParameter('id', $id)
            ->getQuery();
        $query->execute();
        $request->getSession()
            ->getFlashBag()
            ->add('success', 'Comment Deleted!');

        $comment = new Comment();

        $form = $this->createFormBuilder($comment)
            ->setAction($this->generateUrl('addComm', ['id'=>$id]))
            ->setMethod('POST')
            ->add('text', TextareaType::class, array(
                'attr'=>array('cols'=>20, 'rows'=>5)))
            ->add('save', SubmitType::class)
            ->getForm();

        $comments = $task->getComments();

        return $this->redirectToRoute('taskList');
    }
}
