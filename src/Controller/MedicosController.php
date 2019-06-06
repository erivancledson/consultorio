<?php
namespace App\Controller;


use App\Entity\Medico;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use App\Helper\MedicoFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class MedicosController extends AbstractController{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MedicoFactory
     */
    private $medicoFactory;

    public function __construct(
        EntityManagerInterface $entityManager,
        MedicoFactory $medicoFactory
    ) {

        $this->entityManager = $entityManager;
        $this->medicoFactory = $medicoFactory;
    }


       /**
     * @Route("/medicos", methods={"POST"})
     */
    public function novo(Request $request): Response
    {
        $corpoRequisicao = $request->getContent();
        //cria o medico
        $medico = $this->medicoFactory->criarMedico($corpoRequisicao);
        //persiste na entidade o medico
        $this->entityManager->persist($medico);
        //envia para o banco de dados
        $this->entityManager->flush();
        //returna o Json
        return new JsonResponse($medico);
    }


      /**
     * @Route("/medicos", methods={"GET"})
     */
    public function buscarTodos(): Response
    {
        $repositorioDeMedicos = $this
            ->getDoctrine()
            ->getRepository(Medico::class);  //busca os medicos na classe Medico

        $medicoList = $repositorioDeMedicos->findAll();  //realiza o buscar todos

        return new JsonResponse($medicoList); //retorna uma lista de medicos em Json
    }


       /**
     * @Route("/medicos/{id}", methods={"GET"})
     */
    public function buscarUm(int $id): Response
    {
        $medico = $this->buscaMedico($id);  //recupera o id do medico
        $codigoRetorno = is_null($medico) ? Response::HTTP_NO_CONTENT : 200;   //se o codigo for nulo ele retorna 200

        return new JsonResponse($medico, $codigoRetorno);   //retorna um json
    }


      /**
     * @Route("/medicos/{id}", methods={"PUT"})
     */
    public function atualiza(int $id, Request $request): Response
    {
        $corpoRequisicao = $request->getContent();
        $medicoEnviado = $this->medicoFactory->criarMedico($corpoRequisicao);

        $medicoExistente = $this->buscaMedico($id);
        if (is_null($medicoExistente)) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $medicoExistente->crm = $medicoEnviado->crm;
        $medicoExistente->nome = $medicoEnviado->nome;

        $this->entityManager->flush();

        return new JsonResponse($medicoExistente);
    }

    /**
     * @Route("/medicos/{id}", methods={"DELETE"})
     */
    public function remove(int $id): Response
    {
        //busca o medico pelo id
        $medico = $this->buscaMedico($id);
        $this->entityManager->remove($medico); //prepara ele para ser deletado
        $this->entityManager->flush(); //envia para o banco de dados 

        return new Response('', Response::HTTP_NO_CONTENT);
    }





       /**
     * @param int $id
     * @return object|null
     */
    public function buscaMedico(int $id)
    {
        $repositorioDeMedicos = $this
            ->getDoctrine()
            ->getRepository(Medico::class);
        $medico = $repositorioDeMedicos->find($id);  //busca o medico apartir de um id

        return $medico;
    }
}