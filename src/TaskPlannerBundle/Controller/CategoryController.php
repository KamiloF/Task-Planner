<?php

namespace TaskPlannerBundle\Controller;

use TaskPlannerBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


/**
 * Category controller.
 *@Security("has_role('ROLE_USER')")
 *
 */
class CategoryController extends Controller
{
    /**
     * Creates a new category entity.
     *
     * @Route("/newCategory", name="category_new")
     * @Method({"POST"})
     */
    public function newAction(Request $request)
    {
        $user = new Category();

        $form = $this->createFormBuilder($user)
            ->setAction($this->generateUrl('category_new'))
            ->setMethod('POST')
            ->add('name', 'text')
            ->add('save', 'submit', array('label'=>'Save Category'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $request->getSession()
                ->getFlashBag()
                ->add('success', 'Category added!');

            $category = $form->getData();

            $user = $this->get('security.token_storage')->getToken()->getUser();

            $category->setUser($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();


        }
        return $this->redirectToRoute('main');
    }
    /**
     * @Route("/editCat", name="editCat")
     * Method({"GET", "POST"})
     */
    public function editCatAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $categories = $user->getCategories();

        return $this->render('TaskPlannerBundle:Category:editCat.html.twig',
            array('categories'=>$categories));
    }
    /**
     * @Route("/{id}/editCatNext", name="editCatNext")
     */
    public function editCat2Action(Request $request, $id)
    {
        $category = $this->getDoctrine()->getRepository('TaskPlannerBundle:Category')->find($id);

        $form = $this->createFormBuilder($category)
            ->setAction($this->generateUrl('editCatNext', ['id'=>$id]))
            ->setMethod('POST')
            ->add('name', 'text')
            ->add('save', 'submit', array('label'=>'Commit Changes!'))
            ->getForm();

        if ($request->isMethod('GET'))
        {
            return $this->render('TaskPlannerBundle:Category:editCat2.html.twig',
                array('form'=>$form->createView()));
        }
        else if ($request->isMethod('POST'))
        {
            $form->handleRequest($request);


            if ($form->isSubmitted())
            {
                $this->getDoctrine()->getManager()->flush();

                $request->getSession()
                    ->getFlashBag()
                    ->add('success', 'Changes commited!');
            }
            return $this->redirectToRoute('taskList');
        }
    }
    /**
     * @Route("/{id}/deleteCat", name="deleteCat")
     */
    public function deleteCatAction(Request $request, $id)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $id2 = $user->getId();

        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();
        $query = $qb->delete('TaskPlannerBundle:Category', 'category')
            ->where('category.id = :id', 'category.user = :id2')
            ->setParameter('id', $id)
            ->setParameter('id2', $id2)
            ->getQuery();
        $query->execute();
        $request->getSession()
            ->getFlashBag()
            ->add('success', 'Category Deleted!');

        return $this->redirectToRoute('taskList');
    }
}





