<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * AuthController is to handle the all the servicees before the user
 * successfully logged in. It manages login, registration and logout
 * operatiions.
 *
 *   @author rajdip <rajdip.roy@innoraft.com>
 */
class AuthController extends AbstractController
{
  /**
   * It stores a object of EntityManagerInterface class
   * It is to manage persistance and retriveal Entity object from Database.
   *
   *   @var object
   */
  public $em;

  /**
   * It is to store the object of User Class of Entity, it is used to set
   * and get the values of User Class object.
   *
   *   @var object
   */
  public $user;

  /**
   * It is to store the object of UserRepository class, it used
   * to retrieve data of User Entity.
   *
   *   @var object
   */
  public $userRepo;

  /**
   * This is a constructor used for initialize an object of AuthController
   * class, the main use of this function to initialize all the important class
   * and interface's object which are used by the other functions this class.
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
    $this->user = new User();
    $this->userRepo = $this->em->getRepository(User::class);
  }

  /**
   * This method is to render the login page and validate the input user gives
   * in login page and based on validation redirected to stock-board page
   * and store the user id as cookie value along with a cookie variable named
   * active.
   *
   *   @Route("/", name = "login")
   *     This route takes user to the login page.
   *
   *   @param object $rq
   *     It accepts Request object as parameter to handle the input data.
   *
   *   @return Response
   *     Returns the response to the stock-board page if user entered the correct
   *     credentials otherwise to the login page with proper error message.
   */
  public function index(Request $rq): Response
  {
    $data = $rq->request->all();
    $error = "";
    if ($data) {
      $this->user = $this->userRepo->findOneBy([
        "emailId" => $data["emailId"],
        "password" => md5($data["password"]),
      ]);

      if ($this->user) {
        setcookie("active", TRUE, 0.5, "/");
        setcookie("user",$this->user->getId(),0.5,"/");
        return $this->redirect("/stock-board");
      }
      $error = "* Invalid credentials.";
    }
    return $this->render('auth/login.html.twig',[
      "loginErr" => $error,
    ]);
  }

  /**
   * This method is to render the register page and validate the input user gives
   * in register page and based on validation redirected to login page
   * and store the the details into database.
   *
   *   @Route("/register", name = "register")
   *     This route takes the user to the register page.
   *
   *   @param object $rq
   *     It accepts Request object as parameter to handle the input data.
   *
   *   @return Response
   *     Returns the response to the login page if user entered the valid
   *     data otherwise to the register page with proper error message.
   */
  public function register(Request $rq): Response
  {
    $data = $rq->request->all();
    $error = [];
    if ($data) {

      // Checks whether the name contains only alphabet or not .
      // If name contains other than alphabets then store the error .
      if (!(preg_match("/^[a-zA-Z ]*$/", $data["name"]))) {
        $error["name"] = "* Name should contain only alphabet";
      }

      // Checks whether the email id is in valid format or not.
      // If email id is not in valid format then store the email format error
      // otherwise check whether the email id is already used or not, if used
      // then store that error also.
      if ((preg_match("/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/", $data["emailId"]))) {
        $isAvailable = $this->userRepo->findBy(["emailId" => $data["emailId"]]);
        if ($isAvailable) {
          $error["emailId"] = "* email Id already exits";
        }
      }
      else {
        $error["emailId"] = "* not a valid email.";
      }

      // Checks whether the password follows the following checkpoints or not
      // more than 8 characters atleast one uppercase and one lowercase and one
      // digit and one special characters(@, $, #, !, %, *, ?, &).
      // If the password does not match these conditions then store the error.
      if (!(preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$#!%*?&])[A-Za-z\d@#$!%*?&]{8,}$/", $data["password"]))) {
        $error["password"] = "* Weak password.";
      }

      if (empty($error)) {
        $this->user->setName($data["name"]);
        $this->user->setEmailId($data["emailId"]);
        $this->user->setPassword(md5($data["password"]));
        $this->em->persist($this->user);
        $this->em->flush();
        return $this->redirect("/");
      }
    }
    return $this->render("auth/register.html.twig",[
      "error" => $error,
    ]);
  }

  /**
   * This method is to destroy the cookie variable which are set at the time of
   * user login.
   *
   *   @Route("/logout", name = "logout")
   *     This route takes user to the login page after destroying the cookie
   *     variables.
   *
   *   @return Response
   *     Return response to the login page.
   */
  public function logout(): Response
  {
    setcookie("active","",0,"/");
    setcookie("user", "", 0, "/");
    return $this->redirect("/");
  }
}
