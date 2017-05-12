<?php

namespace TaskPlannerBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use TaskPlannerBundle\Entity\Category;
use TaskPlannerBundle\Entity\Task;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
/**
 * User controller.
 *
 *
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="user_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->render('TaskPlannerBundle:User:index.html.twig');
    }

    /**
     * @Security("has_role('ROLE_USER')")
     * @Route("/main", name="main")
     */
    public function account()
    {
        $category = new Category();

        $form = $this->createFormBuilder($category)
            ->setAction($this->generateUrl('category_new'))
            ->setMethod('POST')
            ->add('name', TextType::class)
            ->add('save', SubmitType::class, array('label'=>'Save Category'))
            ->getForm();

        $task = new Task();

        $formtask = $this->createFormBuilder($task)
            ->setAction($this->generateUrl('newTask'))
            ->setMethod('POST')
            ->add('category', EntityType::class, array(
                'class'=>'TaskPlannerBundle:Category',
                'choice_label'=>'name',
                'placeholder'=>'Select Category...',
                'query_builder' => function ($er) {
                    $user1 = $this->get('security.token_storage')->getToken()->getUser();
                    $qb = $er->createQueryBuilder('u');
                    return $qb->select('u')
                        ->where('u.user = :identifier')
                        ->orderBy('u.name', 'ASC')
                        ->setParameter('identifier', $user1);
                }
            ))
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            ->add('deadLine', DateType::class, array(
                'widget' => 'single_text',
            ))
            ->add('save', 'submit', array('label'=>'Save Task'))
            ->getForm();

        $user1 = $this->get('security.token_storage')->getToken()->getUser();
        $id = $user1->getId();

        $em = $this->getDoctrine()->getManager();

        $tasks = $em->getRepository('TaskPlannerBundle:Task')->findAllByUserIdOrderTask($id);


        return $this->render('TaskPlannerBundle:User:show.html.twig',
            array('form'=>$form->createView(), 'formtask'=>$formtask->createView(), 'tasks'=>$tasks));
    }
}



