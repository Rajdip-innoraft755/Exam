<?php

namespace App\Controller;

use App\Entity\Stock;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * HomeController is to handle the all the servicees after the user
 * successfully logged in. It used to display the stocks, add new stocks, delete
 * any stock, edit any stock.
 *
 *   @author rajdip <rajdip.roy@innoraft.com>
 */
class HomeController extends AbstractController
{
  /**
   * It stores a object of EntityManagerInterface class
   * It is to manage persistance and retriveal Entity object from Database.
   *
   * @var object
   */
  public $em;

  /**
   * It is to store the object of User Class of Entity, it is used to store the
   * information of currently logged in user.
   *
   * @var object
   */
  public $user;

  /**
   * It is to store the object of Stock Class of Entity, it is used to set
   * and get the values of Stock Class object.
   *
   * @var object
   */
  public $stock;

  /**
   * It is to store the object of StockRepository class, it used
   * to retrieve data of Stock Entity.
   *
   *   @var object
   */
  public $stockRepo;

  /**
   * This is a constructor used for initialize an object of HomeController
   * class, the main use of this function to initialize all the important class
   * and interface's object which are used by the other functions this class
   * and the information of currently logged in user.
   *
   *   @param EntityManagerInterface $em
   *     Accepts the EntityManagerInterface class object as argument.
   *
   *   @return void
   *     Constructor returns nothing.
   *
   */
  public function __construct(EntityManagerInterface $em)
  {
    $this->em = $em;
    $this->stock = new Stock();
    if (isset($_COOKIE["user"])) {
      $this->user = $this->em->getRepository(User::class)->findOneBy([
        "id" => $_COOKIE["user"],
      ]);
    }
    $this->stockRepo = $this->em->getRepository(Stock::class);
  }

  /**
   * This method is display the all stocks to the user.
   *
   *   @Route("/stock-board" , name = "stock-board")
   *     This route takes the user to the stock-board page where all the stocks
   *     are displayed.
   *
   *   @return Response
   *     Returns the response to the stock board page.
   */
  public function index(): Response
  {
    // checks whether user successfully logged in or not. If user try to access
    // this page without login then redirects to the login page.
    if (!isset($_COOKIE["active"])) {
      return $this->redirect("/");
    }

    $stocks = $this->stockRepo->findAll();
    return $this->render('home/stockboard.html.twig', [
      "stocks" => $stocks,
      "userinfo" => $this->user,
    ]);
  }

  /**
   * This function is used when user wants to add a new stock, it takes the
   * details of stock from user and store that in database and refresh the page.
   * It also displays the stocks previously added by the current users.
   *
   *   @Route("/stock-entry")
   *     This route take user to the stock entry where user can add new stocks
   *
   *   @param Request $rq
   *     This accepts user inputs.
   *
   *   @return Response
   *     Returns response to the stock entry page.
   */
  public function stockEntry(Request $rq): Response
  {

    // checks whether user successfully logged in or not. If user try to access
    // this page without login then redirects to the login page.
    if (!isset($_COOKIE["active"])) {
      return $this->redirect("/");
    }

    $data = $rq->request->all();
    if ($data) {
      $this->stock->setName($data["name"]);
      $this->stock->setprice($data["price"]);
      $currentDate = date("Y-m-d", time());
      $this->stock->setCreateDate($currentDate);
      $this->stock->setLastUpdate($currentDate);
      $this->stock->setOwner($this->user);
      $this->em->persist($this->stock);
      $this->em->flush();
    }
    $stocks = $this->stockRepo->findBy([
      "owner" => $this->user,
    ]);
    return $this->render("home/stockentry.html.twig",[
      "stocks" => $stocks,
      "userinfo" => $this->user,
    ]);
  }

  /**
   * This method is used when user wants to delete any stock which is previously
   * added by him.
   *
   *   @Route("/deletestock", name = "deletestock")
   *     This doesn't take user to a new page its just send response to the
   *     calling ajax function.
   *
   *   @return JsonResponse
   *     Returns success message as JsonResponse to the calling ajax function
   */
  public function deleteStock(Request $rq): JsonResponse
  {
    $this->stock = $this->stockRepo->findOneBy([
      "id" => $rq->request->get("stockId"),
    ]);
    $this->em->remove($this->stock);
    $this->em->flush();
    return new JsonResponse(json_encode([
      "success" => TRUE,
    ]));
  }
  /**
   * This method is to return the updated the stock table when
   * any changes occurs.
   *
   *   @Route("/update")
   *     This doesnt's take user to a new page.
   *
   *   @param Request $rq
   *     This accepts user current url as input.
   *
   *   @return Response
   *     Returns the response with updated table to the calling ajax function.
   */
  public function update(Request $rq):Response
  {
    $url = $rq->request->get("url");
    if ($url == "/stock-entry") {
      $stocks = $this->stockRepo->findBy([
        "owner" => $this->user,
      ]);
    }
    else
    {
      $stocks = $this->stockRepo->findAll();
    }
    return $this->render("home/stocks.html.twig", [
      "stocks" => $stocks,
      "userinfo" => $this->user,
    ]);
  }
}
